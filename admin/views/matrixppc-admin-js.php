<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
/* Allow edit of ips */
$("#allow_edit_ips").change(function() {
	if(this.checked) {
		$('#ips').removeAttr('disabled');
	}else{
		$('#ips').attr('disabled', 'disabled');
	}
});

$('#ppcChangeAdwords').on('click', function(e){
    e.preventDefault();
    $('#changeAdwordsAccountModal').css('display', 'block');
    return false;
});

/* Refresh the AdWords Campaigns */
$('#ppcRefreshCampaigns').on('click', function(e){
    e.preventDefault();

    $('.mxppc-loading-set-container').hide();
    $('.mxppc-loading-container').show();
    $('.campaigns-container').hide();

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'refresh-campaigns'
    };

    jQuery.post(ajaxurl, data, function(response) {
        $('.mxppc-loading-container').hide();
        var items = JSON.parse(response);

        /* Set the selected campaigns view info */
        var activeCampaigns = 0;
        $.each(items, function(index, value){
            if(value.enabled==true){
                activeCampaigns += 1;
            }
        });
        $('#mxppc-selected-campaigns-number').text(activeCampaigns);
        /* --- */

        var html = '';
        if(items.error != undefined){
            html += "<div>" + items.error.message + "</div>";
        }else{
            html += '<ul>';
            $.each(items, function(index, value){
                html += '<li>';
                if(value.enabled){
                    html += '<input id="campaignsInput'+index+'" type="checkbox" name="campaigns[]" class="campaignsBoxes" value="'+index+'" checked>';
                }else{
                    html += '<input id="campaignsInput'+index+'" type="checkbox" name="campaigns[]" class="campaignsBoxes" value="'+index+'">';
                }
                html += '<label for="campaignsInput'+index+'">' + value.name + '</label>';
                html += '</li>';
            });
            html += '</ul>';

        }
        $('.campaigns-container').show();
        $('.campaigns-container').html(html);
    });
    return false;

});

/* Open The Select Campaigns modal */
$('#ppcSelectAdwordsCampagn').on('click', function(e){
    e.preventDefault();

    $('#selectAdwordsCampaignModal').css('display', 'block');
    $('.mxppc-loading-set-container').hide();
    $('.mxppc-loading-container').show();
    $('.campaigns-container').hide();

    /*Ajax to get the campaigns and display them*/

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'get-campaigns'
    };

    jQuery.post(ajaxurl, data, function(response) {
        $('.mxppc-loading-container').hide();
        var items = JSON.parse(response);

        /* Set the selected campaigns view info */
        var activeCampaigns = 0;
        $.each(items, function(index, value){
            if(value.enabled==true){
                activeCampaigns += 1;
            }
        });
        $('#mxppc-selected-campaigns-number').text(activeCampaigns);
        /* --- */

        var html = '';
        if(items.error != undefined){
            html += "<div>" + items.error.message + "</div>";
        }else{
            html += '<ul>';
                $.each(items, function(index, value){
                html += '<li>';
                    if(value.enabled){
                        html += '<input id="campaignsInput'+index+'" type="checkbox" name="campaigns[]" class="campaignsBoxes" value="'+index+'" checked>';
                    }else{
                        html += '<input id="campaignsInput'+index+'" type="checkbox" name="campaigns[]" class="campaignsBoxes" value="'+index+'">';
                    }
                    html += '<label for="campaignsInput'+index+'">' + value.name + '</label>';
                    html += '</li>';
                });
                html += '</ul>';

        }
        $('.campaigns-container').show();
        $('.campaigns-container').html(html);
    });
    return false;
});

$('#AdwordsScriptContainer').on('click', function(){
    $(this).select();
});

/* Set AdWords Campaigns */
$('#submitAdwordsCampagnSelect').on('click', function(e){
    e.preventDefault();
    var selectedCampagns = $('.campaignsBoxes:checkbox:checked');
    var campaigns = [];

    $.each(selectedCampagns, function(item, value){
        campaigns.push($(value).val());
    });

    if(campaigns.length==0){
        mxError('No campaigns selected.');
        return false;
    }

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'set-campaigns',
        'values'    : campaigns
    };

    console.log(data);

    /* Display the loading */
    $('.mxppc-loading-set-container').css('display', 'block');
    $('.campaigns-container').hide();

    jQuery.post(ajaxurl, data, function(response) {
        $('.ppc-grayedout').hide();
        mxAlert('Campaigns Set.');
        var responseParse=JSON.parse(response);
        var responseCount=responseParse['count'];
        $('#mxppc-selected-campaigns-number').text(responseCount);
    });

    return false;
});

