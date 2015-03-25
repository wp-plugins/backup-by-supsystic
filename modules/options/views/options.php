<?php
class optionsViewBup extends viewBup {

    public function getAdminPage() {
        $tabsData =  array(
            'bupMainOptions' => array(
                'title'   => 'Backup',
                'content' => array($this, 'getMainOptionsTab'),
                'faIcon' => 'fa-home',
				'sort_order' => 10,
            )
        );
        $tabsData = dispatcherBup::applyFilters('adminOptionsTabs', $tabsData);
		uasort($tabsData, array($this, 'sortTabsClb'));
        $activeTabForCssClass = $this->getModule()->getActiveTabForCssClass($tabsData);
        $activeTab = $this->getModule()->getActiveTab();
        if(!empty($tabsData[$activeTab]['content'])) {
            $content = call_user_func_array($tabsData[$activeTab]['content'], array());
        } else {
            $content = call_user_func_array($tabsData['bupMainOptions']['content'], array());
            $activeTab = 'bupMainOptions';
        }
        $page = !empty($_GET['page']) ? $_GET['page'] : BUP_PLUGIN_PAGE_URL_SUFFIX;
        frameBup::_()->addJSVar('adminOptionsBup', 'bupActiveTab', ($activeTab != $activeTabForCssClass) ? $activeTabForCssClass : $activeTab); // This js var used for highlighting current item submenu in admin menu
        frameBup::_()->addJSVar('adminOptionsBup', 'bupPageTitle', strip_tags($tabsData[$activeTab]['title']));
        $this->assign('tabsData', $tabsData);
        $this->assign('page', $page);
        $this->assign('activeTab', $activeTab);
        $this->assign('content', $content);
        $this->assign('activeTabForCssClass', $activeTabForCssClass);
        parent::display('optionsAdminPage');
    }
	public function sortTabsClb($a, $b) {
		if(isset($a['sort_order']) && isset($b['sort_order'])) {
			if($a['sort_order'] > $b['sort_order'])
				return 1;
			if($a['sort_order'] < $b['sort_order'])
				return -1;
		}
		return 0;
	}

    public function getMainOptionsTab() {
        //$generalOptions = $this->getModel()->getByCategories('General');
        if(!isset($this->optModel))
            $this->assign('optModel', $this->getModel());
        $backupPlaces = dispatcherBup::applyFilters('adminCloudServices', array());
        $backupDest = frameBup::_()->getModule('options')->get('glb_dest');
        $this->assign('backupPlaces', $backupPlaces);
        $this->assign('backupDest', $backupDest);
        $this->assign('backupOptions', parent::getContent('backupOptions'));
        //$this->assign('allOptions', $generalOptions['opts']);
        return parent::getContent('mainOptionsTab');
    }

	public function displayDeactivatePage() {
        $this->assign('GET', reqBup::get('get'));
        $this->assign('POST', reqBup::get('post'));
        $this->assign('REQUEST_METHOD', strtoupper(reqBup::getVar('REQUEST_METHOD', 'server')));
        $this->assign('REQUEST_URI', basename(reqBup::getVar('REQUEST_URI', 'server')));
        parent::display('deactivatePage');
    }
}
