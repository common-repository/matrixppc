<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}


/**
 * This class is responsible for capturing Search Engine and visitors coming from search engines requests.
 * @since      1.0.0
 * @package    MatrixPPC
 * @subpackage MatrixPPC/includes
 * @author     MatrixPPC <support@matrixppc.ai>
 */
class MatrixPPC_Reactor {

    /**
     * MatrixPPC
     * @since 1.0.0
     * @access private
     * @var MatrixPPC_Reactor
     */
    private static $instance;

    /**
     * @since 1.0.0
     * @access private
     * @var bool
     */
	private static $react=true;

    /**
     * @since 1.0.0
     * @access private
     * @var array
     */
	private static $boldedWords=array();

    /**
     * This function initializes the class and set its properties.
     * @since   1.0.0
     * @access   public
     * @param   void
     * @return  MatrixPPC_Reactor
     */
    public static function getInstance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * This function initializes the class and set its properties.
     * @since   1.0.0
     * @access  public
     * @param   void
     */
    public function __construct( ) {

    }

    /**
     * This function populates the urls table.
     * @since   1.0.0
     * @access  private
     * @param   void
     * @return  void
     */
    private static function populateSearchEngineTable(){
        MatrixPPC_Utils::cronDebug("Populating search engine table...",3);
        $filesData = self::getSearchEngineFilesAsArray(false);
        foreach ($filesData as $data) {
            $items = explode("\n", $data);
            foreach ($items as $item) {
                if ($item != '') {
                    $itemData = explode("\t", $item);
                    if (isset($itemData[1]) && filter_var($itemData[1], FILTER_VALIDATE_URL) !== false) {
                        MatrixPPC_Db::addUrls($itemData[1]);
                    }
                }
            }
        }
        MatrixPPC_Utils::cronDebug("Search engine table populated.", 1);
    }

	/**
	 * This function gets the Search Engine files and creates an array.
	 * @since   1.0.0
	 * @access  private
	 * @param   boolean $deleteAfterCall    Determine if delete or not the files after they were sent to the API
	 * @return  array   List of search engine files
	 */
	private static function getSearchEngineFilesAsArray($deleteAfterCall = true){
		$se_files = array();
		$theFiles = glob(MatrixPPC_Utils::getSearchEnginesDirectory('*.php'));
		foreach ($theFiles as $file) {
			if (basename($file) != "index.php") {
				$se_files[basename($file)] = MatrixPPC_Utils::getSafeFileContents($file);
				if ($deleteAfterCall) {
					MatrixPPC_Utils::$deleteFilesQueue[] = $file;
				}
			}
		}
		MatrixPPC_Utils::cronDebug("Got search engine files as array.",2);
		return $se_files;
	}


    /**
     * This function writes the HTML page source code to file when is visited by a search engine.
     * @since   1.0.0
     * @access  private
     * @param   string  $html
     * @return  void
     */
    private static function setSearchEngineToFile($html = ''){
        $data = array(
            "ip"        => MatrixPPC_Utils::getVisitorIP(),
            "url"       => MatrixPPC_Utils::getFullUrl(),
            "date"      => MatrixPPC_Utils::getCurrentTime(),
            "words"     => self::getPowerWordsTextVersion($html)
        );

        $row=MatrixPPC_Utils::buildLogRow($data);

        MatrixPPC_Utils::writeLogRow("s_file",$row);

        MatrixPPC_Utils::cronDebug("Search engine visit set to file.", 1);

    }

    /**
     * This function is deleting marked files from local storage after OK received from API.
     * @since   1.0.0
     * @access  private
     * @param   void
     * @return  void
     */
    private static function processDeleteFilesQueue(){
        MatrixPPC_Utils::cronDebug("Deleting files marked for deletion...", 3);
        foreach (MatrixPPC_Utils::$deleteFilesQueue as $typeFiles) {
        	foreach($typeFiles as $fileForDeletion) {
		        MatrixPPC_Utils::deleteFile( $fileForDeletion );
	        }
        }
        MatrixPPC_Utils::$deleteFilesQueue = array();
        MatrixPPC_Utils::cronDebug("Deleted files marked for deletion.", 2);
    }

