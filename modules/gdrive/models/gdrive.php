<?php

/**
 * Class gdriveModelBup
 * Google Drive model
 * This class is part of the Google Drive module for Backup by Supsystic
 *
 * @package BackupBySupsystic\Modules\GDrive
 * @version 1.3
 */
class gdriveModelBup extends modelBup {

    /**
     * App key and secret keys in options module
     * @since 1.0
     */
    const CLIENT_ID_INDEX     = 'gdrive_client_id';
    const CLIENT_SECRET_INDEX = 'gdrive_client_secret';

    /**
     * Name of the key in $_SESSION array to store current access token
	 * @since 1.0
     */
    const GDRIVE_SESS_NAME = '_gdrive_token';

    /**
     * Auth scopes express the permissions you request users to authorize
     * @see   https://developers.google.com/drive/web/scopes
     * @since 1.0
     * @var   string
     */
    private $_scopes = array(
        'https://www.googleapis.com/auth/drive',
    );

	/**
	 * Google API redirect URL
	 * System will set this property automatically
	 *
	 * @since 1.1
     * @deprecated since 1.3
	 * @var   string
	 */
	private $_redirect_url = null;

    /**
     * URL to authenticate user
     * System will set this property automatically
     *
     * @since 1.3
     * @var   null|string
     */
    private $_authenticatation_url = null;

	/**
	 * Full path to backups
	 * System will set this property automatically
	 *
	 * @since 1.0
	 * @var   string
	 */
	private $_backupsPath = null;

	private $_folderName = 'Backup by Supsystic';

	private $_currentDomain = '';
    /**
     * Check the server is meets the requirements
     * @since  1.1
     * @return bool
     */
    public function isSupported() {
        if(!defined('BUP_GAPI_SUPPORT')) {
            return false;
        }

        return BUP_GAPI_SUPPORT;
    }

	/**
	 * Returns client's credentials by key
	 *
	 * @since  1.1
	 * @uses   frameBup
	 * @uses   optionsBup
	 * @param  string $key 'clientId' or 'clientSecret'
	 * @return string|null
	 */
    public function getCredential($key) {
        $key = ($key == 'clientId' ? self::CLIENT_ID_INDEX : self::CLIENT_SECRET_INDEX);
        $keys = frameBup::_()->getModule('gdrive')->options;
        foreach($keys as $k){
            if($k['code'] == $key)
                return $k['value'];
        }
        return false;
    }

	/**
	 * Returns client's credentials
	 *
	 * @since  1.1
	 * @return array
	 */
    public function getCredentials() {
        return array(
            'clientId'     => $this->getCredential('clientId'),
            'clientSecret' => $this->getCredential('clientSecret'),
        );
    }

    /**
     * Reset authentication
     * @since 1.3
     * @return void
     */
    public function resetCredentials() {
        if (isset($_SESSION[self::GDRIVE_SESS_NAME])) {
            unset ($_SESSION[self::GDRIVE_SESS_NAME]);
        }

        $this->removeToken();
    }

    /**
     * Is authenticated user?
     *
     * @since  1.0
     * @return boolean
     */
    public function isAuthenticated() {
        if(isset($_SESSION[self::GDRIVE_SESS_NAME]) || null !== $this->readToken()) {
            return true;
        }

        return false;
    }

	/**
	 * Initialize Google Client
	 *
	 * @since  1.1
	 * @return \Google_Client
	 */
	public function getClient() {
        $gDriveAuthUrl = '';
        $keys = frameBup::_()->getModule('gdrive')->options;
        foreach($keys as $k){
            if($k['code'] == 'gdrive_auth_url'){
                $gDriveAuthUrl = $k['value'];
            }
        }
		$credentials = $this->getCredentials();
		$client = new Google_Client(getGoogleClientApiConfig());

		$client->setClientId($credentials['clientId']);
		$client->setClientSecret($credentials['clientSecret']);
		$client->setRedirectUri($gDriveAuthUrl . '/complete/');
		$client->setScopes($this->_scopes);

        /* For offline access */
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        if (null !== $token = $this->readToken()) {
            $client->setAccessToken($token);
            return $client;
        }

		if (isset($_SESSION[self::GDRIVE_SESS_NAME])) {
            $token = $this->addRefreshTokenToJSONToken($_SESSION[self::GDRIVE_SESS_NAME]);
			$client->setAccessToken($token);
		}

		return $client;
	}

