<?php
class promo_supsysticViewBup extends viewBup {
	public function displayAdminFooter() {
		$this->assign('mainLink', $this->getModule()->getMainLink());
		parent::display('adminFooter');
	}	
	public function showAdminSendStatNote() {
		parent::display('adminSendStatNote');
	}	
	public function getOverviewTabContent() {
		frameBup::_()->addScript('jquery.slimscroll', BUP_JS_PATH. 'jquery.slimscroll.js');
		frameBup::_()->addScript('admin.overview', $this->getModule()->getModPath(). 'js/admin.overview.js');
		frameBup::_()->addStyle('admin.overview', $this->getModule()->getModPath(). 'css/admin.overview.css');
		$this->assign('mainLink', $this->getModule()->getMainLink());
		$this->assign('faqList', $this->getFaqList());
		$this->assign('serverSettings', $this->getServerSettings());
		$this->assign('news', $this->getNewsContent());
		$this->assign('contactFields', $this->getModule()->getContactFormFields());
		return parent::getContent('overviewTabContent');
	}
	public function getFaqList() {
		return array(
			__('How to get PRO version of plugin for FREE?', BUP_LANG_CODE) => sprintf(__('You have an incredible opportunity to get PRO version for free. Make Translation of plugin! It will be amazing if you take advantage of this offer! More info you can find here <a target="_blank" href="%s">"Get PRO version of any plugin for FREE"</a>', BUP_LANG_CODE), $this->getModule()->getMainLink()),
			__('Translation', BUP_LANG_CODE) => sprintf(__('All available languages are provided with the Supsystic Google Maps plugin. If your language isn\'t available, your plugin will be in English by default.<br /><b>Available Translations: English</b><br />Translate or update a translation Backup WordPress plugin in your language and get a Premium license for FREE. <a target="_blank" href="%s">Contact us</a>.', BUP_LANG_CODE), $this->getModule()->getMainLink(). '#contact'),
		);
	}
	public function getNewsContent() {
		// TODO: Finish this
		$getData = wp_remote_get('http://supsystic.com/news/main.html');
		$content = '';
		if($getData 
			&& is_array($getData) 
			&& isset($getData['response']) 
			&& isset($getData['response']['code']) 
			&& $getData['response']['code'] == 200
			&& isset($getData['body'])
			&& !empty($getData['body'])
		) {
			$content = $getData['body'];
		} else {
			$content = sprintf(__('There were some problem while trying to retrive our news, but you can always check all list <a target="_blank" href="%s">here</a>.', BUP_LANG_CODE), frameBup::_()->getModule('promo_supsystic')->getMainLink());
		}
		return $content;
	}
	public function getServerSettings() {
		return array(
			'Operating System' => array('value' => PHP_OS),
            'PHP Version' => array('value' => PHP_VERSION),
            'Server Software' => array('value' => $_SERVER['SERVER_SOFTWARE']),
            'MySQL' => array('value' => mysql_get_server_info()),
            'PHP Safe Mode' => array('value' => ini_get('safe_mode') ? __('Yes', BUP_LANG_CODE) : __('No', BUP_LANG_CODE), 'error' => ini_get('safe_mode')),
            'PHP Allow URL Fopen' => array('value' => ini_get('allow_url_fopen') ? __('Yes', BUP_LANG_CODE) : __('No', BUP_LANG_CODE)),
            'PHP Memory Limit' => array('value' => ini_get('memory_limit')),
            'PHP Max Post Size' => array('value' => ini_get('post_max_size')),
            'PHP Max Upload Filesize' => array('value' => ini_get('upload_max_filesize')),
            'PHP Max Script Execute Time' => array('value' => ini_get('max_execution_time')),
            'PHP EXIF Support' => array('value' => extension_loaded('exif') ? __('Yes', BUP_LANG_CODE) : __('No', BUP_LANG_CODE)),
            'PHP EXIF Version' => array('value' => phpversion('exif')),
            'PHP XML Support' => array('value' => extension_loaded('libxml') ? __('Yes', BUP_LANG_CODE) : __('No', BUP_LANG_CODE), 'error' => !extension_loaded('libxml')),
            'PHP CURL Support' => array('value' => extension_loaded('curl') ? __('Yes', BUP_LANG_CODE) : __('No', BUP_LANG_CODE), 'error' => !extension_loaded('curl')),
		);
	}
    public function showReviewAdminNotice() {
        if (is_admin() && frameBup::_()->isPluginAdminPage()) {
            $showReviewBlock = get_option('bupShowReviewBlockV2'); // v2 because was v1 and it don't using now
            $bupShowReviewBlockTimestamp = get_option('bupShowReviewBlockTimestampV2');
            $sendStatAfterSevenDays = get_option('sendStatAfterSevenDays');

            if ($showReviewBlock === false) {
                add_option('bupShowReviewBlockV2', 'yes');
                add_option('sendStatAfterSevenDays', 'yes');
                add_option('bupShowReviewBlockTimestampV2', time());
            } elseif ($showReviewBlock === 'yes' && time() > ($bupShowReviewBlockTimestamp + 86400 * 7)) {
                if ($sendStatAfterSevenDays === 'yes') {
                    $this->getModel()->sendUsageStat(array('code' => 'seven_days_passed', 'visits' => 1,));
                    update_option('sendStatAfterSevenDays', 'no');
                }
                echo parent::getContent('reviewNotice');
            }
        }
    }
}