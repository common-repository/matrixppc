<div class="ppc-grayedout" id="mxPPCFraudSettingsModal" style="display: none;">
    <div class="ppc-modal">
        <div class="title-sep-container">
            <span class="algoImgContainer">
                <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/logo_matrixppc.png",__FILE__)); ?>" alt="" title="MatrixPPC">
            </span>
            <h2><?php _e('Fraud Adwords Click Shield', MatrixPPC_Utils::MATRIXPPC); ?></h2>
        </div>
        <br style="clear: both;">
        <br><br>
        <div class="algo-fraud-words">
            <?php _e("Consider fraud after ", MatrixPPC_Utils::MATRIXPPC); ?>
            <select name="mxPpcFraudClicks" id="mxPpcFraudClicks">
                <?php foreach(range(2, 10) as $clicks): ?>
                    <option value="<?php echo $clicks; ?>" <?php echo MatrixPPC_Config::get('mx_ppc_algo_fraud_clicks') == $clicks ? 'selected' : ''; ?>><?php echo $clicks; ?></option>
                <?php endforeach; ?>
            </select>
            <?php _e("clicks from the same IP in ", MatrixPPC_Utils::MATRIXPPC); ?>
            <select name="mxPpcFraudDays" id="mxPpcFraudDays">
                <?php foreach(range(1, 7) as $days): ?>
                    <option value="<?php echo $days; ?>" <?php echo MatrixPPC_Config::get('mx_ppc_algo_fraud_days') == $days ? 'selected' : ''; ?>><?php echo $days; ?></option>
                <?php endforeach; ?>
            </select>
            <span class="days">
                <?php _e('day(s)', MatrixPPC_Utils::MATRIXPPC); ?>
            </span>
        </div>
        <br>
        <div class="pull-right-adwords">
            <a href="" class="button button-default" id="ppcDismissAlgoFraudModal"><?php _e('Cancel', MatrixPPC_Utils::MATRIXPPC); ?></a>
            <a href="" class="button button-primary" id="submitAlgoFraudSettings"><?php _e('Save', MatrixPPC_Utils::MATRIXPPC); ?></a>
        </div>
        <br style="clear: both;">
    </div>
</div>