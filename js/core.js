if(typeof(BUP_DATA) == 'undefined')
	var BUP_DATA = {};
if(isNumber(BUP_DATA.animationSpeed)) 
    BUP_DATA.animationSpeed = parseInt(BUP_DATA.animationSpeed);
else if(jQuery.inArray(BUP_DATA.animationSpeed, ['fast', 'slow']) == -1)
    BUP_DATA.animationSpeed = 'fast';
BUP_DATA.showSubscreenOnCenter = parseInt(BUP_DATA.showSubscreenOnCenter);
var sdLoaderImgBup = '<img src="'+ BUP_DATA.loader+ '" />';

jQuery.fn.showLoaderBup = function() {
    jQuery(this).html( sdLoaderImgBup );
}
jQuery.fn.appendLoaderBup = function() {
    jQuery(this).append( sdLoaderImgBup );
}

jQuery.sendFormBup = function(params) {
	// Any html element can be used here
	return jQuery('<br />').sendFormBup(params);
}
/**
 * Send form or just data to server by ajax and route response
 * @param string params.fid form element ID, if empty - current element will be used
 * @param string params.msgElID element ID to store result messages, if empty - element with ID "msg" will be used. Can be "noMessages" to not use this feature
 * @param function params.onSuccess funstion to do after success receive response. Be advised - "success" means that ajax response will be success
 * @param array params.data data to send if You don't want to send Your form data, will be set instead of all form data
 * @param array params.appendData data to append to sending request. In contrast to params.data will not erase form data
 * @param string params.inputsWraper element ID for inputs wraper, will be used if it is not a form
 * @param string params.clearMsg clear msg element after receive data, if is number - will use it to set time for clearing, else - if true - will clear msg element after 5 seconds
 */
jQuery.fn.sendFormBup = function(params) {
    var form = null;
    if(!params)
        params = {fid: false, msgElID: false, onSuccess: false};

    if(params.fid)
        form = jQuery('#'+ fid);
    else
        form = jQuery(this);
    
    /* This method can be used not only from form data sending, it can be used just to send some data and fill in response msg or errors*/
    var sentFromForm = (jQuery(form).tagName() == 'FORM');
    var data = new Array();
    if(params.data)
        data = params.data;
    else if(sentFromForm)
        data = jQuery(form).serialize();
    
    if(params.appendData) {
		var dataIsString = typeof(data) == 'string';
		var addStrData = [];
        for(var i in params.appendData) {
			if(dataIsString) {
				addStrData.push(i+ '='+ params.appendData[i]);
			} else
            data[i] = params.appendData[i];
        }
		if(dataIsString)
			data += '&'+ addStrData.join('&');
    }
    var msgEl = null;
    if(params.msgElID) {
        if(params.msgElID == 'noMessages')
            msgEl = false;
        else if(typeof(params.msgElID) == 'object')
           msgEl = params.msgElID;
       else
            msgEl = jQuery('#'+ params.msgElID);
    } else
        msgEl = jQuery('#msg');
	if(typeof(params.inputsWraper) == 'string') {
		form = jQuery('#'+ params.inputsWraper);
		sentFromForm = true;
	}
	if(sentFromForm && form) {
        jQuery(form).find('*').removeClass('bupInputError');
    }
	if(msgEl) {
		jQuery(msgEl).removeClass('bupSuccessMsg')
			.removeClass('bupErrorMsg')
			.showLoaderBup();
	}
    var url = '';
	if(typeof(params.url) != 'undefined')
		url = params.url;
    else if(typeof(ajaxurl) == 'undefined')
        url = BUP_DATA.ajaxurl;
    else
        url = ajaxurl;
    
    jQuery('.bupErrorForField').hide(BUP_DATA.animationSpeed);
	var dataType = params.dataType ? params.dataType : 'json';
	// Set plugin orientation
	if(typeof(data) == 'string')
		data += '&pl='+ BUP_DATA.BUP_CODE;
	else
		data['pl'] = BUP_DATA.BUP_CODE;
	
    jQuery.ajax({
        url: url,
        data: data,
        type: 'POST',
        dataType: dataType,
        success: function(res) {
            toeProcessAjaxResponseBup(res, msgEl, form, sentFromForm, params);
			if(params.clearMsg) {
				setTimeout(function(){
					jQuery(msgEl).animateClear();
				}, typeof(params.clearMsg) == 'boolean' ? 5000 : params.clearMsg);
			}
        }
    });
}