	/**
	 * Authenticate client
	 *
	 * @since  1.1
	 * @return boolean
	 */
	public function authenticate() {
		$client = $this->getClient();
		$request = reqBup::get('get');
		if(!isset($request['googleAuthCode']) OR empty($request['googleAuthCode'])) {
            return $this->getAuthenticationURL();
		}

		$code = trim($request['googleAuthCode']);

        try {
		    $_SESSION[self::GDRIVE_SESS_NAME] = $client->authenticate($code);

            $this->saveToken($_SESSION[self::GDRIVE_SESS_NAME]);

            $tokens = json_decode($this->readToken());
            if(!empty($tokens->refresh_token))
                frameBup::_()->getTable('options')->update(array('value' => $tokens->refresh_token), array('code' => 'gdrive_refresh_token'));

            $uri = null;
            if(is_array($request)){
                $uri = array();
                foreach($request as $key => $value){
                    if($key != 'googleAuthCode')
                        $uri[] = $key . '=' . $value;
                }
                $uri = 'admin.php?' . join('&', $uri);
            }
            $redirectURI = !empty($uri) ? $uri : 'admin.php?page='.BUP_PLUGIN_PAGE_URL_SUFFIX;

            redirectBup(admin_url($redirectURI));
        } catch (Exception $e) {
            $this->pushError($e->getMessage());
            return $this->getAuthenticationURL();
        }
	}

    /**
     * Generate and return authentication URL for user
     *
     * @since  1.3
     * @return string
     */
    public function getAuthenticationURL() {
        $url  = 'http://supsystic.com/authenticator/index.php/authenticator/drive';

        $slug = frameBup::_()->getModule('adminmenu')->getView()->getFile();
        $queryString = !empty($_SERVER['QUERY_STRING']) ? 'admin.php?' . $_SERVER['QUERY_STRING'] : '';
        $redirectURI = !empty($queryString) ? $queryString : 'admin.php?page=' . $slug;

        return $url . '?ref=' . base64_encode(admin_url($redirectURI));
    }

	/**
	 * Get redirect URL for Google API
	 * This method is deprecated, use getAuthenticationURL()
     *
     * @deprecated since 1.3
	 * @since  1.1
	 * @return string
	 */
	public function getRedirectURL() {
		if($this->_redirect_url === null) {
			$this->_redirect_url = BUP_SITE_URL.'wp-admin/admin.php?page='.BUP_PLUG_NAME.'/modules/adminmenu/views/adminmenu.php';
		}

		return $this->_redirect_url;
	}

    /**
     * Returns full path to folder where plugin store backups
     *
     * @since  1.0
     * @uses   frameBup
     * @uses   optionsBup
     * @return string
     */
    public function getBackupsPath() {
        if($this->_backupsPath === null) {
            $this->_backupsPath = frameBup::_()->getModule('warehouse')->getPath()
                . DIRECTORY_SEPARATOR;
        }

        return $this->_backupsPath;
    }

	/**
	 * Returns mime type by file extension
	 *
	 * @since  1.1
	 * @param  string $filename
	 * @return string
	 */
	public function getMimetype($filename) {
		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		switch($extension) {
			case 'sql':
			case 'txt':
				return 'text/plain';
			case 'zip':
				return 'application/zip';
			default:
				return 'text/plain';
		}
	}


    /**
     * Upload files to Google Drive
     *
     * @since  1.1
     * @param  array $files
     * @return integer
     */
    public function upload($files, $stacksFolder = '') {
		if(is_array($files) === false) {
			$files = explode(',', $files);
		}

		if($this->isAuthenticated() === false) {
			return 401;
		}

		$client  = $this->getClient();
		$service = new Google_DriveService($client);

        $domain = $this->getDomain(basename($stacksFolder));

        if (!$domain) {
            $this->pushError(__('Unable to get parent folder ID.', BUP_LANG_CODE));

            return 500;
        }

        $parent = new Google_ParentReference();
        $parent->setId($domain['id']);

		foreach($files as $storageFile) {
			if(file_exists($filepath = $this->getBackupsPath() . $stacksFolder . basename($storageFile))) {

				// Ugly hack to prevent log-*.txt upload
				if(pathinfo($filepath, PATHINFO_EXTENSION) != 'txt') {
					$file = new Google_DriveFile();
					$file->setTitle(basename($storageFile));
					$file->setDescription('Backup by Supsystic');
					$file->setMimeType($this->getMimetype($filepath));

                    $file->setParents(array($parent));

					$service->files->insert($file, array(
						'data'     => file_get_contents($filepath),
						'mimeType' => $this->getMimetype($filepath),
					));
				}
			}

		}

		return 201;
	}

