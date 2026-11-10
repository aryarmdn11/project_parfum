<?php
$admin = require_admin();
$cur = basename($_SERVER['SCRIPT_NAME'] ?? '');
$navOrders = $cur === 'index.php';
$navProducts = $cur === 'products.php';
?><!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title ?? 'Dashboard') ?> | ATEA Parfums Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <style>
    .brand-link { text-align:center; }
    .brand-link .brand-text { font-weight:700; letter-spacing:2px; }
    .content-header h1 { font-size:1.55rem; font-weight:600; }
    .content-wrapper { background:#f4f6f9; }
    .small-box .icon { top:8px; }
    .table td, .table th { vertical-align:middle; }
    .product-card { border-top:3px solid #6c757d; }
    .product-card .form-control { min-width:100px; }
    .order-address { max-width:260px; white-space:normal; }
    .badge-menunggu_pembayaran { background:#ffc107; color:#212529; }
    .badge-diproses { background:#17a2b8; color:white; }
    .badge-dikirim { background:#007bff; color:white; }
    .badge-selesai { background:#28a745; color:white; }
    .badge-dibatalkan { background:#6c757d; color:white; }
    .content-wrapper { min-height:calc(100vh - 57px) !important; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="<?= h(url('index.php')) ?>" class="nav-link" target="_blank">Lihat Website <i class="fas fa-external-link-alt fa-xs ml-1"></i></a></li>
    </ul>
    <ul class="navbar-nav ml-auto align-items-center">
      <li class="nav-item mr-3 d-none d-sm-inline-block"><span class="text-muted"><i class="far fa-user-circle mr-1"></i><?= h($admin['nama']) ?></span></li>
      <li class="nav-item">
        <form method="post" action="<?= h(url('logout.php')) ?>" class="m-0">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-sign-out-alt mr-1"></i>Logout</button>
        </form>
      </li>
    </ul>
  </nav>
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= h(url('admin/index.php')) ?>" class="brand-link">
      <span class="brand-text font-weight-light">ATEA <strong>PARFUMS</strong></span>
    </a>
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image"><i class="fas fa-user-shield text-light fa-lg mt-2 ml-2"></i></div>
        <div class="info"><span class="d-block text-white"><?= h($admin['nama']) ?></span><small class="text-muted">Administrator</small></div>
      </div>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-header">MENU UTAMA</li>
          <li class="nav-item">
            <a href="<?= h(url('admin/index.php')) ?>" class="nav-link <?= $navOrders ? 'active' : '' ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard & Pesanan</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= h(url('admin/products.php')) ?>" class="nav-link <?= $navProducts ? 'active' : '' ?>">
              <i class="nav-icon fas fa-box-open"></i><p>Produk & Stok</p>
            </a>
          </li>
          <li class="nav-header">TOKO</li>
          <li class="nav-item">
            <a href="<?= h(url('index.php')) ?>" class="nav-link" target="_blank">
              <i class="nav-icon fas fa-store"></i><p>Halaman Toko <i class="fas fa-external-link-alt right"></i></p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid"><div class="row mb-2"><div class="col-sm-8"><h1><?= h($title ?? 'Dashboard') ?></h1></div><div class="col-sm-4"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="<?= h(url('admin/index.php')) ?>">Admin</a></li><li class="breadcrumb-item active"><?= h($title ?? 'Dashboard') ?></li></ol></div></div></div>
    </section>
    <section class="content"><div class="container-fluid">
      <?php foreach (flashes() as $f): ?>
        <div class="alert alert-<?= $f['t'] === 'ok' ? 'success' : ($f['t'] === 'err' ? 'danger' : 'info') ?> alert-dismissible fade show" role="alert">
          <?= h($f['m']) ?><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
      <?php endforeach; ?>
