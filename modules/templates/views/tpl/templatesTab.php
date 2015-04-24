<?php 
    $tplPerRow = 2;
?>
<style type="text/css">
    .toeTplOption {
        padding: 15px;
        width: 300px;
    }
    .toeTplOptionSelected {
        border: 4px solid skyblue;
    }
    .toeTplPicturePrev {
        max-width: 300px; 
        max-height: auto;
    }
    .toeTplDeactivateLink {
        display: none;
        float: right;
    }
</style>
<script type="text/javascript">
// <!--
    jQuery(document).ready(function(){
        toeMarkSelectedTpl('<?php echo $this->default_theme?>');
        jQuery('.toeTplActivateLink').click(function(){
            toeUpdateTemplate( jQuery(this).attr('href') );
            return false;
        });
        jQuery('.toeTplDeactivateLink').click(function(){
            toeUpdateTemplate('');
            return false;
        });
    });
    function toeUpdateTemplate(newValue) {
        jQuery(this).sendForm({
            data: {
                reqType: 'ajax', action: 'putOption', page: 'optionsBup', code: 'default_theme', value: newValue
            },
            msgElID: 'toeTplMsg',
            onSuccess: function(res) {
                if(!res.error) {
                    jQuery('#toeTplMsg').html('<?php _e('Template Updated. Reload page to see results.', BUP_LANG_CODE)?>');
					toeMarkSelectedTpl(res.data.value);
				}
            }
        });
    }
    function toeMarkSelectedTpl(tpl) {
        jQuery('.toeTplOption').removeClass('toeTplOptionSelected');
        jQuery('.toeTplDeactivateLink').hide();
        var selectedBox = jQuery('a.toeTplActivateLink[href="'+ tpl+ '"]').parents('div.toeTplOption:first');
        jQuery(selectedBox).addClass('toeTplOptionSelected');
        jQuery(selectedBox).find('.toeTplDeactivateLink:first').show();
    }
// -->
</script>
<table>
<?php 
    $i = 0;
    foreach($this->templates as $code => $t) {
        if($i%$tplPerRow == 0) { 
?>
            <tr>
<?php 
        }
?>
                <td width="<?php echo ceil(100/$tplPerRow)?>%">
                    <div class="toeTplOption">
                        <b><?php echo $t->name?></b><br />
                        <img class="toeTplPicturePrev" src="<?php echo $t->prevImg?>" /><br />
                        <a href="<?php echo $code?>" class="toeTplActivateLink"><?php _e('Activate', BUP_LANG_CODE)?></a>
                        <a href="<?php echo $code?>" class="toeTplDeactivateLink"><?php _e('Deactivate', BUP_LANG_CODE)?></a><br /><br />
                        <div><?php echo $t->description?></div>
                    </div>
                </td>
<?php
        if($i%$tplPerRow == $tplPerRow-1) { 
?>
            </tr>
<?php }
        $i++;
    }
?>
</table>
<div id="toeTplMsg"></div>