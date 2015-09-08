<?php

/**
 * Amazon Module for Backup By Supsystic
 * @package BackupBySupsystic\Modules\Amazon
 * @version 1.1
 */
class amazonBup extends moduleBup {
    
    /**
     * Tab config
     * @since  1.1
     * @var    array
     */
    private $config = array(
        'key'    => 'bupAmazonS3Options',
        'title'  => 'Amazon S3',
        'action' => 'indexAction',
    );
 
    /**
     * "Send to" link config
     * @since  1.1
     * @var    array
     */
    private $storageConfig = array(
        'label'    => 'Amazon S3',
        'provider' => 'amazon',
        'action'   => 'uploadAction',
    );

    /**
     * Path to Amazon Web Services SDK
     * Path must be relative to current module w\ trailing slash
     * 
     * @var string
     */
    private $sdkPath = 'aws/';

    /**
     * Module options
     * @since  1.1
     * @var    array
     */
    private $options = array(
        array(
            'code'        => 'aws_access_key',
            'value'       => '',
            'label'       => 'AWS Access Key',
            'description' => 'Amazon Web Services Access Key to work with the Amazon S3',            
        ),
        array(
            'code'        => 'aws_secret_key',
            'value'       => '',
            'label'       => 'AWS Secret Key',
            'description' => 'Amazon Web Services Secret Key to work with Amazon S3',
        ),
        array(
            'code'        => 'aws_s3_bucket',
            'value'       => '',
            'label'       => 'S3 Bucket',
            'description' => 'Name of bucket to upload backups',
        ),
    );
    
    /**
     * Initialize module
     *
     * @since  1.1
     * @see    http://docs.aws.amazon.com/aws-sdk-php/guide/latest/requirements.html
     * @return void
     */
    public function init() {
        parent::init();

        // Require AWS SDK and check server software
        if(version_compare(PHP_VERSION, '5.3.3', '>=') && extension_loaded('curl')) {
            if(is_dir($sdk = dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->sdkPath) === true
                && file_exists($sdkAutoloader = $sdk . 'aws-autoloader.php')) 
            {
                require $sdkAutoloader;
                define('BUP_S3_SUPPORT', true);
            }
        }
        else {
            define('BUP_S3_SUPPORT', false);
        }

        if (version_compare(PHP_VERSION, '5.3.3', '<')) {
            dispatcherBup::addFilter('getBackupDestination', array($this, 'registerNotSupportTab'));
            return;
        }

        if (is_admin() && frameBup::_()->isPluginAdminPage()) {
            frameBup::_()->addScript('adminAmazonHandle', $this->getModPath() . 'js/admin.amazon.js');
        }

        // Register backup destination
        dispatcherBup::addFilter('getBackupDestination', array($this, 'addAmazonBupDestination'));
        // Register "send to" link
        dispatcherBup::addFilter('adminSendToLinks', array($this, 'registerSendLink'));
		dispatcherBup::addfilter('adminBackupUpload', array($this, 'registerUploadMethod'));
        dispatcherBup::addfilter('adminGetUploadedFiles', array($this, 'getUploadedFiles'));
    }
    
    public function registerNotSupportTab($tabs) {
        $tabs[] = array(
            'title'   => $this->config['title'],
            'content' => __(sprintf('To use this module you need '
                    . 'PHP version <code>5.3.3</code> or higher, your PHP version: '
                    . '<code>%s</code>', PHP_VERSION), BUP_LANG_CODE),
            'faIcon' => 'fa-font',
            'sortNum' => 5,
            'key' => 'amazon',
        );

        return $tabs;
    }

    /**
     * Register module tab in plugin menu
     *
     * @since  1.1
     * @param  array $tabs
     * @return array
     */
    public function addAmazonBupDestination($tabs) {
        $tabs[] = array(
            'title'   => $this->config['title'],
            'content' => $this->run($this->config['action']),
            'faIcon' => 'fa-font',
            'sortNum' => 5,
            'key' => 'amazon',
            'isAuthenticated' => $this->getController()->getModel()->isUserAuthorizedInService() ? 1 : 0,
            'msgForNotAuthenticated' => __('Before start backup - please authenticate with Amazon.', BUP_LANG_CODE),
        );

        return $tabs;
    }

    /**
     * Register 'send to' link to the storage module
     *
     * @since  1.1
     * @param $storageProviders
     * @return void
     */
    public function registerSendLink($storageProviders) {
        array_push($storageProviders, $this->storageConfig);
        return $storageProviders;
    }

    /**
     * Install module's options
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
	 * Add Amazon S3 handler to backup module
	 * @param  array $methods
	 * @return string
	 */
	public function registerUploadMethod($methods) {
		$methods['amazon'] = array($this->getController()->getModel(), 'upload');
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
        if(BUP_S3_SUPPORT && $this->getController()->getModel()->isCredentialsSaved()){
            try {
                $uploadedFiles = $this->getController()->getModel()->getUploadedFiles();
                if (is_array($uploadedFiles)) {
                    foreach ($uploadedFiles as $key => $file) {
                        $files[$key] = $file;
                    }
                }
            } catch (Exception $e) {
                return $files;
            }
        }
        return $files;
    }
}