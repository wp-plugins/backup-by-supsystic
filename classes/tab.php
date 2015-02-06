<?php
/**
 * Abstract class of tab
 * 
 */
class tabBup {
   protected $_name = '';
   protected $_module = '';
   protected $_controller = '';
   protected $_view = '';
   protected $_sortOrder = false;
   protected $_parent = '';
   protected $_nestingLevel = 0;
   /**
	* Construct helper class
	* @param string $name 
	* @param string $module
	*/
   public function __construct($name, $module) {
	   $this->setName($name);
	   $this->setModule($module);
   }
   /**
	* Init function
	*/
   public function init(){

   }
   /**
	* Set the tab name
	* @param string $name 
	*/
   public function setName($name) {
	   $this->_name = $name;
   }
   /**
	* Get the tab name
	* @return string 
	*/
   public function getName() {
	   return $this->_name;
   }
   /**
	* Set the tab module
	* @param string $module 
	*/
   public function setModule($module) {
	   $this->_module = $module;
   }
   /**
	* Get the tab module
	* @return string 
	*/
   public function getModule() {
	   return $this->_module;
   }
   /**
	* Set the tab controller
	* @param string $controller 
	*/
   public function setController($controller) {
	   $this->_controller = $controller;
   }
   /**
	* Get the tab controller
	* @return string 
	*/
   public function getController() {
	   return $this->_controller;
   }
   /**
	* Set the tab view
	* @param string $view
	*/
   public function setView($view) {
	   $this->_view = $view;
   }
   /**
	* Get the tab view
	* @return string 
	*/
   public function getView() {
	   return $this->_view;
   }
   /**
	* Set tab ordering
	* @param numeric $order 
	*/
   public function setSortOrder($order) {
	   $this->_sortOrder = $order;
   }
   /**
	* Return ordering number for tab
	* @return numeric
	*/
   public function getSortOrder() {
	   return $this->_sortOrder;
   }

   public function setParent($code) {
	   $this->_parent = $code;
   }
   public function getParent() {
	   return $this->_parent;
   }

   public function setNestingLevel($nestingLevel) {
	   $this->_nestingLevel = $nestingLevel;
   }
   public function getNestingLevel() {
	   return $this->_nestingLevel;
   }
   public function getWidthPercentage() {
	   return (100 - ((float)$this->_nestingLevel*10));
   }
}

