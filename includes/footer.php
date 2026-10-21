<?php
// Footer + modal produk + drawer keranjang + konfigurasi untuk script.js
// $withCatalog = true untuk halaman yang menampilkan produk
$user = current_user();
$page = $page ?? '';
$boot = [
    'base' => BASE,
    'csrf' => csrf_token(),
    'user' => $user ? ['name' => $user['nama'], 'role' => $user['role']] : null,
    'loginUrl' => url('login.php?next=' . rawurlencode(basename($_SERVER['SCRIPT_NAME']))),
    'checkoutUrl' => url('checkout.php'),
    'lineupUrl' => url('lineup.php'),
    'products' => !empty($withCatalog) ? load_catalog() : [],
];
?>
<footer class="site-footer">
  <div class="footer-main">
    <div class="footer-brand"><img src="<?= h(url('assets/atea-logo.png')) ?>" alt="ATEA Parfums"><p>A scent that feels like you.</p></div>
    <div class="footer-column"><h3>EXPLORE</h3><a href="<?= h(url('index.php')) ?>">Home</a><a href="<?= h(url('lineup.php')) ?>">Lineup</a><a href="<?= h(url('about.php')) ?>">About Us</a></div>
    <div class="footer-column"><h3>NEED HELP?</h3><a href="mailto:hello@ateaparfums.com">Contact</a><a href="<?= h(url('orders.php')) ?>">Pesanan saya</a><a href="<?= h(url('about.php')) ?>">Our story</a></div>
    <div class="footer-newsletter"><h3>STAY IN THE SCENT</h3><p>Ikuti kabar terbaru dari ATEA Parfums.</p><form id="newsletterForm"><input type="email" placeholder="Email address" required aria-label="Alamat email"><button type="submit" aria-label="Daftar newsletter">↗</button></form><small id="newsletterMessage"></small></div>
  </div>
  <div class="footer-bottom"><span>© <?= date('Y') ?> ATEA PARFUMS</span><span>CRAFTED FOR YOUR SIGNATURE</span><a href="#top">BACK TO TOP ↑</a></div>
</footer>
<nav class="bottom-nav" aria-label="Navigasi bawah">
  <a href="<?= h(url('index.php')) ?>" class="<?= $page === 'home' ? 'active' : '' ?>"><span>⌂</span>Home</a>
  <a href="<?= h(url('lineup.php')) ?>" class="<?= $page === 'lineup' ? 'active' : '' ?>"><span>✧</span>Lineup</a>
  <a href="<?= h(url('about.php')) ?>" class="<?= $page === 'about' ? 'active' : '' ?>"><span>◌</span>About</a>
  <button id="bottomSearch"><span>⌕</span>Search</button>
  <button id="bottomBag"><span>▢</span>Cart <b id="bottomBagCount">0</b></button>
</nav>

<div class="modal-backdrop" id="productModal" aria-hidden="true">
  <article class="product-detail" role="dialog" aria-modal="true" aria-labelledby="detailName">
    <button class="modal-close" id="modalClose" aria-label="Tutup detail">×</button>
    <div class="detail-gallery"><div class="detail-image-wrap"><img id="detailImage" src="" alt=""><span class="detail-image-label">ATEA PARFUMS</span></div><div class="detail-thumbs" id="detailThumbs"></div></div>
    <div class="detail-copy">
      <p class="eyebrow" id="detailType"></p>
      <h2 id="detailName"></h2>
      <p class="detail-rating" id="detailRating"></p>
      <p class="detail-description" id="detailDescription"></p>
      <div class="detail-rule"></div>
      <h3>FRAGRANCE PYRAMID</h3>
      <p class="notes-line"><b>Top notes:</b> <span id="detailTop"></span></p>
      <p class="notes-line"><b>Middle notes:</b> <span id="detailMiddle"></span></p>
      <p class="notes-line"><b>Base notes:</b> <span id="detailBase"></span></p>
      <div class="detail-rule"></div>
      <h3>SIZE</h3>
      <div class="size-options" id="sizeOptions" role="radiogroup" aria-label="Pilih ukuran"></div>
      <p class="stock-note" id="stockNote"></p>
      <div class="detail-buy"><div><span class="price-label">PRICE</span><strong id="detailPrice"></strong></div><button id="addToBag">ADD TO CART <span>↗</span></button></div>
      <div class="detail-rule"></div>
      <h3>REVIEWS</h3>
      <div id="reviewList" class="review-list"></div>
      <form id="reviewForm" class="review-form" hidden>
        <label>Rating
          <select name="rating" id="reviewRating"><option value="5">★★★★★ (5)</option><option value="4">★★★★ (4)</option><option value="3">★★★ (3)</option><option value="2">★★ (2)</option><option value="1">★ (1)</option></select>
        </label>
        <textarea name="komentar" id="reviewText" rows="3" maxlength="1000" placeholder="Tulis pendapatmu tentang parfum ini..."></textarea>
        <button type="submit" class="solid-btn dark">KIRIM REVIEW</button>
      </form>
      <p class="review-login" id="reviewLogin" hidden><a href="<?= h($boot['loginUrl']) ?>">Login</a> untuk menulis review.</p>
    </div>
  </article>
</div>

<div class="drawer-overlay" id="drawerOverlay"></div>
<aside class="cart-drawer" id="cartDrawer" aria-hidden="true" aria-label="Keranjang belanja">
  <div class="cart-head"><h2>Cart</h2><button id="cartClose" aria-label="Tutup keranjang">×</button></div>
  <div class="cart-body" id="cartBody"></div>
  <div class="cart-foot" id="cartFoot" hidden>
    <p class="cart-shipping">Shipping &amp; taxes calculated at checkout</p>
    <a class="checkout-btn" id="checkoutBtn" href="<?= h($boot['checkoutUrl']) ?>"><span>CHECKOUT</span><i>•</i><span id="checkoutTotal"></span></a>
  </div>
</aside>
<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>window.ATEA = <?= json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="<?= h(url('script.js')) ?>?v=3"></script>
</body>
</html>