    public function createFolder($title, $parentId = null)
    {
        $client  = $this->getClient();
        $service = new Google_DriveService($client);
        $file    = new Google_DriveFile();

        $file->setTitle($title);
        $file->setMimeType('application/vnd.google-apps.folder');

        if (null !== $parentId) {
            $parent = new Google_ParentReference();
            $parent->setId($parentId);

            $file->setParents(array(
                $parent,
            ));
        }

        return $service->files->insert($file, array(
            'mimeType' => 'application/vnd.google-apps.folder',
        ));
    }

    public function getRootObjects()
    {
        if($this->isAuthenticated() === false) {
            return false;
        }

        $client  = $this->getClient();
        $service = new Google_DriveService($client);
        $token   = null;
        $list    = array();
		$cDomain = $this->_getCurrentDomain();
        $config  = array(
			'q' => 'mimeType = "application/vnd.google-apps.folder" or title = "'. $this->_folderName. '" or title = "'. $cDomain. '"',
		);

        do {
            if(!empty($token)) {
                $config['pageToken'] = $token;
            }
            try {
                $files = $service->files->listFiles($config);
            } catch(Exception $e) {
                $this->resetCredentials();
                redirectBup($this->authenticate());
            }

            $list = array_merge($list, $files['items']);

            $token = null;
            if (isset($files['nextPageToken'])) {
                $token = $files['nextPageToken'];
            }
        } while ($token);

        return $list;
    }

	private function _getCurrentDomain() {
		if(empty($this->_currentDomain)) {
			$this->_currentDomain = parse_url(get_home_url(), PHP_URL_HOST);
		}
		return $this->_currentDomain;
	}
    public function getDomain($stacksFolder = '')
    {
        $rootObjects   = $this->getRootObjects();
        $currentDomain = $this->_getCurrentDomain();
        $domainFolder = false;

        $folders = array();
        $root    = null;

        if ($rootObjects === false) {
            return false;
        }

        foreach ($rootObjects as $object) {
            if ($object['mimeType'] === 'application/vnd.google-apps.folder') {
                $folders[] = $object;

                if ($object['title'] === $this->_folderName) {
                    $root = $object;
                }
            }
        }

        if ($root === null) {
            $root = $this->createFolder( $this->_folderName );
        }

        foreach ($folders as $folder) {
            if ($folder['title'] === $currentDomain) {
                foreach ($folder['parents'] as $parent) {
                    if ($parent['id'] === $root['id']) {
                        $domainFolder = $folder;
                        break;
                    }
                }
            }
        }

        if(!$domainFolder)
            $domainFolder = $this->createFolder($currentDomain, $root['id']);

        if(!$stacksFolder) {
            return $domainFolder;
        } else {
            //creating folder for backup stacks
            foreach ($folders as $folder) {
                if ($folder['title'] === $stacksFolder) {
                    foreach ($folder['parents'] as $parent) {
                        if ($parent['id'] === $domainFolder['id']) {
                            return $folder;
                        }
                    }
                }
            }

            return $this->createFolder($stacksFolder, $domainFolder['id']);
        }
    }

    public function getDomainFiles($stacksFolder = false, $getStacks = false)
    {
        $pageToken = null;
        $domain    = $this->getDomain($stacksFolder);
        $child     = array();

        if (!$domain) {
            return null;
        }

        $client  = $this->getClient();
        $service = new Google_DriveService($client);

        do {
            try {
                $parameters = array();

                if ($pageToken) {
                    $parameters['pageToken'] = $pageToken;
                }

                $children = $service->children->listChildren($domain['id'], $parameters);
                $child = array_merge($child, $children['items']);

                $token = null;
                if (isset($children['nextPageToken'])) {
                    $pageToken = $children['nextPageToken'];
                }
            } catch (Exception $e) {
                $pageToken = null;
                $this->pushError($e->getMessage());

                return array();
            }
        } while ($pageToken);

        // Formatting uploading data files for use their on backups page
        $files = array();
        if($getStacks) {
            $i=0;
            foreach ($child as $file) {
                $fileInfo = $service->files->get($file['id']);
                if(!empty($fileInfo['downloadUrl']) && !empty($fileInfo['title'])) {
                    $files[$i]['downloadUrl'] = $fileInfo['downloadUrl'];
                    $files[$i]['title'] = $fileInfo['title'];
                    $i++;
                }
            }
        } else {
            foreach ($child as $file) {
                $backupInfo = $service->files->get($file['id']);
                $backupInfo = $this->getBackupInfoByFilename($backupInfo['title']);

                if (!empty($backupInfo['ext']) && $backupInfo['ext'] == 'sql') {
                    $files[$backupInfo['id']]['gdrive']['sql'] = $service->files->get($file['id']);
                    $files[$backupInfo['id']]['gdrive']['sql']['backupInfo'] = $backupInfo;
                    $files[$backupInfo['id']]['gdrive']['sql']['backupInfo'] = dispatcherBup::applyFilters('addInfoIfEncryptedDb', $files[$backupInfo['id']]['gdrive']['sql']['backupInfo']);;
                } else {
                    $files[$backupInfo['id']]['gdrive']['zip'] = $service->files->get($file['id']);
                    $files[$backupInfo['id']]['gdrive']['zip']['backupInfo'] = $backupInfo;
                }
            }
        }

        return $files;
    }