    /**
     * This function gets the power words from HTML.
     * @since   1.0.0
     * @access  private
     * @param   string  $html
     * @return  string
     */
    private static function getPowerWordsTextVersion($html){
        $text = MatrixPPC_Utils::convertEncoding($html);
        $text = mb_strtolower($text);
        $text = preg_replace("/&(?:[a-z]+|#x?\d+);/iu", "", $text);
        $text = preg_replace('#<head.*?>([\s\S]*?)<\/head>#iu', '', $text);
        $text = preg_replace('#<script.*?>([\s\S]*?)<\/script>#iu', '', $text);
        $text = preg_replace('#<style.*?>([\s\S]*?)<\/style>#iu', '', $text);
        $text = preg_replace('#<a .*?>([\s\S]*?)<\/a>#iu', '', $text);

        $text = strip_tags(str_replace("<", " <", $text)); // strip tags keep space
        $text = preg_replace('/[\x00-\x1F\x7F\p{P}+]/u', ' ', $text); // remove special chars
        $text = MatrixPPC_Utils::singleSpacing($text);

        $words = explode(" ", $text);
        $wordsScore = array();
        foreach ($words as $key => $word) {
            if (strlen($word) > 3 && !is_numeric($word)) {

                if (isset($wordsScore[$word])) {
                    $wordsScore[$word]++;
                } else {
                    $wordsScore[$word] = 1;
                }
            }
        }
        arsort($wordsScore);
        $wordsScore = array_keys($wordsScore);
        $result = implode(" ", $wordsScore);
        MatrixPPC_Utils::cronDebug("Got ".count($wordsScore)." power words from HTML: ".$result.".",3);
        return $result;
    }

    /**
     * getRulesFromFile($url)
     * This function gets the rules from File and sets them into $this->rules[action]=data.
     * @since   1.0.0
     * @access  private
     * @param   string  $url    The current URL
     * @return 	array	The rules array
     */
    private static function getRulesFromFile($url){
        MatrixPPC_Utils::cronDebug("Getting current URL rules from file...",3);
        $md5 = md5($url);
        $rulesFile = MatrixPPC_Utils::getActionsDirectory(MatrixPPC_Utils::getPartialPath($md5));
        $rules = array();

        if (file_exists($rulesFile)) {
            $myRows = MatrixPPC_Utils::getSafeFileContents($rulesFile);
            $myRows = MatrixPPC_Utils::getArrayFromJSON($myRows);
            if ($myRows == false) {
                MatrixPPC_Utils::cronDebug("Can not get action rules from file.", 1);
                return $rules;
            }
            foreach ($myRows as $key => $row) {
                $rules[$key] = $row;
            }
        }
        MatrixPPC_Utils::cronDebug("Got URL action rules from file.", 1);
        return $rules;
    }

    private static function getAllRulesFromFile(){
        $dir = MatrixPPC_Utils::getActionsDirectory();
        $items = glob($dir."/*");
        $actionFiles = array();
        foreach($items as $item){
            if(is_dir($item)){
                $subItems = glob($item."/*");
                foreach($subItems as $subItem){
                    if(is_dir($subItem)){
                        $thirdItems = glob($subItem."/*");
                        foreach($thirdItems as $titem){
                            if(is_dir($titem)){
                                $files = glob($titem."/*");
                                foreach($files as $file){
                                    array_push($actionFiles, $file);
                                }
                            }
                        }
                    }
                }
            }
        }
        $rules = array();
        $i = 0;
        foreach($actionFiles as $ruleFile){
            if (file_exists($ruleFile)) {
                $myRows = MatrixPPC_Utils::getSafeFileContents($ruleFile);
                $myRows = MatrixPPC_Utils::getArrayFromJSON($myRows);
                if ($myRows == false) {
                    MatrixPPC_Utils::cronDebug("Can not get action rules from file.", 1);
                    return $rules;
                }
                foreach ($myRows as $key => $row) {
                    $rules[$i] = $row;
                    $i++;
                }
            }
        }

        $rules = implode(" ", $rules);
        $unique = explode(" ", $rules);
        $unique = array_unique($unique);
        $unique = implode(" ", $unique);
        $returnRules = array();
        $returnRules[1] = $unique;
        //MatrixPPC_Utils::cronDebug("RULES1[".var_export($returnRules, true)."]", 1);

        return $returnRules;
    }

