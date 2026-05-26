<?php
/**
 * Manajemen produk CRUD + bulk edit.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'paperdoll_register_products_management_menu' ) ) {
	function paperdoll_register_products_management_menu() {
		add_submenu_page(
			'paperdoll-dashboard',
			__( 'Manajemen Produk', 'paperdoll-shop' ),
			__( 'Produk', 'paperdoll-shop' ),
			'manage_options',
			'paperdoll-products-management',
			'paperdoll_render_products_management_page'
		);
	}
}
add_action( 'admin_menu', 'paperdoll_register_products_management_menu' );

if ( ! function_exists( 'paperdoll_handle_products_management_actions' ) ) {
	function paperdoll_handle_products_management_actions() {
		if ( ! isset( $_POST['paperdoll_products_action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'paperdoll_products_management' );
		$action = sanitize_key( wp_unslash( $_POST['paperdoll_products_action'] ) );

		if ( 'save_product' === $action ) {
			$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
			$product_name = sanitize_text_field( wp_unslash( $_POST['product_name'] ?? '' ) );
			$content      = sanitize_textarea_field( wp_unslash( $_POST['product_desc'] ?? '' ) );
			$category_id  = isset( $_POST['product_category'] ) ? absint( $_POST['product_category'] ) : 0;
			$thumbnail_id = isset( $_POST['product_image_id'] ) ? absint( $_POST['product_image_id'] ) : 0;

			$post_data = [
				'post_type'    => 'paperdoll_produk',
				'post_title'   => $product_name,
				'post_content' => $content,
				'post_status'  => 'publish',
			];

			if ( $product_id > 0 ) {
				$post_data['ID'] = $product_id;
				$product_id      = wp_update_post( $post_data );
			} else {
				$product_id = wp_insert_post( $post_data );
			}

			if ( $product_id && ! is_wp_error( $product_id ) ) {
				update_post_meta( $product_id, '_paperdoll_price', sanitize_text_field( wp_unslash( $_POST['product_price'] ?? '' ) ) );
				update_post_meta( $product_id, '_paperdoll_original_price', sanitize_text_field( wp_unslash( $_POST['product_original_price'] ?? '' ) ) );
				update_post_meta( $product_id, '_paperdoll_discount', sanitize_text_field( wp_unslash( $_POST['product_discount'] ?? '' ) ) );
				update_post_meta( $product_id, '_paperdoll_rating', sanitize_text_field( wp_unslash( $_POST['product_rating'] ?? '' ) ) );
				update_post_meta( $product_id, '_paperdoll_sold', sanitize_text_field( wp_unslash( $_POST['product_sold'] ?? '' ) ) );
				update_post_meta( $product_id, '_paperdoll_is_mall', isset( $_POST['product_is_mall'] ) ? '1' : '0' );

				if ( $category_id > 0 ) {
					wp_set_object_terms( $product_id, [ $category_id ], 'paperdoll_kategori' );
				}
				if ( $thumbnail_id > 0 ) {
					set_post_thumbnail( $product_id, $thumbnail_id );
				}
			}
		}

		if ( 'bulk_update' === $action ) {
			$selected_ids  = array_map( 'absint', wp_unslash( $_POST['selected_products'] ?? [] ) );
			$bulk_is_mall  = sanitize_text_field( wp_unslash( $_POST['bulk_is_mall'] ?? '' ) );
			$bulk_discount = sanitize_text_field( wp_unslash( $_POST['bulk_discount'] ?? '' ) );

			foreach ( $selected_ids as $selected_id ) {
				if ( '1' === $bulk_is_mall || '0' === $bulk_is_mall ) {
					update_post_meta( $selected_id, '_paperdoll_is_mall', $bulk_is_mall );
				}
				if ( '' !== $bulk_discount ) {
					update_post_meta( $selected_id, '_paperdoll_discount', $bulk_discount );
				}
			}
		}
	}
}
add_action( 'admin_init', 'paperdoll_handle_products_management_actions' );

if ( ! function_exists( 'paperdoll_render_products_management_page' ) ) {
	function paperdoll_render_products_management_page() {
		$products   = get_posts(
			[
				'post_type'      => 'paperdoll_produk',
				'post_status'    => 'any',
				'posts_per_page' => (int) apply_filters( 'paperdoll_admin_products_posts_per_page', 100 ),
				'orderby'        => 'date',
				'order'          => 'DESC',
			]
		);
		$categories = get_terms(
			[
				'taxonomy'   => 'paperdoll_kategori',
				'hide_empty' => false,
			]
		);
		?>
		<div class="wrap paperdoll-admin-wrap">
			<h1><?php esc_html_e( 'Kustomisasi Edit & Tambah Produk', 'paperdoll-shop' ); ?></h1>

			<form method="post" class="paperdoll-admin-form">
				<?php wp_nonce_field( 'paperdoll_products_management' ); ?>
				<input type="hidden" name="paperdoll_products_action" value="save_product">
				<h2><?php esc_html_e( 'Tambah / Edit Produk', 'paperdoll-shop' ); ?></h2>
				<div class="paperdoll-form-grid">
					<label><?php esc_html_e( 'ID Produk (isi untuk edit)', 'paperdoll-shop' ); ?><input type="number" name="product_id" min="0"></label>
					<label><?php esc_html_e( 'Nama Produk', 'paperdoll-shop' ); ?><input type="text" name="product_name" required></label>
					<label><?php esc_html_e( 'Harga', 'paperdoll-shop' ); ?><input type="number" name="product_price" min="0"></label>
					<label><?php esc_html_e( 'Harga Asli', 'paperdoll-shop' ); ?><input type="number" name="product_original_price" min="0"></label>
					<label><?php esc_html_e( 'Diskon', 'paperdoll-shop' ); ?><input type="text" name="product_discount" placeholder="10% atau 10000"></label>
					<label><?php esc_html_e( 'Rating', 'paperdoll-shop' ); ?><input type="number" name="product_rating" step="0.1" min="0" max="5"></label>
					<label><?php esc_html_e( 'Terjual', 'paperdoll-shop' ); ?><input type="text" name="product_sold"></label>
					<label><?php esc_html_e( 'Kategori', 'paperdoll-shop' ); ?>
						<select name="product_category">
							<option value="0"><?php esc_html_e( '-- Pilih Kategori --', 'paperdoll-shop' ); ?></option>
							<?php foreach ( $categories as $category ) : ?>
								<option value="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="paperdoll-inline-check"><input type="checkbox" name="product_is_mall" value="1"> <?php esc_html_e( 'Tampilkan di Paperdoll Mall', 'paperdoll-shop' ); ?></label>
					<label><?php esc_html_e( 'Deskripsi Produk', 'paperdoll-shop' ); ?><textarea name="product_desc" rows="4"></textarea></label>
					<div>
						<label><?php esc_html_e( 'Gambar Produk', 'paperdoll-shop' ); ?></label>
						<input type="hidden" name="product_image_id" class="paperdoll-media-id" value="">
						<button type="button" class="button paperdoll-media-upload"><?php esc_html_e( 'Upload Gambar', 'paperdoll-shop' ); ?></button>
						<div class="paperdoll-image-preview"></div>
					</div>
				</div>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Simpan Produk', 'paperdoll-shop' ); ?></button></p>
			</form>

			<form method="post" class="paperdoll-admin-form">
				<?php wp_nonce_field( 'paperdoll_products_management' ); ?>
				<input type="hidden" name="paperdoll_products_action" value="bulk_update">
				<h2><?php esc_html_e( 'Bulk Edit Produk', 'paperdoll-shop' ); ?></h2>
				<div class="paperdoll-form-grid paperdoll-bulk-controls">
					<label><?php esc_html_e( 'Set Mall', 'paperdoll-shop' ); ?>
						<select name="bulk_is_mall">
							<option value=""><?php esc_html_e( '-- Tidak diubah --', 'paperdoll-shop' ); ?></option>
							<option value="1"><?php esc_html_e( 'Ya', 'paperdoll-shop' ); ?></option>
							<option value="0"><?php esc_html_e( 'Tidak', 'paperdoll-shop' ); ?></option>
						</select>
					</label>
					<label><?php esc_html_e( 'Set Diskon', 'paperdoll-shop' ); ?><input type="text" name="bulk_discount" placeholder="cth 15%"></label>
				</div>
				<table class="widefat striped">
					<thead>
					<tr>
						<th><input type="checkbox" class="paperdoll-check-all"></th>
						<th>ID</th>
						<th><?php esc_html_e( 'Produk', 'paperdoll-shop' ); ?></th>
						<th><?php esc_html_e( 'Harga', 'paperdoll-shop' ); ?></th>
						<th><?php esc_html_e( 'Diskon', 'paperdoll-shop' ); ?></th>
						<th><?php esc_html_e( 'Mall', 'paperdoll-shop' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $products as $product ) : ?>
						<tr>
							<td><input type="checkbox" name="selected_products[]" value="<?php echo esc_attr( $product->ID ); ?>"></td>
							<td><?php echo esc_html( (string) $product->ID ); ?></td>
							<td><?php echo esc_html( get_the_title( $product ) ); ?></td>
							<td><?php echo esc_html( (string) get_post_meta( $product->ID, '_paperdoll_price', true ) ); ?></td>
							<td><?php echo esc_html( (string) get_post_meta( $product->ID, '_paperdoll_discount', true ) ); ?></td>
							<td><?php echo get_post_meta( $product->ID, '_paperdoll_is_mall', true ) ? '✓' : '-'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Apply Bulk Edit', 'paperdoll-shop' ); ?></button></p>
			</form>
		</div>
		<?php
	}
}
