
<script>
document.addEventListener('DOMContentLoaded', function () {
    const password = document.getElementById('password');
    const toggle = document.getElementById('togglePassword');

    if (!password || !toggle) return;

    toggle.addEventListener('click', function () {
        const tampilkan = password.type === 'password';

        password.type = tampilkan ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(tampilkan));
        toggle.setAttribute(
            'aria-label',
            tampilkan ? 'Sembunyikan password' : 'Tampilkan password'
        );
    });
});
</script>

<?php
require __DIR__ . '/includes/bootstrap.php';
$next = $_GET['next'] ?? $_POST['next'] ?? '';
if (current_user()) redirect(safe_next($next));

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');
    $fails = $_SESSION['login_fail'] ?? ['n' => 0, 't' => time()];
    if (time() - $fails['t'] > 300) $fails = ['n' => 0, 't' => time()];
    if ($fails['n'] >= 5) {
        $error = 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.';
    } else {
        $st = db()->prepare('SELECT id_user, password FROM `user` WHERE email = ?');
        $st->execute([$email]);
        $row = $st->fetch();
        $hash = $row['password'] ?? '$2y$10$usesomesillystringforsalt.invalidinvalidinvalidinvalidinv';
        if ($row && password_verify($pass, $hash)) {
            unset($_SESSION['login_fail']);
            login_user((int)$row['id_user']);
            redirect(safe_next($next));
        }
        $fails['n']++; $_SESSION['login_fail'] = $fails;
        $error = 'Email atau password salah.';
    }
}
$page = 'akun'; $title = 'Login — ATEA Parfums';
require __DIR__ . '/includes/header.php';
?>
<main class="auth-wrap">
  <form class="auth-card" method="post" novalidate>
    <p class="eyebrow">WELCOME BACK</p>
    <h1>Login</h1>
    <?php if ($error): ?><div class="form-error"><?= h($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= h($next) ?>">
    <label>Email<input type="email" name="email" required autocomplete="email" value="<?= h($_POST['email'] ?? '') ?>"></label>
    

<label for="password">Password</label>
<div class="password-wrapper">
    <input
        type="password"
        name="password"
        id="password"
        required
        autocomplete="current-password"
    >

    <button
        type="button"
        class="toggle-password"
        id="togglePassword"
        aria-label="Tampilkan password"
        aria-pressed="false"
    >
        <svg xmlns="http://www.w3.org/2000/svg"
             width="19" height="19"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="1.7"
             stroke-linecap="round"
             stroke-linejoin="round">
            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>
    </button>
</div>


    <button class="solid-btn dark" type="submit">LOGIN</button>
    <p class="auth-alt">Belum punya akun? <a href="<?= h(url('register.php' . ($next ? '?next=' . rawurlencode($next) : ''))) ?>">Daftar</a></p>
  </form>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
