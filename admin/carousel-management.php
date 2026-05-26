<?php
/**
 * Manajemen carousel Lanjut Cek Ini.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'paperdoll_register_carousel_management_menu' ) ) {
	function paperdoll_register_carousel_management_menu() {
		add_submenu_page(
			'paperdoll-dashboard',
			__( 'Carousel', 'paperdoll-shop' ),
			__( 'Carousel', 'paperdoll-shop' ),
			'manage_options',
			'paperdoll-carousel-management',
			'paperdoll_render_carousel_management_page'
		);
	}
}
add_action( 'admin_menu', 'paperdoll_register_carousel_management_menu' );

if ( ! function_exists( 'paperdoll_handle_carousel_management_actions' ) ) {
	function paperdoll_handle_carousel_management_actions() {
		if ( ! isset( $_POST['paperdoll_carousel_action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		check_admin_referer( 'paperdoll_carousel_management' );

		$ordered_ids = array_map( 'absint', wp_unslash( $_POST['carousel_product_ids'] ?? [] ) );
		$ordered_ids = array_values( array_filter( $ordered_ids ) );
		update_option( 'paperdoll_carousel_product_ids', $ordered_ids );
	}
}
add_action( 'admin_init', 'paperdoll_handle_carousel_management_actions' );

if ( ! function_exists( 'paperdoll_render_carousel_management_page' ) ) {
	function paperdoll_render_carousel_management_page() {
		$products      = get_posts(
			[
				'post_type'      => 'paperdoll_produk',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
			]
		);
		$selected_ids  = paperdoll_get_carousel_product_ids();
		$selected_rows = [];
		foreach ( $selected_ids as $selected_id ) {
			$post = get_post( $selected_id );
			if ( $post && 'paperdoll_produk' === $post->post_type ) {
				$selected_rows[] = $post;
			}
		}
		?>
		<div class="wrap paperdoll-admin-wrap">
			<h1><?php esc_html_e( 'Kustomisasi Carousel & "Lanjut Cek Ini"', 'paperdoll-shop' ); ?></h1>
			<form method="post" class="paperdoll-admin-form">
				<?php wp_nonce_field( 'paperdoll_carousel_management' ); ?>
				<input type="hidden" name="paperdoll_carousel_action" value="save_carousel">
				<p><?php esc_html_e( 'Pilih produk lalu atur urutan menggunakan drag-drop.', 'paperdoll-shop' ); ?></p>

				<label><?php esc_html_e( 'Tambah Produk ke Carousel', 'paperdoll-shop' ); ?>
					<select id="paperdoll-carousel-product-picker">
						<option value=""><?php esc_html_e( '-- Pilih produk --', 'paperdoll-shop' ); ?></option>
						<?php foreach ( $products as $product ) : ?>
							<option value="<?php echo esc_attr( (string) $product->ID ); ?>" data-title="<?php echo esc_attr( get_the_title( $product ) ); ?>"><?php echo esc_html( get_the_title( $product ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<button type="button" class="button" id="paperdoll-add-carousel-item"><?php esc_html_e( 'Tambah', 'paperdoll-shop' ); ?></button>

				<ul class="paperdoll-sortable" data-sortable id="paperdoll-carousel-list">
					<?php foreach ( $selected_rows as $row ) : ?>
						<li>
							<input type="hidden" name="carousel_product_ids[]" value="<?php echo esc_attr( (string) $row->ID ); ?>">
							<span><?php echo esc_html( get_the_title( $row ) ); ?></span>
							<button type="button" class="button-link-delete paperdoll-remove-sort-item">×</button>
						</li>
					<?php endforeach; ?>
				</ul>

				<p><button class="button button-primary" type="submit"><?php esc_html_e( 'Simpan Carousel', 'paperdoll-shop' ); ?></button></p>
			</form>
		</div>
		<?php
	}
}