/* AdWords Script Modal */
$('#ppcAdwordsScript').on('click', function(e){
    e.preventDefault();
        if($(this).filter('[disabled]').length == '0'){
            $('#selectAdwordsScriptModal').css('display', 'block');
        }
    return false;
});

$('#copyCode').on('click', function(e){
    e.preventDefault();
    CopyToClipboard('AdwordsScriptContainer');
    var data = {
    'action'    : 'matrixppc_ajax_actions',
    'what'      : 'copy-adwords'
    };
    jQuery.post(ajaxurl, data, function(response) {});
    $('#selectAdwordsScriptModal').hide();
    return false;
});

function CopyToClipboard(containerid) {
    if (document.selection) {
        var range = document.body.createTextRange();
        range.moveToElementText(document.getElementById(containerid));
        range.select().createTextRange();
        document.execCommand("Copy");
    } else if (window.getSelection) {
        var range = document.createRange();
        range.selectNode(document.getElementById(containerid));
        window.getSelection().addRange(range);
        document.execCommand("Copy");
        mxAlert("Script Copied");
    }
}

$('.ppcDismisScriptModal').on('click', function(e){
    e.preventDefault();
    $('.ppc-grayedout').hide();
    return false;
});

$('#ppcDismissCampagnsModal').on('click', function(e){
    e.preventDefault();
    $('.ppc-grayedout').hide();
    return false;
});

$('#ppcDismissModal').on('click', function(e){
    e.preventDefault();
    $('.ppc-grayedout').css('display', 'none');
    return false;
});

/* Allow edit of refs */
$("#allow_edit_refs").change(function() {
	if(this.checked) {
		$('#referers').removeAttr('disabled');
	}else{
		$('#referers').attr('disabled', 'disabled');
	}
});

/* Search actions */
$('#sForm').on('submit', function(e){
    e.preventDefault();
    window.location.href = window.location.href + "options-general.php?page=matrixppc&tab=actions&searchurl=" + $("#url-term").val();
    return false;
});

/* Adapt the cron height */
var h = window.innerHeight;
$('#cronContainer').css('height', (h-280)+'px');


/* Arrange notices in case of many */
var noticesNo = $('.msnotice').length;

$('.msnotice').each(function(item){
    if(noticesNo > 1 && item > 0){
        if(noticesNo > 2){
            if(item == 1){
                $(this).css('top', item * '85' + 'px');
            }else if(item == 2){
                $(this).css('top', item * '75' + 'px');
            }

        }else{
            if(item == 0){
                $(this).css('top', '75' + 'px');
            }else{
                $(this).css('top', '5' + 'px');
            }
        }
    }else{
        if(noticesNo == 1){
            $(this).css('top', '5' + 'px');
        }else{
            if(item == 0){
                $(this).css('top', '75' + 'px');
            }else{
                $(this).css('top', '5' + 'px');
            }
        }

    }

    var $this = $(this);
    setTimeout(function(){
        $this.hide("slow");
    }, 5000);
});

/* Hide the notice on click */
$('.msnotice').on('click', function(){
    $(this).hide();
});

/* OPEN / CLOSE the hamburger */
$('.hamburger').on('click', function(e){
    e.preventDefault();
    if( $('.nav-tab-wrapper').hasClass('show') ){
        //show
        $('.nav-tab-wrapper').removeClass('show');
        $('.nav-tab-wrapper').css('display', 'none');
    }else{
        //hide
        $('.nav-tab-wrapper').addClass('show');
        $('.nav-tab-wrapper').css('display', 'block');
    }
    return false;
});

/* Change debug level */
$('#debug_level').on('change', function(e){
    e.preventDefault();
    var lvl = $(this).val();
    var data = {
        'action'    : 'matrixppc_ajax_actions',
		'what'      : 'debug_level',
        'level'     : $(this).val()
    };
    jQuery.post(ajaxurl, data, function(response) {
        mxAlert("Debug level changed.");
        if(lvl == '3'){
            hideDebugLevelMsg();
        }else{
            $('#lvlMax').addClass('hideDebugLevel');
        }

    });
    return false;
});

/* Clear debug log */
$('#clearLog').on('click', function(e){
    e.preventDefault();
    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'clear_log'
    };
    jQuery.post(ajaxurl, data, function(response) {
        $('#cronContainer').val('');
        mxAlert('Log file cleared.');
        $('#debug-size').text('0B');
    } );

    return false;
});

