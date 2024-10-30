<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all of the code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    MatrixPPC
 * @subpackage MatrixPPC/includes
 * @author     MatrixPPC <support@matrixppc.ai>
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

class MatrixPPC_Activator {
	
	/**
	 * @since   1.0.0
     * @access  protected
	 */
	protected static $errors;
	
	/**
	 * This function generates the WordPress style errors.
     * @since   1.0.0
     * @access  public
	 * @param   void
	 * @return  bool
	 */
	public static function activate() {
		self::$errors = array();

		self::createDBTables();         self::activatorErrorsCheck();

    	self::checkRequirements();      self::activatorErrorsCheck();

		self::generateStorage();        self::activatorErrorsCheck();

        self::scheduleEvents();

		MatrixPPC_Config::setDefaults();

		self::writeDefaultFiles();
		
		MatrixPPC_Utils::cronDebug("Plugin activated", 1);
		
        return 	MatrixPPC_Config::checkKey();
	}

    /**
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return  void
     */
	public static function scheduleEvents(){
        wp_schedule_event( time(),  'mx_ppc_interval','matrixppccronjob'      );

        MatrixPPC_Utils::cronDebug("Cronjobs activated", 3);
    }



    /**
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return  void
     */
	public static function activatorErrorsCheck(){
        if(!empty(self::$errors) && count(self::$errors)){
            wp_die(self::generateErrors(), MatrixPPC_Utils::MATRIXPPC, array('back_link' => true));
            exit;
        }
    }

	/**
	 * This function generates the WordPress style errors.
     * @since   1.0.0
     * @access  public
	 * @param   void
	 * @return  string
	 */
	public static function generateErrors(){
		$html = "";
		foreach(self::$errors as $error){
			$html .= "<div class='notice notice-error'>".$error."</div>";
		}
		return $html;
	}
	
	/**
	 * This function is checking if needed libraries are loaded, else throw errors.
     * @since   1.0.0
     * @access  public
	 * @param   void
	 * @return  void
	 */
	public static function checkRequirements(){
	    $passed=true;

		$requirements = array(
            'Zlib' => __('<b>Zlib</b> PHP extension is not loaded. Please contact your host/server administrator and request activation of the Zlib PHP extension.', MatrixPPC_Utils::MATRIXPPC),
			'cURL' => __('<b>cURL</b> PHP extension is not loaded. Please contact your host/server administrator and request activation of the cURL PHP extension.', MatrixPPC_Utils::MATRIXPPC)
		);

		foreach($requirements as $requirement => $message ){
			if(!extension_loaded($requirement)){
				array_push(self::$errors, $message);
				MatrixPPC_Utils::cronDebug("Requirement ".$requirement." not met", 3);
				$passed=false;
			}
		}

        if($passed) {
            MatrixPPC_Utils::cronDebug("Requirements passed.", 2);
        }
        else{
            MatrixPPC_Utils::cronDebug("Requirements not met to activate MatrixPPC.", 1);
        }
    }

    /**
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return  void
     */
	public static function generateStorage(){

        $createDirs=array(
            MatrixPPC_Utils::getStorageDirectory(),
            MatrixPPC_Utils::getActionsDirectory(),
            MatrixPPC_Utils::getSearchEnginesDirectory(),
            MatrixPPC_Utils::getIPsDirectory()
        );

        foreach ($createDirs as $createDir){
            if(!self::createDir($createDir)){
                break;
            }
        }

    }

    /**
     * @since   1.0.0
     * @access  public
     * @param   string  $what
     * @return  bool
     */
    public static function createDir($what){
        if(!is_dir($what)) {
            if (!mkdir($what, 0775, true)) {
                array_push(self::$errors, __('Can not create [ ' . $what . ' ].', MatrixPPC_Utils::MATRIXPPC));
                MatrixPPC_Utils::cronDebug("Can not create [ " . $what . " ]", 1);
                return false;
            }
            MatrixPPC_Utils::setSafeFileContents($what.DIRECTORY_SEPARATOR."index.php","");
            MatrixPPC_Utils::cronDebug("Created [ " . $what . " ].", 2);
            return true;
        }
        MatrixPPC_Utils::cronDebug("Directory already exists [ " . $what . " ].", 3);
        return true;
    }

    /**
     * @since   1.0.0
     * @access  public
     * @param   string  $what
     * @return  bool
     */
	public static function checkIfWritable($what){
		if(!is_writable($what)){
			array_push(self::$errors, __('The path [ '.$what.' ] is not writable. Check permissions (CHMOD 775) or contact your host/server administrator and request RW access on the indicated PATH.', MatrixPPC_Utils::MATRIXPPC));
            MatrixPPC_Utils::cronDebug("Failed writable test for [ ".$what." ]. MatrixPPC did not activate.",1);
            return false;
		}
        MatrixPPC_Utils::cronDebug("Checked [ ".$what." ]: is writable.", 2);
        return true;
    }

    /**
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return  void
     */
    public static function writeDefaultFiles(){
        $filesToCreate=array();

        require_once MatrixPPC_Utils::getBasePath('includes','init-data.php');

        foreach($filesToCreate as $key=>$fileToCreate){
            $tmpFile=MatrixPPC_Utils::getStorageDirectory($key);
            if(!file_exists($tmpFile)) {
                MatrixPPC_Utils::setSafeFileContents($tmpFile, $fileToCreate);
            }
        }
    }

    /**
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return  void
     */
    public static function createDBTables(){
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_action_creation = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."mx_ppc_actions (
		id INT(11) PRIMARY KEY AUTO_INCREMENT,
		hash char(32) NOT NULL,
		action_id INT(2) NOT NULL,
		data TEXT,
		insert_ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		CONSTRAINT mxppc_unique_index UNIQUE INDEX (hash, action_id)
		) $charset_collate;";

        $table_urls_creation = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."mx_ppc_urls (
		id INT(11) PRIMARY KEY AUTO_INCREMENT,
		url char(32) NOT NULL,
		url_plain VARCHAR(255) NOT NULL,
		updates INT(1) NOT NULL DEFAULT '0',
		update_ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		CONSTRAINT mxppc_urls_unique_index UNIQUE INDEX (url)
		) $charset_collate;";

        $table_actions_ignore_creation = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."mx_ppc_ignore (
		id INT(11) PRIMARY KEY AUTO_INCREMENT,
		id_url INT(11) NOT NULL,
		CONSTRAINT mxppc_ignore_unique_index UNIQUE INDEX (id_url)
		) $charset_collate;";

        $table_algo_shield_creation="CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."mx_ppc_shield (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        ip VARCHAR(45) NOT NULL,
        update_ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT mxppc_algo_shield_unique_index UNIQUE INDEX (ip)
        ) $charset_collate;";

        $wpdb->query( $table_action_creation );
        $wpdb->query( $table_urls_creation );
        $wpdb->query( $table_actions_ignore_creation );
        $wpdb->query( $table_algo_shield_creation );

        MatrixPPC_Utils::cronDebug("Tables created", 3);
    }
}