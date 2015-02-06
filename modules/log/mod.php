<?php
class logBup extends moduleBup {
	
	/**
	 * Menu tab config
	 * @var array
	 */
	private $tab = array(
		'key'    => 'bupLogOptions',
		'title'  => 'Log',
		'action' => 'indexAction',
	);
	
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
		$tabs[$this->tab['key']] = array(
			'title'   => $this->tab['title'],
			'content' => $this->run($this->tab['action']),
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