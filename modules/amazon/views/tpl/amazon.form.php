<?php if(isset($errors) && $errors) { ?>
    <?php foreach($errors as $e) { ?>
        <div class="bupErrorMsg"><?php echo $e?></div>
    <?php }?>
<?php }?>
<div class="Amazon_Main_Auth" id="AmazonCredentialData">
    <div id="bupAmazonAlerts"></div>
    <p><?php echo $form['legend']; ?></p>
    <table class="bupTable100per">
        <?php foreach($form['fields'] as $control): ?>
            <tr>
                <td style="padding: 5px 1px;"><?php echo ucfirst($control['label']); ?></td>
                <td style="padding: 5px 1px;"><?php echo $control['field']; ?></td>
            </tr>
        <?php endforeach; ?>

        <tr>
            <td colspan="2" style="padding: 0 !important;">
                <?php foreach($form['extra'] as $extra): ?>
                    <?php echo $extra; ?>
                <?php endforeach; ?>
            </td>
        </tr>
    </table>
</div>

<div id="Amazon_Auth_Result"></div>