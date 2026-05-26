<?php
/**
 * Integrasi WooCommerce.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'paperdoll_register_woocommerce_integration_menu' ) ) {
	function paperdoll_register_woocommerce_integration_menu() {
		add_submenu_page(
			'paperdoll-dashboard',
			__( 'Integrasi WooCommerce', 'paperdoll-shop' ),
			__( 'WooCommerce', 'paperdoll-shop' ),
			'manage_options',
			'paperdoll-woocommerce-integration',
			'paperdoll_render_woocommerce_integration_page'
		);
	}
}
add_action( 'admin_menu', 'paperdoll_register_woocommerce_integration_menu' );

if ( ! function_exists( 'paperdoll_handle_woocommerce_integration_actions' ) ) {
	function paperdoll_handle_woocommerce_integration_actions() {
		if ( ! isset( $_POST['paperdoll_woo_action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'paperdoll_woocommerce_integration' );
		$action = sanitize_key( wp_unslash( $_POST['paperdoll_woo_action'] ) );

		if ( 'save_woo_settings' === $action ) {
			update_option( 'paperdoll_wc_api_url', esc_url_raw( wp_unslash( $_POST['wc_api_url'] ?? '' ) ) );
			update_option( 'paperdoll_wc_consumer_key', sanitize_text_field( wp_unslash( $_POST['wc_consumer_key'] ?? '' ) ) );
			update_option( 'paperdoll_wc_consumer_secret', sanitize_text_field( wp_unslash( $_POST['wc_consumer_secret'] ?? '' ) ) );
		}

		if ( 'sync_to_woo' === $action && class_exists( 'WooCommerce' ) ) {
			$products = get_posts(
				[
					'post_type'      => 'paperdoll_produk',
					'post_status'    => 'publish',
					'posts_per_page' => 100,
				]
			);
			foreach ( $products as $product ) {
				$wc_product_id = (int) get_post_meta( $product->ID, '_paperdoll_wc_product_id', true );
				$wc_data       = [
					'post_type'   => 'product',
					'post_status' => 'publish',
					'post_title'  => $product->post_title,
				];
				if ( $wc_product_id > 0 ) {
					$wc_data['ID'] = $wc_product_id;
					$wc_product_id = wp_update_post( $wc_data );
				} else {
					$wc_product_id = wp_insert_post( $wc_data );
				}

				if ( $wc_product_id && ! is_wp_error( $wc_product_id ) ) {
					update_post_meta( $wc_product_id, '_regular_price', get_post_meta( $product->ID, '_paperdoll_original_price', true ) );
					update_post_meta( $wc_product_id, '_price', get_post_meta( $product->ID, '_paperdoll_price', true ) );
					update_post_meta( $wc_product_id, '_stock_status', 'instock' );
					update_post_meta( $product->ID, '_paperdoll_wc_product_id', $wc_product_id );
					if ( has_post_thumbnail( $product ) ) {
						set_post_thumbnail( $wc_product_id, get_post_thumbnail_id( $product ) );
					}
				}
			}
		}
	}
}
add_action( 'admin_init', 'paperdoll_handle_woocommerce_integration_actions' );

if ( ! function_exists( 'paperdoll_render_woocommerce_integration_page' ) ) {
	function paperdoll_render_woocommerce_integration_page() {
		$woo_active = class_exists( 'WooCommerce' );
		?>
		<div class="wrap paperdoll-admin-wrap">
			<h1><?php esc_html_e( 'Integrasi WooCommerce', 'paperdoll-shop' ); ?></h1>
			<p><?php esc_html_e( 'Mapping field Paperdoll -> WooCommerce: nama, harga, stok, kategori, dan thumbnail.', 'paperdoll-shop' ); ?></p>

			<form method="post" class="paperdoll-admin-form">
				<?php wp_nonce_field( 'paperdoll_woocommerce_integration' ); ?>
				<input type="hidden" name="paperdoll_woo_action" value="save_woo_settings">
				<h2><?php esc_html_e( 'REST API Settings', 'paperdoll-shop' ); ?></h2>
				<div class="paperdoll-form-grid">
					<label><?php esc_html_e( 'WooCommerce URL', 'paperdoll-shop' ); ?><input type="url" name="wc_api_url" value="<?php echo esc_attr( (string) get_option( 'paperdoll_wc_api_url', home_url() ) ); ?>"></label>
					<label><?php esc_html_e( 'Consumer Key', 'paperdoll-shop' ); ?><input type="text" name="wc_consumer_key" value="<?php echo esc_attr( (string) get_option( 'paperdoll_wc_consumer_key', '' ) ); ?>"></label>
					<label><?php esc_html_e( 'Consumer Secret', 'paperdoll-shop' ); ?><input type="text" name="wc_consumer_secret" value="<?php echo esc_attr( (string) get_option( 'paperdoll_wc_consumer_secret', '' ) ); ?>"></label>
				</div>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Simpan Pengaturan WooCommerce', 'paperdoll-shop' ); ?></button></p>
			</form>

			<form method="post" class="paperdoll-admin-form">
				<?php wp_nonce_field( 'paperdoll_woocommerce_integration' ); ?>
				<input type="hidden" name="paperdoll_woo_action" value="sync_to_woo">
				<h2><?php esc_html_e( 'Sinkronisasi Produk', 'paperdoll-shop' ); ?></h2>
				<p><?php echo $woo_active ? esc_html__( 'WooCommerce aktif. Klik tombol untuk publish/sinkron produk Paperdoll ke WooCommerce.', 'paperdoll-shop' ) : esc_html__( 'WooCommerce belum aktif. Aktifkan plugin untuk menjalankan sinkronisasi.', 'paperdoll-shop' ); ?></p>
				<p><button type="submit" class="button button-primary" <?php disabled( ! $woo_active ); ?>><?php esc_html_e( 'Sync Produk ke WooCommerce', 'paperdoll-shop' ); ?></button></p>
			</form>
		</div>
		<?php
	}
}
