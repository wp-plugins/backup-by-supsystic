<?php
class pagesBup extends moduleBup {
    /**
     * Current Page data
     */
    protected $_current = null;
    
    public function __construct($d, $params = array()) {
        parent::__construct($d, $params);
        $this->_current = new stdClass();
    }
    public function parsePagesArgs($args) {
        $args['toeUseExcludePages'] = true;
        return $args;
    }
    public function getCurrent() {
        return $this->_current;
    }
    public function initPage($posts) {
        if(count($posts) == 1) {
            if($posts[0]->post_type == 'page') {
                if($page = $this->getByID($posts[0]->ID)) {
                    $this->_current = $page;
                    frameBup::_()->getModule($page->mod)->exec($page->action);
					remove_filter('the_content', 'wpautop');
                }
            }
        }
        return $posts;
    }
    public function getByID($id) {
        foreach($this->_params as $p) {
            if((int)$p->page_id == (int)$id)
                return $p;
        }
        return NULL;
    }
    public function getAll() {
        return $this->_params;
    }
    public function getLink($params = array('id' => 0, 'mod' => '', 'action' => '', 'data' => array())) {
        if($page = $this->getPage($params)) {
            $urlParams = array('page_id' => $page->page_id);
            if(!empty($params['data']) && is_array($params['data']))
                $urlParams = array_merge($urlParams, $params['data']);
            return uriBup::_($urlParams);
        }
        return '';
    }
    public function getPage($params = array('id' => 0, 'mod' => '', 'action' => '')) {
        foreach($this->_params as $key => $p) {
            if(($p->mod == $params['mod'] && $p->action == $params['action']) ||
                $p->page_id == $params['id']) {

                return $p;
            }
        }
        return NULL;
    }
    /**
     * Check if current page is page describet using mod and action params in $check variable
     * @see installerBup::init() - $pages variable
     */
    public function is($check = array()) {
        $currentPage = $this->getCurrent();
        if(is_object($currentPage) && isset($currentPage->page_id)) {
            if(isset($check['mod']) && isset($check['action']) && $check['mod'] == $currentPage->mod && $check['action'] == $currentPage->action) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if current page is Login page
     */
    public function isLogin() {
		return basename($_SERVER['SCRIPT_NAME']) == 'wp-login.php';
    }
}

