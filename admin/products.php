<?php

require __DIR__ . '/../includes/bootstrap.php';
require_admin();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $act = $_POST['action'] ?? '';
    $idProduk = (int)($_POST['id'] ?? 0);

    if ($act === 'create') {
        $nama = trim((string)($_POST['nama_produk'] ?? ''));
        $brand = (int)($_POST['id_brand'] ?? 0);
        $kategori = (int)($_POST['id_kategori'] ?? 0);
        $desk = trim((string)($_POST['deskripsi'] ?? ''));
        $gambar = trim((string)($_POST['gambar'] ?? ''));
        $konsentrasi = trim((string)($_POST['konsentrasi'] ?? ''));
        $sku = trim((string)($_POST['sku'] ?? ''));
        $ukuran = (int)($_POST['ukuran_ml'] ?? 0);
        $harga = (float)($_POST['harga'] ?? -1);
        $stok = (int)($_POST['stok'] ?? -1);

        if (
            $nama === '' || mb_strlen($nama) > 150 || $brand < 1 || $kategori < 1 ||
            $sku === '' || mb_strlen($sku) > 50 || $ukuran < 1 ||
            $harga < 0 || $harga > 9999999999 || $stok < 0 || $stok > 100000
        ) {
            flash('err', 'Data produk belum valid. Periksa nama, brand, kategori, SKU, ukuran, harga, dan stok.');
        } else {
            try {
                $pdo->beginTransaction();

                $st = $pdo->prepare("INSERT INTO produk (id_brand, id_kategori, nama_produk, deskripsi, gambar, konsentrasi, status_produk) VALUES (?, ?, ?, ?, ?, ?, 'aktif')");
                $st->execute([
                    $brand,
                    $kategori,
                    $nama,
                    $desk,
                    $gambar !== '' ? $gambar : null,
                    $konsentrasi !== '' ? $konsentrasi : null
                ]);

                $idProdukBaru = (int)$pdo->lastInsertId();
                $st = $pdo->prepare("INSERT INTO produk_variants (id_produk, sku, ukuran_ml, harga, stok, status_variant) VALUES (?, ?, ?, ?, ?, 'aktif')");
                $st->execute([$idProdukBaru, $sku, $ukuran, $harga, $stok]);

                $pdo->commit();
                flash('ok', 'Produk baru berhasil ditambahkan.');
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                if ($e instanceof PDOException && (string)$e->getCode() === '23000') {
                    flash('err', 'SKU sudah digunakan atau brand/kategori tidak valid. Gunakan SKU yang berbeda.');
                } else {
                    throw $e;
                }
            }
        }

    } elseif ($act === 'delete') {
        $cek = $pdo->prepare('SELECT COUNT(*) FROM produk_variants v LEFT JOIN cart_items ci ON ci.id_variant = v.id_variant LEFT JOIN order_items oi ON oi.id_variant = v.id_variant WHERE v.id_produk = ? AND (ci.id_cart_item IS NOT NULL OR oi.id_order_item IS NOT NULL)');
        $cek->execute([$idProduk]);

        if ($idProduk < 1) {
            flash('err', 'Produk tidak valid.');
        } elseif ((int)$cek->fetchColumn() > 0) {
            flash('err', 'Produk tidak bisa dihapus karena variannya sudah digunakan di keranjang atau riwayat pesanan. Nonaktifkan produk jika ingin menyembunyikannya.');
        } else {
            $pdo->prepare('DELETE FROM produk WHERE id_produk = ?')->execute([$idProduk]);
            flash('ok', 'Produk berhasil dihapus.');
        }

    } elseif ($act === 'variant') {
        $sku = trim((string)($_POST['sku'] ?? ''));
        $harga = (float)($_POST['harga'] ?? -1);
        $stok = (int)($_POST['stok'] ?? -1);
        $status = ($_POST['status_variant'] ?? '') === 'nonaktif' ? 'nonaktif' : 'aktif';

        if ($sku === '' || mb_strlen($sku) > 50 || $harga < 0 || $harga > 9999999999 || $stok < 0 || $stok > 100000) {
            flash('err', 'SKU, harga, atau stok tidak valid.');
        } else {
            try {
                $pdo->prepare('UPDATE produk_variants SET sku = ?, harga = ?, stok = ?, status_variant = ? WHERE id_variant = ?')
                    ->execute([$sku, $harga, $stok, $status, (int)($_POST['id'] ?? 0)]);
                flash('ok', 'SKU, harga, stok, dan status varian berhasil diperbarui.');
            } catch (PDOException $e) {
                if ((string)$e->getCode() === '23000') {
                    flash('err', 'SKU tersebut sudah digunakan oleh varian lain.');
                } else {
                    throw $e;
                }
            }
        }

    } elseif ($act === 'produk') {
        $nama = trim((string)($_POST['nama_produk'] ?? ''));
        $desk = trim((string)($_POST['deskripsi'] ?? ''));
        $status = ($_POST['status_produk'] ?? '') === 'nonaktif' ? 'nonaktif' : 'aktif';

        if ($nama === '' || mb_strlen($nama) > 150) {
            flash('err', 'Nama parfum tidak valid.');
        } else {
            $pdo->prepare('UPDATE produk SET nama_produk = ?, deskripsi = ?, status_produk = ? WHERE id_produk = ?')
                ->execute([$nama, $desk, $status, $idProduk]);
            flash('ok', 'Nama, deskripsi, dan status parfum berhasil diperbarui.');
        }
    }

    redirect(url('admin/products.php#p' . (int)($_POST['pid'] ?? $idProduk)));
}

