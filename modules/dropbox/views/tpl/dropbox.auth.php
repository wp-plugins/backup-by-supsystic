<div id="bupDropboxWrapper">
    <div id="bupDropboxAlerts">
        <?php if(!empty($errors)) {
            echo implode('<br />', $errors);
        }?>
    </div>
    <a href="<?php echo $authUrl; ?>" class="button button-primary button-large">
        <?php langBup::_e('Authenticate'); ?>
    </a>
</div>