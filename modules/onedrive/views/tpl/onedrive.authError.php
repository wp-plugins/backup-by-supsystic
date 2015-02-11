<div id="bupOnedriveWrapper">
    <div id="bupOnedriveAlerts"></div>
    <h3>
        <?php echo langBup::_('Authentication failed.'); ?>
    </h3>

    <?php foreach ($errors as $error): ?>
        <p><?php echo $error; ?></p>
    <?php endforeach; ?>

</div>
