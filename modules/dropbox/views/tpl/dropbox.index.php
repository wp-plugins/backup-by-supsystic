<div id="bupDropboxWrapper">
	<div id="bupDropboxAlerts"></div>
	<table style="width: 100%; margin-bottom: 30px;">
		<tbody>
			<?php if(!empty($files['contents'])): ?>
				<?php foreach($files['contents'] as $index => $file): ?>
					<tr id="row-<?php echo $index; ?>">
						<td>
							<?php echo basename($file['path']); ?>
						</td>
						<td>
							<button 
								class="button button-primary button-large bupDropboxRestore"
								data-filename="<?php echo basename($file['path']); ?>"
							>
								<?php langBup::_e('Restore'); ?>
							</button>
							<button 
								class="button button-primary button-large bupDropboxDelete"
								data-filepath="<?php echo $file['path']; ?>"
								data-row-id="#row-<?php echo $index; ?>"
							>
								<?php langBup::_e('Delete'); ?>
							</button>
						</td>
					</tr>
			    <?php endforeach; ?>
			<?php else: ?>
					<tr>
						<td>
							<p><?php langBup::_e('Currently you don\'t have backup files on Dropbox'); ?></p>
						</td>
					</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<div id="bupDropboxLogoutWrapper">
		<button class="button button-primary button-large" id="bupDropboxLogout"><?php langBup::_e('Logout'); ?></button>
	</div>
</div>