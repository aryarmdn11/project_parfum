<?php
// Semua akun menggunakan satu halaman login bersama.
require __DIR__ . '/../includes/bootstrap.php';
redirect(url('login.php?next=' . rawurlencode('admin/index.php')));
