<?php
abstract class moduleBup extends baseObjectBup {
	protected $_controller = NULL;
	protected $_helper = NULL;
	protected $_code = '';
	protected $_onAdmin = false;
	protected $_params = array();
	protected $_typeID = 0;
	protected $_type = '';
	protected $_label = '';
	protected $_description = '';
	/*
	 * ID in modules table
	 */
	protected $_id = 0;
	/**
	 * If module is not in primary package - here wil be it's path
	 */
	protected $_externalDir = '';
	protected $_externalPath = '';
	protected $_isExternal = false;

	public function __construct($d, $params = array()) {
		$this->setTypeID($d['type_id']);
		$this->setType($d['type_name']);
		$this->setCode($d['code']);
		$this->setLabel($d['label']);
		$this->setDescription($d['description']);
		$this->setParams($d['params']);
		$this->_setID($d['id']);
		if(isset($d['ex_plug_dir']) && !empty($d['ex_plug_dir'])) {
			$this->isExternal(true);
			$this->setExternalDir( utilsBup::getExtModDir($d['ex_plug_dir']) );
			$this->setExternalPath( utilsBup::getExtModPath($d['ex_plug_dir']) );
		}
	}
	public function isExternal($newVal = NULL) {
		if(is_null($newVal))
			return $this->_isExternal;
		$this->_isExternal = $newVal;
	}
	public function getModDir() {
		if(empty($this->_externalDir)) {
			return BUP_MODULES_DIR. $this->getCode(). DS;
		} else {
			return $this->_externalDir. $this->getCode(). DS;
		}
	}
	public function getModPath() {
		if(empty($this->_externalPath)) {
			return BUP_MODULES_PATH. $this->getCode(). '/';
		} else {
			return $this->_externalPath. $this->getCode(). '/';
		}
	}
	public function getModRealDir() {
		return dirname(__FILE__). DS;
	}
	public function setExternalDir($dir) {
		$this->_externalDir = $dir;
	}
	public function getExternalDir() {
		return $this->_externalDir;
	}
	public function setExternalPath($path) {
		$this->_externalPath = $path;
	}
	public function getExternalPath() {
		return $this->_externalPath;
	}
	/*
	 * Set ID for module, protected - to limit opportunity change this value
	 */
	protected function _setID($id) {
		$this->_id = $id;
	}
	/**
	 * Get module ID from modules table in database
	 * @return int ID of module
	 */
	public function getID() {
		return $this->_id;
	}
	public function setTypeID($typeID) {
		$this->_typeID = $typeID;
	}
	public function getTypeID() {
		return $this->_typeID;
	}
	public function setType($type) {
		$this->_type = $type;
	}
	public function getType() {
		return $this->_type;
	}
	public function getLabel() {
		return $this->_label;
	}
	public function setLabel($label) {
		$this->_label = $label;
	}
	public function getDescription() {
		return $this->_description;
	}
	public function setDescription($desc) {
		$this->_description = $desc;
	}
	public function init() {

	}
	public function exec($task = '') {
		if($task) {
			if($controller = $this->getController()) {
				return $controller->exec($task);
			}
		}
		return null;
	}
	public function getController() {
		if(!$this->_controller) {
			$this->_createController();
		}
		return $this->_controller;
	}
	protected function _createController() {
		if(!file_exists($this->getModDir(). 'controller.php')) {
			return false;	// EXCEPTION!!!
		}
		if($this->_controller) return true;
		if(file_exists($this->getModDir(). 'controller.php')) {
			$className = '';
			if(import($this->getModDir(). 'controller.php')) {
				$className = toeGetClassNameBup($this->getCode(). 'Controller');
			}
			if(!empty($className)) {
				$this->_controller = new $className($this->getCode());
				$this->_controller->init();
				return true;
			}
		}
		return false;
	}
	/**
	 * Method to call module helper if it exists
	 * @return class helperBup 
	 */
	public function getHelper() {
		if (!$this->_helper)
			$this->_createHelper();
		return $this->_helper;
	}
	/**
	 * Method to create class of module helper
	 * @return class helperBup 
	 */
	protected function _createHelper() {
		if ($this->_helper) return true;
		if (file_exists($this->getModDir().'helper.php')) {
			$helper = $this->getCode().'Helper';
			importClassBup($helper, $this->getModDir(). 'helper.php');
			if (class_exists($helper)) {
				$this->_helper = new $helper($this->_code);
				$this->_helper->init();
				return true;
			}
		}
	}
	public function setCode($code) {
		$this->_code = $code;
	}
	public function getCode() {
		return $this->_code;
	}
	public function onAdmin() {
		return $this->_onAdmin;
	}
	public function __call($name, $arguments) {
		$controller = $this->getController();
		if(method_exists($controller, $name)) {     //try to find this method in controller
			return $this->getController()->$name(
						isset($arguments[0]) ? $arguments[0] : NULL,
						isset($arguments[0]) ? $arguments[0] : NULL,
						isset($arguments[0]) ? $arguments[0] : NULL
					);
		} elseif($controller) {                                    //try to find this method in model
			$model = $controller->getModel();
			if(method_exists($model, $name)) {
				return $this->getController()->$name(
						isset($arguments[0]) ? $arguments[0] : NULL,
						isset($arguments[0]) ? $arguments[0] : NULL,
						isset($arguments[0]) ? $arguments[0] : NULL
					);
			}
		}
		errorsBup::push(langBup::_(array('Module', $this->_code, 'method', $name, 'undefined')), errorsBup::FATAL);
	}
	public function setParams($params) {
		if(!is_array($params)) {
			if(empty($params))
				$params = array();
			else {
				$params = json_decode ($params);
			}
		}
		$this->_params = $params;
	}
	public function getParams($key = NULL) {
		if(is_null($key))
			return $this->_params;
		else if(is_numeric($key) && isset($this->_params[ $key ])) {
			return $this->_params[ $key ];
		} else {
			foreach($this->_params as $p) {
				if(isset($p->$key))
					return $p->$key;
			}
			return false;
		}
	}
	/**
	 * Retrive one parameter using it's key, alias for getParams() method
	 */
	public function getParam($key) {
		return $this->getParams($key);
	}
	public function install() {

	}
	public function uninstall() {

	}
	/**
	 * Returns the available tabs
	 * @return array of tab
	 */
	public function getTabs() {
		return array();
	}
	public function getConstant($name) {
		$thisClassRefl = new ReflectionObject($this);
		return $thisClassRefl->getConstant($name);
	} 
}
