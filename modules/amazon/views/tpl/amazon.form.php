<?php if(isset($errors) && $errors) { ?>
    <?php foreach($errors as $e) { ?>
        <div class="bupErrorMsg"><?php echo $e?></div>
    <?php }?>
<?php }?>
<div class="Amazon_Main_Auth">
    <form id="bupAmazonCredentials">
        <div id="bupAmazonAlerts"></div>

        <p><?php echo $form['legend']; ?></p>
        <?php foreach($form['fields'] as $control): ?>
        <p>
            <?php echo ucfirst($control['label']); ?>
            <?php echo $control['field']; ?>
        </p>
        <?php endforeach; ?>

        <?php foreach($form['extra'] as $extra): ?>
            <?php echo $extra; ?>
        <?php endforeach; ?>
    </form>
</div>

<div id="Amazon_Auth_Result"></div>