/* Enable / disable signature */
$('#mx_signature').change(function(){
    var tmpValue="0";
    if(this.checked){
        tmpValue="1";
    }
    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'change_signature',
        'value'     : tmpValue
    };
    jQuery.post(ajaxurl, data, function(){
        if(tmpValue=='1'){
            mxAlert("Signature activated");
        }else{
            mxAlert("Signature deactivated");
        }
    });
});

/* Ignore action */
$('.action_item').on('click', function(e){
    e.preventDefault();
    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'ignore_action',
        'value'     : $(this).data('id')
    };
    jQuery.post(ajaxurl, data, function(){
        window.location.reload();
    });
    return false;
});

/* Apply action */
$('.remove_ig_item').on('click', function(e){
    e.preventDefault();
    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'apply_action',
        'value'     : $(this).data('id')
    };
    jQuery.post(ajaxurl, data, function(){
        window.location.reload();
    });
    return false;
});

/* Enable / disable cron debug */
$('#activateLogs').on('change', function(){
    var tmpValue="0";
    var tmpWord="deactivated";
    if(this.checked){
        tmpValue="1";
        tmpWord="activated";
    }
    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'activate_debug',
        'value'     : tmpValue
    };
    jQuery.post(ajaxurl, data, function(){
        $('#debug-tab').toggle();

        mxAlert("Debug " + tmpWord);
    });
});

/* Enable / disable plugin */
$('.activator').on('click', function(e){
    var tmpValue='0';
    var tmpWord="deactivated";
    if ( $(this).val() == 1 ){
        tmpValue='1';
        tmpWord="activated";
    }
    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'activate_plugin',
        'value'     : tmpValue
    };
    jQuery.post(ajaxurl, data, function(){
        if (tmpValue == '1'){
            $('.ms-auto-control .notice-dismiss').click();
        }
        mxAlert("MatrixPPC has been " + tmpWord + ".");
    });
});

/* Refresh debug log */
function refreshLogs(){
    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'debug_log'
    };
    jQuery.post(ajaxurl, data, function(response){
        var tmp=JSON.parse(response);
        $('#cronContainer').val(tmp.debug);
        $('#debug-size').text(tmp.size);
    });
}

/* Change debug level */
$('#delete_files').on('click', function(e){
    e.preventDefault();

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'delete_files'
    };

    jQuery.post(ajaxurl, data, function(response) {
        mxAlert('Files deleted.');
        console.log('here');
    });
    return false;
});

<?php
if(isset($_GET['tab']) && $_GET['tab']=="debug")
{
?>
setInterval(function(){
    refreshLogs();
}, 10000);
<?php
}
?>

/* Alert */
var timeout;

function mxAlert(msg){
    $('#wpbody-content').prepend('<div class="msnotice msnotice-success notice notice-success"><img src="../wp-content/plugins/matrixppc/admin/img/success.png">&nbsp;<span>'+msg+'</span></div>');
    hideNotices();
    arrangeNotices();
    hideNoticeOnClick();
}

function mxError(msg){
    $('#wpbody-content').prepend('<div class="msnotice mserror notice notice-success"><img src="../wp-content/plugins/matrixppc/admin/img/error.png">&nbsp;<span>'+msg+'</span></div>');
    hideNotices();
    arrangeNotices();
    hideNoticeOnClick();
}

function arrangeNotices(){
    var noticesNo = $('.msnotice').length;
    $('.msnotice').each(function(id, item){
        $(this).css('top', id * 70 + 'px');
    });

    if(noticesNo > 1){
        hideNotices();
    }
}

function hideNotices(){
    clearTimeout(timeout);
    timeout = setTimeout(function(){$('.msnotice').hide('slow');},5000);
}

function hideNoticeOnClick(){
    $('.msnotice').on('click', function(){
        $(this).hide();
        $(this).removeClass('msnotice');
        arrangeNotices();
    })
}

function hideDebugLevelMsg(){
    var html = '<div class="ms-alert ms-alert-danger" id="lvlMax"><?php _e('(Current debug file size: <b>'.MatrixPPC_Utils::humanFilesize(filesize(MatrixPPC_Utils::getStorageDirectory("debug.php"))).'</b>)', MatrixPPC_Utils::MATRIXPPC); ?></div>';
    if( $('#lvlMax').length == 0 ){
        $('#debugLvl').append(html);
    }else{
        $('#lvlMax').removeClass('hideDebugLevel');
    }
}

