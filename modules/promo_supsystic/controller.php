<?php
class promo_supsysticControllerBup extends controllerBup {
	public function bupSendInfo(){
		$res = new responseBup();
		if($this->getModel()->welcomePageSaveInfo(reqBup::get('post'))) {
			$res->addMessage(__('Information was saved. Thank you!', BUP_LANG_CODE));
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

    public function getPromoScheduleAction() {
        return $this->render('schedulePromo');
    }

    public function getPromoMigrationAction() {
        return $this->render('migrationPromo');
    }

    public function sendStatistic(){
        $res = new responseBup();
        $req = reqBup::get('post');
        $statisticCode = !empty($req['statisticCode']) ? $req['statisticCode'] : null;
        if($statisticCode) {
            $this->getModel()->sendUsageStat(array('code' => $statisticCode, 'visits' => 1,));
            if($statisticCode === 'maybe_later_leave_feedback' || $statisticCode === 'leaved_feedback')
                update_option('bupShowReviewBlockV2', 'no');
        } else {
            $res->addError('unexpectedError');
        }
        return $res->ajaxExec();
    }

    /**
     *
     * @param  string $template
     * @param  array  $data
     * @return string
     */
    public function render($template, $data = array()) {
        return $this->getView()->getContent($template, $data);
    }
}