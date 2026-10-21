<?php
$admin = require_admin();
$cur = basename($_SERVER['SCRIPT_NAME']);
?><!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($title ?? 'Admin') ?> — ATEA Admin</title>
<link rel="stylesheet" href="<?= h(url('style.css')) ?>">
</head><body class="admin-body">
<header class="admin-bar">
  <a class="admin-brand" href="<?= h(url('admin/index.php')) ?>">ATEA <small>ADMIN</small></a>
  <nav>
    <a href="<?= h(url('admin/index.php')) ?>" class="<?= $cur === 'index.php' ? 'active' : '' ?>">Pesanan</a>
    <a href="<?= h(url('admin/products.php')) ?>" class="<?= $cur === 'products.php' ? 'active' : '' ?>">Produk &amp; Stok</a>
    <a href="<?= h(url('index.php')) ?>">Lihat website ↗</a>
  </nav>
  <form method="post" action="<?= h(url('logout.php')) ?>"><?= csrf_field() ?><button type="submit" class="link-btn light">Logout (<?= h($admin['nama']) ?>)</button></form>
</header>
<main class="admin-main">
<?php foreach (flashes() as $f): ?><div class="flash flash-<?= h($f['t']) ?> in-admin"><?= h($f['m']) ?></div><?php endforeach; ?>
