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
							$args = [];
							// Resolve render callback (supports "render": "file:..." or function name)
							if (is_array($meta) && !empty($meta['render'])) {
								$render_meta = $meta['render'];
								$callback = null;
								if (is_string($render_meta)) {
									if (strpos($render_meta, 'file:') === 0) {
										$relative = trim(substr($render_meta, 5));
										$relative = ltrim($relative, './');
										$render_file = trailingslashit($maybe_dir) . $relative;
										if (file_exists($render_file)) {
											$callback = include $render_file;
										}
									} else {
										$callback = $render_meta;
									}
								}
								if (is_string($callback) || is_callable($callback)) {
									$args['render_callback'] = $callback;
								}
							}
							// Ensure styles are registered even if block.json points to non-existent filenames
							$block_base_url = trailingslashit(WCBA_BUILD_URL) . 'blocks/' . $entry . '/';
							$style_candidates = ['style-index.css', 'style.css'];
							$editor_style_candidates = ['index.css', 'editor.css'];
							foreach ($style_candidates as $file) {
								$path = trailingslashit($maybe_dir) . $file;
								if (file_exists($path)) {
									$handle = sanitize_key(str_replace('/', '-', $name) . '-style');
									wp_register_style($handle, $block_base_url . $file, [], WCBA_VERSION);
									$args['style'] = $handle;
									break;
								}
							}
							foreach ($editor_style_candidates as $file) {
								$path = trailingslashit($maybe_dir) . $file;
								if (file_exists($path)) {
									$handle = sanitize_key(str_replace('/', '-', $name) . '-editor-style');
									wp_register_style($handle, $block_base_url . $file, [], WCBA_VERSION);
									$args['editor_style'] = $handle;
									break;
								}
							}
							register_block_type($maybe_dir, $args);
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
					$args = [];
					// Resolve render callback
					if (is_array($meta) && !empty($meta['render'])) {
						$render_meta = $meta['render'];
						$callback = null;
						if (is_string($render_meta)) {
							if (strpos($render_meta, 'file:') === 0) {
								$relative = trim(substr($render_meta, 5));
								$relative = ltrim($relative, './');
								$render_file = trailingslashit(WCBA_BUILD_PATH) . $relative;
								if (file_exists($render_file)) {
									$callback = include $render_file;
								}
							} else {
								$callback = $render_meta;
							}
						}
						if (is_string($callback) || is_callable($callback)) {
							$args['render_callback'] = $callback;
						}
					}
					// Ensure styles for root build block
					$block_base_url = trailingslashit(WCBA_BUILD_URL);
					$style_candidates = ['style-index.css', 'style.css'];
					$editor_style_candidates = ['index.css', 'editor.css'];
					foreach ($style_candidates as $file) {
						$path = trailingslashit(WCBA_BUILD_PATH) . $file;
						if (file_exists($path)) {
							$handle = $name ? sanitize_key(str_replace('/', '-', $name) . '-style') : 'wcba-root-style';
							wp_register_style($handle, $block_base_url . $file, [], WCBA_VERSION);
							$args['style'] = $handle;
							break;
						}
					}
					foreach ($editor_style_candidates as $file) {
						$path = trailingslashit(WCBA_BUILD_PATH) . $file;
						if (file_exists($path)) {
							$handle = $name ? sanitize_key(str_replace('/', '-', $name) . '-editor-style') : 'wcba-root-editor-style';
							wp_register_style($handle, $block_base_url . $file, [], WCBA_VERSION);
							$args['editor_style'] = $handle;
							break;
						}
					}
					register_block_type(WCBA_BUILD_PATH, $args);
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
						$args = [];
						// Resolve render callback
						if (is_array($meta) && !empty($meta['render'])) {
							$render_meta = $meta['render'];
							$callback = null;
							if (is_string($render_meta)) {
								if (strpos($render_meta, 'file:') === 0) {
									$relative = trim(substr($render_meta, 5));
									$relative = ltrim($relative, './');
									$render_file = trailingslashit($maybe_dir) . $relative;
									if (file_exists($render_file)) {
										$callback = include $render_file;
									}
								} else {
									$callback = $render_meta;
								}
							}
							if (is_string($callback) || is_callable($callback)) {
								$args['render_callback'] = $callback;
							}
						}
						// Try to attach styles from src if present (useful in dev)
						$block_base_url = trailingslashit(WCBA_URL) . 'src/blocks/' . $entry . '/';
						$style_candidates = ['style.css'];
						$editor_style_candidates = ['editor.css'];
						foreach ($style_candidates as $file) {
							$path = trailingslashit($maybe_dir) . $file;
							if (file_exists($path)) {
								$handle = sanitize_key(str_replace('/', '-', $name) . '-style');
								wp_register_style($handle, $block_base_url . $file, [], WCBA_VERSION);
								$args['style'] = $handle;
								break;
							}
						}
						foreach ($editor_style_candidates as $file) {
							$path = trailingslashit($maybe_dir) . $file;
							if (file_exists($path)) {
								$handle = sanitize_key(str_replace('/', '-', $name) . '-editor-style');
								wp_register_style($handle, $block_base_url . $file, [], WCBA_VERSION);
								$args['editor_style'] = $handle;
								break;
							}
						}
						register_block_type($maybe_dir, $args);
					}
				}
			}
		}
	}
}


