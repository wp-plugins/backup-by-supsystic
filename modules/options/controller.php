<?php
class optionsControllerBup extends controllerBup {
	
	public function saveGroup() {
		$res = new responseBup();
		
		$post = reqBup::get('post');
		
		if ($result = $this->getModel()->saveGroup($post)) {
			$res->addMessage(__('Save Complete', BUP_LANG_CODE));
			$res->addData($result);
		} else 
			$res->pushError ($this->getModel('options')->getErrors());
		return $res->ajaxExec();
	}
	
	public function saveMainFromDestGroup(){
		$res = new responseBup();
		$post = reqBup::get('post');
		if ($this->getModel()->saveMainFromDestGroup($post) && $this->getModel()->saveGroup($post)) {
			$res->addMessage(__('Save Complete', BUP_LANG_CODE));
			$res->addData(true);
		} else 
			$res->pushError ($this->getModel('options')->getErrors());
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			BUP_USERLEVELS => array(
				BUP_ADMIN => array('saveGroup', 'saveMainFromDestGroup')
			),
		);
	}
}

