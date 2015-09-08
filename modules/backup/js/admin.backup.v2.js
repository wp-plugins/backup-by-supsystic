!function(a){var b={},c={};a.ajaxq=function(d,e){function i(a){if(b[d])b[d].push(a);else{b[d]=[];var e=a();c[d]=e}}function j(){if(b[d]){var a=b[d].shift();if(a){var e=a();c[d]=e}else delete b[d]}}if("undefined"==typeof e)throw"AjaxQ: queue name is not provided";var f=a.Deferred(),g=f.promise();g.success=g.done,g.error=g.fail,g.complete=g.always;var h=a.extend(!0,{},e);return i(function(){var b=a.ajax.apply(window,[h]);return b.done(function(){f.resolve.apply(this,arguments)}),b.fail(function(){f.reject.apply(this,arguments)}),b.always(j),b}),g},a.each(["getq","postq"],function(b,c){a[c]=function(b,d,e,f,g){return a.isFunction(e)&&(g=g||f,f=e,e=void 0),a.ajaxq(b,{type:"postq"===c?"post":"get",url:d,data:e,success:f,dataType:g})}});var d=function(a){return b.hasOwnProperty(a)},e=function(){for(var a in b)if(d(a))return!0;return!1};a.ajaxq.isRunning=function(a){return a?d(a):e()},a.ajaxq.getActiveRequest=function(a){if(!a)throw"AjaxQ: queue name is required";return c[a]},a.ajaxq.abort=function(b){if(!b)throw"AjaxQ: queue name is required";a.ajaxq.clear(b);var c=a.ajaxq.getActiveRequest(b);c&&c.abort()},a.ajaxq.clear=function(a){if(a)b[a]&&delete b[a];else for(var c in b)b.hasOwnProperty(c)&&delete b[c]}}(jQuery);

