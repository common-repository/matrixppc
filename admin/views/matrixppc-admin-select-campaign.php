<div class="ppc-grayedout" id="selectAdwordsCampaignModal" style="<?php echo (isset($_GET['change-adwords']) && $_GET['change-adwords'] == 'true') ? 'display: block' : 'display: none'; ?>">
    <div class="ppc-modal">
        <div class="title-sep-container">
            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/pay-per-click.png",__FILE__));?>">
            <h2><?php _e('Select AdWords Campaigns', MatrixPPC_Utils::MATRIXPPC); ?></h2>
        </div>
        <div class="mxppc-loading-container">
            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/matrixppc_spinner.svg",__FILE__));?>" alt=""><br>
            <?php _e('Loading Campaigns', MatrixPPC_Utils::MATRIXPPC); ?>
        </div>
        <div class="mxppc-loading-set-container" style="display: none;">
            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/matrixppc_spinner.svg",__FILE__));?>" alt=""><br>
            <?php _e('Setting Campaigns', MatrixPPC_Utils::MATRIXPPC); ?>
        </div>
        <div class="campaigns-container"></div>
        <div class="pull-right-adwords">
            <a href="" class="button button-default" id="ppcDismissCampagnsModal"><?php _e('Cancel', MatrixPPC_Utils::MATRIXPPC); ?></a>
            <a href="" class="button button-primary" id="ppcRefreshCampaigns"><?php _e('Refresh Campaigns',MatrixPPC_Utils::MATRIXPPC); ?></a>
            <a href="" class="button button-primary" id="submitAdwordsCampagnSelect"><?php _e('Select', MatrixPPC_Utils::MATRIXPPC); ?></a>
        </div>
        <br style="clear: both;">
    </div>
</div>