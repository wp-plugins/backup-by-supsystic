<?php
class optionsViewBup extends viewBup {

    public function getAdminPage() {
        $tabsData =  array(
            'bupMainOptions' => array(
                'title'   => __('Backup', BUP_LANG_CODE),
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
        if(!isset($this->optModel))
            $this->assign('optModel', $this->getModel());

        $backupPlaces = dispatcherBup::applyFilters('getBackupDestination', array());
        // sorting $backupPlaces by $backupPlaces['sortNum']
        $sort = array();
        foreach ($backupPlaces as $key => $row) {
            $sort[$key] = $row['sortNum'];
        }
        array_multisort($sort, SORT_ASC, $backupPlaces);

        $backupDest = frameBup::_()->getModule('options')->get('glb_dest');
		$zipNotExtMsg = frameBup::_()->getModule('backup')->getController()->checkExtensions();
        $zipExtExist = ($zipNotExtMsg !== true) ? 'disabled' : true;

        $this->assign('zipExtExist', $zipExtExist);
        $this->assign('zipNotExtMsg', $zipNotExtMsg);
        $this->assign('backupPlaces', $backupPlaces);
        $this->assign('backupDest', $backupDest);
        $this->assign('backupOptions', parent::getContent('backupOptions'));
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
