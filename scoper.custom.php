<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Customize the PHP-Scoper configuration.
 *
 * @param array $config The PHP-Scoper configuration.
 *
 * @return array The customized PHP-Scoper configuration.
 */
function customize_php_scoper_config( array $config = array() ) {
	// Add to exclude-files list any helpers files.
	$config['exclude-files'][] = 'helpers.php';

	// Add the helper functions from helpers.php to the list of functions to expose.
	$config['expose-functions'][] = 'collect';
	$config['expose-functions'][] = 'data_fill';
	$config['expose-functions'][] = 'data_get';
	$config['expose-functions'][] = 'data_set';
	$config['expose-functions'][] = 'head';
	$config['expose-functions'][] = 'last';
	$config['expose-functions'][] = 'value';
	$config['expose-functions'][] = 'append_config';
	$config['expose-functions'][] = 'blank';
	$config['expose-functions'][] = 'class_basename';
	$config['expose-functions'][] = 'class_uses_recursive';
	$config['expose-functions'][] = 'e';
	$config['expose-functions'][] = 'env';
	$config['expose-functions'][] = 'filled';
	$config['expose-functions'][] = 'object_get';
	$config['expose-functions'][] = 'optional';
	$config['expose-functions'][] = 'preg_replace_array';
	$config['expose-functions'][] = 'retry';
	$config['expose-functions'][] = 'tap';
	$config['expose-functions'][] = 'throw_if';
	$config['expose-functions'][] = 'throw_unless';
	$config['expose-functions'][] = 'trait_uses_recursive';
	$config['expose-functions'][] = 'transform';
	$config['expose-functions'][] = 'windows_os';
	$config['expose-functions'][] = 'with';

	return $config;
}
