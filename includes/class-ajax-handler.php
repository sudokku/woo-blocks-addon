<?php

namespace WCBA;

if ( ! defined( 'ABSPATH' ) ) exit;

class Ajax_Handler {

	public function init() {
		// Register AJAX actions for both logged-in and logged-out users
		add_action( 'wp_ajax_wcba_sort_products', [ $this, 'handle_sort_products' ] );
		add_action( 'wp_ajax_nopriv_wcba_sort_products', [ $this, 'handle_sort_products' ] );

		add_action( 'wp_ajax_wcba_filter_products', [ $this, 'handle_filter_products' ] );
		add_action( 'wp_ajax_nopriv_wcba_filter_products', [ $this, 'handle_filter_products' ] );

		add_action( 'wp_ajax_wcba_load_more_products', [ $this, 'handle_load_more_products' ] );
		add_action( 'wp_ajax_nopriv_wcba_load_more_products', [ $this, 'handle_load_more_products' ] );
	}

	/**
	 * Handle AJAX request for sorting products
	 */
	public function handle_sort_products() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wcba-ajax-nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Security check failed.', 'wcba' ) ] );
		}

		$attributes_json = isset( $_POST['attributes'] ) ? wp_unslash( $_POST['attributes'] ) : '';
		$order_by        = isset( $_POST['orderBy'] ) ? sanitize_key( $_POST['orderBy'] ) : 'date';
		$order           = isset( $_POST['order'] ) ? strtoupper( sanitize_key( $_POST['order'] ) ) : 'DESC';
		$block_id        = isset( $_POST['blockId'] ) ? sanitize_text_field( $_POST['blockId'] ) : '';

		if ( empty( $attributes_json ) || empty( $block_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Missing required parameters.', 'wcba' ) ] );
		}

		$attributes = json_decode( $attributes_json, true );
		if ( ! is_array( $attributes ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid attributes.', 'wcba' ) ] );
		}

		// Update order attributes
		$attributes['orderBy'] = $order_by;
		$attributes['order']   = $order;
		$attributes['paged']   = 1; // Reset to first page when sorting

		// Get the block instance
		$block_name = 'wcba/product-grid-advanced';
		$block      = \WP_Block_Type_Registry::get_instance()->get_registered( $block_name );

		if ( ! $block || ! isset( $block->render_callback ) ) {
			wp_send_json_error( [ 'message' => __( 'Block not found.', 'wcba' ) ] );
		}

		// Render the block
		$content = $this->render_block_grid( $block->render_callback, $attributes, '', null );

		wp_send_json_success( [
			'html'      => $content,
			'blockId'   => $block_id,
		] );
	}

	/**
	 * Handle AJAX request for filtering products
	 */
	public function handle_filter_products() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wcba-ajax-nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Security check failed.', 'wcba' ) ] );
		}

		$attributes_json = isset( $_POST['attributes'] ) ? wp_unslash( $_POST['attributes'] ) : '';
		$filters         = isset( $_POST['filters'] ) ? $_POST['filters'] : [];
		$block_id        = isset( $_POST['blockId'] ) ? sanitize_text_field( $_POST['blockId'] ) : '';

		if ( empty( $attributes_json ) || empty( $block_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Missing required parameters.', 'wcba' ) ] );
		}

		$attributes = json_decode( $attributes_json, true );
		if ( ! is_array( $attributes ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid attributes.', 'wcba' ) ] );
		}

		// Apply filters to attributes
		if ( isset( $filters['categories'] ) && is_array( $filters['categories'] ) ) {
			$attributes['categories'] = array_map( 'absint', $filters['categories'] );
		}
		if ( isset( $filters['priceMin'] ) ) {
			$attributes['priceMin'] = floatval( $filters['priceMin'] );
		}
		if ( isset( $filters['priceMax'] ) ) {
			$attributes['priceMax'] = floatval( $filters['priceMax'] );
		}
		if ( isset( $filters['rating'] ) ) {
			// Rating filter will be handled in render
			$attributes['filterRating'] = absint( $filters['rating'] );
		}
		if ( isset( $filters['stock'] ) && $filters['stock'] === 'instock' ) {
			$attributes['inStock'] = true;
		}
		$attributes['paged'] = 1; // Reset to first page when filtering

		// Get the block instance
		$block_name = 'wcba/product-grid-advanced';
		$block      = \WP_Block_Type_Registry::get_instance()->get_registered( $block_name );

		if ( ! $block || ! isset( $block->render_callback ) ) {
			wp_send_json_error( [ 'message' => __( 'Block not found.', 'wcba' ) ] );
		}

		// Render the block
		$content = $this->render_block_grid( $block->render_callback, $attributes, '', null );

		wp_send_json_success( [
			'html'      => $content,
			'blockId'   => $block_id,
		] );
	}

	/**
	 * Handle AJAX request for loading more products
	 */
	public function handle_load_more_products() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wcba-ajax-nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Security check failed.', 'wcba' ) ] );
		}

		$attributes_json = isset( $_POST['attributes'] ) ? wp_unslash( $_POST['attributes'] ) : '';
		$page            = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$block_id        = isset( $_POST['blockId'] ) ? sanitize_text_field( $_POST['blockId'] ) : '';

		if ( empty( $attributes_json ) || empty( $block_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Missing required parameters.', 'wcba' ) ] );
		}

		$attributes = json_decode( $attributes_json, true );
		if ( ! is_array( $attributes ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid attributes.', 'wcba' ) ] );
		}

		// Set the page
		$attributes['paged'] = $page;

		// Get the block instance
		$block_name = 'wcba/product-grid-advanced';
		$block      = \WP_Block_Type_Registry::get_instance()->get_registered( $block_name );

		if ( ! $block || ! isset( $block->render_callback ) ) {
			wp_send_json_error( [ 'message' => __( 'Block not found.', 'wcba' ) ] );
		}

		// Render just the grid items (no wrapper)
		$content = $this->render_block_grid_items( $block->render_callback, $attributes, '', null );

		wp_send_json_success( [
			'html' => $content,
		] );
	}

	/**
	 * Render the full block grid (for sorting/filtering)
	 */
	private function render_block_grid( $callback, $attributes, $content, $block ) {
		if ( ! is_callable( $callback ) ) {
			return '';
		}

		// Temporarily override $_GET for pagination
		$old_paged = isset( $_GET['paged'] ) ? $_GET['paged'] : null;
		if ( isset( $attributes['paged'] ) ) {
			$_GET['paged'] = $attributes['paged'];
		} else {
			$_GET['paged'] = 1;
		}

		ob_start();
		$result = call_user_func( $callback, $attributes, $content, $block );
		$output = ob_get_clean();

		// Restore $_GET
		if ( $old_paged !== null ) {
			$_GET['paged'] = $old_paged;
		} else {
			unset( $_GET['paged'] );
		}

		return $output ? $output : $result;
	}

	/**
	 * Render just the grid items (for load more)
	 */
	private function render_block_grid_items( $callback, $attributes, $content, $block ) {
		// This will render the full block, but we'll extract just the items
		$full_html = $this->render_block_grid( $callback, $attributes, $content, $block );
		
		// Extract just the grid items using DOM
		if ( class_exists( 'DOMDocument' ) ) {
			libxml_use_internal_errors( true );
			$dom = new \DOMDocument();
			@$dom->loadHTML( '<?xml encoding="UTF-8">' . $full_html );
			$xpath = new \DOMXPath( $dom );
			$items = $xpath->query( '//div[contains(@class, "wcba-product-grid")]/*' );
			
			$html = '';
			foreach ( $items as $item ) {
				$html .= $dom->saveHTML( $item );
			}
			
			return $html;
		}

		// Fallback: use regex to extract items
		preg_match( '/<div class="wcba-product-grid"[^>]*>(.*?)<\/div>/s', $full_html, $matches );
		return isset( $matches[1] ) ? $matches[1] : '';
	}
}

