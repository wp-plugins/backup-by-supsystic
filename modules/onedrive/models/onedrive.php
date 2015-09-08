<?php

class onedriveModelBup extends modelBup
{

    const CLIENT_ID     = '000000004413CF67';
    const CLIENT_SECRET = 'jZ9BU1k812PYIGxTCQqnXHauXrwOuYB9';
    const SESSION_ID    = '_onedrive_acessToken';
    const SESSION_EXP   = '_onedrive_accessToken_expires';
    const AUTH_URL      = 'https://login.live.com/oauth20_authorize.srf';
    const TOKEN_URL     = 'https://login.live.com/oauth20_token.srf';
    const REDIRECT_URI  = 'https://supsystic.com/authenticator/index.php/authenticator/onedrive';


    /**
     * Checks whether the current user is authenticated to the OneDrive.
     * @return bool
     */
    public function isAuthenticated()
    {
        if (isset($_SESSION[self::SESSION_ID]) || null !== ($token = $this->readToken())) {
            if(empty($_SESSION[self::SESSION_ID]))
                $_SESSION[self::SESSION_ID] = $token;

            if (!isset($_SESSION[self::SESSION_EXP]))
                $_SESSION[self::SESSION_EXP] = $this->getRefreshTokenExpireTime();

            return true;
        }

        return false;
    }

    public function refreshAccessToken()
    {
        if (!isset($_SESSION[self::SESSION_EXP])) {
            return;
        }

        $timestamp = time();
        $expiresIn = (int)$_SESSION[self::SESSION_EXP];

        if ($timestamp < $expiresIn) {
            return;
        }

        if (null === $refreshToken = $this->getRefreshToken()) {
            return;
        }

        $response = wp_remote_post(
            self::TOKEN_URL,
            array(
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ),
                'body'    => $this->buildQuery(
                        array(
                            'client_id'     => self::CLIENT_ID,
                            'redirect_uri'  => $this->encodeUrl(
                                    self::REDIRECT_URI
                                ),
                            'client_secret' => $this->encodeSecret(
                                    self::CLIENT_SECRET
                                ),
                            'refresh_token' => $refreshToken,
                            'grant_type'    => 'refresh_token'
                        )
                    ),
            )
        );

        if (is_wp_error($response)) {
            $this->pushError($response->get_error_message());

            return false;
        }

