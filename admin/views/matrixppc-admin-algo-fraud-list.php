<div class="ppc-grayedout" id="mxPPCFraudListModal" style="display: none;">
    <div class="ppc-modal">
        <div class="title-sep-container">
            <span class="algoImgContainer">
                <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/logo_matrixppc.png",__FILE__)); ?>" alt="" title="MatrixPPC">
            </span>
            <h2><?php _e('Fraud Adwords Click Shield Block List', MatrixPPC_Utils::MATRIXPPC); ?></h2>
        </div>
        <br style="clear: both;">
        <br><br>
        <div class="algo-fraud-words">
            <textarea style="width:100%; height:300px;" disabled="disabled"><?php
                $bannedIps=MatrixPPC_Db::getBannedIps();
                if(count($bannedIps)>0) {
                    echo implode("\n", $bannedIps);
                }
                else{
                    echo "No fraud identified yet.";
                }
                ?></textarea>
        </div>
        <br>
        <div class="pull-right-adwords">
            <a href="" class="button button-default" id="ppcResetFraudsters"><?php _e('Reset Fraudsters', MatrixPPC_Utils::MATRIXPPC); ?></a>
            <a href="" class="button button-primary" id="ppcDismissAlgoFraudListModal"><?php _e('Ok', MatrixPPC_Utils::MATRIXPPC); ?></a>
        </div>
        <br style="clear: both;">
    </div>
</div>