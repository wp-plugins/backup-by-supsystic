<?php
	$title = 'Supsystic! Coming Soon - plugin deactivation';
?>
<html>
    <head>
        <title><?php langBup::_e( $title )?></title>
    </head>
    <body>
<div style="position: fixed; margin-left: 40%; margin-right: auto; text-align: center; background-color: #fdf5ce; padding: 10px; margin-top: 10%;">
    <div><?php langBup::_e( $title )?></div>
    <?php echo htmlBup::formStart('deactivatePlugin', array('action' => $this->REQUEST_URI, 'method' => $this->REQUEST_METHOD))?>
    <?php
        $formData = array();
        switch($this->REQUEST_METHOD) {
            case 'GET':
                $formData = $this->GET;
                break;
            case 'POST':
                $formData = $this->POST;
                break;
        }
        foreach($formData as $key => $val) {
            if(is_array($val)) {
                foreach($val as $subKey => $subVal) {
                    echo htmlBup::hidden($key. '['. $subKey. ']', array('value' => $subVal));
                }
            } else
                echo htmlBup::hidden($key, array('value' => $val));
        }
    ?>
        <table width="100%">
            <tr>
                <td><?php langBup::_e('Delete Plugin Data (options, setup data, database tables, etc.)')?>:</td>
                <td><?php echo htmlBup::radiobuttons('deleteOptions', array('options' => array('No', 'Yes')))?></td>
            </tr>
        </table>
    <?php echo htmlBup::submit('toeGo', array('value' => langBup::_('Done')))?>
    <?php echo htmlBup::formEnd()?>
    </div>
</body>
</html>