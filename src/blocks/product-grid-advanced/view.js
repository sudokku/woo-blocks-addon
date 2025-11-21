( function() {
	'use strict';

	// Handle sorting
	document.addEventListener( 'change', function( e ) {
		if ( ! e.target.matches( '.wcba-sort-select' ) ) {
			return;
		}

		const select = e.target;
		const blockId = select.getAttribute( 'data-block-id' );
		const [ orderBy, order ] = select.value.split( '|' );
		const grid = document.getElementById( blockId );
		if ( ! grid ) {
			return;
		}

		// Update URL and reload
		const url = new URL( window.location.href );
		url.searchParams.set( 'orderby', orderBy );
		url.searchParams.set( 'order', order );
		window.location.href = url.toString();
	} );

	// Handle filters
	document.addEventListener( 'click', function( e ) {
		if ( e.target.matches( '.wcba-filter-apply' ) ) {
			e.preventDefault();
			const filtersContainer = e.target.closest( '.wcba-grid-filters' );
			if ( ! filtersContainer ) {
				return;
			}

			const blockId = filtersContainer.getAttribute( 'data-block-id' );
			const url = new URL( window.location.href );

			// Get filter values
			const categorySelect = filtersContainer.querySelector( '[data-filter="category"]' );
			if ( categorySelect && categorySelect.selectedOptions.length > 0 ) {
				const categories = Array.from( categorySelect.selectedOptions ).map( opt => opt.value );
				url.searchParams.set( 'filter_cat', categories.join( ',' ) );
			}

			const priceMin = filtersContainer.querySelector( '[data-filter="price_min"]' )?.value;
			const priceMax = filtersContainer.querySelector( '[data-filter="price_max"]' )?.value;
			if ( priceMin ) {
				url.searchParams.set( 'filter_price_min', priceMin );
			}
			if ( priceMax ) {
				url.searchParams.set( 'filter_price_max', priceMax );
			}

			const ratingSelect = filtersContainer.querySelector( '[data-filter="rating"]' );
			if ( ratingSelect && ratingSelect.value ) {
				url.searchParams.set( 'filter_rating', ratingSelect.value );
			}

			const stockCheckbox = filtersContainer.querySelector( '[data-filter="stock"]' );
			if ( stockCheckbox && stockCheckbox.checked ) {
				url.searchParams.set( 'filter_stock', 'instock' );
			}

			window.location.href = url.toString();
		}

		if ( e.target.matches( '.wcba-filter-reset' ) ) {
			e.preventDefault();
			const url = new URL( window.location.href );
			// Remove all filter params
			[ 'filter_cat', 'filter_price_min', 'filter_price_max', 'filter_rating', 'filter_stock' ].forEach( param => {
				url.searchParams.delete( param );
			} );
			window.location.href = url.toString();
		}
	} );

	// Handle load more button
	document.addEventListener( 'click', function( e ) {
		if ( ! e.target.matches( '.wcba-loadmore-btn' ) ) {
			return;
		}

		e.preventDefault();
		const button = e.target;
		const currentPage = parseInt( button.getAttribute( 'data-page' ), 10 );
		const maxPages = parseInt( button.getAttribute( 'data-max' ), 10 );
		const nextPage = currentPage + 1;
		const blockId = button.closest( '.wcba-product-grid-advanced' )?.id;
		if ( ! blockId ) {
			return;
		}

		// Disable button
		button.disabled = true;
		button.textContent = button.textContent + '...';

		// Fetch next page via AJAX
		const url = new URL( window.location.href );
		url.searchParams.set( 'paged', nextPage );
		url.searchParams.set( 'action', 'wcba_load_more_products' );
		url.searchParams.set( 'block_id', blockId );

		fetch( url.toString() )
			.then( response => response.json() )
			.then( data => {
				if ( data.success && data.data.html ) {
					const grid = document.querySelector( `#${ blockId } .wcba-product-grid` );
					if ( grid ) {
						grid.insertAdjacentHTML( 'beforeend', data.data.html );
						if ( nextPage >= maxPages ) {
							button.parentElement.remove();
						} else {
							button.setAttribute( 'data-page', nextPage );
							button.disabled = false;
							button.textContent = button.textContent.replace( '...', '' );
						}
					}
				}
			} )
			.catch( error => {
				console.error( 'Load more error:', error );
				button.disabled = false;
				button.textContent = button.textContent.replace( '...', '' );
			} );
	} );

	// Infinite scroll
	let infiniteScrollObserver = null;
	if ( 'IntersectionObserver' in window ) {
		infiniteScrollObserver = new IntersectionObserver( function( entries ) {
			entries.forEach( entry => {
				if ( entry.isIntersecting ) {
					const loadmoreBtn = entry.target.querySelector( '.wcba-loadmore-btn' );
					if ( loadmoreBtn && ! loadmoreBtn.disabled ) {
						loadmoreBtn.click();
					}
				}
			} );
		}, {
			rootMargin: '100px',
		} );

		// Observe load more buttons for infinite scroll mode
		document.querySelectorAll( '.wcba-product-grid-advanced[data-infinite="true"] .wcba-grid-loadmore' ).forEach( el => {
			infiniteScrollObserver.observe( el );
		} );
	}
} )();

