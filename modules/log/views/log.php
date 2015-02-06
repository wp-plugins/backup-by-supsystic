<?php
class logViewBup extends viewBup {
	public function getAdminOptions() {
		frameBup::_()->addScript('adminLogOptions', $this->getModule()->getModPath(). 'js/admin.log.js');
		return parent::getContent('logPage');
	}
	
	public function getBlockLogBup($key, $files) {
		$sendTplData = array();
		$title = $this->getModel()->getDateLog($key);
		$header = 'Backup log '.$title;
		$sendTplData = array( $key, $header, $files );
		$this->assign('logData', $sendTplData);

		$ret = parent::getContent('logBlock');
			
		return $ret;
	}
	
}