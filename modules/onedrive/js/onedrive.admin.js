(function ($) {

    function OneDrive() {
        return this;
    }

    OneDrive.prototype.logout = function () {
        $.sendFormBup({
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
    };

    OneDrive.prototype.restore = function (fileId, fileName, rowId) {
        $.sendFormBup({
            msgElID: 'bupOnedriveAlerts-' + rowId,
            data: {
                reqType: 'ajax',
                page:    'onedrive',
                action:  'downloadAction',
                file_id: fileId
            },
            onSuccess: function (response) {
                if (!response.error) {
                    $.sendFormBup({
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
    };

    OneDrive.prototype.delete = function (e) {
		if (confirm('Are you sure?')) {
			var $button = $(e.currentTarget),
				$row = $button.parents('tr'),
				$filename = $row.data('filename'),
				$rowId = $row.data('id'),
				deleteLog = 1;
			//If two backup files(DB & Filesystem) exist - don't remove backup log
			if((j(this).closest('table tbody').find('tr')).length > 1)
				deleteLog = 0;

			$.sendFormBup({
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
    };

    $(document).ready(function (OneDrive) {

        $('.onedriveLogout').on('click', OneDrive.logout);

        $('.onedriveDelete').on('click', OneDrive.delete);

        $('.bupRestoreOnedrive').on('click', function () {
			if (confirm('Are you sure?')) {
            	OneDrive.restore($(this).data('file-id'), $(this).data('file-name'), $(this).data('row-id'));
			}
        });

    }(new OneDrive()));

}(jQuery));
