<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/cart.php';
require_admin();
$pdo = db();
$statuses = ['menunggu_pembayaran' => 'Menunggu pembayaran', 'diproses' => 'Diproses', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $oid = (int)($_POST['id'] ?? 0);
    $act = $_POST['action'] ?? '';
    $cur = $pdo->prepare('SELECT status_pesanan FROM orders WHERE id_order = ?');
    $cur->execute([$oid]);
    $curStatus = $cur->fetchColumn();
    if (!$curStatus) { flash('err', 'Pesanan tidak ditemukan.'); redirect(url('admin/index.php')); }

    if ($act === 'status') {
        $new = $_POST['status'] ?? '';
        if (!isset($statuses[$new])) { flash('err', 'Status tidak valid.'); }
        elseif ($curStatus === 'dibatalkan') { flash('err', 'Pesanan yang sudah dibatalkan tidak bisa diubah.'); }
        elseif ($new === 'dibatalkan') { if (cancel_order($oid, null)) flash('ok', "Pesanan #$oid dibatalkan, stok dikembalikan."); else flash('err', 'Pesanan tidak bisa dibatalkan (mungkin sudah dikirim).'); }
        else {
            $pdo->prepare('UPDATE orders SET status_pesanan = ? WHERE id_order = ?')->execute([$new, $oid]);
            flash('ok', "Status pesanan #$oid diubah.");
        }
    } elseif ($act === 'pay_ok' || $act === 'pay_fail') {
        if ($curStatus === 'dibatalkan') { flash('err', 'Pesanan sudah dibatalkan.'); }
        else {
            if ($act === 'pay_ok') {
                $pdo->prepare("UPDATE payments SET status_pembayaran = 'berhasil', tanggal_pembayaran = ? WHERE id_order = ?")->execute([now(), $oid]);
                if ($curStatus === 'menunggu_pembayaran') $pdo->prepare("UPDATE orders SET status_pesanan = 'diproses' WHERE id_order = ?")->execute([$oid]);
                flash('ok', "Pembayaran pesanan #$oid dikonfirmasi, status menjadi Diproses.");
            } else {
                $pdo->prepare("UPDATE payments SET status_pembayaran = 'gagal' WHERE id_order = ?")->execute([$oid]);
                flash('ok', "Pembayaran pesanan #$oid ditandai gagal.");
            }
        }
    }
    redirect(url('admin/index.php' . (!empty($_GET['s']) ? '?s=' . rawurlencode($_GET['s']) : '')));
}