    /**
     * This function detects the visitor type.
     * @since   1.0.0
     * @acces   public
     * @param   void
     * @return  void
     */
    public function detectAndSaveVisitor(){
        $currentURL=MatrixPPC_Utils::getFullUrl();

        $skipUrlStrings=array("/feed/",".css",".js",".ico",".htaccess");

        foreach($skipUrlStrings as $skipUrlString){
            $check=strpos($currentURL,$skipUrlString);
            if( $check !== false){
                self::$react = false;
            }
        }

        if(
            self::$react                        &&
            !is_admin()                         &&
            !defined( 'DOING_CRON' )
        ){
            MatrixPPC_Utils::cronDebug("Detecting [ ".$currentURL." ] visitor type...",3);
            ob_start( array('MatrixPPC_Reactor', 'obStart') );
        }
    }

    /**
     * @since 1.0.0
     * @param string $visitorIP
     * @return boolean
     */
    public static function checkFraud($visitorIP){
        MatrixPPC_Utils::cronDebug("Checking fraud for [".$visitorIP."]",3);
        if($visitorIP==""){
            return false;
        }
        $separator="\t";
        $maxHistoryDays=8;
        $check=array();

        $filename=MatrixPPC_Utils::getIPsDirectory(
            MatrixPPC_Utils::getPartialPath(
                md5($visitorIP)
            )
        );
        if(file_exists($filename)){
            $result=false;
            $content=MatrixPPC_Utils::getSafeFileContents($filename);
            $list=explode($separator,$content);
            unset($list[0]); // remove the ip
            $list[]=time();
            foreach($list as $key=>$value){

                // clear history after $maxHistoryDays
                if($value-time()>$maxHistoryDays*24*60*60){
                    unset($list[$key]);
                }

                if($value-time()<=MatrixPPC_Config::get("mx_ppc_algo_fraud_days")*24*60*60){
                    $check[]=$value;
                    if(count($check)>=MatrixPPC_Config::get("mx_ppc_algo_fraud_clicks")){
                        $result=true;
                    }
                }
            }
            MatrixPPC_Utils::setSafeFileContents(
                $filename,
                $visitorIP.$separator.implode($separator,$list)
            );
            MatrixPPC_Utils::cronDebug($result===false?"!Not fraud [$visitorIP].":"Fraud [$visitorIP]",3);
            return $result;
        }
        else{
            MatrixPPC_Utils::setSafeFileContents($filename,$visitorIP.$separator.time());
            MatrixPPC_Utils::cronDebug("Not fraud - [$visitorIP].",3);
        }
        return false;
    }

    /**
     * @param $visitorIP
     */
    public static function updateBlockList($visitorIP){
        if($visitorIP==""){
            return;
        }
        $separator="\n";
        $initial = MatrixPPC_Db::getBannedIps();
        $after = MatrixPPC_Db::banIP($visitorIP);
        $dif=array_diff($after,$initial);
        // only if different make the call
        if(count($dif)>0) {
            $list = implode($separator, $after);
            if ($list !== false) {
                $api = MatrixPPC_API::getInstance();

                try{
                    $response = $api->call(
                        "block-list",
                        array(),
                        array(
                            'ccid' => MatrixPPC_Config::get("mx_ppc_adw_client_customer_id"),
                            'block' => $list
                        )
                    );
                }catch(Exception $e){
                    MatrixPPC_Utils::cronDebug($e->getMessage(), 3);
                }

                if (
                    (is_array($response)) &&
                    ($response['ok'] == true)
                ) {
                    MatrixPPC_Utils::cronDebug("Updated block-list with [$visitorIP].", 3);
                } else {
                    MatrixPPC_Utils::cronDebug("Update block-list with [$visitorIP] failed.", 3);
                }
            }
        }
    }

