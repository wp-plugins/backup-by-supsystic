<?php
class templatesBup extends moduleBup {
    /**
     * Returns the available tabs
     * 
     * @return array of tab 
     */
    protected $_styles = array();
    public function getTabs(){
        $tabs = array();
        $tab = new tabBup(__('Templates', BUP_LANG_CODE), $this->getCode());
        $tab->setView('templatesTab');
		$tab->setSortOrder(1);
        $tabs[] = $tab;
        return $tabs;
    }
    public function init() {
		if (is_admin() && frameBup::_()->isPluginAdminPage()) {
			$this->_styles = array(
				'styleBup'				=> array('path' => BUP_CSS_PATH. 'style.css'), 
				'adminStylesBup'		=> array('path' => BUP_CSS_PATH. 'adminStyles.css'),
                'supsystic-uiBup'	    => array('path' => BUP_CSS_PATH. 'supsystic-ui.css'),
                'font-awesomeBup'	    => array('path' => BUP_CSS_PATH. 'font-awesome.css'),
				'jquery-tabs'			=> array('path' => BUP_CSS_PATH. 'jquery-tabs.css'),
				'jquery-buttons'		=> array('path' => BUP_CSS_PATH. 'jquery-buttons.css'),
                'icheck'			    => array('path' => BUP_CSS_PATH. 'jquery.icheck.css', 'for' => 'admin'),
                'tooltipster'		    => array('path' => BUP_CSS_PATH. 'tooltipster.css', 'for' => 'admin'),
				'wp-jquery-ui-dialog'	=> array(),
				'farbtastic'			=> array(),
				// Our corrections for ui dialog
				'jquery-dialog'			=> array('path' => BUP_CSS_PATH. 'jquery-dialog.css'),
				'jquery-progress'			=> array('path' => BUP_CSS_PATH. 'jquery-progress.css'),
			);
			$defaultPlugTheme = frameBup::_()->getModule('options')->get('default_theme');
			$ajaxurl = admin_url('admin-ajax.php');
			if(frameBup::_()->getModule('options')->get('ssl_on_ajax')) {
				$ajaxurl = uriBup::makeHttps($ajaxurl);
			}
			$jsData = array(
				'siteUrl'					=> BUP_SITE_URL,
				'imgPath'					=> BUP_IMG_PATH,
				'loader'					=> BUP_LOADER_IMG, 
				'close'						=> BUP_IMG_PATH. 'cross.gif', 
				'ajaxurl'					=> $ajaxurl,
				'animationSpeed'			=> frameBup::_()->getModule('options')->get('js_animation_speed'),
				'siteLang'					=> langBup::getData(),
				'options'					=> frameBup::_()->getModule('options')->getByCode(),
				'BUP_CODE'					=> BUP_CODE,
			);
			$jsData = dispatcherBup::applyFilters('jsInitVariables', $jsData);

			frameBup::_()->addScript('jquery');
			frameBup::_()->addScript('jquery-ui-tabs', '', array('jquery'));
			frameBup::_()->addScript('jquery-ui-dialog', '', array('jquery'));
			frameBup::_()->addScript('jquery-ui-button', '', array('jquery'));

			frameBup::_()->addScript('farbtastic');

			frameBup::_()->addScript('commonBup', BUP_JS_PATH. 'common.js');
			frameBup::_()->addScript('coreBup', BUP_JS_PATH. 'core.js');
            frameBup::_()->addScript('icheck', BUP_JS_PATH. 'icheck.min.js');
            frameBup::_()->addScript('tooltipster', BUP_JS_PATH. 'jquery.tooltipster.min.js');

            frameBup::_()->addScript('adminOptionsBup', BUP_JS_PATH. 'admin.options.js', array(), false, true);
            frameBup::_()->addScript('ajaxupload', BUP_JS_PATH. 'ajaxupload.js');
            frameBup::_()->addScript('postbox', get_bloginfo('wpurl'). '/wp-admin/js/postbox.js');

			frameBup::_()->addJSVar('coreBup', 'BUP_DATA', $jsData);

			/*$desktop = true;
			if(utilsBup::isTablet()) {
				$this->_styles['style-tablet'] = array();
				$desktop = false;
			} elseif(utilsBup::isMobile()) {
				$this->_styles['style-mobile'] = array();
				$desktop = false;
			}
			if($desktop) {
				$this->_styles['style-desctop'] = array();
			}*/

			foreach($this->_styles as $s => $sInfo) {
				if(isset($sInfo['for'])) {
					if(($sInfo['for'] == 'frontend' && is_admin()) || ($sInfo['for'] == 'admin' && !is_admin()))
						continue;
				}
				$canBeSubstituted = true;
				if(isset($sInfo['substituteFor'])) {
					switch($sInfo['substituteFor']) {
						case 'frontend':
							$canBeSubstituted = !is_admin();
							break;
						case 'admin':
							$canBeSubstituted = is_admin();
							break;
					}
				}
				if($canBeSubstituted && file_exists(BUP_TEMPLATES_DIR. $defaultPlugTheme. DS. $s. '.css')) {
					frameBup::_()->addStyle($s, BUP_TEMPLATES_PATH. $defaultPlugTheme. '/'. $s. '.css');
				} elseif($canBeSubstituted && file_exists(utilsBup::getCurrentWPThemeDir(). 'csp'. DS. $s. '.css')) {
					frameBup::_()->addStyle($s, utilsBup::getCurrentWPThemePath(). '/toe/'. $s. '.css');
				} elseif(!empty($sInfo['path'])) {
					frameBup::_()->addStyle($s, $sInfo['path']);
				} else {
					frameBup::_()->addStyle($s);
				}
			}
			add_action('wp_head', array($this, 'addInitJsVars'));
		}

        // Some common styles - that need to be on all admin pages - be careful with them
        frameBup::_()->addStyle('supsystic-for-all-admin-' . BUP_CODE, BUP_CSS_PATH . 'supsystic-for-all-admin.css');
        parent::init();
    }
	/**
	 * Some JS variables should be added after first wordpress initialization.
	 * Do it here.
	 */
	public function addInitJsVars() {
		/*frameBup::_()->addJSVar('adminOptions', 'BUP_PAGES', array(
			'isCheckoutStep1' => frameBup::_()->getModule('pages')->isCheckoutStep1(),
			'isCart' => frameBup::_()->getModule('pages')->isCart(),
		));*/
	}
}