        if (200 != wp_remote_retrieve_response_code($response)) {
            $this->pushError(wp_remote_retrieve_body($response));

            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response));

        $_SESSION[self::SESSION_ID] = $body->access_token;
        $_SESSION[self::SESSION_EXP] = time() + (int)$body->expires_in;

        $this->saveToken($_SESSION[self::SESSION_ID]);

        if (property_exists($body, 'refresh_token')) {
            $this->saveRefreshToken($body->refresh_token);
            $this->saveRefreshTokenExpireTime($_SESSION[self::SESSION_EXP]);
        }

        return true;
    }

    /**
     * Returns the authorization URL.
     * @return string
     */
    public function getAuthorizationUrl()
    {
        $slug = frameBup::_()->getModule('adminmenu')->getView()->getFile();
        $queryString = !empty($_SERVER['QUERY_STRING']) ? 'admin.php?' . $_SERVER['QUERY_STRING'] : '';
        $redirectURI = !empty($queryString) ? $queryString : 'admin.php?page=' . $slug;

        $query = array(
            'client_id'     => self::CLIENT_ID,
            'redirect_uri'  => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => array(
                'wl.signin',
                'wl.basic',
                'wl.skydrive',
                'wl.skydrive_update',
                'wl.offline_access'
            ),
            'state'         => urlencode(admin_url($redirectURI)),
        );

        return self::AUTH_URL . '?' . $this->buildQuery($query);
    }

    /**
     * Authorize user with the authorization code.
     * @param  string $code Authorization code.
     * @return bool
     */
    public function authorize($code)
    {
        $response = wp_remote_post(self::TOKEN_URL, array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => $this->buildQuery(array(
                'client_id'     => self::CLIENT_ID,
                'redirect_uri'  => $this->encodeUrl(self::REDIRECT_URI),
                'client_secret' => $this->encodeSecret(self::CLIENT_SECRET),
                'code'          => $code,
                'grant_type'    => 'authorization_code',
            )),
        ));

        if (is_wp_error($response)) {
            $this->pushError($response->get_error_message());

            return false;
        }

        if (200 != wp_remote_retrieve_response_code($response)) {
            $this->pushError(wp_remote_retrieve_body($response));

            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response));

        $_SESSION[self::SESSION_ID] = $body->access_token;
        $_SESSION[self::SESSION_EXP] = time() + (int)$body->expires_in;

        $this->saveToken($_SESSION[self::SESSION_ID]);

        if (property_exists($body, 'refresh_token')) {
            $this->saveRefreshToken($body->refresh_token);
            $this->saveRefreshTokenExpireTime($_SESSION[self::SESSION_EXP]);
        }

        return true;
    }

    public function logout()
    {
        if (isset($_SESSION[self::SESSION_ID])) {
            unset ($_SESSION[self::SESSION_ID]);
        }
        if (isset($_SESSION[self::SESSION_EXP])) {
            unset ($_SESSION[self::SESSION_EXP]);
        }

        $this->removeToken();
        $this->deleteRefreshToken();
        $this->deleteRefreshTokenExpireTime();
    }

    /**
     * Returns the files from the Backup By Supsystic folder.
     * @return array|null
     */
    public function getUserFiles($stacksFolder = '')
    {
        $this->refreshAccessToken();
        $root = $this->getDomainObject($stacksFolder);

        if ($root) {
            return $this->getFolderObjects($root->id);
        }

        return null;
    }

    public function getBackupFolderObject()
    {
        $rootObjects = $this->getSkyDriveObjects();

        if (null === $rootObjects || $this->haveErrors()) {
            return null;
        }

        foreach ($rootObjects as $object) {
            if ($object->type == 'folder' && $object->name == 'Backup By Supsystic') {
                return $object;
            }
        }

        return $this->createFolder('Backup By Supsystic');
    }

    public function getDomainObject($stacksFolder = '')
    {
        $currentDomain = parse_url(get_bloginfo('wpurl'), PHP_URL_HOST);
        $root = $this->getBackupFolderObject();
        $domainFolder = false;

        if (null === $root) {
            $this->pushError('Failed to get access to the Backup By Supsystic folder.');
            return null;
        }

        $domains = $this->getFolderObjects($root->id);

        if (null === $domains) {
            $this->pushError('Failed to get domains list.');
            return null;
        }

        foreach ($domains as $domain) {
            if ($domain->type == 'folder' && $domain->name == $currentDomain) {
                $domainFolder = $domain;
                break;
            }
        }

        if(!$domainFolder)
            $domainFolder = $this->createFolder($currentDomain, null, $root->id);

        if(!$stacksFolder) {
            return $domainFolder;
        } else {
            //creating folder for backup stacks
            $domainFolders = $this->getFolderObjects($domainFolder->id, true);

            foreach ($domainFolders as $folder) {
                if (!empty($folder->type) && $folder->type == 'folder' && $folder->name == $stacksFolder) {
                    return $folder;
                }
            }

            return $this->createFolder($stacksFolder , null, $domainFolder->id);
        }
    }

    /**
     * Returns the object with the Microsoft SkyDrive data.
     * @return stdClass|null
     */
    public function getSkydrive()
    {
        $response = wp_remote_get(
            $this->buildUrl('me/skydrive?access_token={token}', array(
                'token' => $this->getAccessToken()
            ))
        );

        if ($this->hasError($response)) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);

        if ($this->isJsonEncoded($response)) {
            $body = json_decode($body);
        }

        return $body;
    }

    /**
     * Returns the list of the files on the user's SkyDrive.
     * @return stdClass[]|null
     */
    public function getSkyDriveObjects()
    {
        // if (null === $skydrive = $this->getSkydrive()) {
        //     return null;
        // }

        // return $this->getFolderObjects($skydrive->id);
        return $this->getFolderObjects('me/skydrive');
    }

    /**
     * Returns the list of the objects inside the folder.
     * @param  string $folderId
     * @return stdClass[]|null
     */
    public function getFolderObjects($folderId, $getStacksFolder = false)
    {
        $url = $this->buildUrl('{folder_id}/files?access_token={token}', array(
            'folder_id' => $folderId,
            'token'     => $this->getAccessToken(),
        ));

        $response = wp_remote_get($url);

        if ($this->hasError($response)) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response));

        if(!empty($body->data[0]->name) && strpos($body->data[0]->name, 'backup_') !== false ){

            if($getStacksFolder) {
                $folders = array();

                foreach ($body->data as $key => $folder) {
                    $folders[] = $folder;
                }

                return $folders;
            } else {
                // Formatting uploading data files for use their on backups page
                $files = array();

                foreach ($body->data as $key => $file) {
                    $backupInfo = $this->getBackupInfoByFilename($file->name);

                    if (empty($files[$backupInfo['id']]['onedrive']))
                        $files[$backupInfo['id']]['onedrive'] = new stdClass();

                    if (!empty($backupInfo['ext']) && $backupInfo['ext'] == 'sql') {
                        $files[$backupInfo['id']]['onedrive']->sql = $body->data[$key];
                        $files[$backupInfo['id']]['onedrive']->sql->backupInfo = $backupInfo;
                        $files[$backupInfo['id']]['onedrive']->sql->backupInfo = dispatcherBup::applyFilters('addInfoIfEncryptedDb', $files[$backupInfo['id']]['onedrive']->sql->backupInfo);
                    } else {
                        $files[$backupInfo['id']]['onedrive']->zip = $body->data[$key];
                        $files[$backupInfo['id']]['onedrive']->zip->backupInfo = $backupInfo;
                    }
                }

                return $files;
            }
        }

        return $body->data;
    }

    /**
     * Creates a new folder in the SkyDrive root directory.
     * @param  string $name        Folder name
     * @param  string $description Folder descriotion
     * @return stdClass|null
     */
    public function createFolder($name, $description = null, $parent = 'me/skydrive')
    {
        $url = $this->buildUrl($parent);

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode(array(
                'description' => $description,
                'name'        => $name,
            )),
        ));

        if ($this->hasError($response, 201)) {
            return null;
        }

        return $this->getBody($response);
    }

    public function upload($files = array(), $stacksFolder = '')
    {
        if (!is_array($files)) {
            $files = explode(',', $files);
        }

        if (!$this->isAuthenticated()) {
            return 401;
        }

        $this->refreshAccessToken();
        if (null === $folder = $this->getDomainObject(basename($stacksFolder))) {
            return 500;
        }

        $skydrive = new skydriveBup($this->getAccessToken());

        foreach ($files as $file) {
            if (file_exists($file = $this->getBackupsPath() . $stacksFolder .basename($file))) {
                try {
                    $skydrive->put_file(
                        $folder->id,
                        $file
                    );
                } catch (Exception $e) {
                    $this->pushError($e->getMessage());
                    return 500;
                }
            }
        }

        return 201;
    }

    public function download($fileId, $returnDataString = false, $stacksFolder = '')
    {
        $this->refreshAccessToken();
        $skydrive = new skydriveBup($this->getAccessToken());

        if (!$this->isAuthenticated()) {
            $this->pushError(__('Authorization required.', BUP_LANG_CODE));

            return false;
        }

        try {
            $data = $skydrive->download($fileId);

            if (!is_array($data)) {
                $this->pushError(__('Enexpected error.', BUP_LANG_CODE));

                return false;
            }

            foreach ($data as $file) {
                $filename = $this->getBackupsPath()
                    . '/'
                    . $stacksFolder
                    . $file['properties']['name'];

                if($returnDataString)
                    return $file['data'];

                if (!file_put_contents($filename, $file['data'])) {
                    $this->pushError(__('Failed to save downloaded file.', BUP_LANG_CODE));

                    return false;
                }
            }

            return true;
        } catch (Exception $e) {
            $this->pushError($e->getMessage());

            return false;
        }
    }

    public function getFileProps($fileId)
    {
        $response = wp_remote_get(
            $this->buildUrl('{file_id}?access_token={token}', array(
                'file_id' => $fileId,
                'token'   => $this->getAccessToken(),
            ))
        );

        if (!$this->hasError($response)) {
            return $this->getBody($response);
        }

        return false;
    }

    /**
     * Removes the object (folder or photo) from the OneDrive.
     * @param  string $objectId The identifier of the folder or photo.
     * @return bool
     */
    public function deleteObject($objectId)
    {
        $url = $this->buildUrl('{object_id}?access_token={token}', array(
            'object_id' => $objectId,
            'token'     => $this->getAccessToken(),
        ));

        $response = wp_remote_get($url, array(
            'method' => 'DELETE',
        ));

        if ($this->hasError($response, 204)) {
            return false;
        }

        return true;
    }

    /**
     * Returns the response body.
     * @param  array $response Response array
     * @return stdClass|null
     */
    public function getBody($response)
    {
        $body = wp_remote_retrieve_body($response);

        if ($this->isJsonEncoded($response)) {
            $body = json_decode($body);
        }

        return $body;
    }

    /**
     * Returns the access token.
     * @return string|null
     */
    public function getAccessToken()
    {
        if (!isset($_SESSION[self::SESSION_ID])) {
            return null;
        }

        return $_SESSION[self::SESSION_ID];
    }

    public function getBackupsPath()
    {
        return frameBup::_()->getModule('warehouse')->getPath() . DS;
    }

    public function isLocalFileExists($filename) {
        $filepath = $this->getBackupsPath();

        if (file_exists($filepath . $filename)) {
            return true;
        }

        return false;
    }

    /**
     * Builds the request URL.
     * @param  string $pattern    URL string with the optional context.
     * @param  array  $parameters An array of the URL parameters (context).
     * @return string
     */
    protected function buildUrl($pattern, array $parameters = array())
    {
        $baseUrl = 'https://apis.live.net/v5.0/';
        $replace = array();

        foreach ($parameters as $parameter => $value) {
            $replace['{' . $parameter . '}'] = $value;
        }

        $query = @strtr($pattern, $replace);

        return $baseUrl . ltrim($query, '/');
    }

    /**
     * Checks whether the response has errors.
     * @param  array $response    Response array.
     * @param  int   $successCode Expected success HTTP status code.
     * @return bool
     */
    protected function hasError($response, $successCode = 200)
    {
        if (is_wp_error($response)) {
            $this->pushError($response->get_error_message());

            return true;
        }

        if ($this->isStatusCode($successCode, $response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);

        if ($this->isJsonEncoded($response)) {
            $body = json_decode($body);

            $this->pushError($body->error->message);
        } else {
            $this->pushError($body);
        }

        return true;
    }

    /**
     * Compare status codes.
     * @param  int   $expected Expected status code.
     * @param  mixed $response The response
     * @return bool
     */
    protected function isStatusCode($expected, $response)
    {
        if (is_wp_error($response)) {
            return false;
        }

        $actual = wp_remote_retrieve_response_code($response);

        return ((int) $expected === (int) $actual);
    }

    /**
     * Checks whether the response is JSON encoded
     * @param  mixed $response
     * @return bool
     */
    protected function isJsonEncoded($response)
    {
        $headers = wp_remote_retrieve_headers($response);

        if (!isset($headers['content-type'])) {
            return false;
        }

        if (!preg_match('/json/ui', $headers['content-type'])) {
            return false;
        }

        return true;
    }

    /**
     * Thank you, Microsoft. I hate your stupid encoding requirments.
     * Use this function instead of http_build_query.
     * @param  array $data An array of the query string data.
     * @return string
     */
    protected function buildQuery(array $data)
    {
        $queryString = '';

        foreach ($data as $param => $value) {
            if ($param == 'redirec_uri' || $param == 'state') {
                $value = $this->encodeUrl($value);
            }

            if ($param == 'scope') {
                $value = $this->encodeScope($value);
            }

            if ($param == 'client_secret') {
                $value = $this->encodeSecret($value);
            }

            $queryString .= $param . '=' . $value . '&';
        }

        return rtrim($queryString, '&');
    }

    protected function encodeUrl($url)
    {
        $replace = array(
            '/' => '%2F',
            ':' => '%3A',
            ' ' => '%20',
        );


        // Disable notice. PHP 5.5 bug.
        // http://php.net//manual/ru/function.strtr.php#112930
        return @strtr($url, $replace);
    }

    protected function encodeScope($scope)
    {
        if (is_array($scope)) {
            $scope = implode(' ', $scope);
        }

        // Disable notice. PHP 5.5 bug.
        // http://php.net//manual/ru/function.strtr.php#112930
        return @strtr($scope, array(
            ' ' => '%20',
        ));
    }

    protected function encodeSecret($secret)
    {
        // Disable notice. PHP 5.5 bug.
        // http://php.net//manual/ru/function.strtr.php#112930
        return @strtr($secret, array(
            '+' => '%2B',
        ));
    }

    public function removeToken()
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        if (false !== $expired = glob($storage . '/onedriveAccessToken*.json')) {
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

        file_put_contents($storage . '/' . uniqid('onedriveAccessToken') . '.json', $token);
    }

    /**
     * Reads the token
     */
    protected function readToken()
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        if (false !== $token = glob($storage . '/onedriveAccessToken*.json')) {
            if (is_array($token) && count($token) === 1) {
                return file_get_contents($token[0]);
            }
        }

        return null;
    }

    protected function deleteRefreshToken()
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        if (false !== $expired = glob($storage . '/onedriveRefreshToken*.json')) {
            if (is_array($expired) && count($expired) > 0) {
                foreach ($expired as $file) {
                    @unlink($file);
                }
            }
        }
    }

    protected function deleteRefreshTokenExpireTime()
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        if (false !== $expired = glob($storage . '/onedriveExpireTimeRefreshToken*.json')) {
            if (is_array($expired) && count($expired) > 0) {
                foreach ($expired as $file) {
                    @unlink($file);
                }
            }
        }
    }

    protected function getRefreshToken()
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        if (false !== $refreshToken = glob($storage . '/onedriveRefreshToken*.json')) {
            if (is_array($refreshToken) && count($refreshToken) === 1) {
                return @file_get_contents($refreshToken[0]);
            }
        }

        return null;
    }

    protected function getRefreshTokenExpireTime()
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        if (false !== $refreshTokenExpireTime = glob($storage . '/onedriveExpireTimeRefreshToken*.json')) {
            if (is_array($refreshTokenExpireTime) && count($refreshTokenExpireTime) === 1) {
                return @file_get_contents($refreshTokenExpireTime[0]);
            }
        }

        return null;
    }

    protected function saveRefreshToken($refreshToken)
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        $this->deleteRefreshToken();

        $result = file_put_contents(
            $storage . '/' . uniqid('onedriveRefreshToken') . '.json',
            $refreshToken
        );

        if (!$result) {
            @error_log('Backup by Supsystic: Failed to write OneDrive refresh token.');
        }
    }

    protected function saveRefreshTokenExpireTime($refreshTokenExpireTime)
    {
        $storage = frameBup::_()->getModule('warehouse')->getPath();

        $this->deleteRefreshTokenExpireTime();

        $result = file_put_contents(
            $storage . '/' . uniqid('onedriveExpireTimeRefreshToken') . '.json',
            $refreshTokenExpireTime
        );

        if (!$result) {
            @error_log('Backup by Supsystic: Failed to write OneDrive refresh token expire time.');
        }
    }
    public function isUserAuthorizedInService($destination = null)
    {
        $isAuthorized = $this->isAuthenticated() ? true : false;
        if(!$isAuthorized)
            $this->pushError($this->backupPlaceAuthErrorMsg . 'OneDrive!');
        return $isAuthorized;
    }
}