    public static function obStart($html){
        $currentURL=MatrixPPC_Utils::getFullUrl();

        if(self::detectAdBotByIP()){
            MatrixPPC_Utils::cronDebug("Search engine detected on [ ".$currentURL." ].",1);

            // BOLD ALGO
            if(MatrixPPC_Config::get("mx_ppc_algo_bold")=="1") {
                self::setSearchEngineToFile($html);
            }

        }

        elseif(self::detectReferredByAdWords()){

            MatrixPPC_Utils::cronDebug("Visitor referred by search engine detected on [ ".$currentURL." ].",1);

            // UPDATE REFERRER STATISTICS
            MatrixPPC_Config::set(
                'mx_ppc_total_ref',
                (int)MatrixPPC_Config::get('mx_ppc_total_ref')+1
            );

            // ADWORDS FRAUD ALGO
            if(MatrixPPC_Config::get("mx_ppc_algo_fraud")=="1"){
                $visitorIP=MatrixPPC_Utils::getVisitorIP();
                if(self::checkFraud($visitorIP)){
                    self::updateBlockList($visitorIP);
                }
            }
        }

        return self::alterContent($html);
    }

    /**
     * This function detects visitor by Search Engine Referrer.
     * @since   1.0.0
     * @access  private
     * @param   void
     * @return  boolean
     */
    private static function detectReferredByAdWords(){
        MatrixPPC_Utils::cronDebug("Detecting referred by search engine...",3);
        $pageUrl            =   MatrixPPC_Utils::getFullUrl();
        $visitorReferer     =   MatrixPPC_Utils::getVisitorReferer();
        if($visitorReferer  ==  "") {
            MatrixPPC_Utils::cronDebug("Not referred by search engine.(1)",3);
            return false;
        }
        $seList             =   MatrixPPC_Utils::getReferrerMatchesFromFile();
        $visitorRefererHost =   parse_url($visitorReferer, PHP_URL_HOST);
        $visitorRefererQuery =   parse_url($visitorReferer, PHP_URL_QUERY);

        if(!isset($visitorRefererQuery) || $visitorRefererQuery == null){
            MatrixPPC_Utils::cronDebug("Not referred by search engine.(2)",3);
            return false;
        }

        parse_str(parse_url($pageUrl, PHP_URL_QUERY), $queryparams);

        if(!isset($queryparams['gclid']) || empty($queryparams['gclid'])){
            MatrixPPC_Utils::cronDebug("Not referred by search engine.(3)",3);
            return false;
        }

        foreach($seList as $ref){
            $ref = trim($ref);
            if(
                $ref != "" &&
                preg_match( $ref, $visitorRefererHost)
            ){
                MatrixPPC_Utils::cronDebug("Referred by search engine.",2);
                return true;
            }
        }
        MatrixPPC_Utils::cronDebug("Not referred by search engine(4).",3);
        return false;
    }

    /**
     * @since   1.0.0
     * @access  public
     * @param   array   $schedules
     * @return  array   $schedules
     */
    public static function cronAddMatrixPPC($schedules) {
        if(isset($schedules['mx_ppc_interval'])) {
            return $schedules;
        }
        $schedules['mx_ppc_interval'] = array(
            'interval' => MatrixPPC_Config::getCallInterval(),
            'display' => __('MatrixPPC Interval')
        );
        return $schedules;
    }

    /**
     * This function detects the Search Engine by IP.
     * @since   1.0.0
     * @access  private
     * @param   void
     * @return  boolean
     */
    private static function detectAdBotByIP(){
        // TODO: cache the result
        $ips			= MatrixPPC_Utils::getSearchEngineIPsFromFile();
        $visitorIP		= trim(MatrixPPC_Utils::getVisitorIP());
        foreach( $ips as $ip ){
            if( MatrixPPC_Utils::isIpInRange( $visitorIP, trim($ip) ) ){
                return true;
            }
        }
        return false;
    }

