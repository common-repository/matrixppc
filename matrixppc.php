<?php

/**
 * The MatrixPPC plugin bootstrap file.
 *
 * @link              https://www.matrixppc.ai
 * @since             1.0.0
 * @package           MatrixPPC
 *
 * @wordpress-plugin
 * Plugin Name:       MatrixPPC
 * Plugin URI:        https://www.matrixppc.ai
 * Description:       Increase PPC traffic
 * Version:           1.0.0
 * Author:            MatrixPPC
 * Author URI:        https://www.matrixppc.ai
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) .'lib'. DIRECTORY_SEPARATOR .'class-matrixppc-utils.php';

/**
 * The code that runs during the plugin activation.
 * This action is documented in includes/class-matrixppc-activator.php.
 * @since   1.0.0
 * @param   void
 * @return  void
 */
function activate_MatrixPPC() {
	require_once MatrixPPC_Utils::getBasePath('includes','class-matrixppc-activator.php');
	MatrixPPC_Activator::activate();
}

/**
 * The code that runs during the plugin deactivation.
 * This action is documented in includes/class-matrixppc-deactivator.php.
 * @since   1.0.0
 * @param   void
 * @return  void
 */
function deactivate_MatrixPPC() {
	require_once MatrixPPC_Utils::getBasePath('includes','class-matrixppc-deactivator.php');
	MatrixPPC_Deactivator::deactivate();
}

register_activation_hook    ( __FILE__, 'activate_MatrixPPC'    );
register_deactivation_hook  ( __FILE__, 'deactivate_MatrixPPC'  );

require MatrixPPC_Utils::getBasePath('includes','class-matrixppc.php');

/**
 * This plugin begins the execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since   1.0.0
 * @access  public
 * @param   void
 * @return  void
 */
function run_MatrixPPC() {

	$plugin = new MatrixPPC();
	$plugin->run();

}
run_MatrixPPC();