$prods = $pdo->query('SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON k.id_kategori = p.id_kategori ORDER BY p.id_produk')->fetchAll();
$brands = $pdo->query('SELECT id_brand, nama_brand FROM brand ORDER BY nama_brand')->fetchAll();
$categories = $pdo->query('SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll();

$vars = [];
foreach ($pdo->query('SELECT * FROM produk_variants ORDER BY id_produk, ukuran_ml') as $v) {
    $vars[$v['id_produk']][] = $v;
}

$summary = $pdo->query("SELECT COUNT(*) AS total, SUM(status_produk = 'aktif') AS aktif FROM produk")->fetch();
$stock = $pdo->query("SELECT COALESCE(SUM(stok), 0) FROM produk_variants WHERE status_variant = 'aktif'")->fetchColumn();

$title = 'Produk & Stok';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-boxes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Produk</span>
                <span class="info-box-number"><?= (int)$summary['total'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-eye"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Produk Aktif</span>
                <span class="info-box-number"><?= (int)$summary['aktif'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-cubes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Stok Varian Aktif</span>
                <span class="info-box-number"><?= (int)$stock ?> unit</span>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i>
    Nama parfum, deskripsi, SKU, harga, dan stok yang ditampilkan di halaman ini diambil dari database ATEA Parfums.
</div>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-plus mr-1"></i> Tambah Produk</h3>
    </div>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nama parfum</label>
                    <input type="text" name="nama_produk" class="form-control" maxlength="150" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Brand</label>
                    <select name="id_brand" class="form-control" required>
                        <option value="">Pilih brand</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= (int)$b['id_brand'] ?>"><?= h($b['nama_brand']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Kategori</label>
                    <select name="id_kategori" class="form-control" required>
                        <option value="">Pilih kategori</option>
                        <?php foreach ($categories as $k): ?>
                            <option value="<?= (int)$k['id_kategori'] ?>"><?= h($k['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Deskripsi parfum</label>
                <textarea name="deskripsi" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Gambar (path file, opsional)</label>
                    <input type="text" name="gambar" class="form-control" placeholder="assets/nama-gambar.png">
                </div>
                <div class="form-group col-md-4">
                    <label>Konsentrasi (opsional)</label>
                    <input type="text" name="konsentrasi" class="form-control" placeholder="Eau De Parfum">
                </div>
                <div class="form-group col-md-4">
                    <label>SKU varian</label>
                    <input type="text" name="sku" class="form-control" maxlength="50" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Ukuran (ml)</label>
                    <input type="number" name="ukuran_ml" class="form-control" min="1" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" class="form-control" min="0" step="1000" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Stok</label>
                    <input type="number" name="stok" class="form-control" min="0" max="100000" required>
                </div>
            </div>
            <small class="text-muted">Satu varian (ukuran, SKU, harga, dan stok) akan dibuat bersama produk.</small>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambahkan Produk</button>
        </div>
    </form>
</div>

<?php foreach ($prods as $p): ?>
    <div class="card product-card" id="p<?= (int)$p['id_produk'] ?>">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><?= h($p['nama_produk']) ?></h3>
            <div class="card-tools">
                <span class="badge badge-light"><?= h($p['nama_kategori']) ?></span>
                <span class="badge badge-<?= $p['status_produk'] === 'aktif' ? 'success' : 'secondary' ?>">
                    <?= $p['status_produk'] === 'aktif' ? 'Aktif' : 'Nonaktif' ?>
                </span>
                <form method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus parfum ini? Tindakan ini tidak dapat dibatalkan.');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$p['id_produk'] ?>">
                    <input type="hidden" name="pid" value="<?= (int)$p['id_produk'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger ml-2"><i class="fas fa-trash mr-1"></i>Hapus</button>
                </form>
            </div>
        </div>

        <div class="card-body">
            <form method="post" class="mb-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="produk">
                <input type="hidden" name="id" value="<?= (int)$p['id_produk'] ?>">
                <input type="hidden" name="pid" value="<?= (int)$p['id_produk'] ?>">

                <div class="form-group">
                    <label>Nama parfum</label>
                    <input type="text" name="nama_produk" class="form-control" maxlength="150" value="<?= h($p['nama_produk']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi parfum</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?= h($p['deskripsi']) ?></textarea>
                </div>
                <div class="form-row align-items-end">
                    <div class="col-md-4 col-lg-3 form-group mb-md-0">
                        <label>Status produk</label>
                        <select name="status_produk" class="form-control">
                            <option value="aktif" <?= $p['status_produk'] === 'aktif' ? 'selected' : '' ?>>Aktif — tampil di website</option>
                            <option value="nonaktif" <?= $p['status_produk'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif — sembunyikan</option>
                        </select>
                    </div>
                    <div class="col-md-auto mt-2 mt-md-0">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-save mr-1"></i>Simpan informasi parfum</button>
                    </div>
                </div>
            </form>

            <h6 class="font-weight-bold mb-2"><i class="fas fa-tags mr-1"></i> Varian, SKU, Harga & Stok</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="min-width:170px">SKU</th>
                            <th>Ukuran</th>
                            <th style="min-width:150px">Harga (Rp)</th>
                            <th style="min-width:100px">Stok</th>
                            <th style="min-width:130px">Status Varian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vars[$p['id_produk']] ?? [] as $v): ?>
                            <tr>
                                <td>
                                    <form id="vf<?= (int)$v['id_variant'] ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="variant">
                                        <input type="hidden" name="id" value="<?= (int)$v['id_variant'] ?>">
                                        <input type="hidden" name="pid" value="<?= (int)$p['id_produk'] ?>">
                                    </form>
                                    <input form="vf<?= (int)$v['id_variant'] ?>" type="text" class="form-control form-control-sm" name="sku" value="<?= h($v['sku']) ?>" maxlength="50" required>
                                </td>
                                <td class="align-middle"><?= (int)$v['ukuran_ml'] ?> ml</td>
                                <td>
                                    <input form="vf<?= (int)$v['id_variant'] ?>" type="number" class="form-control form-control-sm" name="harga" min="0" max="9999999999" step="1000" value="<?= (int)$v['harga'] ?>" required>
                                </td>
                                <td>
                                    <input form="vf<?= (int)$v['id_variant'] ?>" type="number" class="form-control form-control-sm" name="stok" min="0" max="100000" value="<?= (int)$v['stok'] ?>" required>
                                </td>
                                <td>
                                    <select form="vf<?= (int)$v['id_variant'] ?>" name="status_variant" class="form-control form-control-sm">
                                        <option value="aktif" <?= $v['status_variant'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="nonaktif" <?= $v['status_variant'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                    </select>
                                </td>
                                <td class="align-middle">
                                    <button form="vf<?= (int)$v['id_variant'] ?>" class="btn btn-sm btn-outline-primary" type="submit"><i class="fas fa-save mr-1"></i>Simpan</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
