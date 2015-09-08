
// Checkbox toggler
(function ($) {

    function CheckboxController() {
        this.$checkboxes = $('.bupFull');
        this.$fullBackup = $('#bupFullBackup');
    }

    CheckboxController.prototype.isChecked = (function () {
        var checked = true, map;

        map = (function (index, checkbox) {
            if (!checkbox.checked) {
                checked = false;
            }
        });

        this.$checkboxes.each(map);

        return checked;
    });

    CheckboxController.prototype.toggle = (function (e) {
        $.each(this.$checkboxes, (function (index, checkbox) {
            if (e.currentTarget.checked) {
                $(checkbox).attr('checked', 'checked');
                $('.bupSecretKeyDBRow').show();
            } else {
                $(checkbox).removeAttr('checked');
				$('.bupSecretKeyDBRow').hide();
            }
        }));
		bupCheckUpdateArea();
    });

    CheckboxController.prototype.toggleFull = (function (e) {
        if (!e.currentTarget.checked) {
            this.$fullBackup.removeAttr('checked');
        } else {
            var checked = true;

            $.each(this.$checkboxes, $.proxy((function (index, checkbox) {
                if (!checkbox.checked) {
                    checked = false;
                }
            }), this));

            if (checked) {
                this.$fullBackup.attr('checked', 'checked').trigger('change');
            }
        }
		bupCheckUpdateArea();
    });

    $(document).ready(function () {
        var Ctrl = new CheckboxController();

        Ctrl.$fullBackup.on('change', $.proxy(Ctrl.toggle, Ctrl));
        Ctrl.$checkboxes.on('click', $.proxy(Ctrl.toggleFull, Ctrl));

        if (Ctrl.isChecked()) {
            Ctrl.$fullBackup.attr('checked', 'checked');
        }
    });

}(jQuery));

var bupAdminFormChanged = [];
var bupAdminCheckboxChanged = false;
var goToOptionsTab = false;
// window.onbeforeunload = function(){
// 	// If there are at lease one unsaved form - show message for confirnation for page leave
// 	if(bupAdminFormChanged.length)
// 		return 'Some changes were not-saved. Are you sure you want to leave?';
// };



