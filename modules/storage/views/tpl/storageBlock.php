<div class="backupBlock" align="right">
 <fieldset>
	<legend><?php echo '#'.$this->storageData[0].' '.$this->storageData[1]; ?></legend>
    <div align="left" id="MSG_EL_ID_<?php echo $this->storageData[0]; ?>"></div>
    <div class="bup_a_Send_to"><a href="javascript:void (0)">send to ></a></div>
    <div align="right" class="bup_Send_to">
        <?php $storageProviders = array(); $storageProviders = dispatcherBup::applyFilters('adminSendToLinks', $storageProviders); ?>
        <?php foreach($storageProviders as $provider): ?>
            <a 
                id="<?php echo $this->storageData[0]; ?>" 
                rel="<?php echo implode(',' ,$this->storageData[2]).',log-id'.$this->storageData[0].'.txt' ?>"
                class="upload" 
                href="javascript:void(0)" 
                data-action="<?php echo $provider['action']; ?>" 
                data-provider="<?php echo $provider['provider']; ?>" 
            > <!-- a -->
                <?php echo $provider['label']; ?>
            </a>
        <?php endforeach; ?>
    </div>
    <table>
	<?php foreach($this->storageData[2] as $el){ ?>
    <?php 
			$name = frameBup::_()->getModule('backup')->getModel()->fileNameFormat($el, 'prefix');
			$name = ($name == 'full' && preg_match('~\.sql~', $el)) ? 'database' : $name;
			//if ($name == 'full' && preg_match('~\.sql~', $el)) continue;
	?>
		<tr class="tabStr">
			<td align="right" style="height:20px !important; margin:0px !important; padding:0px !important; " ><?php echo ucfirst($name); ?></td>
            <td width="162">
				<?php echo htmlBup::submit($el, array('value' => langBup::_('Restore'), 'attrs' => 'class="button button-primary button-small restoreBup" id="'.$this->storageData[0].'"'))?>
                <a class="button button-primary button-small bupButDownload" href="<?php echo substr(BUP_URL, 0, strlen(BUP_URL)-1).frameBup::_()->getModule('options')->get('warehouse').$el; ?>" title="download">Download</a> <a class="delBackup" id="del|<?php echo $this->storageData[0].'|'.$el,'|'.$name; ?>" href="javascript:void (0)" title="delete"></a>
            </td>
		</tr>
	<?php } ?>
    </table>
	</fieldset>
</div>
