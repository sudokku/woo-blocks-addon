<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'wcba_render_advanced_product_card' ) ) {
	function wcba_render_advanced_product_card( $attributes, $content, $block ) {
		if (!function_exists('wc_get_product')) {
			return '<div class="wcba-product-card wcba-product-card--error">' . esc_html__('WooCommerce is not active.', 'wcba') . '</div>';
		}
		$product_id     = isset( $attributes['productId'] ) ? absint( $attributes['productId'] ) : 0;
		$use_ajax_cart  = ! empty( $attributes['useAjaxCart'] );
		$overlay        = ! empty( $attributes['overlayEnabled'] );
		$overlay_style  = isset( $attributes['overlayStyle'] ) ? sanitize_key( $attributes['overlayStyle'] ) : 'none';
		$show_price     = isset( $attributes['showPrice'] ) ? (bool) $attributes['showPrice'] : true;
		$show_sale      = ! empty( $attributes['showSaleBadge'] );
		$show_discount  = ! empty( $attributes['showDiscountPercent'] );
		$show_custom    = ! empty( $attributes['showCustomBadge'] );
		$custom_badge   = isset( $attributes['customBadgeText'] ) ? wp_kses_post( $attributes['customBadgeText'] ) : '';
		$show_quickview = ! empty( $attributes['showQuickView'] );
		
		if ( ! $product_id ) {
			return '<div class="wcba-product-card wcba-product-card--empty">' . esc_html__( 'Select a product...', 'wcba' ) . '</div>';
		}
		
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return '<div class="wcba-product-card wcba-product-card--missing">' . sprintf(esc_html__('Product not found (ID: %d).', 'wcba'), $product_id) . '</div>';
		}
		
		$link      = get_permalink( $product_id );
		$title     = $product->get_name();
		$thumb     = get_the_post_thumbnail_url( $product_id, 'woocommerce_thumbnail' );
		if ( ! $thumb ) {
			$thumb = wc_placeholder_img_src( 'woocommerce_thumbnail' );
		}
		
		$regular_price = $product->get_regular_price();
		$sale_price    = $product->get_sale_price();
		
		$price_html = '';
		if ( $show_price ) {
			$price_html .= '<div class="wcba-card__price">';
			if ( $product->is_on_sale() && $sale_price !== '' ) {
				$price_html .= '<del class="wcba-price--regular">' . wc_price( $regular_price ) . '</del>';
				$price_html .= '<ins class="wcba-price--sale">' . wc_price( $sale_price ) . '</ins>';
			} else {
				$price_html .= '<span class="wcba-price--regular">' . wp_kses_post( $product->get_price_html() ) . '</span>';
			}
			$price_html .= '</div>';
		}
		
		$badges_html = '<div class="wcba-card__badges">';
		if ( $show_sale && $product->is_on_sale() ) {
			$badges_html .= '<span class="wcba-badge wcba-badge--sale">' . esc_html__( 'Sale', 'wcba' ) . '</span>';
		}
		if ( $show_discount && $product->is_on_sale() && $regular_price && $sale_price && floatval( $regular_price ) > 0 ) {
			$discount = round( ( ( floatval( $regular_price ) - floatval( $sale_price ) ) / floatval( $regular_price ) ) * 100 );
			$badges_html .= '<span class="wcba-badge wcba-badge--discount">-' . intval( $discount ) . '%</span>';
		}
		if ( $show_custom && $custom_badge ) {
			$badges_html .= '<span class="wcba-badge wcba-badge--custom">' . $custom_badge . '</span>';
		}
		$badges_html .= '</div>';
		
		$add_to_cart_html = '';
		if ( $product->is_purchasable() && $product->is_in_stock() ) {
			if ( $use_ajax_cart ) {
				$classes = implode( ' ', array_filter( [ 'button', 'ajax_add_to_cart', 'add_to_cart_button' ] ) );
				$add_to_cart_html = sprintf(
					'<a href="%1$s" data-product_id="%2$d" data-quantity="1" class="%3$s">%4$s</a>',
					esc_url( $product->add_to_cart_url() ),
					$product_id,
					esc_attr( $classes ),
					esc_html__( 'Add to cart', 'wcba' )
				);
			} else {
				$add_to_cart_html = sprintf(
					'<form class="cart" action="%1$s" method="post" enctype="multipart/form-data"><button type="submit" name="add-to-cart" value="%2$d" class="button">%3$s</button></form>',
					esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ),
					$product_id,
					esc_html__( 'Add to cart', 'wcba' )
				);
			}
		}
		
		$overlay_html = '';
		if ( $overlay && in_array( $overlay_style, [ 'gradient', 'solid' ], true ) ) {
			$overlay_html = '<div class="wcba-card__overlay is-' . esc_attr( $overlay_style ) . '"></div>';
		}
		
		ob_start();
		?>
		<div class="wcba-product-card">
		<div class="wcba-card__media">
		<a href="<?php echo esc_url( $link ); ?>" class="wcba-card__thumb"><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>" /></a>
		<?php echo $overlay_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php echo $badges_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<div class="wcba-card__content">
		<h3 class="wcba-card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a></h3>
		<?php echo $price_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="wcba-card__actions"><?php echo $add_to_cart_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		</div>
		</div>
		<?php
		return ob_get_clean();
	}
}

return 'wcba_render_advanced_product_card';


