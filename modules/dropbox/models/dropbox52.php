<?php

/**
 * Class dropbox52ModelBup
 * This class uses only with PHP on 32bit system or with PHP 5.2.x
 * @package Dropbox\Models
 */
class dropbox52ModelBup extends modelBup {

    /**
     * Key to store token to the session
     */
    const TOKEN  = 'dropbox_token';

	private $_inFile = NULL;
	private $_chunkSize = 4194304;

	const API_URL     = 'https://api.dropbox.com/1/';
    const CONTENT_URL = 'https://api-content.dropbox.com/1/';
    /**
     * Dummy method
     * @return bool
     */
    public function isSupported() { return true; }

    /**
     * Is authenticated user?
     * @return bool
     */
    public function isAuthenticated() {
        if (isset($_SESSION[self::TOKEN]) && !empty($_SESSION[self::TOKEN])) {
            return true;
        }

        if (null !== ($token = $this->readToken())) {
            $_SESSION[self::TOKEN] = $token;
            return true;
        }

        return false;
    }

    /**
     * Authenticate user
     * @param string $token Dropbox OAuth2 Token from Authenticator
     * @return bool
     */
    public function authenticate($token) {
        if (!$this->isSessionStarted()) {
            session_start();
        }

        $_SESSION[self::TOKEN] = $token;
        $this->saveToken($token);

        return true;
    }

    /**
     * Get an associative array with dropbox metadata from sandbox
     * @return array|null
     */
    public function getUploadedFiles($stacksFolder = '') {

        if (!$this->isAuthenticated()) {
            return null;
        }

        $url = self::API_URL. 'metadata/sandbox' . $this->getDropboxPath() . $stacksFolder;

        $request = curlBup::createGetRequest($url, array(
            'file_limit' => 25000,
            'list'       => 'true',
            'locale'     => 'en',
        ));

        $request->setAuthorization($this->getToken());
       // try {
        $response = json_decode($request->exec(), true);
       // } catch (RuntimeException $e) {
         //   exit (sprintf('Dropbox Client error: %s\nTry to refresh page', $e->getMessage()));
       // }

        if (isset($response['error'])) {
            return null;
        }

        // Formatting uploading data files for use their on backups page
        $files = array();
        if(!$stacksFolder) {
            foreach ($response['contents'] as $file) {
                $pathInfo = pathinfo($file['path']);
                $backupInfo = $this->getBackupInfoByFilename($pathInfo['basename']);

                if (!empty($backupInfo['ext']) && $backupInfo['ext'] == 'sql') {
                    $files[$backupInfo['id']]['dropbox']['sql'] = $file;
                    $files[$backupInfo['id']]['dropbox']['sql']['backupInfo'] = $backupInfo;
                    $files[$backupInfo['id']]['dropbox']['sql']['backupInfo'] = dispatcherBup::applyFilters('addInfoIfEncryptedDb', $files[$backupInfo['id']]['dropbox']['sql']['backupInfo']);
                } else {
                    $files[$backupInfo['id']]['dropbox']['zip'] = $file;
                    $files[$backupInfo['id']]['dropbox']['zip']['backupInfo'] = $backupInfo;
                }
            }
            unset($response['contents']);
            $response['contents'] = $files;
        } else {
            foreach ($response['contents'] as $file) {
                $pathInfo = pathinfo($file['path']);
                $files[] = basename($pathInfo['dirname']) . '/' . basename($file['path']);
            }

            $response = $files;
        }

        return $response;
    }

	 /**
     * Trim the path of forward slashes and replace
     * consecutive forward slashes with a single slash
     * @param string $path The path to normalise
     * @return string
     */
    private function normalisePath($path)
    {
        $path = preg_replace('#/+#', '/', trim($path, '/'));
        return $path;
    }

