<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    MatrixPPC
 * @subpackage MatrixPPC/includes
 * @author     MatrixPPC <support@matrixppc.ai>
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

class MatrixPPC_Deactivator {
	
	/**
	 * Runned at the plugin deactivation.
	 * Unhooks the cronjobs and removes the actions.
	 * @since   1.0.0
	 * @param   void
     * @access  public
	 * @return  void
	 */
	public static function deactivate() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-matrixppc-reactor.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/class-matrixppc-utils.php';
		$reactor  = MatrixPPC_Reactor::getInstance();
		$utils=new MatrixPPC_Utils();
		//wp_clear_scheduled_hook("matrixppccronjob");
		//wp_clear_scheduled_hook("matrixppcstopwords");
        //MatrixPPC_Utils::cronDebug("Cronjobs deactivated", 3);
        //remove_action('wp_loaded', array($reactor, 'detectAndSaveVisitor'));
		//remove_filter('document_title_parts', array($reactor, 'handleChangeTitle'));
		//remove_filter('document_title_parts', array($utils, 'getPageTitle'));
        MatrixPPC_Utils::cronDebug("MatrixPPC plugin deactivated.", 3);
    }
}