jQuery(document).ready(function($) {

	//handlers for functional in free version plugin
	if(bupFreeVersionPlugin === 'true'){
		jQuery('#bupShowMigratePromoDlg').dialog({
			modal:    true,
			autoOpen: false,
			width: 500,
			height: 200
		});
		jQuery('.bupMigratePromo').on('click', function(){
			jQuery('#bupShowMigratePromoDlg').dialog('open');
		});
	}

	//promo block for pro-version, module 'scrambler'
	var databaseCheckbox = jQuery('.bupDatabaseCheckbox');
	var secretKeyRow = jQuery('.bupSecretKeyDBRow'); // input for secret key encrypting db on backup setting page
	var databaseBackupSelected = databaseCheckbox.attr('checked') ? true : false;

	if(databaseBackupSelected)
		secretKeyRow.show();
	else
		secretKeyRow.hide();

	databaseCheckbox.on('click', function($this) {
		secretKeyRow.toggle(200);
	});

	bupShowLogDlg();
	bupBackupsShowLogDlg();
	var j = jQuery.noConflict();

	var inProcessMessage = j('#inProcessMessage');

	// Warehouse path
	var warehouseInput = j('#warehouseInput'),
		warehouseToggler = j('#warehouseToggle'),
		warehouseInputEvents = 'input change paste keyup';

	warehouseInput.on(warehouseInputEvents, function () {
		var input = j( this );

		j('#realpath').text(input.val());
	}).trigger('change');

	warehouseToggler.on('change', function () {
		var bupAbsPath = j('#abspath');

		bupAbsPath.hide();

		if (this.checked) {
			bupAbsPath.show();
		}
	}).trigger('change');

	// Toggle 'Send to'
	j('.bupSendTo').on('click', function() {
		j(this).parent().next().toggle();
	});

	// Download
	j('.bupDownload').on('click', function() {
		var filename = j(this).attr('data-filename');

		BackupModule.download(filename);
	});

	// Delete
	j('.bupDelete').on('click', function() {
		if (confirm('Are you sure?')) {
			var filename  = j(this).attr('data-filename'),
				id        = j(this).attr('data-id'),
				deleteLog = 1;
			//If two backup files(DB & Filesystem) exist - don't remove backup log
			if((j(this).closest('table tbody').find('tr')).length > 1)
				deleteLog = 0;

			BackupModule.remove(id, filename, this, deleteLog);
		}
	});

	// Restore
	jQuery(document).on('click', '.bupRestore', function(){
		if (confirm('Are you sure?')) {
			var filename = j(this).attr('data-filename'),
					id   = j(this).attr('data-id');
			BackupModule.restore(id, filename);
		}
	});

	// Create
	j('#bupAdminMainForm').submit(function(event) {
		var isAuthenticated = parseInt(jQuery('#bup-is-authenticated').val());
		var msgForNotAuthenticated = jQuery('#bup-msg-for-not-authenticated').val();
		var authenticateBlockId = jQuery('#bup-authenticate-block-id').val();
		var emailAddress = jQuery('.emailAddress input[type=text]').val();
		var emailNotify = jQuery("input.emailCh").prop('checked');
		jQuery('span.bupErrorMsg').html('');

		if(emailNotify && emailAddress && !isValidEmailAddress(emailAddress)) {
			var emailErrorMsg = jQuery('.emailAddress').data('wrong-email-msg');
			jQuery('#BUP_MESS_MAIN').addClass('bupErrorMsg').html(emailErrorMsg + emailAddress);
			return false;
		}

		if(!isAuthenticated) {
			$('html, body').animate({
				scrollTop: $('#' + authenticateBlockId).offset().top
			}, 2000);

			jQuery('#' + authenticateBlockId + ' span.bupErrorMsg').html(msgForNotAuthenticated);
			return false;
		}

		var submitButton = jQuery('input[name="backupnow"]').val();
		if(submitButton == 'Cancel'){
			// Unlock
			if (confirm('Are you sure?')) {
				$.post(ajaxurl, {
					pl: 'bup',
					reqType: 'ajax',
					page: 'backup',
					action: 'unlockAction'
				}).success(function () {
						document.location.reload(true);
				});
			}
		}
		if(submitButton != 'Start Backup')
			return false;

		jQuery('#BUP_SHOW_LOG').show();
		jQuery("#bupOptions").clone().prependTo("#bupAdminMainForm");
		jQuery("#bupAdminMainForm #bupOptions").hide();
		event.preventDefault();
		BackupModule.create(this);
		jQuery("#bupAdminMainForm #bupOptions").remove();
	});

	jQuery('.bupSendToBtn').on('click', function(clickEvent) {
		clickEvent.preventDefault();
		var providerModule = jQuery(this).attr('data-provider'),
			providerAction = jQuery(this).attr('data-action'),
			files          = jQuery(this).attr('data-files'),
			id             = jQuery(this).attr('id');

		BackupModule.upload(providerModule, providerAction, files, id);
	});

	j('.bupSaveRestoreSetting').on('click', function(clickEvent) {
		BackupModule.saveRestoreSetting(clickEvent);
	});
});

/**
 * Backup Module
 */