jQuery(document).ready(function($){
	if(bupPageTitle !== 'Overview'){
		var title = jQuery('head title').html();
		title = title.replace('Overview', bupPageTitle);
		jQuery('head title').html(title);
	}
	if(typeof(bupActiveTab) != 'undefined' && bupActiveTab != 'bupMainOptions' && jQuery('#toplevel_page_supsystic-backup').hasClass('wp-has-current-submenu')) {
		var subMenus = jQuery('#toplevel_page_supsystic-backup').find('.wp-submenu li');
		subMenus.removeClass('current').each(function(){
			if(jQuery(this).find('a[href$="&tab=' + bupActiveTab + '"]').size()) {
				jQuery(this).addClass('current');
			}
		});
	}

	jQuery('.supsystic-tooltip').tooltipster({
		contentAsHTML: true
		,	interactive: true
		,	speed: 250
		,	delay: 0
		,	animation: 'swing'
		,	position: 'top-left'
		,	maxWidth: 450
		,	functionReady: function(origin, tooltip) {
			// Move tolltip from center of it's arrow - to right side, like on our design, position: 'top-right' option didn't work for me
			/*var fromLeftSide = 20
			 ,	width = tooltip.width();
			 if(width > fromLeftSide) {
			 var modeToLeft = (width / 2) - fromLeftSide
			 ,	arrow = tooltip.find('.tooltipster-arrow span')
			 ,	arrowWidth = 18;
			 tooltip.css({
			 'left': parseInt(tooltip.css('left')) + modeToLeft
			 });
			 arrow.css({
			 'left': -(width - fromLeftSide - arrowWidth)
			 });
			 }*/
		}
	});
	bupInitCustomCheckRadio();
	bupInitStickyItem();

	var $destinationForm = $('input[name=dest_opt]');
	$destinationForm.on('click', function ($this) {
		var radioButton = $this.currentTarget;
		var proVersion = jQuery(radioButton).closest('div.bupMargDest').find('span.bupAIP').html() ? false : true;
		var backupPlace = radioButton.attributes.value.value;

		var isAuthenticated = jQuery(radioButton).data('is-authenticated');
		jQuery('#bup-is-authenticated').val(isAuthenticated);

		var msgForNotAuthenticated = jQuery(radioButton).data('msg-for-not-authenticated');
		jQuery('#bup-msg-for-not-authenticated').val(msgForNotAuthenticated);

		var authenticateBlockId = jQuery(radioButton).data('authenticate-block-id');
		jQuery('#bup-authenticate-block-id').val(authenticateBlockId);

		jQuery('div.bupOptions').hide('slow');
		jQuery('div .bup-' + backupPlace).show('slow');
		if(proVersion){
			jQuery('input[name="backupnow"]').val('Start Backup');
			//jQuery('#bupMainOption').show('slow').css('display', 'inline-block');
		}else{
			jQuery('input[name="backupnow"]').val('Where to Backup:');
			//jQuery('#bupMainOption').hide('slow');
		}
	});

	var $backupDest = $('input[name=backupDest]').val();
	if($backupDest) {
		$('.bupMargDest input[value=' + $backupDest + ']').next().click();
	}

    var changeHiddenField = (function (checkbox, field) {
        jQuery(checkbox).on('change', function () {
            if (this.checked) {
                jQuery(field).val(1);
            } else {
                jQuery(field).val(0);
            }
        });
    });

    changeHiddenField('.emailCh', '[name="opt_values[email_ch]"]');
    changeHiddenField('.wareabs', '[name="opt_values[warehouse_abs]"]');

	//jQuery('.mainOpinonsBup input[type=checkbox]').change(function() {
	jQuery('.mainOpinonsBup .bupCheckbox').change(function() {
		bupAdminCheckboxChanged = true;

		if (jQuery(this).attr('name') == 'opt_values[full]') {
			jQuery('input.bupOptDatabase').removeAttr('checked');
			jQuery('input.bupOptAny').removeAttr('checked');
		} else if (jQuery(this).attr('name') == 'opt_values[database]') {
//			jQuery('input.bupOptAny').removeAttr('checked');
			jQuery('input.bupOptFull').removeAttr('checked');
		} else if (jQuery(this).attr('name') == 'opt_values[any]') {
			jQuery('input.bupOptFull').removeAttr('checked');
		}

	});

	jQuery('input[name=__toggleEmailCheckbox]').change(function() {
		if (jQuery(this).prop('checked')){
			jQuery('.emailAddress').show();
		} else {
			jQuery('.emailAddress').hide();
		}
	});

	jQuery('#bupAdminTemplateOptionsForm').submit(function(){
		bupAdminCheckboxChanged = false;
		jQuery(this).sendFormBup({
			msgElID: 'bupAdminTemplateOptionsMsg'
		});
		return false;
	});

	jQuery(document).on('click', '.resetDrBx a', function(){
		if (confirm('Are you sure?')) {
			jQuery(this).sendFormBup({
		  msgElID: 'bupSuccessMsg',
		  data: {page: 'dropbox', action: 'resetAccount', reqType: 'ajax' },
		  onSuccess: function(res) {
			  if (!res.error) {
				  location.reload();
				  //getDropboxListBup();
			  }
		  }
		});
		} else {
			return false;
		}
	});

	jQuery('#bupMainFormOptions').submit(function(){
		return false;
	});

jQuery(document).on('click', '#redirStorage', function() {
      //getSubersListBup();
	  var index = jQuery('#bupAdminOptionsTabs a[href="#bupStorageOptions"]').parents('li').index();
		jQuery('#bupAdminOptionsTabs').tabs('option', 'active', index);
	return false;
 });

jQuery(document).on('click', '.bupRedirDropBox', function() {
 	var index = jQuery('#bupAdminOptionsTabs a[href="#bupDropboxOptions"]').parents('li').index();
		jQuery('#bupAdminOptionsTabs').tabs('option', 'active', index);
	return false;
});

jQuery(document).on('click', '#redirLog', function() {
      getLogListBup();
	  var index = jQuery('#bupAdminOptionsTabs a[href="#bupLogOptions"]').parents('li').index();
		jQuery('#bupAdminOptionsTabs').tabs('option', 'active', index);
	return false;
 });

jQuery(document).on('click', '#redirDropbox', function() {
      getDropboxListBup();
	  var index = jQuery('#bupAdminOptionsTabs a[href="#bupDropboxOptions"]').parents('li').index();
		jQuery('#bupAdminOptionsTabs').tabs('option', 'active', index);
	return false;
 });

bupChangeDestOptions();

/*-----*/
	jQuery('#bupAdminOptionsTabs').tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    jQuery( "#bupAdminOptionsTabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );

	jQuery( "#bupAdminOptionsTabs" ).tabs({

  		beforeActivate: function( event, ui ) {
			//alert(ui.newPanel.attr('id'));
			// if (ui.newPanel.attr('id') != 'bupTemplateOptions') {
			//   if (bupAdminCheckboxChanged){
			// 	  if (confirm('Do you want to exit without saving all changes?')) {
			// 		  bupAdminCheckboxChanged = false;
			// 	  } else {
			// 		  goToOptionsTab = true;
			// 	  }
			//   }
			// }
            //
			// if (ui.newPanel.attr('id') != 'bupScheduleOptions') {
			//   if (bupAdminSheduleCheckboxChanged){
			// 	  if (confirm('Do you want to exit without saving all changes?')) {
			// 		  bupAdminSheduleCheckboxChanged = false;
			// 	  } else {
			// 		  goToScheduleTab = true;
			// 	  }
			//   }
			// }
            //

		},
		activate: function( event, ui ) {
			if (goToOptionsTab){
				var index = jQuery('#bupAdminOptionsTabs a[href="#bupTemplateOptions"]').parents('li').index();
				jQuery('#bupAdminOptionsTabs').tabs('option', 'active', index);
				goToOptionsTab = false;
			}

			if (goToScheduleTab){
				var index = jQuery('#bupAdminOptionsTabs a[href="#bupScheduleOptions"]').parents('li').index();
				jQuery('#bupAdminOptionsTabs').tabs('option', 'active', index);
				goToScheduleTab = false;
			}
		}
	});

	jQuery('#bupAdminOptionsForm').submit(function(){
		jQuery(this).sendFormBup({
			msgElID: 'bupAdminMainOptsMsg'
		,	onSuccess: function(res) {
				if(!res.error) {
					changeModeOptionBup( jQuery('#bupAdminOptionsForm [name="opt_values[mode]"]').val() );
				}
			}
		});
		return false;
	});
	jQuery('#bupAdminOptionsSaveMsg').submit(function(){
		return false;
	});
	jQuery('.bupSetTemplateOptionButton').click(function(){
		toeShowTemplatePopupBup();
		return false;
	});
	jQuery('.bupGoToTemplateTabOptionButton').click(function(){
		// Go to tempalte options tab
		var index = jQuery('#bupAdminOptionsTabs a[href="#bupTemplateOptions"]').parents('li').index();
		// @deprecated by jquery
		//jQuery('#bupAdminOptionsTabs').tabs('select', index);
		jQuery('#bupAdminOptionsTabs').tabs('option', 'active', index);

		toeShowTemplatePopupBup();

		return false;
	});
	function toeShowTemplatePopupBup() {
		/*var width = jQuery(document).width() * 0.9
		,	height = jQuery(document).height() * 0.9;*/
		tb_show(toeLangBup('Preset Templates'), '#TB_inline?inlineId=bupAdminTemplatesSelection', false);
	}
	jQuery('#bupAdminOptionsForm [name="opt_values[mode]"]').change(function(){
		changeModeOptionBup( jQuery(this).val(), true );
	});
	changeModeOptionBup( toeOptionBup('mode') );
	selectTemplateImageBup( toeOptionBup('template') );
	// Remove class is to remove this class from wrapper object
	//jQuery('.bupAdminTemplateOptRow').not('.bupAvoidJqueryUiStyle').buttonset().removeClass('ui-buttonset');

	jQuery('#bupAdminTemplateOptionsForm [name="opt_values[bg_type]"]').change(function(){
		changeBgTypeOptionBup();
	});
	changeBgTypeOptionBup();

	 jQuery('.bupOptTip').live('mouseover',function(event){
        if(!jQuery('#bupOptDescription').attr('toeFixTip')) {
			var pageY = event.pageY - jQuery(window).scrollTop();
			var pageX = event.pageX;
			var tipMsg = jQuery(this).attr('tip');
			var moveToLeft = jQuery(this).hasClass('toeTipToLeft');	// Move message to left of the tip link
			if(typeof(tipMsg) == 'undefined' || tipMsg == '') {
				tipMsg = jQuery(this).attr('title');
			}
			toeOptShowDescriptionBup( tipMsg, pageX, pageY, moveToLeft );
			jQuery('#bupOptDescription').attr('toeFixTip', 1);
		}
        return false;
    });
    jQuery('.bupOptTip').live('mouseout',function(){
		toeOptTimeoutHideDescriptionBup();
        return false;
    });
	jQuery('#bupOptDescription').live('mouseover',function(e){
		jQuery(this).attr('toeFixTip', 1);
		return false;
    });
	jQuery('#bupOptDescription').live('mouseout',function(e){
		toeOptTimeoutHideDescriptionBup();
		return false;
    });

	jQuery('#bupColorBgSetDefault').click(function(){
		jQuery.sendFormBup({
			data: {page: 'options', action: 'setTplDefault', code: 'bg_color', reqType: 'ajax'}
		,	msgElID: 'bupAdminOptColorDefaultMsg'
		,	onSuccess: function(res) {
				if(!res.error) {
					if(res.data.newOptValue) {
						jQuery('#bupAdminTemplateOptionsForm [name="opt_values[bg_color]"]')
							.val( res.data.newOptValue )
							.css('background-color', res.data.newOptValue);
					}
				}
			}
		});
		return false;
	});
	jQuery('#bupImgBgSetDefault').click(function(){
		jQuery.sendFormBup({
			data: {page: 'options', action: 'setTplDefault', code: 'bg_image', reqType: 'ajax'}
		,	msgElID: 'bupAdminOptImgBgDefaultMsg'
		,	onSuccess: function(res) {
				if(!res.error) {
					if(res.data.newOptValue) {
						jQuery('#bupOptBgImgPrev').attr('src', res.data.newOptValue);
					}
				}
			}
		});
		return false;
	});
	jQuery('#bupImgBgRemove').click(function(){
		if(confirm(toeLangBup('Are you sure?'))) {
			jQuery.sendFormBup({
				data: {page: 'options', action: 'removeBgImg', reqType: 'ajax'}
			,	msgElID: 'bupAdminOptImgBgDefaultMsg'
			,	onSuccess: function(res) {
					if(!res.error) {
						jQuery('#bupOptBgImgPrev').attr('src', '');
					}
				}
			});
		}
		return false;
	});
	jQuery('#bupLogoSetDefault').click(function(){
		jQuery.sendFormBup({
			data: {page: 'options', action: 'setTplDefault', code: 'logo_image', reqType: 'ajax'}
		,	msgElID: 'bupAdminOptLogoDefaultMsg'
		,	onSuccess: function(res) {
				if(!res.error) {
					if(res.data.newOptValue) {
						jQuery('#bupOptLogoImgPrev').attr('src', res.data.newOptValue);
					}
				}
			}
		});
		return false;
	});
	jQuery('#bupLogoRemove').click(function(){
		if(confirm(toeLangBup('Are you sure?'))) {
			jQuery.sendFormBup({
				data: {page: 'options', action: 'removeLogoImg', reqType: 'ajax'}
			,	msgElID: 'bupAdminOptLogoDefaultMsg'
			,	onSuccess: function(res) {
					if(!res.error) {
						jQuery('#bupOptLogoImgPrev').attr('src', '');
					}
				}
			});
		}
		return false;
	});
	jQuery('#bupMsgTitleSetDefault').click(function(){
		jQuery.sendFormBup({
			data: {page: 'options', action: 'setTplDefault', code: 'msg_title_params', reqType: 'ajax'}
		,	msgElID: 'bupAdminOptMsgTitleDefaultMsg'
		,	onSuccess: function(res) {
				if(!res.error) {
					if(res.data.newOptValue) {
						if(res.data.newOptValue.msg_title_color)
							jQuery('#bupAdminTemplateOptionsForm [name="opt_values[msg_title_color]"]')
								.val( res.data.newOptValue.msg_title_color )
								.css('background-color', res.data.newOptValue.msg_title_color);
						if(res.data.newOptValue.msg_title_font)
							jQuery('#bupAdminTemplateOptionsForm [name="opt_values[msg_title_font]"]').val(res.data.newOptValue.msg_title_font);
					}
				}
			}
		});
		return false;
	});
	jQuery('#bupMsgTextSetDefault').click(function(){
		jQuery.sendFormBup({
			data: {page: 'options', action: 'setTplDefault', code: 'msg_text_params', reqType: 'ajax'}
		,	msgElID: 'bupAdminOptMsgTextDefaultMsg'
		,	onSuccess: function(res) {
				if(!res.error) {
					if(res.data.newOptValue) {
						if(res.data.newOptValue.msg_text_color)
							jQuery('#bupAdminTemplateOptionsForm [name="opt_values[msg_text_color]"]')
								.val( res.data.newOptValue.msg_text_color )
								.css('background-color', res.data.newOptValue.msg_text_color);
						if(res.data.newOptValue.msg_text_font)
							jQuery('#bupAdminTemplateOptionsForm [name="opt_values[msg_text_font]"]').val(res.data.newOptValue.msg_text_font);
					}
				}
			}
		});
		return false;
	});
	// If some changes was made in those forms and they were not saved - show message for confirnation before page reload
	var formsPreventLeave = ['bupAdminOptionsForm', 'bupAdminTemplateOptionsForm', 'bupSubAdminOptsForm', 'bupAdminSocOptionsForm'];
	jQuery('#'+ formsPreventLeave.join(', #')).find('input,select').change(function(){
		var formId = jQuery(this).parents('form:first').attr('id');
		changeAdminFormBup(formId);
	});
	jQuery('#'+ formsPreventLeave.join(', #')).find('input[type=text],textarea').keyup(function(){
		var formId = jQuery(this).parents('form:first').attr('id');
		changeAdminFormBup(formId);
	});
	jQuery('#'+ formsPreventLeave.join(', #')).submit(function(){
		if(bupAdminFormChanged.length) {
			var id = jQuery(this).attr('id');
			for(var i in bupAdminFormChanged) {
				if(bupAdminFormChanged[i] == id) {
					bupAdminFormChanged.pop(i);
				}
			}
		}
	});
});

//----------- Bup --------------

function bupChangeDestOptions(){
	jQuery('input[name=dest_opt]').change(function() {
		if (jQuery(this).attr('class') == 'bupNotPaid'){
			jQuery(this).prop('checked', false);
			jQuery('.bupMsgDest').html('This option available in the <span style="color:#900;">PRO version</span>. <a href="http://supsystic.com/plugins/backup-plugin/">More info</a>');
		} else {
			jQuery('.bupMsgDest').html('');
		}
	});
}


//-----------

function changeAdminFormBup(formId) {
	if(jQuery.inArray(formId, bupAdminFormChanged) == -1)
		bupAdminFormChanged.push(formId);
}
function changeModeOptionBup(option, ignoreChangePanelMode) {
	jQuery('.bupAdminOptionRow-template, .bupAdminOptionRow-redirect, .bupAdminOptionRow-sub_notif_end_maint').hide();
	switch(option) {
		case 'coming_soon':
			jQuery('.bupAdminOptionRow-template').show( BUP_DATA.animationSpeed );
			break;
		case 'redirect':
			jQuery('.bupAdminOptionRow-redirect').show( BUP_DATA.animationSpeed );
			break;
		case 'disable':
			jQuery('.bupAdminOptionRow-sub_notif_end_maint').show( BUP_DATA.animationSpeed );
			break;
	}
	if(!ignoreChangePanelMode) {
		// Determine should we show Comin Soon sign in wordpress admin panel or not
		if(option == 'disable' && !jQuery('#wp-admin-bar-comingsoon').hasClass('bupHidden'))
			jQuery('#wp-admin-bar-comingsoon').addClass('bupHidden');
		else if(option != 'disable' && jQuery('#wp-admin-bar-comingsoon').hasClass('bupHidden'))
			jQuery('#wp-admin-bar-comingsoon').removeClass('bupHidden');
	}
}
function setTemplateOptionBup(code) {
	jQuery.sendFormBup({
		data: {page: 'options', action: 'save', opt_values: {template: code}, code: 'template', reqType: 'ajax'}
	,	msgElID: jQuery('.bupAdminTemplateShell-'+ code).find('.bupAdminTemplateSaveMsg')
	,	onSuccess: function(res) {
			if(!res.error) {
				selectTemplateImageBup(code);
				if(res.data && res.data.new_name) {
					jQuery('.bupAdminTemplateSelectedName').html(res.data.new_name);
				}
			}
		}
	})
	return false;
}

function selectTemplateImageBup(code) {
	jQuery('.bupAdminTemplateShell').removeClass('bupAdminTemplateShellSelected');
	if(code) {
		jQuery('.bupAdminTemplateShell-'+ code).addClass('bupAdminTemplateShellSelected');
	}
}
function changeBgTypeOptionBup() {
	jQuery('#bupBgTypeStandart-selection, #bupBgTypeColor-selection, #bupBgTypeImage-selection').hide();
	if(jQuery('#bupAdminTemplateOptionsForm [name="opt_values[bg_type]"]:checked').size())
		jQuery('#'+ jQuery('#bupAdminTemplateOptionsForm [name="opt_values[bg_type]"]:checked').attr('id')+ '-selection').show( BUP_DATA.animationSpeed );
}
/* Background image manipulation functions */
function toeOptImgCompleteSubmitNewFile(file, res) {
    toeProcessAjaxResponseBup(res, 'bupOptImgkMsg');
    if(!res.error) {
        toeOptImgSetImg(res.data.imgPath);
    }
}
function toeOptImgOnSubmitNewFile() {
    jQuery('#bupOptImgkMsg').showLoaderBup();
}
function toeOptImgSetImg(src) {
	jQuery('#bupOptBgImgPrev').attr('src', src);
}
/* Logo image manipulation functions */
function toeOptLogoImgCompleteSubmitNewFile(file, res) {
    toeProcessAjaxResponseBup(res, 'bupOptLogoImgkMsg');
    if(!res.error) {
        toeOptLogoImgSetImg(res.data.imgPath);
    }
}
function toeOptLogoImgOnSubmitNewFile() {
    jQuery('#bupOptLogoImgkMsg').showLoaderBup();
}
function toeOptLogoImgSetImg(src) {
	jQuery('#bupOptLogoImgPrev').attr('src', src);
}
function bupInitCustomCheckRadio(selector) {
	if(!selector)
		selector = document;
	jQuery(selector).find('input').iCheck('destroy').iCheck({
		checkboxClass: 'icheckbox_minimal'
		,	radioClass: 'iradio_minimal'
	}).on('ifChanged', function(e){
			// for checkboxHiddenVal type, see class htmlSwr
			jQuery(this).trigger('change');
			if(jQuery(this).hasClass('cbox')) {
				var parentRow = jQuery(this).parents('.jqgrow:first');
				if(parentRow && parentRow.size()) {
					jQuery(this).parents('td:first').trigger('click');
				} else {
					var checkId = jQuery(this).attr('id');
					if(checkId && checkId != '' && strpos(checkId, 'cb_') === 0) {
						var parentTblId = str_replace(checkId, 'cb_', '');
						if(parentTblId && parentTblId != '' && jQuery('#'+ parentTblId).size()) {
							jQuery('#'+ parentTblId).find('input[type=checkbox]').iCheck('update');
						}
					}
				}
			}
		}).on('ifClicked', function(e){
			jQuery(this).trigger('click');
		});
}
/*Some items should be always on users screen*/
function bupInitStickyItem() {
	jQuery(window).scroll(function(){
		var stickiItemsSelectors = ['.ui-jqgrid-hdiv', '.supsystic-sticky']
			,	elementsUsePaddingNext = ['.ui-jqgrid-hdiv']	// For example - if we stick row - then all other should not offest to top after we will place element as fixed
			,	wpTollbarHeight = 32
			,	wndScrollTop = jQuery(window).scrollTop() + wpTollbarHeight
			,	footer = jQuery('.swrAdminFooterShell')
			,	footerHeight = footer && footer.size() ? footer.height() : 0
			,	docHeight = jQuery(document).height();
		for(var i = 0; i < stickiItemsSelectors.length; i++) {
			var element = jQuery(stickiItemsSelectors[ i ]);
			if(element && element.size()) {
				var scrollMinPos = element.offset().top
					,	prevScrollMinPos = parseInt(element.data('scrollMinPos'))
					,	useNextElementPadding = toeInArray(stickiItemsSelectors[ i ], elementsUsePaddingNext) !== -1;
				if(wndScrollTop > scrollMinPos && !element.hasClass('supsystic-sticky-active')) {
					element.addClass('supsystic-sticky-active').data('scrollMinPos', scrollMinPos).css({
						'top': wpTollbarHeight
					});
					if(useNextElementPadding) {
						element.addClass('supsystic-sticky-active-bordered');
						var nextElement = element.next();
						if(nextElement && nextElement.size()) {
							nextElement.data('prevPaddingTop', nextElement.css('padding-top'));
							nextElement.css({
								'padding-top': element.height()
							});
						}
					}
				} else if(!isNaN(prevScrollMinPos) && wndScrollTop <= prevScrollMinPos) {
					element.removeClass('supsystic-sticky-active').data('scrollMinPos', 0).css({
						'top': 0
					});
					if(useNextElementPadding) {
						element.removeClass('supsystic-sticky-active-bordered');
						var nextElement = element.next();
						if(nextElement && nextElement.size()) {
							var nextPrevPaddingTop = parseInt(nextElement.data('prevPaddingTop'));
							if(isNaN(nextPrevPaddingTop))
								nextPrevPaddingTop = 0;
							nextElement.css({
								'padding-top': nextPrevPaddingTop
							});
						}
					}
				} else {
					if(element.hasClass('supsystic-sticky-active') && footerHeight) {
						var elementHeight = element.height()
							,	heightCorrection = 32
							,	topDiff = docHeight - footerHeight - (wndScrollTop + elementHeight + heightCorrection);
						//console.log(topDiff);
						if(topDiff < 0) {
							//console.log(topDiff, elementTop + topDiff);
							element.css({
								'top': wpTollbarHeight + topDiff
							});
						} else {
							element.css({
								'top': wpTollbarHeight
							});
						}
					}
				}
			}
		}
	});
}
function bupCheckUpdate(checkbox) {
	jQuery(checkbox).iCheck('update');
}
function bupCheckUpdateArea(selector) {
	if(typeof(selector) === 'undefined')
		selector = document;
	jQuery(selector).find('input[type=checkbox]').iCheck('update');
}

function isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
	return pattern.test(emailAddress);
};