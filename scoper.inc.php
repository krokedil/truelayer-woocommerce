<?php // phpcs:ignore
/**
 * PHP Scoper configuration file.
 */

use Symfony\Component\Finder\Finder;

/**
 * Get the excluded WordPress symbols.
 *
 * @param string $file_name The file name.
 *
 * @return array
 */
function get_wp_excluded_symbols( string $file_name ): array {
	$file_name = __DIR__ . '/vendor-bin/php-scoper/vendor/sniccowp/php-scoper-wordpress-excludes/generated/' . $file_name;

	return json_decode(
		file_get_contents( $file_name ), // phpcs:ignore
		true,
	);
}

$wp_classes   = get_wp_excluded_symbols( 'exclude-wordpress-classes.json' );
$wp_functions = get_wp_excluded_symbols( 'exclude-wordpress-functions.json' );
$wp_constants = get_wp_excluded_symbols( 'exclude-wordpress-constants.json' );

return array(
	'exclude-classes'   => $wp_classes,
	'exclude-constants' => $wp_functions,
	'exclude-functions' => $wp_constants,
	'finders'           => array(
		Finder::create()
		->in( 'vendor' )
		->name( array( '*.php', 'LICENSE', 'composer.json' ) )
		->exclude(
			array(
				'doc',
				'test',
				'test_old',
				'tests',
				'Tests',
				'vendor-bin',
				'phpunit',
				'php-stubs',
				'bamarni',
				'e2e',
			),
		),
	),
);
