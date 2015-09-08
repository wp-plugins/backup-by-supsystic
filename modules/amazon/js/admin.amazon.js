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
	jQuery(document).on('click', '.bupAmazonS3Restore', function() {
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
		var fullLoginData = true;

		var accessKey = j('#AmazonCredentialData input[name=access]').removeClass('bupInputError').val();
		var SecretKey = j('#AmazonCredentialData input[name=secret]').removeClass('bupInputError').val();
		var bucket    = j('#AmazonCredentialData input[name=bucket]').removeClass('bupInputError').val();

		if(!accessKey || !SecretKey || !bucket)
			fullLoginData = false;

		if(!accessKey)
			j('#AmazonCredentialData input[name=access]').addClass('bupInputError').attr('placeholder', 'Required Field');
		if(!SecretKey)
			j('#AmazonCredentialData input[name=secret]').addClass('bupInputError').attr('placeholder', 'Required Field');
		if(!bucket)
			j('#AmazonCredentialData input[name=bucket]').addClass('bupInputError').attr('placeholder', 'Required Field');

		if(fullLoginData) {
			jQuery.sendFormBup({
				msgElID: 'Amazon_Auth_Result',
				data: {
					pl: 'bup',
					reqType: 'ajax',
					page: 'amazon',
					action: 'manageCredentialsAction',
					access: accessKey,
					secret: SecretKey,
					bucket: bucket
				},
				onSuccess: function (res) {
					res = (typeof res === 'string') ? j.parseJSON(res) : res;
					if (!res.error)
						document.location.reload(true);
				}
			});
		}
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
            onSuccess: function(response) {
				if(!response.error) {
					location.reload();
					//jQuery('#' + rowId).remove();
				}
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
		var secretKey = jQuery('input.bupSecretKeyForCryptDB').val();
        jQuery.sendFormBup({
            msgElID: 'bupAmazonAlerts-' + rowId,
            data: {
                'reqType': 'ajax',
                'page':    'backup',
                'action':  'restoreAction',
                'filename': filename,
				'encryptDBSecretKey': secretKey
            },
            onSuccess: function(response) {
				if (response.error === false && !response.data.need) {
					jQuery('#bupEncryptingModalWindow').dialog('close');
					location.reload(true);
				} else if(response.data.need) {
					requestSecretKeyToRestoreEncryptedDb('bupAmazonS3Restore', {'row-id': rowId, 'filename': filename}); // open modal window to request secret key for decrypt DB dump
				} else if(response.error) {
					jQuery('input.bupSecretKeyForCryptDB').val(''); // clear input value, because user earlier entered secret key
					jQuery('#bupEncryptingModalWindow').dialog('close');
				}
            }
        });
    }
}