jQuery(document).ready(function() {
	var j = jQuery.noConflict();
	
	j('#bupDropboxCode').submit(function(event) {
		event.preventDefault();
		DropboxModule.login(this);
	});
	
	j('#bupDropboxLogout').on('click', function(event) {
		event.preventDefault();
		DropboxModule.logout();
	});
	
	j('.bupDropboxDelete').on('click', function(event) {
		if (confirm('Are you sure?')) {
			event.preventDefault();

			var file = j(this).attr('data-filepath'),
				row  = j(this).attr('data-row-id'),
				fileType  = j(this).attr('data-file-type'),
				deleteLog = 1;

			//If two backup files(DB & Filesystem) exist - don't remove backup log
			if((j(this).closest('table tbody').find('tr')).length > 1)
				deleteLog = 0;

			DropboxModule.remove(file, row, fileType, deleteLog);
		}
	});
	
	j('.bupDropboxRestore').on('click', function(event) {
		if (confirm('Are you sure?')) {
			event.preventDefault();

			var filename = j(this).attr('data-filename'),
				row  = j(this).attr('data-row-id');
			DropboxModule.restore(filename, row);
		}
	});
});

var DropboxModule = {
	
	login: function(form) {
		jQuery(form).sendFormBup({
			msgElID: 'bupDropboxAlerts',
			onSuccess: function(response) {
				//console.log(response);
				if(response.error === false) {
					location.reload(true);
				}
			}
		});
	},
	logout: function() {
		jQuery.sendFormBup({
			msgElID: 'bupDropboxAlerts',
			data: {
				'reqType': 'ajax',
				'page':    'dropbox',
				'action':  'logoutAction'
			},
			onSuccess: function() {
				location.reload(true);
			}
		});
	},
	remove: function(file, row, fileType, deleteLog) {
		jQuery.sendFormBup({
			msgElID: 'bupDropboxAlerts-' + row,
			data: {
				'reqType': 'ajax',
				'page':    'dropbox',
				'action':  'deleteAction',
				'file':    file,
				'deleteLog':    deleteLog
			},
			onSuccess: function(response) {
				if(response.error === false) {
					jQuery('#row-' + fileType + '-' + row).remove();
				}
			}
		});
	},
	restore: function(filename, row) {
		jQuery.sendFormBup({
			msgElID: 'bupDropboxAlerts-' + row,
			data: {
				'reqType':  'ajax',
				'page':     'dropbox',
				'action':   'restoreAction',
				'file':     filename
			},
			onSuccess: function(response) {
				//console.log(response);
				if(response.error === false) {
					jQuery.sendFormBup({
						msgElID: 'bupDropboxAlerts-' + row,
						data: {
							'reqType': 'ajax',
							'page':    'backup',
							'action':  'restoreAction',
							'filename': response.data.filename
						},
						onSuccess: function(response) {
							//console.log(response);
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