    /**
     * This function writes the actions to file.
     * @since   1.0.0
     * @access   public
     * @param   string  $md5        Md5 of current URL
     * @param   int     $action     Id of action
     * @param   string  $payload    The payload for specified action
     * @return  void
     */
    public function setDataToFile($md5, $action, $payload ){
        MatrixPPC_Utils::cronDebug("Writing actions to file...",3);
        $myRows=array();
        $actionsFile=MatrixPPC_Utils::getActionsDirectory( MatrixPPC_Utils::getPartialPath($md5) );
        if( file_exists( $actionsFile ) ){
            $tmpData=MatrixPPC_Utils::getSafeFileContents( $actionsFile );
            $myRows=MatrixPPC_Utils::getArrayFromJSON($tmpData);
        }
        $myRows[$action]=$payload;
        $tmpData=MatrixPPC_Utils::getJSONFromArray( $myRows );
        MatrixPPC_Utils::setSafeFileContents( $actionsFile, $tmpData );
        MatrixPPC_Utils::cronDebug("Wrote actions to file.",2);
    }

    /**
     * This function prepares the data received from API on the sync-data call.
     * @since   1.0.0
     * @access  public
     * @param   array   $readyData
     * @return  void
     */
    public function prepareData($readyData=Array() ){
        MatrixPPC_Utils::cronDebug("Preparing received data from API...",3);

        foreach ( $readyData as $md5=>$data ){

            $words = array();

	        /**
	         * Statistics update ACT
	         */
	        $countData=count($data);
            if($countData > 0) {
            	$configTotalAct=(int)MatrixPPC_Config::get('mx_ppc_total_act');
                MatrixPPC_Config::set('mx_ppc_total_act', $configTotalAct + $countData );
	            MatrixPPC_Utils::cronDebug("Total actions [ ".$configTotalAct." ] incremented by [ ".$countData." ]",2);
            }

            foreach($data as $key=>$value){     // each action for current url (one URL may have multiple actions)

                $this->setActionInDB( $md5, $key, $value );
                $this->setDataToFile( $md5, $key, $value );
                $newWords = explode(' ', $value);
                if($key==1) {
                    foreach($newWords as $w){
                        if( strlen($w) > 3){
                            array_push($words, $w);
                        }
                    }
                }
            }
        }

    }

    /**
     * This function records current action to DataBase.
     * @since   1.0.0
     * @access  private
     * @param   string      $md5        Md5 of current URL
     * @param   int         $action     The action ID
     * @param   string      $payload    The payload for specified action
     * @return  void
     */
    private function setActionInDB($md5, $action, $payload ){
        if($payload != "") {
            MatrixPPC_Utils::cronDebug("Writing action to DB...", 3);
            MatrixPPC_Db::setActions($md5, $action, $payload);
            MatrixPPC_Utils::cronDebug("Wrote action to DB.", 2);
        }
        else{
            MatrixPPC_Utils::cronDebug("Empty action skipped from writing to database.",2);
        }
    }

