<?php
/**
 * The API access library
 * @since      1.0.0
 * @package    MatrixPPC
 * @subpackage MatrixPPC/includes
 * @author     MatrixPPC <support@matrixppc.ai>
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

class MatrixPPC_API {
	/**
	 * @since    1.0.0
	 * @access   public
	 * @var      string
	 */
	public $lastHTTPStatus = '';
	
	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $curlContent = '';
	
	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $APIKey = '';
	
	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $wordpressVersion = '';

    /**
	 * @since	1.0.0
	 * @access	private
     * @var		string
     */
    private  $apiHost;

	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      MatrixPPC_API
	 */
	private static $instance;

	/**
	 * @since	1.0.0
	 * @access	public
	 * @param	void
	 * @return	MatrixPPC_API
	 */
	public static function getInstance() {
		global $wp_version;
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self( MatrixPPC_Config::getKey(),$wp_version );
		}
		return self::$instance;
	}

	/**
	 * @since	1.0.0
	 * @access	public
	 * @param	string	$apiKey
	 * @param	string	$wordpressVersion
	 */
	public function __construct($apiKey, $wordpressVersion) {
	    $this->setAPIHost("api.matrixppc.ai");
	    //$this->setAPIHost("localhost/brain.ppc/public");
		$this->APIKey               =   $apiKey;
		$this->wordpressVersion     =   $wordpressVersion;
	}

    /**
     * @since	1.0.0 
     * @access	public
     * @param	string	$newKey
	 * @return	void
	 */
	public function setApiKey($newKey){
		MatrixPPC_Utils::cronDebug("Changed API Key on the fly.",1);
		$this->APIKey	=	$newKey;
	}

    /**
     * @since	1.0.0
     * @access	public
     * @param	string	$action
	 * @param	array	$getParams
	 * @param	array	$postParams
	 * @param 	bool	$forceCall
     * @return	array	Response from API server
     */
	public function call($action, $getParams = array(), $postParams = array(), $forceCall=false) {
		if($action=="get-anon-api-key"){
			$forceCall=true;
		}
		if(!$forceCall){
            if(MatrixPPC_Config::isKeySet(true)){
            	$forceCall=true;
			}
		}
		if( $forceCall) {
            MatrixPPC_Utils::cronDebug("API CALL " . $action,1);

            if (count($getParams)) {
                MatrixPPC_Utils::cronDebug("SENDING GET to API: " . json_encode($getParams, true),2);
            }
            if (count($postParams)) {
                MatrixPPC_Utils::cronDebug("SENDING POST to API: " . json_encode($postParams, true),2);
            }
            $apiURL = $this->getAPIHost();
            
            $gzData = $this->getURL(rtrim($apiURL, '/') . '/v' . MatrixPPC_Utils::MATRIXPPC_API_VERSION . '?' . $this->makeAPIQueryString(count($postParams)) . '&' . self::buildQuery(
                    array_merge(
                        array('action' => $action),
                        $getParams
                    )), $postParams);
            if (!$gzData) {
                MatrixPPC_Utils::cronDebug("We received an empty data response from the MatrixPPC API when calling the '$action' function.",1);
                return array();
            }
			$jSonDeflatedData = MatrixPPC_Utils::gzDecode($gzData);
            MatrixPPC_Utils::cronDebug("RAW DATA FROM API: ".var_export($jSonDeflatedData,true),1);
            $jSonDeflatedData = trim($jSonDeflatedData);
            $dat = MatrixPPC_Utils::getArrayFromJSON($jSonDeflatedData);
            MatrixPPC_Utils::cronDebug("API RECEIVED RAW DATA: " . $jSonDeflatedData,3);
            if (!is_array($dat)) {
                MatrixPPC_Utils::cronDebug("We received a data structure that is not the expected array when contacting the MatrixPPC API and calling the " . $action . " function.",1);
                return array();
            }
            return $dat;
        }
        else{
            if ($this->APIKey == '') {
                MatrixPPC_Utils::cronDebug("STOPPING " . $action . " API CALL BECAUSE NO API KEY PRESENT.",1);
                return array();
            }
		}
        return array();
	}


    /**
     * @since	1.0.0
     * @access	protected
     * @param	string		$url
	 * @param	array		$postParams
     * @return	string
     */
	protected function getURL($url, $postParams = array()) {
		if (!function_exists('wp_remote_post')) {
			require_once ABSPATH . WPINC . '/http.php';
		}
		
		$deflatedPost=gzdeflate(MatrixPPC_Utils::getJSONFromArray($postParams));
		$args = array(
			'timeout'    => 900,
			'user-agent' => "MatrixPPC Plugin " . MatrixPPC_Utils::MATRIXPPC_API_VERSION,
			'body'       => $deflatedPost,
            'sslverify'  => self::SSLEnabled()
		);
		
		try{
			$response = wp_remote_post($url, $args);
		}catch(Exception $e){
			wp_die("We can't reach MatrixPPC API");
		}
		
		if (is_wp_error($response)) {
        	$error_message = $response->get_error_message();
        	MatrixPPC_Utils::cronDebug("There was an " . ($error_message ? '' : 'unknown ') . "error connecting to the the MatrixPPC API" . ($error_message ? ": $error_message" : '.'),1);
        	wp_die("There was an " . ($error_message ? '' : 'unknown ') . "error connecting to the the MatrixPPC API" . ($error_message ? ": $error_message" : '.'));
        }else{
        	$this->lastHTTPStatus = (int) wp_remote_retrieve_response_code($response);
        }
        
		if (200 != $this->lastHTTPStatus) {
			MatrixPPC_Utils::cronDebug("The MatrixPPC API is currently unavailable. This may be for maintenance or a temporary outage. [{$this->lastHTTPStatus}]",1);
			wp_die("The MatrixPPC API is currently unavailable. This may be for maintenance or a temporary outage. [{$this->lastHTTPStatus}]");
		}
		$this->curlContent = wp_remote_retrieve_body($response);
		
		return $this->curlContent;
	}

    /**
     * @since	1.0.0
     * @access	public
     * @param	array	$postParamsNo
     * @return	string
     */
	public function makeAPIQueryString($postParamsNo) {
		$homeURL = MatrixPPC_Utils::getFullUrl(false);
		return self::buildQuery(array(
			'v'         => $this->wordpressVersion,
			's'         => $homeURL,
			'k'         => $this->APIKey,
			'openssl'   => function_exists('openssl_verify') && defined('OPENSSL_VERSION_NUMBER') ? OPENSSL_VERSION_NUMBER : '0.0.0',
			'phpv'      => phpversion(),
            'gzip'		=> $postParamsNo>0 ? '1' : '0',
			'r'			=> rand(1,PHP_INT_MAX)
    	));
	}

    /**
     * @since	1.0.0
     * @access	private
     * @param	array	$data
     * @return	string
     */
	private function buildQuery($data) {
		if (version_compare(phpversion(), '5.1.2', '>=')) {
			return http_build_query($data, '', '&');
		} else {
			return http_build_query($data);
		}
	}

    /**
     * @since	1.0.0
     * @access	private
     * @param	void
     * @return	string
     */
	private function getAPIHost() {
		return (self::SSLEnabled() ? "https://" : "http://").$this->apiHost;
	}

    /**
     * @since	1.0.0
     * @access	private
     * @param	string		$host
     * @return	void
     */
	private function setAPIHost($host){
	    $this->apiHost=$host;
    }

    /**
     * @since	1.0.0
     * @access	public
     * @param	void
     * @return	bool
     */
	public static function SSLEnabled() {
	    return false; //TODO: remove this line when we activate ssl on api
		if (!function_exists('wp_http_supports')) {
			require_once ABSPATH . WPINC . '/http.php';
		}
		return wp_http_supports(array('ssl'));
	}
}

?>
