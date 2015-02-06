<?php
class promo_supsysticBup extends moduleBup {
	private $_specSymbols = array(
		'from'	=> array('?', '&'),
		'to'	=> array('%', '^'),
	);
	public function init() {
		add_action('admin_footer', array($this, 'displayAdminFooter'), 9);
		parent::init();
	}
	public function addWelcome() {
		installerBup::setUsed();
		return $this->getView()->showWelcomePage();
	}
	function displayAdminFooter(){
		if (frameBup::_()->isPluginAdminPage()){
			$this->getView()->displayAdminFooter();	
		}
	}
	private function _encodeSlug($slug) {
		return str_replace($this->_specSymbols['from'], $this->_specSymbols['to'], $slug);
	}
	private function _decodeSlug($slug) {
		return str_replace($this->_specSymbols['to'], $this->_specSymbols['from'], $slug);
	}
	public function decodeSlug($slug) {
		return $this->_decodeSlug($slug);
	}
	public function preparePromoLink($url) {
		return $url. '?ref=user';
	}
}