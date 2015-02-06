<?php
class adminmenuBup extends moduleBup {
    public function init() {
        parent::init();
        $this->getController()->getView('adminmenu')->init();
		$plugName = plugin_basename(BUP_DIR. BUP_MAIN_FILE);
		add_filter('plugin_action_links_'. $plugName, array($this, 'addSettingsLinkForPlug') );
    }
	public function addSettingsLinkForPlug($links) {
		array_unshift($links, '<a href="'. uriBup::_(array('baseUrl' => admin_url('admin.php'), 'page' => plugin_basename(frameBup::_()->getModule('adminmenu')->getView()->getFile()))). '">'. langBup::_('Settings'). '</a>');
		return $links;
	}
}

