<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/catalog.php';
require __DIR__ . '/../includes/cart.php';

$user = current_user();
if (!$user) json_out(['error' => 'login', 'items' => [], 'total' => 0, 'count' => 0], 401);
$uid = (int)$user['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) json_out(['error' => 'Sesi tidak valid, muat ulang halaman.'], 419);
    $in = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $in['action'] ?? '';
    $pdo = db();

    if ($action === 'add') {
        $vid = (int)($in['variant'] ?? 0);
        $qty = max(1, min(99, (int)($in['qty'] ?? 1)));
        $st = $pdo->prepare("SELECT v.stok FROM produk_variants v JOIN produk p ON p.id_produk = v.id_produk
                             WHERE v.id_variant = ? AND v.status_variant = 'aktif' AND p.status_produk = 'aktif'");
        $st->execute([$vid]);
        $stok = $st->fetchColumn();
        if ($stok === false) json_out(['error' => 'Produk tidak tersedia.'], 404);
        if ((int)$stok < 1) json_out(['error' => 'Stok habis.'], 409);
        $cid = cart_id($uid, true);
        $ex = $pdo->prepare('SELECT id_cart_item, jumlah FROM cart_items WHERE id_cart = ? AND id_variant = ?');
        $ex->execute([$cid, $vid]);
        if ($row = $ex->fetch()) {
            $new = min((int)$stok, (int)$row['jumlah'] + $qty);
            $pdo->prepare('UPDATE cart_items SET jumlah = ? WHERE id_cart_item = ?')->execute([$new, $row['id_cart_item']]);
        } else {
            $pdo->prepare('INSERT INTO cart_items (id_cart, id_variant, jumlah) VALUES (?, ?, ?)')->execute([$cid, $vid, min((int)$stok, $qty)]);
        }
    } elseif ($action === 'update' || $action === 'remove') {
        $iid = (int)($in['id'] ?? 0);
        $cid = cart_id($uid);
        $qty = $action === 'remove' ? 0 : (int)($in['qty'] ?? 0);
        if ($cid) {
            if ($qty <= 0) {
                $pdo->prepare('DELETE FROM cart_items WHERE id_cart_item = ? AND id_cart = ?')->execute([$iid, $cid]);
            } else {
                $st = $pdo->prepare('SELECT v.stok FROM cart_items ci JOIN produk_variants v ON v.id_variant = ci.id_variant WHERE ci.id_cart_item = ? AND ci.id_cart = ?');
                $st->execute([$iid, $cid]);
                $stok = $st->fetchColumn();
                if ($stok !== false) {
                    $pdo->prepare('UPDATE cart_items SET jumlah = ? WHERE id_cart_item = ? AND id_cart = ?')->execute([max(1, min(99, (int)$stok, $qty)), $iid, $cid]);
                }
            }
        }
    } else {
        json_out(['error' => 'Aksi tidak dikenal.'], 400);
    }
}
json_out(cart_payload($uid));
