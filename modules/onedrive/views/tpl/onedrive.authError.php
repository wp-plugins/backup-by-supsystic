<div id="bupOnedriveWrapper">
    <div id="bupOnedriveAlerts"></div>
    <h3>
        <?php echo __('Authentication failed.', BUP_LANG_CODE); ?>
    </h3>

    <?php foreach ($errors as $error): ?>
        <p><?php echo $error; ?></p>
    <?php endforeach; ?>

</div>
