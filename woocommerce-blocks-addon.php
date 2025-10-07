<?php
/**
 * Plugin Name: WooCommerce Blocks Addon
 * Description: Adds extra WooCommerce Gutenberg blocks and styling options.
 * Version: 0.1.0
 * Author: Your Name
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Core plugin constants
define( 'WCBA_VERSION', '0.1.0' );
define( 'WCBA_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCBA_URL', plugin_dir_url( __FILE__ ) );
define( 'WCBA_BUILD_PATH', WCBA_PATH . 'build/' );
define( 'WCBA_BUILD_URL', WCBA_URL . 'build/' );
define( 'WCBA_TEXTDOMAIN', 'wcba' );

require_once WCBA_PATH . 'includes/class-plugin-loader.php';
$wcba_loader = new WCBA\Plugin_Loader();
$wcba_loader->init();

register_activation_hook( __FILE__, [ 'WCBA\\Plugin_Loader', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'WCBA\\Plugin_Loader', 'deactivate' ] );
