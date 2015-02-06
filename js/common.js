jQuery.fn.nextInArray = function(element) {
    var nextId = 0;
    for(var i = 0; i < this.length; i++) {
        if(this[i] == element) {
            nextId = i + 1;
            break;
        }
    }
    if(nextId > this.length-1)
        nextId = 0;
    return this[nextId];
}
jQuery.fn.clearForm = function() {
	return this.each(function() {
		var type = this.type, tag = this.tagName.toLowerCase();
		if (tag == 'form')
			return jQuery(':input', this).clearForm();
		if (type == 'text' || type == 'password' || tag == 'textarea')
			this.value = '';
		else if (type == 'checkbox' || type == 'radio')
			this.checked = false;
		else if (tag == 'select') 
			this.selectedIndex = -1;
	});
}
jQuery.fn.tagName = function() {
    return this.get(0).tagName;
}
jQuery.fn.exists = function(){
    return (jQuery(this).size() > 0 ? true : false);
}
function isNumber(val) {
    return /^\d+/.test(val);
}
jQuery.fn.serializeAnything = function(addData) {
    var toReturn    = [];
    var els         = jQuery(this).find(':input').get();
    jQuery.each(els, function() {
        if (this.name && !this.disabled && (this.checked || /select|textarea/i.test(this.nodeName) || /text|hidden|password/i.test(this.type))) {
            var val = jQuery(this).val();
            toReturn.push( encodeURIComponent(this.name) + "=" + encodeURIComponent( val ) );
        }
    });
    if(typeof(addData) != 'undefined') {
        for(var key in addData)
            toReturn.push(key + "=" + addData[key]);
    }
    return toReturn.join("&").replace(/%20/g, "+");
}
 

function str_replace(haystack, needle, replacement) { 
	var temp = haystack.split(needle); 
	return temp.join(replacement); 
}
/**
 * @see php html::nameToClassId($name) method
 **/
function nameToClassId(name) {
    return str_replace(
        str_replace(name, ']', ''), 
            '[', ''
    );
}
function strpos( haystack, needle, offset){
    var i = haystack.indexOf( needle, offset ); // returns -1
    return i >= 0 ? i : false;
}
function extend(Child, Parent) {
    var F = function() { };
    F.prototype = Parent.prototype;
    Child.prototype = new F();
    Child.prototype.constructor = Child;
    Child.superclass = Parent.prototype;
}
function toeRedirect(url) {
    document.location.href = url;
}
function toeReload() {
    document.location.reload();
}
jQuery.fn.toeRebuildSelect = function(data, useIdAsValue, val) {
    if(jQuery(this).tagName() == 'SELECT' && typeof(data) == 'object') {
        if(jQuery(data).size() > 0) {
            if(typeof(val) == 'undefined')
                val = false;
            if(jQuery(this).children('option').length) {
                jQuery(this).children('option').remove();
            }
            if(typeof(useIdAsValue) == 'undefined')
                useIdAsValue = false;
            var selected = '';
            for(var id in data) {
                selected = '';
                if(val && ((useIdAsValue && id == val) || (data[id] == val)))
                    selected = 'selected';
                jQuery(this).append('<option value="'+ (useIdAsValue ? id : data[id])+ '" '+ selected+ '>'+ data[id]+ '</option>');
            }
        }
    }
}
/**
 * We will not use just jQUery.inArray because it is work incorrect for objects
 * @return mixed - key that was found element or -1 if not
 */
function toeInArray(needle, haystack) {
    if(typeof(haystack) == 'object') {
        for(var k in haystack) {
            if(haystack[ k ] == needle)
                return k;
        }
    } else if(typeof(haystack) == 'array') {
        return jQuery.inArray(needle, haystack);
    }
    return -1;
}
jQuery.fn.setReadonly = function() {
	jQuery(this).addClass('toeReadonly').attr('readonly', 'readonly');
}
jQuery.fn.unsetReadonly = function() {
	jQuery(this).removeClass('toeReadonly').removeAttr('readonly', 'readonly');
}
jQuery.fn.getClassId = function(pref, test) {
	var classId = jQuery(this).attr('class');
	classId = classId.substr( strpos(classId, pref+ '_') );
	if(strpos(classId, ' '))
		classId = classId.substr( 0, strpos(classId, ' ') );
	classId = classId.split('_');
	classId = classId[1];
	return classId;
}
function toeTextIncDec(textFieldId, inc) {
	var value = parseInt(jQuery('#'+ textFieldId).val());
	if(isNaN(value))
		value = 0;
	if(!(inc < 0 && value < 1)) {
		value += inc;
	}
	jQuery('#'+ textFieldId).val(value);
}

/**
 * Make first letter of string in upper case
 * @param str string - string to convert
 * @return string converted string - first letter in upper case
 */
