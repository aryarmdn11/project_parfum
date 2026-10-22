<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/catalog.php';
$page = 'home';
$title = 'ATEA Parfums — Discover Your Scent';
$withCatalog = true;
// ===== DATA ANGGOTA KELOMPOK =====


$team = [
    [
        'name' => 'Arya Ramadhan',
        'school' => [
            'SD Negeri Kaong',
            'SMP 2 KD',
            'Inka Boarding School',
            'Universitas Cendekia Abditama'
        ],
        'quotes' => 'Jangan bilang tidak mungkin kepadaku sebelum kamu mati mencobanya.',
        'photo' => 'assets/team/anggota1.jpeg'
    ],


    [
        'name' => 'Evan Havillah Dwika Putra',
        'school' => [
          'SD Bina Nusa',
          'MTS Al-Hafizh',
          'SMK Negri 5 Kabupaten Tangerang',
          'Universitas Cendekia Abditama'
        ],
        'quotes' => 'Percayalah pada kemampuanmu untuk mengatasi kesulitan.',
        'photo' => 'assets/team/anggota2.jpeg'
    ],
    
    [
        'name' => 'Andika Syafulloh Yusuf',
        'school' => [
          'SDN Kutabumi',
          'SMPN 2 Pasarkemis', 
          'SMKN 2 Kab Tangerang',
          'Universitas Cendekia Abditama'
        ],
        'quotes' => 'Saat kamu tahu seberapa berharganya dirimu, dunia tidak punya pilihan selain menghormatimu.',
        'photo' => 'assets/team/anggota3.jpeg'
    ],
    [
        'name' => 'Tegar Maulida Dias',
        'school' => [
          'SDN PB Kelapa Dua 1',
          'MTS Al-Amin',
          'MA Al-Amin',
          'Universitas Cendekia Abditama'
        ],
        'quotes' => 'Jangan MUNDUR, Allah gak TIDUR.',
        'photo' => 'assets/team/anggota4.jpeg'
    ],
    
];

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="home-hero">
      <div class="home-hero-copy">
        <p class="eyebrow light">THE ART OF PERSONAL SCENT</p>
        <h1>Find the scent<br><em>that feels like you.</em></h1>
        <p>Lebih dari sekadar aroma. Sebuah detail kecil yang meninggalkan kesan, bahkan setelah kamu pergi.</p>
        <a class="solid-btn" href="<?= h(url('lineup.php')) ?>">Discover our Collections</a>
      </div>
      <div class="home-hero-visual">
        <div class="arch"><img src="<?= h(url('assets/orlune-box-set.png')) ?>" alt="ATEA Parfums Orlune, botol dan rigid box"></div>
        <span class="hero-note note-top">EXTRAIT DE PARFUM</span>
      </div>
    </section>

    <section class="intro-strip">
      <p>SCENT IS A FORM OF SELF-EXPRESSION</p>
      <span>Dirancang untuk menemani setiap versi dirimu.</span>
    </section>

    <section class="section-wrap">
      <div class="section-heading">
        <div><p class="eyebrow">PHOTO AND PROFILES</p><h2>Of the <em>Founders</em></h2></div>
        <a href="<?= h(url('lineup.php')) ?>" class="text-link">VIEW FULL LINEUP <span>↗</span></a>
      </div>
      <div class="product-grid team-grid">
  <?php foreach ($team as $m): ?>
    <article class="product-card team-card">
      <div class="product-image">
        <img src="<?= h(url($m['photo'])) ?>" alt="Foto <?= h($m['name']) ?>" loading="lazy">
      </div>
      <div class="product-meta">
        <div>
          <h3 class="product-name"><?= h($m['name']) ?></h3>
          <span class="product-type">ASAL SEKOLAH</span>
        </div>
      </div>
      

<ul class="team-schools">
    <?php foreach ($m['school'] as $school): ?>
        <li><?= h($school) ?></li>
    <?php endforeach; ?>
</ul>


<div class="team-quotes">
    <span class="product-type">QUOTES</span>
    <p><?= h($m['quotes']) ?></p>
</div>

    </article>
  <?php endforeach; ?>
</div>
    </section>

    <section class="brand-story">
      <div class="story-mark"><img src="<?= h(url('assets/atea-mark.png')) ?>" alt=""></div>
      <div class="story-copy"><p class="eyebrow">A NOTE ABOUT ATEA</p><h2>Make it<br><em>unforgettable.</em></h2><p>ATEA Parfums percaya bahwa aroma punya cara tersendiri untuk bercerita. Koleksi kami dibuat sebagai ruang untuk menemukan karakter, suasana, dan kesan yang ingin kamu tinggalkan.</p><a href="<?= h(url('about.php')) ?>" class="text-link">READ OUR STORY <span>↗</span></a></div>
    </section>
  </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
