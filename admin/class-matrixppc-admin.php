<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.matrixppc.ai
 * @since      1.0.0
 *
 * @package    MatrixPPC
 * @subpackage MatrixPPC/admin
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

class MatrixPPC_Admin {
	
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $MatrixPPC    The ID of this plugin.
	 */
	private $MatrixPPC;
	
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */

	private $version;
	/**
	 * This function initializes the class and set its properties.
	 *
	 * @since 1.0.0
	 * @acces public
	 * @param    string    $MatrixPPC       The name of this plugin.
	 * @param    string    $version   		The version of this plugin.
	 */
	public function __construct( $MatrixPPC, $version ) {
		$this->MatrixPPC = $MatrixPPC;
		$this->version = $version;
	}
	
	/**
	 * This function creates the Settings link for the plugin.
	 *
	 * @since 1.0.0
	 * @acces public
	 * @param 	array $links
     * @param   string  $file
	 * @return 	array $links
	 */
	public function matrixppc_action_links($links, $file){
		if($file == 'matrixppc/matrixppc.php'){
			$links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=matrixppc&tab=stats') ) .'">Settings</a>';
		}
		
		return $links;
	}
	
	/**
	 * This function generates the tabs for the plugin settings page.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param 		string $current 	Current settings tab
	 * @return 		string	The tabs to be displayed
	 */
	public function matrixppc_tabs($current = 'stats'){
		$tabs = array(
			'stats' 	=> __('Stats', MatrixPPC_Utils::MATRIXPPC),
			'algos' 	=> __('Algos', MatrixPPC_Utils::MATRIXPPC),
			'settings' 	=> __('Settings', MatrixPPC_Utils::MATRIXPPC),
			'actions' 	=> __('Actions', MatrixPPC_Utils::MATRIXPPC),
			'advanced' 	=> __('Advanced', MatrixPPC_Utils::MATRIXPPC),
			'debug'     => __('Debug', MatrixPPC_Utils::MATRIXPPC)
	    );

		$html =  '<h2 class="nav-tab-wrapper">';
		foreach( $tabs as $tab => $name ) {
            $style = "";
            $class = ($tab == $current) ? 'nav-tab-active' : '';
            if ($tab == 'debug') {
                $style .= ' id="debug-tab" ';
                if (MatrixPPC_Config::get('mx_ppc_activate_cronlog') == '0') {
                    $style .= ' style="display:none;" ';
                }
            }
            $html .= '<a class="nav-tab ' . $class . '" ' . $style . ' href="?page=matrixppc&tab=' . $tab . '">' . $name . '</a>';
        }
		$html .= '</h2>';

		return $html;
	}

	/**
	 * This function adds the plugin settings page to the menu.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param 	void
	 * @return 	void
	 */
	public function matrixppc_add_menu_page(){
		add_options_page('MatrixPPC Settings', 'MatrixPPC', 'manage_options', 'matrixppc', array($this, 'matrixppc_page'));
	}
	
