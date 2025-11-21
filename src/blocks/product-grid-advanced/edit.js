import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	RangeControl,
	TextControl,
	Spinner,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit( { attributes, setAttributes } ) {
	const {
		querySource,
		productIds,
		categories,
		tags,
		featured,
		onSale,
		inStock,
		priceMin,
		priceMax,
		orderBy,
		order,
		perPage,
		columns,
		columnsTablet,
		columnsMobile,
		gap,
		enableFilters,
		filterCategories,
		filterPrice,
		filterRating,
		filterStock,
		enableSorting,
		paginationMode,
		showImage,
		showTitle,
		showPrice,
		showRating,
		showDescription,
		showSaleBadge,
		showNewBadge,
		showAddToCart,
		useAjaxCart,
		imageAspectRatio,
		imageHoverEffect,
		cardPadding,
		cardBorderRadius,
		cardShadow,
		cardHoverShadow,
	} = attributes;

	const blockProps = useBlockProps( { className: 'wcba-product-grid-advanced' } );

	// Fetch categories
	const { items: categoriesList, isLoading: categoriesLoading } = useSelect( ( select ) => {
		const { getEntityRecords, isResolving } = select( 'core' );
		return {
			items: getEntityRecords( 'taxonomy', 'product_cat', { per_page: -1, orderby: 'name', order: 'asc' } ) || [],
			isLoading: isResolving( 'getEntityRecords', [ 'taxonomy', 'product_cat' ] ),
		};
	}, [] );

	// Fetch tags
	const { items: tagsList, isLoading: tagsLoading } = useSelect( ( select ) => {
		const { getEntityRecords, isResolving } = select( 'core' );
		return {
			items: getEntityRecords( 'taxonomy', 'product_tag', { per_page: -1, orderby: 'name', order: 'asc' } ) || [],
			isLoading: isResolving( 'getEntityRecords', [ 'taxonomy', 'product_tag' ] ),
		};
	}, [] );

	// Fetch products for manual selection
	const [ productSearch, setProductSearch ] = wp.element.useState( '' );
	const { items: productsList, isLoading: productsLoading } = useSelect( ( select ) => {
		const { getEntityRecords, isResolving } = select( 'core' );
		const query = productSearch && productSearch.length > 1
			? { search: productSearch, per_page: 20, status: 'publish' }
			: { per_page: 10, status: 'publish' };
		return {
			items: getEntityRecords( 'postType', 'product', query ) || [],
			isLoading: isResolving( 'getEntityRecords', [ 'postType', 'product', query ] ),
		};
	}, [ productSearch ] );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Product Source', 'wcba' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Query Source', 'wcba' ) }
						value={ querySource }
						options={ [
							{ label: __( 'All Products', 'wcba' ), value: 'all' },
							{ label: __( 'By Category', 'wcba' ), value: 'category' },
							{ label: __( 'By Tag', 'wcba' ), value: 'tag' },
							{ label: __( 'Featured Products', 'wcba' ), value: 'featured' },
							{ label: __( 'On Sale Products', 'wcba' ), value: 'onsale' },
							{ label: __( 'Manual Selection', 'wcba' ), value: 'manual' },
						] }
						onChange={ ( val ) => setAttributes( { querySource: val } ) }
					/>

					{ querySource === 'category' && (
						<>
							{ categoriesLoading ? (
								<Spinner />
							) : (
								<SelectControl
									label={ __( 'Categories', 'wcba' ) }
									value={ categories.length > 0 ? categories[ 0 ] : '' }
									options={ [
										{ label: __( '— Select —', 'wcba' ), value: '' },
										...categoriesList.map( ( cat ) => ( { label: cat.name, value: cat.id } ) ),
									] }
									onChange={ ( val ) => setAttributes( { categories: val ? [ parseInt( val, 10 ) ] : [] } ) }
								/>
							) }
						</>
					) }

					{ querySource === 'tag' && (
						<>
							{ tagsLoading ? (
								<Spinner />
							) : (
								<SelectControl
									label={ __( 'Tags', 'wcba' ) }
									value={ tags.length > 0 ? tags[ 0 ] : '' }
									options={ [
										{ label: __( '— Select —', 'wcba' ), value: '' },
										...tagsList.map( ( tag ) => ( { label: tag.name, value: tag.id } ) ),
									] }
									onChange={ ( val ) => setAttributes( { tags: val ? [ parseInt( val, 10 ) ] : [] } ) }
								/>
							) }
						</>
					) }

					{ querySource === 'manual' && (
						<>
							<TextControl
								label={ __( 'Search Products', 'wcba' ) }
								value={ productSearch }
								onChange={ setProductSearch }
							/>
							{ productsLoading ? (
								<Spinner />
							) : (
								<SelectControl
									label={ __( 'Selected Products', 'wcba' ) }
									value={ productIds.length > 0 ? productIds[ 0 ] : '' }
									options={ [
										{ label: __( '— Select —', 'wcba' ), value: '' },
										...productsList.map( ( p ) => ( { label: p.title?.rendered || `#${ p.id }`, value: p.id } ) ),
									] }
									onChange={ ( val ) => setAttributes( { productIds: val ? [ parseInt( val, 10 ) ] : [] } ) }
								/>
							) }
						</>
					) }

					<ToggleControl
						label={ __( 'Featured Products Only', 'wcba' ) }
						checked={ !!featured }
						onChange={ ( val ) => setAttributes( { featured: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'On Sale Only', 'wcba' ) }
						checked={ !!onSale }
						onChange={ ( val ) => setAttributes( { onSale: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'In Stock Only', 'wcba' ) }
						checked={ !!inStock }
						onChange={ ( val ) => setAttributes( { inStock: !!val } ) }
					/>

					<RangeControl
						label={ __( 'Minimum Price', 'wcba' ) }
						value={ priceMin }
						onChange={ ( val ) => setAttributes( { priceMin: val } ) }
						min={ 0 }
						max={ 1000 }
					/>

					<RangeControl
						label={ __( 'Maximum Price', 'wcba' ) }
						value={ priceMax }
						onChange={ ( val ) => setAttributes( { priceMax: val } ) }
						min={ 0 }
						max={ 1000 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Sorting', 'wcba' ) }>
					<SelectControl
						label={ __( 'Order By', 'wcba' ) }
						value={ orderBy }
						options={ [
							{ label: __( 'Date', 'wcba' ), value: 'date' },
							{ label: __( 'Price', 'wcba' ), value: 'price' },
							{ label: __( 'Popularity', 'wcba' ), value: 'popularity' },
							{ label: __( 'Rating', 'wcba' ), value: 'rating' },
							{ label: __( 'Title', 'wcba' ), value: 'title' },
						] }
						onChange={ ( val ) => setAttributes( { orderBy: val } ) }
					/>

					<SelectControl
						label={ __( 'Order', 'wcba' ) }
						value={ order }
						options={ [
							{ label: __( 'Ascending', 'wcba' ), value: 'ASC' },
							{ label: __( 'Descending', 'wcba' ), value: 'DESC' },
						] }
						onChange={ ( val ) => setAttributes( { order: val } ) }
					/>

					<RangeControl
						label={ __( 'Products Per Page', 'wcba' ) }
						value={ perPage }
						onChange={ ( val ) => setAttributes( { perPage: val } ) }
						min={ 1 }
						max={ 100 }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Grid Layout', 'wcba' ) }>
					<RangeControl
						label={ __( 'Columns (Desktop)', 'wcba' ) }
						value={ columns }
						onChange={ ( val ) => setAttributes( { columns: val } ) }
						min={ 1 }
						max={ 6 }
					/>

					<RangeControl
						label={ __( 'Columns (Tablet)', 'wcba' ) }
						value={ columnsTablet }
						onChange={ ( val ) => setAttributes( { columnsTablet: val } ) }
						min={ 1 }
						max={ 4 }
					/>

					<RangeControl
						label={ __( 'Columns (Mobile)', 'wcba' ) }
						value={ columnsMobile }
						onChange={ ( val ) => setAttributes( { columnsMobile: val } ) }
						min={ 1 }
						max={ 3 }
					/>

					<RangeControl
						label={ __( 'Gap (px)', 'wcba' ) }
						value={ gap }
						onChange={ ( val ) => setAttributes( { gap: val } ) }
						min={ 0 }
						max={ 50 }
					/>

					<SelectControl
						label={ __( 'Pagination Mode', 'wcba' ) }
						value={ paginationMode }
						options={ [
							{ label: __( 'Numbers', 'wcba' ), value: 'numbers' },
							{ label: __( 'Load More Button', 'wcba' ), value: 'loadmore' },
							{ label: __( 'Infinite Scroll', 'wcba' ), value: 'infinite' },
							{ label: __( 'None', 'wcba' ), value: 'none' },
						] }
						onChange={ ( val ) => setAttributes( { paginationMode: val } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Card Content', 'wcba' ) }>
					<ToggleControl
						label={ __( 'Show Image', 'wcba' ) }
						checked={ !!showImage }
						onChange={ ( val ) => setAttributes( { showImage: !!val } ) }
					/>

					{ showImage && (
						<>
							<SelectControl
								label={ __( 'Image Aspect Ratio', 'wcba' ) }
								value={ imageAspectRatio }
								options={ [
									{ label: __( '1:1', 'wcba' ), value: '1:1' },
									{ label: __( '4:3', 'wcba' ), value: '4:3' },
									{ label: __( '16:9', 'wcba' ), value: '16:9' },
									{ label: __( '3:4', 'wcba' ), value: '3:4' },
									{ label: __( 'Auto', 'wcba' ), value: 'auto' },
								] }
								onChange={ ( val ) => setAttributes( { imageAspectRatio: val } ) }
							/>

							<SelectControl
								label={ __( 'Image Hover Effect', 'wcba' ) }
								value={ imageHoverEffect }
								options={ [
									{ label: __( 'None', 'wcba' ), value: 'none' },
									{ label: __( 'Zoom', 'wcba' ), value: 'zoom' },
									{ label: __( 'Fade', 'wcba' ), value: 'fade' },
									{ label: __( 'Slide', 'wcba' ), value: 'slide' },
								] }
								onChange={ ( val ) => setAttributes( { imageHoverEffect: val } ) }
							/>
						</>
					) }

					<ToggleControl
						label={ __( 'Show Title', 'wcba' ) }
						checked={ !!showTitle }
						onChange={ ( val ) => setAttributes( { showTitle: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Show Price', 'wcba' ) }
						checked={ !!showPrice }
						onChange={ ( val ) => setAttributes( { showPrice: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Show Rating', 'wcba' ) }
						checked={ !!showRating }
						onChange={ ( val ) => setAttributes( { showRating: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Show Description', 'wcba' ) }
						checked={ !!showDescription }
						onChange={ ( val ) => setAttributes( { showDescription: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Show Sale Badge', 'wcba' ) }
						checked={ !!showSaleBadge }
						onChange={ ( val ) => setAttributes( { showSaleBadge: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Show New Badge', 'wcba' ) }
						checked={ !!showNewBadge }
						onChange={ ( val ) => setAttributes( { showNewBadge: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Show Add to Cart', 'wcba' ) }
						checked={ !!showAddToCart }
						onChange={ ( val ) => setAttributes( { showAddToCart: !!val } ) }
					/>

					{ showAddToCart && (
						<ToggleControl
							label={ __( 'AJAX Add to Cart', 'wcba' ) }
							checked={ !!useAjaxCart }
							onChange={ ( val ) => setAttributes( { useAjaxCart: !!val } ) }
						/>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Frontend Filters', 'wcba' ) }>
					<ToggleControl
						label={ __( 'Enable Filters', 'wcba' ) }
						checked={ !!enableFilters }
						onChange={ ( val ) => setAttributes( { enableFilters: !!val } ) }
					/>

					{ enableFilters && (
						<>
							<ToggleControl
								label={ __( 'Category Filter', 'wcba' ) }
								checked={ !!filterCategories }
								onChange={ ( val ) => setAttributes( { filterCategories: !!val } ) }
							/>

							<ToggleControl
								label={ __( 'Price Filter', 'wcba' ) }
								checked={ !!filterPrice }
								onChange={ ( val ) => setAttributes( { filterPrice: !!val } ) }
							/>

							<ToggleControl
								label={ __( 'Rating Filter', 'wcba' ) }
								checked={ !!filterRating }
								onChange={ ( val ) => setAttributes( { filterRating: !!val } ) }
							/>

							<ToggleControl
								label={ __( 'Stock Filter', 'wcba' ) }
								checked={ !!filterStock }
								onChange={ ( val ) => setAttributes( { filterStock: !!val } ) }
							/>
						</>
					) }

					<ToggleControl
						label={ __( 'Enable Sorting', 'wcba' ) }
						checked={ !!enableSorting }
						onChange={ ( val ) => setAttributes( { enableSorting: !!val } ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Card Styling', 'wcba' ) }>
					<RangeControl
						label={ __( 'Card Padding (px)', 'wcba' ) }
						value={ cardPadding }
						onChange={ ( val ) => setAttributes( { cardPadding: val } ) }
						min={ 0 }
						max={ 50 }
					/>

					<RangeControl
						label={ __( 'Border Radius (px)', 'wcba' ) }
						value={ cardBorderRadius }
						onChange={ ( val ) => setAttributes( { cardBorderRadius: val } ) }
						min={ 0 }
						max={ 50 }
					/>

					<ToggleControl
						label={ __( 'Card Shadow', 'wcba' ) }
						checked={ !!cardShadow }
						onChange={ ( val ) => setAttributes( { cardShadow: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Hover Shadow', 'wcba' ) }
						checked={ !!cardHoverShadow }
						onChange={ ( val ) => setAttributes( { cardHoverShadow: !!val } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<ServerSideRender block="wcba/product-grid-advanced" attributes={ attributes } />
			</div>
		</>
	);
}
