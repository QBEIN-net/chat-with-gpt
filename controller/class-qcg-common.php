<?php
/**
 * The core plugin class.
 *
 * It is used to define startup settings and requirements
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'QCG_Common' ) ) {

	class QCG_Common {
		/**
		 * @var string Plugin common system name
		 */
		const PLUGIN_SYSTEM_NAME = 'chat-with-gpt';

		/**
		 * @var string Human readable plugin name for front end
		 */
		const PLUGIN_HUMAN_NAME = 'Chat with GPT';

		/**
		 * @var string Plugin table name for restrict user access on request limit
		 */
		const PLUGIN_DB_TABLE_NAME = 'qcg_user_access';

		/**
		 * @var string Path to plugin root directory
		 */
		public $plugin_base_path = '';

		public function __construct() {
			$this->plugin_base_path = self::get_plugin_root_path();

			$this->load_dependencies();
			$this->set_locale();
		}

		/**
		 * Plugin entry point
		 */
		public function run() {
			$this->define_admin_hooks();

			add_action( 'init', function () {
				$settings = get_option( 'qcg_settings', array(
					'api_key'       => '',
					'req_limit'     => 10,
					'allow_guest'   => false,
					'model'         => 'gpt-3.5-turbo',
					'authenticated' => false
				) );


				if ( $settings['authenticated'] ) {
					if ( $settings['allow_guest'] || is_user_logged_in() ) {
						$this->define_common_hooks();
					}
				}
			} );

		}

		/**
		 * Use for register module options
		 */
		public static function activate() {
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();

			$table_name = $wpdb->prefix . self::PLUGIN_DB_TABLE_NAME;

			$sql = "
CREATE TABLE " . $table_name . " (
  id int(11) NOT NULL AUTO_INCREMENT,
  dt date NOT NULL,
  ip_address varchar(15),
  request_count int(11),
  PRIMARY KEY  (id),
  KEY ip_address (ip_address)
) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

		/**
		 * Do all jobs when module deactivated
		 */
		public static function deactivate() {
			global $wpdb;
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}" . self::PLUGIN_DB_TABLE_NAME );
		}

		/**
		 * Get path or uri for plugin based folder
		 *
		 * @param string $type switch path or url for result
		 *
		 * @return string
		 */
		public static function get_plugin_root_path( $type = 'path' ): string {
			if ( 'url' == $type ) {
				return plugin_dir_url( dirname( __FILE__ ) );
			}

			return plugin_dir_path( dirname( __FILE__ ) );
		}

		/**
		 * Load plugin files
		 */
		private function load_dependencies() {
			require_once $this->plugin_base_path . 'controller/class-qcg-validators.php';
			require_once $this->plugin_base_path . 'vendor/autoload.php';
			require_once $this->plugin_base_path . 'controller/class-qcg-connector.php';
			require_once $this->plugin_base_path . 'controller/class-qcg-chat.php';
		}

		/**
		 * Add localization support
		 */
		private function set_locale() {
			load_plugin_textdomain(
				self::PLUGIN_SYSTEM_NAME,
				false,
				self::PLUGIN_SYSTEM_NAME . '/languages/'
			);
		}

		/**
		 * Print success messages
		 */
		public static function print_success() {
			echo '<div class="notice notice-success">';
			echo '<p style="color:green;font-weight: bold;">' . __( 'Settings successfully updated', self::PLUGIN_SYSTEM_NAME ) . '</p>';
			echo '</div>';
		}

		/**
		 * Add actions and work for admin part of plugin
		 */
		private function define_admin_hooks() {
			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'register_settings_pages' ) );
				add_filter(
					"plugin_action_links_" . plugin_basename( dirname( __DIR__ ) . '/bootstrap.php' ),
					array( $this, 'plugin_action_links' ),
					10,
					4
				);
			}
		}

		/**
		 * Add actions and work for common part of plugin
		 */
		private function define_common_hooks() {
			// Load Chat html
			add_action( 'wp_footer', array( $this, 'qcg_footer_code' ) );
			// Load js for handling chat
			add_action( 'wp_enqueue_scripts', array( $this, 'qcg_set_data' ), 99 );
			wp_enqueue_style( 'qcg-chat-style', self::get_plugin_root_path('url') . 'asset/chat.min.css', array(),'0.0.11' );
			// Register ajax requests for Chat
			add_action( 'wp_ajax_qcg_ask_request', 'QCG_Chat::communicate' );
			add_action( 'wp_ajax_nopriv_qcg_ask_request', 'QCG_Chat::communicate' );
			add_action( 'wp_ajax_qcg_send_email', 'QCG_Chat::send_email' );
			add_action( 'wp_ajax_nopriv_qcg_send_email', 'QCG_Chat::send_email' );
		}

		/**
		 * Load js
		 *
		 * @return void
		 */
		public function qcg_set_data() {
			wp_enqueue_script( 'chat-with-gpt-common', self::get_plugin_root_path('url') . 'asset/common.min.js', array( 'jquery' ), '0.0.11', true );

			wp_localize_script( 'chat-with-gpt-common', 'qcgmyajax',
				array(
					'url' => admin_url( 'admin-ajax.php' )
				)
			);
		}

		/**
		 * Past Chat code in site footer
		 *
		 * @return void
		 */
		public function qcg_footer_code() {
			echo self::render( 'chat' );
		}

		/**
		 * Add link to plugin settings page im plugins list
		 *
		 * @param $actions
		 * @param $plugin_file
		 * @param $plugin_data
		 * @param $context
		 *
		 * @return mixed
		 */
		public static function plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
			array_unshift( $actions,
				sprintf( '<a href="%s" aria-label="%s">%s</a>',
					menu_page_url( self::PLUGIN_SYSTEM_NAME, false ),
					esc_attr__( 'open settings', self::PLUGIN_SYSTEM_NAME ),
					esc_html__( 'open settings', self::PLUGIN_SYSTEM_NAME )
				)
			);

			return $actions;
		}

		/**
		 * Register tools page
		 */
		public function register_settings_pages() {
			add_submenu_page(
				'tools.php',
				__( 'Chat with GPT', self::PLUGIN_SYSTEM_NAME ),
				__( 'Chat with GPT', self::PLUGIN_SYSTEM_NAME ),
				'administrator',
				self::PLUGIN_SYSTEM_NAME,
				__CLASS__ . '::markup_settings_page'
			);
		}

		/**
		 * Markup for admin settings plugin pages
		 */
		public static function markup_settings_page() {
			$settings = get_option( 'qcg_settings', array(
				'api_key'       => '',
				'req_limit'     => 10,
				'allow_guest'   => false,
				'model'         => 'gpt-3.5-turbo',
				'authenticated' => false
			) );

			$myErrors = '';
			if ( ! empty( $_POST ) ) {
				if ( ! isset( $_POST['_wpnonce_qcg_nonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce_qcg_nonce'], 'qcg-settings' ) ) {
					print 'Sorry, your nonce did not verify.';
					exit;
				}

				$post_data = QCG_Validators::validate_fields();
				if ( isset( $post_data['qcg_settings'] ) ) {
					$settings = $post_data['qcg_settings'];
				}
			}

			$conn = new QCG_Connector( $settings['api_key'] );
			list( $error, $models ) = $conn->get_models();
			$settings['authenticated'] = true;
			if ( ! ! $error ) {
				$settings['authenticated'] = false;
				$myErrors                  = new WP_Error();
				if ( $error === 'Unauthorized' ) {
					$myErrors->add( 'api_key', __( 'Please provide correct api key to interact with openai chatGPT!', self::PLUGIN_SYSTEM_NAME ) );
				} else {
					$myErrors->add( 'api_key', __( 'Can`t connect to openai chatGPT: ' . $error, self::PLUGIN_SYSTEM_NAME ) );
				}
				$models = QCG_Connector::$default_models;
			}

			if ( isset( $post_data['qcg_settings'] ) ) {
				update_option( 'qcg_settings', $settings );
				add_action( 'qcg_success_msg', 'QCG_Common::print_success' );
			}

			echo self::render( 'settings-page', array( 'settings' => $settings, 'models' => $models, 'errors' => $myErrors ) );
		}

		/**
		 * Render plugin views function
		 *
		 * @param $name
		 * @param array|null $vars
		 *
		 * @return false|string
		 */
		public static function render( $name, $vars = null ) {
			if ( is_array( $vars ) ) {
				extract( $vars );
			}
			ob_start();
			$name = str_replace( '.php', '', $name ) . '.php';
			$path = self::get_plugin_root_path() . 'views/' . $name;
			if ( file_exists( $path ) ) {
				require( $path );
			}

			return ob_get_clean();
		}
	}
}
