<?php
/**
 * PaperDoll Shop - header.php
 * Menggunakan SVG icon inline — tidak perlu CDN apapun
 *
 * @package PaperdollShop
 */

defined( 'ABSPATH' ) || exit;

// Load icon helper
require_once PAPERDOLL_DIR . '/template-parts/icon-helper.php';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- SVG Icon Sprite (inline, tanpa CDN) -->
<?php pd_icon_sprite(); ?>

<!-- Sticky Header -->
<header class="site-header" role="banner">
    <div class="header-container">
        <div class="search-bar-wrapper">
            <?php echo pd_icon('search', 'search-icon', '18'); ?>
            <input
                type="search"
                id="search-input"
                placeholder="<?php esc_attr_e( 'Cari Paperdoll imut...', 'paperdoll-shop' ); ?>"
                onkeyup="handleSearch(event)"
                autocomplete="off"
                aria-label="<?php esc_attr_e( 'Cari produk', 'paperdoll-shop' ); ?>"
            >
        </div>
        <div class="header-actions">
            <button class="header-btn" onclick="openWhatsAppChat()" aria-label="<?php esc_attr_e( 'Chat WhatsApp', 'paperdoll-shop' ); ?>">
                <?php echo pd_icon('chat', '', '22'); ?>
                <span class="badge-red">1</span>
            </button>
            <button class="header-btn" onclick="toggleCartSheet()" aria-label="<?php esc_attr_e( 'Keranjang belanja', 'paperdoll-shop' ); ?>">
                <?php echo pd_icon('cart', '', '22'); ?>
                <span class="badge-red" id="cart-badge-count" style="display:none;" aria-live="polite">0</span>
            </button>
        </div>
    </div>
</header>
