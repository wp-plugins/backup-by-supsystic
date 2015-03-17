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
				fileId = j(this).attr('data-file-id'),
				fileType = j(this).attr('data-file-type'),
				deleteLog = 1;

			//If two backup files(DB & Filesystem) exist - don't remove backup log
			if(j('#zip-'+rowId).length && fileType=='sql')
				deleteLog = 0;
			if(j('#sql-'+rowId).length && fileType=='zip')
				deleteLog = 0;

			GoogleDriveModule.delete(rowId, fileId, filename, fileType, deleteLog);
		} else {
			return false;
		}

	});
	
	j('.bupGDriveRestore').on('click', function(event) {
		if (confirm('Are you sure?')) {
			event.preventDefault();

			var download = j(this).attr('data-file-url'),
				filename = j(this).attr('data-file-name'),
				rowId  = j(this).attr('data-row-id'),
				fileType  = j(this).attr('data-file-type');

			GoogleDriveModule.restore(download, filename, rowId, fileType);
		}
	});

	j('.gDriveAuthenticate').on('click', function(event) {
		j.sendFormBup({
			data: {
				'reqType': 'ajax',
				'page':    'gdrive',
				'action':  'saveBackupDestinationOnAuthenticate'
			}
		});
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
	delete: function(rowId, fileId, filename, fileType, deleteLog) {
		jQuery.sendFormBup({
			msgElID: 'bupGDriveAlerts-' + rowId,
			data: {
				'reqType': 'ajax',
				'page':    'gdrive',
				'action':  'deleteAction',
				'file':    fileId,
				'filename':    filename,
				'deleteLog':    deleteLog
			},
			onSuccess: function(response) {
				if(response.error === false) {
					jQuery('#' + fileType + '-' + rowId).remove();
				}
			}
		});
	},
	restore: function(downloadUrl, filename, rowId) {
		jQuery.sendFormBup({
			msgElID: 'bupGDriveAlerts-' + rowId,
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
						msgElID: 'bupGDriveAlerts-' + rowId,
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