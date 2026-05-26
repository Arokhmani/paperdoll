<?php
/**
 * Dashboard utama admin Paperdoll.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'paperdoll_register_dashboard_main_menu' ) ) {
	function paperdoll_register_dashboard_main_menu() {
		add_menu_page(
			__( 'Paperdoll Dashboard', 'paperdoll-shop' ),
			__( 'Paperdoll Dashboard', 'paperdoll-shop' ),
			'manage_options',
			'paperdoll-dashboard',
			'paperdoll_render_dashboard_main_page',
			'dashicons-store',
			56
		);
	}
}
add_action( 'admin_menu', 'paperdoll_register_dashboard_main_menu' );

if ( ! function_exists( 'paperdoll_enqueue_dashboard_assets' ) ) {
	function paperdoll_enqueue_dashboard_assets( $hook ) {
		if ( false === strpos( $hook, 'paperdoll' ) ) {
			return;
		}

		wp_enqueue_style(
			'paperdoll-admin-dashboard',
			PAPERDOLL_ASSETS . '/css/admin-dashboard.css',
			[],
			PAPERDOLL_VERSION
		);

		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script(
			'paperdoll-admin-dashboard',
			PAPERDOLL_ASSETS . '/js/admin-dashboard.js',
			[ 'jquery', 'jquery-ui-sortable' ],
			PAPERDOLL_VERSION,
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'paperdoll_enqueue_dashboard_assets' );

if ( ! function_exists( 'paperdoll_render_dashboard_main_page' ) ) {
	function paperdoll_render_dashboard_main_page() {
		$plugin_status = [
			'WooCommerce' => class_exists( 'WooCommerce' ),
			'Elementor'   => defined( 'ELEMENTOR_VERSION' ),
			'Yoast SEO'   => defined( 'WPSEO_VERSION' ),
			'WPForms'     => class_exists( 'WPForms' ),
			'MailerLite'  => class_exists( 'MailerLiteApi' ) || defined( 'MAILERLITE_WP_VERSION' ),
		];
		?>
		<div class="wrap paperdoll-admin-wrap">
			<h1><?php esc_html_e( 'Dashboard Paperdoll', 'paperdoll-shop' ); ?></h1>
			<p><?php esc_html_e( 'Kelola produk, kupon, banner, carousel, kategori, integrasi WooCommerce, dan checkout WhatsApp dari satu tempat.', 'paperdoll-shop' ); ?></p>

			<div class="paperdoll-grid">
				<a class="paperdoll-card" href="<?php echo esc_url( admin_url( 'admin.php?page=paperdoll-products-management' ) ); ?>">
					<h2><?php esc_html_e( 'Produk', 'paperdoll-shop' ); ?></h2>
					<p><?php esc_html_e( 'CRUD produk + bulk edit + opsi tampil di Mall.', 'paperdoll-shop' ); ?></p>
				</a>
				<a class="paperdoll-card" href="<?php echo esc_url( admin_url( 'admin.php?page=paperdoll-coupons-management' ) ); ?>">
					<h2><?php esc_html_e( 'Kupon & Diskon', 'paperdoll-shop' ); ?></h2>
					<p><?php esc_html_e( 'Kupon custom, limit penggunaan, tanggal berlaku, dan diskon otomatis.', 'paperdoll-shop' ); ?></p>
				</a>
				<a class="paperdoll-card" href="<?php echo esc_url( admin_url( 'admin.php?page=paperdoll-banners-management' ) ); ?>">
					<h2><?php esc_html_e( 'Slider Promo', 'paperdoll-shop' ); ?></h2>
					<p><?php esc_html_e( 'Upload banner, link target, preview, dan urutan drag-drop.', 'paperdoll-shop' ); ?></p>
				</a>
				<a class="paperdoll-card" href="<?php echo esc_url( admin_url( 'admin.php?page=paperdoll-carousel-management' ) ); ?>">
					<h2><?php esc_html_e( 'Carousel', 'paperdoll-shop' ); ?></h2>
					<p><?php esc_html_e( 'Pilih produk untuk section “Lanjut cek ini” dan atur urutan.', 'paperdoll-shop' ); ?></p>
				</a>
				<a class="paperdoll-card" href="<?php echo esc_url( admin_url( 'admin.php?page=paperdoll-categories-management' ) ); ?>">
					<h2><?php esc_html_e( 'Kategori & Icon', 'paperdoll-shop' ); ?></h2>
					<p><?php esc_html_e( 'Edit label kategori dan upload icon SVG/PNG.', 'paperdoll-shop' ); ?></p>
				</a>
				<a class="paperdoll-card" href="<?php echo esc_url( admin_url( 'admin.php?page=paperdoll-woocommerce-integration' ) ); ?>">
					<h2><?php esc_html_e( 'Integrasi WooCommerce', 'paperdoll-shop' ); ?></h2>
					<p><?php esc_html_e( 'Sinkron produk, kategori, harga, dan stok.', 'paperdoll-shop' ); ?></p>
				</a>
				<a class="paperdoll-card" href="<?php echo esc_url( admin_url( 'admin.php?page=paperdoll-checkout-whatsapp' ) ); ?>">
					<h2><?php esc_html_e( 'Checkout WhatsApp', 'paperdoll-shop' ); ?></h2>
					<p><?php esc_html_e( 'Format pesan checkout otomatis ke admin.', 'paperdoll-shop' ); ?></p>
				</a>
			</div>

			<h2><?php esc_html_e( 'Status Plugin Pendukung', 'paperdoll-shop' ); ?></h2>
			<table class="widefat striped">
				<thead><tr><th><?php esc_html_e( 'Plugin', 'paperdoll-shop' ); ?></th><th><?php esc_html_e( 'Status', 'paperdoll-shop' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $plugin_status as $plugin_name => $active ) : ?>
					<tr>
						<td><?php echo esc_html( $plugin_name ); ?></td>
						<td><?php echo $active ? '<span class="paperdoll-badge is-on">Aktif</span>' : '<span class="paperdoll-badge">Belum aktif</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
