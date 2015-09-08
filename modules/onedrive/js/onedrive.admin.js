jQuery(document).ready(function() {
	var j = jQuery.noConflict();

	j('.onedriveLogout').on('click', function(event) {
		event.preventDefault();

		j.sendFormBup({
			msgElID: 'bupOnedriveAlerts',
			data: {
				reqType: 'ajax',
				page:    'onedrive',
				action:  'logoutAction'
			},
			onSuccess: function () {
				location.reload(true);
			}
		});
	});

	j('.onedriveDelete').on('click', function(event) {
		event.preventDefault();

		if (confirm('Are you sure?')) {
			var $button = j(event.currentTarget),
				$row = $button.parents('tr'),
				$filename = $row.data('filename'),
				$rowId = $row.data('id'),
				deleteLog = 1;
			//If two backup files(DB & Filesystem) exist - don't remove backup log
			if((j(this).closest('table tbody').find('tr')).length > 1)
				deleteLog = 0;

			j.sendFormBup({
				msgElID: 'bupOnedriveAlerts-' + $rowId,
				data: {
					reqType:  'ajax',
					page:     'onedrive',
					action:   'deleteAction',
					id:       $button.data('file-id'),
					filename: $filename,
					deleteLog: deleteLog
				},
				onSuccess: function (response) {
					if (response.error === false) {
						$row.remove();
						jQuery('#MSG_EL_ID_' + $rowId).html('Backup successfully removed');
					}
				}
			});
		}
	});

	j(document).on('click', '.bupRestoreOnedrive', function(event){
		if (confirm('Are you sure?')) {
			event.preventDefault();

			var fileId = j(event.currentTarget).data('file-id');
			var fileName = j(event.currentTarget).data('file-name');
			var rowId = j(event.currentTarget).data('row-id');

			j.sendFormBup({
				msgElID: 'bupOnedriveAlerts-' + rowId,
				data: {
					reqType: 'ajax',
					page:    'onedrive',
					action:  'downloadAction',
					file_id: fileId,
					fileName: fileName
				},
				onSuccess: function (response) {
					if (!response.error) {
						var secretKey = jQuery('input.bupSecretKeyForCryptDB').val();
						j.sendFormBup({
							msgElID: 'bupOnedriveAlerts-' + rowId,
							data: {
								reqType: 'ajax',
								page:    'backup',
								action:  'restoreAction',
								filename: fileName,
								'encryptDBSecretKey': secretKey
							},
							onSuccess: function (response) {
								if (response.error === false && !response.data.need) {
									jQuery('#bupEncryptingModalWindow').dialog('close');
									location.reload(true);
								} else if (response.data.need) {
									requestSecretKeyToRestoreEncryptedDb('bupRestoreOnedrive', {
										'row-id': rowId,
										'file-id': fileId,
										'file-name': fileName
									}); // open modal window to request secret key for decrypt DB dump
								} else if (response.error) {
									jQuery('input.bupSecretKeyForCryptDB').val(''); // clear input value, because user earlier entered secret key
									jQuery('#bupEncryptingModalWindow').dialog('close');
								}
							}
						});
					}
				}
			});
		}
	});

	j('.oneDriveAuthenticate').on('click', function(event) {
		j.sendFormBup({
			data: {
				'reqType': 'ajax',
				'page':    'onedrive',
				'action':  'saveBackupDestinationOnAuthenticate'
			}
		});
	});
});
