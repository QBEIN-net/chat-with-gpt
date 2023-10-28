<?php
/**
 * Plugin Name: Chat with GPT
 * Description: The Ultimate Plugin for Engaging Chat and Conversations with AI
 * Plugin URI:  https://qbein.net/chat-with-gpt
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author:      Qbein
 * Author URI:  https://qbein.net/
 * Version:     1.0.0
 * License: GPLv2 or later
 * Text Domain: chat-with-gpt
 * Domain Path: /languages
 *
 * Network: false
 */

defined( 'ABSPATH' ) || exit;

define( 'QCG_REQUIRED_PHP_VERSION', '7.3' );
define( 'QCG_REQUIRED_WP_VERSION', '5.0' );

/**
 * Checks if the system requirements are met
 *
 * @return array Array of errors or false if all is ok
 */
function qcg_requirements_met(): array {
	global $wp_version;

	$errors = [];

	if ( version_compare( PHP_VERSION, QCG_REQUIRED_PHP_VERSION, '<' ) ) {
		$errors[] = printf(
			esc_html__( 'Your server is running PHP version %1$s but this plugin requires at least PHP %2$s. Please run an upgrade.', 'chat-with-gpt' ),
			PHP_VERSION,
			QCG_REQUIRED_PHP_VERSION
		);
	}

	if ( version_compare( $wp_version, QCG_REQUIRED_WP_VERSION, '<' ) ) {
		$errors[] = printf(
			esc_html__( 'Your Wordpress running version is %1$s but this plugin requires at least version %2$s. Please run an upgrade.', 'chat-with-gpt' ),
			esc_html( $wp_version ),
			QCG_REQUIRED_WP_VERSION
		);
	}

	$extensions = get_loaded_extensions();

	if ( ! in_array( 'curl', $extensions ) ) {
		$errors[] = esc_html__( 'Your need to install curl php extension to continue plugin use. Please install it first.', 'chat-with-gpt' );
	}

	if ( ! in_array( 'json', $extensions ) ) {
		$errors[] = esc_html__( 'Your need to install json php extension to continue plugin use. Please install it first.', 'chat-with-gpt' );
	}

	return $errors;
}

/**
 * Begins execution of the plugin.
 *
 * Plugin run entry point
 */
function qcg_run() {
	$plugin = new QCG_Common();
	$plugin->run();
}


/**
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met.
 * Otherwise, older PHP installations could crash when trying to parse it.
 */
require_once( __DIR__ . '/controller/class-qcg-common.php' );

$errors = qcg_requirements_met();
if ( ! $errors ) {
	if ( method_exists( QCG_Common::class, 'activate' ) ) {
		register_activation_hook( __FILE__, 'QCG_Common::activate' );
	}

	qcg_run();
} else {
	add_action( 'admin_notices', function () use ( $errors ) {
		require_once( dirname( __FILE__ ) . '/views/requirements-error.php' );
	} );
}

if ( method_exists( QCG_Common::class, 'deactivate' ) ) {
	register_deactivation_hook( __FILE__, array( QCG_Common::class, 'deactivate' ) );
}

if ( method_exists( QCG_Common::class, 'uninstall' ) ) {
	register_uninstall_hook( __FILE__, array( QCG_Common::class, 'uninstall' ) );
}