    /**
     * This function is sending the data colected to the API.
     * @since   1.0.0
     * @access  public
     * @param   boolean $actualSend
     * @return  void
     */
    public function send_data($actualSend=true){
        MatrixPPC_Utils::cronDebug("Cron activated at ".date("Y-m-d H:i:s"), 1);

        self::populateSearchEngineTable();
        $api		                                    =   MatrixPPC_Api::getInstance();
        $reactor	                                    =   MatrixPPC_Reactor::getInstance();
        MatrixPPC_Utils::$deleteFilesQueue              =   array();
        $data                                           =   array();

        if($actualSend===true) {
            list($data, MatrixPPC_Utils::$deleteFilesQueue) = MatrixPPC_Utils::buildSyncDataPackage(MatrixPPC_Utils::getMemoryLimit());
        }
        $data['ccid']=MatrixPPC_Config::get("mx_ppc_adw_client_customer_id");

        $response = $api->call('sync-data',array(),$data);

        if(
            (is_array($response))           &&
            ($response['ok']==true)
        ){
            // Update ccid status
            if(isset($response['ccid_status'])){
                MatrixPPC_Config::set("mx_ppc_adw_client_customer_id_status",(string)$response['ccid_status']);
            }
            if(isset($respose['upgrade'])){
                if($response['upgrade']==true){
                    MatrixPPC_Config::set("mx_ppc_need_upgrade","1");
                }
                else{
                    MatrixPPC_Config::set("mx_ppc_need_upgrade","2");
                }
            }

            if( isset($response['actions'])     &&
                !empty($response['actions'])
            ) {
                $reactor->prepareData($response['actions']);
            }

            self::processDeleteFilesQueue();

	        /**
	         * Statistics update S & R
	         */
	        $countSFiles=0;
	        foreach($data['s_files'] as $sFile){
	        	$rows=explode("\n",$sFile);
	        	$countSFiles+=count($rows);
	        }

            if($countSFiles > 0) {
            	$configTotalS=(int)MatrixPPC_Config::get('mx_ppc_total_se');
                MatrixPPC_Config::set( 'mx_ppc_total_se', $configTotalS + $countSFiles );
                MatrixPPC_Utils::cronDebug("Total search engine visits [ ".$configTotalS." ] incremented by [ ".$countSFiles." ]",2);
            }

			// --

            MatrixPPC_Utils::cronDebug("Collected data sent to API", 2);
        }
    }

    /**
     * This function gets specified ignored URL rules number from database.
     * @since   1.0.0
     * @access  private
     * @param 	int  $id
     * @return 	int
     */
    private static function getIgnoreRulesNumberForUrlFromDatabase($id){
        if(is_numeric($id)) {
            MatrixPPC_Utils::cronDebug("Getting specified ignored URL rules number from database...", 3);
            $results = MatrixPPC_Db::getIgnoredRulesNo($id);

            MatrixPPC_Utils::cronDebug("Got specified ignored URL rules number from database:" . $results['total'] . ".", 3);
            return $results['total'];
        }
        return 0;
    }

	/**
	 * @since   1.0.0
	 * @access  public
	 * @param   string  $content
	 * @return  string  mixed
	 */
    public static function boldWords($content) {
        global $wpdb;

        $currentURL = MatrixPPC_Utils::getFullUrl();

        $urls = $wpdb->get_row("SELECT `id` FROM " . $wpdb->prefix . "mx_ppc_urls WHERE `url_plain`='" . $currentURL . "'", ARRAY_A);
        $ignore = self::getIgnoreRulesNumberForUrlFromDatabase($urls['id']);

        if($ignore==0) {
            MatrixPPC_Utils::cronDebug( "Applying bold words...", 1 );

            if (MatrixPPC_Config::get('mx_ppc_algo_bold_globally') == '1') {
                $rules = self::getAllRulesFromFile();
            } else {
                $rules = self::getRulesFromFile($currentURL);
            }

            if (isset($rules[MatrixPPC_Utils::$actionsId['bold']])) {
                $wordsToBold = explode(" ", $rules[MatrixPPC_Utils::$actionsId['bold']]);
                shuffle($wordsToBold); // randomize the words
                if (count($wordsToBold) > 0) {
                    foreach ($wordsToBold as $wordToBold) {
                        $wordPositions = self::getStringPositions($content, $wordToBold);

                        if (count($wordPositions) > 0) {
                            $positionWords = self::getPositionWords($content, $wordToBold, $wordPositions);

                            foreach ($positionWords as $positionWord) {
                                // MAX BOLDED SAME WORD PER PAGE
                                $alreadyBoldedThisWord = 0;
                                foreach (self::$boldedWords as $boldedWord) {
                                    if ($boldedWord == $wordToBold) {
                                        $alreadyBoldedThisWord += 1;
                                    }
                                }
                                if ($alreadyBoldedThisWord >= MatrixPPC_Config::get("mx_ppc_algo_bold_mbpps")) {
                                    break;
                                }
                                // ---

                                $content = self::boldWord(
                                    $content,
                                    $wordToBold,
                                    $positionWord
                                );

                                // MAX BOLDED TOTAL PER PAGE
                                if (count(self::$boldedWords) >= MatrixPPC_Config::get("mx_ppc_algo_bold_mbpp")) {
                                    return $content;
                                }
                                // ---
                            }
                        }

                    }
                }
            }

        }
        else{
            MatrixPPC_Utils::cronDebug( "NOT applying bold words (ignored URL)...", 1 );
        }
	    return $content;
	}

