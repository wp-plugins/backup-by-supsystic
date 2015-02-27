!function(a){var b={},c={};a.ajaxq=function(d,e){function i(a){if(b[d])b[d].push(a);else{b[d]=[];var e=a();c[d]=e}}function j(){if(b[d]){var a=b[d].shift();if(a){var e=a();c[d]=e}else delete b[d]}}if("undefined"==typeof e)throw"AjaxQ: queue name is not provided";var f=a.Deferred(),g=f.promise();g.success=g.done,g.error=g.fail,g.complete=g.always;var h=a.extend(!0,{},e);return i(function(){var b=a.ajax.apply(window,[h]);return b.done(function(){f.resolve.apply(this,arguments)}),b.fail(function(){f.reject.apply(this,arguments)}),b.always(j),b}),g},a.each(["getq","postq"],function(b,c){a[c]=function(b,d,e,f,g){return a.isFunction(e)&&(g=g||f,f=e,e=void 0),a.ajaxq(b,{type:"postq"===c?"post":"get",url:d,data:e,success:f,dataType:g})}});var d=function(a){return b.hasOwnProperty(a)},e=function(){for(var a in b)if(d(a))return!0;return!1};a.ajaxq.isRunning=function(a){return a?d(a):e()},a.ajaxq.getActiveRequest=function(a){if(!a)throw"AjaxQ: queue name is required";return c[a]},a.ajaxq.abort=function(b){if(!b)throw"AjaxQ: queue name is required";a.ajaxq.clear(b);var c=a.ajaxq.getActiveRequest(b);c&&c.abort()},a.ajaxq.clear=function(a){if(a)b[a]&&delete b[a];else for(var c in b)b.hasOwnProperty(c)&&delete b[c]}}(jQuery);