	/**
	 * This function represents the plugin settings page, and displays the view for the settings page and processes settings.
	 * 
	 * @since 1.0.0
	 * @access public
     * @param   void
	 * @return 	void
	 */
	public function matrixppc_page(){

		$api		= MatrixPPC_Api::getInstance();
		$reactor	= MatrixPPC_Reactor::getInstance();

		if(MatrixPPC_Config::get('mx_ppc_need_upgrade')==1){
		    self::displayError("You are generating too much traffic to MatrixPPC API Servers. You must upgrade your license to premium. <b><a href=\"https://matrixppc.ai/?op=upgrade&key=".MatrixPPC_Config::get("mx_ppc_key")."\" target=\"_blank\">MatrixPPC PRO</a></b>", 'need-premium');
        }

		if($_SERVER['REQUEST_METHOD'] === 'POST'){
			if( isset( $_POST['ips'] ) && isset($_POST['allow_edit_ips']) && $_POST['allow_edit_ips']=="on" ){
                $ips = sanitize_textarea_field($_POST['ips']);
                self::setSEIPsToFile($ips);
				self::displaySuccess("IPs modified.");
				MatrixPPC_Utils::cronDebug("IPs modified", 1);
			}
			if( isset( $_POST['referers'] ) && isset($_POST['allow_edit_refs']) && $_POST['allow_edit_refs']=="on" ){
                $referers = sanitize_textarea_field($_POST['referers']);
				self::setRefsToFile($referers);
				self::displaySuccess("Referrer fingerprints modified.");
				MatrixPPC_Utils::cronDebug("Referrer fingerprints modified", 1);
			} 
		}
		
		$tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'stats';
		$tabs = $this->matrixppc_tabs($tab);
		
		if($tab === 'settings'){
			$ips		= MatrixPPC_Utils::getSearchEngineIPsFromFile();
			$referers	= MatrixPPC_Utils::getReferrerMatchesFromFile();
		}

        if($tab == 'actions'){
            $campaigns = MatrixPPC_Db::getCampaigns();
            $ignored_data = MatrixPPC_Db::getIgnored();
        }

		if($tab == 'debug'){
			$cronContent = MatrixPPC_Utils::debugTail();
		}

		if(isset($_GET['searchurl'])){
			if( isset($_GET['searchurl']) && !empty($_GET['searchurl']) ){
				$term = sanitize_text_field($_GET['searchurl']);
				$term = str_replace(array('*'), '', $term);
				$type = filter_var($term, FILTER_VALIDATE_URL) === false ? 'REGEXP' : 'LIKE';
				
				if($type == 'LIKE'){
					$term = '%'.$term.'%';
				}
                $search_result = MatrixPPC_Db::getSearch($type, $term);
			}
		}


		include MatrixPPC_Utils::getBasePath('admin','views','matrixppc-admin-views.php');
	}
	
	
	/**
	 * This function generates the new cron intervals.
	 *
     * @since   1.0.0
     * @access  public
	 * @param 		array 	$schedules 		Wordpress crons array.
	 * @return 		array
	 */
	public function matrixppc_add_schedules( $schedules ) {
        $schedules['weekly'] = array(
            'interval' => 604800,
            'display' => __('Every week', MatrixPPC_Utils::MATRIXPPC)
        );
        return $schedules;
	}

    /**
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return  void
     */
    public function matrixppc_disabled_notice()
    {
        require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'matrixppc-admin-notice.php';
    }


    /**
     * This function generates the Wordpress error.
     *
     * @since   1.0.0
     * @access  public
     * @param string    $error
     * @return  void
     */
    
    public static function displayError($error, $class = ''){
    	$return = '<div class="msnotice msnotice-error notice notice-error '.$class.'">';
        $return .= '<img src="'.plugins_url("img/error.png",__FILE__).'">&nbsp;';
    	$return .= "<span>".__($error, MatrixPPC_Utils::MATRIXPPC)."</span>";
    	$return .= '</div>';
    	MatrixPPC_Utils::cronDebug("Error displayed: ".$error, 1);
    	echo $return;
    }
    
    /**
     * This function generates the Wordpress success message.
     * @since   1.0.0
     * @access  public
     * @param string    $message
     * @return  void
     */
    public static function displaySuccess($message){
    	$return = '<div class="msnotice msnotice-success notice notice-success">';
    	$return .= '<img src="'.plugins_url("img/success.png",__FILE__).'">&nbsp;';
    	$return .= "<span>".__($message, MatrixPPC_Utils::MATRIXPPC, 1)."</span>";
    	$return .= '</div>';
    	
    	echo $return;
    }
    
    /**
     * This function generates the Wordpress notice.
     *
     * @since   1.0.0
     * @access  public
     * @param string    $message
     * @return  void
     */
    public static function displayNotice($message){
    	$return = '<div class="msnotice msnotice-warning notice notice-warning">';
        $return .= '<img src="'.plugins_url("img/warning.png",__FILE__).'">&nbsp;';
    	$return .= "<span>".__($message, MatrixPPC_Utils::MATRIXPPC, 1)."</span>";
    	$return .= '</div>';
    	
    	echo $return;
    }

