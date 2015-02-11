<?php

/**
 * Dropbox Module for Backup By Supsystic
 * @package BackupBySupsystic\Modules\Dropbox
 * @version 1.0
 */
class dropboxBup extends moduleBup {

	/**
	 * Module configurations
	 * @since 1.0
	 * @var   array
	 */
	private $config = array(
		'tabs' => array(
			'key'    => 'bupDropboxOptions',
			'title'  => 'Dropbox',
			'action' => 'indexAction',
		),
		'storage' => array(
			'label'    => 'Dropbox',
			'provider' => 'dropbox',
			'action'   => 'uploadAction',
		),
	);

	/**
	 * Database options
	 * @since 1.0
	 * @var   string
	 */
	private $options = array(
		array(
			'code'        => 'dropbox_app_key',
			'value'       => 'uam2sj2hctayn66',
			'label'       => 'Dropbox Application Key',
			'description' => 'Dropbox Application Key',
		),
		array(
			'code'        => 'dropbox_app_secret',
			'value'       => 'nznkl0jasyygrkz',
			'label'       => 'Dropbox Application Secret',
			'description' => 'Dropbox Application Secret',
		),
		// Deprecated
        array(
            'code'        => 'dropbox_auth_url',
            'value'       => 'http://supsystic.com/authenticator/index.php/authenticator/dropbox/',
            'label'       => 'Authenticator URL',
            'description' => 'URL to authenticate user with out authenticator',
        ),
        array(
            'code'        => 'dropbox_model',
            'value'       => '',
            'label'       => 'Dropbox model',
            'description' => 'Module uses two models: for PHP 5.2.x and for PHP >= 5.3.x',
        ),
	);

	/**
	 * Relative path to Dropbox SDK
	 */
	private $sdkPath = 'sdk/';

	/**
	 * Module installer
	 *
	 * @since  1.0
	 */
	public function install() {
		parent::install();

		foreach($this->options as $options) {
			frameBup::_()->getTable('options')->insert($options);
		}
	}

	/**
	 * Module initialization
	 *
	 * @since  1.0
	 */
	public function init() {
		parent::init();
        if (!extension_loaded('curl')) {
            dispatcherBup::addFilter('adminCloudServices', array($this, 'registerTabNotSupport'));
            return;
        }

        $curl = curl_version();

		if((version_compare(PHP_VERSION, '5.3.1', '>=') &&
				(substr($curl['ssl_version'], 0, 3) != 'NSS')) && PHP_INT_MAX > 2147483647)
		{
			require $this->sdkPath . 'autoload.php';

            require dirname(__FILE__) . '/classes/curlBup.php';
            frameBup::_()->getModule('options')->set('dropbox', 'dropbox_model');
        }
		else {
            require dirname(__FILE__) . '/classes/curlBup.php';
            $this->getController()->modelType = 'dropbox52';
            frameBup::_()->getModule('options')->set('dropbox52', 'dropbox_model');
		}

        frameBup::_()->getModule('options')->set('dropbox52', 'dropbox_model');

        dispatcherBup::addFilter('adminCloudServices', array($this, 'registerTab'));
        dispatcherBup::addFilter('adminSendToLinks', array($this, 'registerSendLink'));
        dispatcherBup::addfilter('adminBackupUpload', array($this, 'registerUploadMethod'));

	}

	public function registerTabNotSupport($tabs) {
		$tabs[$this->config['tabs']['key']] = array(
			'title'   => $this->config['tabs']['title'],
			'content' => langbup::_('Your server does not support the Dropbox without cURL extension'),
            'faIcon' => 'fa-dropbox',
		);

		return $tabs;
	}

	/**
	 * Register Dropbox tab in plugin menu
	 *
	 * @since  1.0
	 * @param  array $tabs
	 * @return array
	 */
	public function registerTab($tabs) {

		if(is_admin() && frameBup::_()->isPluginAdminPage()) {
			frameBup::_()->addScript('adminDropboxOptions', $this->getModPath() . 'js/admin.dropbox.js');
		}

		$tabs[$this->config['tabs']['key']] = array(
			'title'   => $this->config['tabs']['title'],
			'content' => $this->run($this->config['tabs']['action']),
            'faIcon' => 'fa-dropbox',
		);

		return $tabs;
	}

	/**
	 * Register "Send to" link to storage module
	 *
	 * @since  1.0
	 * @param  array $providers
	 * @return array
	 */
	public function registerSendLink($providers) {
		array_push($providers, $this->config['storage']);
		return $providers;
	}

	/**
	 * Add Dropbox handler to backup module
	 * @param  array $methods
	 * @return string
	 */
	public function registerUploadMethod($methods) {
        $model = frameBup::_()->getModule('options')->get('dropbox_model');
		$methods['dropbox'] = array($this->getController()->getModel($model), 'upload');
		return $methods;
	}

    /**
     * Run controller's action
     *
     * @since  1.0
     * @param  string $action
     * @return mixed
     */
    public function run($action) {
        $controller = $this->getController();
        if(method_exists($controller, $action)) {
            return $controller->{$action}();
        }
    }
}