    /**
     * Encode the path, then replace encoded slashes
     * with literal forward slash characters
     * @param string $path The path to encode
     * @return string
     */
    private function encodePath($path)
    {
        $path = $this->normalisePath($path);
        $path = str_replace('%2F', '/', rawurlencode($path));
        return $path;
    }
    /**
     * Upload files to Dropbox
     * @param array $files An array of files to upload
     * @return int
     */
    public function upload($files = array(), $stacksFolder = '') {
        if (!$this->isAuthenticated()) return 401;
        if (empty($files)) return 404;

        $filepath = $this->getBackupsPath();

        foreach ($files as $file) {

            $file = basename($file);

            if (file_exists($file = rtrim($filepath, '/') . '/' . $stacksFolder . $file)) {
                $pointer = @fopen($file, 'rb');

                if (!$pointer) {
                    $this->pushError(sprintf('Failed to read file: %s', $file));

                    return 500;
                }

                $uid = null;
                $chunkSize = 4194304;
                $totalSize = 0;

                $body = fread($pointer, $chunkSize);

                if (!$body) {
                    $this->pushError(sprintf('Errors while reading file: %s', $file));

                    return 500;
                }

                try {
                    $request = curlBup::createPutRequest(
                        self::CONTENT_URL . 'chunked_upload',
                        array(),
                        $body
                    );

                    $request->setAuthorization($this->getToken());
                    $request->setHeader('Content-Type', 'application/octet-stream');
                    $response = json_decode($request->exec(), true);

                    if (!isset($response['upload_id'])) {
                        $error = 'Failed to retrieve upload id.';

                        if (isset($response['error'])) {
                            $error = implode(':', $response);
                        }

                        throw new Exception($error);
                    }

                    $uid = $response['upload_id'];
                    $totalSize = $response['offset'];
                } catch (Exception $e) {
                    $this->pushError($e->getMessage());

                    return 500;
                }

                while(!feof($pointer)) {
                    fseek($pointer, $totalSize);
                    $body = fread($pointer, $chunkSize);

                    if (!$body) {
                        $this->pushError(sprintf('Errors while reading file: %s', $file));

                        return 500;
                    }

                    $fields = array(
                        'offset' => $totalSize,
                        'upload_id' => $uid,
                    );

                    try {
                        $request = curlBup::createPutRequest(
                            self::CONTENT_URL . 'chunked_upload',
                            $fields,
                            $body
                        );

                        $request->setHeader('Content-Type', 'application/octet-stream');
                        $request->setAuthorization($this->getToken());

                        $response = json_decode($request->exec(), true);

                        if (isset($response['error'])) {
                            throw new Exception(implode(':', $response));
                        }

                        $uid = $response['upload_id'];
                        $totalSize = $response['offset'];
                    } catch (Exception $e) {
                        $this->pushError($e->getMessage());

                        return 500;
                    }
                }

                fclose($pointer);

                try {
                    $url = self::CONTENT_URL
                        . 'commit_chunked_upload/auto'
                        . $this->getDropboxPath()
                        . $stacksFolder
                        . basename($file);

                    $request = curlBup::createPostRequest(
                        $url,
                        array(
                            'upload_id' => $uid,
                            'overwrite' => 1,
                        )
                    );

                    $request->setAuthorization($this->getToken());

                    $response = json_decode($request->exec(), true);

                    if (isset($response['error'])) {
                        throw new Exception(implode(':', $response));
                    }
                } catch (Exception $e) {
                    $this->pushError($e->getMessage());

                    return 500;
                }

            } else {
                $this->pushError(sprintf('File not found: %s', $file));
                return 404;
            }
        }
        return 200;
    }
    /**
     * Remove file from Dropbox
     * @param string $filepath Filename with full path to file
     * @return bool
     */
    public function remove($filepath) {

        if (!$this->isAuthenticated()) {
            $this->pushError(__('Authentication required', BUP_LANG_CODE));
            return false;
        }

        $url = self::API_URL. 'fileops/delete';
        $request = curlBup::createPostRequest($url, array(
            'root'   => 'sandbox',
            'path'   => $filepath,
            'locale' => 'en',
        ));

        $request->setAuthorization($this->getToken());

        try {
            $response = json_decode($request->exec(), true);
        } catch (RuntimeException $e) {
            $this->pushError($e->getMessage());
            return false;
        }

        if (isset($response['error'])) {
            $this->pushError(implode(':', $response));
            return false;
        }

        return true;
    }

    /**
     * Download file from Dropbox
     * @param  string $filename Name of the file to download
     * @return bool
     */
    public function download($filename, $returnDataString = false) {
        @set_time_limit(0);
        if (!$this->isAuthenticated()) {
            $this->pushError(__('Authentication required', BUP_LANG_CODE));
            return false;
        }

        if (file_exists($this->getBackupsPath() . $filename)) {
            return $returnDataString ? file_get_contents($this->getBackupsPath() . $filename) : true;
        }

        $url = self::CONTENT_URL. 'files/sandbox';
        $request = curlBup::createGetRequest(
            $url . $this->getDropboxPath() . ltrim($filename, '/')
        );
        $request->setAuthorization($_SESSION[self::TOKEN]);

        try {
            $response = $request->exec();
        } catch (RuntimeException $e) {
            $this->pushError($e->getMessage());
        }

        if($returnDataString)
            return $response;

        if (!file_put_contents($this->getBackupsPath() . $filename, $response)) {
            $this->pushError(__(sprintf('Can\'t download the file: %', $filename), BUP_LANG_CODE));
            return false;
        }

        return true;
    }

    /**
     * Dummy method
     * @return array
     */
    public function getQuota() {
        return array();
    }

    /**
     * Is session already started?
     * @return bool
     */
    public function isSessionStarted() {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return session_status() === PHP_SESSION_NONE ? false : true;
        } else {
            return session_id() === '' ? false : true;
        }
    }

    /**
     * Get path to the backups
     * @return string
     */
    public function getBackupsPath() {
        return frameBup::_()->getModule('warehouse')->getPath()
            . DIRECTORY_SEPARATOR;

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
		if(isset($_SESSION[self::TOKEN]) && !empty($_SESSION[self::TOKEN])) {
			return $_SESSION[self::TOKEN];
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
