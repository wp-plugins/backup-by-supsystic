<?php
class optionsViewBup extends viewBup {

    public function getAdminPage() {
        $tabsData =  array(
            'bupMainOptions' => array(
                'title'   => 'Backup',
                'content' => array($this, 'getMainOptionsTab'),
                'faIcon' => 'fa-home',
            ),
            'bupLog' => array(
                'title' => 'Log',
                'content' => array(frameBup::_()->getModule('storage')->getController()->getView(), 'getAdminOptions'),
                'faIcon' => 'fa-database',
            ),
        );
        $tabsData = dispatcherBup::applyFilters('adminOptionsTabs', $tabsData);
        $activeTabForCssClass = $this->getModule()->getActiveTabForCssClass($tabsData);
        $activeTab = $this->getModule()->getActiveTab();
        if(!empty($tabsData[$activeTab]['content'])) {
            $content = call_user_func_array($tabsData[$activeTab]['content'], array());
        } else {
            $content = call_user_func_array($tabsData['bupMainOptions']['content'], array());
            $activeTab = 'bupMainOptions';
        }
        $page = !empty($_GET['page']) ? $_GET['page'] : BUP_PLUGIN_PAGE_URL_SUFFIX;
        frameBup::_()->addJSVar('adminOptionsBup', 'bupActiveTab', $activeTab);
        $this->assign('tabsData', $tabsData);
        $this->assign('page', $page);
        $this->assign('activeTab', $activeTab);
        $this->assign('content', $content);
        $this->assign('activeTabForCssClass', $activeTabForCssClass);
        parent::display('optionsAdminPage');
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
