<?php

namespace WCBA;

if ( ! defined( 'ABSPATH' ) ) exit;

class Register_Blocks {

	public function register_all(): void {
		// 1) Register built blocks if present: build/blocks/<block>/block.json
		if (is_dir(WCBA_BUILD_PATH)) {
			$blocks_dir = trailingslashit(WCBA_BUILD_PATH) . 'blocks/';
			if (is_dir($blocks_dir)) {
				$entries = scandir($blocks_dir);
				if (is_array($entries)) {
					foreach ($entries as $entry) {
						if ($entry === '.' || $entry === '..') {
							continue;
						}
						$maybe_dir = $blocks_dir . $entry;
						$block_json = $maybe_dir . '/block.json';
						if (is_dir($maybe_dir) && file_exists($block_json)) {
							$meta = wp_json_file_decode($block_json, ['associative' => true]);
							$name = is_array($meta) && !empty($meta['name']) ? $meta['name'] : null;
							if ($name && \WP_Block_Type_Registry::get_instance()->is_registered($name)) {
								continue;
							}
							register_block_type($maybe_dir);
						}
					}
				}
			}

			// Root fallback: build/block.json
			$root_block_json = trailingslashit(WCBA_BUILD_PATH) . 'block.json';
			if (file_exists($root_block_json)) {
				$meta = wp_json_file_decode($root_block_json, ['associative' => true]);
				$name = is_array($meta) && !empty($meta['name']) ? $meta['name'] : null;
				if (!$name || !\WP_Block_Type_Registry::get_instance()->is_registered($name)) {
					register_block_type(WCBA_BUILD_PATH);
				}
			}
		}

		// 2) During development, also register from source: src/blocks/<block>/block.json
		$src_blocks_dir = trailingslashit(WCBA_PATH) . 'src/blocks/';
		if (is_dir($src_blocks_dir)) {
			$entries = scandir($src_blocks_dir);
			if (is_array($entries)) {
				foreach ($entries as $entry) {
					if ($entry === '.' || $entry === '..') {
						continue;
					}
					$maybe_dir = $src_blocks_dir . $entry;
					$block_json = $maybe_dir . '/block.json';
					if (is_dir($maybe_dir) && file_exists($block_json)) {
						$meta = wp_json_file_decode($block_json, ['associative' => true]);
						$name = is_array($meta) && !empty($meta['name']) ? $meta['name'] : null;
						if ($name && \WP_Block_Type_Registry::get_instance()->is_registered($name)) {
							continue;
						}
						register_block_type($maybe_dir);
					}
				}
			}
		}
	}
}


