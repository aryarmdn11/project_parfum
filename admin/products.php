<?php
require __DIR__ . '/../includes/bootstrap.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $act = $_POST['action'] ?? '';
    if ($act === 'variant') {
        $harga = (float)($_POST['harga'] ?? -1);
        $stok = (int)($_POST['stok'] ?? -1);
        $status = ($_POST['status_variant'] ?? '') === 'nonaktif' ? 'nonaktif' : 'aktif';
        if ($harga < 0 || $harga > 99999999 || $stok < 0 || $stok > 100000) flash('err', 'Harga atau stok tidak valid.');
        else {
            $pdo->prepare('UPDATE produk_variants SET harga = ?, stok = ?, status_variant = ? WHERE id_variant = ?')->execute([$harga, $stok, $status, (int)$_POST['id']]);
            flash('ok', 'Varian diperbarui.');
        }
    } elseif ($act === 'produk') {
        $desk = trim((string)($_POST['deskripsi'] ?? ''));
        $status = ($_POST['status_produk'] ?? '') === 'nonaktif' ? 'nonaktif' : 'aktif';
        $pdo->prepare('UPDATE produk SET deskripsi = ?, status_produk = ? WHERE id_produk = ?')->execute([$desk, $status, (int)$_POST['id']]);
        flash('ok', 'Produk diperbarui.');
    }
    redirect(url('admin/products.php#p' . (int)($_POST['pid'] ?? 0)));
}

$prods = $pdo->query('SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON k.id_kategori = p.id_kategori ORDER BY p.id_produk')->fetchAll();
$vars = [];
foreach ($pdo->query('SELECT * FROM produk_variants ORDER BY id_produk, ukuran_ml') as $v) $vars[$v['id_produk']][] = $v;
$title = 'Produk & Stok';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Produk &amp; Stok</h1>
<p class="muted">Ubah deskripsi, harga, stok, dan status tampil produk. Perubahan langsung muncul di website.</p>
<?php foreach ($prods as $p): ?>
<div class="panel" id="p<?= (int)$p['id_produk'] ?>">
  <form method="post" class="prod-form"><?= csrf_field() ?><input type="hidden" name="action" value="produk"><input type="hidden" name="id" value="<?= (int)$p['id_produk'] ?>"><input type="hidden" name="pid" value="<?= (int)$p['id_produk'] ?>">
    <h2><?= h($p['nama_produk']) ?> <small class="muted"><?= h($p['konsentrasi']) ?> · <?= h($p['nama_kategori']) ?></small></h2>
    <textarea name="deskripsi" rows="2"><?= h($p['deskripsi']) ?></textarea>
    <div class="inline-form">
      <select name="status_produk"><option value="aktif" <?= $p['status_produk'] === 'aktif' ? 'selected' : '' ?>>Tampil di website</option><option value="nonaktif" <?= $p['status_produk'] === 'nonaktif' ? 'selected' : '' ?>>Sembunyikan</option></select>
      <button class="btn-sm" type="submit">Simpan produk</button>
    </div>
  </form>
  <table class="tbl">
    <tr><th>SKU</th><th>Ukuran</th><th>Harga (Rp)</th><th>Stok</th><th>Status</th><th></th></tr>
    <?php foreach ($vars[$p['id_produk']] ?? [] as $v): ?>
    <tr>
      <td><?= h($v['sku']) ?></td><td><?= (int)$v['ukuran_ml'] ?> ml</td>
      <td><input form="vf<?= (int)$v['id_variant'] ?>" type="number" name="harga" min="0" step="1000" value="<?= (int)$v['harga'] ?>"></td>
      <td><input form="vf<?= (int)$v['id_variant'] ?>" type="number" name="stok" min="0" value="<?= (int)$v['stok'] ?>"></td>
      <td><select form="vf<?= (int)$v['id_variant'] ?>" name="status_variant"><option value="aktif" <?= $v['status_variant'] === 'aktif' ? 'selected' : '' ?>>Aktif</option><option value="nonaktif" <?= $v['status_variant'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option></select></td>
      <td><form id="vf<?= (int)$v['id_variant'] ?>" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="variant"><input type="hidden" name="id" value="<?= (int)$v['id_variant'] ?>"><input type="hidden" name="pid" value="<?= (int)$p['id_produk'] ?>"><button class="btn-sm" type="submit">Simpan</button></form></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endforeach; ?>
</main></body></html>
