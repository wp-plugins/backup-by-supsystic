<?php
class frameBup {
    private $_modules = array();
    private $_tables = array();
    private $_allModules = array();
    /**
     * bool Uses to know if we are on one of the plugin pages
     */
    private $_inPlugin = false;
    /**
     * Array to hold all scripts and add them in one time in addScripts method
     */
    private $_scripts = array();
    private $_scriptsInitialized = false;
    private $_styles = array();
	private $_stylesInitialized = false;

    private $_scriptsVars = array();
    private $_mod = '';
    private $_action = '';
    /**
     * Object with result of executing non-ajax module request
     */
    private $_res = null;

    public function __construct() {
        $this->_res = toeCreateObjBup('response', array());

    }
    static public function getInstance() {
        static $instance;
        if(!$instance) {
            $instance = new frameBup();
        }
        return $instance;
    }
    static public function _() {
        return self::getInstance();
    }
    public function parseRoute() {
        // Check plugin
        $pl = reqBup::getVar('pl');
        if($pl == BUP_CODE) {
            $mod = reqBup::getMode();
            if($mod)
                $this->_mod = $mod;
            $action = reqBup::getVar('action');
            if($action)
                $this->_action = $action;
        }
    }
    public function setMod($mod) {
        $this->_mod = $mod;
    }
    public function getMod() {
        return $this->_mod;
    }
    public function setAction($action) {
        $this->_action = $action;
    }
    public function getAction() {
        return $this->_action;
    }
    protected function _extractModules() {
        $activeModules = $this->getTable('modules')
                ->innerJoin( $this->getTable('modules_type'), 'type_id' )
                ->get($this->getTable('modules')->alias(). '.*, '. $this->getTable('modules_type')->alias(). '.label as type_name');
        if($activeModules) {
            foreach($activeModules as $m) {
                $code = $m['code'];
                $moduleLocationDir = BUP_MODULES_DIR;
                if(!empty($m['ex_plug_dir'])) {
                    $moduleLocationDir = utilsBup::getExtModDir( $m['ex_plug_dir'] );
                }

                if(is_dir($moduleLocationDir. $code)) {
                    $this->_allModules[$m['code']] = 1;
                    if((bool)$m['active']) {
                        importClassBup($code. strFirstUp(BUP_CODE), $moduleLocationDir. $code. DS. 'mod.php');
                        $moduleClass = toeGetClassNameBup($code);
                        if(class_exists($moduleClass)) {
                            $this->_modules[$code] = new $moduleClass($m);
                            $this->_modules[$code]->setParams((array)json_decode($m['params']));
                            if(is_dir($moduleLocationDir. $code. DS. 'tables')) {
                                $this->_extractTables($moduleLocationDir. $code. DS. 'tables'. DS);
                            }
                        }
                    }
                }
            }
        }
        //$operationTime = microtime(true) - $startTime;
    }
    protected function _initModules() {
        if(!empty($this->_modules)) {
            foreach($this->_modules as $mod) {
                 $mod->init();
            }
        }
    }
    public function init() {
        //$startTime = microtime(true);
        langBup::init();
        reqBup::init();
        $this->_extractTables();
        $this->_extractModules();

        $this->_initModules();

        $this->_execModules();

        add_action('init', array($this, 'addScripts'));
        add_action('init', array($this, 'addStyles'));

        register_uninstall_hook(BUP_DIR. DS. BUP_MAIN_FILE, array('utilsBup', 'deletePlugin'));
        register_activation_hook( BUP_DIR. DS. BUP_MAIN_FILE, array('utilsBup', 'activatePlugin')  ); //See classes/install.php file

        add_action('admin_notices', array('errorsBup', 'displayOnAdmin'));
        add_action('init', array($this, 'connectLang'));
		//var_dump($this->getActivationErrors());
        //$operationTime = microtime(true) - $startTime;
    }

    public function connectLang() {
        load_plugin_textdomain(BUP_LANG_CODE, false, BUP_PLUG_NAME. '/lang');
    }

