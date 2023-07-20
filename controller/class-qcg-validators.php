<?php
/**
 * All plugin validators here
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'QCG_Validators' ) ) {

	class QCG_Validators {

		/**
		 * Validator for preset edit fields
		 *
		 * @return array
		 */
		public static function validate_fields() {
			if ( empty( $_POST ) ) {
				return array();
			}

			$result = array();
			foreach ( $_POST as $key => $val ) {
				switch ( $key ) {
					case 'api_key':
						$apikey            = stripslashes( sanitize_text_field( $val ) );
						$result['api_key'] = $apikey;
						break;
					case 'req_limit':
						$limit               = QCG_Validators::clear_digits( $val );
						$result['req_limit'] = $limit;
						break;
					case 'allow_guest':
						$result['allow_guest'] = (bool) $val;
						break;
					case 'model':
						$model           = stripslashes( sanitize_text_field( $val ) );
						$result['model'] = $model;
						break;
				}
			}

			return array( 'qcg_settings' => $result );
		}

		/**
		 * Sanitize string to integer value
		 *
		 * @param $text
		 *
		 * @return int
		 */
		public static function clear_digits( $text ) {
			return intval( preg_replace( '@[^\d]+@si', '', $text ) );
		}
	}
}
