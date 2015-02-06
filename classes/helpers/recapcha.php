<?php
class recapcha {
	private $_publicKey = '6LfUotgSAAAAAL4pqsHxE8sx6Cz8o7AEc_JjtROD';
	private $_privateKey = '6LfUotgSAAAAACFAM1TMpIsLiQsfDmV-mRNfQg1n';
	
	public function __construct() {
		if(!function_exists('recaptcha_get_html')) {	// In case if this lib was already included by another plugin
			import(CSP_HELPERS_DIR. 'recaptchalib.php');
		}
	}
	static public function getInstance() {
		static $instance = NULL;
		if(empty($instance)) {
			$instance = new recapcha();
		}
		return $instance;
	}
	static public function _() {
		return self::getInstance();
	}
	public function getHtml() {
		if(reqCsp::getVar('reqType') == 'ajax') {
			$divId = 'toeRecapcha'. mt_rand(1, 9999);
			return '<div id="'. $divId. '"></div>'.
				'<script type="text/javascript">
				// <!--
				Recaptcha.create("'. $this->_publicKey. '",
					"'. $divId. '",
					{
					  theme: "red",
					  callback: Recaptcha.focus_response_field
					}
				  );
				// -->
				</script>';
		} else {
			return recaptcha_get_html($this->_publicKey, null, true);
		}
	}
	public function check() {
		$resp = recaptcha_check_answer($this->_privateKey, 
					$_SERVER['REMOTE_ADDR'], 
					reqCsp::getVar('recaptcha_challenge_field'),
					reqCsp::getVar('recaptcha_response_field'));
		return $resp->is_valid;
	}
}