$filter = $_GET['s'] ?? '';
$where = isset($statuses[$filter]) ? 'WHERE o.status_pesanan = ?' : '';
$st = $pdo->prepare("SELECT o.*, u.nama, u.email, u.no_telepon,
        (SELECT metode_pembayaran FROM payments WHERE id_order = o.id_order ORDER BY id_payment DESC LIMIT 1) AS metode,
        (SELECT status_pembayaran FROM payments WHERE id_order = o.id_order ORDER BY id_payment DESC LIMIT 1) AS status_bayar,
        (SELECT nomor_referensi FROM payments WHERE id_order = o.id_order ORDER BY id_payment DESC LIMIT 1) AS ref
     FROM orders o JOIN `user` u ON u.id_user = o.id_user $where ORDER BY o.id_order DESC LIMIT 100");
$st->execute($where ? [$filter] : []);
$orders = $st->fetchAll();
$itemsBy = [];
foreach ($pdo->query('SELECT oi.id_order, oi.jumlah, oi.harga_satuan, p.nama_produk, v.ukuran_ml, v.sku FROM order_items oi JOIN produk_variants v ON v.id_variant = oi.id_variant JOIN produk p ON p.id_produk = v.id_produk') as $r) $itemsBy[$r['id_order']][] = $r;

$stat = $pdo->query("SELECT
   (SELECT COUNT(*) FROM orders) AS total_order,
   (SELECT COUNT(*) FROM orders WHERE status_pesanan = 'menunggu_pembayaran') AS menunggu,
   (SELECT COALESCE(SUM(jumlah_pembayaran), 0) FROM payments WHERE status_pembayaran = 'berhasil') AS omzet,
   (SELECT COUNT(*) FROM `user` WHERE role = 'customer') AS pelanggan")->fetch();

$title = 'Pesanan';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Pesanan</h1>
<div class="stat-row">
  <div class="stat"><span>Total pesanan</span><b><?= (int)$stat['total_order'] ?></b></div>
  <div class="stat"><span>Menunggu pembayaran</span><b><?= (int)$stat['menunggu'] ?></b></div>
  <div class="stat"><span>Omzet (pembayaran berhasil)</span><b><?= h(rupiah($stat['omzet'])) ?></b></div>
  <div class="stat"><span>Pelanggan</span><b><?= (int)$stat['pelanggan'] ?></b></div>
</div>
<div class="filter-row">
  <a href="<?= h(url('admin/index.php')) ?>" class="<?= $filter === '' ? 'active' : '' ?>">Semua</a>
  <?php foreach ($statuses as $k => $l): ?><a href="?s=<?= h($k) ?>" class="<?= $filter === $k ? 'active' : '' ?>"><?= h($l) ?></a><?php endforeach; ?>
</div>
<?php if (!$orders): ?><p class="muted">Belum ada pesanan.</p><?php endif; ?>
<?php foreach ($orders as $o): ?>
<div class="panel admin-order">
  <div class="order-head">
    <div><b>#<?= (int)$o['id_order'] ?></b> · <?= h($o['nama']) ?> <small class="muted">(<?= h($o['email']) ?><?= $o['no_telepon'] ? ' · ' . h($o['no_telepon']) : '' ?>)</small><br><small class="muted"><?= h($o['tanggal_pesanan']) ?> · ref <?= h($o['ref'] ?? '-') ?></small></div>
    <span class="badge badge-<?= h($o['status_pesanan']) ?>"><?= h($statuses[$o['status_pesanan']] ?? $o['status_pesanan']) ?></span>
  </div>
  <ul class="order-items">
    <?php foreach ($itemsBy[$o['id_order']] ?? [] as $i): ?>
      <li><span><?= h($i['nama_produk']) ?> <?= (int)$i['ukuran_ml'] ?> ml <small class="muted">(<?= h($i['sku']) ?>)</small> × <?= (int)$i['jumlah'] ?></span><span><?= h(rupiah($i['harga_satuan'] * $i['jumlah'])) ?></span></li>
    <?php endforeach; ?>
  </ul>
  <p class="order-total">Total <b><?= h(rupiah($o['total_harga'])) ?></b> · <?= h($o['metode'] ?? '-') ?> · pembayaran: <b><?= h($o['status_bayar'] ?? '-') ?></b></p>
  <details><summary>Alamat pengiriman</summary><p class="muted small"><?= nl2br(h($o['alamat_pengiriman'])) ?></p></details>
  <?php if ($o['status_pesanan'] !== 'dibatalkan'): ?>
  <div class="order-actions">
    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id_order'] ?>"><input type="hidden" name="action" value="status">
      <select name="status"><?php foreach ($statuses as $k => $l): ?><option value="<?= h($k) ?>" <?= $k === $o['status_pesanan'] ? 'selected' : '' ?>><?= h($l) ?></option><?php endforeach; ?></select>
      <button class="btn-sm" type="submit">Ubah status</button>
    </form>
    <?php if (($o['status_bayar'] ?? '') === 'pending'): ?>
    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id_order'] ?>"><input type="hidden" name="action" value="pay_ok"><button class="btn-sm ok" type="submit">Konfirmasi pembayaran</button></form>
    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id_order'] ?>"><input type="hidden" name="action" value="pay_fail"><button class="btn-sm" type="submit">Tandai gagal</button></form>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php endforeach; ?>
</main></body></html>