/**
 * Hide content in element and then clear it
 */
jQuery.fn.animateClear = function() {
	var newContent = jQuery('<span>'+ jQuery(this).html()+ '</span>');
	jQuery(this).html( newContent );
	jQuery(newContent).hide(BUP_DATA.animationSpeed, function(){
		jQuery(newContent).remove();
	});
}
/**
 * Hide content in element and then remove it
 */
jQuery.fn.animateRemove = function(animationSpeed) {
	animationSpeed = animationSpeed == undefined ? BUP_DATA.animationSpeed : animationSpeed;
	jQuery(this).hide(animationSpeed, function(){
		jQuery(this).remove();
	});
}

function toeProcessAjaxResponseBup(res, msgEl, form, sentFromForm, params) {
    if(typeof(params) == 'undefined')
        params = {};
    if(typeof(msgEl) == 'string')
        msgEl = jQuery('#'+ msgEl);
    if(msgEl)
        jQuery(msgEl).html('');
    /*if(sentFromForm) {
        jQuery(form).find('*').removeClass('bupInputError');
    }*/
    if(typeof(res) == 'object') {
        if(res.error) {
            if(msgEl) {
                jQuery(msgEl).removeClass('bupSuccessMsg')
					.addClass('bupErrorMsg');
            }
            for(var name in res.errors) {
                if(sentFromForm) {
                    jQuery(form).find('[name*="'+ name+ '"]').addClass('bupInputError');
                }
                if(jQuery('.bupErrorForField.toe_'+ nameToClassId(name)+ '').exists())
                    jQuery('.bupErrorForField.toe_'+ nameToClassId(name)+ '').show().html(res.errors[name]);
                else if(msgEl)
                    jQuery(msgEl).append(res.errors[name]).append('<br />');
            }
        } else if(res.messages.length) {
            if(msgEl) {
                jQuery(msgEl).removeClass('bupErrorMsg')
					.addClass('bupSuccessMsg');
                for(var i in res.messages) {
                    jQuery(msgEl).append(res.messages[i]).append('<br />');
                }
            }
        }
    }
    if(params.onSuccess && typeof(params.onSuccess) == 'function') {
        params.onSuccess(res);
    }
}

function getDialogElementBup() {
	return jQuery('<div/>').appendTo(jQuery('body'));
}

function toeOptionBup(key) {
	if(BUP_DATA.options && BUP_DATA.options[ key ] && BUP_DATA.options[ key ].value)
		return BUP_DATA.options[ key ].value;
	return false;
}
function toeLangBup(key) {
	if(BUP_DATA.siteLang && BUP_DATA.siteLang[key])
		return BUP_DATA.siteLang[key];
	return key;
}
function toePagesBup(key) {
	if(typeof(BUP_DATA) != 'undefined' && BUP_DATA[key])
		return BUP_DATA[key];
	return false;;
}
/**
 * This function will help us not to hide desc right now, but wait - maybe user will want to select some text or click on some link in it.
 */
function toeOptTimeoutHideDescriptionBup() {
	jQuery('#bupOptDescription').removeAttr('toeFixTip');
	setTimeout(function(){
		if(!jQuery('#bupOptDescription').attr('toeFixTip'))
			toeOptHideDescriptionBup();
	}, 500);
}
/**
 * Show description for options
 */
function toeOptShowDescriptionBup(description, x, y, moveToLeft) {
    if(typeof(description) != 'undefined' && description != '') {
        if(!jQuery('#bupOptDescription').size()) {
            jQuery('body').append('<div id="bupOptDescription"></div>');
        }
		if(moveToLeft)
			jQuery('#bupOptDescription').css('right', jQuery(window).width() - (x - 10));	// Show it on left side of target
		else
			jQuery('#bupOptDescription').css('left', x + 10);
        jQuery('#bupOptDescription').css('top', y);
        jQuery('#bupOptDescription').show(200);
        jQuery('#bupOptDescription').html(description);
    }
}
/**
 * Hide description for options
 */
function toeOptHideDescriptionBup() {
	jQuery('#bupOptDescription').removeAttr('toeFixTip');
    jQuery('#bupOptDescription').hide(200);
}