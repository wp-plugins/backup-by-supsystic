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
        $subMenuTabs = array(
            1 => array('parentSlug' => $this->_file, 'pageTitle' => __('Backup', BUP_LANG_CODE), 'menuTitle' => __('Backup', BUP_LANG_CODE), 'capability' => $accessCap, 'menuSlug' => $this->_file),
			2 => array('parentSlug' => $this->_file, 'pageTitle' => __('Overview', BUP_LANG_CODE), 'menuTitle' => __('Overview', BUP_LANG_CODE), 'capability' => $accessCap, 'menuSlug' => 'admin.php?page=supsystic-backup&tab=overview'),
            3 => array('parentSlug' => $this->_file, 'pageTitle' => __('Schedule', BUP_LANG_CODE), 'menuTitle' => __('Schedule', BUP_LANG_CODE), 'capability' => $accessCap, 'menuSlug' => 'admin.php?page=supsystic-backup&tab=bupSchedule'),
            4 => array('parentSlug' => $this->_file, 'pageTitle' => __('Restore', BUP_LANG_CODE), 'menuTitle' => __('Restore', BUP_LANG_CODE), 'capability' => $accessCap, 'menuSlug' => 'admin.php?page=supsystic-backup&tab=bupLog'),
        );
        $subMenuTabs = dispatcherBup::applyFilters('addAdminSubMenuTabs', $subMenuTabs);

        add_menu_page(__('Backup by Supsystic', BUP_LANG_CODE), __('Backup by Supsystic', BUP_LANG_CODE), $accessCap, $this->_file, array(frameBup::_()->getModule('options')->getView(), 'getAdminPage'));
        foreach($subMenuTabs as $tab){
            add_submenu_page($tab['parentSlug'], $tab['pageTitle'], $tab['menuTitle'], $tab['capability'], $tab['menuSlug']);
        }
//        remove_submenu_page($this->_file, $this->_file);
    }
    public function getFile() {
        return $this->_file;
    }
}