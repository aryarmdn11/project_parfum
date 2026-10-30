
<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/catalog.php';

$page = 'about';
$title = 'About Us – ATEA Parfums';

require __DIR__ . '/includes/header.php';
?>

<main class="page-about">

    <!-- HERO -->
    <section class="about-hero">
        <p class="eyebrow">WELCOME TO ATEA PARFUMS</p>

        <h1>Discover Your Signature Scent</h1>

        <p class="hero-description">
            Temukan aroma yang menggambarkan karakter dan ceritamu.
            Setiap wewangian memiliki keunikan untuk menemani
            setiap momen berharga.
        </p>

       <div class="about-hero-image">
    <div class="about-hero-bg"></div>
    <img src="<?= h(url('assets/all q.jpg')) ?>" alt="ATEA Parfums">
</div>
    </section>

    <!-- OUR STORY: TEKS KIRI, GAMBAR KANAN -->
    <section class="about-row">
        <div class="about-row-text">
            <p class="eyebrow">OUR STORY</p>
            <h2 style="color: #0F2C59 !important;">More Than a Fragrance</h2>

            <p>
                ATEA Parfums hadir dengan gagasan bahwa parfum bukan
                sekadar wewangian, tetapi juga bagian dari identitas
                dan cara seseorang mengekspresikan diri. Kami ingin
                menghadirkan aroma yang berkesan dan menemani berbagai
                momen kehidupan.
            </p>
        </div>

        <div class="about-row-image">
            <img
               <img src="<?= h(url('assets/Atea Blue.png')) ?>" alt="ATEA Blue">    
                alt="Koleksi parfum ATEA"
            >
        </div>
    </section>

    <!-- OUR PHILOSOPHY: GAMBAR KIRI, TEKS KANAN -->
    <section class="about-row reverse">
        <div class="about-row-text">
            <p class="eyebrow">OUR PHILOSOPHY</p>
            <h2 style="color: #710014 !important;">A Scent That Defines You</h2>

            <p>
                Setiap orang memiliki karakter dan cerita yang berbeda.
                Karena itu, kami percaya bahwa aroma yang tepat dapat
                menjadi bagian dari ekspresi diri serta memberikan
                rasa nyaman dan percaya diri.
            </p>
        </div>

        <div class="about-row-image">
            <img
                <img src="<?= h(url('assets/Atea Red.png')) ?>" alt="ATEA Red">
                alt="Orlune perfume"
            >
        </div>
    </section>

    <!-- OUR COMMITMENT: TEKS KIRI, GAMBAR KANAN -->
    <section class="about-row">
        <div class="about-row-text">
            <p class="eyebrow">OUR COMMITMENT</p>
            <h2 style="color: #8A6BBE !important;">Made for Every Moment</h2>
            <p>
                Dari aktivitas sehari-hari hingga momen spesial,
                ATEA ingin menjadi bagian dari perjalananmu.
                Kami terus berupaya mengembangkan koleksi aroma
                yang menarik untuk berbagai kepribadian.
            </p>

            <a class="outline-link" href="<?= h(url('lineup.php')) ?>">
                EXPLORE OUR COLLECTION
            </a>
        </div>

        <div class="about-row-image">
            <img
                src="<?= h(url('assets/Purple.jpg')) ?>"
                alt="ATEA Parfums collection"
            >
        </div>
    </section>

    <!-- ENDING -->
    <section class="about-ending">
        <img
            src="<?= h(url('assets/atea-mark.png')) ?>"
            alt="ATEA Parfums"
        >

        <h2>Find Your Signature Scent</h2>
        <p>Your scent, your story, your identity.</p>

        <a class="outline-link" href="<?= h(url('lineup.php')) ?>">
            DISCOVER ATEA
        </a>
    </section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
```