    /**
     * @param $content
     * @param $word
     * @param $wordPositions
     * @return array
     */
	public static function getPositionWords($content, $word, $wordPositions){
        $result=array();
        $posWords=array();
        $wordPositions = array_reverse($wordPositions);

        foreach ($wordPositions as $wordPosition){
            $posWord=$wordPosition."_".$word;
            $posWords[]=$posWord;
            $content=substr($content,0,$wordPosition).
                $posWord.
                substr($content,$wordPosition + strlen($word) - 1, strlen($content));
        }

        $content = MatrixPPC_Utils::singleSpacing(strip_tags($content));

        foreach ($posWords as $positionWord){
            if(stripos($content,$positionWord)!==false){
                $expPosWord=explode("_",$positionWord);
                $result[]=$expPosWord[0];
            }
        }

        return $result;
    }

    /**
     * Returns an array with all the positions of $word in $content
     * @param $content
     * @param $word
     * @return array
     */
    public static function getStringPositions($content,$word){
        $lastPos = 0;
        $positions = array();

        while (($lastPos = stripos($content, $word, $lastPos))!== false) {
            $beforeLetter=$content[$lastPos-1];
            $afterLetter=$content[$lastPos+strlen($word)];
            if(
                !ctype_alnum($beforeLetter) &&
                !ctype_alnum($afterLetter)
            ) {
                $positions[] = $lastPos;
            }

            $lastPos = $lastPos + strlen($word);
        }
        return $positions;
    }

    /**
     * @since   1.0.0
     * @access  public
     * @param   string  $pos
     * @param   string  $content
     * @param   int     $ord
     * @return  string  mixed
     */
	public static function getWordTags($content, $pos, $ord = 1){
	    $allowedClosedTagsAnalize=1; //TODO: the lower... the faster...
        $closedTagsAnalized=0;

	    $tmpE=explode(" ",substr($content,$pos,strlen($content)));

/*	    MatrixPPC_Utils::cronDebug("Analizam [".
            $tmpE[0].
            "][".$ord."]",1);*/

        $excluded = array(
            'link',
            'meta',
            'script',
            'br',
            'hr',
            'embed',
            'img',
            'input',
            'command',
            'keygen',
            'source',
            'track',
            'wbr',
            'base',
            'basefont',
            'frame',
            'area',
            'col',
            'param'
        );

        $howManyFound=0;
        $found=false;
        $content = substr($content, 0, $pos-1);

        $tmp = explode('>', $content);
	    $tmp = array_map("trim",$tmp);

        $cTmp = count($tmp);

	    if($cTmp<=1) {
            return "";
        }
        $i = 0;
	    $curPos=$cTmp-1;

        while (!$found && isset($tmp[$curPos])) {
            $curPos = $cTmp - 1 - $i;

            $firstWord = MatrixPPC_Utils::firstWord($tmp[$curPos]);
            $lastWord = MatrixPPC_Utils::lastWord($tmp[$curPos]);

            while(
                (isset($tmp[$curPos][count($tmp[$curPos])-1]) &&
                $tmp[$curPos][count($tmp[$curPos])-1]=="/")
            ){
                $curPos-=1;
                $i+=1;
            }

            $startTag = explode('<', $tmp[$curPos]);
            $cStartTag = count($startTag);

            if($cStartTag<=0) {
                return '';
            }

            $checkCloseTag = strpos($tmp[$curPos], '</');

            if ($checkCloseTag !== false) {
                //MatrixPPC_Utils::cronDebug("[close tag detected - ".$lastWord." ]",3);
                $closedTagsAnalized+=1;
                if($closedTagsAnalized>$allowedClosedTagsAnalize){
                    return 'ok';
                }
                $closeTag=substr($lastWord,1,strlen($lastWord));
                $i+=1;
                $skippedCloseTag=false;
                $tmpI=$curPos;
                while($tmpI>0 && !$skippedCloseTag){
                    $tmpI--;

                    $fWord=MatrixPPC_Utils::firstWord($tmp[$tmpI]);
                    $i+=1;

                    if($closeTag == $fWord) {
                        $skippedCloseTag=true;
                        //MatrixPPC_Utils::cronDebug(" Skipping [".$fWord." == ".$lastWord." ]",1);
                        break;
                    }
                    else{
                        //MatrixPPC_Utils::cronDebug("NOT Skipping [".$fWord." != ".$lastWord." ]",1);
                    }
                }
            } else {
                $i = $i + 1;
                if (isset($startTag[1]) && $startTag[1] != null ) {
                    $result = MatrixPPC_Utils::firstWord($startTag[1]);

                    $avoidTags = array('title', 'textarea');
                    if(in_array($result, $avoidTags)){
                        $result=MatrixPPC_Utils::$boldTags[0];
                        //$result="avoided";
                    }
                    if(!in_array($result,$excluded)) {
                        $howManyFound++;
                    }
                    if($howManyFound == $ord) {
                        //MatrixPPC_Utils::cronDebug("[".$firstWord."][".$result."]",1);
                        return ($result);
                    }
                }
            }

            if($curPos < 0){
                return '';
            }
        }

        return '';
    }

