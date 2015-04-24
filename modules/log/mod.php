<?php
class logBup extends moduleBup {
	
	/**
	 * Plugin initialization
	 */
    public function init() {
		parent::init();
		
		//dispatcherBup::addFilter('adminOptionsTabs', array($this, 'registerTab'));
	}
	
	/**
	 * Module tab registration
	 * @param  array $tabs
	 * @return array
	 */
	public function registerTab($tabs) {
		$tabs['bupLogOptions'] = array(
			'title'   => __('Restore', BUP_LANG_CODE),
			'content' => $this->run('indexAction'),
            'faIcon' => 'fa-file-text',
		);
		
		return $tabs;
	}
	
	public function run($action) {
		$controller = $this->getController();
		if (method_exists($controller, $action)) {
			return $controller->$action();
		}
	}
}