<?php

/*
Plugin Name: Elementor Idosell
Description: Elementor integration for Idosell
Version: 2022.0.14-release
Author: Mateusz "Z4NR34L" Janota
Author URI: https://www.zanreal.pl
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require __DIR__ . '/vendor/autoload.php';

$ddElementorUpdateChecked = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/z4nr34l/elementor-idosell',
	__FILE__,
	'elementor-idosell'
);
$ddElementorUpdateChecked->getVcsApi()->enableReleaseAssets();

$app = new \ElementorIdosell\Application();
$app->registerActions();
$app->run();
