<?php
class htmlBup {
    static public $categoriesOptions = array();
    static public $productsOptions = array();
    static public function block($name, $params= array('attrs' => '', 'value' => '')){
        $output .= '<p class="toe_'. self::nameToClassId($name). '">'.$params['value'].'</p>';
        //$output .= self::hidden($name, $params);
        return $output;
    }
    static public function nameToClassId($name) {
        return str_replace(array('[', ']'), '', $name);
    }
    static public function textarea($name, $params = array('attrs' => '', 'value' => '', 'rows' => 3, 'cols' => 50)) {
		$params['attrs'] = isset($params['attrs']) ? $params['attrs'] : '';
        $params['rows'] = isset($params['rows']) ? $params['rows'] : 3;
        $params['cols'] = isset($params['cols']) ? $params['cols'] : 50;
		if(isset($params['placeholder'])) {
			$params['attrs'] .= ' placeholder="'. $params['placeholder']. '"';
		}
		if(isset($params['required'])) {
			$params['attrs'] .= ' required ';
		}
        return '<textarea name="'.$name.'" '.$params['attrs'].' rows="'.$params['rows'].'" cols="'.$params['cols'].'">'.
                (isset($params['value']) ? $params['value'] : '').
                '</textarea>';
    }
    static public function input($name, $params = array('attrs' => '', 'type' => 'text', 'value' => '')) {
		$params['attrs'] = isset($params['attrs']) ? $params['attrs'] : '';
		if(isset($params['required']) && $params['required']) {
			$params['attrs'] .= ' required ';	// HTML5 "required" validation attr
		}
		if(isset($params['placeholder']) && $params['placeholder']) {
			$params['attrs'] .= ' placeholder="'. $params['placeholder']. '"';	// HTML5 "required" validation attr
		}
		$params['type'] = isset($params['type']) ? $params['type'] : '';
		$params['value'] = isset($params['value']) ? $params['value'] : '';
        return '<input type="'.$params['type'].'" name="'.$name.'" value="'.$params['value'].'" '.$params['attrs'].' />';
    }
	static public function email($name, $params = array('attrs' => '', 'value' => '')) {
        $params['type'] = 'email';
        return self::input($name, $params);
    }
    static public function text($name, $params = array('attrs' => '', 'value' => '')) {
        $params['type'] = 'text';
        return self::input($name, $params);
    }
	static public function reset($name, $params = array('attrs' => '', 'value' => '')) {
        $params['type'] = 'reset';
        return self::input($name, $params);
    }
    static public function password($name, $params = array('attrs' => '', 'value' => '')) {
        $params['type'] = 'password';
        return self::input($name, $params);
    }
    static public function hidden($name, $params = array('attrs' => '', 'value' => '')) {
        $params['type'] = 'hidden';
        return self::input($name, $params);
    }
    static public function checkbox($name, $params = array('attrs' => '', 'value' => '', 'checked' => '')) {
        $params = array_merge(
            array('attrs' => '', 'value' => '', 'checked' => ''),
            $params
        );

        $params['type'] = 'checkbox';
        if($params['checked'])
            $params['checked'] = 'checked';
        if($params['value'] === NULL)
            $params['value'] = 1;
        $params['attrs'] .= ' '.$params['checked'];
        return self::input($name, $params);
    }
    static public function checkboxlist($name, $params = array('options' => array(), 'attrs' => '', 'checked' => '', 'delim' => '<br />', 'usetable' => 5), $delim = '<br />') {
        $out = '';
        if(!strpos($name, '[]')) {
            $name .= '[]';
        }
        $i = 0;
        if($params['options']) {
            if(!isset($params['delim']))
                $params['delim'] = $delim;
            if($params['usetable']) $out .= '<table><tr>';
            foreach($params['options'] as $v) {
                if($params['usetable']) {
                    if($i != 0 && $i%$params['usetable'] == 0) $out .= '</tr><tr>';
                    $out .= '<td>';
                }
                $out .= self::checkbox($name, array(
                    'attrs' => $params['attrs'],
                    'value' => $v['id'],
                    'checked' => $v['checked']
                ));
                $out .= '&nbsp;'. $v['text']. $params['delim'];
                if($params['usetable']) $out .= '</td>';
                $i++;
            }
            if($params['usetable']) $out .= '</tr></table>';
        }
        return $out;
    }
    static public function datepicker($name, $params = array('attrs' => '', 'value' => '')) {
        if(isset($params['id']) && !empty($params['id']))
            $id = $params['id'];
        else
            $id = self::nameToClassId($name);
        return self::input($name, array(
                'attrs' => 'id="'.$id.'" '.$params['attrs'],
                'type' => 'text',
                'value' => $params['value']
        )).'<script type="text/javascript">
            // <!--
                jQuery(document).ready(function(){
                    jQuery("#'.$id.'").datepicker({dateFormat: "'.BUP_DATE_FORMAT_JS.'",
                        changeYear: true,
                        yearRange: "1905:'.(date('Y')+5).'"});
                });
            // -->
        </script>';
    }
    static public function submit($name, $params = array('attrs' => '', 'value' => '')) {
        $params['type'] = 'submit';
        return self::input($name, $params);
    }
    static public function img($src, $usePlugPath = 1, $params = array('width' => '', 'height' => '', 'attrs' => '')) {
        if($usePlugPath) $src = BUP_IMG_PATH. $src;
        return '<img src="'.$src.'" '
				.(isset($params['width']) ? 'width="'.$params['width'].'"' : '')
				.' '
				.(isset($params['height']) ? 'height="'.$params['height'].'"' : '')
				.' '
				.(isset($params['attrs']) ? $params['attrs'] : '').' />';
    }
    static public function selectbox($name, $params = array('attrs' => '', 'options' => array(), 'value' => '')) {
        $out = '';
        $out .= '<select name="'.$name.'" '.$params['attrs'].'>';
        if(!empty($params['options'])) {
            foreach($params['options'] as $k => $v) {
                $selected = (isset($params['value']) && $k == $params['value'] ? 'selected="true"' : '');
                $out .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
        }
        $out .= '</select>';
        return $out;
    }
    static public function selectlist($name, $params = array('attrs'=>'', 'size'=> 5, 'options' => array(), 'value' => '')) {
        $out = '';
        if(!strpos($name, '[]'))
            $name .= '[]';
        if (!isset($params['size']) || !is_numeric($params['size']) || $params['size'] == '') {
            $params['size'] = 5;
        }
        $out .= '<select multiple="multiple" size="'.$params['size'].'" name="'.$name.'" '.$params['attrs'].'>';
        if(!empty($params['options'])) {
            foreach($params['options'] as $k => $v) {
                $selected = (in_array($k,(array)$params['value']) ? 'selected="true"' : '');
                $out .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
        }
        $out .= '</select>';
        return $out;
    }
    static public function ajaxfile($name, $params = array('url' => '', 'value' => '', 'fid' => '', 'buttonName' => '')) {
        $out = '';
        if(strpos($params['url'], 'pl='. BUP_CODE) === false)
			$params['url'] = uriBup::_(array('baseUrl' => $params['url'], 'pl' => BUP_CODE));
        $out .= self::button(array('value' => __( empty($params['buttonName']) ? 'Upload' :  $params['buttonName'] , BUP_LANG_CODE), 'attrs' => 'id="toeUploadbut_'.$name.'" class="button button-large"'));
        $display = (empty($params['value']) ? 'style="display: none;"' : '');
        if($params['preview'])
            $out .= self::img($params['value'], 0, array('attrs' => 'id="prev_'.$name.'" '.$display.' class="previewpicture"'));
        $out .= '<span class="delete_option" id="delete_'.$name.'" '.$display.'></span>';
        $out .= '<script type="text/javascript">// <!--
                jQuery(document).ready(function(){
                    new AjaxUpload("#toeUploadbut_'.$name.'", {
                        action: "'.$params['url'].'",
                        name: "'. $name. '" '.
                        (empty($params['data']) ? '' : ',  data: '. $params['data']. '').
                        (empty($params['autoSubmit']) ? '' : ',  autoSubmit: "'. $params['autoSubmit']. '"').
                        (empty($params['responseType']) ? '' : ',  responseType: "'. $params['responseType']. '"').
                        (empty($params['onChange']) ? '' : ',  onChange: '. $params['onChange']. '').
                        (empty($params['onSubmit']) ? '' : ',  onSubmit: '. $params['onSubmit']. '').
                        (empty($params['onComplete']) ? '' : ',  onComplete: '. $params['onComplete']. '').
                    '});
                });
            // --></script>';
        return $out;
    }
    static public function button($params = array('attrs' => '', 'value' => '')) {
        return '<button '.$params['attrs'].'>'.$params['value'].'</button>';
    }
    static public function inputButton($params = array('attrs' => '', 'value' => '')) {
		if(!is_array($params))
			$params = array();
		$params['type'] = 'button';
        return self::input('', $params);
    }
    static public function radiobuttons($name, $params = array('attrs' => '', 'options' => array(), 'value' => '', '')) {
        $out = '';
		if(isset($params['options']) && is_array($params['options']) && !empty($params['options'])) {
			$params['labeled'] = isset($params['labeled']) ? $params['labeled'] : false;
			$params['attrs'] = isset($params['attrs']) ? $params['attrs'] : '';
			$params['mo_br'] = isset($params['mo_br']) ? $params['mo_br'] : false;
			foreach($params['options'] as $key => $val) {
				$id = self::nameToClassId($key). '_'. mt_rand(1, 999999);
				$attrs = $params['attrs'];
				$attrs .= ' id="'. $id. '"';
				$checked =(isset($params['value']) && $key == $params['value']) ? 'checked' : '';
				$label = $params['labeled'] ? '<label for="'. $id. '">'. $val. '</label>' : $val;
				$out .= self::input($name, array('attrs' => $attrs.' '.$checked, 'type' => 'radio', 'value' => $key)). ' '. $label. ($params['mo_br'] ? '' : '<br />');
			}
		}
        return $out;
    }
    static public function radiobutton($name, $params = array('attrs' => '', 'value' => '', 'checked' => '')) {
        $params['type'] = 'radio';
		$params['attrs'] = isset($params['attrs']) ? $params['attrs'] : '';
        if(isset($params['checked']) && $params['checked'])
            $params['attrs'] .= ' checked';
        return self::input($name, $params);
    }
    static public function nonajaxautocompleate($name, $params = array('attrs' => '', 'options' => array())) {
        if(empty($params['options'])) return false;
        $out = '';
        $jsArray = array();
        $oneItem = '<div id="%%ID%%"><div class="ac_list_item"><input type="hidden" name="'.$name.'[]" value="%%ID%%" />%%VAL%%</div><div class="close" onclick="removeAcOpt(%%ID%%)"></div><div class="br"></div></div>';
        $tID = $name. '_ac';
        $out .= self::text($tID. '_ac', array('attrs' => 'id="'.$tID.'"'));
        $out .= '<div id="'.$name.'_wraper">';
        foreach($params['options'] as $opt) {
            $jsArray[$opt['id']] = $opt['text'];
            if(isset($opt['checked']) && $opt['checked'] == 'checked') {
                $out .= str_replace(array('%%ID%%', '%%VAL%%'), array($opt['id'], $opt['text']), $oneItem);
            }
        }
        $out .= '</div>';
        $out .= '<script type="text/javascript">
                var ac_values_'.$name.' = '.json_encode(array_values($jsArray)).';
                var ac_keys_'.$name.' = '.json_encode(array_keys($jsArray)).';
                jQuery(document).ready(function(){
                    jQuery("#'.$tID.'").autocomplete(ac_values_'.$name.', {
                        autoFill: false,
                        mustMatch: false,
                        matchContains: false
                    }).result(function(a, b, c){
                        var keyID = jQuery.inArray(c, ac_values_'.$name.');
                        if(keyID != -1) {
                            addAcOpt(ac_keys_'.$name.'[keyID], c, "'.$name.'");
                        }
                    });
                });
        </script>';
        return $out;
    }
    static public function formStart($name, $params = array('action' => '', 'method' => 'GET', 'attrs' => '', 'hideMethodInside' => false)) {
        $params['attrs'] = isset($params['attrs']) ? $params['attrs'] : '';
        $params['action'] = isset($params['action']) ? $params['action'] : '';
        $params['method'] = isset($params['method']) ? $params['method'] : 'GET';
        if(isset($params['hideMethodInside']) && $params['hideMethodInside']) {
            return '<form name="'. $name. '" action="'. $params['action']. '" method="'. $params['method']. '" '. $params['attrs']. '>'.
                self::hidden('method', array('value' => $params['method']));
        } else {
            return '<form name="'. $name. '" action="'. $params['action']. '" method="'. $params['method']. '" '. $params['attrs']. '>';
        }
    }
    static public function formEnd() {
        return '</form>';
    }
    static public function statesInput($name, $params = array('value' => '', 'attrs' => '', 'notSelected' => true, 'id' => '', 'selectHtml' => '')) {
        if(empty($params['selectHtml']) || !method_exists(html, $params['selectHtml']))
            return false;

        $params['notSelected'] = isset($params['notSelected']) ? $params['notSelected'] : true;
        $states = fieldAdapterBup::getStates($params['notSelected']);

        foreach($states as $sid => $s) {
            $params['options'][$sid] = $s['name'];
        }
        $idSelect = '';
        $idText = '';
        $id = '';
        if(!empty($params['id'])) {
            $id = $params['id'];
        } else {
            $id = self::nameToClassId($name);
        }
        $paramsText = $paramsSelect = $params;
        $paramsText['attrs'] .= 'id = "'. $id. '_text"';
        $paramsSelect['attrs'] .= 'id = "'. $id. '_select"';
        $res = '';
        $res .= self::$params['selectHtml']($name, $paramsSelect);
        $res .= self::text($name, $paramsText);
        if(empty($params['doNotAddJs'])) {
            $res .= '<script type="text/javascript">
                // <!--
                if(!toeStates.length)
                    toeStates = '. utilsBup::jsonEncode($states). ';
                toeStatesObjects["'. $id. '"] = new toeStatesSelect("'. $id. '");
                // -->
            </script>';
        }
        return $res;
    }
    static public function statesList($name, $params = array('value' => '', 'attrs' => '', 'notSelected' => true, 'id' => '')) {
        $params['selectHtml'] = 'selectbox';
        return self::statesInput($name, $params);
    }
    static public function statesListMultiple($name, $params = array('value' => '', 'attrs' => '', 'notSelected' => true, 'id' => '')) {
        if(!empty($params['value'])) {
            if(is_string($params['value'])) {
                if(strpos($params['value'], ','))
                    $params['value'] = array_map('trim', explode(',', $params['value']));
                else
                    $params['value'] = array(trim($params['value']));
            }
        }
        $params['selectHtml'] = 'selectlist';
        return self::statesInput($name, $params);
    }
    static public function countryList($name, $params = array('value' => '', 'attrs' => '', 'notSelected' => true)) {
        $params['notSelected'] = isset($params['notSelected']) ? $params['notSelected'] : true;
        $params['options'] = fieldAdapterBup::getCountries($params['notSelected']);
        $params['attrs'] .= ' type="country"';
        return self::selectbox($name, $params);
    }
    static public function countryListMultiple($name, $params = array('value' => '', 'attrs' => '', 'notSelected' => true)) {
        if(!empty($params['value'])) {
            if(is_string($params['value'])) {
                if(strpos($params['value'], ','))
                    $params['value'] = array_map('trim', explode(',', $params['value']));
                else
                    $params['value'] = array(trim($params['value']));
            }
        }
        $params['notSelected'] = isset($params['notSelected']) ? $params['notSelected'] : true;
        $params['options'] = fieldAdapterBup::getCountries($params['notSelected']);
        $params['attrs'] .= ' type="country"';
        return self::selectlist($name, $params);
    }
    static public function textFieldsDynamicTable($name, $params = array('value' => '', 'attrs' => '', 'options' => array())) {
        $res = '';
        if(empty($params['options']))
            $params['options'] = array(0 => array('label' => ''));
        if(!empty($params['options'])) {
            $pattern = array();
            foreach($params['options'] as $key => $p) {
                $pattern[$key] = htmlBup::text($name. '[]['. $key. ']');
            }
            $countOptions = count($params['options']);
            $remove = '<a href="#" onclick="toeRemoveTextFieldsDynamicTable(this); return false;">remove</a>';
            $add = '<a href="#" onclick="toeAddTextFieldsDynamicTable(this, '. $countOptions. '); return false;">add</a>';

            $res = '<div class="toeTextFieldsDynamicTable">';
            if(empty($params['value']))
                $params['value'] = array();
            elseif(!is_array($params['value'])) {
                $params['value'] = utilsBup::jsonDecode($params['value']);
                //$params['value'] = $params['value'][0];
            }
            $i = 0;
            do {
                $res .= '<div class="toeTextFieldDynamicRow">';
                foreach($params['options'] as $key => $p) {
                    switch($countOptions) {
                        case 1:
                            if(isset($params['value'][$i]))
                                $value = is_array($params['value'][$i]) ? $params['value'][$i][$key] : $params['value'][$i];
                            else
                                $value = '';
                            break;
                        case 2:
                        default:
                            $value = isset($params['value'][$i][$key]) ? $params['value'][$i][$key] : '';
                            break;
                    }
                    $paramsForText = array(
                        'value' => $value,
                    );
                    $res .= __($p['label'], BUP_LANG_CODE). htmlBup::text($name. '['. $i. ']['. $key. ']', $paramsForText);
                }
                $res .= $remove. '</div>';
                $i++;
            } while($i < count($params['value']));
            $res .= $add;
            $res .= '</div>';
        }
        return $res;
    }
    static public function categorySelectlist($name, $params = array('attrs'=>'', 'size'=> 5, 'value' => '')) {
        self::_loadCategoriesOptions();
        if(self::$categoriesOptions) {
            $params['options'] = self::$categoriesOptions;
            return self::selectlist($name, $params);
        }
        return false;
    }
    static public function categorySelectbox($name, $params = array('attrs'=>'', 'size'=> 5, 'value' => '')) {
        self::_loadCategoriesOptions();
        if(!empty(self::$categoriesOptions)) {
            $params['options'] = self::$categoriesOptions;
            return self::selectbox($name, $params);
        }
        return false;
    }
    static public function productsSelectlist($name, $params = array('attrs'=>'', 'size'=> 5, 'value' => '')) {
        self::_loadProductsOptions();
        if(!empty(self::$productsOptions)) {
            $params['options'] = self::$productsOptions;
            return self::selectlist($name, $params);
        }
        return false;
    }
    static public function productsSelectbox($name, $params = array('attrs'=>'', 'size'=> 5, 'value' => '')) {
        self::_loadProductsOptions();
        if(!empty(self::$productsOptions)) {
            $params['options'] = self::$productsOptions;
            return self::selectbox($name, $params);
        }
        return false;
    }
    static protected function _loadCategoriesOptions() {
        if(empty(self::$categoriesOptions)) {
            $categories = frameBup::_()->getModule('products')->getCategories();
            if(!empty($categories)) {
                foreach($categories as $c) {
                    self::$categoriesOptions[$c->term_taxonomy_id] = $c->cat_name;
                }
            }
        }
    }
    static protected function _loadProductsOptions() {
        if(empty(self::$productsOptions)) {
            $products = frameBup::_()->getModule('products')->getModel()->get(array('getFields' => 'post.ID, post.post_title'));
            if(!empty($products)) {
                foreach($products as $p) {
                    self::$productsOptions[$p['ID']] = $p['post_title'];
                }
            }
        }
    }
    static public function slider($name, $params = array('value' => 0, 'min' => 0, 'max' => 0, 'step' => 1, 'slide' => '')) {
        $id = self::nameToClassId($name);
        $paramsStr = '';
        if(empty($params['slide']) && $params['slide'] !== false) { //Can be set to false to ignore function onSlide event binding
            $params['slide'] = 'toeSliderMove';
        }
        if(!empty($params)) {
            $paramsArr = array();
            foreach($params as $k => $v) {
                $value = (is_numeric($v) || in_array($k, array('slide'))) ? $v : '"'. $v. '"';
                $paramsArr[] = $k. ': '. $value;
            }
            $paramsStr = implode(', ', $paramsArr);
        }
        $res = '<div id="toeSliderDisplay_'. $id. '">'. (empty($params['value']) ? '' : $params['value']). '</div>';
        $res .= '<div id="'. $id. '"></div>';
        $params['attrs'] = 'id="toeSliderInput_'. $id. '"';
        $res .= self::hidden($name, $params);
        $res .= '<script type="text/javascript"><!--
            jQuery(function(){
                jQuery("#'. $id. '").slider({'. $paramsStr. '});
            });
            --></script>';
        return $res;
    }
	static public function capcha() {
		return recapcha::_()->getHtml();
	}
	static public function textIncDec($name, $params = array('value' => '', 'attrs' => '', 'options' => array(), 'onclick' => '', 'id' => '')) {
		if(!isset($params['attrs']))
			$params['attrs'] = '';
		$textId = (isset($params['id']) && !empty($params['id'])) ? $params['id'] : 'toeTextIncDec_'. mt_rand(9, 9999);
		$params['attrs'] .= ' id="'. $textId. '"';
		$textField = self::text($name, $params);
		$onClickInc = 'toeTextIncDec(\''. $textId. '\', 1); return false;';
		$onClickDec = 'toeTextIncDec(\''. $textId. '\', -1); return false;';
		if(isset($params['onclick']) && !empty($params['onclick'])) {
			$onClickInc = $params['onclick']. ' '. $onClickInc;
			$onClickDec = $params['onclick']. ' '. $onClickDec;
		}
		$textField .= '<div class="toeUpdateQtyButtonsWrapper"><div class="toeIncDecButton toeIncButton '. $textId. '" onclick="'. $onClickInc. '">+</div>'
				. '<div class="toeIncDecButton toeDecButton '. $textId. '" onclick="'. $onClickDec. '">-</div></div>';
		return $textField;
	}
	static public function colorpicker($name, $params = array('value' => '')) {
		$textId = self::nameToClassId($name). '_'. mt_rand(9, 9999);
		$pickerId = $textId. '_picker';
		if(!isset($params['attrs']))
			$params['attrs'] = '';
		$params['attrs'] .= 'id="'. $textId. '"';
		$out = self::text($name, $params);
		$out .= '<div style="position: absolute; z-index: 1;" id="'. $pickerId. '"></div>';
		$out .= '<script type="text/javascript">//<!--
			jQuery(function(){
				jQuery("#'. $pickerId. '").hide();
				jQuery("#'. $pickerId. '").farbtastic("#'. $textId. '");
				jQuery("#'. $textId. '").click(function() {
					jQuery("#'. $pickerId. '").fadeIn();
				});
				// Close picker if clicked on outside area
				jQuery(document).mousedown(function() {
					jQuery("#'. $pickerId. '").each(function() {
						var display = jQuery(this).css("display");
						if ( display == "block" )
							jQuery(this).fadeOut();
					});
				});
			});
			//--></script>';
		return $out;
	}
	static public function fontsList($name, $params = array('value' => '')) {
		static $options = array();

		if(empty($options)) {	// Fill them only one time per loading
			foreach(fieldAdapterBup::getFontsList() as $font)
				$options[ $font ] = $font;
		}
		$params['options'] = $options;
		return self::selectbox($name, $params);
	}
	static public function checkboxHiddenVal($name, $params = array('attrs' => '', 'value' => '', 'checked' => '')) {
		$checkId = self::nameToClassId($name). '_check';
		$hideId = self::nameToClassId($name). '_text';
		if(!isset($params['attrs']))
			$params['attrs'] = '';
		$paramsCheck = $paramsHidden = $params;
		$paramsCheck['attrs'] .= ' id="'. $checkId. '"';
		$paramsHidden['attrs'] .= ' id="'. $hideId. '"';
		$paramsHidden['value'] = $paramsCheck['checked'] ? '1' : '0';
		$out = self::checkbox(self::nameToClassId($name), $paramsCheck);
		$out .= self::hidden($name, $paramsHidden);
		$out .= '<script type="text/javascript">//<!--
			jQuery(function(){
				jQuery("#'. $checkId. '").change(function(){
					jQuery("#'. $hideId. '").val( (jQuery(this).attr("checked") ? 1 : 0) );
				});
			});
			//--></script>';
		return $out;
	}
}
