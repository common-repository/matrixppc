<?php

/**
 * The db-specific functionality of the plugin.
 *
 * @link       https://www.matrixppc.ai
 * @since      1.0.0
 *
 * @package    MatrixPPC
 * @subpackage MatrixPPC/Db
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

class MatrixPPC_Db {

    public static function getBannedIps(){
        $maxIPs=500;

        global $wpdb;

        $query="SELECT ip FROM " . $wpdb->prefix . "mx_ppc_shield 
        ORDER BY update_ts DESC limit ".$maxIPs;

        $results = $wpdb->get_col($query,0);

        return $results;
    }

    /**
     * @since 1.0.0
     * @return void
     */
    public static function resetFraudsters(){
        global $wpdb;
        $wpdb->query("DELETE FROM " . $wpdb->prefix . "mx_ppc_shield");
    }
    /**
     * @since 1.0.0
     * @param $ip
     * @return boolean
     */
    public static function banIP($ip){
        if($ip==""){
            return false;
        }

        MatrixPPC_Utils::cronDebug("ADWORDS Shield identified as [$ip] as fraud!");

        global $wpdb;

        $query = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "mx_ppc_shield
                                (ip, update_ts)
                                 VALUES(%s, NOW())
                                 ON DUPLICATE KEY UPDATE
                                 update_ts = NOW()
                                 ", $ip);
        $wpdb->query($query);

        return self::getBannedIps();
    }

    /**
     * @since 1.0.0
     * @access public
     * @param string $url
     * @return boolean|integer
     */
    public static function addUrls($url){
        if($url==""){
            return false;
        }
        global $wpdb;
        $query = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "mx_ppc_urls
                                (url, url_plain, updates, update_ts)
                                 VALUES('%s', '%s', '%d', '%s')
                                 ON DUPLICATE KEY UPDATE
                                 updates = updates + 1,
                                 update_ts = '%s'
                                 ", md5($url), $url, 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
        $wpdb->query($query);
        return $wpdb->insert_id;
    }

    /**
     * @since 1.0.0
     * @param int $urlId
     * @return mixed
     */
    public static function ignoreAction($urlId){
        global $wpdb;
        $item = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."mx_ppc_urls WHERE id = '%s'", $urlId), ARRAY_A );
        $wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->base_prefix."mx_ppc_ignore(id_url) VALUES('%d')", $item['id']) );
        return $item;
    }

    /**
     * @since 1.0.0
     * @param int $urlId
     * @return mixed
     */
    public static function applyAction($urlId){
        global $wpdb;
        $item = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."mx_ppc_urls WHERE id = '%s'", $urlId), ARRAY_A );
        $wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->base_prefix."mx_ppc_ignore WHERE id = %s", $urlId) );
        return $item;
    }

    /**
     * @since 1.0.0
     * @return mixed
     */
    public static function repopulateActions(){
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM ".$wpdb->base_prefix."mx_ppc_actions ",ARRAY_A );
        return $results;
    }

    /**
     * @since 1.0.0
     * @return mixed
     */
    public static function getCampaigns(){
        global $wpdb;
        $campaigns = $wpdb->get_results("	SELECT urls.id as id, urls.url, urls.url_plain, actions.hash, actions.action_id, actions.data 
											FROM ".$wpdb->base_prefix."mx_ppc_urls as urls 
											INNER JOIN ".$wpdb->base_prefix."mx_ppc_actions as actions ON urls.url = actions.hash
											LEFT JOIN ".$wpdb->base_prefix."mx_ppc_ignore as ignr ON ignr.id_url = urls.id
											WHERE ignr.id_url IS NULL
											LIMIT 10" ,ARRAY_A);
        return $campaigns;
    }

    /**
     * @since 1.0.0
     * @return mixed
     */
    public static function getIgnored(){
        global $wpdb;
        $ignored_data = $wpdb->get_results("SELECT 
                                                      ignr.id as igid, 
                                                      urls.url_plain as igdata, 
                                                      actions.action_id as action_id, 
                                                      actions.data as actiondata 
                                                FROM ".$wpdb->base_prefix."mx_ppc_ignore ignr 
												JOIN ".$wpdb->base_prefix."mx_ppc_urls urls ON urls.id = ignr.id_url
												JOIN ".$wpdb->base_prefix."mx_ppc_actions actions ON actions.hash = urls.url
												LIMIT 10;
												");
        return $ignored_data;
    }

    /**
     * $since 1.0.0
     * @param string $type
     * @param string $term
     * @return mixed
     */
    public static function getSearch($type, $term){
        global $wpdb;
        $search_result = $wpdb->get_results($wpdb->prepare("SELECT urls.id as id_website, actions.action_id, actions.data, urls.* FROM ".$wpdb->base_prefix."mx_ppc_urls as urls
						LEFT JOIN ".$wpdb->base_prefix."mx_ppc_actions as actions ON urls.url = actions.hash
						WHERE actions.action_id <> 2
						AND url_plain $type '%s' LIMIT 10", $term), ARRAY_A);
        return $search_result;
    }

    /**
     * @since 1.0.0
     * @param string $md5
     * @param int $action
     * @param string $payload
     */
    public static function setActions($md5, $action, $payload){
        global $wpdb;
        $query = $wpdb->prepare("INSERT
    			INTO " . $wpdb->base_prefix . "mx_ppc_actions( `hash`,`action_id`,`data` )
    			VALUES( '%s', %d,'%s' )
    			ON DUPLICATE KEY UPDATE data='%s'", $md5, $action, $payload, $payload);
        $wpdb->query($query);
    }

    /**
     * @since 1.0.0
     * @param $id
     * @return mixed
     */
    public static function getIgnoredRulesNo($id){
        global $wpdb;
        $results = $wpdb->get_row("SELECT count(*) AS `total` FROM " . $wpdb->prefix . "mx_ppc_ignore WHERE `id_url` = " . $id, ARRAY_A);
        return $results;
    }

    /**
     * This function gets the path relatively to storage base.
     * @since   1.0.0
     * @access  public
     * @param   void
     * @return  string
     */
    public static function getBaseStoragePath()
    {
        global $wpdb;
        $basePath = array();
        $basePath[] = WP_CONTENT_DIR;

        $basePath[] = MatrixPPC_Utils::MATRIXPPC;

        // add the MU parameter
        if ($wpdb->prefix != $wpdb->base_prefix) {
            $basePath[] = "mu";
            $basePath[] = $wpdb->blogid;
        }

        $argList = func_get_args();

        return str_replace(
            array('/', '\\'),
            DIRECTORY_SEPARATOR,
            implode(DIRECTORY_SEPARATOR, array_merge($basePath, $argList))
        );
    }

}
