<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

class MatrixPPC_Config {

	/**
	 * @since    1.0.0
	 * @access   public
	 * @var      array
	 */
    public static $defaultConfig = array(
        'mx_ppc_version'                        =>  '1.0.0',
        'mx_ppc_activate_cronlog'               =>  '0',
    	'mx_ppc_key'							=>  '',
    	'mx_ppc_total_se'						=>  '0',
    	'mx_ppc_total_ref'						=>	'0',
    	'mx_ppc_total_act'						=>	'0',
        'mx_ppc_need_upgrade'                   =>  '0',
   		'mx_ppc_debug_level'					=> 	'1',
        'mx_ppc_signature_active'               =>  '1',
        'mx_ppc_max_send_size'                  =>  '16000000',
        'mx_ppc_max_filesize'                   =>  '500000',
        'mx_ppc_interval'                       =>  '3600',
        'mx_ppc_adwords_campaigns'              =>  '0',
        'mx_ppc_adwords_campaigns_json'         =>  '{}',
        'mx_ppc_algo_bold'                      =>  '0',
        'mx_ppc_algo_fraud'                     =>  '0',
        'mx_ppc_algo_bold_globally'             =>  '0',
        'mx_ppc_algo_bold_mbpp'                 =>  '5',
        'mx_ppc_algo_bold_mbpps'                =>  '2',
        'mx_ppc_algo_bold_light'                =>  '1',
        'mx_ppc_algo_fraud_clicks'              =>  '3',
        'mx_ppc_algo_fraud_days'                =>  '1',
        'mx_ppc_adw_client_customer_id'         =>  '',
        'mx_ppc_adw_client_customer_id_status'  =>  '0',

	);

    /**
     * This function sets a var through the Config class.
     * @since   1.0.0
     * @access   public
     * @param 	string  $key
     * @param   string  $val
     * @return 	string
     */
    public static function set($key, $val) {
        update_option($key,$val);
        self::updateCachedOption($key,$val);
        return $val;
    }

    /**
     * This function gets a var through the Config class.
     * @since   1.0.0
     * @acces public
     * @param   string  $key
     * @return  string
     */
    public static function get($key) {
        return (self::getCachedOption($key));
    }

    /**
     * This function initializes the default values on plugin install.
     * @since   1.0.0
     * @acces   public
     * @param   void
     * @return  void
     */
	public static function setDefaults() {
        foreach (self::$defaultConfig as $key => $value) {
            if (self::get($key) === false) {
               add_option($key, $value);
            }
        }
        MatrixPPC_Utils::cronDebug("Default config settings set", 3);
    }

	/**
     * @since   1.0.0
     * @access  public
	 * @param   string    $key
	 * @param   string    $value
	 */
    public static function add($key,$value){
	    if(self::get($key) === false){
	        add_option($key, $value);
	        self::updateCachedOption($key,$value);
        }
    }

    /**
     * This function cleans the WP instance on plugin uninstall.
     * @since   1.0.0
     * @acces   public
     * @param   void
     * @return  void
     */
    public static function unsetDefaults(){
        foreach (self::$defaultConfig as $key => $value) {
            if (get_option($key) !== false) {
                delete_option($key);
            }
        }
    }

    /**
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return  void
     */
    public static function unsetNetworkDefaults(){
        global $wpdb;
        $networkSites=get_sites();
        if(is_array($networkSites)) {
            foreach ($networkSites as $networkSite) {
                $prefix = $wpdb->get_blog_prefix($networkSite->blog_id);
                $wpOptions = $prefix."options";
                $mFields = implode('\',\'',array_keys(self::$defaultConfig));
                $mFields = '(\''.$mFields.'\')';
                $query="delete from `".$wpOptions."` where `option_name` in ".$mFields;
                $wpdb->query($query);
            }
        }
    }

    /**
     * This function loads the actual Config vars.
     * @since   1.0.0
     * @acces   private
     * @param   void
     * @return  array
     */
	private static function loadAllOptions() {
        $options = wp_cache_get('mx_ppc_options', MatrixPPC_Utils::MATRIXPPC);
        if (!$options) {
            foreach (self::$defaultConfig as $key=>$value) {
                $options[$key]=get_option($key);
            }
         }
        wp_cache_add_non_persistent_groups(MatrixPPC_Utils::MATRIXPPC);
        wp_cache_add('mx_ppc_options', $options, MatrixPPC_Utils::MATRIXPPC);
        return $options;
    }

    /**
     * This function updates a var and stores it.
     * @since   1.0.0
     * @acces   private
     * @param   string  $name
     * @param   string  $val
     * @return  void
     */
	private static function updateCachedOption($name, $val) {
        $options = self::loadAllOptions();
        $options[$name] = $val;
        wp_cache_set('mx_ppc_options', $options, MatrixPPC_Utils::MATRIXPPC);
    }