    /**
     * @since   1.0.0
     * @access  public
     * @param 	string 	$ip
     * @param 	array 	$array
     * @return  bool
     */
    public static function isIpInArray($ip,$array){
        $found=false;
        if(is_array($array)){
            foreach($array as $tmp){
                if(MatrixPPC_Utils::isIpInRange($ip,$tmp)){
                    $found=true;
                    break;
                }
            }
        }
        return $found;
    }

    /**
     * This function writes the IPs of the Search Engines to file.
     *
     * @since   1.0.0
     * @access  public
     * @param 	array 	$data IPs that will be written to a file
     * @return  void
     */
    public static function setSEIPsToFile($data){
    	$write='';
    	$invalidDetected=false;

    	$form_ips = explode( "\n", $data );
        $form_ips = array_map( "trim",$form_ips );
        $form_ips = array_unique( $form_ips );
        $writeArray = array();
        foreach( $form_ips as $ip ){
            $netmask='';
    		$tmpValid=true;
    		
    		if(strpos( $ip,' - ' )){
    			$ipS=explode( ' - ', $ip,2 );
    			if( MatrixPPC_Utils::validateIp( $ipS[0] ) && MatrixPPC_Utils::validateIp( $ipS[1] ) ){
    				$write.=$ip."\n";
    				if(!self::isIpInArray($ip,$writeArray)){
    				    $writeArray[]=$ip;
                    }
    			}
    			else{
    				$invalidDetected=true;
    			}
    		}
    		elseif( strpos( $ip,'-' ) ){
    			$ipS=explode( '-', $ip,2 );

    			if( MatrixPPC_Utils::validateIp( $ipS[0] ) && MatrixPPC_Utils::validateIp( $ipS[1] ) ){
                    $write.=$ip."\n";
                    if(!self::isIpInArray($ip,$writeArray)){
                        $writeArray[]=$ip;
                    }
                }
    			else{
    				$invalidDetected=true;
    			}
    		}
    		else{
    			if( strpos( $ip,'/' ) ){
    				list( $ip, $netmask ) = explode( '/', $ip, 2 );
    				if( strpos( $netmask,'/' ) ){
    					$tmpValid=false;
    				}
    			}
    			if( 	$tmpValid &&
    					MatrixPPC_Utils::validateIp( $ip ) &&
    					( $netmask=='' || ( is_numeric( $netmask ) && $netmask<=32 && $netmask>=0 ) )
    					){
    						if( $netmask!='' ){
    							$ip.="/".$netmask;
    						}
    						$write.=$ip."\n";
                            if(!self::isIpInArray($ip,$writeArray)){
                                $writeArray[]=$ip;
                            }
    			}
    			elseif( $ip!='' ) { // dont show warning if its just an empty line
    				$invalidDetected=true;
    			}
    		}
    		//--

    	}
    	MatrixPPC_Utils::setSafeFileContents( MatrixPPC_Utils::getStorageDirectory('seips.php'), implode("\n",$writeArray) ); //substr to eliminate the last \n
    	if( $invalidDetected ){
    		MatrixPPC_Utils::cronDebug("IPs set to file, with invalid data filtered out.", 1);
    	}
    	else{
    		MatrixPPC_Utils::cronDebug("IPs set to file", 1);
    	}
    }
    
    
    /**
     * This function writes the Search Engines Referrers to the file.
     *
     * @since   1.0.0
     * @access  public
     * @param 	array   $data   Array of Referars that will be written to a file
     * @return  void
     */
    public static function setRefsToFile($data){
    	
    	$write='';
    	$invalidDetected=false;
    	$form_refs = explode("\n", $data);
    	$form_refs = array_map("trim",$form_refs);
    	$form_refs = array_unique($form_refs);
    	foreach($form_refs as $referrer){
    		if(		$referrer!= '' &&
    				!( @preg_match( $referrer, null ) === false ) )	// smart regex expression validation)
    		{
    			$write.=$referrer."\n";
    		}
    		elseif($referrer!= ''){// dont show warning if its just an empty line
    			$invalidDetected=true;
    		}
    	}
    	MatrixPPC_Utils::setSafeFileContents(MatrixPPC_Utils::getStorageDirectory('refs.php'), substr( $write,0,-1 ) ); //substr to eliminate the last \n
    	if( $invalidDetected ){
    		MatrixPPC_Utils::cronDebug("Referrer fingerprints saved to file, with invalid data filtered out.", 1);
    	}
    	else{
    		MatrixPPC_Utils::cronDebug("Search engines referrer fingerprints saved to file.", 1);
    	}
    }

