jQuery(document).ready(function(){
	
	var j = jQuery.noConflict();
	
	j('#bupGDriveCredentials').submit(function(event) {
		event.preventDefault();
		GoogleDriveModule.authenticate(this);
	});
	
	j('#bupGDriveLogout').on('click', function(event) {
		event.preventDefault();
		GoogleDriveModule.logout();
	});
	
	j('.bupGDriveDelete').on('click', function(event) {
		if (confirm('Are you sure?')) {
			event.preventDefault();

			var rowId  = j(this).attr('data-row-id'),
				filename  = j(this).attr('data-filename'),
				fileId = j(this).attr('data-file-id');
			GoogleDriveModule.delete(rowId, fileId, filename);
		} else {
			return false;
		}

	});
	
	j('.bupGDriveRestore').on('click', function(event) {
		event.preventDefault();
		
		var download = j(this).attr('data-file-url'),
			filename = j(this).attr('data-file-name');
			
		GoogleDriveModule.restore(download, filename);
	});
});

var GoogleDriveModule = {
	/**
	 * Send form data to controller and try to authenticate client
	 * @returns void
	 */
	authenticate: function(form) {
		jQuery(form).sendFormBup({
			msgElID: 'bupGDriveAlerts',
			onSuccess: function() {
				location.reload(true);
			}
		});
	},
	/**
	 * Reset credentials
	 * @returns void
	 */
	logout: function() {
		jQuery.sendFormBup({
			msgElID: 'bupGDriveAlerts',
			data: {
				'reqType': 'ajax',
				'page':    'gdrive',
				'action':  'resetCredentialsAction'
			},
			onSuccess: function() {
				location.reload(true);
			}
		});
	},
	delete: function(rowId, fileId, filename) {
		jQuery.sendFormBup({
			msgElID: 'bupGDriveAlerts',
			data: {
				'reqType': 'ajax',
				'page':    'gdrive',
				'action':  'deleteAction',
				'file':    fileId,
				'filename':    filename
			},
			onSuccess: function(response) {
				if(response.error === false) {
					jQuery('#bupControl-' + rowId).remove();
					jQuery('#MSG_EL_ID_' + rowId).html('Backup successfully removed');
				}
			}
		});
	},
	restore: function(downloadUrl, filename) {
		jQuery.sendFormBup({
			msgElID: 'bupGDriveAlerts',
			data: {
				'reqType':      'ajax',
				'page':         'gdrive',
				'action':       'downloadAction',
				'filename':     filename,
				'download_url': downloadUrl
			},
			onSuccess: function(response) {
				if(response.error === false) {
					jQuery.sendFormBup({
						msgElID: 'bupGDriveAlerts',
						data: {
							'reqType': 'ajax',
							'page':    'backup',
							'action':  'restoreAction',
							'filename': response.data.filename
						},
						onSuccess: function(response) {
							if(response.error === false) {
								location.reload(true);
							}
						}
					});
				}
			}
		});
	}
};