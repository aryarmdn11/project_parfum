<?php
require __DIR__ . '/includes/bootstrap.php';

try {
    $count = (int) db()->query("SELECT COUNT(*) FROM `user` WHERE role = 'admin'")->fetchColumn();
} catch (Throwable $e) {
    http_response_code(500);
    exit('Database belum siap. Pastikan MySQL aktif dan database atea_parfums sudah di-import melalui phpMyAdmin. Detail: ' . h($e->getMessage()));
}

$error = '';
$done = false;
if ($count > 0) {
    $done = true;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $nama = trim($_POST['nama'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Isi nama dan email yang valid. Password minimal 8 karakter.';
    } else {
        $st = db()->prepare("INSERT INTO `user` (nama, email, password, role) VALUES (?, ?, ?, 'admin')");
        try {
            $st->execute([$nama, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $done = true;
        } catch (PDOException $e) {
            $error = $e->getCode() === '23000' ? 'Email tersebut sudah terdaftar.' : 'Gagal membuat admin: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Setup Admin - ATEA Parfums</title>
<style>
body{font:16px Arial,sans-serif;background:#f4f6f9;display:grid;place-items:center;min-height:100vh;margin:0}
main{background:#fff;padding:30px;border-radius:12px;width:min(420px,calc(100% - 40px));box-shadow:0 4px 20px #0001}
h2{margin-top:0}label{display:block;margin:14px 0 6px}input{box-sizing:border-box;width:100%;padding:11px;border:1px solid #ccc;border-radius:6px}
button,a.btn{display:inline-block;margin-top:18px;padding:11px 16px;background:#222;color:white;border:0;border-radius:6px;text-decoration:none;cursor:pointer}
.error{background:#ffe5e5;color:#a00;padding:10px;border-radius:6px}
small{color:#666}
</style></head><body><main>
<h2>ATEA PARFUMS</h2><p>Pengaturan akun administrator</p>
<?php if ($done): ?>
<p><?= $count > 0 ? 'Akun admin sudah tersedia. Setup admin dikunci demi keamanan.' : 'Akun administrator berhasil dibuat.' ?></p>
<a class="btn" href="<?= h(url('admin/login.php')) ?>">Ke Login Admin</a>
<p><small>Hapus file setup_admin.php dari folder project setelah selesai.</small></p>
<?php else: ?>
<?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>
<form method="post"><?= csrf_field() ?>
<label>Nama administrator</label><input name="nama" required maxlength="100">
<label>Email</label><input type="email" name="email" required maxlength="150">
<label>Password (minimal 8 karakter)</label><input type="password" name="password" required minlength="8">
<button type="submit">Buat Akun Admin</button>
</form>
<?php endif; ?>
</main></body></html>
