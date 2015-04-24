<?php
class storageControllerBup extends controllerBup {
	public function displayStorage(){
		$res = new responseBup();
		$res->addData(array($this->getView()->getAdminOptions()));
		return $res->ajaxExec();
	}
	public function getList() {
		$res = new responseBup();
		$list = $this->getModel()->getList(reqBup::get('post'));
		$res->addData('list', $list);
		$res->addData('count', $this->getModel()->getCount());
		//$res->addMessage(__('Done', BUP_LANG_CODE));
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			BUP_USERLEVELS => array(
				BUP_ADMIN => array('displayStorage', 'getList')
			),
		);
	}
}