	/**
	 * @since   1.0.0
	 * @access  public
	 * @param   void
	 * @return  void
	 */
    public static function matrixppc_ajax_actions(){
	    $knownActions=array(
            "debug_level",
            "clear_log",
            "change_signature",
            "ignore_action",
            "apply_action",
            "activate_debug",
            "debug_log",
            "repopulate-settings",
            "repopulate-actions",
            "delete_files",
            "get-campaigns",
            "refresh-campaigns",
            "set-campaigns",
            "copy-adwords",
            "bold-algo-status",
            "fraud-algo-status",
            "bold-algo-settings",
            "fraud-algo-settings",
            "reset-fraudsters",
            "connect-method",
            "revoke-access",
            "refresh-status",
            "save-tokens",
            "algo_bold_light"
        );

	    // Sanitize the action
	    if(!in_array($_POST['what'],$knownActions)){
	    	wp_die();
	    }

	    switch($_POST['what']){

		    case "debug_level":
		    	    if(isset($_POST['level']) && in_array($_POST['level'],array("1","2","3"))) {
				        MatrixPPC_Config::set( 'mx_ppc_debug_level', (string)$_POST['level'] );
			        }
		    	break;

            case "clear_log":
		            MatrixPPC_Utils::setSafeFileContents(MatrixPPC_Utils::getStorageDirectory('debug.php'),"");
                break;

            case "change_signature":
                    if(isset($_POST['value']) && in_array($_POST['value'],array('0','1'))){
	                    MatrixPPC_Config::set("mx_ppc_signature_active", (string)$_POST['value']);
	                    MatrixPPC_Utils::cronDebug("Plugin signature [ ".$_POST['value']." ]",2);
                    }

                break;

            case "ignore_action":
                    if(isset($_POST['value']) && is_numeric($_POST['value'])){
	                    $urlId = (int)$_POST['value'];
	                    $item = MatrixPPC_Db::ignoreAction($urlId);
	                    if(!is_null($item)){
		                    MatrixPPC_Utils::cronDebug("Item added to ignore [ ".$item['url_plain']." ]", 2);
	                    }
                    }
                break;

		    case "apply_action":
                    if(isset($_POST['value']) && is_numeric($_POST['value'])){
                        $urlId = (int)$_POST['value'];
	                    $item = MatrixPPC_Db::applyAction($urlId);
                        MatrixPPC_Utils::cronDebug("Item removed from ignore [ ".$item['url_plain']." ]", 2);
                    }
			    break;

		    case "activate_debug":
                    if(isset($_POST['value']) && in_array($_POST['value'],array('0','1'))){
                        MatrixPPC_Config::set('mx_ppc_activate_cronlog', (string)$_POST['value']);
                        MatrixPPC_Utils::cronDebug("Debug log [ ".$_POST['value']." ]", 1);
                    }
			    break;

            case "debug_log":
                    $response=array();
                    $response['debug']=MatrixPPC_Utils::debugTail();
                    $response['size']=MatrixPPC_Utils::humanFilesize(filesize(MatrixPPC_Utils::getStorageDirectory("debug.php")));
                    echo json_encode($response);
                break;

            case "repopulate-settings":
                    $response=array();
                    $api=MatrixPPC_API::getInstance();
                    MatrixPPC_Utils::cronDebug("User requested repopulate-settings.",1);
                    $apiResponse=$api->call('repopulate-settings' );
                    if(isset($apiResponse['seips'])){
                        $seIps = sanitize_textarea_field( implode("\n", $apiResponse['seips']) );
                        self::setSEIPsToFile($seIps);
                        MatrixPPC_Utils::cronDebug("SEIps repopulated", 2);
                        $response['ips'] = implode("\n",MatrixPPC_Utils::getSearchEngineIPsFromFile());
                    }
                    if(isset($apiResponse['refs'])){
                        $refs = sanitize_textarea_field(implode( "\n", $apiResponse['refs'] ));
                        self::setRefsToFile($refs);
                        MatrixPPC_Utils::cronDebug("Refs repopulated", 2);
	                    $response['referers'] = implode("\n",MatrixPPC_Utils::getReferrerMatchesFromFile());
                    }
                    echo json_encode($response);
                break;

            case "repopulate-actions":
                    $reactor=MatrixPPC_Reactor::getInstance();
                    $results = MatrixPPC_Db::repopulateActions();
		            foreach( $results as $result ){
			            $reactor->setDataToFile( $result['hash'], $result['action_id'], $result['data'] );
		            }
		            MatrixPPC_Utils::cronDebug("Actions repopulated", 2);
                break;

            case "delete_files":
                $theSeFiles = glob(MatrixPPC_Utils::getSearchEnginesDirectory('*.php'));
                $theFiles = array_merge($theSeFiles, array()); // merge with other files to delete

                $safeFiles = array(
                    MatrixPPC_Utils::getSearchEnginesDirectory('index.php'),
                );
                MatrixPPC_Utils::cronDebug("Deleting files marked for deletion...", 3);

                foreach ($theFiles as $file) {
                    if(!in_array($file, $safeFiles)) {
                        MatrixPPC_Utils::deleteFile($file);
                    }
                }
                MatrixPPC_Utils::deleteActionsFiles();
                MatrixPPC_Utils::cronDebug("Internal Files deleted", 1);
                break;
            case "refresh-campaigns":
                $api=MatrixPPC_API::getInstance();
                $apiResponse=$api->call('refresh-campaigns' );

                if(isset($apiResponse['campaigns'])){
                    MatrixPPC_Utils::cronDebug("Campaigns refreshed", 2);
                    if(count($apiResponse['campaigns'])>0) {

                        // Set the selected campaigns counter
                        $selectedCampaigns=0;
                        foreach($apiResponse['campaigns'] as $campaign){
                            if($campaign['enabled']===true){
                                $selectedCampaigns+=1;
                            }
                        }
                        MatrixPPC_Config::set("mx_ppc_adwords_campaigns",$selectedCampaigns);
                        // ---

                        echo json_encode($apiResponse['campaigns']);
                    }
                    else{
                        $response['error']['message']="No campaigns present. <b>Make sure you installed the adwords script!</b> <br /><i>After installing the adwords script it can take up to a day to receive the data from it.</i>";
                        echo json_encode($response);
                    }
                }
                else{
                    $response['error']['message']="MatrixPPC API is probably down. Please try again later or check your internet connection.";
                    echo json_encode($response);
                }
                if(isset($apiResponse['error'])){
                    MatrixPPC_Utils::cronDebug("Can't refresh Campaigns", 1);
                    echo json_encode($apiResponse);
                }
                break;

            case "get-campaigns":
                $api=MatrixPPC_API::getInstance();
                $apiResponse=$api->call('get-campaigns' );

                if(isset($apiResponse['campaigns'])){
                    MatrixPPC_Utils::cronDebug("Campaigns fetched", 2);
                    if(count($apiResponse['campaigns'])>0) {

                        // Set the selected campaigns counter
                        $selectedCampaigns=0;
                        foreach($apiResponse['campaigns'] as $campaign){
                            if($campaign['enabled']===true){
                                $selectedCampaigns+=1;
                            }
                        }
                        MatrixPPC_Config::set("mx_ppc_adwords_campaigns",$selectedCampaigns);
                        // ---

                        echo json_encode($apiResponse['campaigns']);
                    }
                    else{
                        $response['error']['message']="No campaigns present. <b>Make sure you installed the adwords script!</b> <br /><i>After installing the adwords script it can take up to a day to receive the data from it.</i>";
                        echo json_encode($response);
                    }
                }
                else{
                    $response['error']['message']="MatrixPPC API is probably down. Please try again later or check your internet connection.";
                    echo json_encode($response);
                }
                if(isset($apiResponse['error'])){
                    MatrixPPC_Utils::cronDebug("Can't fetch Campaigns", 1);
                    echo json_encode($apiResponse);
                }
                break;
            case "set-campaigns":
                $valuesToSend = json_encode($_POST['values']);
                $api=MatrixPPC_API::getInstance();
                $response = $api->call('set-campaigns',array(),$valuesToSend);
                $responseCount=(int)$response['count'];
                if($responseCount){
                    MatrixPPC_Config::set('mx_ppc_adwords_campaigns',$responseCount);
                }
                echo json_encode($response);
                break;
            case "copy-adwords":
                MatrixPPC_Config::set("mx_ppc_show_script","0");
                break;
            case "bold-algo-status":
                $currentStatus = MatrixPPC_Config::get("mx_ppc_algo_bold");
                MatrixPPC_Config::set("mx_ppc_algo_bold", $currentStatus == '1' ? '0' : '1' );
                break;
            case "fraud-algo-status":
                $currentStatus = MatrixPPC_Config::get("mx_ppc_algo_fraud");
                MatrixPPC_Config::set("mx_ppc_algo_fraud", $currentStatus == '1' ? '0' : '1' );
                break;
            case "bold-algo-settings":
                if(isset($_POST['globally']) && in_array($_POST['globally'],array('0','1'))){
                    $globally = (int)$_POST['globally'];
                    MatrixPPC_Config::set("mx_ppc_algo_bold_globally", $globally);
                    MatrixPPC_Config::set("mx_ppc_algo_bold_mbpp",(int)$_POST['mx_ppc_algo_bold_mbpp']);
                    MatrixPPC_Config::set("mx_ppc_algo_bold_mbpps",(int)$_POST['mx_ppc_algo_bold_mbpps']);
                }
                break;
            case "reset-fraudsters":
                $ip_files=MatrixPPC_Utils::getIPsDirectory();
                MatrixPPC_Utils::rrmdir($ip_files);
                require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."includes".DIRECTORY_SEPARATOR."class-matrixppc-activator.php";
                MatrixPPC_Activator::createDir($ip_files);
                MatrixPPC_Db::resetFraudsters();
                $api = MatrixPPC_API::getInstance();

                try{
                    $response = $api->call(
                        "block-list",
                        array(),
                        array(
                            'ccid'=>MatrixPPC_Config::get("mx_ppc_adw_client_customer_id"),
                            'block'=>''
                        )
                    );
                }catch(Exception $e){
                    MatrixPPC_Utils::cronDebug($e->getMessage());
                }

                if (
                    (is_array($response)) &&
                    ($response['ok'] == true)
                ) {
                    MatrixPPC_Utils::cronDebug("Fraudsters list reset ok.");
                }
                break;
            case "fraud-algo-settings":
                if(isset($_POST['clicks']) && in_array($_POST['clicks'], range(2, 10))){
                    $clicks = (int)$_POST['clicks'];
                    MatrixPPC_Config::set("mx_ppc_algo_fraud_clicks", $clicks);
                }
                if(isset($_POST['days']) && in_array($_POST['days'], range(1, 7))){
                    $days = (int)$_POST['days'];
                    MatrixPPC_Config::set("mx_ppc_algo_fraud_days", $days);
                }
                break;
            case "revoke-access":
                $values = array(
                    'ccid'         =>  MatrixPPC_Config::get("mx_ppc_adw_client_customer_id"),
                );
                $valuesToSend = json_encode($values);

                $api=MatrixPPC_API::getInstance();
                $response=$api->call('revoke-access',array(),$valuesToSend);
                if($response['ok'] === true){
                    MatrixPPC_Config::set("mx_ppc_adw_client_customer_id","");
                    MatrixPPC_Config::set("mx_ppc_adw_client_customer_id_status","0");
                }
                echo MatrixPPC_Utils::getJSONFromArray($response);
                break;
            case "refresh-status":
                    $reactor=MatrixPPC_Reactor::getInstance();
                    $reactor->send_data(false);
                break;
            case "save-tokens":
                    $ccid=(string)$_POST['client_customer_id'];

                    $values = array(
                        'mx_ppc_adw_client_customer_id'         =>  trim($ccid),
                    );

                    $valuesToSend = json_encode($values);
                    $api=MatrixPPC_API::getInstance();
                    $response=$api->call('set-adwords',array(),$valuesToSend);
                    if($response['ok'] == true){
                        // Save the ccid
                        MatrixPPC_Config::set("mx_ppc_adw_client_customer_id",$ccid);
                        // Put ccid status to pending
                        MatrixPPC_Config::set("mx_ppc_adw_client_customer_id_status","1");
                        // Reset selected campaigns
                        MatrixPPC_Config::set("mx_ppc_adwords_campaigns","0");
                        // Reset selected campaigns json
                        MatrixPPC_Config::set("mx_ppc_adwords_campaigns_json","{}");
                    }
                    echo MatrixPPC_Utils::getJSONFromArray($response);
                break;
            case "algo_bold_light":
                MatrixPPC_Utils::cronDebug("Bold algo changed memory consumption.");
                //MatrixPPC_Config::set('mx_ppc_algo_bold_light', $_POST['algo_light'] == '1' ? '0' : '1');

                $status = MatrixPPC_Config::get('mx_ppc_algo_bold_light');

                if( isset($_POST['algo_light'])){
                    MatrixPPC_Config::set('mx_ppc_algo_bold_light', $status == '1' ? '0' : '1');
                    $response['ok'] = true;
                }else{
                    $response['message'] = 'Couldn\'t save changes.';
                }
                echo MatrixPPC_Utils::getJSONFromArray($response);
                break;
	    }

    	wp_die(); // AJAX call is done. Just die!
    }
	/**
	 * @since   1.0.0
	 * @access  public
	 * @param   void
	 * @return  void
	 */
	public static function matrixppc_add_js(){
	    if(isset($_GET['page']) && $_GET['page']==MatrixPPC_Utils::MATRIXPPC) {
		    ?>
<!-- <?php echo MatrixPPC_Utils::MATRIXPPC; ?> JS -->
<script type="text/javascript">
jQuery(document).ready(function ($) {

	<?php include dirname( __FILE__ ) . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "matrixppc-admin-js.php"; ?>

});
</script>
<!-- /<?php echo MatrixPPC_Utils::MATRIXPPC; ?> JS -->
		    <?php
	    }
	}

	/**
	 * @since   1.0.0
     * @access  public
     * @param   void
     * @return  void
	 */
	public static function matrixppc_add_css(){
		if(isset($_GET['page']) && $_GET['page']==MatrixPPC_Utils::MATRIXPPC) {
			?>
            <!-- <?php echo MatrixPPC_Utils::MATRIXPPC; ?> CSS -->
            <style>

					<?php include dirname( __FILE__ ) . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "matrixppc-admin-css.php"; ?>

            </style>
            <!-- /<?php echo MatrixPPC_Utils::MATRIXPPC; ?> CSS -->
			<?php
		}
    }
}