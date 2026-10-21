<?php
// Variabel yang dipakai: $page (home|lineup|about|akun), $title
$user = current_user();
$navItems = [['home', 'index.php', 'Home'], ['lineup', 'lineup.php', 'Lineup'], ['about', 'about.php', 'About Us']];
?><!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#710014">
  <meta name="description" content="ATEA Parfums, temukan wewangian yang terasa seperti milikmu.">
  <title><?= h($title ?? 'ATEA Parfums') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Italiana&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= h(url('style.css')) ?>">
</head>
<body data-page="<?= h($page ?? '') ?>" id="top">
<div class="announcement">A scent that stays with you. <span>— ATEA PARFUMS —</span></div>
<header class="site-header">
  <button class="mobile-menu" id="mobileMenu" aria-label="Buka menu"><span></span><span></span></button>
  <nav class="nav-left" id="navLeft">
    <?php foreach ($navItems as [$k, $href, $label]): ?>
      <a href="<?= h(url($href)) ?>" class="<?= ($page ?? '') === $k ? 'active' : '' ?>"><?= h($label) ?></a>
    <?php endforeach; ?>
    <span class="nav-m-sep"></span>
    <?php if ($user): ?>
      <a class="nav-m" href="<?= h(url('orders.php')) ?>">Pesanan Saya</a>
      <?php if ($user['role'] === 'admin'): ?><a class="nav-m" href="<?= h(url('admin/index.php')) ?>">Panel Admin</a><?php endif; ?>
      <form class="nav-m" method="post" action="<?= h(url('logout.php')) ?>"><?= csrf_field() ?><button type="submit">Logout</button></form>
    <?php else: ?>
      <a class="nav-m" href="<?= h(url('login.php')) ?>">Login</a>
      <a class="nav-m" href="<?= h(url('register.php')) ?>">Daftar</a>
    <?php endif; ?>
  </nav>
  <a class="brand" href="<?= h(url('index.php')) ?>" aria-label="ATEA Parfums home"><img src="<?= h(url('assets/atea-logo.png')) ?>" alt="ATEA Parfums"></a>
  <div class="nav-actions">
    <button class="icon-btn" id="searchToggle" aria-label="Cari produk" title="Cari"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="7.4"></circle><path d="m16.2 16.2 5 5"></path></svg></button>
    <div class="account-wrap">
      <?php if ($user): ?>
        <button class="icon-btn" id="accountBtn" aria-label="Akun" title="<?= h($user['nama']) ?>" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"></path></svg></button>
        <div class="account-menu" id="accountMenu">
          <p>Halo, <b><?= h(strtok($user['nama'], ' ')) ?></b></p>
          <a href="<?= h(url('orders.php')) ?>">Pesanan Saya</a>
          <?php if ($user['role'] === 'admin'): ?><a href="<?= h(url('admin/index.php')) ?>">Panel Admin</a><?php endif; ?>
          <form method="post" action="<?= h(url('logout.php')) ?>"><?= csrf_field() ?><button type="submit">Logout</button></form>
        </div>
      <?php else: ?>
        <a class="icon-btn" href="<?= h(url('login.php')) ?>" aria-label="Login" title="Login"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"></path></svg></a>
      <?php endif; ?>
    </div>
    <button class="icon-btn bag-button" id="bagButton" aria-label="Buka keranjang" title="Keranjang"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h16l-1.2 12H5.2L4 8Z"></path><path d="M8 9V6a4 4 0 0 1 8 0v3"></path></svg><span id="bagCount">0</span></button>
  </div>
  <form class="search-panel" id="searchPanel" role="search"><input id="searchInput" type="search" placeholder="Cari nama parfum..." aria-label="Cari nama parfum"><button type="button" id="closeSearch" aria-label="Tutup pencarian">×</button></form>
</header>
<?php foreach (flashes() as $f): ?>
  <div class="flash flash-<?= h($f['t']) ?>"><?= h($f['m']) ?></div>
<?php endforeach; ?>
