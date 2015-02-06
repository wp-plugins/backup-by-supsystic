<?php
class scheduleControllerBup extends controllerBup {
	
	public function exemple() {
		$res = new responseBup();
		
		if ($data = $this->getModel()->backUpNow())	{
			$res->addMessage(langBup::_('Backup complete'));
			$res->addData($data);
		} else 
			$res->pushError ($this->getModel('options')->getErrors());
		return $res->ajaxExec();
	}
	
	public function saveGroupEvery(){
		$res = new responseBup();
		
		if ($data = $this->getModel()->saveGroupEvery(reqBup::get('post')))	{
			$res->addMessage(langBup::_('Save complete'));
			//$res->addData($data);
		} else 
			$res->pushError ($this->getModel()->getErrors());
		return $res->ajaxExec();
		
	}
	public function getPermissions() {
		return array(
			BUP_USERLEVELS => array(
				BUP_ADMIN => array('exemple', 'saveGroupEvery')
			),
		);
	}
}

