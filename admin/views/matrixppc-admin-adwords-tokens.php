<div class="ppc-grayedout" id="AdwordsTokensModal" style="display: none;">
    <div class="ppc-modal">
        <div class="title-sep-container">
            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/pay-per-click.png",__FILE__));?>">
            <h2><?php _e('AdWords Tokens', MatrixPPC_Utils::MATRIXPPC); ?></h2>
        </div>
        <div class="campaigns-container-tokens">
<!--            <div class="form-group">-->
<!--                <label for="ppc_developer_token">--><?php //_e('Developer Token', MatrixPPC_Utils::MATRIXPPC); ?><!--</label>-->
<!--                <input type="text" class="form-control" name="ppc_developer_token" id="ppc_developer_token" value="--><?php //echo MatrixPPC_Config::get('mx_ppc_adw_dev_token'); ?><!--">-->
<!--            </div>-->
            <div class="form-group">
                <label for="ppc_client_customer_id"><?php _e('Client Customer ID'); ?></label>
                <input
                        type="text"
                        class="form-control"
                        name="ppc_customer_client_id"
                        id="ppc_client_customer_id"
                        value="<?php echo MatrixPPC_Config::get("mx_ppc_adw_client_customer_id"); ?>"
                >
            </div>
<!--            <div class="form-group">-->
<!--                <label for="ppc_client_id">--><?php //_e('Client Id'); ?><!--</label>-->
<!--                <input type="text" class="form-control" name="ppc_client_id" id="ppc_client_id" value="--><?php //echo MatrixPPC_Config::get('mx_ppc_adw_client_id'); ?><!--">-->
<!--            </div>-->
<!--            <div class="form-group">-->
<!--                <label for="ppc_client_secret">Client Secret</label>-->
<!--                <input type="text" class="form-control" name="ppc_client_secret" id="ppc_client_secret" value="--><?php //echo MatrixPPC_Config::get('mx_ppc_adw_client_secret'); ?><!--">-->
<!--            </div>-->
<!--            <div class="form-group">-->
<!--                <label for="ppc_refresh_token">Refresh Token</label>-->
<!--                <input type="text" class="form-control" name="ppc_refresh_token" id="ppc_refresh_token" value="--><?php //echo MatrixPPC_Config::get('mx_ppc_adw_refresh_token'); ?><!--">-->
<!--            </div>-->
        </div>
        <div class="pull-right-adwords">
            <a href="" class="button button-default" id="ppcDismissTokensModal"><?php _e('Close', MatrixPPC_Utils::MATRIXPPC); ?></a>
            <a href="" class="button button-primary" id="submitAdwordsTokensSelect"><?php _e('Save', MatrixPPC_Utils::MATRIXPPC); ?></a>
        </div>
        <br style="clear: both;">
    </div>
</div>