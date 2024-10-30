<?php
/**
 * Updates MatrixPPC env to the latest downloaded version of the plugin.
 *
 * @package    MatrixPPC
 * @subpackage MatrixPPC/includes
 * @author     MatrixPPC <support@matrixppc.ai>
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class MatrixPPC_Updater{

	/**
	 * @since   1.0.0
	 * @var     string  $version
	 */
	protected static $version;

	/**
	 * MatrixPPC_Updater constructor.
	 * @since   1.0.0
	 * @access  public
	 * @param   string  $version
	 */
	public function __construct($version){
		self::$version=$version;
	}

	/**
	 * @since   1.0.0
	 * @access  public
	 * @param   string  $newVersion
	 * @return  bool
	 */
	public static function changeVersion($newVersion){
		MatrixPPC_Config::set("mx_ppc_version",$newVersion);
		MatrixPPC_Utils::cronDebug("Updated ".MatrixPPC_Utils::MATRIXPPC." to ".$newVersion);
		return self::check();
	}
	/**
	 * @since   1.0.0
	 * @access  public
	 * @param   void
	 * @return  bool
	 */
	public static function check(){
		$currentVersion=MatrixPPC_Config::get("mx_ppc_version");

		if($currentVersion === false){
			$currentVersion="1.0.0";
		}

		if(version_compare($currentVersion,self::$version,'==')){
			return true;
		}


		if(version_compare($currentVersion,self::$version,"lt")){
			return self::changeVersion(self::$version);
		}

		return false;
	}
}