function toeStrFirstUp(str) {
	str += '';
	var f = str.charAt(0).toUpperCase();
	return f + str.substr(1);
}
function parseStr (str, array) {
  // http://kevin.vanzonneveld.net
  // +   original by: Cagri Ekin
  // +   improved by: Michael White (http://getsprink.com)
  // +    tweaked by: Jack
  // +   bugfixed by: Onno Marsman
  // +   reimplemented by: stag019
  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
  // +   bugfixed by: stag019
  // +   input by: Dreamer
  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
  // +   bugfixed by: MIO_KODUKI (http://mio-koduki.blogspot.com/)
  // +   input by: Zaide (http://zaidesthings.com/)
  // +   input by: David Pesta (http://davidpesta.com/)
  // +   input by: jeicquest
  // +   improved by: Brett Zamir (http://brett-zamir.me)
  // %        note 1: When no argument is specified, will put variables in global scope.
  // %        note 1: When a particular argument has been passed, and the returned value is different parse_str of PHP. For example, a=b=c&d====c
  // *     example 1: var arr = {};
  // *     example 1: parse_str('first=foo&second=bar', arr);
  // *     results 1: arr == { first: 'foo', second: 'bar' }
  // *     example 2: var arr = {};
  // *     example 2: parse_str('str_a=Jack+and+Jill+didn%27t+see+the+well.', arr);
  // *     results 2: arr == { str_a: "Jack and Jill didn't see the well." }
  // *     example 3: var abc = {3:'a'};
  // *     example 3: parse_str('abc[a][b]["c"]=def&abc[q]=t+5');
  // *     results 3: JSON.stringify(abc) === '{"3":"a","a":{"b":{"c":"def"}},"q":"t 5"}';
	var strArr = String(str).replace(/^&/, '').replace(/&$/, '').split('&'),
	sal = strArr.length,
	i, j, ct, p, lastObj, obj, lastIter, undef, chr, tmp, key, value,
	postLeftBracketPos, keys, keysLen,
	fixStr = function (str) {
		return decodeURIComponent(str.replace(/\+/g, '%20'));
	};
	// Comented by Alexey Bolotov
	/*
	if (!array) {
	array = this.window;
	}*/
	if (!array) {
		array = {};
	}

	for (i = 0; i < sal; i++) {
		tmp = strArr[i].split('=');
		key = fixStr(tmp[0]);
		value = (tmp.length < 2) ? '' : fixStr(tmp[1]);

		while (key.charAt(0) === ' ') {
			key = key.slice(1);
		}
		if (key.indexOf('\x00') > -1) {
			key = key.slice(0, key.indexOf('\x00'));
		}
		if (key && key.charAt(0) !== '[') {
			keys = [];
			postLeftBracketPos = 0;
			for (j = 0; j < key.length; j++) {
				if (key.charAt(j) === '[' && !postLeftBracketPos) {
					postLeftBracketPos = j + 1;
				} else if (key.charAt(j) === ']') {
					if (postLeftBracketPos) {
						if (!keys.length) {
							keys.push(key.slice(0, postLeftBracketPos - 1));
						}
						keys.push(key.substr(postLeftBracketPos, j - postLeftBracketPos));
						postLeftBracketPos = 0;
						if (key.charAt(j + 1) !== '[') {
							break;
						}
					}
				}
			}
			if (!keys.length) {
				keys = [key];
			}
			for (j = 0; j < keys[0].length; j++) {
				chr = keys[0].charAt(j);
				if (chr === ' ' || chr === '.' || chr === '[') {
					keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1);
				}
				if (chr === '[') {
					break;
				}
			}

			obj = array;
			for (j = 0, keysLen = keys.length; j < keysLen; j++) {
				key = keys[j].replace(/^['"]/, '').replace(/['"]$/, '');
				lastIter = j !== keys.length - 1;
				lastObj = obj;
				if ((key !== '' && key !== ' ') || j === 0) {
					if (obj[key] === undef) {
						obj[key] = {};
					}
					obj = obj[key];
				} else { // To insert new dimension
					ct = -1;
					for (p in obj) {
						if (obj.hasOwnProperty(p)) {
							if (+p > ct && p.match(/^\d+$/g)) {
								ct = +p;
							}
						}
					}
					key = ct + 1;
				}
			}
			lastObj[key] = value;
		}
	}
	return array;
}

function toeListableBup(params) {
	this.params			= jQuery.extend({}, params);
	this.table			= jQuery(this.params.table);
	this.paging			= jQuery(this.params.paging);
	this.perPage		= this.params.perPage;
	this.list			= this.params.list;
	this.count			= this.params.count;
	this.page			= this.params.page;
	this.pagingCallback	= this.params.pagingCallback;
	var self			= this;
	
	this.draw = function(list, count) {
		this.table.find('tr').not('.bupExample, .bupTblHeader').remove();
		var exampleRow = this.table.find('.bupExample');
		for(var i in list) {
			var newRow = exampleRow.clone();
			for(var key in list[i]) {
				var element = newRow.find('.'+ key);
				if(element.size()) {
					var valueTo = element.attr('valueTo');
					if(valueTo) {
						var newValue = list[i][key];
						var prevValue = element.attr(valueTo);
						if(prevValue)
							newValue = prevValue+ ' '+ newValue;
						element.attr(valueTo, newValue);
					} else
						element.html(list[i][key]);
				}
			}
			newRow.removeClass('bupExample').show();
			this.table.append(newRow);
		}
		if(this.paging) {
			this.paging.html('');
			if(count && count > list.length && this.perPage) {
				for(var i = 1; i <= Math.ceil(count/this.perPage); i++) {
					var newPageId = i-1
					,	newElement = (newPageId == this.page) ? jQuery('<b/>') : jQuery('<a/>');
					if(newPageId != this.page) {
						newElement.attr('href', '#'+ newPageId)
						.click(function(){
							if(self.pagingCallback && typeof(self.pagingCallback) == 'function') {
								self.pagingCallback(parseInt(jQuery(this).attr('href').replace('#', '')));
								return false;
							}
						});
					}
					newElement.addClass('toePagingElement').html(i);
					this.paging.append(newElement);
				}
			}
		}
	}
	
	if(this.list)
		this.draw(this.list, this.count);
}
function bupRedirect(url) {
    document.location.href = url;
}