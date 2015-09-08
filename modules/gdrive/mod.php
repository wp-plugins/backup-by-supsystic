<?php

/**
 * Google Drive Module for Backup by Supsystic
 * @package BackupbySupsystic\Modules\GDrive
 * @version 1.3
 */
class gdriveBup extends moduleBup {

	/**
	 * Relative to current module path to Google API Client Library with
	 * trailing slash
	 * @var string
	 */
	private $GAPIPath = 'gapicl/';

	/**
	 * Module Configurations
	 * @since  1.1
	 * @var    array
	 */
	private $config = array(
		'tabs' => array(
			'key'    => 'bupGdriveOptions',
			'title'  => 'Google Drive',
			'action' => 'indexAction',
		),
		'storage' => array(
			'label'    => 'Google Drive',
			'provider' => 'gdrive',
			'action'   => 'uploadAction',
		),
	);

	/**
	 * Database options
	 * @since  1.1
	 * @var    array
	 */
	public $options = array(
        array(
            'code'        => 'gdrive_client_id',
            'value'       => '917290043125-534inl2ha2pdn641r2ebir1a1skme2qe.apps.googleusercontent.com',
            'label'       => 'Google Drive API Client ID',
            'description' => 'Client ID for web application',
        ),
        array(
            'code'        => 'gdrive_client_secret',
            'value'       => 'p92NzUx1n0rNKciQMd5MHm37',
            'label'       => 'Google Drive API Client secret',
            'description' => 'Client secret for web application',
        ),
		// Deprecated!
        array(
            'code'        => 'gdrive_auth_url',
            'value'       => 'http://supsystic.com/authenticator/index.php/authenticator/drive',
            'label'       => 'Authentication URL',
            'description' => 'Allows to authenticate users with our credentials (Use Redirect URI value here)',
        ),
	);

	/**
	 * Module initialization
	 *
	 * @since  1.1
	 * @return void
	 */
    public function init() {
		parent::init();

		// Check requirements and require Google API Client Library
		if(version_compare(PHP_VERSION, '5.2.1', '>=') && extension_loaded('json') === true) {
			if(is_dir($gapi = dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->GAPIPath) === true) {
				if(file_exists($client = $gapi . 'Google_Client.php')) {
					require $client;
				}
				if(file_exists($drive = $gapi . 'contrib/Google_DriveService.php')) {
					require $drive;
				}
				define('BUP_GAPI_SUPPORT', true);
			}
			else {
				define('BUP_GAPI_SUPPORT', false);
			}
		}

        if (is_admin() && frameBup::_()->isPluginAdminPage()) {
            frameBup::_()->addScript('adminGDriveOptions', $this->getModPath(). 'js/admin.gdrive.js');
        }

		dispatcherBup::addFilter('getBackupDestination', array($this, 'addGDriveBupDestination'));
		dispatcherBup::addFilter('adminSendToLinks', array($this, 'registerSendLink'));
		dispatcherBup::addfilter('adminBackupUpload', array($this, 'registerUploadMethod'));
		dispatcherBup::addfilter('adminGetUploadedFiles', array($this, 'getUploadedFiles'));
	}

	/**
	 * Module Installer
	 *
	 * @since  1.1
	 * @return void
	 */
	public function install() {
		parent::install();

		foreach($this->options as $options) {
			frameBup::_()->getTable('options')->insert($options);
		}
	}

	/**
	 * Register tab to plugin's menu
	 *
	 * @since  1.1
	 * @param  array $tabs
	 * @return array
	 */
	public function addGDriveBupDestination($tabs) {
		$tabs[] = array(
			'title'   => $this->config['tabs']['title'],
			'content' => $this->run($this->config['tabs']['action']),
            'faIcon' => 'fa-google',
            'sortNum' => 3,
            'key' => 'googledrive',
            'isAuthenticated' => $this->getController()->getModel()->isAuthenticated() ? 1 : 0,
            'msgForNotAuthenticated' => __('Before start backup - please authenticate with Google Drive.', BUP_LANG_CODE),
		);

		return $tabs;
	}

	/**
	 * Register "send to" link to storage
	 *
	 * @since  1.1
	 * @param  array $storageProviders
	 * @return array
	 */
	public function registerSendLink($storageProviders) {
		array_push($storageProviders, $this->config['storage']);

		return $storageProviders;
	}

	/**
	 * Add Google Drive handler to backup module
	 * @param  array $methods
	 * @return string
	 */
	public function registerUploadMethod($methods) {
		$methods['googledrive'] = array($this->getController()->getModel(), 'upload');
		return $methods;
	}

    /**
     * Run controller's action
     *
     * @since  1.1
     * @param  string $action
     * @return mixed
     */
    public function run($action) {
        $controller = $this->getController();
        if(method_exists($controller, $action)) {
            return $controller->{$action}();
        }
    }

    /**
     * Register uploaded files to backups page
     *
     * @param  array $files
     * @return array
     */
    public function getUploadedFiles($files) {
        if($this->getController()->getModel()->isSupported()){
            $uploadedFiles = $this->getController()->getModel()->getUploadedFiles();
            if(is_array($uploadedFiles)){
                foreach($uploadedFiles as $key=>$file){
                    if((isset($file['gdrive']['sql']['labels']['trashed']) && $file['gdrive']['sql']['labels']['trashed'] === false) || (isset($file['gdrive']['zip']['labels']['trashed']) && $file['gdrive']['zip']['labels']['trashed'] === false))
                        $files[$key] = $file;
                }
            }
        }
        return $files;
    }
}
