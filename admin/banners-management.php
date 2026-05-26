<?php
/**
 * Manajemen banner slider promo.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'paperdoll_register_banners_management_menu' ) ) {
	function paperdoll_register_banners_management_menu() {
		add_submenu_page(
			'paperdoll-dashboard',
			__( 'Slider Promo', 'paperdoll-shop' ),
			__( 'Slider Promo', 'paperdoll-shop' ),
			'manage_options',
			'paperdoll-banners-management',
			'paperdoll_render_banners_management_page'
		);
	}
}
add_action( 'admin_menu', 'paperdoll_register_banners_management_menu' );

if ( ! function_exists( 'paperdoll_handle_banners_management_actions' ) ) {
	function paperdoll_handle_banners_management_actions() {
		if ( ! isset( $_POST['paperdoll_banner_action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'paperdoll_banners_management' );
		$action = sanitize_key( wp_unslash( $_POST['paperdoll_banner_action'] ) );

		if ( 'save_banner' === $action ) {
			$banner_id   = absint( wp_unslash( $_POST['banner_id'] ?? 0 ) );
			$title       = sanitize_text_field( wp_unslash( $_POST['banner_title'] ?? '' ) );
			$image_id    = absint( wp_unslash( $_POST['banner_image_id'] ?? 0 ) );
			$target_link = esc_url_raw( wp_unslash( $_POST['banner_link'] ?? '' ) );

			$post_data = [
				'post_type'   => 'paperdoll_banner',
				'post_status' => 'publish',
				'post_title'  => $title,
			];

			if ( $banner_id > 0 ) {
				$post_data['ID'] = $banner_id;
				$banner_id       = wp_update_post( $post_data );
			} else {
				$banner_id = wp_insert_post( $post_data );
			}

			if ( $banner_id && ! is_wp_error( $banner_id ) ) {
				if ( $image_id > 0 ) {
					set_post_thumbnail( $banner_id, $image_id );
				}
				update_post_meta( $banner_id, '_banner_link', $target_link );
			}
		}

		if ( 'delete_banner' === $action ) {
			$banner_id = absint( wp_unslash( $_POST['banner_id'] ?? 0 ) );
			if ( $banner_id > 0 ) {
				wp_delete_post( $banner_id, true );
			}
		}

		if ( 'sort_banners' === $action ) {
			$ordered_ids = array_map( 'absint', wp_unslash( $_POST['banner_order'] ?? [] ) );
			foreach ( $ordered_ids as $menu_order => $banner_id ) {
				wp_update_post(
					[
						'ID'         => $banner_id,
						'menu_order' => $menu_order,
					]
				);
			}
		}
	}
}
add_action( 'admin_init', 'paperdoll_handle_banners_management_actions' );

if ( ! function_exists( 'paperdoll_render_banners_management_page' ) ) {
	function paperdoll_render_banners_management_page() {
		$banners = get_posts(
			[
				'post_type'      => 'paperdoll_banner',
				'post_status'    => 'any',
				'posts_per_page' => 50,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
		);
		?>
		<div class="wrap paperdoll-admin-wrap">
			<h1><?php esc_html_e( 'Kustomisasi Slider Promo', 'paperdoll-shop' ); ?></h1>

			<form method="post" class="paperdoll-admin-form">
				<?php wp_nonce_field( 'paperdoll_banners_management' ); ?>
				<input type="hidden" name="paperdoll_banner_action" value="save_banner">
				<h2><?php esc_html_e( 'Tambah / Edit Banner', 'paperdoll-shop' ); ?></h2>
				<div class="paperdoll-form-grid">
					<label>ID <input type="number" name="banner_id" min="0"></label>
					<label><?php esc_html_e( 'Judul Banner', 'paperdoll-shop' ); ?><input type="text" name="banner_title" required></label>
					<label><?php esc_html_e( 'Target Link', 'paperdoll-shop' ); ?><input type="url" name="banner_link" placeholder="https://"></label>
					<div>
						<label><?php esc_html_e( 'Gambar Banner', 'paperdoll-shop' ); ?></label>
						<input type="hidden" name="banner_image_id" class="paperdoll-media-id" value="">
						<button type="button" class="button paperdoll-media-upload"><?php esc_html_e( 'Upload Banner', 'paperdoll-shop' ); ?></button>
						<div class="paperdoll-image-preview"></div>
					</div>
				</div>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Simpan Banner', 'paperdoll-shop' ); ?></button></p>
			</form>

			<form method="post" class="paperdoll-admin-form">
				<?php wp_nonce_field( 'paperdoll_banners_management' ); ?>
				<input type="hidden" name="paperdoll_banner_action" value="sort_banners">
				<h2><?php esc_html_e( 'Urutan Banner (Drag & Drop)', 'paperdoll-shop' ); ?></h2>
				<ul class="paperdoll-sortable" data-sortable>
					<?php foreach ( $banners as $banner ) : ?>
						<li>
							<input type="hidden" name="banner_order[]" value="<?php echo esc_attr( (string) $banner->ID ); ?>">
							<?php echo esc_html( get_the_title( $banner ) ); ?>
							<?php if ( has_post_thumbnail( $banner ) ) : ?>
								<div class="paperdoll-image-preview"><?php echo get_the_post_thumbnail( $banner, [ 200, 80 ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Simpan Urutan', 'paperdoll-shop' ); ?></button></p>
			</form>

			<h2><?php esc_html_e( 'Hapus Banner', 'paperdoll-shop' ); ?></h2>
			<table class="widefat striped">
				<thead><tr><th>ID</th><th>Banner</th><th>Aksi</th></tr></thead>
				<tbody>
				<?php foreach ( $banners as $banner ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $banner->ID ); ?></td>
						<td><?php echo esc_html( get_the_title( $banner ) ); ?></td>
						<td>
							<form method="post">
								<?php wp_nonce_field( 'paperdoll_banners_management' ); ?>
								<input type="hidden" name="paperdoll_banner_action" value="delete_banner">
								<input type="hidden" name="banner_id" value="<?php echo esc_attr( (string) $banner->ID ); ?>">
								<button type="submit" class="button button-small" onclick="return confirm('Hapus banner ini?');"><?php esc_html_e( 'Hapus', 'paperdoll-shop' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
