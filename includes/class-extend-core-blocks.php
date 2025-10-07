<?php

namespace WCBA;

if ( ! defined( 'ABSPATH' ) ) exit;

class Extend_Core_Blocks {

	public function __construct() {
		add_filter( 'block_type_metadata', [ $this, 'extend_block_metadata' ], 10, 2 );
	}

	public function extend_block_metadata( $metadata, $path ) {
		// Placeholder for future block extension (add supports, styles, etc.)
		return $metadata;
	}
}


