<?php
/**
 * PaperDoll Shop - index.php
 * Template utama — semua icon pakai SVG inline
 *
 * @package PaperdollShop
 */

defined( 'ABSPATH' ) || exit;

$banners          = paperdoll_get_banners();
$wa_number        = PAPERDOLL_WA_NUMBER;
$default_location = get_option( 'paperdoll_default_location', 'Banjarnegara, Jateng' );

get_header();
?>

<div class="app-container">

    <!-- LAYAR 1: BERANDA -->
    <div id="screen-home" class="app-screen active">

        <!-- BANNER SLIDER -->
        <section class="slider-section">
            <div class="slider-container" id="slider">
                <div class="slides" id="slides">
                    <?php foreach ( $banners as $banner ) : ?>
                    <div class="slide" data-url="<?php echo esc_url( $banner['url'] ); ?>">
                        <img src="<?php echo esc_url( $banner['img'] ); ?>" alt="<?php echo esc_attr( $banner['title'] ); ?>" loading="eager">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="slider-dots" id="dots-container"></div>
        </section>

        <!-- PILL FILTER -->
        <div class="pills-scroll">
            <div class="pill-item active" onclick="pillsAction(this,'bonus')">
                <?php echo pd_icon('gift','','14','#ff6000'); ?>
                <?php esc_html_e('Bonus Anda','paperdoll-shop'); ?>
            </div>
            <div class="pill-item" onclick="pillsAction(this,'rp0')">
                <?php echo pd_icon('wallet','','14'); ?>
                <?php esc_html_e('Rp0 Mulai Belanja','paperdoll-shop'); ?>
            </div>
            <div class="pill-item" onclick="pillsAction(this,'kupon')">
                <?php echo pd_icon('ticket','','14'); ?>
                <?php esc_html_e('Kupon Diskon','paperdoll-shop'); ?>
            </div>
            <div class="pill-item" onclick="requestUserLocation()">
                <?php echo pd_icon('location','','14'); ?>
                <?php esc_html_e('Dikirim ke','paperdoll-shop'); ?>
                <span id="delivery-location" style="margin-left:3px;font-weight:bold;"><?php echo esc_html($default_location); ?></span>
            </div>
        </div>

        <!-- KATEGORI ICONS -->
        <section class="categories-container">
            <div class="categories-scroll">
                <?php
                $cats = [
                    ['slug'=>'all',       'icon'=>'grid',   'label'=>__('Semua Produk','paperdoll-shop')],
                    ['slug'=>'toca',      'icon'=>'home',   'label'=>__('Rumah Toca','paperdoll-shop')],
                    ['slug'=>'anime',     'icon'=>'female', 'label'=>__('Gaya Anime','paperdoll-shop')],
                    ['slug'=>'hijab',     'icon'=>'heart',  'label'=>__('Hijab Imut','paperdoll-shop')],
                    ['slug'=>'aksesoris', 'icon'=>'shirt',  'label'=>__('Baju & Aksesoris','paperdoll-shop')],
                    ['slug'=>'viral',     'icon'=>'flame',  'label'=>__('Paling Viral','paperdoll-shop')],
                ];
                foreach ($cats as $cat) : ?>
                <div class="cat-item" onclick="filterCategory('<?php echo esc_js($cat['slug']); ?>')">
                    <div class="cat-icon-wrapper">
                        <?php echo pd_icon($cat['icon'],'','22'); ?>
                    </div>
                    <span><?php echo esc_html($cat['label']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- LANJUT CEK INI, YUK -->
        <h2 class="section-title"><?php esc_html_e('Lanjut cek ini, yuk','paperdoll-shop'); ?></h2>
        <div class="scroll-products" id="recent-products"></div>

        <!-- KATALOG UTAMA -->
        <section class="for-you-section">
            <div class="tab-menu" id="home-tabs">
                <div class="tab-item active" onclick="switchHomeTab(this,'all')"><?php esc_html_e('For Anda','paperdoll-shop'); ?></div>
                <div class="tab-item" onclick="switchHomeTab(this,'viral')"><span class="tab-tag-promo">Guncang 6.6</span></div>
                <div class="tab-item" onclick="switchHomeTab(this,'toca')"><?php esc_html_e('Rumah Toca','paperdoll-shop'); ?></div>
                <div class="tab-item" onclick="switchHomeTab(this,'anime')"><?php esc_html_e('Gaya Anime','paperdoll-shop'); ?></div>
            </div>
            <div class="product-grid" id="product-list" aria-live="polite"></div>
        </section>
    </div>

    <!-- LAYAR 2: FEED -->
    <div id="screen-feed" class="app-screen">
        <div class="dummy-screen-content">
            <?php echo pd_icon('tiktok','','64','#00aa5b'); ?>
            <h2><?php esc_html_e('Inspirasi & Video Kreatif','paperdoll-shop'); ?></h2>
            <p><?php esc_html_e('Temukan video tutorial merakit Paperdoll DIY dan ulasan seru langsung dari para kreator kesayangan Anda.','paperdoll-shop'); ?></p>
            <button class="btn-whatsapp mt-20" onclick="openWhatsAppChat()">
                <?php echo pd_icon('whatsapp','','18','#fff'); ?>
                <?php esc_html_e('Gabung Grup Komunitas','paperdoll-shop'); ?>
            </button>
        </div>
    </div>

    <!-- LAYAR 3: MALL -->
    <div id="screen-mall" class="app-screen">
        <div class="dummy-screen-content">
            <?php echo pd_icon('store','','64','#00aa5b'); ?>
            <h2><?php esc_html_e('Paperdoll Mall Official','paperdoll-shop'); ?></h2>
            <p><?php esc_html_e('Garansi 100% Original, Pengiriman Cepat, & Bebas Pengembalian 14 Hari.','paperdoll-shop'); ?></p>
        </div>
        <div class="product-grid" id="mall-product-list"></div>
    </div>

    <!-- LAYAR 4: TRANSAKSI -->
    <div id="screen-transactions" class="app-screen">
        <div class="dummy-screen-content">
            <?php echo pd_icon('analytics','','64','#00aa5b'); ?>
            <h2><?php esc_html_e('Riwayat Transaksi','paperdoll-shop'); ?></h2>
            <p><?php esc_html_e('Daftar pembelian atau orderan aktif Anda melalui WhatsApp akan tercatat di sini setelah melakukan koordinasi dengan Admin.','paperdoll-shop'); ?></p>
            <button class="btn-primary mt-20" style="width:80%;margin:20px auto 0;" onclick="switchScreen('home')">
                <?php esc_html_e('Belanja Sekarang','paperdoll-shop'); ?>
            </button>
        </div>
    </div>

    <!-- LAYAR 5: AKUN -->
    <div id="screen-account" class="app-screen">
        <div class="dummy-screen-content" style="padding-top:20px;">
            <div class="profile-avatar"><?php echo pd_icon('user','','36','#00aa5b'); ?></div>
            <h2><?php esc_html_e('Profil Anda','paperdoll-shop'); ?></h2>
            <p><?php esc_html_e('Kelola pesanan, alamat pengiriman otomatis, voucher gratis ongkir, dan info langganan Anda.','paperdoll-shop'); ?></p>
            <div class="profile-info-card mt-20">
                <div class="profile-info-row">
                    <span><?php esc_html_e('Lokasi Terdeteksi','paperdoll-shop'); ?></span>
                    <strong id="acc-location"><?php echo esc_html($default_location); ?></strong>
                </div>
                <div class="profile-info-row">
                    <span><?php esc_html_e('Voucher Saya','paperdoll-shop'); ?></span>
                    <strong class="highlight"><?php esc_html_e('3 Kupon Aktif','paperdoll-shop'); ?></strong>
                </div>
                <div class="profile-info-row">
                    <span><?php esc_html_e('Metode Pembayaran','paperdoll-shop'); ?></span>
                    <strong><?php esc_html_e('WhatsApp COD / Transfer','paperdoll-shop'); ?></strong>
                </div>
            </div>
            <button class="btn-whatsapp" onclick="openWhatsAppChat()">
                <?php echo pd_icon('help','','18','#fff'); ?>
                <?php esc_html_e('Hubungi Customer Care','paperdoll-shop'); ?>
            </button>
        </div>
    </div>

</div><!-- /app-container -->

<!-- BOTTOM NAVIGATION -->
<nav class="bottom-nav">
    <button class="nav-item active" onclick="switchScreen('home')">
        <?php echo pd_icon('thumbup','','20'); ?>
        <span><?php esc_html_e('Buat Kamu','paperdoll-shop'); ?></span>
    </button>
    <button class="nav-item" onclick="switchScreen('feed')">
        <?php echo pd_icon('tv','','20'); ?>
        <span><?php esc_html_e('Feed','paperdoll-shop'); ?></span>
    </button>
    <button class="nav-item" onclick="switchScreen('mall')">
        <?php echo pd_icon('store','','20'); ?>
        <span><?php esc_html_e('Mall','paperdoll-shop'); ?></span>
    </button>
    <button class="nav-item" onclick="switchScreen('transactions')">
        <?php echo pd_icon('file','','20'); ?>
        <span><?php esc_html_e('Transaksi','paperdoll-shop'); ?></span>
    </button>
    <button class="nav-item" onclick="switchScreen('account')">
        <?php echo pd_icon('user','','20'); ?>
        <span><?php esc_html_e('Akun','paperdoll-shop'); ?></span>
    </button>
</nav>

<!-- DETAIL PRODUK SHEET -->
<div class="detail-sheet" id="detailSheet" onclick="closeDetailOutside(event)">
    <div class="detail-content" onclick="event.stopPropagation()">
        <div class="detail-header">
            <h3><?php esc_html_e('Detail Produk','paperdoll-shop'); ?></h3>
            <button class="close-btn" onclick="toggleDetailSheet()">
                <?php echo pd_icon('x','','22'); ?>
            </button>
        </div>
        <div class="detail-body" id="detail-body-content"></div>
        <div class="detail-footer-btn-row">
            <button class="btn-sheet-add-cart" id="btn-add-cart-sheet">
                <span id="icon-cart-btn"><?php echo pd_icon('cart','','18','#fff'); ?></span>
                <?php esc_html_e('Masuk Keranjang','paperdoll-shop'); ?>
            </button>
            <button class="btn-sheet-whatsapp" id="btn-buy-wa-sheet">
                <?php echo pd_icon('whatsapp','','18','#fff'); ?>
                <?php esc_html_e('Pesan Langsung','paperdoll-shop'); ?>
            </button>
        </div>
    </div>
</div>

<!-- KERANJANG SHEET -->
<div class="bottom-sheet" id="cartSheet" onclick="closeCartOutside(event)">
    <div class="sheet-content" onclick="event.stopPropagation()">
        <div class="sheet-header">
            <h3><?php esc_html_e('Keranjang Belanja','paperdoll-shop'); ?></h3>
            <button class="close-btn" onclick="toggleCartSheet()">
                <?php echo pd_icon('x','','22'); ?>
            </button>
        </div>
        <div class="sheet-body" id="cart-list-container"></div>
        <div class="sheet-footer">
            <div class="total-row">
                <span><?php esc_html_e('Total Transaksi','paperdoll-shop'); ?></span>
                <span id="cart-total-price">Rp 0</span>
            </div>
            <button class="whatsapp-btn" onclick="sendToWhatsApp()">
                <?php echo pd_icon('whatsapp','','18','#fff'); ?>
                <?php printf(esc_html__('Beli via WhatsApp (%s)','paperdoll-shop'), esc_html($wa_number)); ?>
            </button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast-message"><?php esc_html_e('Produk dimasukkan ke keranjang!','paperdoll-shop'); ?></div>

<!-- SVG icon refs untuk JS (dipakai di buildProductCard) -->
<script>
// SVG icons untuk JavaScript rendering (kartu produk, cart items)
const PD_ICONS = {
    starFilled: `<?php echo pd_icon('star-filled','','11','#ffc400'); ?>`,
    star:       `<?php echo pd_icon('star','','11','#ffc400'); ?>`,
    checkCircle:`<?php echo pd_icon('check-circle','','12','#00aa5b'); ?>`,
    store:      `<?php echo pd_icon('store','','12','#5d3ebc'); ?>`,
    plus:       `<?php echo pd_icon('plus','','13'); ?>`,
    minus:      `<?php echo pd_icon('minus','','13'); ?>`,
    cartX:      `<?php echo pd_icon('cart-x','','48','#d1d5db'); ?>`,
    whatsapp:   `<?php echo pd_icon('whatsapp','','16','#fff'); ?>`,
    cart:       `<?php echo pd_icon('cart','','16','#fff'); ?>`,
    cartOutline:`<?php echo pd_icon('cart','','13','currentColor'); ?>`,
};
</script>

<?php get_footer(); ?>
