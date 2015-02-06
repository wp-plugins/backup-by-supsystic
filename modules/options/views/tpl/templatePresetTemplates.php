<?php if(!empty($this->tplModules)) { ?>
	<?php foreach($this->tplModules as $tplMod) { ?>
	<div class="cspAdminTemplateShell cspAdminTemplateShell-<?php echo $tplMod->getCode()?>">
		<a href="#" onclick="return setTemplateOptionBup('<?php echo $tplMod->getCode()?>');"><?php echo htmlBup::img( $tplMod->getPrevImgPath(), false, array('attrs' => 'class="cspAdminTemplateImgPrev"'));?></a>
		<br />
		<a href="#" onclick="return setTemplateOptionBup('<?php echo $tplMod->getCode()?>');"><?php echo $tplMod->getLabel()?></a>
		<div class="cspAdminTemplateSaveMsg"></div>
	</div>
	<?php } ?>
	<div style="clear: both;"></div>
<?php } else { ?>
	<?php langBup::_e('No template modules were found'); ?>
<?php }?>