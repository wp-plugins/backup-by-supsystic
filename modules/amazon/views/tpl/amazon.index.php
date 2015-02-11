<div id="bupAmazonAlerts"></div>
<div id="bupAmazonWrapper">
    <section id="bupAmazonUploadedFiles">
        <table style="width: 100%;">
            <!-- <thead>
                <th><?php langBup::_e('ID'); ?></th>
                <th><?php langBup::_e('File'); ?></th>
                <th><?php langBup::_e('Actions'); ?></th>
            </thead> -->
            <tbody>
				<?php if(!empty($files)): ?>
                <?php foreach($files as $index => $file): ?>
                <tr id="backup-<?php echo $index;?>">
                    <!-- <td><?php echo $index+1; ?></td> -->
                    <td><?php echo $file; ?></td>
                    <td>
                        <button class="button button-primary bupAmazonS3Restore" data-row-id="backup-<?php echo $index; ?>" data-filename="<?php echo $file; ?>">
                            <?php langBup::_e('Restore'); ?>
                        </button>
                        <button class="button button-primary bupAmazonS3Delete" data-row-id="backup-<?php echo $index; ?>" data-filename="<?php echo $file; ?>">
                            <?php langBup::_e('Delete'); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
				<?php else: ?>
				<tr>
					<td>
						<p><?php langBup::_e('Currently you don\'t have backup files on Amazon S3'); ?></p>
					</td>
				</tr>
				<?php endif; ?>
            </tbody>
        </table>
    </section>
    <section style="margin-top: 30px;" id="bupAmazonLogout">
        <form id="bupAmazonEdit">
            <?php echo htmlBup::hidden('reqType', array('value' => 'ajax')); ?>
            <?php echo htmlBup::hidden('page',    array('value' => 'amazon')); ?>
            <?php echo htmlBup::hidden('action',  array('value' => 'resetOptionsAction')); ?>
            <?php echo htmlBup::hidden('reset',   array('value' => 'true')); ?>
            <?php echo htmlBup::submit('logout',  array(
                'value' => langBup::_('Logout'),
                'attrs' => 'class="button button-primary button-large"',
            ));
            ?>
        </form>
    </section>
</div>