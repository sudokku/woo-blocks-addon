( function() {
	'use strict';

	// Handle sorting via AJAX
	document.addEventListener( 'change', function( e ) {
		if ( ! e.target.matches( '.wcba-sort-select' ) ) {
			return;
		}

		const select = e.target;
		const blockContainer = select.closest( '.wcba-product-grid-advanced' );
		if ( ! blockContainer ) {
			return;
		}

		const blockId = blockContainer.id;
		const attributesJson = blockContainer.getAttribute( 'data-attributes' );
		const nonce = blockContainer.getAttribute( 'data-nonce' );
		const ajaxUrl = blockContainer.getAttribute( 'data-ajax-url' );

		if ( ! blockId || ! attributesJson || ! nonce || ! ajaxUrl ) {
			return;
		}

		const [ orderBy, order ] = select.value.split( '|' );
		const gridContainer = blockContainer.querySelector( '.wcba-grid-container' );
		if ( ! gridContainer ) {
			return;
		}

		// Show loading state
		const grid = blockContainer.querySelector( '.wcba-product-grid' );
		if ( grid ) {
			grid.style.opacity = '0.5';
			grid.style.pointerEvents = 'none';
		}

		// Make AJAX request
		const formData = new FormData();
		formData.append( 'action', 'wcba_sort_products' );
		formData.append( 'nonce', nonce );
		formData.append( 'blockId', blockId );
		formData.append( 'attributes', attributesJson );
		formData.append( 'orderBy', orderBy );
		formData.append( 'order', order );

		fetch( ajaxUrl, {
			method: 'POST',
			body: formData,
		} )
			.then( response => response.json() )
			.then( data => {
				if ( data.success && data.data && data.data.html ) {
					// Replace the grid and pagination with new content
					const tempDiv = document.createElement( 'div' );
					tempDiv.innerHTML = data.data.html;
					
					// Get new elements
					const newGridContainer = tempDiv.querySelector( '.wcba-grid-container' );
					const newPagination = tempDiv.querySelector( '.wcba-grid-pagination' );
					const newLoadMore = tempDiv.querySelector( '.wcba-grid-loadmore' );
					
					if ( newGridContainer ) {
						// Replace grid container
						if ( gridContainer ) {
							gridContainer.replaceWith( newGridContainer );
						}
						
						// Update pagination
						const oldPagination = blockContainer.querySelector( '.wcba-grid-pagination' );
						if ( oldPagination && newPagination ) {
							oldPagination.replaceWith( newPagination );
						} else if ( oldPagination && ! newPagination ) {
							oldPagination.remove();
						} else if ( ! oldPagination && newPagination ) {
							blockContainer.appendChild( newPagination );
						}

						// Update load more button
						const oldLoadMore = blockContainer.querySelector( '.wcba-grid-loadmore' );
						if ( oldLoadMore && newLoadMore ) {
							oldLoadMore.replaceWith( newLoadMore );
						} else if ( oldLoadMore && ! newLoadMore ) {
							oldLoadMore.remove();
						} else if ( ! oldLoadMore && newLoadMore ) {
							blockContainer.appendChild( newLoadMore );
						}
					}
				}
			} )
			.catch( error => {
				console.error( 'Sort error:', error );
			} )
			.finally( () => {
				// Remove loading state
				const grid = blockContainer.querySelector( '.wcba-product-grid' );
				if ( grid ) {
					grid.style.opacity = '';
					grid.style.pointerEvents = '';
				}
			} );
	} );

	// Handle filters via AJAX
	document.addEventListener( 'click', function( e ) {
		if ( e.target.matches( '.wcba-filter-apply' ) ) {
			e.preventDefault();
			const filtersContainer = e.target.closest( '.wcba-grid-filters' );
			if ( ! filtersContainer ) {
				return;
			}

			const blockContainer = filtersContainer.closest( '.wcba-product-grid-advanced' );
			if ( ! blockContainer ) {
				return;
			}

			const blockId = blockContainer.id;
			const attributesJson = blockContainer.getAttribute( 'data-attributes' );
			const nonce = blockContainer.getAttribute( 'data-nonce' );
			const ajaxUrl = blockContainer.getAttribute( 'data-ajax-url' );

			if ( ! blockId || ! attributesJson || ! nonce || ! ajaxUrl ) {
				return;
			}

			const filters = {};
			
			const categorySelect = filtersContainer.querySelector( '[data-filter="category"]' );
			if ( categorySelect && categorySelect.selectedOptions.length > 0 ) {
				filters.categories = Array.from( categorySelect.selectedOptions ).map( opt => opt.value );
			}

			const priceMin = filtersContainer.querySelector( '[data-filter="price_min"]' )?.value;
			const priceMax = filtersContainer.querySelector( '[data-filter="price_max"]' )?.value;
			if ( priceMin ) {
				filters.priceMin = priceMin;
			}
			if ( priceMax ) {
				filters.priceMax = priceMax;
			}

			const ratingSelect = filtersContainer.querySelector( '[data-filter="rating"]' );
			if ( ratingSelect && ratingSelect.value ) {
				filters.rating = ratingSelect.value;
			}

			const stockCheckbox = filtersContainer.querySelector( '[data-filter="stock"]' );
			if ( stockCheckbox && stockCheckbox.checked ) {
				filters.stock = 'instock';
			}

			// Show loading state
			const gridContainer = blockContainer.querySelector( '.wcba-grid-container' );
			if ( gridContainer ) {
				gridContainer.style.opacity = '0.5';
				gridContainer.style.pointerEvents = 'none';
			}

			// Update attributes to match the user's filtering
			const attributes = JSON.parse(attributesJson);
			if (filters.categories && filters.categories.length) {
				attributes.querySource = 'category';
				attributes.categories = filters.categories;
			} else if (filters.tags && filters.tags.length) {
				attributes.querySource = 'tag';
				attributes.tags = filters.tags;
			} else if (!filters.categories || filters.categories.length === 0) {
				// No category filter: only reset if querySource was category
				if (attributes.querySource === 'category') {
					attributes.querySource = 'all';
					attributes.categories = [];
				}
			}

			// Make AJAX request
			const formData = new FormData();
			formData.append( 'action', 'wcba_filter_products' );
			formData.append( 'nonce', nonce );
			formData.append( 'blockId', blockId );
			formData.append( 'attributes', JSON.stringify( attributes ) );
			formData.append( 'filters', JSON.stringify( filters ) );

			fetch( ajaxUrl, {
				method: 'POST',
				body: formData,
			} )
				.then( response => response.json() )
				.then( data => {
					if ( data.success && data.data && data.data.html ) {
						const tempDiv = document.createElement( 'div' );
						tempDiv.innerHTML = data.data.html;
						
						// Get new elements
						const newGridContainer = tempDiv.querySelector( '.wcba-grid-container' );
						const newPagination = tempDiv.querySelector( '.wcba-grid-pagination' );
						const newLoadMore = tempDiv.querySelector( '.wcba-grid-loadmore' );
						
						if ( newGridContainer ) {
							// Replace grid container
							const oldGridContainer = blockContainer.querySelector( '.wcba-grid-container' );
							if ( oldGridContainer ) {
								oldGridContainer.replaceWith( newGridContainer );
							}
							
							// Update pagination
							const oldPagination = blockContainer.querySelector( '.wcba-grid-pagination' );
							if ( oldPagination && newPagination ) {
								oldPagination.replaceWith( newPagination );
							} else if ( oldPagination && ! newPagination ) {
								oldPagination.remove();
							} else if ( ! oldPagination && newPagination ) {
								blockContainer.appendChild( newPagination );
							}

							// Update load more button
							const oldLoadMore = blockContainer.querySelector( '.wcba-grid-loadmore' );
							if ( oldLoadMore && newLoadMore ) {
								oldLoadMore.replaceWith( newLoadMore );
							} else if ( oldLoadMore && ! newLoadMore ) {
								oldLoadMore.remove();
							} else if ( ! oldLoadMore && newLoadMore ) {
								blockContainer.appendChild( newLoadMore );
							}
						}
					}
				} )
				.catch( error => {
					console.error( 'Filter error:', error );
				} )
				.finally( () => {
					if ( gridContainer ) {
						gridContainer.style.opacity = '';
						gridContainer.style.pointerEvents = '';
					}
				} );
		}

		if ( e.target.matches( '.wcba-filter-reset' ) ) {
			e.preventDefault();
			const filtersContainer = e.target.closest( '.wcba-grid-filters' );
			if ( ! filtersContainer ) {
				return;
			}

			// Reset all filter inputs
			filtersContainer.querySelectorAll( 'select, input' ).forEach( input => {
				if ( input.type === 'checkbox' ) {
					input.checked = false;
				} else if ( input.tagName === 'SELECT' && input.multiple ) {
					// Clear all selected options in multiple select
					Array.from( input.options ).forEach( option => {
						option.selected = false;
					} );
				} else {
					input.value = '';
				}
			} );

			// Trigger apply filters with empty filters
			const applyBtn = filtersContainer.querySelector( '.wcba-filter-apply' );
			if ( applyBtn ) {
				applyBtn.click();
			}
		}
	} );

	// Handle load more button
	document.addEventListener( 'click', function( e ) {
		if ( ! e.target.matches( '.wcba-loadmore-btn' ) ) {
			return;
		}

		e.preventDefault();
		const button = e.target;
		const blockContainer = button.closest( '.wcba-product-grid-advanced' );
		if ( ! blockContainer ) {
			return;
		}

		const blockId = blockContainer.id;
		const attributesJson = blockContainer.getAttribute( 'data-attributes' );
		const nonce = blockContainer.getAttribute( 'data-nonce' );
		const ajaxUrl = blockContainer.getAttribute( 'data-ajax-url' );

		if ( ! blockId || ! attributesJson || ! nonce || ! ajaxUrl ) {
			return;
		}

		const currentPage = parseInt( button.getAttribute( 'data-page' ), 10 );
		const maxPages = parseInt( button.getAttribute( 'data-max' ), 10 );
		const nextPage = currentPage + 1;

		// Disable button
		button.disabled = true;
		const originalText = button.textContent;
		button.textContent = button.textContent + '...';

		// Make AJAX request
		const formData = new FormData();
		formData.append( 'action', 'wcba_load_more_products' );
		formData.append( 'nonce', nonce );
		formData.append( 'blockId', blockId );
		formData.append( 'attributes', attributesJson );
		formData.append( 'page', nextPage );

		fetch( ajaxUrl, {
			method: 'POST',
			body: formData,
		} )
			.then( response => response.json() )
			.then( data => {
				if ( data.success && data.data && data.data.html ) {
					const grid = blockContainer.querySelector( '.wcba-product-grid' );
					if ( grid ) {
						grid.insertAdjacentHTML( 'beforeend', data.data.html );
						if ( nextPage >= maxPages ) {
							button.parentElement.remove();
						} else {
							button.setAttribute( 'data-page', nextPage );
							button.disabled = false;
							button.textContent = originalText;
						}
					}
				}
			} )
			.catch( error => {
				console.error( 'Load more error:', error );
				button.disabled = false;
				button.textContent = originalText;
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
		document.querySelectorAll( '.wcba-product-grid-advanced[data-pagination-mode="infinite"] .wcba-grid-loadmore' ).forEach( el => {
			infiniteScrollObserver.observe( el );
		} );
	}
} )();
