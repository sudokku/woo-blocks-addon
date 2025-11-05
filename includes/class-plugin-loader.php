<?php

namespace WCBA;

if ( ! defined( 'ABSPATH' ) ) exit;

class Plugin_Loader {

	public function init() {
		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
	}

	public function on_plugins_loaded() {
		if ( ! $this->check_requirements() ) {
			return;
		}

		$this->register_autoload();
		$this->init_hooks();
	}

	private function check_requirements(): bool {
		// Require WooCommerce and the Blocks package environment.
		if ( ! class_exists( '\WooCommerce' ) ) {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'WooCommerce Blocks Addon requires WooCommerce to be installed and active.', WCBA_TEXTDOMAIN ) . '</p></div>';
			} );
			return false;
		}

		return true;
	}

	private function register_autoload(): void {
		// In a small plugin we can manually require classes.
		require_once WCBA_PATH . 'includes/class-register-blocks.php';
		require_once WCBA_PATH . 'includes/class-extend-core-blocks.php';
	}

	private function init_hooks(): void {
		add_action( 'init', [ $this, 'register_blocks' ] );
		add_action( 'init', [ $this, 'register_assets' ] );
	}

	public function register_blocks(): void {
		$registrar = new Register_Blocks();
		$registrar->register_all();
	}

	public function register_assets(): void {
		// Example: register a shared frontend stylesheet if needed later.
		// Keeping placeholder to wire up once build assets exist.
	}

	public static function activate(): void
	{
		// Minimal environment validation on activation.
		if (!class_exists('\\WooCommerce')) {
			// Deactivate self and explain requirement.
			deactivate_plugins(plugin_basename(WCBA_PATH . 'woocommerce-blocks-addon.php'));
			wp_die(esc_html__('WooCommerce Blocks Addon requires WooCommerce to be active.', WCBA_TEXTDOMAIN));
		}
	}

	public static function deactivate(): void
	{
		// Placeholder for future cleanup.
	}
}

