<?php

/**
 * Dropbox Model
 * @package BackupBySupsystic\Modules\Dropbox
 * @version 1.2
 */
class dropboxModelBup extends modelBup {

	/**
	 * Database options fields
	 */
	const APP_KEY_INDEX    = 'dropbox_app_key';
	const APP_SECRET_INDEX = 'dropbox_app_secret';

	/**
	 * Key to store token to the session
	 */
	const TOKEN_SESS_NAME  = 'dropbox_token';

	/**
	 * Path to backups on server
	 * System will set this value automatically
	 * @var string
	 */
	private $backupsPath = null;

	/**
	 * Like user-agent for Dropbox Core API :)
	 * @var string
	 */
	private $applicationName = 'Backup By Supsystic';
	/**
	 * Returns Dropbox SDK support value
	 * @return boolean
	 */
	public function isSupported() {
		if(!defined('BUP_DROPBOX_SUPPORT')) {
			return false;
		}

		return BUP_DROPBOX_SUPPORT;
	}

	/**
	 * Returns credential by key
	 *
	 * @since  1.0
	 * @param  string $key 'key' or 'secret'
	 * @return type
	 */
	public function getCredential($key) {
		$key = ($key == 'key' ? self::APP_KEY_INDEX : self::APP_SECRET_INDEX);
		return frameBup::_()->getModule('options')->get($key);
	}

	/**
	 * Returns array with credentials
	 *
	 * @since  1.0
	 * @return array
	 */
	public function getCredentials() {
		return array(
			'app_key'    => $this->getCredential('key'),
			'app_secret' => $this->getCredential('secret'),
		);
	}


	/**
	 * Is authenticated client?
	 *
	 * @since  1.0
	 * @return boolean
	 */
	public function isAuthenticated() {
		/*if(isset($_SESSION[self::TOKEN_SESS_NAME]) && !empty($_SESSION[self::TOKEN_SESS_NAME])) {
			return true;
		}

        if (null !== $this->readToken()) {
            return true;
        }*/
		$token = $this->getToken();
		return !empty($token);

		//return false;
	}

	/**
	 * Authenticate client with OAuth2
	 *
	 * @since  1.0
	 * @param  string $token
	 * @return boolean
	 */
	public function authenticate($token) {
		try {
			$_SESSION[self::TOKEN_SESS_NAME] = $token;
            $this->saveToken($token);
			return true;
		} catch (Exception $ex) {
			$this->pushError($ex->getMessage());
			return false;
		}
	}

    /**
     * Returns fullpath to folder where plugin store backups
     *
     * @since  1.0
     * @uses   frameBup
     * @uses   optionsBup
     * @return string
     */
    public function getBackupsPath() {
        if($this->backupsPath === null) {
            $this->backupsPath = frameBup::_()->getModule('warehouse')->getPath()
				. DIRECTORY_SEPARATOR;
        }

        return $this->backupsPath;
    }

    /**
     * Upload files to dropbox
     *
     * @since  1.0
     * @uses   \Dropbox\Client
     * @see    https://www.dropbox.com/developers/core/start/php#uploading
     * @param  array $files
     * @param bool $offline
     * @return integer
     */
	public function upload($files = array(), $offline = false) {
		if($this->isAuthenticated() === false) {
			return 401;
		}

		if(empty($files)) {
			return 404;
		}

		$filepath = $this->getBackupsPath();

        /*if (null === $token = $this->readToken()) {
            $token = $_SESSION[self::TOKEN_SESS_NAME];
        }*/
		$token = $this->getToken();
		$client   = new Dropbox\Client($token, $this->applicationName);

		foreach($files as $file) {

            $file = basename($file);

			if(file_exists($filepath . $file))
			{
				try {
					$stream = @fopen($filepath . $file, 'rb');
                    $client->uploadFile(
                        $this->getDropboxPath() . $file,
                        Dropbox\WriteMode::add(),
                        $stream
                    );
					fclose($stream);
				} catch(Exception $ex) {
					$this->pushError($ex->getMessage());
					return 500;
				}
			}
		}

		return 200;
	}