    /**
     * Check permissions for action in controller by $code and made corresponding action
     * @param string $code Code of controller that need to be checked
     * @param string $action Action that need to be checked
     * @return bool true if ok, else - should exit from application
     */
    public function checkPermissions($code, $action) {
        if($this->havePermissions($code, $action))
            return true;
        else {
            exit(_e('You have no permissions to view this page', BUP_LANG_CODE));
        }
    }
    /**
     * Check permissions for action in controller by $code
     * @param string $code Code of controller that need to be checked
     * @param string $action Action that need to be checked
     * @return bool true if ok, else - false
     */
    public function havePermissions($code, $action) {
        $res = true;
        $mod = $this->getModule($code);
        $action = strtolower($action);
        if($mod) {
            $permissions = $mod->getController()->getPermissions();
            if(!empty($permissions)) {  // Special permissions
                if(isset($permissions[BUP_METHODS])
                    && !empty($permissions[BUP_METHODS])

                ) {
                    foreach($permissions[BUP_METHODS] as $method => $permissions) {   // Make case-insensitive
                        $permissions[BUP_METHODS][strtolower($method)] = $permissions;
                    }
                    if(array_key_exists($action, $permissions[BUP_METHODS])) {        // Permission for this method exists
                        $currentUserPosition = $this->getCurrentUserPosition();
                        if((is_array($permissions[ BUP_METHODS ][ $action ]) && !in_array($currentUserPosition, $permissions[ BUP_METHODS ][ $action ]))
                            || (!is_array($permissions[ BUP_METHODS ][ $action ]) && $permissions[BUP_METHODS][$action] != $currentUserPosition)
                        ) {
                            $res = false;
                        }
                    }
                }
                if(isset($permissions[BUP_USERLEVELS])
                    && !empty($permissions[BUP_USERLEVELS])
                ) {
                    $currentUserPosition = $this->getCurrentUserPosition();
					// For multi-sites network admin role is undefined, let's do this here
					if(is_multisite() && is_admin()) {
						$currentUserPosition = BUP_ADMIN;
					}
                    foreach($permissions[BUP_USERLEVELS] as $userlevel => $methods) {
                        if(is_array($methods)) {
                            $lowerMethods = array_map('strtolower', $methods);          // Make case-insensitive
                            if(in_array($action, $lowerMethods)) {                      // Permission for this method exists
                                if($currentUserPosition != $userlevel)
                                    $res = false;
                                break;
                            }
                        } else {
                            $lowerMethod = strtolower($methods);            // Make case-insensitive
                            if($lowerMethod == $action) {                   // Permission for this method exists
                                if($currentUserPosition != $userlevel)
                                    $res = false;
                                break;
                            }
                        }
                    }
                }

            }
        }
        return $res;
    }
    public function getRes() {
        return $this->_res;
    }
    protected function _execModules() {
        if($this->_mod) {
            // If module exist and is active
            $mod = $this->getModule($this->_mod);
            if($mod && $this->_action) {
                if($this->checkPermissions($this->_mod, $this->_action)) {
                    switch(reqBup::getVar('reqType')) {
                        case 'ajax':
                            add_action('wp_ajax_'. $this->_action, array($mod->getController(), $this->_action));
							// All actions here - can be triggered only from admin area
                            //add_action('wp_ajax_nopriv_'. $this->_action, array($mod->getController(), $this->_action));
                            break;
                        default:
                            $this->_res = $mod->exec($this->_action);
                            break;
                    }
                }
            }
        }
    }
    protected function _extractTables($tablesDir = BUP_TABLES_DIR) {
        $mDirHandle = opendir($tablesDir);
        while(($file = readdir($mDirHandle)) !== false) {
            if(is_file($tablesDir. $file) && $file != '.' && $file != '..' && strpos($file, '.php')) {
                $this->_extractTable( str_replace('.php', '', $file), $tablesDir );
            }
        }
    }
    protected function _extractTable($tableName, $tablesDir = BUP_TABLES_DIR) {
        importClassBup('noClassNameHere', $tablesDir. $tableName. '.php');
        $this->_tables[$tableName] = tableBup::_($tableName);
        //var_dump($tableName, $this->_tables[$tableName]);
    }
    /**
     * public alias for _extractTables method
     * @see _extractTables
     */
    public function extractTables($tablesDir) {
        if(!empty($tablesDir))
            $this->_extractTables($tablesDir);
    }
    public function exec() {
        /**
         * @deprecated
         */
        /*if(!empty($this->_modules)) {
            foreach($this->_modules as $mod) {
                $mod->exec();
            }
        }*/
    }
    public function getTables () {
        return $this->_tables;
    }
    /**
     * Return table by name
     * @param string $tableName table name in database
     * @return tableBup table
     * @example frameBup::_()->getTable('products')->getAll()
     */
    public function getTable($tableName) {
        if(empty($this->_tables[$tableName])) {
            $this->_extractTable($tableName);
        }
        return $this->_tables[$tableName];
    }
    public function getModules($filter = array()) {
        $res = array();
        if(empty($filter))
            $res = $this->_modules;
        else {
            foreach($this->_modules as $code => $mod) {
                if(isset($filter['type'])) {
                    if(is_numeric($filter['type']) && $filter['type'] == $mod->getTypeID())
                        $res[$code] = $mod;
                    elseif($filter['type'] == $mod->getType())
                        $res[$code] = $mod;
                }
            }
        }
        return $res;
    }

