<div id="bupOnedriveWrapper">
    <div id="bupOnedriveAlerts"></div>
    <h3>
        <?php echo __('An error has been occured.', BUP_LANG_CODE); ?>
    </h3>

    <?php foreach ($errors as $error): ?>
        <p><?php echo $error; ?></p>
    <?php endforeach; ?>

    <div>
        <button class="onedriveLogout button button-primary">
            <?php echo __('Logout', BUP_LANG_CODE); ?>
        </button>
    </div>

</div>