	/**
	 * Returns uploaded file to plugin's root folder
	 *
	 * @since  1.0
	 * @uses   \Dropbox\Client
	 * @see    https://www.dropbox.com/developers/core/start/php#listing
	 * @return null|array
	 */
	public function getUploadedFiles() {
		// never executed, but dont remove, ok?
		if($this->isAuthenticated() === false) {
			return null;
		}

       // try {
        $client = new Dropbox\Client($this->getToken(), $this->applicationName);
        $response = $client->getMetadataWithChildren(rtrim($this->getDropboxPath(), '/'));

        // Formatting uploading data files for use their on backups page
        $files = array();
        foreach ($response['contents'] as $file) {
            $pathInfo = pathinfo($file['path']);
            $backupInfo = $this->getBackupInfoByFilename($pathInfo['basename']);

            if(!empty($backupInfo['ext']) && $backupInfo['ext'] == 'sql'){
                $files[$backupInfo['id']]['dropbox']['sql'] = $file;
                $files[$backupInfo['id']]['dropbox']['sql']['backupInfo'] = $backupInfo;
                $files[$backupInfo['id']]['dropbox']['sql']['backupInfo'] = dispatcherBup::applyFilters('addInfoIfEncryptedDb', $files[$backupInfo['id']]['dropbox']['sql']['backupInfo']);
            }elseif(!empty($backupInfo['ext']) && $backupInfo['ext'] == 'zip'){
                $files[$backupInfo['id']]['dropbox']['zip'] = $file;
                $files[$backupInfo['id']]['dropbox']['zip']['backupInfo'] = $backupInfo;
            }
        }
        unset($response['contents']);
        $response['contents']= $files;

        return $response;
       // } catch (Exception $e) {
            //echo sprintf('Dropbox client error: %s. Try to refresh page', $e->getMessage());
        //}
	}

	/**
	 * Delete file on Dropbox
	 *
	 * @since  1.0
	 * @uses   \Dropbox\Client
	 * @see    https://www.dropbox.com/developers/core/docs#fileops-delete
	 * @see    http://dropbox.github.io/dropbox-sdk-php/api-docs/v1.1.x/source-class-Dropbox.Client.html#1274-1303
	 * @param  string $filepath
	 * @return boolean
	 */
	public function remove($filepath) {
		$client = new Dropbox\Client($this->getToken(), $this->applicationName);

		try {
			$client->delete($filepath);
			return true;
		} catch (Exception $ex) {
			$this->pushError($ex->getMessage());
			return false;
		}
	}

	/**
	 * Download file from Dropbox
	 *
	 * @since  1.0
	 * @uses   \Dropbox\Client
	 * @see    http://dropbox.github.io/dropbox-sdk-php/api-docs/v1.1.x/source-class-Dropbox.Client.html#162-216
	 * @param  string $filename
	 * @return boolean
	 */
	public function download($filename, $returnDataString = false) {
		if($this->isAuthenticated() === false) {
			$this->pushError(__('Authentication required', BUP_LANG_CODE));
			return false;
		}

		if(file_exists($this->getBackupsPath() . $filename)) {
            return $returnDataString ? file_get_contents($this->getBackupsPath() . $filename) : true;
		}

		try {
			$client = new Dropbox\Client($this->getToken(), $this->applicationName);

            if($returnDataString){
                $dataString = null;
                $client->getFile($this->getDropboxPath() . $filename, $dataString);
                return $dataString;
            }

			$stream = @fopen($this->getBackupsPath() . $filename, 'wb');
			$result = $client->getFile($this->getDropboxPath() . $filename, $stream);
			fclose($stream);

			if($result === null) {
				$this->pushError(__('File not found', BUP_LANG_CODE));
				return false;
			}

			return true;
		} catch(Exception $ex) {
			$this->pushError($ex->getMessage());
			return false;
		}

	}

	/**
	 * Get account information
	 *
	 * @since  1.1
	 * @return null|array
	 */
	public function getUserinfo() {
		if($this->isAuthenticated() === false) {
			return null;
		}

		$client = new Dropbox\Client($this->getToken(), $this->applicationName);
		return $client->getAccountInfo();
	}

	/**
	 * Get used and available quota and free space percent
     *
     * @since  1.2
     * @return array
	 */
	public function getQuota() {
        return array();
	}

    public function removeToken()
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        if (false !== $expired = glob($storage . '/dropbox*.json')) {
            if (is_array($expired) && count($expired) > 0) {
                foreach ($expired as $file) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Saves the token
     *
     * @param string $token
     */
    protected function saveToken($token)
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        $this->removeToken();

        file_put_contents($storage . '/' . uniqid('dropbox') . '.json', $token);
    }

    /**
     * Reads the token
     */
    protected function readToken()
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        if (false !== $token = glob($storage . '/dropbox*.json')) {
            if (is_array($token) && count($token) === 1) {
                return file_get_contents($token[0]);
            }
        }

        return null;
    }

    protected function getDomainName()
    {
        return parse_url(get_bloginfo('wpurl'), PHP_URL_HOST);
    }

    protected function getDropboxPath()
    {
        return '/' . $this->getDomainName() . '/';
    }
	protected function getToken()
    {
		if(isset($_SESSION[self::TOKEN_SESS_NAME]) && !empty($_SESSION[self::TOKEN_SESS_NAME])) {
			return $_SESSION[self::TOKEN_SESS_NAME];
		}

        if (null !== ($token = $this->readToken())) {
            return $token;
        }

		return false;
	}
    public function isUserAuthorizedInService($destination = null)
    {
        $isAuthorized = $this->isAuthenticated() ? true : false;
        if(!$isAuthorized)
            $this->pushError($this->backupPlaceAuthErrorMsg . 'DropBox!');
        return $isAuthorized;
    }
}
