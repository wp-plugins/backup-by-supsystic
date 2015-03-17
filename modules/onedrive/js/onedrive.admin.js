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

	j('.bupRestoreOnedrive').on('click', function(event) {
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
				file_id: fileId
			},
			onSuccess: function (response) {
				if (!response.error) {
					j.sendFormBup({
						msgElID: 'bupOnedriveAlerts-' + rowId,
						data: {
							reqType: 'ajax',
							page:    'backup',
							action:  'restoreAction',
							filename: fileName
						},
						onSuccess: function (response) {
							if (!response.error) {
								location.reload(true);
							}
						}
					});
				}
			}
		});
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
