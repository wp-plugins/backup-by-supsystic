<?php
class optionsViewBup extends viewBup {

    public function getAdminPage() {
        $tabsData =  array(
            'bupMainOptions' => array(
                'title'   => 'Main',
                'content' => $this->getMainOptionsTab(),
                'faIcon' => 'fa-home',
            ),
            'bupStorageOptions' => array(
                'title' => 'Backups',
                'content' => frameBup::_()->getModule('storage')->getController()->getView()->getAdminOptions(),
                'faIcon' => 'fa-database',
            ),
        );

        $activeTab = $this->getModule()->getActiveTab();
        $tabsData = dispatcherBup::applyFilters('adminOptionsTabs', $tabsData);
        if(!empty($tabsData[$activeTab]['content'])) {
            $content = $tabsData[$activeTab]['content'];
        } else {
            $content = $tabsData['bupMainOptions']['content'];
            $activeTab = 'bupMainOptions';
        }
        $page = !empty($_GET['page']) ? $_GET['page'] : BUP_PLUGIN_PAGE_URL_SUFFIX;
        $this->assign('tabsData', $tabsData);
        $this->assign('page', $page);
        $this->assign('activeTab', $activeTab);
        $this->assign('content', $content);
        parent::display('optionsAdminPage');
    }

    public function getMainOptionsTab() {
        //$generalOptions = $this->getModel()->getByCategories('General');
        if(!isset($this->optModel))
            $this->assign('optModel', $this->getModel());
        $backupPlaces = dispatcherBup::applyFilters('adminCloudServices', array());
        $this->assign('backupPlaces', $backupPlaces);
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
