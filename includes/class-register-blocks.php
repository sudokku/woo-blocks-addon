<?php

namespace WCBA;

if ( ! defined( 'ABSPATH' ) ) exit;

class Register_Blocks {

	public function register_all(): void {
		// Register dynamic blocks here when they are created.
		// For static blocks built via block.json, we'll look for built assets later.

		// Example placeholder: ensure a public build dir is available for future assets.
		if ( ! file_exists( WCBA_BUILD_PATH ) ) {
			// Silently skip if build folder not present; we are just bootstrapping.
			return;
		}
	}
}


