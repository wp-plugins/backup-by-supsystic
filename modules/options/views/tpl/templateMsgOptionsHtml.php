<h4 class="cspTitle"><?php langBup::_e('Title')?>:</h4>
<?php echo htmlBup::text('opt_values[msg_title]', array('value' => $this->optModel->get('msg_title')))?>
<div class="cspLeftCol">
    <?php langBup::_e('Select color')?>:
    <?php echo htmlBup::colorpicker('opt_values[msg_title_color]', array('value' => $this->optModel->get('msg_title_color')))?>
</div>
<div class="cspRightCol">
    <?php langBup::_e('Select font')?>:
    <?php echo htmlBup::fontsList('opt_values[msg_title_font]', array('value' => $this->optModel->get('msg_title_font')));?>
</div>
<div class="clearfix"></div>
<div class="clearfix">
	<?php echo htmlBup::button(array('value' => langBup::_('Set default'), 'attrs' => 'id="cspMsgTitleSetDefault"'))?>
	<div id="cspAdminOptMsgTitleDefaultMsg"></div>
</div>
<div class="clearfix"></div>
<br />
<h4 class="cspTitle"><?php langBup::_e('Text')?>:</h4>
<?php echo htmlBup::textarea('opt_values[msg_text]', array('value' => $this->optModel->get('msg_text')))?>
<div class="cspLeftCol">
    <?php langBup::_e('Select color')?>:
    <?php echo htmlBup::colorpicker('opt_values[msg_text_color]', array('value' => $this->optModel->get('msg_text_color')))?>
</div>
<div class="cspRightCol">
    <?php langBup::_e('Select font')?>:
    <?php echo htmlBup::fontsList('opt_values[msg_text_font]', array('value' => $this->optModel->get('msg_text_font')));?>
</div>
<div class="clearfix"></div>
<div class="clearfix">
	<?php echo htmlBup::button(array('value' => langBup::_('Set default'), 'attrs' => 'id="cspMsgTextSetDefault"'))?>
	<div id="cspAdminOptMsgTextDefaultMsg"></div>
</div>
<div class="clearfix"></div>