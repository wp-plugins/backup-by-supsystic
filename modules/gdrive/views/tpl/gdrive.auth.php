<div id="bupGDriveWrapper">
    <div id="bupGDriveAlerts"></div>
    <a href="<?php echo $url; ?>" class="button button-primary button-large"><?php langBup::_e('Authenticate'); ?></a>
    <?php
    if(!empty($errors) && is_array($errors)):
        foreach($errors as $error): ?>
        <p class="bupErrorMsg"><?php echo $error; ?></p>
    <?php
        endforeach;
    endif;
    ?>
</div>