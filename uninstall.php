<?php

/**
 * Fired when the plugin is uninstalled. 
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * @link       http://matrixppc.ai
 * @since      1.0.0
 *
 * @package    MatrixPPC
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$path = plugin_dir_path( __FILE__ ) .'includes'. DIRECTORY_SEPARATOR .'class-matrixppc-uninstaller.php';
require_once str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
MatrixPPC_Uninstaller::uninstall();