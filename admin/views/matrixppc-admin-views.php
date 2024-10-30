<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.matrixppc.ai
 * @since      1.0.0
 *
 * @package    MatrixPPC
 * @subpackage MatrixPPC/admin/partials
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

?>

<div class="header_wrap">
    <div class="pull-left">
        <a href="https://www.matrixppc.ai/" target="_blank" style="float:left;">
            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/logo_matrixppc.png",__FILE__)); ?>" alt="" title="MatrixPPC">
        </a>
    </div>

    <div class="hamburger">
        <a href="" class="btn btn-hamburger">&#9776;</a>
    </div>
    <?php echo $tabs; ?>
</div>

<?php if($tab == 'stats'): ?>
    <ul class="stats-container">
        <li>
            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/search_engine_visits.png",__FILE__)); ?>">
            <strong><?php _e('Search engine visits', MatrixPPC_Utils::MATRIXPPC); ?></strong>
            <span class="pull-right stats-content">
                    <?php echo MatrixPPC_Config::get('mx_ppc_total_se'); ?>
                </span>
        </li>
        <li>
            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/visitors_search_engines.png",__FILE__));?>">
            <strong><?php _e('Visitors from search engines', MatrixPPC_Utils::MATRIXPPC); ?></strong>
            <span class="pull-right stats-content">
                    <?php echo MatrixPPC_Config::get('mx_ppc_total_ref'); ?>
                </span>
        </li>
        <li>
            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/actions_30days.png",__FILE__));?>">
            <strong><?php _e('Total Actions Received (from API)', MatrixPPC_Utils::MATRIXPPC); ?></strong>
            <span class="pull-right stats-content">
                    <?php echo MatrixPPC_Config::get('mx_ppc_total_act'); ?>
                </span>
        </li>

        <li>
            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/shield.png",__FILE__));?>">
            <strong><?php _e('IPs blocked by AdWords Shield', MatrixPPC_Utils::MATRIXPPC); ?></strong>
            <span class="pull-right stats-content">
                    <?php echo count(MatrixPPC_Db::getBannedIps()); ?>
                </span>
        </li>
    </ul>
<?php elseif ($tab == 'algos'): ?>

    <?php
        $algosStatus=array(
            'bold' => MatrixPPC_Config::get("mx_ppc_algo_bold"),
            'fraud' => MatrixPPC_Config::get("mx_ppc_algo_fraud"),
        );
    ?>
    <div class="matrixppc-algos">
