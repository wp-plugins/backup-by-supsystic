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
		event.preventDefault();
		
		var file = j(this).attr('data-filepath'),
		    row  = j(this).attr('data-row-id');
			
		DropboxModule.remove(file, row);
	});
	
	j('.bupDropboxRestore').on('click', function(event) {
		event.preventDefault();
		
		var filename = j(this).attr('data-filename');
		DropboxModule.restore(filename);
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
	remove: function(file, row) {
		jQuery.sendFormBup({
			msgElID: 'bupDropboxAlerts',
			data: {
				'reqType': 'ajax',
				'page':    'dropbox',
				'action':  'deleteAction',
				'file':    file
			},
			onSuccess: function(response) {
                //console.log(response);
				if(response.error === false) {
					jQuery(row).remove();
				}
			}
		});
	},
	restore: function(filename) {
		jQuery.sendFormBup({
			msgElID: 'bupDropboxAlerts',
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
						msgElID: 'bupDropboxAlerts',
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