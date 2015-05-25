<?php
class optionsBup extends moduleBup {
	protected $_uploadDir = 'bup';
	protected $_bgImgSubDir = 'bg_img';
	protected $_bgLogoImgSubDir = 'logo_img';

    /**
     * Method to trigger the database update
     */
    public function init(){
        parent::init();
    }
    /**
     * Returns the available tabs
     * 
     * @return array of tab 
     */
    public function getTabs(){
        $tabs = array();
        $tab = new tabBup(__('General', BUP_LANG_CODE), $this->getCode());
        $tab->setView('optionTab');
        $tab->setSortOrder(-99);
        $tabs[] = $tab;
        return $tabs;
    }
    /**
     * This method provides fast access to options model method get
     * @see optionsModel::get($d)
     */
    public function get($d = array()) {
        return $this->getController()->getModel()->get($d);
    }
	
	public function getValueType($d) {
        return $this->getController()->getModel()->getValueType($d);
    }
	
	public function set($value, $code) {
        return $this->getController()->getModel()->set($value, $code);
    }
	
	public function getEvery() {
        return $this->getController()->getModel()->getEvery();
    }
    public function getActiveTab() {
        $reqTab = reqBup::getVar('tab');
        return empty($reqTab) ? 'bupMainOptions' : $reqTab;
    }
    public function getActiveTabForCssClass($tabsData) {
        $reqTab = reqBup::getVar('tab');
        $currentTab = empty($reqTab) ? 'bupMainOptions' : $reqTab;
        foreach($tabsData as $key => $tab){
            if($currentTab == $key && !empty($tab['parent'])){
                $currentTab = $tab['parent'];
                break;
            }
        }
        return $currentTab;
    }
	public function getTabUrl($tab) {
		static $mainUrl;
		if(empty($mainUrl)) {
			$mainUrl = frameBup::_()->getModule('adminmenu')->getMainLink();
		}
		return empty($tab) ? $mainUrl : $mainUrl. '&tab='. $tab;
	}
}

