<?php
// Fungsi keranjang & pesanan (tabel carts, cart_items, orders, order_items, payments).

function cart_id(int $uid, bool $create = false): ?int {
    $st = db()->prepare('SELECT id_cart FROM carts WHERE id_user = ?');
    $st->execute([$uid]);
    $id = $st->fetchColumn();
    if ($id) return (int)$id;
    if (!$create) return null;
    db()->prepare('INSERT INTO carts (id_user, created_at) VALUES (?, ?)')->execute([$uid, now()]);
    return (int)db()->lastInsertId();
}

function cart_items(int $uid, bool $lock = false): array {
    $sql = 'SELECT ci.id_cart_item, ci.jumlah, v.id_variant, v.ukuran_ml, v.harga, v.stok, v.status_variant,
                   p.nama_produk, p.konsentrasi, p.gambar, p.status_produk
            FROM cart_items ci
            JOIN carts c ON c.id_cart = ci.id_cart
            JOIN produk_variants v ON v.id_variant = ci.id_variant
            JOIN produk p ON p.id_produk = v.id_produk
            WHERE c.id_user = ? ORDER BY ci.id_cart_item' . ($lock ? for_update() : '');
    $st = db()->prepare($sql);
    $st->execute([$uid]);
    return $st->fetchAll();
}

function cart_payload(int $uid): array {
    $items = []; $total = 0.0; $count = 0;
    foreach (cart_items($uid) as $r) {
        $sub = (float)$r['harga'] * (int)$r['jumlah'];
        $total += $sub; $count += (int)$r['jumlah'];
        $items[] = [
            'id' => (int)$r['id_cart_item'], 'variant' => (int)$r['id_variant'], 'name' => $r['nama_produk'],
            'size' => (int)$r['ukuran_ml'], 'type' => (string)$r['konsentrasi'], 'price' => (float)$r['harga'],
            'qty' => (int)$r['jumlah'], 'stock' => (int)$r['stok'], 'image' => asset_url($r['gambar']),
        ];
    }
    return ['items' => $items, 'total' => $total, 'count' => $count];
}

/** Membuat pesanan dari keranjang. Mengembalikan id_order. Melempar RuntimeException bila gagal. */
function place_order(int $uid, string $alamat, string $metode): int {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $items = cart_items($uid, true);
        if (!$items) throw new RuntimeException('Keranjang kamu kosong.');
        $total = 0.0;
        foreach ($items as $r) {
            if ($r['status_variant'] !== 'aktif' || $r['status_produk'] !== 'aktif')
                throw new RuntimeException($r['nama_produk'] . ' ' . $r['ukuran_ml'] . ' ml sudah tidak tersedia. Hapus dari keranjang dulu.');
            if ((int)$r['jumlah'] > (int)$r['stok'])
                throw new RuntimeException('Stok ' . $r['nama_produk'] . ' ' . $r['ukuran_ml'] . ' ml tinggal ' . (int)$r['stok'] . '. Kurangi jumlahnya.');
            $total += (float)$r['harga'] * (int)$r['jumlah'];
        }
        $now = now();
        $pdo->prepare("INSERT INTO orders (id_user, tanggal_pesanan, total_harga, alamat_pengiriman, status_pesanan, created_at)
                       VALUES (?, ?, ?, ?, 'menunggu_pembayaran', ?)")->execute([$uid, $now, $total, $alamat, $now]);
        $oid = (int)$pdo->lastInsertId();

        $ins = $pdo->prepare('INSERT INTO order_items (id_order, id_variant, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)');
        $dec = $pdo->prepare('UPDATE produk_variants SET stok = stok - ? WHERE id_variant = ? AND stok >= ?');
        foreach ($items as $r) {
            $q = (int)$r['jumlah'];
            $ins->execute([$oid, $r['id_variant'], $q, $r['harga'], (float)$r['harga'] * $q]);   // harga_satuan = snapshot
            $dec->execute([$q, $r['id_variant'], $q]);
            if ($dec->rowCount() !== 1) throw new RuntimeException('Stok berubah saat diproses. Coba lagi.');
        }
        $ref = 'ATEA-' . date('Ymd') . '-' . str_pad((string)$oid, 5, '0', STR_PAD_LEFT);
        $pdo->prepare("INSERT INTO payments (id_order, metode_pembayaran, tanggal_pembayaran, jumlah_pembayaran, status_pembayaran, nomor_referensi)
                       VALUES (?, ?, NULL, ?, 'pending', ?)")->execute([$oid, $metode, $total, $ref]);
        $cid = cart_id($uid);
        if ($cid) $pdo->prepare('DELETE FROM cart_items WHERE id_cart = ?')->execute([$cid]);
        $pdo->commit();
        return $oid;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

/** Membatalkan pesanan + mengembalikan stok. $uid = null untuk admin. */
function cancel_order(int $oid, ?int $uid = null): bool {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $sql = 'SELECT status_pesanan FROM orders WHERE id_order = ?' . ($uid ? ' AND id_user = ?' : '') . for_update();
        $st = $pdo->prepare($sql);
        $st->execute($uid ? [$oid, $uid] : [$oid]);
        $status = $st->fetchColumn();
        $allowed = $uid ? ['menunggu_pembayaran'] : ['menunggu_pembayaran', 'diproses'];
        if (!$status || !in_array($status, $allowed, true)) { $pdo->rollBack(); return false; }
        $pdo->prepare("UPDATE orders SET status_pesanan = 'dibatalkan' WHERE id_order = ?")->execute([$oid]);
        $its = $pdo->prepare('SELECT id_variant, jumlah FROM order_items WHERE id_order = ?');
        $its->execute([$oid]);
        $up = $pdo->prepare('UPDATE produk_variants SET stok = stok + ? WHERE id_variant = ?');
        foreach ($its->fetchAll() as $i) $up->execute([$i['jumlah'], $i['id_variant']]);
        $pdo->prepare("UPDATE payments SET status_pembayaran = CASE WHEN status_pembayaran = 'berhasil' THEN 'refund' ELSE 'gagal' END WHERE id_order = ?")->execute([$oid]);
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