<!--    <div class="bar-container-algos">-->
<!--        <span class="pull-left">-->
<!--            <h2>--><?php //_e('Algos',  MatrixPPC_Utils::MATRIXPPC); ?><!--</h2>-->
<!--        </span>-->
<!--        <span class="pull-right suggest-container">-->
<!--            <a href="https://www.matrixppc.ai/suggestions/?api_key=--><?php //echo MatrixPPC_Config::get("mx_ppc_key"); ?><!--" target="_blank" class="button button-primary">--><?php //_e('Request New Algorithm', MatrixPPC_Utils::MATRIXPPC); ?><!--</a>-->
<!--        </span>-->
<!--    </div>-->
    <br><br>
    <table class="widefat fixed" cellspacing="0" id="algosList">
        <thead>
            <tr>
                <th><?php _e('Algo Name', MatrixPPC_Utils::MATRIXPPC); ?></th>
                <th><?php _e('Description', MatrixPPC_Utils::MATRIXPPC); ?></th>
                <th><?php _e('Settings', MatrixPPC_Utils::MATRIXPPC); ?></th>
                <th><?php _e('Status', MatrixPPC_Utils::MATRIXPPC); ?></th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php _e('Fraud Adwords Clicks Shield', MatrixPPC_Utils::MATRIXPPC); ?></td>
            <td><?php _e('Detect click fraud and stop it.', MatrixPPC_Utils::MATRIXPPC); ?></td>
            <td>
                <a href="#" class="button button-default" id="mxPPCFraudSettings">Settings</a>
                <a href="#" class="button button-default" id="mxPPCFraudList">Block List</a>
            </td>
            <td>
                <input type="radio" name="activeFraud" class="activateFraud" value="1" <?php echo $algosStatus['fraud'] == '1' ? 'checked' : ''; ?>>Active
                <input type="radio" name="activeFraud" class="activateFraud" value="0" <?php echo $algosStatus['fraud'] == '0' ? 'checked' : ''; ?>>Inactive

            </td>
        </tr>
            <tr>
                <td><?php _e('Bold Words', MatrixPPC_Utils::MATRIXPPC); ?></td>
                <td><?php _e('Bold AdWords search keywords on site pages based on &trade;Google searches that lead to your landing pages.', MatrixPPC_Utils::MATRIXPPC); ?></td>
                <td><a href="" class="button button-default" id="mxPPCBoldSettings"><?php _e('Settings', MatrixPPC_Utils::MATRIXPPC); ?></a></td>
                <td>
                    <input type="radio" name="activeBold" class="activateBold" value="1" <?php echo $algosStatus['bold'] == '1' ? 'checked' : ''; ?>>Active
                    <input type="radio" name="activeBold" class="activateBold" value="0" <?php echo $algosStatus['bold'] == '0' ? 'checked' : ''; ?>>Inactive
                </td>
            </tr>
        </tbody>
    </table>
        <div class="middle-button">
            <a href="https://www.matrixppc.ai/suggestions/?api_key=<?php echo MatrixPPC_Config::get("mx_ppc_key"); ?>" target="_blank" class="button button-primary"><?php _e('Request New Algorithm', MatrixPPC_Utils::MATRIXPPC); ?></a>
        </div>
    </div>
    <?php include MatrixPPC_Utils::getBasePath('admin','views','matrixppc-admin-algo-bold.php'); ?>
    <?php include MatrixPPC_Utils::getBasePath('admin','views','matrixppc-admin-algo-fraud.php'); ?>
    <?php include MatrixPPC_Utils::getBasePath('admin','views','matrixppc-admin-algo-fraud-list.php'); ?>

