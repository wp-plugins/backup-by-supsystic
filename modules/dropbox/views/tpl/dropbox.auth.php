<div id="bupDropboxWrapper">
    <div id="bupDropboxAlerts">
        <?php if(!empty($errors)) {
            echo implode('<br />', $errors);
        }?>
    </div>
    <a href="<?php echo $authUrl; ?>" class="button button-primary button-large dropboxAuthenticate">
        <?php _e('Authenticate', BUP_LANG_CODE); ?>
    </a>
</div>