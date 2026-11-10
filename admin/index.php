<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/cart.php';
require_admin();
$pdo = db();
$statuses = [
    'menunggu_pembayaran' => 'Menunggu pembayaran',
    'diproses' => 'Diproses',
    'dikirim' => 'Dikirim',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $oid = (int)($_POST['id'] ?? 0);
    $act = $_POST['action'] ?? '';
    $cur = $pdo->prepare('SELECT status_pesanan FROM orders WHERE id_order = ?');
    $cur->execute([$oid]);
    $curStatus = $cur->fetchColumn();
    if (!$curStatus) {
        flash('err', 'Pesanan tidak ditemukan.');
        redirect(url('admin/index.php'));
    }

    if ($act === 'status') {
        $new = $_POST['status'] ?? '';
        if (!isset($statuses[$new])) {
            flash('err', 'Status tidak valid.');
        } elseif ($curStatus === 'dibatalkan') {
            flash('err', 'Pesanan yang sudah dibatalkan tidak bisa diubah.');
        } elseif ($new === 'dibatalkan') {
            if (cancel_order($oid, null)) flash('ok', "Pesanan #$oid dibatalkan, stok dikembalikan.");
            else flash('err', 'Pesanan tidak bisa dibatalkan (mungkin sudah dikirim).');
        } else {
            $pdo->prepare('UPDATE orders SET status_pesanan = ? WHERE id_order = ?')->execute([$new, $oid]);
            flash('ok', "Status pesanan #$oid diubah.");
        }
    } elseif ($act === 'pay_ok' || $act === 'pay_fail') {
        if ($curStatus === 'dibatalkan') {
            flash('err', 'Pesanan sudah dibatalkan.');
        } else {
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
foreach ($pdo->query('SELECT oi.id_order, oi.jumlah, oi.harga_satuan, p.nama_produk, v.ukuran_ml, v.sku
    FROM order_items oi JOIN produk_variants v ON v.id_variant = oi.id_variant
    JOIN produk p ON p.id_produk = v.id_produk') as $r) {
    $itemsBy[$r['id_order']][] = $r;
}

$stat = $pdo->query("SELECT
    (SELECT COUNT(*) FROM orders) AS total_order,
    (SELECT COUNT(*) FROM orders WHERE status_pesanan = 'menunggu_pembayaran') AS menunggu,
    (SELECT COALESCE(SUM(jumlah_pembayaran), 0) FROM payments WHERE status_pembayaran = 'berhasil') AS omzet,
    (SELECT COUNT(*) FROM `user` WHERE role = 'customer') AS pelanggan,
    (SELECT COUNT(*) FROM produk WHERE status_produk = 'aktif') AS produk_aktif")->fetch();

$title = 'Dashboard';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info"><div class="inner"><h3><?= (int)$stat['total_order'] ?></h3><p>Total Pesanan</p></div><div class="icon"><i class="fas fa-shopping-bag"></i></div><a href="#daftar-pesanan" class="small-box-footer">Lihat pesanan <i class="fas fa-arrow-circle-right"></i></a></div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning"><div class="inner"><h3><?= (int)$stat['menunggu'] ?></h3><p>Menunggu Pembayaran</p></div><div class="icon"><i class="fas fa-clock"></i></div><a href="?s=menunggu_pembayaran" class="small-box-footer">Periksa sekarang <i class="fas fa-arrow-circle-right"></i></a></div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success"><div class="inner"><h3 class="text-truncate"><?= h(rupiah($stat['omzet'])) ?></h3><p>Omzet Pembayaran Berhasil</p></div><div class="icon"><i class="fas fa-wallet"></i></div><a href="#daftar-pesanan" class="small-box-footer">Lihat transaksi <i class="fas fa-arrow-circle-right"></i></a></div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-primary"><div class="inner"><h3><?= (int)$stat['pelanggan'] ?></h3><p>Total Pelanggan</p></div><div class="icon"><i class="fas fa-users"></i></div><a href="<?= h(url('admin/products.php')) ?>" class="small-box-footer">Kelola toko <i class="fas fa-arrow-circle-right"></i></a></div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="info-box"><span class="info-box-icon bg-secondary"><i class="fas fa-box-open"></i></span><div class="info-box-content"><span class="info-box-text">Produk Aktif</span><span class="info-box-number"><?= (int)$stat['produk_aktif'] ?> produk</span></div></div>
  </div>
  <div class="col-md-6">
    <div class="info-box"><span class="info-box-icon bg-dark"><i class="fas fa-user-shield"></i></span><div class="info-box-content"><span class="info-box-text">Login sebagai</span><span class="info-box-number"><?= h($admin['nama'] ?? 'Administrator') ?></span></div></div>
  </div>
</div>

<div class="card card-outline card-primary" id="daftar-pesanan">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-receipt mr-1"></i> Daftar Pesanan Terbaru</h3>
    <div class="card-tools"><span class="badge badge-light">Maksimal 100 pesanan</span></div>
  </div>
  <div class="card-body">
    <div class="mb-3 d-flex flex-wrap">
      <a href="<?= h(url('admin/index.php')) ?>" class="btn btn-sm <?= $filter === '' ? 'btn-primary' : 'btn-outline-secondary' ?> mr-1 mb-1">Semua</a>
      <?php foreach ($statuses as $k => $label): ?>
        <a href="?s=<?= h($k) ?>#daftar-pesanan" class="btn btn-sm <?= $filter === $k ? 'btn-primary' : 'btn-outline-secondary' ?> mr-1 mb-1"><?= h($label) ?></a>
      <?php endforeach; ?>
    </div>
    <?php if (!$orders): ?>
      <div class="text-center text-muted py-5"><i class="fas fa-inbox fa-3x mb-3"></i><p class="mb-0">Belum ada pesanan untuk ditampilkan.</p></div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="thead-light"><tr><th>Pesanan</th><th>Pelanggan</th><th>Produk</th><th>Total</th><th>Pembayaran</th><th>Status</th><th style="min-width:250px">Tindakan</th></tr></thead>
          <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><strong>#<?= (int)$o['id_order'] ?></strong><br><small class="text-muted"><?= h($o['tanggal_pesanan']) ?></small><br><small class="text-muted">Ref: <?= h($o['ref'] ?? '-') ?></small></td>
              <td><?= h($o['nama']) ?><br><small class="text-muted"><?= h($o['email']) ?></small><?php if ($o['no_telepon']): ?><br><small class="text-muted"><?= h($o['no_telepon']) ?></small><?php endif; ?></td>
              <td>
                <?php foreach ($itemsBy[$o['id_order']] ?? [] as $item): ?>
                  <div><?= h($item['nama_produk']) ?> <small class="text-muted"><?= (int)$item['ukuran_ml'] ?> ml × <?= (int)$item['jumlah'] ?></small></div>
                <?php endforeach; ?>
              </td>
              <td><strong><?= h(rupiah($o['total_harga'])) ?></strong></td>
              <td><?= h($o['metode'] ?? '-') ?><br><span class="badge badge-light"><?= h($o['status_bayar'] ?? 'Belum ada') ?></span></td>
              <td><span class="badge badge-<?= h($o['status_pesanan']) ?>"><?= h($statuses[$o['status_pesanan']] ?? $o['status_pesanan']) ?></span><details class="mt-2"><summary class="small">Alamat kirim</summary><small class="text-muted"><?= nl2br(h($o['alamat_pengiriman'])) ?></small></details></td>
              <td>
                <?php if ($o['status_pesanan'] !== 'dibatalkan'): ?>
                  <form method="post" class="mb-2"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id_order'] ?>"><input type="hidden" name="action" value="status">
                    <div class="input-group input-group-sm"><select name="status" class="form-control"><?php foreach ($statuses as $k => $label): ?><option value="<?= h($k) ?>" <?= $k === $o['status_pesanan'] ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select><div class="input-group-append"><button class="btn btn-primary" type="submit">Ubah</button></div></div>
                  </form>
                  <?php if (($o['status_bayar'] ?? '') === 'pending'): ?>
                    <form method="post" class="d-inline-block mb-1"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id_order'] ?>"><input type="hidden" name="action" value="pay_ok"><button class="btn btn-success btn-xs" type="submit" onclick="return confirm('Konfirmasi pembayaran pesanan ini?')"><i class="fas fa-check mr-1"></i>Konfirmasi bayar</button></form>
                    <form method="post" class="d-inline-block mb-1"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$o['id_order'] ?>"><input type="hidden" name="action" value="pay_fail"><button class="btn btn-outline-danger btn-xs" type="submit" onclick="return confirm('Tandai pembayaran gagal?')">Gagal</button></form>
                  <?php endif; ?>
                <?php else: ?><small class="text-muted">Pesanan dibatalkan</small><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