    /**
     * This function sets a cached option.
     * @since   1.0.0
     * @acces   private
     * @param   string  $name
     * @return  string
     */
	private static function getCachedOption($name) {
        $options = self::loadAllOptions();
        if (isset($options[$name])) {
            return $options[$name];
        }
        return get_option($name);
    }

    /**
     * This function checks if the API key is set.
     * @since   1.0.0
     * @access  public
     * @param   bool    $forceCall
     * @return  boolean
     */
    public static function isKeySet($forceCall=false){ //if isNotSet then try to set it
        $key = self::get('mx_ppc_key');
        
        $isSet = !($key == false || $key == "");
        if($isSet){
            return true;
        }
        if($forceCall){
            MatrixPPC_Utils::cronDebug("Force generating key...",3);
            $newKey=self::generateKey();
            if($newKey != false) {
                MatrixPPC_Utils::cronDebug("New key aquired.",3);
                MatrixPPC_Config::set("mx_ppc_key", $newKey);
                $api = MatrixPPC_API::getInstance();
                $api->setApiKey($newKey);
                return true;
            }
        }
        return $isSet;
    }

    /**
     * This function gets the API key.
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return 	mixed   Return the api key or false if it doesn't exist.
     */
    public static function getKey(){
        if (self::isKeySet())
            return self::get('mx_ppc_key');
        return false;
    }

    /**
     * This function generates the API key.
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return  mixed   API key or boolean false
     */
    public static function generateKey(){
    	require_once MatrixPPC_Utils::getBasePath("lib".DIRECTORY_SEPARATOR."class-matrixppc-api.php");
    	
    	$api = MatrixPPC_API::getInstance();
    	
    	$keyData = $api->call('get-anon-api-key', array(), array(), true);
    	
        if (isset($keyData['ok']) && isset($keyData['apiKey'])) {
            MatrixPPC_Utils::cronDebug("API Key generated", 3);
            return ($keyData['apiKey']);
        } else {
            MatrixPPC_Utils::cronDebug("Could not understand the response we received from the MatrixPPC API when applying for a free API key.", 3);
            return false;
        }
    }

    /**
     * This function checks if the key is set. If is not set we generate it and set it now.
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return  bool
     */
    public static function checkKey(){
        MatrixPPC_Utils::cronDebug("Checking key",3);
        if (!self::isKeySet()) {

        	$genKey = self::generateKey();
            if ($genKey !== false) {
            	MatrixPPC_Utils::cronDebug("Key checked OK.",3);
                self::set('mx_ppc_key', $genKey);
                MatrixPPC_Utils::cronDebug("Set new API key: ".$genKey,1);
                return true;
            }
            
            MatrixPPC_Utils::cronDebug("Failed to set new API key.",1);
            return false;
        }
        
        MatrixPPC_Utils::cronDebug("Key checked OK.",3);
        return true;
    }

    /**
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return  string | bool   The interval or false
     */
    public static function getCallInterval(){
        return MatrixPPC_Config::get("mx_interval");
    }

    /**
     * @since   1.0.0
     * @access  public
     * @param   string  Time to add
     * @return  void
     */
    public static function setCallInterval($timeAdd){
        MatrixPPC_Config::set('mx_ppc_interval', $timeAdd);
        wp_clear_scheduled_hook('matrixppccronjob');
        wp_schedule_event( time() + $timeAdd,  'mx_ppc_interval','matrixppccronjob');
    }

    /**
     * @since   1.0.0
     * @access  public
     * @param   string    $curStatus    [empty, normal, full]
     * @return  void
     */
    public static function updateThrottle($curStatus){
        if(self::get("mx_throttle_active")=="0"){
            return;
        }
        $medInterval    = 3600;
        $minInterval    = $medInterval / 4;
        $maxInterval    = $medInterval * 24 * 7;

        $curInterval    = self::getCallInterval();
        $newInterval    = $curInterval;

        switch($curStatus){
            case 'empty':
                $newInterval    = $curInterval * 2;
                break;
            case 'normal':
                if( $curInterval > $medInterval ){
                    $newInterval    = $curInterval / 2;
                }
                elseif( $curInterval < $medInterval ){
                    $newInterval    = $curInterval * 2;
                }
                break;
            case 'full':
                $newInterval    = $curInterval / 2;
                break;
        }

        $newInterval = $newInterval > $maxInterval ? $maxInterval : ( $newInterval < $minInterval ? $minInterval : $newInterval );

        if( $newInterval != $curInterval ) {
            self::setCallInterval($newInterval);
        }
    }

}
?>