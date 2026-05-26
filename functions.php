<?php
/**
 * PaperDoll Shop - functions.php
 * Fungsi utama tema WordPress untuk toko paperdoll mobile-first
 *
 * @package PaperdollShop
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// ============================================================
// KONSTANTA TEMA
// ============================================================
define( 'PAPERDOLL_VERSION',   '1.0.0' );
define( 'PAPERDOLL_DIR',       get_template_directory() );
define( 'PAPERDOLL_URL',       get_template_directory_uri() );
define( 'PAPERDOLL_ASSETS',    PAPERDOLL_URL . '/assets' );
define( 'PAPERDOLL_WA_NUMBER', get_option( 'paperdoll_wa_number', '628385448811' ) );

// ============================================================
// SETUP TEMA
// ============================================================
function paperdoll_theme_setup() {
    // Dukungan bahasa
    load_theme_textdomain( 'paperdoll-shop', PAPERDOLL_DIR . '/languages' );

    // Fitur WordPress standar
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style'
    ] );
    add_theme_support( 'custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ] );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'wp-block-styles' );

    // Ukuran thumbnail produk
    add_image_size( 'paperdoll-card',   400, 400, true );
    add_image_size( 'paperdoll-banner', 1000, 365, true );
    add_image_size( 'paperdoll-scroll', 220, 220, true );
}
add_action( 'after_setup_theme', 'paperdoll_theme_setup' );

// ============================================================
// ENQUEUE STYLES & SCRIPTS
// ============================================================
function paperdoll_enqueue_assets() {
    // Google Fonts – Inter
    wp_enqueue_style(
        'google-fonts-inter',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        [],
        null
    );

    // Stylesheet Utama Tema
    wp_enqueue_style(
        'paperdoll-style',
        get_stylesheet_uri(),
        [ 'google-fonts-inter' ],
        PAPERDOLL_VERSION
    );

    // Script Utama
    wp_enqueue_script(
        'paperdoll-main',
        PAPERDOLL_ASSETS . '/js/main.js',
        [],
        PAPERDOLL_VERSION,
        true   // Di footer
    );

    // Kirim data PHP ke JavaScript
    wp_localize_script( 'paperdoll-main', 'paperdollData', [
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'paperdoll_nonce' ),
        'waNumber'  => PAPERDOLL_WA_NUMBER,
        'siteName'  => get_bloginfo( 'name' ),
        'siteUrl'   => home_url(),
        'themeUrl'  => PAPERDOLL_URL,
        'currency'  => 'Rp',
        'products'  => paperdoll_get_products_json(),
    ] );
}
add_action( 'wp_enqueue_scripts', 'paperdoll_enqueue_assets' );

// ============================================================
// PWA MANIFEST & META TAGS
// ============================================================
function paperdoll_pwa_meta_tags() {
    $theme_color = '#00aa5b';
    ?>
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo esc_url( home_url( '/paperdoll-manifest.json' ) ); ?>">

    <!-- PWA & Mobile Meta -->
    <meta name="theme-color" content="<?php echo esc_attr( $theme_color ); ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
    <meta name="application-name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('<?php echo esc_url( home_url( '/paperdoll-sw.js' ) ); ?>')
                    .then(reg => console.log('PaperDoll SW registered:', reg.scope))
                    .catch(err => console.warn('PaperDoll SW failed:', err));
            });
        }
    </script>
    <?php
}
add_action( 'wp_head', 'paperdoll_pwa_meta_tags', 1 );

// ============================================================
// CUSTOM POST TYPE: PRODUK PAPERDOLL
// ============================================================
function paperdoll_register_post_types() {
    // CPT: Produk
    register_post_type( 'paperdoll_produk', [
        'labels' => [
            'name'               => __( 'Produk Paperdoll', 'paperdoll-shop' ),
            'singular_name'      => __( 'Produk', 'paperdoll-shop' ),
            'add_new'            => __( 'Tambah Produk', 'paperdoll-shop' ),
            'add_new_item'       => __( 'Tambah Produk Baru', 'paperdoll-shop' ),
            'edit_item'          => __( 'Edit Produk', 'paperdoll-shop' ),
            'view_item'          => __( 'Lihat Produk', 'paperdoll-shop' ),
            'search_items'       => __( 'Cari Produk', 'paperdoll-shop' ),
            'not_found'          => __( 'Produk tidak ditemukan', 'paperdoll-shop' ),
            'all_items'          => __( 'Semua Produk', 'paperdoll-shop' ),
            'menu_name'          => __( 'Produk', 'paperdoll-shop' ),
        ],
        'public'              => true,
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => 'produk' ],
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'show_in_rest'        => true,
        'menu_icon'           => 'dashicons-products',
        'menu_position'       => 5,
    ] );

    // CPT: Banner Slider
    register_post_type( 'paperdoll_banner', [
        'labels' => [
            'name'          => __( 'Banner Slider', 'paperdoll-shop' ),
            'singular_name' => __( 'Banner', 'paperdoll-shop' ),
            'add_new_item'  => __( 'Tambah Banner', 'paperdoll-shop' ),
        ],
        'public'       => false,
        'show_ui'      => true,
        'supports'     => [ 'title', 'thumbnail' ],
        'menu_icon'    => 'dashicons-images-alt2',
        'menu_position'=> 6,
    ] );
}
add_action( 'init', 'paperdoll_register_post_types' );

// ============================================================
// CUSTOM TAXONOMY: KATEGORI PRODUK
// ============================================================
function paperdoll_register_taxonomies() {
    register_taxonomy( 'paperdoll_kategori', 'paperdoll_produk', [
        'labels' => [
            'name'              => __( 'Kategori', 'paperdoll-shop' ),
            'singular_name'     => __( 'Kategori', 'paperdoll-shop' ),
            'add_new_item'      => __( 'Tambah Kategori', 'paperdoll-shop' ),
            'edit_item'         => __( 'Edit Kategori', 'paperdoll-shop' ),
            'search_items'      => __( 'Cari Kategori', 'paperdoll-shop' ),
            'all_items'         => __( 'Semua Kategori', 'paperdoll-shop' ),
        ],
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'rewrite'           => [ 'slug' => 'kategori-produk' ],
        'show_admin_column' => true,
    ] );
}
add_action( 'init', 'paperdoll_register_taxonomies' );

// ============================================================
// META BOX: HARGA & DETAIL PRODUK
// ============================================================
function paperdoll_add_meta_boxes() {
    add_meta_box(
        'paperdoll_product_details',
        __( 'Detail Harga & Produk', 'paperdoll-shop' ),
        'paperdoll_render_product_meta_box',
        'paperdoll_produk',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'paperdoll_add_meta_boxes' );

function paperdoll_render_product_meta_box( $post ) {
    wp_nonce_field( 'paperdoll_save_product_meta', 'paperdoll_meta_nonce' );

    $price          = get_post_meta( $post->ID, '_paperdoll_price',         true );
    $original_price = get_post_meta( $post->ID, '_paperdoll_original_price', true );
    $discount       = get_post_meta( $post->ID, '_paperdoll_discount',       true );
    $rating         = get_post_meta( $post->ID, '_paperdoll_rating',         true );
    $sold_count     = get_post_meta( $post->ID, '_paperdoll_sold',           true );
    $is_mall        = get_post_meta( $post->ID, '_paperdoll_is_mall',        true );
    ?>
    <table class="form-table" style="width:100%">
        <tr>
            <th style="padding:4px 0"><label for="paperdoll_price"><?php esc_html_e( 'Harga (Rp)', 'paperdoll-shop' ); ?></label></th>
            <td><input type="number" id="paperdoll_price" name="paperdoll_price" value="<?php echo esc_attr( $price ); ?>" style="width:100%"></td>
        </tr>
        <tr>
            <th style="padding:4px 0"><label for="paperdoll_original_price"><?php esc_html_e( 'Harga Asli (Rp)', 'paperdoll-shop' ); ?></label></th>
            <td><input type="number" id="paperdoll_original_price" name="paperdoll_original_price" value="<?php echo esc_attr( $original_price ); ?>" style="width:100%"></td>
        </tr>
        <tr>
            <th style="padding:4px 0"><label for="paperdoll_discount"><?php esc_html_e( 'Diskon (%)', 'paperdoll-shop' ); ?></label></th>
            <td><input type="text" id="paperdoll_discount" name="paperdoll_discount" placeholder="cth: 25%" value="<?php echo esc_attr( $discount ); ?>" style="width:100%"></td>
        </tr>
        <tr>
            <th style="padding:4px 0"><label for="paperdoll_rating"><?php esc_html_e( 'Rating (0-5)', 'paperdoll-shop' ); ?></label></th>
            <td><input type="number" id="paperdoll_rating" name="paperdoll_rating" min="0" max="5" step="0.1" value="<?php echo esc_attr( $rating ); ?>" style="width:100%"></td>
        </tr>
        <tr>
            <th style="padding:4px 0"><label for="paperdoll_sold"><?php esc_html_e( 'Terjual', 'paperdoll-shop' ); ?></label></th>
            <td><input type="text" id="paperdoll_sold" name="paperdoll_sold" placeholder="cth: 250+" value="<?php echo esc_attr( $sold_count ); ?>" style="width:100%"></td>
        </tr>
        <tr>
            <th style="padding:4px 0"><label><?php esc_html_e( 'Paperdoll Mall', 'paperdoll-shop' ); ?></label></th>
            <td>
                <input type="checkbox" name="paperdoll_is_mall" value="1" <?php checked( $is_mall, '1' ); ?>>
                <span><?php esc_html_e( 'Tampilkan di Official Mall', 'paperdoll-shop' ); ?></span>
            </td>
        </tr>
    </table>
    <?php
}

function paperdoll_save_product_meta( $post_id ) {
    // Verifikasi nonce & permission
    if (
        ! isset( $_POST['paperdoll_meta_nonce'] ) ||
        ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['paperdoll_meta_nonce'] ) ), 'paperdoll_save_product_meta' )
    ) return;

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $fields = [
        'paperdoll_price'          => '_paperdoll_price',
        'paperdoll_original_price' => '_paperdoll_original_price',
        'paperdoll_discount'       => '_paperdoll_discount',
        'paperdoll_rating'         => '_paperdoll_rating',
        'paperdoll_sold'           => '_paperdoll_sold',
    ];

    foreach ( $fields as $field => $meta_key ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, $meta_key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
        }
    }

    $is_mall = isset( $_POST['paperdoll_is_mall'] ) ? '1' : '0';
    update_post_meta( $post_id, '_paperdoll_is_mall', $is_mall );
}
add_action( 'save_post_paperdoll_produk', 'paperdoll_save_product_meta' );

// ============================================================
// AMBIL PRODUK SEBAGAI JSON (untuk JS)
// ============================================================
function paperdoll_get_products_json() {
    $query = new WP_Query( [
        'post_type'      => 'paperdoll_produk',
        'posts_per_page' => 50,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    $products = [];

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $id      = get_the_ID();
            $terms   = wp_get_post_terms( $id, 'paperdoll_kategori', [ 'fields' => 'slugs' ] );
            $cat     = ! empty( $terms ) && ! is_wp_error( $terms ) ? $terms[0] : 'all';

            $reviews_raw = get_post_meta( $id, '_paperdoll_reviews', true );
            $reviews     = is_array( $reviews_raw ) ? $reviews_raw : [];

            $products[] = [
                'id'            => $id,
                'name'          => get_the_title(),
                'price'         => (int) get_post_meta( $id, '_paperdoll_price',          true ),
                'originalPrice' => (int) get_post_meta( $id, '_paperdoll_original_price', true ),
                'discount'      => get_post_meta( $id, '_paperdoll_discount', true ) ?: '0%',
                'rating'        => get_post_meta( $id, '_paperdoll_rating',   true ) ?: '5.0',
                'sold'          => get_post_meta( $id, '_paperdoll_sold',     true ) ?: '0',
                'category'      => $cat,
                'img'           => get_the_post_thumbnail_url( $id, 'paperdoll-card' ) ?: '',
                'desc'          => wp_strip_all_tags( get_the_excerpt() ?: get_the_content() ),
                'isMall'        => (bool) get_post_meta( $id, '_paperdoll_is_mall', true ),
                'reviews'       => $reviews,
            ];
        }
    }

    wp_reset_postdata();
    return $products;
}

// ============================================================
// AMBIL BANNER SLIDER
// ============================================================
function paperdoll_get_banners() {
    $query = new WP_Query( [
        'post_type'      => 'paperdoll_banner',
        'posts_per_page' => 6,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ] );

    $banners = [];

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $img = get_the_post_thumbnail_url( get_the_ID(), 'paperdoll-banner' );
            if ( $img ) {
                $banners[] = [
                    'img'   => $img,
                    'title' => get_the_title(),
                    'url'   => get_post_meta( get_the_ID(), '_banner_link', true ) ?: '#',
                ];
            }
        }
    }

    wp_reset_postdata();

    // Fallback ke placeholder jika tidak ada banner
    if ( empty( $banners ) ) {
        $banners = [
            [
                'img'   => 'https://images.unsplash.com/photo-1596461404969-9ae70f2830c1?auto=format&fit=crop&w=1000&q=80',
                'title' => 'Promo Gajian Paperdoll',
                'url'   => '#',
            ],
            [
                'img'   => 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=1000&q=80',
                'title' => 'Hemat Ongkir hingga Rp15.000',
                'url'   => '#',
            ],
            [
                'img'   => 'https://images.unsplash.com/photo-1515488042361-404e9250afef?auto=format&fit=crop&w=1000&q=80',
                'title' => 'Koleksi Baru: Kamar Tidur 3D',
                'url'   => '#',
            ],
        ];
    }

    return $banners;
}

// ============================================================
// AJAX: PENCARIAN PRODUK
// ============================================================
function paperdoll_ajax_search() {
    check_ajax_referer( 'paperdoll_nonce', 'nonce' );

    $keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';

    $query = new WP_Query( [
        'post_type'      => 'paperdoll_produk',
        'posts_per_page' => 20,
        'post_status'    => 'publish',
        's'              => $keyword,
    ] );

    $results = [];

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $id        = get_the_ID();
            $results[] = [
                'id'    => $id,
                'name'  => get_the_title(),
                'price' => (int) get_post_meta( $id, '_paperdoll_price', true ),
                'img'   => get_the_post_thumbnail_url( $id, 'paperdoll-card' ) ?: '',
            ];
        }
    }

    wp_reset_postdata();
    wp_send_json_success( $results );
}
add_action( 'wp_ajax_paperdoll_search',        'paperdoll_ajax_search' );
add_action( 'wp_ajax_nopriv_paperdoll_search', 'paperdoll_ajax_search' );

// ============================================================
// HALAMAN PENGATURAN TEMA (Admin)
// ============================================================
function paperdoll_admin_menu() {
    add_theme_page(
        __( 'Pengaturan PaperDoll Shop', 'paperdoll-shop' ),
        __( 'PaperDoll Shop', 'paperdoll-shop' ),
        'manage_options',
        'paperdoll-settings',
        'paperdoll_settings_page'
    );
}
add_action( 'admin_menu', 'paperdoll_admin_menu' );

function paperdoll_settings_page() {
    if ( isset( $_POST['paperdoll_save_settings'] ) ) {
        check_admin_referer( 'paperdoll_settings_action' );
        update_option( 'paperdoll_wa_number', sanitize_text_field( wp_unslash( $_POST['paperdoll_wa_number'] ?? '' ) ) );
        update_option( 'paperdoll_store_name', sanitize_text_field( wp_unslash( $_POST['paperdoll_store_name'] ?? '' ) ) );
        update_option( 'paperdoll_store_city', sanitize_text_field( wp_unslash( $_POST['paperdoll_store_city'] ?? '' ) ) );
        update_option( 'paperdoll_default_location', sanitize_text_field( wp_unslash( $_POST['paperdoll_default_location'] ?? '' ) ) );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Pengaturan disimpan!', 'paperdoll-shop' ) . '</p></div>';
    }

    $wa_number        = get_option( 'paperdoll_wa_number',        '628385448811' );
    $store_name       = get_option( 'paperdoll_store_name',       'PaperDoll Shop' );
    $store_city       = get_option( 'paperdoll_store_city',       'Yogyakarta' );
    $default_location = get_option( 'paperdoll_default_location', 'Banjarnegara, Jateng' );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Pengaturan PaperDoll Shop', 'paperdoll-shop' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'paperdoll_settings_action' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="paperdoll_wa_number"><?php esc_html_e( 'Nomor WhatsApp', 'paperdoll-shop' ); ?></label></th>
                    <td>
                        <input type="text" id="paperdoll_wa_number" name="paperdoll_wa_number" value="<?php echo esc_attr( $wa_number ); ?>" class="regular-text" placeholder="628385448811">
                        <p class="description"><?php esc_html_e( 'Format: kode negara tanpa + (cth: 628385448811)', 'paperdoll-shop' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="paperdoll_store_name"><?php esc_html_e( 'Nama Toko', 'paperdoll-shop' ); ?></label></th>
                    <td><input type="text" id="paperdoll_store_name" name="paperdoll_store_name" value="<?php echo esc_attr( $store_name ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="paperdoll_store_city"><?php esc_html_e( 'Kota Toko', 'paperdoll-shop' ); ?></label></th>
                    <td><input type="text" id="paperdoll_store_city" name="paperdoll_store_city" value="<?php echo esc_attr( $store_city ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="paperdoll_default_location"><?php esc_html_e( 'Lokasi Default Pengiriman', 'paperdoll-shop' ); ?></label></th>
                    <td>
                        <input type="text" id="paperdoll_default_location" name="paperdoll_default_location" value="<?php echo esc_attr( $default_location ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Tampil saat geolokasi tidak diizinkan pengguna', 'paperdoll-shop' ); ?></p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="paperdoll_save_settings" class="button button-primary">
                    <?php esc_html_e( 'Simpan Pengaturan', 'paperdoll-shop' ); ?>
                </button>
            </p>
        </form>
    </div>
    <?php
}

// ============================================================
// CUSTOMIZER: OPSI WARNA TEMA
// ============================================================
function paperdoll_customize_register( $wp_customize ) {
    $wp_customize->add_section( 'paperdoll_colors', [
        'title'    => __( 'Warna PaperDoll Shop', 'paperdoll-shop' ),
        'priority' => 30,
    ] );

    $wp_customize->add_setting( 'paperdoll_primary_color', [
        'default'           => '#00aa5b',
        'sanitize_callback' => 'sanitize_hex_color',
    ] );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'paperdoll_primary_color', [
        'label'   => __( 'Warna Utama (Hijau)', 'paperdoll-shop' ),
        'section' => 'paperdoll_colors',
    ] ) );

    $wp_customize->add_setting( 'paperdoll_accent_color', [
        'default'           => '#ff6000',
        'sanitize_callback' => 'sanitize_hex_color',
    ] );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'paperdoll_accent_color', [
        'label'   => __( 'Warna Aksen (Orange)', 'paperdoll-shop' ),
        'section' => 'paperdoll_colors',
    ] ) );
}
add_action( 'customize_register', 'paperdoll_customize_register' );

// Output CSS Dinamis dari Customizer
function paperdoll_customizer_css() {
    $primary = get_theme_mod( 'paperdoll_primary_color', '#00aa5b' );
    $accent  = get_theme_mod( 'paperdoll_accent_color',  '#ff6000' );
    ?>
    <style id="paperdoll-customizer-css">
        :root {
            --primary: <?php echo esc_attr( $primary ); ?>;
            --orange:  <?php echo esc_attr( $accent ); ?>;
        }
    </style>
    <?php
}
add_action( 'wp_head', 'paperdoll_customizer_css' );

// ============================================================
// DAFTARKAN MANIFEST.JSON & SW.JS via Rewrite Rules
// ============================================================
function paperdoll_add_rewrite_rules() {
    add_rewrite_rule( '^paperdoll-manifest\.json$', 'index.php?paperdoll_manifest=1', 'top' );
    add_rewrite_rule( '^paperdoll-sw\.js$',          'index.php?paperdoll_sw=1',       'top' );
}
add_action( 'init', 'paperdoll_add_rewrite_rules' );

function paperdoll_query_vars( $vars ) {
    $vars[] = 'paperdoll_manifest';
    $vars[] = 'paperdoll_sw';
    return $vars;
}
add_filter( 'query_vars', 'paperdoll_query_vars' );

function paperdoll_handle_pwa_files() {
    // Serve manifest.json
    if ( get_query_var( 'paperdoll_manifest' ) ) {
        header( 'Content-Type: application/manifest+json' );
        $manifest = [
            'name'             => get_bloginfo( 'name' ),
            'short_name'       => get_option( 'paperdoll_store_name', 'PaperDoll' ),
            'description'      => get_bloginfo( 'description' ) ?: 'Toko PaperDoll Online Terlengkap',
            'start_url'        => home_url( '/' ),
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#00aa5b',
            'theme_color'      => get_theme_mod( 'paperdoll_primary_color', '#00aa5b' ),
            'lang'             => 'id',
            'icons'            => [
                [
                    'src'     => PAPERDOLL_ASSETS . '/icons/icon-192.png',
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src'     => PAPERDOLL_ASSETS . '/icons/icon-512.png',
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
            'categories' => [ 'shopping', 'lifestyle' ],
        ];
        echo wp_json_encode( $manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
        exit;
    }

    // Serve service-worker.js
    if ( get_query_var( 'paperdoll_sw' ) ) {
        header( 'Content-Type: application/javascript' );
        readfile( PAPERDOLL_DIR . '/paperdoll-sw.js' );
        exit;
    }
}
add_action( 'template_redirect', 'paperdoll_handle_pwa_files' );

// ============================================================
// HELPER: FORMAT RUPIAH
// ============================================================
if ( ! function_exists( 'paperdoll_format_rupiah' ) ) {
    function paperdoll_format_rupiah( int $amount ): string {
        return 'Rp ' . number_format( $amount, 0, ',', '.' );
    }
}

// ============================================================
// DEQUEUE BLOCK LIBRARY (opsional — efisiensi)
// ============================================================
function paperdoll_dequeue_block_styles() {
    // Hapus komentar baris ini jika ingin tetap memakai blok Gutenberg
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'paperdoll_dequeue_block_styles', 100 );
