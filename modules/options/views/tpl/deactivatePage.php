<?php
	$title = 'Backup by Supsystic - plugin deactivation';
?>
<html>
    <head>
        <title><?php _e($title, BUP_LANG_CODE)?></title>
    </head>
    <body>
<div style="position: fixed; margin-left: 40%; margin-right: auto; text-align: center; background-color: #fdf5ce; padding: 10px; margin-top: 10%;">
    <div><?php _e($title, BUP_LANG_CODE)?></div>
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
                <td><?php _e('Delete Plugin Data (options, setup data, database tables, etc.)', BUP_LANG_CODE)?>:</td>
                <td><?php echo htmlBup::radiobuttons('deleteOptions', array('options' => array('No', 'Yes')))?></td>
            </tr>
        </table>
    <?php echo htmlBup::submit('toeGo', array('value' => __('Done', BUP_LANG_CODE)))?>
    <?php echo htmlBup::formEnd()?>
    </div>
</body>
</html>