<?php
class adminmenuViewBup extends viewBup {
    protected $_file = '';

    public function init() {
        $this->_file = BUP_PLUGIN_PAGE_URL_SUFFIX;
        add_action('admin_menu', array($this, 'initMenu'), 9);
        parent::init();
    }
    public function initMenu() {
		$accessCap = 'manage_options';
		$firstTimeLookedToPlugin = installerBup::isUsed();
				
		if($firstTimeLookedToPlugin) {
        	add_menu_page(langBup::_('Backup by Supsystic'), langBup::_('Backup by Supsystic'), $accessCap, $this->_file, array(frameBup::_()->getModule('options')->getView(), 'getAdminPage'));
		} else {
			if ( frameBup::_()->getModule('promo_supsystic') ){
				add_menu_page(langBup::_('Backup by Supsystic'), langBup::_('Backup by Supsystic'), $accessCap, $this->_file, array(frameBup::_()->getModule('promo_supsystic')->getView(), 'showWelcomePage'));
			} else { // if not install module "promo_supsystic"
				installerBup::setUsed();
				add_menu_page(langBup::_('Backup by Supsystic'), langBup::_('Backup by Supsystic'), $accessCap, $this->_file, array(frameBup::_()->getModule('options')->getView(), 'getAdminPage'));
			}
		}
    }
    public function getFile() {
        return $this->_file;
    }
}