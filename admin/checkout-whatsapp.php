<?php
/**
 * Konfigurasi checkout WhatsApp.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'paperdoll_register_checkout_whatsapp_menu' ) ) {
	function paperdoll_register_checkout_whatsapp_menu() {
		add_submenu_page(
			'paperdoll-dashboard',
			__( 'Checkout WhatsApp', 'paperdoll-shop' ),
			__( 'Checkout WhatsApp', 'paperdoll-shop' ),
			'manage_options',
			'paperdoll-checkout-whatsapp',
			'paperdoll_render_checkout_whatsapp_page'
		);
	}
}
add_action( 'admin_menu', 'paperdoll_register_checkout_whatsapp_menu' );

if ( ! function_exists( 'paperdoll_handle_checkout_whatsapp_actions' ) ) {
	function paperdoll_handle_checkout_whatsapp_actions() {
		if ( ! isset( $_POST['paperdoll_checkout_wa_action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'paperdoll_checkout_whatsapp' );

		update_option( 'paperdoll_wa_number', sanitize_text_field( wp_unslash( $_POST['wa_number'] ?? '' ) ) );
		update_option( 'paperdoll_whatsapp_template', sanitize_textarea_field( wp_unslash( $_POST['wa_template'] ?? '' ) ) );
		update_option( 'paperdoll_enable_whatsapp_checkout', isset( $_POST['wa_enable_checkout'] ) ? '1' : '0' );
	}
}
add_action( 'admin_init', 'paperdoll_handle_checkout_whatsapp_actions' );

if ( ! function_exists( 'paperdoll_render_checkout_whatsapp_page' ) ) {
	function paperdoll_render_checkout_whatsapp_page() {
		$template = get_option( 'paperdoll_whatsapp_template', "Halo Admin, saya ingin checkout:\n- {product_name} x {qty}\n- Harga: {price}\n- Total: {total}" );
		?>
		<div class="wrap paperdoll-admin-wrap">
			<h1><?php esc_html_e( 'Checkout by WhatsApp', 'paperdoll-shop' ); ?></h1>
			<form method="post" class="paperdoll-admin-form">
				<?php wp_nonce_field( 'paperdoll_checkout_whatsapp' ); ?>
				<input type="hidden" name="paperdoll_checkout_wa_action" value="save_checkout_wa">
				<div class="paperdoll-form-grid">
					<label><?php esc_html_e( 'Nomor Admin WhatsApp', 'paperdoll-shop' ); ?><input type="text" name="wa_number" value="<?php echo esc_attr( (string) get_option( 'paperdoll_wa_number', '' ) ); ?>" placeholder="628xxxx"></label>
					<label class="paperdoll-inline-check"><input type="checkbox" name="wa_enable_checkout" value="1" <?php checked( get_option( 'paperdoll_enable_whatsapp_checkout', '1' ), '1' ); ?>> <?php esc_html_e( 'Aktifkan checkout WhatsApp', 'paperdoll-shop' ); ?></label>
					<label><?php esc_html_e( 'Template Pesan Otomatis', 'paperdoll-shop' ); ?>
						<textarea name="wa_template" rows="8"><?php echo esc_textarea( $template ); ?></textarea>
						<small><?php esc_html_e( 'Gunakan placeholder: {product_name}, {qty}, {price}, {total}', 'paperdoll-shop' ); ?></small>
					</label>
				</div>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Simpan Konfigurasi', 'paperdoll-shop' ); ?></button></p>
			</form>
		</div>
		<?php
	}
}
