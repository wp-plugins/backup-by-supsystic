<?php
class promo_supsysticBup extends moduleBup {
	private $_mainLink = '';
    private $_msgForPro = '';
	private $_specSymbols = array(
		'from'	=> array('?', '&'),
		'to'	=> array('%', '^'),
	);
	public function __construct($d) {
		parent::__construct($d);
		$this->getMainLink();
		$this->_msgForPro = __('Please, be advised, that this option is available only in PRO version. You can ', BUP_LANG_CODE) . '<a class="button button-primary button-small" href="' . $this->getMainLink() . '" target="_blank">' . __('Get PRO', BUP_LANG_CODE) . '</a>';
	}
	public function init() {
		parent::init();
        if(!frameBup::_()->getModule('license')) {
            dispatcherBup::addFilter('adminOptionsTabs', array($this, 'registerModuleTab'));
            dispatcherBup::addFilter('getBackupDestination', array($this, 'addRemoteBackupDestination'));
            dispatcherBup::addFilter('getInputForSecretKeyEncryptDb', array($this, 'getPromoSecretKeyEncryptDb'));
            frameBup::_()->addJSVar('adminBackupOptionsV2', 'bupFreeVersionPlugin', 'true');
        } else {
            frameBup::_()->addJSVar('adminBackupOptionsV2', 'bupFreeVersionPlugin', 'false');
        }
		dispatcherBup::addFilter('adminOptionsTabs', array($this, 'registerOverviewTab'));
		dispatcherBup::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		add_action('admin_footer', array($this, 'displayAdminFooter'), 9);
        add_action('admin_notices', array($this->getController()->getView(), 'showReviewAdminNotice'));
	}
	public function displayAdminFooter() {
		if(frameBup::_()->isPluginAdminPage()) {
			$this->getView()->displayAdminFooter();
		}
	}
	public function registerOverviewTab($tabs) {
		$tabs['overview'] = array(
			'title'   => __('Overview', BUP_LANG_CODE),
			'content' => array($this, 'getOverviewTabContent'),
			'faIcon' => 'fa-info',
			'sort_order' => 5,
        );
		return $tabs;
	}
	public function getOverviewTabContent() {
		return $this->getView()->getOverviewTabContent();
	}
	private function _encodeSlug($slug) {
		return str_replace($this->_specSymbols['from'], $this->_specSymbols['to'], $slug);
	}
	private function _decodeSlug($slug) {
		return str_replace($this->_specSymbols['to'], $this->_specSymbols['from'], $slug);
	}
	public function decodeSlug($slug) {
		return $this->_decodeSlug($slug);
	}
	public function preparePromoLink($url) {
		return $url. '?ref=user';
	}
	public function getMainLink() {
		if(empty($this->_mainLink)) {
            $affiliateQueryString = '';
			$this->_mainLink = '//supsystic.com/plugins/backup-plugin/' . $affiliateQueryString;
		}
		return $this->_mainLink ;
	}
	public function getContactFormFields() {
		$fields = array(
            'name' => array('label' => __('Name', BUP_LANG_CODE), 'valid' => 'notEmpty', 'html' => 'text'),
			'email' => array('label' => __('Email', BUP_LANG_CODE), 'html' => 'email', 'valid' => array('notEmpty', 'email'), 'placeholder' => 'example@mail.com', 'def' => get_bloginfo('admin_email')),
			'website' => array('label' => __('Website', BUP_LANG_CODE), 'html' => 'text', 'placeholder' => 'http://example.com', 'def' => get_bloginfo('url')),
			'subject' => array('label' => __('Subject', BUP_LANG_CODE), 'valid' => 'notEmpty', 'html' => 'text'),
            'category' => array('label' => __('Topic', BUP_LANG_CODE), 'valid' => 'notEmpty', 'html' => 'selectbox', 'options' => array(
				'plugins_options' => __('Plugin options', BUP_LANG_CODE),
				'bug' => __('Report a bug', BUP_LANG_CODE),
				'functionality_request' => __('Require a new functionallity', BUP_LANG_CODE),
				'other' => __('Other', BUP_LANG_CODE),
			)),
			'message' => array('label' => __('Message', BUP_LANG_CODE), 'valid' => 'notEmpty', 'html' => 'textarea', 'placeholder' => __('Hello Supsystic Team!', BUP_LANG_CODE)),
        );
		foreach($fields as $k => $v) {
			if(isset($fields[ $k ]['valid']) && !is_array($fields[ $k ]['valid']))
				$fields[ $k ]['valid'] = array( $fields[ $k ]['valid'] );
		}
		return $fields;
	}
	public function isPro() {
		return frameBup::_()->getModule('license') ? true : false;
	}
    public function registerModuleTab($tabs) {
        $tabs['bupSchedule'] = array(
            'title'   => __('Schedule <p class="bupAIP">Available In PRO</p>', BUP_LANG_CODE),
            'content' => array($this->getController(), 'getPromoScheduleAction'),
            'faIcon' => 'fa-clock-o',
            'sort_order' => 15
        );
        $tabs['bupMigration'] = array(
            'title'   => __('Migration <p class="bupAIP">Available In PRO</p>', BUP_LANG_CODE),
            'content' => array($this->getController(), 'getPromoMigrationAction'),
            'faIcon' => 'fa-copy',
            'sort_order' => 55
        );

        return $tabs;
    }
    public function addRemoteBackupDestination(array $destinations) {
        $connectType = array(
            'remoteFtp' => __('Remote FTP Server', BUP_LANG_CODE),
            'remoteSFtp' => __('Remote SFTP(SCP) Server', BUP_LANG_CODE),
            'remoteFtpS' => __('Remote FTPS Server', BUP_LANG_CODE)
        );

        $sortNum = count($destinations);
        foreach($connectType as $type => $title){
            $sortNum++;
            $destinations[] = array(
                'title' => $title,
                'content' => $this->_msgForPro,
                'sortNum' => $sortNum,
                'key' => $type,
                'isAuthenticated' => 1,
                'msgForNotAuthenticated' => '',
            );
        }

        return $destinations;
    }
    public function getPromoSecretKeyEncryptDb() {
        return $this->_msgForPro;
    }
}