<?php elseif ($tab == 'actions'): ?>
    <div class="matrixppc-actions">
        <br>
        <div class="bar-container">
                <span class="pull-left" style="display: inline-block;width:auto;">
                    <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/actions_30days.png",__FILE__));?>">
                    <h2><?php _e('Actions',  MatrixPPC_Utils::MATRIXPPC); ?></h2>
                </span>`
            <div class="pull-right" style="display: inline-block;">
                <div class="search-actions-form">
                    <form action="<?php echo MatrixPPC_Utils::getFullUrl(); ?>" method="GET" id="sForm">
                        <div>
                            <input type="text" name="searchedterm" class="search-ignore" placeholder="Search URL" id="url-term" value="<?php isset($term) ? esc_attr($term) : ''; ?>">
                        </div>
                        <div>
                            <input type="submit" value="Search" class="button button-default" id="search-url">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="clearfix" style="clear: both;"></div>
        <br>
        <?php
        $domain=MatrixPPC_Utils::getFullUrl(false);
        if( isset($campaigns) && count($campaigns) && !isset($search_result) ): ?>
            <small style="margin-top: 4px; display: inline-block;float: right;"><i><?php _e('Last 10 actions', MatrixPPC_Utils::MATRIXPPC); ?></i></small>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                <tr>
                    <th>URL</th>
                    <th>Action</th>
                    <th>Data</th>
                    <th>Ignore</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($campaigns as $res): ?>
                    <tr>
                        <td alt="<?php echo str_replace($domain,"",$res['url_plain']); ?>">
                            <a href="<?php echo $res['url_plain'];?>" title="<?php echo $res['url_plain'];?>" target="_blank">
                                <?php echo str_replace($domain,"",$res['url_plain']); ?>
                            </a>
                        </td>
                        <td alt="<?php echo $res['action_id'] == 1 ? 'Bold Words' : 'Change Content'; ?>"><?php echo $res['action_id'] == 1 ? 'Bold Words' : 'Change Content'; ?></td>
                        <td alt="<?php echo esc_attr($res['data']); ?>"><?php echo esc_attr($res['data']); ?></td>
                        <td>
                            <a class="action_item" href="#" data-id="<?php echo $res['id']; ?>">Ignore</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif(isset($search_result) && count($search_result)): ?>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                <tr>
                    <th>URL</th>
                    <th>Action</th>
                    <th>Data</th>
                    <th>Ignore</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($search_result as $res): ?>
                    <tr>
                        <td><?php echo str_replace($domain,"",$res['url_plain']); ?></td>
                        <td><?php echo $res['action_id'] == 1 ? 'Bold Words' : 'Change Content'; ?></td>
                        <td><?php echo esc_attr($res['data']); ?></td>
                        <td>
                            <a class="action_item" href="#" data-id="<?php echo $res['id_website']; ?>">Ignore</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <?php if(isset($search_result) && !count($search_result)): ?>
                <em><?php $this->displayNotice('There are no results for the searched term'); ?></em>
                <em><?php _e('There are no actions for the searched term'); ?></em>
            <?php else: ?>
                <em><?php $this->displayNotice('There are no actions.'); ?></em>
                <p><?php _e('You haven\'t received any actions from the MatrixPPC API yet.'); ?></p>
            <?php endif; ?>
        <?php endif; ?>
        <br>
        <?php if(isset($ignored_data) && !empty($ignored_data)): ?>
            <strong>Ignored</strong>
            <small style="margin-top: 4px; display: inline-block;float: right;"><i><?php _e('Last 10 ignored actions', MatrixPPC_Utils::MATRIXPPC); ?></i></small>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                <tr>
                    <th>URL</th>
                    <th>Action</th>
                    <th>Data</th>
                    <th>Apply</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($ignored_data as $res): ?>
                    <tr>
                        <td>
                            <?php echo str_replace($domain,"",$res->igdata); ?>
                        </td>
                        <td>
                            <?php echo $res->action_id == 1 ? 'Bold Words' : 'Change Content'; ?>
                        </td>
                        <td>
                            <?php echo esc_attr($res->actiondata); ?>
                        </td>
                        <td>
                            <a class="remove_ig_item" href="#" data-id="<?php echo $res->igid; ?>">Apply</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php elseif ($tab == 'settings'): ?>
    <div class="matrixppc-settings">
            <div class="third">
                <div class="inside">
                    <div class="title-sep-container">
                        <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/api_key.png",__FILE__));?>" style="margin-left:-2px;margin-right: 15px;margin-top: -7px;">
                        <h2><?php _e('API Key',MatrixPPC_Utils::MATRIXPPC); ?></h2><br>
                    </div>
                    <input type="text" class="apiKeyValue" readonly="readonly" value="<?php echo MatrixPPC_Config::getKey() == false ? __('There is no key',MatrixPPC_Utils::MATRIXPPC) : MatrixPPC_Config::getKey(); ?>">
                    <div class="element-info">
                        <?php
                        $needUpgrade=MatrixPPC_Config::get("mx_ppc_need_upgrade");
                        if($needUpgrade=="0" || $needUpgrade=="1") {
                            _e('Upgrade to <b><a href="https://www.matrixppc.ai/?op=upgrade&key=');
                            echo MatrixPPC_Config::get("mx_ppc_key");
                            _e('" target="_blank">MatrixPPC PRO</a></b>.');
                        }
                        elseif($needUpgrade=="2"){
                            _e("You are using <b><a href=\"https://www.matrixppc.ai/\" target=\"_blank\">MatrixPPC PRO</a></b>.");
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="third" id="debugLevel">
                <div class="inside">
                    <div class="title-sep-container">
                        <div class="" style="margin-top: -5px;">
                            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/debug.png",__FILE__));?>">
                            <h2>Debug</h2>
                            <div class="pull-right-debug" style="width: 60%;">
                                <input type="checkbox" name="mx_activate_cronlog" id="activateLogs"
                                       <?php if(MatrixPPC_Config::get('mx_ppc_activate_cronlog') == '1'){ ?>checked="checked" <?php } ?>
                                >
                                <label for="mx_activate_cronlog">Activate debug</label>
                            </div>
                        </div>
                    </div>
                    <table class="debug-table">
                        <tr>
                        <td style="width: 200px;">
                            <p style="float:left; clear:left;margin-bottom: 5px;"><?php _e("Debug Level", MatrixPPC_Utils::MATRIXPPC); ?></p>
                            <select name="debug_level" id="debug_level" style="float: right;margin-top: 8px;margin-left: 10px;">
                                <option value="1" <?php echo MatrixPPC_Config::get('mx_ppc_debug_level') == "1" ? 'selected="selected"' : '' ?>>Basic</option>
                                <option value="2" <?php echo MatrixPPC_Config::get('mx_ppc_debug_level') == "2" ? 'selected="selected"' : '' ?>>Medium</option>
                                <option value="3" <?php echo MatrixPPC_Config::get('mx_ppc_debug_level') == "3" ? 'selected="selected"' : '' ?>>Max Verbose</option>
                            </select>
                        </td>
                        </tr>
                        <tr>
                            <td id="debugLvl" style="width:calc(100% - 200px); padding-top:6px;">
                                <?php if(MatrixPPC_Config::get('mx_ppc_debug_level') == "3"){ ?>
                                    <div class="ms-alert ms-alert-danger" id="lvlMax">
                                        <?php _e('(Current debug file size: <b>'.MatrixPPC_Utils::humanFilesize(filesize(MatrixPPC_Utils::getStorageDirectory("debug.php"))).'</b>)', MatrixPPC_Utils::MATRIXPPC); ?>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                    <div class="element-info">
                        <?php _e('Observe MatrixPPC decisions and traffic.', MatrixPPC_Utils::MATRIXPPC); ?><br>
                    </div>
                </div>
            </div>

            <div class="third">
                <div class="inside">
                    <div class="title-sep-container titles-box">
                        <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/signature.png",__FILE__));?>">
                        <h2><?php _e('Plugin Signature', MatrixPPC_Utils::MATRIXPPC); ?></h2>
                    </div>
                    <form method="post" action="<?php echo MatrixPPC_Utils::cleanURL(admin_url( "options-general.php?page=matrixppc&tab=advanced" )); ?>">
                        <div class="form-group">
                            <label for="mx_signature">
                                <input name="mx_signature" id="mx_signature" type="checkbox" value="1" <?php echo MatrixPPC_Config::get('mx_signature_active')==="1"?"checked":""; ?> style="display:inline-block;"/>
                                <?php _e('Plugin Signature', MatrixPPC_Utils::MATRIXPPC); ?>
                            </label>
                        </div>
                    </form>
                    <div class="element-info"><?php _e('Enable / disable the MatrixPPC signature.', MatrixPPC_Utils::MATRIXPPC); ?></div>
                    <br style="clear:both;">
                </div>
            </div>
            <div style="clear:both;"></div>
            <form method="post" action="<?php echo MatrixPPC_Utils::cleanURL(admin_url("options-general.php?page=matrixppc&tab=settings")); ?>" class="settings-form">
            <div class="full-width">
                <div class="full-inside">
                    <div class="left-half">
                        <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/search_engine_visits.png",__FILE__));?>">
                        <h2><?php _e('Search Engines', MatrixPPC_Utils::MATRIXPPC); ?></h2>
                        <?php settings_fields( 'matrixppc_settings' ); ?>
                        <?php do_settings_sections( 'matrixppc_settings' ); ?>
                        <div class="form-group">
                            <label for="ips"><?php _e('Search Engines IPs', MatrixPPC_Utils::MATRIXPPC); ?></label>
                            <textarea id="ips" class="form-control" cols="30" rows="8" name="ips" disabled="disabled"><?php
                                if(!empty($ips)){
                                    foreach($ips as $ip){
                                        if($ip != ''){
                                            echo $ip."\n";
                                        }
                                    }
                                }
                                ?></textarea>
                            <div class="enable-ips-box">
                                <input type="checkbox" name="allow_edit_ips" id="allow_edit_ips"> <?php _e('Edit IPs', MatrixPPC_Utils::MATRIXPPC); ?>
                            </div>
                        </div>
                    </div>
                    <div class="right-half">
                        <div class="form-group">
                            <label for="referers"><?php _e('Search Engines Referrer Fingerprints', MatrixPPC_Utils::MATRIXPPC); ?></label>
                            <textarea id="referers" class="form-control" cols="30" rows="8" name="referers" disabled="disabled"><?php
                                if(!empty($referers)){
                                    foreach($referers as $referer){
                                        if($referer != ''){
                                            echo $referer."\n";
                                        }
                                    }
                                }
                                ?></textarea>
                            <div class="enable-refs-box">
                                <input type="checkbox" name="allow_edit_refs" id="allow_edit_refs"> <?php _e('Edit Fingerprints', MatrixPPC_Utils::MATRIXPPC); ?>
                            </div>
                        </div>
                    </div>
                    <br style="clear:both;" />
                    <div class="form-buttons">
                        <a href="#" id="repopulate-settings" class="button button-success"><?php _e('Repopulate Settings To Default Values', MatrixPPC_Utils::MATRIXPPC); ?></a>
                        <?php submit_button(); ?>
                    </div>
                    <div class="element-info">
                        <?php _e('IPs used to identify search engines and search engines referrer fingerprints.') ?>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </form>
    </div>
<?php elseif ($tab == 'debug'): ?>
    <div class="matrixppc-debug">
        <div class="bar-container">
            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/debug.png",__FILE__));?>">
            <h2><?php _e('Debug Tail');?></h2>
            <div class="pull-right-debug">
                <small style="margin-top: 4px; display: inline-block;"><i>
                        <span><?php _e('Updates every <b>10</b> seconds.', MatrixPPC_Utils::MATRIXPPC); ?></span>
                        <span><?php _e('Current filesize:', MatrixPPC_Utils::MATRIXPPC); ?> <b id="debug-size"><?php echo MatrixPPC_Utils::humanFilesize(filesize(MatrixPPC_Utils::getStorageDirectory("debug.php")));?></b> </span>
                    </i></small>
                <span><a href="<?php echo MatrixPPC_Utils::cleanURL(admin_url('options-general.php?page=matrixppc&tab=debug&clearlog=1')); ?>" class="button button-default" id="clearLog"><?php _e('Clear Log', MatrixPPC_Utils::MATRIXPPC); ?></a></span>
            </div>
        </div>
        <div class="cron-log-display">
            <textarea style="width: 100%;" id="cronContainer" spellcheck="false"><?php echo $cronContent; ?></textarea>
        </div>
    </div>
<?php elseif ($tab == 'advanced'): ?>
        <div class="half">
            <div class="inside">
                <div class="title-sep-container">
                    <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/actions_files.png",__FILE__));?>">
                    <h2><?php _e('Internal Files', MatrixPPC_Utils::MATRIXPPC); ?></h2>
                </div>
                <p><?php _e('Delete Internal Data Files', MatrixPPC_Utils::MATRIXPPC); ?></p>
                <a href="#" id="delete_files" class="button button-success"><?php _e('Delete all files', MatrixPPC_Utils::MATRIXPPC); ?></a>
                <p><?php _e('Repopulate Actions Files From Database', MatrixPPC_Utils::MATRIXPPC); ?></p>
                <a href="#" id="repopulate-actions" class="button button-success"><?php _e('Repopulate', MatrixPPC_Utils::MATRIXPPC); ?></a>
                <div class="element-info"><?php _e('MatrixPPC files tools.', MatrixPPC_Utils::MATRIXPPC); ?></div>
                <br style="clear:both;">
            </div>
        </div>


        <div class="half" id=""><!--TODO: delete debug_level -->
            <div class="inside">
                <div class="title-sep-container">
                    <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/pay-per-click.png",__FILE__));?>">
                    <h2><?php _e('Adwords API', MatrixPPC_Utils::MATRIXPPC); ?></h2>
                </div>
                    <br />
                    <?php
                    $ccid=MatrixPPC_Config::get("mx_ppc_adw_client_customer_id");
                    $ccidStatus=MatrixPPC_Config::get("mx_ppc_adw_client_customer_id_status");
                    ?>

                    <?php
                    if($ccidStatus==0) {
                        ?>
                        <a href="" class="button button-primary" id="ppcAdwordsTokens">
                            <?php _e('Connect', MatrixPPC_Utils::MATRIXPPC); ?>
                        </a>
                        <?php
                    }
                    else{
                        ?>
                        <a href="" class="button button-primary" id="ppcAdwordsRevoke">
                            <?php _e('Revoke Access', MatrixPPC_Utils::MATRIXPPC); ?>
                        </a>
                        <?php
                    }
                    ?>

                    <br /><br />
                    <?php _e('Client Customer ID:', MatrixPPC_Utils::MATRIXPPC); ?>

                    <?php
                    if($ccid==""){
                        echo '<span style="color:#ff0000; font-weight:bold;">';
                        _e('Not set.',MatrixPPC_Utils::MATRIXPPC);
                        echo '</span>';
                    }
                    else{
                        echo '<span style="color:#00FF00; font-weight:bold;">';
                        echo $ccid;
                        echo '</span>';

                    }
                    ?>
                    <br />
                    <?php _e('Status:', MatrixPPC_Utils::MATRIXPPC); ?>
                    <?php
                    if($ccidStatus==0){
                        echo '<span style="color: #ff0000; font-weight:bold;">';
                        _e('Not connected.',MatrixPPC_Utils::MATRIXPPC);
                        echo '</span>';
                    }
                    elseif($ccidStatus==1){
                        echo '<span style="color:#FFA500; font-weight:bold;">';
                        _e('Pending.</span> <span>(Login to AdWords and approve manager add!)',MatrixPPC_Utils::MATRIXPPC);
                        echo '</span>';
                        ?>
                        <br /><br />
                        <a href="" class="button button-default" id="ppcAdwordsRefresh">
                            <?php _e('Refresh Status', MatrixPPC_Utils::MATRIXPPC); ?>
                        </a>
                        <?php
                    }
                    elseif($ccidStatus==2){
                        echo '<span style="color:#00FF00; font-weight:bold;">';
                        _e('Connected.',MatrixPPC_Utils::MATRIXPPC);
                        echo '</span>';
                    }
                    ?>


                    <?php
                    if($ccidStatus==2) {
                        ?>
                        <br/><br/>
                        <a href="" class="button button-default"
                           id="ppcSelectAdwordsCampagn"><?php _e('Select Campaigns', MatrixPPC_Utils::MATRIXPPC); ?></a>

                        <br/><br/>
                        <?php _e('Selected Campaigns:', MatrixPPC_Utils::MATRIXPPC); ?>
                        <span id="mxppc-selected-campaigns-number">
                        <?php echo MatrixPPC_Config::get('mx_ppc_adwords_campaigns'); ?>
                        </span>
                        <?php
                    }
                    ?>
                <div class="element-info">
                    <?php _e('Connected AdWords account', MatrixPPC_Utils::MATRIXPPC); ?>
                </div>
                <br style="clear:both;">
            </div>
        </div>

    <?php include MatrixPPC_Utils::getBasePath('admin','views','matrixppc-admin-adwords-tokens.php'); ?>
    <?php include MatrixPPC_Utils::getBasePath('admin','views','matrixppc-admin-select-campaign.php'); ?>

<?php endif; ?>
<br style="clear:both;">