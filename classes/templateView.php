<?php
class templateViewBup extends viewBup {
	protected $_styles = array();
	protected $_scripts = array();
	/**
	 * Provide or not html code of subscribe for to template. Can be re-defined for child classes
	 */
	protected $_useSubscribeForm = true;
	/**
	 * Provide or not html code of social icons for to template. Can be re-defined for child classes
	 */
	protected $_useSocIcons = true;
	public function getComingSoonPageHtml() {
		$this->_beforeShow();
		
		$this->assign('msgTitle', frameBup::_()->getModule('options')->get('msg_title'));
		$this->assign('msgTitleColor', frameBup::_()->getModule('options')->get('msg_title_color'));
		$this->assign('msgTitleFont', frameBup::_()->getModule('options')->get('msg_title_font'));
		$msgTitleStyle = array();
		if(!empty($this->msgTitleColor))
			$msgTitleStyle['color'] = $this->msgTitleColor;
		if(!empty($this->msgTitleFont)) {
			$msgTitleStyle['font-family'] = $this->msgTitleFont;
			$this->_styles[] = 'http://fonts.googleapis.com/css?family='. $this->msgTitleFont. '&subset=latin,cyrillic-ext';
		}
		$this->assign('msgTitleStyle', utilsBup::arrToCss( $msgTitleStyle ));
		
		$this->assign('msgText', frameBup::_()->getModule('options')->get('msg_text'));
		$this->assign('msgTextColor', frameBup::_()->getModule('options')->get('msg_text_color'));
		$this->assign('msgTextFont', frameBup::_()->getModule('options')->get('msg_text_font'));
		$msgTextStyle = array();
		if(!empty($this->msgTextColor))
			$msgTextStyle['color'] = $this->msgTextColor;
		if(!empty($this->msgTextFont)) {
			$msgTextStyle['font-family'] = $this->msgTextFont;
			if($this->msgTitleFont != $this->msgTextFont)
				$this->_styles[] = 'http://fonts.googleapis.com/css?family='. $this->msgTextFont. '&subset=latin,cyrillic-ext';
		}
		$this->assign('msgTextStyle', utilsBup::arrToCss( $msgTextStyle ));
		
		if($this->_useSubscribeForm && frameBup::_()->getModule('options')->get('sub_enable')) {
			$this->_scripts[] = frameBup::_()->getModule('subscribe')->getModPath(). 'js/frontend.subscribe.js';
			$this->assign('subscribeForm', frameBup::_()->getModule('subscribe')->getController()->getView()->getUserForm());
		}
		if($this->_useSocIcons) {
			$this->assign('socIcons', frameBup::_()->getModule('social_icons')->getController()->getView()->getFrontendContent());
		}
		
		if(file_exists($this->getModule()->getModDir(). 'css/style.css'))
			$this->_styles[] = $this->getModule()->getModPath(). 'css/style.css';
		
		$this->assign('logoPath', $this->getModule()->getLogoImgPath());
		$this->assign('bgCssAttrs', dispatcherBup::applyFilters('tplBgCssAttrs', $this->getModule()->getBgCssAttrs()));
		$this->assign('styles', dispatcherBup::applyFilters('tplStyles', $this->_styles));
		$this->assign('scripts', dispatcherBup::applyFilters('tplScripts', $this->_scripts));
		$this->assign('initJsVars', dispatcherBup::applyFilters('tplInitJsVars', $this->initJsVars()));
		$this->assign('messages', frameBup::_()->getRes()->getMessages());
		$this->assign('errors', frameBup::_()->getRes()->getErrors());
		return parent::getContent($this->getCode(). 'BUPHtml');
	}
	public function addScript($path) {
		if(!in_array($path, $this->_scripts))
			$this->_scripts[] = $path;
	}
	public function addStyle($path) {
		if(!in_array($path, $this->_styles))
			$this->_styles[] = $path;
	}
	public function initJsVars() {
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
			'BUP_CODE'					=> BUP_CODE,
		);
		return '<script type="text/javascript">
		// <!--
			var BUP_DATA = '. utilsBup::jsonEncode($jsData). ';
		// -->
		</script>';
	}
	protected function _beforeShow() {
		
	}
}