/* Repopulate settings */
$('#repopulate-settings').on('click', function(event){
    event.preventDefault();
    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'repopulate-settings'
    };
    jQuery.post(ajaxurl, data, function(response){
        tmp=JSON.parse(response);
        if(tmp.ips){
            $('#ips').val(tmp.ips);
        }
        if(tmp.referers){
            $('#referers').val(tmp.referers);
        }
    });
    mxAlert("Settings repopulated.");
    return false;
});

/* Repopulate actions */
$('#repopulate-actions').on('click',function(event){
    event.preventDefault();
    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'repopulate-actions'
    };
    jQuery.post(ajaxurl, data, function(response) {
        mxAlert("Actions repopulated.");
    });
    return false;
});

/* Bold Words Algo Modal */
$('#mxPPCBoldSettings').on('click', function(e){
    e.preventDefault();
        $('#mxPPCBoldSettingsModal').css('display', 'block');
    return false;
});

/* Bold Words Algo Dismiss */
$('#ppcDismissAlgoBoldModal').on('click', function(e){
    e.preventDefault();
    $('#mxPPCBoldSettingsModal').css('display', 'none');
    return false;
});

/* Fraud Adwords Click Shield Algo Modal */
$('#mxPPCFraudSettings').on('click', function(e){
    e.preventDefault();
        $('#mxPPCFraudSettingsModal').css('display', 'block');
    return false;
});

/* Fraud Adwords Click Shield Algo BLOCK LIST  Modal */
$('#mxPPCFraudList').on('click', function(e){
    e.preventDefault();
    $('#mxPPCFraudListModal').css('display', 'block');
    return false;
});


/* Fraud Adwords Click Shield Algo Modal */
$('#ppcDismissAlgoFraudModal').on('click', function(e){
    e.preventDefault();
    $('#mxPPCFraudSettingsModal').css('display', 'none');
    return false;
});

/* Reset Fraudsters */
$('#ppcResetFraudsters').on('click', function(e){
    e.preventDefault();

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'reset-fraudsters'
    };

    jQuery.post(ajaxurl, data, function(response) {
        mxAlert("All fraudsters and fraud statistics resetted.");
        $(".algo-fraud-words textarea").val('No fraud identified yet.');
    });
    return false;
});

/* Fraud Adwords Click Shield Algo LIST Modal */
$('#ppcDismissAlgoFraudListModal').on('click', function(e){
    e.preventDefault();
    $('#mxPPCFraudListModal').css('display', 'none');
    return false;
});

/* Check if all Algos are disabled and pop a notice about that... */
function checkAllAlgosDisabled(){
    var activeALgos = checkActiveAlgos();

    if( activeALgos > 0){
        $('.mxPpcGlobalNotice').hide();
    }else if(activeALgos == 0){

        var alertsActive=0;
        $('div .mxPpcGlobalNotice').each(function(){
            if($(this).css('display')=='block'){
                alertsActive+=1;
            }
        });
        if(alertsActive==0){
            var message = '<div class="notice notice-error is-dismissible ms-auto-control mxPpcGlobalNotice"><p><?php _e( 'MatrixPPC is active but no algos have been enabled. Visit ', MatrixPPC_Utils::MATRIXPPC); ?><a href="<?php echo admin_url( 'options-general.php?page=matrixppc&tab=algos' ); ?>" title="MatrixPPC Algos Options"><?php _e('this page', MatrixPPC_Utils::MATRIXPPC); ?></a><?php _e(' and enable the desired algo!', MatrixPPC_Utils::MATRIXPPC); ?></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
            $('#wpbody').prepend(message);
            jQuery(document).on( 'click', '.ms-auto-control .notice-dismiss', function(e){
                e.preventDefault();
                jQuery.get('<?php echo admin_url('options-general.php?page=matrixppc&inactivenotice=1'); ?>');
                return false;
            });
        }
    }
}

/* Bold Words Algo status change */
$('.activateBold').on('click', function(e){

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'bold-algo-status'
    };

    var tmpName = 'inactive';
    if( $(this).val() == '1' ){
        var tmpName = 'active';
    }

    jQuery.post(ajaxurl, data, function(response) {
        mxAlert("Algo status changed to "+tmpName+".");
    });

    checkAllAlgosDisabled();
});

/* Fraud Adwords Clicks Shield Algo status change */
$('.activateFraud').on('click', function(e){

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'fraud-algo-status'
    };

    var tmpName = 'inactive';
    if( $(this).val() == '1' ){
        var tmpName = 'active';
    }

    jQuery.post(ajaxurl, data, function(response) {
        mxAlert("Algo status changed to "+tmpName+".");
    });

    checkAllAlgosDisabled();
});

