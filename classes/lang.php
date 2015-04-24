<?php
class langBup {
	static private $_codeStorage = array();
	static private $_data = array();
	/**
	 * Initialize language for plugin
	 */
	static public function init() {
		self::$_data = self::extract();
	}
	static public function attach($d) {
		self::$_data = array_merge(self::$_data, self::extract($d));
	}
	static public function extract($d = array('dir' => '', 'langBup' => '')) {
		$data = array();
		if(isset($d['dir']) && !empty($d['dir']))
			$langDirPath = $d['dir'];
		else if(isset($d['langBup']) && !empty($d['langBup']))
			$langDirPath = BUP_LANG_DIR. $d['langBup']. DS;
		else
			$langDirPath = BUP_LANG_DIR. BUP_WPLANG. DS;

		if(is_dir($langDirPath)) {
			$dh = opendir($langDirPath);
			while(($file = readdir($dh)) !== false) {
				if(!in_array($file, array('.', '..')) && !empty($file)) {
					$fileinfo = pathinfo($langDirPath. $file);
					if($fileinfo['extension'] == 'ini') {
						$langArr = parse_ini_file($langDirPath. $file, true);
						if(is_array($langArr) && !empty($langArr)) {
							$normalLangArr = array();
							foreach($langArr as $k => $v) {
								$normalLangArr[ self::unEscKey($k) ] = $v;
							}
							$data = array_merge($data, $normalLangArr);
						}
					}
				}
			}
			closedir($dh);
		}
		if(!is_array($data))	// For some cases
			$data = array();
		return $data;
	}


    /**
     * Translate specified string with gettext
     * @param string $msgid A string to translate
     * @return string
     */
    static public function _($msgid)
    {
        return __($msgid, BUP_LANG_CODE);
	}

    /**
     * Echo specified string
     * @param string $msgid A string to translate
     */
    static public function _e($msgid)
    {
		echo self::_($msgid);
	}

	static public function getData() {
		return self::$_data;
	}
	static public function unEscKey($key) {
		$illegals = self::getIllegalIniChars();
		return str_replace(
				$illegals,
				array_keys($illegals),
				$key);
	}
	static public function escKey($key) {
		$illegals = self::getIllegalIniChars();
		return str_replace(
				array_keys($illegals),
				$illegals,
				$key);
	}
	/**
	 * Illegal characters for keys in .ini files and it's representation for us
	 */
	static public function getIllegalIniChars() {
		return array(
			'?' => '%quest%',
			'{' => '%opening_brace%',
			'}' => '%closing_brace%',
			'|' => '%vertical_bar%',
			'&' => '%ampersand%',
			'~' => '%tilde%',
			'!' => '%exclamation_point%',
			'[' => '%opening_bracket%',
			']' => '%closing_bracket%',
			'(' => '%opening_parenthesis%',
			')' => '%closing_parenthesis%',
			'^' => '%caret%',
			'Yes'	=> '%Yes%',
			'yes'	=> '%yes%',
			'No'	=> '%No%',
			'no'	=> '%no%',
			'none'	=> '%none%',
		);
	}
}
