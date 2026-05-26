<?php
/**
 * Manajemen kategori dan icon.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'paperdoll_register_categories_management_menu' ) ) {
	function paperdoll_register_categories_management_menu() {
		add_submenu_page(
			'paperdoll-dashboard',
			__( 'Kategori & Icon', 'paperdoll-shop' ),
			__( 'Kategori & Icon', 'paperdoll-shop' ),
			'manage_options',
			'paperdoll-categories-management',
			'paperdoll_render_categories_management_page'
		);
	}
}
add_action( 'admin_menu', 'paperdoll_register_categories_management_menu' );

if ( ! function_exists( 'paperdoll_handle_categories_management_actions' ) ) {
	function paperdoll_handle_categories_management_actions() {
		if ( ! isset( $_POST['paperdoll_categories_action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'paperdoll_categories_management' );
		$action = sanitize_key( wp_unslash( $_POST['paperdoll_categories_action'] ) );

		if ( 'save_category' === $action ) {
			$term_id = absint( wp_unslash( $_POST['term_id'] ?? 0 ) );
			$name    = sanitize_text_field( wp_unslash( $_POST['term_name'] ?? '' ) );
			$label   = sanitize_text_field( wp_unslash( $_POST['term_label'] ?? '' ) );
			$icon_id = absint( wp_unslash( $_POST['term_icon_id'] ?? 0 ) );

			if ( $term_id > 0 ) {
				wp_update_term( $term_id, 'paperdoll_kategori', [ 'name' => $name ] );
			} elseif ( '' !== $name ) {
				$new_term = wp_insert_term( $name, 'paperdoll_kategori' );
				if ( ! is_wp_error( $new_term ) ) {
					$term_id = (int) $new_term['term_id'];
				}
			}

			if ( $term_id > 0 ) {
				update_term_meta( $term_id, '_paperdoll_cat_label', $label );
				update_term_meta( $term_id, '_paperdoll_cat_icon_id', $icon_id );
			}
		}
	}
}
add_action( 'admin_init', 'paperdoll_handle_categories_management_actions' );

if ( ! function_exists( 'paperdoll_render_categories_management_page' ) ) {
	function paperdoll_render_categories_management_page() {
		$categories = get_terms(
			[
				'taxonomy'   => 'paperdoll_kategori',
				'hide_empty' => false,
			]
		);
		?>
		<div class="wrap paperdoll-admin-wrap">
			<h1><?php esc_html_e( 'Kustomisasi Icon Kategori', 'paperdoll-shop' ); ?></h1>
			<form method="post" class="paperdoll-admin-form">
				<?php wp_nonce_field( 'paperdoll_categories_management' ); ?>
				<input type="hidden" name="paperdoll_categories_action" value="save_category">
				<h2><?php esc_html_e( 'Tambah / Edit Kategori', 'paperdoll-shop' ); ?></h2>
				<div class="paperdoll-form-grid">
					<label><?php esc_html_e( 'ID Term (isi untuk edit)', 'paperdoll-shop' ); ?><input type="number" name="term_id" min="0"></label>
					<label><?php esc_html_e( 'Nama Kategori', 'paperdoll-shop' ); ?><input type="text" name="term_name" required></label>
					<label><?php esc_html_e( 'Label Tampil', 'paperdoll-shop' ); ?><input type="text" name="term_label"></label>
					<div>
						<label><?php esc_html_e( 'Icon SVG/PNG', 'paperdoll-shop' ); ?></label>
						<input type="hidden" name="term_icon_id" class="paperdoll-media-id" value="">
						<button type="button" class="button paperdoll-media-upload"><?php esc_html_e( 'Upload Icon', 'paperdoll-shop' ); ?></button>
						<div class="paperdoll-image-preview"></div>
					</div>
				</div>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Simpan Kategori', 'paperdoll-shop' ); ?></button></p>
			</form>

			<h2><?php esc_html_e( 'Daftar Kategori', 'paperdoll-shop' ); ?></h2>
			<table class="widefat striped">
				<thead><tr><th>ID</th><th>Nama</th><th>Label Tampil</th><th>Icon</th></tr></thead>
				<tbody>
				<?php foreach ( $categories as $category ) : ?>
					<?php $icon_id = (int) get_term_meta( $category->term_id, '_paperdoll_cat_icon_id', true ); ?>
					<tr>
						<td><?php echo esc_html( (string) $category->term_id ); ?></td>
						<td><?php echo esc_html( $category->name ); ?></td>
						<td><?php echo esc_html( (string) get_term_meta( $category->term_id, '_paperdoll_cat_label', true ) ); ?></td>
						<td><?php echo $icon_id ? wp_get_attachment_image( $icon_id, [ 42, 42 ] ) : '-'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