/* Bold Algo Settings */
$('#submitAlgoBoldSettings').on('click', function(e){
    e.preventDefault();

    var globally = 0;
    if($('#boldAlgoSettingsPage').is(':checked')){
        globally = 0;
    }

    if($('#boldAlgoSettingsGlobally').is(':checked')){
        globally = 1;
    }

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'bold-algo-settings',
        'globally'  : globally,
        'mx_ppc_algo_bold_mbpp' : $('#mx_ppc_algo_bold_mbpp').val(),
        'mx_ppc_algo_bold_mbpps' : $('#mx_ppc_algo_bold_mbpps').val()
    };

    var tmpName = 'specific on page';
    if( globally == '1' ){
        var tmpName = 'globally';
    }

    jQuery.post(ajaxurl, data, function(response) {
        $('#mxPPCBoldSettingsModal').css('display', 'none');
        mxAlert("Bold algo is applied "+tmpName+".");
    });

    return false;
});

/*Fraud algo days text*/
$('#mxPpcFraudDays').on('change', function(){
    if( $(this).val() == '1' ){
        $('.days').text('day');
    }else{
        $('.days').text('days');
    }
});

/* Save Fraud Algo Settings */
$('#submitAlgoFraudSettings').on('click', function(e){
    e.preventDefault();

    var clicks = $('#mxPpcFraudClicks').val();
    var days = $('#mxPpcFraudDays').val();

    console.log(days);

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'fraud-algo-settings',
        'clicks'    : clicks,
        'days'      : days
    };

    jQuery.post(ajaxurl, data, function(response) {
        $('#mxPPCFraudSettingsModal').css('display', 'none');
        mxAlert("Fraud algo settings saved.");
    });

    return false;
});

/* Open Tokens Modal */
$('#ppcAdwordsTokens').on('click', function(e){
    e.preventDefault();

    if($(this).filter('[disabled]').length == '0'){
        $('#AdwordsTokensModal').css('display', 'block');
    }
    return false;
});

/* Close Tokens Modal*/
$('#ppcDismissTokensModal').on('click', function(e){
    e.preventDefault();
        $('#AdwordsTokensModal').css('display', 'none');
    return false;
});

/* Revoke AdWords Access */
$('#ppcAdwordsRevoke').on('click', function(e){
    e.preventDefault();

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'revoke-access',
    };

    jQuery.post(ajaxurl, data, function(response) {
        response=JSON.parse(response);
        if(response.ok==true){
            mxAlert('Access revoked.');
            setTimeout(function(){window.location.reload()},500);

        }
        else if(response.ok==false){
            mxError(response.message);
        }
    });
});
/* Refresh AdWords Status */
$('#ppcAdwordsRefresh').on('click', function(e){
    e.preventDefault();

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'refresh-status'
    };

    jQuery.post(ajaxurl, data, function(response) {
        mxAlert('AdWords CCID Status refreshed.');
        setTimeout(function(){window.location.reload()},500);
    });

});

/*Light memory usage*/
$('#boldAlgoLight').on('click', function(){

    var data = {
        'action'        : 'matrixppc_ajax_actions',
        'what'          : 'algo_bold_light',
        'algo_light'    : $(this).val(),
    };

    jQuery.post(ajaxurl, data, function(response) {
        response=JSON.parse(response);
        if(response.ok==true){
            mxAlert('Light memory usage changed.');
        }else{
            mxError(response.message);
        }
    });
});

/* Save AdWords Token */
$('#submitAdwordsTokensSelect').on('click', function(e){
    e.preventDefault();

    var data = {
        'action'    : 'matrixppc_ajax_actions',
        'what'      : 'save-tokens',
        'client_customer_id'  : $('#ppc_client_customer_id').val(),

    };

    jQuery.post(ajaxurl, data, function(response) {
        response=JSON.parse(response);
        if(response.ok==true){
            mxAlert('AdWords Customer ID Saved.');
            setTimeout(function(){window.location.reload()},500);
        }
        else{
            mxError(response.message);
        }
    });

    return false;
});

/*Count how many active algos we have*/
function checkActiveAlgos(){
    var activeAlgos = 0;
    $('#algosList input:radio:checked').each(function () {
        if($(this).val() == 1){
            activeAlgos += 1;
        }
    });
    return activeAlgos;
}