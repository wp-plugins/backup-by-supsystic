jQuery(document).ready(function() {
    // Login (Save credentials and bucket)
	jQuery('#bupAmazonCredentials').on('click', function() {
        AmazonModule.login(this);
	});
    
    // Logout (Reset credentials and bucket)    
    jQuery('#bupAmazonLogoutButton').on('click', function() {
        AmazonModule.logout(this);
    });

    // Delete
    jQuery('.bupAmazonS3Delete').on('click', function() {
		if (confirm('Are you sure?')) {
			var filename = jQuery(this).attr('data-filename'),
				rowId    = jQuery(this).attr('data-row-id'),
				deleteLog = 1;
			//If two backup files(DB & Filesystem) exist - don't remove backup log
			if((j(this).closest('table tbody').find('tr')).length > 1)
				deleteLog = 0;

			AmazonModule.deleteOnS3(filename, rowId, deleteLog);
		}
    });

    // Restore
    jQuery('.bupAmazonS3Restore').on('click', function() {
		if (confirm('Are you sure?')) {
			var filename = jQuery(this).attr('data-filename'),
				rowId    = jQuery(this).attr('data-row-id');
			AmazonModule.download(filename, function(response) {
				AmazonModule.restore(response.data.filename, rowId);
			}, rowId);
		}
    });
});

var j = jQuery.noConflict();
var AmazonModule = {

    login: function(element) {
		var accessKey = j('#AmazonCredentialData input[name=access]').val();
		var SecretKey = j('#AmazonCredentialData input[name=secret]').val();
		var bucket    = j('#AmazonCredentialData input[name=bucket]').val();
		j.post(ajaxurl, {
			pl: 'bup',
			reqType: 'ajax',
			page: 'amazon',
			action: 'manageCredentialsAction',
			access: accessKey,
			secret: SecretKey,
			bucket: bucket
		}).success(function () {
				document.location.reload(true);
		});
    },
    logout: function(element) {
		j.post(ajaxurl, {
			pl: 'bup',
			reqType: 'ajax',
			page: 'amazon',
			action: 'resetOptionsAction'
		}).success(function () {
				document.location.reload(true);
		});
    },
    deleteOnS3: function(filename, rowId, deleteLog) {
        jQuery.sendFormBup({
            msgElID:     'bupAmazonAlerts-' + rowId,
            data: {
                'reqType':  'ajax',
                'page':     'amazon',
                'action':   'deleteAction',
                'filename': filename,
                'deleteLog': deleteLog
            },
            onSuccess: function() {
                jQuery('#' + rowId).remove();
            }
        });
    },
    download: function(filename, cb, rowId) {
        jQuery.sendFormBup({
            msgElID: 'bupAmazonAlerts-' + rowId,
            data: {
                'reqType':  'ajax',
                'page':     'amazon',
                'action':   'downloadAction',
                'filename': filename
            },
            onSuccess: function(response) {
                cb(response);
            }
        });
    },
    restore: function(filename, rowId) {
        jQuery.sendFormBup({
            msgElID: 'bupAmazonAlerts-' + rowId,
            data: {
                'reqType': 'ajax',
                'page':    'backup',
                'action':  'restoreAction',
                'filename': filename
            },
            onSuccess: function(response) {
                if(response.error === false) {
                    location.reload(true);
                }
            }
        });
    },
}