	/**
	 * Return ALL uploaded to Google Drive files.
	 * You need to filter files manually.
	 * Note: trashed files - labels => trashed (boolean)
	 * Note: filter by description "Backup by Supsystic"
	 *
	 * @since  1.1
	 * @return boolean|array
	 */
	public function getUploadedFiles($stacksFolder = false, $getStacks = false) {
		if($this->isAuthenticated() === false) {
			return false;
		}

		// $client  = $this->getClient();
		// $service = new Google_DriveService($client);
		// $token   = null;
		// $list    = array();
		// $config  = array();
        //
		// do {
		// 	if(!empty($token)) {
		// 		$config['pageToken'] = $token;
		// 	}
		// 	try {
		// 		$files = $service->files->listFiles($config);
		// 	} catch(Exception $e) {
		// 		session_destroy();
		// 		redirectBup($client->createAuthUrl());
		// 	}
        //
		// 	$list = array_merge($list, $files['items']);
		// 	$token = $files['nextPageToken'];
		// } while ($token);
        //
		// return $list;
		//
		return $this->getDomainFiles($stacksFolder, $getStacks);
	}

	/**
	 * Delete file from Google Drive by the FileID, not by name
	 * Method names as 'remove', because 'delete' will overload's modelBup method
	 *
	 * @param  string $file
	 * @return boolean
	 */
	public function remove($file) {
		if($this->isAuthenticated() === false) {
			return false;
		}

		$client  = $this->getClient();
		$service = new Google_DriveService($client);

		$service->files->delete($file);

		return true;
	}

	/**
	 * Check for local backup
	 *
	 * @since  1.1
	 * @param  string $filename
	 * @return boolean
	 */
	public function isLocalFileExists($filename) {
		$filepath = $this->getBackupsPath();

		if(file_exists($filepath . $filename)) {
			return true;
		}

		return false;
	}

	/**
	 * Download file from Google Drive
	 *
     * @since  1.1
	 * @param  string $url
	 * @param  string $filename
	 * @return boolean|null
	 */
	public function download($url = null, $filename = '', $returnDataString = false) {

		$client = $this->getClient();
		$service = new Google_DriveService($client);

		if($url) {
			$request = new Google_HttpRequest($url);
			$httpRequest = Google_Client::$io->authenticatedRequest($request);

			if($httpRequest->getResponseHttpCode() == 200) {
				$filepath = $this->getBackupsPath() . $filename;
				$content  = $httpRequest->getResponseBody();

                if($returnDataString)
                    return $content;

				if(file_put_contents($filepath, $content) !== false) {
					return true;
				}

				return false;
			}

			return false;
		}

		return null;
	}

    public function removeToken()
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        if (false !== $expired = glob($storage . '/drive*.json')) {
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

        file_put_contents($storage . '/' . uniqid('drive') . '.json', $token);
    }

    /**
     * Reads the token
     */
    protected function readToken()
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        if (false !== $token = glob($storage . '/drive*.json')) {
            if (is_array($token) && count($token) === 1) {
                $token = $this->addRefreshTokenToJSONToken(file_get_contents($token[0]));
                return $token;
            }
        }

        return null;
    }

    protected function addRefreshTokenToJSONToken($token)
    {
        $refreshToken = frameBup::_()->getTable('options')->get('value', array('code' => 'gdrive_refresh_token'), '', 'row');
        if (!empty($refreshToken['value']) && false !== json_decode($token)) {
            $token = json_decode($token);
            $token->refresh_token = $refreshToken['value'];
            $token = json_encode($token);
        }
        return $token;
    }
    public function isUserAuthorizedInService($destination = null)
    {
        $isAuthorized = $this->isAuthenticated() ? true : false;
        if(!$isAuthorized)
            $this->pushError($this->backupPlaceAuthErrorMsg . 'GoogleDrive!');
        return $isAuthorized;
    }
}