jQuery(document).ready(function($) {
	bupShowLogDlg();
	bupBackupsShowLogDlg();
	var j = jQuery.noConflict();

	var inProcessMessage = j('#inProcessMessage');
	if (inProcessMessage.length) {
		var refreshId = setInterval(function () {
			j.post(ajaxurl, {
				pl: 'bup',
				reqType: 'ajax',
				page: 'backup',
				action: 'checkProcessAction'
			}).success(function (response) {
				response = j.parseJSON(response);

				if (response.data.in_process) {
					inProcessMessage.show();
				} else {
					inProcessMessage.hide();
					j('[name="backupnow"]').show();
				}
			});
		}, 15000);
	}

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

	// Autosave

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
	j('.bupRestore').on('click', function() {
		if (confirm('Are you sure?')) {
			var filename = j(this).attr('data-filename'),
					id   = j(this).attr('data-id');
			BackupModule.restore(id, filename);
		}
	});

	// Create
	j('#bupAdminMainForm').submit(function(event) {
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

		/*console.log('Module: ' + providerModule);
		console.log('Action: ' + providerAction);
		console.log('Files: '  + files);*/
		BackupModule.upload(providerModule, providerAction, files, id);

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
		jQuery.sendFormBup({
			msgElID: 'MSG_EL_ID_' + id,
			data: {
				'reqType':  'ajax',
				'page':     'backup',
				'action':   'restoreAction',
				'filename': filename
			},
			onSuccess: function(response) {
				if (response.error === false) {
					location.reload(true);
				}
			}
		});
	},
	create: function(form) {
		jQuery('input[name="backupnow"]').val('Cancel');
		jQuery('#bupInfo').hide();
		var progress = jQuery('.main-progress-bar');

        jQuery('.' + progress.attr('class') + ' .progress-bar span').css({ width: '1%' });
		progress.show().css({ display: 'inline-block' });;

		jQuery(form).sendFormBup({
			msgElID: 'BUP_MESS_MAIN',
			onSuccess: function(response) {

                if (response.data.length === 0) {
					jQuery('input[name="backupnow"]').val('Start Backup');
					progress.hide();
					jQuery('#BUP_SHOW_LOG').hide();
					jQuery('#bupInfo').show();
					jQuery('#bupLogText').html('Log is clear.');
                    return;
                }

                var files = response.data.files,
                    perStack = response.data.per_stack,
                    stacks = bupGetFilesStacks(files, perStack),
                    total = stacks.length, i = 0;

                if (stacks.length < 1) {
                    progress.hide();
                    jQuery('#BUP_MESS_MAIN')
                        .addClass('bupSuccessMsg')
                        .text('Could not find any files to backup based on your settings.');
                }

                jQuery.each(stacks, function(index, stack) {
                    bupGetTemporaryArchive(stack, progress, function() {

                        var span = jQuery('.progress-bar').find('span'),
                            width = jQuery.trim(parseFloat(jQuery(span[0]).attr('style').split(':')[1])), // :D
                            current, percent;

                        i++;

                        percent = (i / total) * 100;

                       /* console.log('Stack ' + i + ' of ' + total);
                        console.log('Complete: ' + percent + '%');*/
                        jQuery('#BUP_MESS_MAIN').addClass('bupSuccessMsg').text('Please wait while the plugin gathers information  (' + Math.round(percent) + '%)');
                        jQuery('#bupCompletePercent').text(Math.round(percent) + '%');

                        jQuery(span[0]).css({ width: percent + '%' });

                        if (percent === 100) {
                            setTimeout(function() {
                                sendCompleteRequest(progress);
                                jQuery('#BUP_MESS_MAIN').addClass('bupSuccessMsg').text('Processing file stacks, please wait. It may take some time (depending on the number and size of files)');
                            }, 1000);
                        }
                    });
                });
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
	}
};

function bupGetFilesStacks(files, num) {
    var stack = [],
        parts = Math.ceil(files.length / num);

    for(var i = 0; i < parts; i++) {
        stack.push(bupGetStack(files, num));
    }

    return stack;
}

function bupGetStack(files, num) {

    var stack = [];

    if (files.length < num) {
        num = files.length;
    }

    for(var j = 0; j < num; j++) {
        stack.push(files.pop());
    }

    return stack;
}

function bupGetTemporaryArchive(files, progress, cb) {
    jQuery.postq('bupTempArchive', ajaxurl,{
        reqType: 'ajax',
        page:    'backup',
        action:  'createStackAction',
        files:   files,
        pl:      'bup'
    }, function (response) {

        response = jQuery.parseJSON(response);

        cb();
        if (!response.error) {
            jQuery.postq('bupWriteTempArchive', ajaxurl, {
                reqType: 'ajax',
                page: 'backup',
                action: 'writeTmpDbAction',
                tmp: response.data.filename,
                pl: 'bup'
            });
        } else {
            progress.hide();
        }
    });

//    jQuery.postq('bupTempArchive', {
//        data: {
//            reqType:  'ajax',
//            page:     'backup',
//            action:   'createStackAction',
//            files:    files
//        },
//        onSuccess: function(response) {
//
//            cb();
//
//            if (response.error === false) {
//                jQuery.sendFormBup({
//                    data: {
//                        reqType:  'ajax',
//                        page:     'backup',
//                        action:   'writeTmpDbAction',
//                        tmp:      response.data.filename
//                    }
//                });
//            } else {
//                progress.hide(function() {
//                });
//            }
//        }
//    });
}

function sendCompleteRequest(progress) {
    jQuery.sendFormBup({
        msgElID: 'BUP_MESS_MAIN',
        data: {
            reqType:  'ajax',
            page:     'backup',
            action:   'createAction',
            complete: true
        },
        onSuccess: function(r) {
			jQuery('input[name="backupnow"]').val('Start Backup');
            progress.hide();
			jQuery('#BUP_SHOW_LOG').hide();
			jQuery('#bupInfo').show();
			jQuery('#bupLogText').html('Log is clear.');
        }
    });
}

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

		j.post(ajaxurl, {
			pl: 'bup',
			reqType: 'ajax',
			page: 'backup',
			action: 'getBackupLog'
		}).success(function (response) {
				response = j.parseJSON(response);
				if(response.data.backupLog){
					var logText = response.data.backupLog.join('<br>');
					jQuery('#bupLogText').html(logText);
				}
		});
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

	jQuery('.bupShowLogDlg').click(function($this){
		j('#bupLogText').html('');
		var logContent = j($this.currentTarget).data('log');
		j('#bupLogText').html(logContent);
		$container.dialog('open');
	});
}