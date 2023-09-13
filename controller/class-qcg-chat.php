<?php
/**
 * The Chat communication controller.
 *
 * It is used to define startup settings and requirements
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'QCG_Chat' ) ) {
	class QCG_Chat {
		/**
		 * AJAX callback for chat
		 *
		 * @return void
		 */
		public static function communicate() {
			$settings = get_option( 'qcg_settings' );
			if ( ! isset( $settings['authenticated'] ) || ! $settings['authenticated'] ) {
				die( json_encode( array(
					'status' => 'qcgInvalidApiKey',
					'data'   => __( 'Chat is broken. Please contact with administration', 'chat-with-gpt' )
				) ) );
			}

			$ask = json_decode( stripslashes_deep( $_POST['q'] ), true );

			$ip = self::get_user_ip_address() ?? '';
//			$ip              = '127.0.0.1'; //fixme:debug
			$is_limit_exceed = self::is_user_limits_exceed( $ip, $settings );
			if ( $is_limit_exceed ) {
				die( json_encode( array(
					'status' => 'qcgLimitPerUserPerDate',
					'data'   => __( 'The daily request limit has been reached', 'chat-with-gpt' )
				) ) );
			}

			$conn = new QCG_Connector( $settings['api_key'] );
			$ans  = $conn->chatCompetition( $ask, $settings );
			if ( ! $ans ) {
				die( __( 'ChatGPT don`t answer. Please try again', 'chat-with-gpt' ) );
			}
			die( json_encode( array(
				'status' => 'success',
				'data'   => $ans
			) ) );
		}

		/**
		 * Get user ip address by headers
		 *
		 * @return string
		 */
		public static function get_user_ip_address(): string {
			foreach ( array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ) as $key ) {
				if ( array_key_exists( $key, $_SERVER ) === true ) {
					foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
						$ip = trim( $ip );

						if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
							return $ip;
						}
					}
				}
			}

			return '';
		}

		public static function send_email() {
			$clientEmail = wp_specialchars_decode( $_POST['email'] );
			$email       = get_site_option( 'admin_email' );
			$subject     = printf( __( 'New Email from ChatGPT form on %s', 'chat-with-gpt' ), site_url() );

			$message = sprintf(
				__( 'New email received from ChatGPT plugin form 

Email: %1$s', 'chat-with-gpt' ),
				$clientEmail
			);

			$header = sprintf(
				'From: "%1$s" <%2$s>',
				_x( 'Site Admin', 'email "From" field' ),
				$email
			);

			$sent = wp_mail(
				$clientEmail,
				wp_specialchars_decode( $subject ),
				$message,
				$header
			);

			if ( is_bool( $sent ) ) {
				if ( $sent ) {
					die( __( 'Your email has been successfully sent. We will connect with you as soon as possible.', 'chat-with-gpt' ) );
				} else {
					die( __( 'Something went wrong. Please try tomorrow', 'chat-with-gpt' ) );
				}
			} else {
				die( $sent );
			}
		}

		/**
		 * Check user request limit and update +1 on use
		 *
		 * @param $ip string - User ip address
		 * @param $settings array - plugin settings where limit per user saved
		 *
		 * @return bool - true if today user request limit exceed
		 */
		private static function is_user_limits_exceed( string $ip, array $settings ): bool {
			global $wpdb;
			$table_name = $wpdb->prefix . QCG_Common::PLUGIN_DB_TABLE_NAME;
			$today      = date( 'Y-m-d' );

			$user = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %s WHERE ip_address=%s;', $table_name, $ip ) );

			if ( empty( $user ) ) {
				$wpdb->insert( $table_name, array( 'dt' => $today, 'ip_address' => $ip, 'request_count' => 1 ), array( '%s', '%s', '%d' ) );

				return false;
			}

			if ( $user->dt !== $today ) {
				$wpdb->update( $table_name, array( 'dt' => $today, 'request_count' => 1 ), array( 'id' => $user->id ), array( '%s', '%d' ), array( '%d' ) );

				return false;
			}

			if ( $user->request_count > $settings['req_limit'] ) {
				return true;
			}

			$wpdb->update( $table_name, array( 'request_count' => $user->request_count + 1 ), array( 'id' => $user->id ), array( '%d' ), array( '%d' ) );

			return false;
		}
	}
}
