<?php
/**
 * Plugin Name: Contact Form 7 Origin Protection
 * Plugin URI: https://robertwent.com
 * Description: Blocks Contact Form 7 submissions that don't originate from the same domain to prevent spam.
 * Version: 1.0.0
 * Author: Robert Went
 * Author URI: https://robertwent.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cf7-origin-protection
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CF7 Origin Protection Class
 */
class CF7_Origin_Protection {

	/**
	 * Plugin version
	 */
	const VERSION = '1.0.0';

	/**
	 * Initialize the plugin
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'init_rest_protection' ] );
		add_action( 'admin_notices', [ $this, 'check_dependencies' ] );
	}

	/**
	 * Initialize REST API protection
	 */
	public function init_rest_protection() {
		add_filter( 'rest_pre_dispatch', [ $this, 'validate_cf7_origin' ], 10, 3 );
	}

	/**
	 * Validate Contact Form 7 submission origin
	 *
	 * @param mixed $result
	 * @param WP_REST_Server $server
	 * @param WP_REST_Request $request
	 *
	 * @return mixed
	 */
	public function validate_cf7_origin( $result, $server, $request ) {
		// Only check CF7 REST API requests
		if ( strpos( $request->get_route(), '/contact-form-7/v1/' ) === false ) {
			return $result;
		}

		$origin      = $_SERVER['HTTP_ORIGIN'] ?? '';
		$site_url    = get_site_url();
		$parsed_site = parse_url( $site_url );
		$site_host   = $parsed_site['host'];

		// Allow requests from the same domain
		if ( ! empty( $origin ) && str_contains( $origin, $site_host ) ) {
			return $result;
		}

		// Log blocked attempt for debugging
		error_log( "CF7 Origin Protection: Blocked submission from origin: {$origin} (expected: {$site_host})" );

		// Block invalid origin
		return new WP_REST_Response( [
			'status'  => 'blocked',
			'message' => 'Invalid request origin'
		], 403 );
	}

	/**
	 * Check plugin dependencies
	 */
	public function check_dependencies() {
		if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			echo '<div class="notice notice-warning"><p>';
			echo '<strong>CF7 Origin Protection:</strong> This plugin requires Contact Form 7 to be installed and activated.';
			echo '</p></div>';
		}
	}
}

// Initialize the plugin
new CF7_Origin_Protection();