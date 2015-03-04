<?php
    global $wpdb;
    if (!defined('WPLANG') || WPLANG == '') {
        define('BUP_WPLANG', 'en_GB');
    } else {
        define('BUP_WPLANG', WPLANG);
    }
    if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);


    $wpContentArray = explode('/', content_url());
    if(count($wpContentArray) < 2)
        $wpContentArray = explode('\\', content_url());
    $wpContentFolder = array_pop($wpContentArray);
    define('BUP_WP_CONTENT_DIR', $wpContentFolder);

    define('BUP_PLUG_NAME', basename(dirname(__FILE__)));
	define('BUP_PLUG_NAME_PRO', 'supsystic-backup-pro');
    define('BUP_DIR', WP_PLUGIN_DIR. DS. BUP_PLUG_NAME. DS);
    define('BUP_TPL_DIR', BUP_DIR. 'tpl'. DS);
    define('BUP_CLASSES_DIR', BUP_DIR. 'classes'. DS);
    define('BUP_TABLES_DIR', BUP_CLASSES_DIR. 'tables'. DS);
	define('BUP_HELPERS_DIR', BUP_CLASSES_DIR. 'helpers'. DS);
	define('BUP_GLIB_DIR', BUP_HELPERS_DIR. 'googlelib'. DS);
	define('BUP_TOKEN_DIR', BUP_HELPERS_DIR. 'tokens'. DS);
    define('BUP_LANG_DIR', BUP_DIR. 'lang'. DS);
    define('BUP_IMG_DIR', BUP_DIR. 'img'. DS);
    define('BUP_TEMPLATES_DIR', BUP_DIR. 'templates'. DS);
    define('BUP_MODULES_DIR', BUP_DIR. 'modules'. DS);
    define('BUP_FILES_DIR', BUP_DIR. 'files'. DS);
    define('BUP_ADMIN_DIR', ABSPATH. 'wp-admin'. DS);
	define('BUP_S_WP_PLUGIN_NAME', 'Backup by Supsystic');


    define('BUP_SITE_URL', get_bloginfo('wpurl'). '/');
    define('BUP_JS_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/js/');
    define('BUP_CSS_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/css/');
    define('BUP_IMG_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/img/');
    define('BUP_MODULES_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/modules/');
    define('BUP_TEMPLATES_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/templates/');
    define('BUP_IMG_POSTS_PATH', BUP_IMG_PATH. 'posts/');
    define('BUP_JS_DIR', BUP_DIR. 'js/');
    define('BUP_PLUGIN_PAGE_URL_SUFFIX', 'supsystic-backup');

    define('BUP_URL', BUP_SITE_URL);

    define('BUP_LOADER_IMG', BUP_IMG_PATH. 'loading.gif');
    define('BUP_DATE_DL', '/');
    define('BUP_DATE_FORMAT', 'd/m/Y');
    define('BUP_DATE_FORMAT_HIS', 'd/m/Y (H:i:s)');
    define('BUP_DATE_FORMAT_JS', 'dd/mm/yy');
    define('BUP_DATE_FORMAT_CONVERT', '%d/%m/%Y');
    define('BUP_WPDB_PREF', $wpdb->prefix);
    define('BUP_DB_PREF', 'bup_');    /*BackUP*/
    define('BUP_MAIN_FILE', 'backup-supsystic.php');

    define('BUP_DEFAULT', 'default');
    define('BUP_CURRENT', 'current');


    define('BUP_PLUGIN_INSTALLED', true);
    define('BUP_VERSION', '1.0.7');
	define('BUP_S_VERSION', BUP_VERSION);
    define('BUP_USER', 'user');


    define('BUP_CLASS_PREFIX', 'bup');
    define('BUP_FREE_VERSION', false);
	define('BUP_TEST_MODE', true);
	
    define('BUP_SUCCESS', 'Success');
    define('BUP_FAILED', 'Failed');
	define('BUP_ERRORS', 'bupErrors');

	define('BUP_THEME_MODULES', 'theme_modules');


	define('BUP_ADMIN',	'admin');
	define('BUP_LOGGED','logged');
	define('BUP_GUEST',	'guest');

	define('BUP_ALL', 'all');

	define('BUP_METHODS',		'methods');
	define('BUP_USERLEVELS',	'userlevels');
	/**
	 * Framework instance code, unused for now
	 */
	define('BUP_CODE', 'bup');

    /** Files per stack in filesystem backup */
    define('BUP_FILES_PER_STACK', 500);

	//define('PCLZIP_TEMPORARY_DIR', '/usr/www/temp/');
	//require_once(BUP_HELPERS_DIR. 'pclzip.lib.php');

	//define('BUP_SIZE_REQUEST', 4194304); // 1M  //2097152 //4194304 //8388608
	define('BUP_MAX_FILE_REQUEST', 500);

    define('BUP_LOCK_FIELD', 'bup_locked');
