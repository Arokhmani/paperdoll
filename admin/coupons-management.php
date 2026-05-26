<?php
/**
 * Manajemen kupon dan diskon otomatis.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'paperdoll_maybe_migrate_coupon_table' ) ) {
	function paperdoll_maybe_migrate_coupon_table() {
		if ( get_option( 'paperdoll_coupon_table_version' ) === '1' ) {
			return;
		}

		global $wpdb;
		$table_name      = $wpdb->prefix . 'paperdoll_coupons';
		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			code varchar(100) NOT NULL,
			discount_type varchar(10) NOT NULL DEFAULT 'percent',
			discount_value decimal(12,2) NOT NULL DEFAULT 0,
			min_purchase decimal(12,2) NOT NULL DEFAULT 0,
			starts_at date DEFAULT NULL,
			ends_at date DEFAULT NULL,
			usage_limit_per_user int(11) NOT NULL DEFAULT 1,
			is_active tinyint(1) NOT NULL DEFAULT 1,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY code (code)
		) {$charset_collate};";
		dbDelta( $sql );

		update_option( 'paperdoll_coupon_table_version', '1' );
	}
}
add_action( 'admin_init', 'paperdoll_maybe_migrate_coupon_table' );

if ( ! function_exists( 'paperdoll_register_coupons_management_menu' ) ) {
	function paperdoll_register_coupons_management_menu() {
		add_submenu_page(
			'paperdoll-dashboard',
			__( 'Kupon & Diskon', 'paperdoll-shop' ),
			__( 'Kupon & Diskon', 'paperdoll-shop' ),
			'manage_options',
			'paperdoll-coupons-management',
			'paperdoll_render_coupons_management_page'
		);
	}
}
add_action( 'admin_menu', 'paperdoll_register_coupons_management_menu' );

if ( ! function_exists( 'paperdoll_handle_coupons_management_actions' ) ) {
	function paperdoll_handle_coupons_management_actions() {
		if ( ! isset( $_POST['paperdoll_coupon_action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'paperdoll_coupons_management' );
		global $wpdb;
		$table_name = $wpdb->prefix . 'paperdoll_coupons';
		$action     = sanitize_key( wp_unslash( $_POST['paperdoll_coupon_action'] ) );

		if ( 'save_coupon' === $action ) {
			$coupon_data = [
				'code'                 => strtoupper( sanitize_text_field( wp_unslash( $_POST['coupon_code'] ?? '' ) ) ),
				'discount_type'        => sanitize_key( wp_unslash( $_POST['coupon_discount_type'] ?? 'percent' ) ),
				'discount_value'       => (float) wp_unslash( $_POST['coupon_discount_value'] ?? 0 ),
				'min_purchase'         => (float) wp_unslash( $_POST['coupon_min_purchase'] ?? 0 ),
				'starts_at'            => sanitize_text_field( wp_unslash( $_POST['coupon_starts_at'] ?? '' ) ),
				'ends_at'              => sanitize_text_field( wp_unslash( $_POST['coupon_ends_at'] ?? '' ) ),
				'usage_limit_per_user' => absint( wp_unslash( $_POST['coupon_usage_limit'] ?? 1 ) ),
				'is_active'            => isset( $_POST['coupon_is_active'] ) ? 1 : 0,
			];

			$coupon_id = absint( wp_unslash( $_POST['coupon_id'] ?? 0 ) );
			if ( $coupon_id > 0 ) {
				$wpdb->update( $table_name, $coupon_data, [ 'id' => $coupon_id ] );
			} else {
				$wpdb->insert( $table_name, $coupon_data );
			}
		}

		if ( 'delete_coupon' === $action ) {
			$delete_id = absint( wp_unslash( $_POST['coupon_id'] ?? 0 ) );
			if ( $delete_id > 0 ) {
				$wpdb->delete( $table_name, [ 'id' => $delete_id ] );
			}
		}

		if ( 'save_auto_discount' === $action ) {
			update_option( 'paperdoll_auto_discount_category', absint( wp_unslash( $_POST['auto_discount_category'] ?? 0 ) ) );
			update_option( 'paperdoll_auto_discount_min_total', (float) wp_unslash( $_POST['auto_discount_min_total'] ?? 0 ) );
			update_option( 'paperdoll_auto_discount_value', sanitize_text_field( wp_unslash( $_POST['auto_discount_value'] ?? '' ) ) );
		}
	}
}
add_action( 'admin_init', 'paperdoll_handle_coupons_management_actions' );

if ( ! function_exists( 'paperdoll_render_coupons_management_page' ) ) {
	function paperdoll_render_coupons_management_page() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'paperdoll_coupons';
		$coupons    = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY created_at DESC" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$categories = get_terms(
			[
				'taxonomy'   => 'paperdoll_kategori',
				'hide_empty' => false,
			]
		);
		?>
		<div class="wrap paperdoll-admin-wrap">
			<h1><?php esc_html_e( 'Kustomisasi Kupon & Diskon Otomatis', 'paperdoll-shop' ); ?></h1>

			<form method="post" class="paperdoll-admin-form">
				<?php wp_nonce_field( 'paperdoll_coupons_management' ); ?>
				<input type="hidden" name="paperdoll_coupon_action" value="save_coupon">
				<h2><?php esc_html_e( 'Buat / Edit Kupon', 'paperdoll-shop' ); ?></h2>
				<div class="paperdoll-form-grid">
					<label>ID <input type="number" min="0" name="coupon_id"></label>
					<label><?php esc_html_e( 'Kode Kupon', 'paperdoll-shop' ); ?><input type="text" required name="coupon_code"></label>
					<label><?php esc_html_e( 'Jenis Diskon', 'paperdoll-shop' ); ?>
						<select name="coupon_discount_type">
							<option value="percent">%</option>
							<option value="fixed">Rp</option>
						</select>
					</label>
					<label><?php esc_html_e( 'Nilai Diskon', 'paperdoll-shop' ); ?><input type="number" min="0" step="0.01" name="coupon_discount_value"></label>
					<label><?php esc_html_e( 'Minimum Pembelian', 'paperdoll-shop' ); ?><input type="number" min="0" step="0.01" name="coupon_min_purchase"></label>
					<label><?php esc_html_e( 'Berlaku Dari', 'paperdoll-shop' ); ?><input type="date" name="coupon_starts_at"></label>
					<label><?php esc_html_e( 'Berlaku Hingga', 'paperdoll-shop' ); ?><input type="date" name="coupon_ends_at"></label>
					<label><?php esc_html_e( 'Limit per User', 'paperdoll-shop' ); ?><input type="number" min="1" name="coupon_usage_limit" value="1"></label>
					<label class="paperdoll-inline-check"><input type="checkbox" name="coupon_is_active" checked value="1"> <?php esc_html_e( 'Aktif', 'paperdoll-shop' ); ?></label>
				</div>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Simpan Kupon', 'paperdoll-shop' ); ?></button></p>
			</form>

			<form method="post" class="paperdoll-admin-form">
				<?php wp_nonce_field( 'paperdoll_coupons_management' ); ?>
				<input type="hidden" name="paperdoll_coupon_action" value="save_auto_discount">
				<h2><?php esc_html_e( 'Diskon Otomatis', 'paperdoll-shop' ); ?></h2>
				<div class="paperdoll-form-grid">
					<label><?php esc_html_e( 'Kategori', 'paperdoll-shop' ); ?>
						<select name="auto_discount_category">
							<option value="0"><?php esc_html_e( '-- Semua Kategori --', 'paperdoll-shop' ); ?></option>
							<?php foreach ( $categories as $category ) : ?>
								<option value="<?php echo esc_attr( $category->term_id ); ?>" <?php selected( (int) get_option( 'paperdoll_auto_discount_category', 0 ), (int) $category->term_id ); ?>><?php echo esc_html( $category->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label><?php esc_html_e( 'Minimum Total Belanja', 'paperdoll-shop' ); ?><input type="number" min="0" step="0.01" name="auto_discount_min_total" value="<?php echo esc_attr( (string) get_option( 'paperdoll_auto_discount_min_total', 0 ) ); ?>"></label>
					<label><?php esc_html_e( 'Nilai Diskon Otomatis', 'paperdoll-shop' ); ?><input type="text" name="auto_discount_value" value="<?php echo esc_attr( (string) get_option( 'paperdoll_auto_discount_value', '' ) ); ?>"></label>
				</div>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Simpan Diskon Otomatis', 'paperdoll-shop' ); ?></button></p>
			</form>

			<h2><?php esc_html_e( 'Daftar Kupon Aktif', 'paperdoll-shop' ); ?></h2>
			<table class="widefat striped">
				<thead><tr><th>ID</th><th>Kode</th><th>Diskon</th><th>Min</th><th>Periode</th><th>Limit/User</th><th>Aktif</th><th>Aksi</th></tr></thead>
				<tbody>
				<?php foreach ( $coupons as $coupon ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $coupon->id ); ?></td>
						<td><?php echo esc_html( $coupon->code ); ?></td>
						<td><?php echo esc_html( ( 'percent' === $coupon->discount_type ? $coupon->discount_value . '%' : 'Rp ' . $coupon->discount_value ) ); ?></td>
						<td><?php echo esc_html( (string) $coupon->min_purchase ); ?></td>
						<td><?php echo esc_html( (string) $coupon->starts_at . ' - ' . (string) $coupon->ends_at ); ?></td>
						<td><?php echo esc_html( (string) $coupon->usage_limit_per_user ); ?></td>
						<td><?php echo (int) $coupon->is_active ? '✓' : '-'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
						<td>
							<form method="post">
								<?php wp_nonce_field( 'paperdoll_coupons_management' ); ?>
								<input type="hidden" name="paperdoll_coupon_action" value="delete_coupon">
								<input type="hidden" name="coupon_id" value="<?php echo esc_attr( (string) $coupon->id ); ?>">
								<button class="button button-small" type="submit" onclick="return confirm('Hapus kupon ini?');"><?php esc_html_e( 'Hapus', 'paperdoll-shop' ); ?></button>
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