    public function getModule($code) {
        /**@var moduleBup $module*/
        $module = (isset($this->_modules[$code]) ? $this->_modules[$code] : NULL);
        return $module;
    }
    public function inPlugin() {
        return $this->_inPlugin;
    }
    /**
     * Push data to script array to use it all in addScripts method
     * @see wp_enqueue_script definition
     */
    public function addScript($handle, $src = '', $deps = array(), $ver = false, $in_footer = false, $vars = array()) {
		$src = empty($src) ? $src : uriBup::_($src);
        if($this->_scriptsInitialized) {
            wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
        } else {
            $this->_scripts[] = array(
                'handle' => $handle,
                'src' => $src,
                'deps' => $deps,
                'ver' => $ver,
                'in_footer' => $in_footer,
                'vars' => $vars
            );
        }
    }
    /**
     * Add all scripts from _scripts array to wordpress
     */
    public function addScripts() {
        if(!empty($this->_scripts)) {
            foreach($this->_scripts as $s) {
                wp_enqueue_script($s['handle'], $s['src'], $s['deps'], $s['ver'], $s['in_footer']);

                if($s['vars'] || isset($this->_scriptsVars[$s['handle']])) {
                    $vars = array();
                    if($s['vars'])
                        $vars = $s['vars'];
                    if($this->_scriptsVars[$s['handle']])
                        $vars = array_merge($vars, $this->_scriptsVars[$s['handle']]);
                    if($vars) {
                        foreach($vars as $k => $v) {
                            wp_localize_script($s['handle'], $k, $v);
                        }
                    }
                }
            }
        }
        $this->_scriptsInitialized = true;
    }
    public function addJSVar($script, $name, $val) {
        if($this->_scriptsInitialized) {
            wp_localize_script($script, $name, $val);
        } else {
            $this->_scriptsVars[$script][$name] = $val;
        }
    }

    public function addStyle($handle, $src = false, $deps = array(), $ver = false, $media = 'all') {
		$src = empty($src) ? $src : uriBup::_($src);
		if($this->_stylesInitialized) {
			wp_enqueue_style($handle, $src, $deps, $ver, $media);
		} else {
			$this->_styles[] = array(
				'handle' => $handle,
				'src' => $src,
				'deps' => $deps,
				'ver' => $ver,
				'media' => $media
			);
		}
    }
    public function addStyles() {
        if(!empty($this->_styles)) {
            foreach($this->_styles as $s) {
                wp_enqueue_style($s['handle'], $s['src'], $s['deps'], $s['ver'], $s['media']);
            }
        }
		$this->_stylesInitialized = true;
    }
    //Very interesting thing going here.............
    public function loadPlugins() {
        require_once(ABSPATH. 'wp-includes/pluggable.php');
        //require_once(ABSPATH.'wp-load.php');
        //load_plugin_textdomain('some value');
    }
    public function loadWPSettings() {
        require_once(ABSPATH. 'wp-settings.php');
    }
    public function moduleActive($code) {
        return isset($this->_modules[$code]);
    }
    public function moduleExists($code) {
        if($this->moduleActive($code))
            return true;
        return isset($this->_allModules[$code]);
    }
    public function isTplEditor() {
        $tplEditor = reqBup::getVar('tplEditor');
        return (bool) $tplEditor;
    }

    /**
     * Checks if the the current page is plugin-menu or not
     * @return bool TRUE if current page is plugin-menu, FALSE otherwise.
     */
    public function isPluginAdminPage()
    {
        return (isset($_GET['page']) && $_GET['page'] === BUP_PLUGIN_PAGE_URL_SUFFIX);
    }
	public function isAdmin() {
		if(!function_exists('wp_get_current_user'))
			$this->loadPlugins();
        return current_user_can('manage_options');
    }
	public function getCurrentUserPosition() {
		if($this->isAdmin())
			return BUP_ADMIN;
		else if($this->getCurrentID())
			return BUP_LOGGED;
		else
			return BUP_GUEST;
	}
    public function getCurrentID() {
        if(!function_exists('wp_get_current_user'))
			$this->loadPlugins();
		$user = wp_get_current_user();
		return isset($user->data->ID) ? $user->data->ID : null;
    }

    public function humanSize($bytes) {
        $si_prefix = array('B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB');
        $base = 1024;
        $class = min((int)log($bytes , $base) , count($si_prefix) - 1);

        return sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
    }

    public static function devlog($data) {
        if (is_array($data)) {
            array_map(array('frameBup', 'devlog'), $data);
            return;
        }

        $type = gettype($data);

        error_log('(' . $type . ') ' . var_export($data, true), 4);
    }
	public function savePluginActivationErrors() {
		update_option(BUP_CODE. '_plugin_activation_errors',  ob_get_contents());
	}
	public function getActivationErrors() {
		return get_option(BUP_CODE. '_plugin_activation_errors');
	}
}
