<?php
require __DIR__ . '/../includes/bootstrap.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $pid = (int)($_GET['produk'] ?? 0);
    $st = $pdo->prepare('SELECT r.rating, r.komentar, r.tanggal_review, u.nama FROM reviews r JOIN `user` u ON u.id_user = r.id_user
                         WHERE r.id_produk = ? ORDER BY r.tanggal_review DESC, r.id_review DESC LIMIT 20');
    $st->execute([$pid]);
    $uid = current_user()['id_user'] ?? null;
    $mine = null;
    if ($uid) {
        $m = $pdo->prepare('SELECT rating, komentar FROM reviews WHERE id_user = ? AND id_produk = ?');
        $m->execute([$uid, $pid]);
        $mine = $m->fetch() ?: null;
    }
    $list = array_map(fn($r) => [
        'name' => mb_substr(trim(strtok($r['nama'], ' ')), 0, 20), 'rating' => (int)$r['rating'],
        'text' => (string)$r['komentar'], 'date' => substr($r['tanggal_review'], 0, 10),
    ], $st->fetchAll());
    json_out(['reviews' => $list, 'mine' => $mine]);
}

$user = current_user();
if (!$user) json_out(['error' => 'Login dulu untuk menulis review.'], 401);
if (!csrf_ok($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) json_out(['error' => 'Sesi tidak valid, muat ulang halaman.'], 419);
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$pid = (int)($in['produk'] ?? 0);
$rating = (int)($in['rating'] ?? 0);
$komentar = trim((string)($in['komentar'] ?? ''));
if ($rating < 1 || $rating > 5) json_out(['error' => 'Rating harus 1 sampai 5.'], 422);
if (mb_strlen($komentar) > 1000) json_out(['error' => 'Komentar maksimal 1000 karakter.'], 422);
$ex = $pdo->prepare('SELECT 1 FROM produk WHERE id_produk = ?');
$ex->execute([$pid]);
if (!$ex->fetchColumn()) json_out(['error' => 'Produk tidak ditemukan.'], 404);

$uid = (int)$user['id_user'];
$old = $pdo->prepare('SELECT id_review FROM reviews WHERE id_user = ? AND id_produk = ?');
$old->execute([$uid, $pid]);
if ($rid = $old->fetchColumn()) {      // satu review per user per produk -> update
    $pdo->prepare('UPDATE reviews SET rating = ?, komentar = ?, tanggal_review = ? WHERE id_review = ?')->execute([$rating, $komentar, now(), $rid]);
} else {
    $pdo->prepare('INSERT INTO reviews (id_user, id_produk, rating, komentar, tanggal_review) VALUES (?, ?, ?, ?, ?)')->execute([$uid, $pid, $rating, $komentar, now()]);
}
json_out(['ok' => true]);