var BackupModule = {
	download: function(filename) {
		document.location.href = document.location.href + '&download=' + filename;
	},
	remove: function(id, filename, button, deleteLog) {
		jQuery.sendFormBup({
			msgElID: 'MSG_EL_ID_' + id,
			data: {
				'reqType':  'ajax',
				'page':     'backup',
				'action':   'removeAction',
				'filename': filename,
				'deleteLog': deleteLog
			},
			onSuccess: function(response) {
				if (response.error === false) {
					jQuery(button).parent().parent().remove();
				}
			}
		});
	},
	restore: function(id, filename) {
		var secretKey = jQuery('input.bupSecretKeyForCryptDB').val();
		jQuery.sendFormBup({
			msgElID: 'MSG_EL_ID_' + id,
			data: {
				'reqType':  'ajax',
				'page':     'backup',
				'action':   'restoreAction',
				'filename': filename,
				'encryptDBSecretKey': secretKey
			},
			onSuccess: function(response) {
				if (response.error === false && !response.data.need) {
					jQuery('#bupEncryptingModalWindow').dialog('close');
					location.reload(true);
				} else if(response.data.need) {
					requestSecretKeyToRestoreEncryptedDb('bupRestore', {'id': id, 'filename': filename}); // open modal window to request secret key for decrypt DB dump
				} else if(response.error) {
					jQuery('input.bupSecretKeyForCryptDB').val(''); // clear input value, because user earlier entered secret key
					jQuery('#bupEncryptingModalWindow').dialog('close');
				}
			}
		});
	},
	create: function(form) {
		//jQuery('input[name="backupnow"]').val('Cancel');
		jQuery('input[name="backupnow"]').hide();
		jQuery('#bupInfo').hide();
		jQuery('#bupCompletePercent').html('');
		var progress = jQuery('.main-progress-bar');

        jQuery('.' + progress.attr('class') + ' .progress-bar span').css({ width: '1%' });
		progress.show().css({ display: 'inline-block' });

		jQuery(form).sendFormBup({
			msgElID: 'BUP_MESS_MAIN',
			onSuccess: function(response) {
				if(response.data.backupLog != undefined) {
					//backupLog.html(response.data.backupLog);
				}

				if(response.data.backupComplete) {
					onBackupFullCompleteAction();
					return;
				}

				var backupId = response.data.backupId;

				var refreshLog = setInterval(function () {
					jQuery.post(ajaxurl, {
						pl: 'bup',
						reqType: 'ajax',
						page: 'backup',
						action: 'getBackupLog',
						backupId: backupId
					}).success(function (response) {
						response = jQuery.parseJSON(response);

						if(response.data.backupLog != undefined) {
							jQuery('span.bupShowLogDlg').data('backup-log', response.data.backupLog).attr('data-backup-log', response.data.backupLog);
						}

						if(response.data.backupProcessPercent != undefined && response.data.backupProcessPercent) {
							jQuery('#bupCompletePercent').html(response.data.backupProcessPercent + '%');
						} else {
							jQuery('#bupCompletePercent').html('');
						}

						if(response.data.backupMessage != undefined && response.data.backupMessage) {
							jQuery('#BUP_MESS_MAIN').addClass('bupSuccessMsg').html(response.data.backupMessage);
						}

						if (response.data.backupComplete) {
							clearInterval(refreshLog);
							onBackupFullCompleteAction();
						}
					});
				}, 5000);
			}
		});
//		jQuery('#BUP_SHOW_LOG').hide();
	},
	upload: function(providerModule, providerAction, files, identifier) {
		jQuery.sendFormBup({
			msgElID: 'MSG_EL_ID_' + identifier,
			data: {
				page:    providerModule, // Module
				action:  providerAction, // Action
				reqType: 'ajax',         // Request type
				sendArr: files           // Files
			}
		});
	},
	saveRestoreSetting: function(clickEvent){
		var key = j(clickEvent.currentTarget).data('setting-key');
		var value = j(clickEvent.currentTarget).attr('checked');

		jQuery.sendFormBup({
			msgElID: 'bupRestorePresetsMsg',
			data: {
				'page':    'backup', // Module
				'action':  'saveRestoreSettingAction', // Action
				'reqType': 'ajax',         // Request type
				'setting-key': key,
				'value': value

				},
			onSuccess: function(response) {
					if(response.error)
						document.location.reload();
			}
		});
	}
};

function bupShowLogDlg() {
	var $container = jQuery('#bupShowLogDlg').dialog({
		modal:    true,
		autoOpen: false,
		width: 1000,
		height: 400
	});
	jQuery('.bupShowLogDlg').click(function(){
		var j = jQuery.noConflict();
		$container.dialog('open');

		jQuery('#bupLogText').html(jQuery('span.bupShowLogDlg').data('backup-log'));
		return false;
	});
}

function bupBackupsShowLogDlg() {
	var $container = jQuery('#bupShowLogDlg').dialog({
		modal:    true,
		autoOpen: false,
		width: 1000,
		height: 400
	});
	var j = jQuery.noConflict();

	jQuery('.bupShowLogHistory').click(function($this){
		j('#bupLogText').html('');
		var logContent = j($this.currentTarget).data('log');
		j('#bupLogText').html(logContent);
		$container.dialog('open');
	});
}

function onBackupFullCompleteAction(){
	//jQuery('input[name="backupnow"]').val('Start Backup');
	jQuery('input[name="backupnow"]').show();
	jQuery('.main-progress-bar').hide();
	jQuery('#BUP_SHOW_LOG').hide();
	jQuery('#bupInfo').show();
	jQuery('#bupLogText').html('Log is clear.');
}