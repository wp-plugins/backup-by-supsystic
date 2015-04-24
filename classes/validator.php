<?php
class validatorBup {
    static public $errors = array();
    static public function validate($field, $label = '', $validate = array()) {
        self::$errors = array();
        if(is_object($field) && get_class($field) != 'fieldBup') {
            $value = $field;
            $field = new fieldBupBup('noMatter');
            $field->label = $label;
            $field->setValue($value);
            $field->setValidation($validate);
        }
        if(!empty($field->validate)) {
            foreach($field->validate as $v) {
                 if(method_exists('validatorBup', $v)) 
                    self::$v($field);
            }
        }
        if(method_exists('validatorBup', $field->type)) {
            $validate = $field->type;
            self::$validate($field);
        }
        if($field->maxlen) {
            self::validLen($field);
        }
        return self::$errors;
    }
    static public function validLen($field, $label = '', $validate = array()) {
        if( !(bool) (strlen($field->value) <= $field->maxlen)) {
			self::addError(__(array('Invalid length for', $field->label, ', max length is', $field->maxlen), BUP_LANG_CODE), $field->name);
			return false;
		}
		return true;
    }
    static public function _($field) {
        return self::validate($field);
    }
    static public function getErrors() {
        return self::$errors;
    }
    static public function numeric($field) {
        if(!is_numeric($field->value) && !empty($field->value)) {
            self::addError(__(array('Invalid numeric value for', $field->label), BUP_LANG_CODE), $field->name);
            return false;
        }
        return true;
    }
    static public function int($field) {
        return self::numeric($field);
    }
    static public function float($field) {
        return self::numeric($field);
    }
    static public function double($field) {
        return self::numeric($field);
    }
    static protected function _notEmpty($value) {
        if(is_array($value)) {
            foreach($value as $v) {
                if(self::_notEmpty($v)) {       //If at least 1 element of array are not empty - all array will be not empty
                    $res = true;
                    break;
                }
            }
        } else
            $res = !empty($value);
        return $res;
    }
    static public function notEmpty($field) {
        if(!self::_notEmpty($field->value)) {
            self::addError(__(array('Please enter', $field->label), BUP_LANG_CODE), $field->name);
            return false;
        }
        return true;
    }
    static public function selectNotEmpty($field) {
        if(empty($field->value)) {
            self::addError(__(array('Please select', $field->label), BUP_LANG_CODE), $field->name);
            return false;
        }
        return true;
    }
    static public function email($field) {
        if(!is_email($field->value)) {
            self::addError(__(array('Invalid', $field->label), BUP_LANG_CODE), $field->name);
            return false;
        } elseif(email_exists($field->value)) {
            self::addError(__(array($field->label, 'is already registered'), BUP_LANG_CODE), $field->name);
            return false;
        }
        return true;
    }
    static public function addError($error, $key = '') {
        if($key)
            self::$errors[$key] = $error;
        else
            self::$errors[] = $error;
    }
    static public function string($field) {
        if (preg_match('/([0-9].*)/', $field->value)) {
            self::addError(__(array('Invalid', $field->label), BUP_LANG_CODE), $field->name);
            return false;
        }
        return true;
    }
    /**
     * Fective method
     */
    /*static public function none($fileld) {
        return true;
    }*/
    static public function getProductValidationMethods() {
        $res = array();
        $all = get_class_methods('validatorBup');
        foreach($all as $m) {
            if(in_array($m, array('int', 'none', 'string'))) {
                $res[$m] = __($m, BUP_LANG_CODE);
            }
        }
        return $res;
    }
    
    static public function getUserValidationMethods() {
        // here validation for user fields
        $res = array();
        $all = get_class_methods('validatorBup');
        foreach($all as $m) {
            if(in_array($m, array('int', 'none', 'string', 'email', 'validLen'))) {
                $res[$m] = __($m, BUP_LANG_CODE);
            }
        }
        return $res;
    }
    static public function prepareInput($input) {
        if(is_array($input)) {
            return array_map(array(validator, 'prepareInput'), $input);
        } else
            return mysql_escape_string($input);
    }
}

