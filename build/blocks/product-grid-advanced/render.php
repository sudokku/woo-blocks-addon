<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'wcba_render_product_grid_advanced' ) ) {
function wcba_render_product_grid_advanced( $attributes, $content, $block ) {
	// Extract attributes with defaults
	$query_source     = isset( $attributes['querySource'] ) ? sanitize_key( $attributes['querySource'] ) : 'all';
	$product_ids      = isset( $attributes['productIds'] ) && is_array( $attributes['productIds'] ) ? array_map( 'absint', $attributes['productIds'] ) : [];
	$categories       = isset( $attributes['categories'] ) && is_array( $attributes['categories'] ) ? array_map( 'absint', $attributes['categories'] ) : [];
	$tags             = isset( $attributes['tags'] ) && is_array( $attributes['tags'] ) ? array_map( 'absint', $attributes['tags'] ) : [];
	$featured         = ! empty( $attributes['featured'] );
	$on_sale          = ! empty( $attributes['onSale'] );
	$in_stock         = ! empty( $attributes['inStock'] );
	$price_min        = isset( $attributes['priceMin'] ) ? floatval( $attributes['priceMin'] ) : 0;
	$price_max        = isset( $attributes['priceMax'] ) ? floatval( $attributes['priceMax'] ) : 0;
	$order_by         = isset( $attributes['orderBy'] ) ? sanitize_key( $attributes['orderBy'] ) : 'date';
	$order            = isset( $attributes['order'] ) ? strtoupper( sanitize_key( $attributes['order'] ) ) : 'DESC';
	$per_page         = isset( $attributes['perPage'] ) ? absint( $attributes['perPage'] ) : 12;
	$columns          = isset( $attributes['columns'] ) ? absint( $attributes['columns'] ) : 4;
	$columns_tablet   = isset( $attributes['columnsTablet'] ) ? absint( $attributes['columnsTablet'] ) : 3;
	$columns_mobile   = isset( $attributes['columnsMobile'] ) ? absint( $attributes['columnsMobile'] ) : 2;
	$gap              = isset( $attributes['gap'] ) ? absint( $attributes['gap'] ) : 20;
	$enable_filters   = ! empty( $attributes['enableFilters'] );
	$filter_categories = ! empty( $attributes['filterCategories'] );
	$filter_price     = ! empty( $attributes['filterPrice'] );
	$filter_rating    = ! empty( $attributes['filterRating'] );
	$filter_stock     = ! empty( $attributes['filterStock'] );
	$enable_sorting   = ! empty( $attributes['enableSorting'] );
	$pagination_mode  = isset( $attributes['paginationMode'] ) ? sanitize_key( $attributes['paginationMode'] ) : 'numbers';
	$show_image       = ! isset( $attributes['showImage'] ) || ! empty( $attributes['showImage'] );
	$show_title       = ! isset( $attributes['showTitle'] ) || ! empty( $attributes['showTitle'] );
	$show_price       = ! isset( $attributes['showPrice'] ) || ! empty( $attributes['showPrice'] );
	$show_rating      = ! empty( $attributes['showRating'] );
	$show_description = ! empty( $attributes['showDescription'] );
	$show_sale_badge  = ! isset( $attributes['showSaleBadge'] ) || ! empty( $attributes['showSaleBadge'] );
	$show_new_badge   = ! empty( $attributes['showNewBadge'] );
	$show_add_to_cart = ! isset( $attributes['showAddToCart'] ) || ! empty( $attributes['showAddToCart'] );
	$use_ajax_cart    = ! isset( $attributes['useAjaxCart'] ) || ! empty( $attributes['useAjaxCart'] );
	$image_aspect     = isset( $attributes['imageAspectRatio'] ) ? sanitize_key( $attributes['imageAspectRatio'] ) : '1:1';
	$image_hover      = isset( $attributes['imageHoverEffect'] ) ? sanitize_key( $attributes['imageHoverEffect'] ) : 'none';
	$card_padding     = isset( $attributes['cardPadding'] ) ? absint( $attributes['cardPadding'] ) : 0;
	$card_radius      = isset( $attributes['cardBorderRadius'] ) ? absint( $attributes['cardBorderRadius'] ) : 0;
	$card_shadow      = ! empty( $attributes['cardShadow'] );
	$card_hover_shadow = ! empty( $attributes['cardHoverShadow'] );

	// Get current page for pagination
	$paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;

	// Build query args
	$args = [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $paged,
		'orderby'        => $order_by,
		'order'          => $order,
	];

	// Handle manual product selection
	if ( 'manual' === $query_source && ! empty( $product_ids ) ) {
		$args['post__in'] = $product_ids;
		$args['orderby']  = 'post__in';
	}

	// Handle category query
	if ( 'category' === $query_source && ! empty( $categories ) ) {
		$args['tax_query'][] = [
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $categories,
		];
	}

	// Handle tag query
	if ( 'tag' === $query_source && ! empty( $tags ) ) {
		if ( ! isset( $args['tax_query'] ) ) {
			$args['tax_query'] = [];
		}
		$args['tax_query'][] = [
			'taxonomy' => 'product_tag',
			'field'    => 'term_id',
			'terms'    => $tags,
		];
	}

	// Handle featured products
	if ( 'featured' === $query_source || $featured ) {
		$args['tax_query'][] = [
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => 'featured',
		];
	}

	// Handle on sale products
	if ( 'onsale' === $query_source || $on_sale ) {
		$args['post__in'] = array_merge( [ 0 ], wc_get_product_ids_on_sale() );
	}

	// Handle stock status
	if ( $in_stock ) {
		$args['meta_query'][] = [
			'key'   => '_stock_status',
			'value' => 'instock',
		];
	}

	// Handle price range
	if ( $price_min > 0 || $price_max > 0 ) {
		if ( ! isset( $args['meta_query'] ) ) {
			$args['meta_query'] = [];
		}
		$price_query = [
			'key'     => '_price',
			'type'    => 'DECIMAL',
			'compare' => 'BETWEEN',
		];
		if ( $price_min > 0 ) {
			$price_query['value'][0] = $price_min;
		} else {
			$price_query['value'][0] = 0;
		}
		if ( $price_max > 0 ) {
			$price_query['value'][1] = $price_max;
		} else {
			$price_query['value'][1] = 999999;
		}
		$args['meta_query'][] = $price_query;
	}

	// Set relation for multiple tax queries
	if ( isset( $args['tax_query'] ) && count( $args['tax_query'] ) > 1 ) {
		$args['tax_query']['relation'] = 'AND';
	}

	// Set relation for multiple meta queries
	if ( isset( $args['meta_query'] ) && count( $args['meta_query'] ) > 1 ) {
		$args['meta_query']['relation'] = 'AND';
	}

	// Execute query
	$products_query = new WP_Query( $args );
	$products       = $products_query->have_posts() ? $products_query->posts : [];

	// Generate unique ID for this block instance
	$block_id = 'wcba-grid-' . wp_unique_id();

	// Calculate grid CSS variables
	$grid_styles = sprintf(
		'--wcba-grid-columns: %d; --wcba-grid-columns-tablet: %d; --wcba-grid-columns-mobile: %d; --wcba-grid-gap: %dpx; --wcba-card-padding: %dpx; --wcba-card-radius: %dpx;',
		$columns,
		$columns_tablet,
		$columns_mobile,
		$gap,
		$card_padding,
		$card_radius
	);

	ob_start();
	?>
	<div class="wcba-product-grid-advanced" id="<?php echo esc_attr( $block_id ); ?>" style="<?php echo esc_attr( $grid_styles ); ?>">
		<?php if ( $enable_filters || $enable_sorting ) : ?>
			<div class="wcba-grid-controls">
				<?php if ( $enable_sorting ) : ?>
					<div class="wcba-grid-sort">
						<select class="wcba-sort-select" data-block-id="<?php echo esc_attr( $block_id ); ?>">
							<option value="date|DESC" <?php selected( $order_by, 'date' ); selected( $order, 'DESC' ); ?>><?php esc_html_e( 'Newest', 'wcba' ); ?></option>
							<option value="date|ASC" <?php selected( $order_by, 'date' ); selected( $order, 'ASC' ); ?>><?php esc_html_e( 'Oldest', 'wcba' ); ?></option>
							<option value="price|ASC" <?php selected( $order_by, 'price' ); selected( $order, 'ASC' ); ?>><?php esc_html_e( 'Price: Low to High', 'wcba' ); ?></option>
							<option value="price|DESC" <?php selected( $order_by, 'price' ); selected( $order, 'DESC' ); ?>><?php esc_html_e( 'Price: High to Low', 'wcba' ); ?></option>
							<option value="popularity|DESC" <?php selected( $order_by, 'popularity' ); ?>><?php esc_html_e( 'Popularity', 'wcba' ); ?></option>
							<option value="rating|DESC" <?php selected( $order_by, 'rating' ); ?>><?php esc_html_e( 'Rating', 'wcba' ); ?></option>
							<option value="title|ASC" <?php selected( $order_by, 'title' ); selected( $order, 'ASC' ); ?>><?php esc_html_e( 'Name: A-Z', 'wcba' ); ?></option>
							<option value="title|DESC" <?php selected( $order_by, 'title' ); selected( $order, 'DESC' ); ?>><?php esc_html_e( 'Name: Z-A', 'wcba' ); ?></option>
						</select>
					</div>
				<?php endif; ?>

				<?php if ( $enable_filters ) : ?>
					<div class="wcba-grid-filters" data-block-id="<?php echo esc_attr( $block_id ); ?>">
						<?php if ( $filter_categories ) : ?>
							<div class="wcba-filter-categories">
								<label><?php esc_html_e( 'Categories', 'wcba' ); ?></label>
								<?php
								$product_cats = get_terms( [
									'taxonomy'   => 'product_cat',
									'hide_empty' => true,
								] );
								if ( ! is_wp_error( $product_cats ) && ! empty( $product_cats ) ) :
									?>
									<select class="wcba-filter-select" data-filter="category" multiple>
										<?php foreach ( $product_cats as $cat ) : ?>
											<option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $filter_price ) : ?>
							<div class="wcba-filter-price">
								<label><?php esc_html_e( 'Price Range', 'wcba' ); ?></label>
								<div class="wcba-price-range">
									<input type="number" class="wcba-price-min" placeholder="<?php esc_attr_e( 'Min', 'wcba' ); ?>" data-filter="price_min" />
									<span>-</span>
									<input type="number" class="wcba-price-max" placeholder="<?php esc_attr_e( 'Max', 'wcba' ); ?>" data-filter="price_max" />
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $filter_rating ) : ?>
							<div class="wcba-filter-rating">
								<label><?php esc_html_e( 'Minimum Rating', 'wcba' ); ?></label>
								<select class="wcba-filter-select" data-filter="rating">
									<option value=""><?php esc_html_e( 'All', 'wcba' ); ?></option>
									<option value="4"><?php esc_html_e( '4+ Stars', 'wcba' ); ?></option>
									<option value="3"><?php esc_html_e( '3+ Stars', 'wcba' ); ?></option>
									<option value="2"><?php esc_html_e( '2+ Stars', 'wcba' ); ?></option>
									<option value="1"><?php esc_html_e( '1+ Stars', 'wcba' ); ?></option>
								</select>
							</div>
						<?php endif; ?>

						<?php if ( $filter_stock ) : ?>
							<div class="wcba-filter-stock">
								<label>
									<input type="checkbox" class="wcba-filter-checkbox" data-filter="stock" value="instock" />
									<?php esc_html_e( 'In Stock Only', 'wcba' ); ?>
								</label>
							</div>
						<?php endif; ?>

						<button class="wcba-filter-apply"><?php esc_html_e( 'Apply Filters', 'wcba' ); ?></button>
						<button class="wcba-filter-reset"><?php esc_html_e( 'Reset', 'wcba' ); ?></button>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="wcba-grid-container">
			<div class="wcba-product-grid <?php echo esc_attr( $card_shadow ? 'has-shadow' : '' ); ?> <?php echo esc_attr( $card_hover_shadow ? 'has-hover-shadow' : '' ); ?>" data-block-id="<?php echo esc_attr( $block_id ); ?>">
				<?php if ( ! empty( $products ) ) : ?>
					<?php foreach ( $products as $post ) : ?>
						<?php
						$product = wc_get_product( $post->ID );
						if ( ! $product ) {
							continue;
						}
						$link        = get_permalink( $product->get_id() );
						$title       = $product->get_name();
						$thumb       = get_the_post_thumbnail_url( $product->get_id(), 'woocommerce_thumbnail' );
						if ( ! $thumb ) {
							$thumb = wc_placeholder_img_src( 'woocommerce_thumbnail' );
						}
						$price_html  = $product->get_price_html();
						$rating      = $product->get_average_rating();
						$rating_count = $product->get_rating_count();
						$description = $product->get_short_description();
						$is_on_sale  = $product->is_on_sale();
						$is_new      = false;
						if ( $show_new_badge ) {
							$post_date    = get_the_date( 'Y-m-d', $product->get_id() );
							$days_since   = ( time() - strtotime( $post_date ) ) / DAY_IN_SECONDS;
							$is_new       = $days_since <= 30; // Products newer than 30 days
						}
						$image_classes = 'wcba-card__image';
						if ( 'none' !== $image_hover ) {
							$image_classes .= ' hover-' . esc_attr( $image_hover );
						}
						$aspect_class = 'aspect-' . str_replace( ':', '-', $image_aspect );
						?>
						<div class="wcba-grid-item" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
							<div class="wcba-product-card">
								<?php if ( $show_image ) : ?>
									<div class="wcba-card__media <?php echo esc_attr( $aspect_class ); ?>">
										<a href="<?php echo esc_url( $link ); ?>" class="<?php echo esc_attr( $image_classes ); ?>">
											<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
										</a>
										<?php if ( $show_sale_badge && $is_on_sale ) : ?>
											<span class="wcba-badge wcba-badge--sale"><?php esc_html_e( 'Sale', 'wcba' ); ?></span>
										<?php endif; ?>
										<?php if ( $show_new_badge && $is_new ) : ?>
											<span class="wcba-badge wcba-badge--new"><?php esc_html_e( 'New', 'wcba' ); ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<div class="wcba-card__content">
									<?php if ( $show_title ) : ?>
										<h3 class="wcba-card__title">
											<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
										</h3>
									<?php endif; ?>

									<?php if ( $show_rating && $rating > 0 ) : ?>
										<div class="wcba-card__rating">
											<?php echo wc_get_rating_html( $rating, $rating_count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</div>
									<?php endif; ?>

									<?php if ( $show_price ) : ?>
										<div class="wcba-card__price">
											<?php echo wp_kses_post( $price_html ); ?>
										</div>
									<?php endif; ?>

									<?php if ( $show_description && $description ) : ?>
										<div class="wcba-card__description">
											<?php echo wp_kses_post( wp_trim_words( $description, 15 ) ); ?>
										</div>
									<?php endif; ?>

									<?php if ( $show_add_to_cart && $product->is_purchasable() && $product->is_in_stock() ) : ?>
										<div class="wcba-card__actions">
											<?php
											if ( $use_ajax_cart ) {
												$classes = implode( ' ', array_filter( [ 'button', 'ajax_add_to_cart', 'add_to_cart_button' ] ) );
												printf(
													'<a href="%1$s" data-product_id="%2$d" data-quantity="1" class="%3$s">%4$s</a>',
													esc_url( $product->add_to_cart_url() ),
													$product->get_id(),
													esc_attr( $classes ),
													esc_html__( 'Add to cart', 'wcba' )
												);
											} else {
												printf(
													'<form class="cart" action="%1$s" method="post" enctype="multipart/form-data"><button type="submit" name="add-to-cart" value="%2$d" class="button">%3$s</button></form>',
													esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ),
													$product->get_id(),
													esc_html__( 'Add to cart', 'wcba' )
												);
											}
											?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="wcba-grid-empty">
						<p><?php esc_html_e( 'No products found.', 'wcba' ); ?></p>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( 'numbers' === $pagination_mode && $products_query->max_num_pages > 1 ) : ?>
				<div class="wcba-grid-pagination">
					<?php
					echo paginate_links( [
						'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
						'format'    => '?paged=%#%',
						'current'   => $paged,
						'total'     => $products_query->max_num_pages,
						'prev_text' => __( '&laquo; Previous', 'wcba' ),
						'next_text' => __( 'Next &raquo;', 'wcba' ),
					] );
					?>
				</div>
			<?php elseif ( 'loadmore' === $pagination_mode && $products_query->max_num_pages > $paged ) : ?>
				<div class="wcba-grid-loadmore">
					<button class="wcba-loadmore-btn" data-page="<?php echo esc_attr( $paged ); ?>" data-max="<?php echo esc_attr( $products_query->max_num_pages ); ?>">
						<?php esc_html_e( 'Load More', 'wcba' ); ?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php

	wp_reset_postdata();
	return ob_get_clean();
}
}

return 'wcba_render_product_grid_advanced';

