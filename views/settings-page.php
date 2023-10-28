<?php
defined( 'ABSPATH' ) || exit;

/* @var array $settings */
/* @var array $models */
/* @var array $errors */

?>
<div class="wrap">
    <h2 id="chat-with-gpt-settings"><?php esc_attr_e( 'Chat with GPT settings', 'chat-with-gpt' ) ?></h2>

	<?php if ( is_wp_error( $errors ) ): ?>
        <div class="notice notice-error">
            <p style="color:red; font-weight: bold;"><?php echo esc_html( $errors->get_error_message() ); ?></p>
        </div>
	<?php else: ?>
		<?php do_action( 'qcg_success_msg' ); ?>
	<?php endif; ?>

    <p><?php esc_attr_e( 'Setting up your account to interact with plugin', 'chat-with-gpt' ); ?></p>
    <form method="post" name="qcg-settings" id="qcg-settings" class="validate" novalidate="novalidate">
        <input name="action" type="hidden" value="qcg-settings"/>
		<?php wp_nonce_field( 'qcg-settings', '_wpnonce_qcg_nonce' ); ?>
		<?php
		// Load up the passed data, else set to a default.
		$creating = isset( $_POST['qcg-settings'] );

		$api_key     = esc_attr( sanitize_text_field( $creating && isset( $_POST['api_key'] ) ? $_POST['api_key'] : ( $settings['api_key'] ?? '' ) ) );
		$req_limit   = esc_attr( sanitize_text_field( $creating && isset( $_POST['req_limit'] ) ? $_POST['req_limit'] : ( $settings['req_limit'] ?? '' ) ) );
		$allow_guest = esc_attr( rest_sanitize_boolean( $creating && isset( $_POST['allow_guest'] ) ? $_POST['allow_guest'] : ( $settings['allow_guest'] ?? false ) ) );
		$model       = esc_attr( sanitize_text_field( $creating && isset( $_POST['model'] ) ? $_POST['model'] : ( $settings['model'] ?? '' ) ) );
		?>

        <table class="form-table" role="presentation">
            <tr class="form-field form-required">
                <th scope="row"><label for="user_login"><?php esc_html_e( 'chatGPT Api key', 'chat-with-gpt' ); ?> <span class="description"><?php esc_html_e( '(required)', 'chat-with-gpt' ); ?></span></label></th>
                <td><input name="api_key" type="text" id="api_key" value="<?php echo esc_attr( $api_key ); ?>" aria-required="true" autocapitalize="none" autocorrect="off" autocomplete="off" maxlength="60"/></td>
            </tr>
            <tr class="form-field form-required">
                <th scope="row"><label for="user_login"><?php esc_html_e( 'Requests limit per user per day', 'chat-with-gpt' ); ?> <span class="description"><?php esc_html_e( '(required)', 'chat-with-gpt' ); ?></span></label></th>
                <td><input name="req_limit" type="text" id="req_limit" value="<?php echo esc_attr( $req_limit ); ?>" aria-required="true" style="width:55px;"/></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Allow guest customers usage', 'chat-with-gpt' ); ?></th>
                <td>
                    <input type="checkbox" name="allow_guest" id="allow_guest" value="1" <?php checked( esc_attr( $allow_guest ) ); ?> />
                    <label for="allow_guest"><?php esc_html_e( 'Show chat for guest users', 'chat-with-gpt' ); ?></label>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="model"><?php esc_html_e( 'Select model', 'chat-with-gpt' ); ?></label></th>
                <td>
                    <select name="model" id="model">
						<?php foreach ( $models as $template ) {
							$selected = selected( $model, $template, false ); ?>
                            <option value="<?php echo esc_attr( $template ) ?>" <?php echo $selected ?>> <?php echo esc_html( $template ) ?> </option>
						<?php } ?>
                    </select>
                </td>
            </tr>
        </table>
		<?php submit_button( esc_html__( 'Save settings', 'chat-with-gpt' ), 'primary', 'qcg-settings', true, array( 'id' => 'qcg-settings-wrap' ) ); ?>
    </form>
</div> <!-- .wrap -->
