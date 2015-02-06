<?php
class storageViewBup extends viewBup {
    public function getAdminOptions() {
//		frameBup::_()->addScript('adminStorageOptions', $this->getModule()->getModPath(). 'js/admin.storage.js');
		
//		$storage = array();
		/*$arrStorage = $this->getModel()->getStorage();
		
		$storageBlock = array(); $sendTplData = array();
		foreach($arrStorage as $key=>$el){
			$title = frameBup::_()->getModule('backup')->getModel()->fileNameFormat(current($el), 'title');
			$type = frameBup::_()->getModule('backup')->getModel()->fileNameFormat(current($el), 'prefix');
			$header = ($type == 'full') ? '<strong>Full backup</strong> '.$title : $title;
			$sendTplData = array( $key, $header, $el );
			$this->assign('storageData', $sendTplData);
			$storageBlock[] = parent::getContent('storageBlock');
		}*/
		
		//$this->assign('storageBlock', $storageBlock);
			
//		return parent::getContent('storagePage');
	}
	
	 public function getBlockBup($key, $files) {
		
		//$storage = array();
		//$arrStorage = $this->getModel()->getStorage();
		
		//$storageBlock = array(); 
		$sendTplData = array();
		//print_r($arrStorage);
		//foreach($arrStorage as $key=>$el){
//			$title = frameBup::_()->getModule('backup')->getModel()->fileNameFormat(current($files), 'title');
//			$type = frameBup::_()->getModule('backup')->getModel()->fileNameFormat(current($files), 'prefix');
//			$header = ($type == 'full') ? '<strong>Full backup</strong> '.$title : $title;
//			$sendTplData = array( $key, $header, $files );
//			$this->assign('storageData', $sendTplData);
			//$storageBlock[] = parent::getContent('storageBlock');
//			$ret = parent::getContent('storageBlock');
		//}
		
		//$this->assign('storageBlock', $storageBlock);
			
//		return $ret;
	}
	
	public function getAdminOptionsLimit($d = array()) { // deprecate
		frameBup::_()->addScript('adminScheduleOptions', $this->getModule()->getModPath(). 'js/admin.storage.js');
		
		$storage = array();
		//$arrStorage = $this->getModel()->getStorage();
		$arrStorage = $this->getModel()->getList_A($d);
		
		$storageBlock = array(); $sendTplData = array();
		$i=0; $ret = array();
		foreach($arrStorage as $key=>$el){
			$i++;
			$title = frameBup::_()->getModule('backup')->getModel()->fileNameFormat(current($el), 'title');
			$type = frameBup::_()->getModule('backup')->getModel()->fileNameFormat(current($el), 'prefix');
			$header = ($type == 'full') ? '<strong>Full backup</strong> '.$title : $title;
			$sendTplData = array( $key, $header, $el );
			$this->assign('storageData', $sendTplData);
			$storageBlock['id'] = $key;
			$storageBlock['bupbackupblock'] = parent::getContent('storageBlock');
			array_push($ret, array('id'=>$key, 'bupbackupblock'=>parent::getContent('storageBlock')));
			
		}
		
		//$this->assign('storageBlock', $storageBlock);
			
		return $ret;
	}
}
