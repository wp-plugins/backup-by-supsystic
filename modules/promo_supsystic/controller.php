<?php
class promo_supsysticControllerBup extends controllerBup {
	public function bupSendInfo(){
		$res = new responseBup();
		// Start usage in any case
		installerBup::setUsed();
		if($this->getModel()->welcomePageSaveInfo(reqBup::get('post'))) {
			$res->addMessage(langBup::_('Information was saved. Thank you!'));
		} else {
			$res->pushError($this->getModel()->getErrors());
		}
		$originalPage = reqBup::getVar('original_page');
		//$return = $this->getModule()->decodeSlug(str_replace('return=', '', $originalPage));
		$return = admin_url( strpos($originalPage, '?') ? $originalPage : 'admin.php?page='. $originalPage);
		// Start usage in any case
        redirectBup($return);
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			BUP_USERLEVELS => array(
				BUP_ADMIN => array('bupSendInfo')
			),
		);
	}
}