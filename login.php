<?php
require __DIR__ . '/includes/bootstrap.php';

$next = $_GET['next'] ?? $_POST['next'] ?? '';
$loggedInUser = current_user();
if ($loggedInUser) {
    login_redirect_for($loggedInUser, $next);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');

    $fails = $_SESSION['login_fail'] ?? ['n' => 0, 't' => time()];
    if (time() - $fails['t'] > 300) {
        $fails = ['n' => 0, 't' => time()];
    }

    if ($fails['n'] >= 5) {
        $error = 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.';
    } else {
        $st = db()->prepare('SELECT id_user, password, role FROM `user` WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $account = $st->fetch();

        if ($account && password_verify($pass, $account['password'])) {
            unset($_SESSION['login_fail']);
            login_user((int)$account['id_user']);
            $st = db()->prepare('SELECT id_user, nama, email, no_telepon, role FROM `user` WHERE id_user = ?');
            $st->execute([(int)$account['id_user']]);
            $user = $st->fetch();
            if ($user) {
                login_redirect_for($user, $next);
            }
        }

        $fails['n']++;
        $_SESSION['login_fail'] = $fails;
        $error = 'Email atau password salah.';
    }
}

$page = 'akun';
$title = 'Login — ATEA Parfums';
require __DIR__ . '/includes/header.php';
?>
<main class="auth-wrap">
  <form class="auth-card" method="post" novalidate>
    <p class="eyebrow">WELCOME BACK</p>
    <h1>Login</h1>
    <p>Masuk menggunakan akun Customer atau Admin. Sistem akan mengarahkanmu secara otomatis.</p>
    <?php if ($error): ?><div class="form-error"><?= h($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= h($next) ?>">
    <label>Email<input type="email" name="email" required autocomplete="username" value="<?= h($_POST['email'] ?? '') ?>"></label>
    <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
    <button class="solid-btn dark" type="submit">LOGIN</button>
    <p class="auth-alt">Belum punya akun? <a href="<?= h(url('register.php' . ($next ? '?next=' . rawurlencode($next) : ''))) ?>">Daftar</a></p>
  </form>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
