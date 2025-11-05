import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, ToggleControl, SelectControl, TextControl, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit( { attributes, setAttributes } ) {
	const {
		overlayEnabled,
		overlayStyle,
		imageSwapEnabled,
		showPrice,
		showSaleBadge,
		showDiscountPercent,
		showCustomBadge,
		customBadgeText,
		showAddToCart,
		showQuickView,
		productId,
		useAjaxCart,
	} = attributes;

	const [ search, setSearch ] = wp.element.useState( '' );
	const { items: products, isLoading } = useSelect( ( select ) => {
		const { getEntityRecords, isResolving } = select( 'core' );
		const query = search && search.length > 1 ? { search, per_page: 20, status: 'publish' } : { per_page: 10, status: 'publish' };
		return {
			items: getEntityRecords( 'postType', 'product', query ) || [],
			isLoading: isResolving( 'getEntityRecords', [ 'postType', 'product', query ] ),
		};
	}, [ search ] );

	const blockProps = useBlockProps( { className: 'wcba-product-card' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Advanced Product Card', 'wcba' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Search Product', 'wcba' ) }
						value={ search }
						onChange={ setSearch }
					/>
					{ isLoading ? (
						<Spinner />
					) : (
						<SelectControl
							label={ __( 'Select Product', 'wcba' ) }
							value={ productId || '' }
							options={ [
								{ label: __( '— Select —', 'wcba' ), value: '' },
								...products.map( ( p ) => ( { label: p.title?.rendered || `#${ p.id }`, value: p.id } ) ),
							] }
							onChange={ ( val ) => setAttributes( { productId: val ? parseInt( val, 10 ) : undefined } ) }
						/>
					) }

					<ToggleControl
						label={ __( 'AJAX Add to Cart', 'wcba' ) }
						checked={ !!useAjaxCart }
						onChange={ ( val ) => setAttributes( { useAjaxCart: !!val } ) }
					/>
					<PanelRow>
						<ToggleControl
							label={ __( 'Enable Overlay', 'wcba' ) }
							checked={ !!overlayEnabled }
							onChange={ ( val ) => setAttributes( { overlayEnabled: !!val } ) }
						/>
					</PanelRow>
					{ overlayEnabled && (
						<SelectControl
							label={ __( 'Overlay Style', 'wcba' ) }
							value={ overlayStyle }
							options={ [
								{ label: __( 'None', 'wcba' ), value: 'none' },
								{ label: __( 'Gradient', 'wcba' ), value: 'gradient' },
								{ label: __( 'Solid', 'wcba' ), value: 'solid' },
							] }
							onChange={ ( val ) => setAttributes( { overlayStyle: val } ) }
						/>
					) }

					<ToggleControl
						label={ __( 'Image Swap on Hover', 'wcba' ) }
						checked={ !!imageSwapEnabled }
						onChange={ ( val ) => setAttributes( { imageSwapEnabled: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Show Price', 'wcba' ) }
						checked={ !!showPrice }
						onChange={ ( val ) => setAttributes( { showPrice: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Show Sale Badge', 'wcba' ) }
						checked={ !!showSaleBadge }
						onChange={ ( val ) => setAttributes( { showSaleBadge: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Show Discount Percent', 'wcba' ) }
						checked={ !!showDiscountPercent }
						onChange={ ( val ) => setAttributes( { showDiscountPercent: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Custom Badge', 'wcba' ) }
						checked={ !!showCustomBadge }
						onChange={ ( val ) => setAttributes( { showCustomBadge: !!val } ) }
					/>
					{ showCustomBadge && (
						<TextControl
							label={ __( 'Custom Badge Text', 'wcba' ) }
							value={ customBadgeText || '' }
							onChange={ ( val ) => setAttributes( { customBadgeText: val } ) }
						/>
					) }

					<ToggleControl
						label={ __( 'Show Add to Cart', 'wcba' ) }
						checked={ !!showAddToCart }
						onChange={ ( val ) => setAttributes( { showAddToCart: !!val } ) }
					/>

					<ToggleControl
						label={ __( 'Show Quick View', 'wcba' ) }
						checked={ !!showQuickView }
						onChange={ ( val ) => setAttributes( { showQuickView: !!val } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<ServerSideRender block="wcba/advanced-product-card" attributes={ attributes } />
			</div>
		</>
	);
}


