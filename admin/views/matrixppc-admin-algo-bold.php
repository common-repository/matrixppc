<div class="ppc-grayedout" id="mxPPCBoldSettingsModal" style="display: none;">
    <div class="ppc-modal">
        <div class="title-sep-container">
            <span class="algoImgContainer">
                <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/logo_matrixppc.png",__FILE__)); ?>" alt="" title="MatrixPPC">
            </span>
            <h2><?php _e('Bold Words', MatrixPPC_Utils::MATRIXPPC); ?></h2>
        </div>
        <br style="clear: both;">
        <br><br>
        <div class="algo-bold-words">
            <?php $algoVisibility = MatrixPPC_Config::get('mx_ppc_algo_bold_globally'); ?>
            <div class="form-group">
                <label for="boldAlgoSettingsPage">
                    <input type="radio" name="boldAlgoSettings" class="boldAlgoSettings" id="boldAlgoSettingsPage" value="0" <?php echo $algoVisibility == '0' ? 'checked' : ''; ?>> <?php _e('Bold words specific on page based on the AdWords campaigns landing pages', MatrixPPC_Utils::MATRIXPPC); ?>
                </label>
                <br />
                <label for="boldAlgoSettingsGlobally">
                    <input type="radio" name="boldAlgoSettings" class="boldAlgoSettings" id="boldAlgoSettingsGlobally" value="1" <?php echo $algoVisibility == '1' ? 'checked' : ''; ?>> <?php _e('Bold Words Website Wide (Globally)', MatrixPPC_Utils::MATRIXPPC); ?>
                </label>
                <br /><br />

                <select name="mx_ppc_algo_bold_mbpp" id="mx_ppc_algo_bold_mbpp" style="display:block; width:50px; float:left; clear:left;">
                    <?php
                    $tmp=MatrixPPC_Config::get("mx_ppc_algo_bold_mbpp");
                    for($i=1; $i<=20; $i++){
                        $selected="";
                        if($i==$tmp){
                            $selected="selected=\"selected\"";
                        }
                        echo '<option value="'.$i.'" '.$selected.' >'.$i.'</option>';
                    }
                    ?>
                </select>
                <label for="mx_ppc_algo_bold_mbpp" style="float:left; display: block; width:270px; margin-left:20px; padding-top:5px;">
                    Max number of bold words to create per page
                </label>
                <br />
                <select name="mx_ppc_algo_bold_mbpps" id="mx_ppc_algo_bold_mbpps" style="display:block; width:50px; float:left;  clear:left;">
                    <?php
                    $tmp=MatrixPPC_Config::get("mx_ppc_algo_bold_mbpps");
                    for($i=1; $i<=20;$i++){
                        $selected="";
                        if($i==$tmp){
                            $selected="selected=\"selected\"";
                        }
                        echo '<option value="'.$i.'" '.$selected.' >'.$i.'</option>';
                    }
                    ?>
                </select>
                <label for="mx_ppc_algo_bold_mbpps" style="float:left; display: block; width:270px; margin-left:20px; padding-top:5px;">
                    Max number of same word to bold per page
                </label>
                <br /><br /> <br /> <br />
                <label for="boldAlgoLight">
                    <input type="checkbox" name="boldAlgoLight" class="boldAlgoLight" id="boldAlgoLight" <?php echo MatrixPPC_Config::get('mx_ppc_algo_bold_light') == '1' ? 'checked' : ''; ?>> <?php _e("Light memory usage", MatrixPPC_Utils::MATRIXPPC); ?> (<?php _e("Does not check if word is already bolded or in bold permitted zones.") ?>)
                </label>

            </div>
        </div>
<!--        <br>-->
        <div class="pull-right-adwords">
            <a href="" class="button button-default" id="ppcDismissAlgoBoldModal"><?php _e('Cancel', MatrixPPC_Utils::MATRIXPPC); ?></a>
            <a href="" class="button button-primary" id="submitAlgoBoldSettings"><?php _e('Save', MatrixPPC_Utils::MATRIXPPC); ?></a>
        </div>
        <br style="clear: both;">
    </div>
</div>