    /**
     * @since 1.0.0
     * @access public
     * @param $content
     * @param string $word The word to bold
     * @param int $pos Position of the word to bold
     * @return string
     */
	public static function boldWord($content,$word,$pos){
	    // Fallback for high Memory Usage.
        $memoryLimit=MatrixPPC_Utils::getMemoryLimit();
        $memoryUsed=memory_get_usage();
        $usedPerc=ceil(100 * $memoryUsed / $memoryLimit);
        if($usedPerc>75){
            MatrixPPC_Utils::cronDebug("Memory usage to high... aborting bold operation for ".$word.".",3);
            return $content;
        }

	    $boldTag=MatrixPPC_Utils::$boldTags[rand(0,count(MatrixPPC_Utils::$boldTags)-1)];
	    if(!self::isBoldWord($content,$pos)) {
	        self::$boldedWords[]=$word;
	        MatrixPPC_Utils::cronDebug("Bolded [$word]",3);
            $boldWord = "<".$boldTag.">" . substr($content,$pos,strlen($word)) . "</".$boldTag.">";
            $content = substr($content,0,$pos)
                .$boldWord
                .substr($content,$pos+strlen($word),strlen($content));
        }
        return $content;
    }

    /**
     * Is the word at $pos position in $content bold or not?
     * @param $content
     * @param $pos
     * @param int $checkLevels
     * @return bool
     */
    public static function isBoldWord($content,$pos, $checkLevels=2){
        if(MatrixPPC_Config::get("mx_ppc_algo_bold_light")=="1"){
            return false;
        }
	    for($i=1; $i<=$checkLevels; $i++){
	        $tag=self::getWordTags($content,$pos,$i);
	        $tag=strtolower($tag);
	        if(in_array($tag,MatrixPPC_Utils::$boldTags)){
	            return true;
            }
        }
	    return false;
    }

    public static function applySignature($content){
        if(MatrixPPC_Config::get("mx_ppc_signature_active")==="1") {
            $content = preg_replace( '/' . preg_quote( '</title>', '/' ) . '/', "</title>\n<!-- Website enhanced by https://www.MatrixPPC.ai/ -->", $content, 1 );
        }
        return $content;
    }

	/**
	 * @since   1.0.0
	 * @access  public
	 * @param   string  $content
	 * @return  string
	 */
	public static function alterContent($content ) {
	    if(MatrixPPC_Config::get("mx_ppc_algo_bold")=="1") {
            $content = self::boldWords($content);
        }

        $content=self::applySignature($content);
		return $content;
	}
}

