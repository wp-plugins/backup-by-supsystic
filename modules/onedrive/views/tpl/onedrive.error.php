<div id="bupOnedriveWrapper">
    <div id="bupOnedriveAlerts"></div>
    <h3>
        <?php echo langBup::_('An error has been occured.'); ?>
    </h3>

    <?php foreach ($errors as $error): ?>
        <p><?php echo $error; ?></p>
    <?php endforeach; ?>

    <div>
        <button class="onedriveLogout button button-primary">
            <?php echo langBup::_('Logout'); ?>
        </button>
    </div>

</div>
