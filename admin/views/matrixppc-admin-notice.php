<div class="notice notice-error is-dismissible ms-auto-control mxPpcGlobalNotice">
    <p><?php _e( 'MatrixPPC is active but no algos have been enabled. Visit ', MatrixPPC_Utils::MATRIXPPC); ?><a href="<?php echo admin_url( 'options-general.php?page=matrixppc&tab=algos' ); ?>" title="MatrixPPC Algos Options"><?php _e('this page', MatrixPPC_Utils::MATRIXPPC); ?></a><?php _e(' and enable the desired algo!', MatrixPPC_Utils::MATRIXPPC); ?></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
</div>
<script>
    jQuery(document).on( 'click', '.ms-auto-control .notice-dismiss', function(e){
        e.preventDefault();
        jQuery.get('<?php echo admin_url('options-general.php?page=matrixppc&inactivenotice=1'); ?>');
        return false;
    });
</script>