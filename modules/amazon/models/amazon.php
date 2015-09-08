<?php

/**
 * Amazon S3 Model
 * @package BackupBySupsystic\Modules\Amazon
 * @version 1.1
 */
class amazonModelBup extends ModelBup {
    
    /**
     * Database options keys
     */
    const ACCESS_KEY_INDEX = 'aws_access_key';
    const SECRET_KEY_INDEX = 'aws_secret_key';
    const BUCKET_KEY_INDEX = 'aws_s3_bucket';
    
    /**
     * Files order types
     */
    const ORDER_DESCENDING = 0;
    const ORDER_ASCENDING  = 1;

    /**
     * Full path to backups
     * @var string
     */
    protected $backupsPath = null;
    
    /**
     * Check for AWS SDK support on server (PHP > 5.3 and other)
     *
     * @since  1.0
     * @see    http://docs.aws.amazon.com/aws-sdk-php/guide/latest/requirements.html
     * @return boolean
     */
    public function isSupported() {
        if(!defined('BUP_S3_SUPPORT')) {
            return false;
        }
        
        return BUP_S3_SUPPORT;
    }

    /**
     * Store application's credentials to database
     *
     * @since  1.0
     * @uses   frameBup
     * @uses   optionsBup
     * @see    http://docs.aws.amazon.com/aws-sdk-php/guide/latest/credentials.html
     * @param  array $request
     * @return boolean
     */
    public function storeCredentials(array $request) {
        
        $accesskey = (isset($request['access']) ? trim($request['access']) : null);
        $secretkey = (isset($request['secret']) ? trim($request['secret']) : null);
        
        // don't use !== NULL, cuz values may be empty strings, etc.
        if(!empty($accesskey) && !empty($secretkey)) {
            $options = frameBup::_()->getModule('options');
            $options->set($accesskey, amazonModelBup::ACCESS_KEY_INDEX);
            $options->set($secretkey, amazonModelBup::SECRET_KEY_INDEX);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns array with access and secret key if they are stored in database
     *
     * @since  1.0
     * @return array
     */
    public function getCredentials() {
        return array(
            'access' => $this->getCredential('access'),
            'secret' => $this->getCredential('secret'),
        );
    }

    /**
     * Return selected credential
     *
     * @since  1.1
     * @uses   frameBup
     * @uses   optionsBup
     * @param  string $key 'access' or 'secret'
     * @return string
     */
    public function getCredential($key) {
        $key = ($key == 'access' ? amazonModelBup::ACCESS_KEY_INDEX : amazonModelBup::SECRET_KEY_INDEX);
        return frameBup::_()->getModule('options')->get($key);
    }

    /**
     * Reset credentials
     *
     * @since  1.1
     * @return \amazonModelBup
     */
    public function resetCredentials() {
        $options = frameBup::_()->getModule('options');
        $options->set(null, amazonModelBup::ACCESS_KEY_INDEX);
        $options->set(null, amazonModelBup::SECRET_KEY_INDEX);

        return $this;
    }

    /**
     * Check credentials in database
     *
     * @since  1.1
     * @return boolean
     */
    public function isCredentialsSaved() {
        $credentials = $this->getCredentials();
        if(!empty($credentials['access']) && !empty($credentials['secret'])) {
            return true;
        }

        return false;
    }

    /**
     * Returns all available buckets for user
     *
     * @since  1.1
     * @uses   \Aws\S3\S3Client
     * @see    http://docs.aws.amazon.com/aws-sdk-php/guide/latest/service-s3.html#factory-method
     * @see    http://docs.aws.amazon.com/aws-sdk-php/guide/latest/service-s3.html#listing-your-buckets
     * @return array
     */
    public function getAvailableBuckets() {
        $credentials = $this->getCredentials();

        $client = Aws\S3\S3Client::factory(array(
            'key'    => $credentials['access'],
            'secret' => $credentials['secret'],
        ));

        $data = $client->listBuckets();
        return $data['Buckets'];
    }

    /**
     * Returns mimetype of file by extension.
     * Note: S3 doesn't support x-sql mimetype, use text/plain
     *
     * @since  1.0
     * @param  string $filename Full path to file
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
     * Returns fullpath to folder where plugin store backups
     *
     * @since  1.0
     * @uses   frameBup
     * @uses   optionsBup
     * @return string
     */
    public function getBackupsPath() {
        if($this->backupsPath === null) {
            $this->backupsPath = substr(ABSPATH, 0, strlen(ABSPATH)-1).frameBup::_()->getModule('options')->get('warehouse');
        }
        
        return $this->backupsPath;
    }
    
    /**
     * Upload files to Amazon S3
     *
     * @since  1.0
     * @uses   \Aws\S3\S3Client
     * @see    http://docs.aws.amazon.com/aws-sdk-php/guide/latest/service-s3.html#factory-method
     * @see    http://docs.aws.amazon.com/aws-sdk-php/guide/latest/service-s3.html#uploading-a-file
     * @param  array $files
     * @return integer
     */
    public function upload(array $files, $stacksFolder = '') {
        $credentials = $this->getCredentials();
        $bucket = $this->getBucket();

        // Dummy credentials check
        // @TODO find way to check if credentials are valid
        if(empty($credentials['access']) OR empty($credentials['secret'])) {
            return 403;
        }
        
        // Initialize S3 client
        $client = Aws\S3\S3Client::factory(array(
            'key'    => $credentials['access'],
            'secret' => $credentials['secret'],
        ));

        
        // Check selected bucket
        if(!$client->doesBucketExist($bucket)) {
            return 404;
        }

        foreach($files as $file) {
            $filename = basename($file);
            $file     = $this->getBackupsPath() . $stacksFolder . $filename;

            if(file_exists($file)) {
                try {
                    $result[] = $client->putObject(array(
                        'Bucket' => $bucket,

                        'Key' => $this->getCurrentDomain() . '/' . $stacksFolder . $filename,
                        'SourceFile' => $file,
                        'Metadata' => array(
                            'Content-Type' => $this->getMimetype($file),
                        ),
                    ));
                } catch (Exception $e) {
                    return 401;
                }
            }
        }

        return 201;
    }

    /**
     * Delete file from Amazon S3 Bucket
     *
     * @since  1.1
     * @uses   \Aws\S3\S3Client
     * @see    http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.S3.S3Client.html#_doesObjectExist
     * @see    http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.S3.S3Client.html#_deleteObject
     * @param  string $filename
     * @return integer
     */
    public function remove($filename = null, $folder = false) {
        if($filename === null OR empty($filename)) {
            return 400;
        }

        $client = Aws\S3\S3Client::factory(array(
            'key'    => $this->getCredential('access'),
            'secret' => $this->getCredential('secret'),
        ));

        if($folder) {
            $result = $client->deleteMatchingObjects(
                $this->getBucket(),
                $filename . '/'
            );

            return 200;
        } else {
            if ($client->doesObjectExist($this->getBucket(), $filename) === false) {
                return 404;
            }

            $client->deleteObject(array(
                'Bucket' => $this->getBucket(),
                'Key' => $filename,
            ));

            return 200;
        }
    }

    /**
     * Download file from Amazon S3 to BackupBySupsystic folder
     * @since  1.1
     * @uses   \Aws\S3\S3Client
     * @see    http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.S3.S3Client.html#_getObject
     * @param  string $filename 
     * @return integer
     */
    public function download($filename, $stacksFolder = '') {
        $filenameInfo = pathinfo($filename);
        $backupPath = $this->getBackupsPath();
        if(!file_exists($backupPath . $filenameInfo['basename'])) {
            $client = Aws\S3\S3Client::factory(array(
                'key' => $this->getCredential('access'),
                'secret' => $this->getCredential('secret'),
            ));

            if ($client->doesObjectExist($this->getBucket(), $filename) === false) {
                return 404;
            }
            if (!file_exists($backupPath . $filenameInfo['basename'])) {
                $client->getObject(array(
                    'Bucket' => $this->getBucket(),
                    'Key' => $filename,
                    'SaveAs' => $backupPath . $stacksFolder . $filenameInfo['basename'],
                ));
            }

            return 201;
        } elseif(file_exists($backupPath . $filenameInfo['basename'])) {
            return 201;
        }
    }

    /**
     * Returns uploaded files to amazon s3
     *
     * @since  1.1
     * @uses   \Aws\S3\S3Client
     * @see    http://docs.aws.amazon.com/aws-sdk-php/latest/class-Aws.S3.S3Client.html#_listObjects
     * @see    http://www.php.net/manual/ru/class.arrayaccess.php
     * @see    http://www.php.net/manual/ru/class.iteratoraggregate.php
     * @see    http://www.php.net/manual/ru/class.countable.php
     * @see    http://docs.aws.amazon.com/aws-sdk-php/latest/class-Guzzle.Common.ToArrayInterface.html
     * @param  boolean $match Show only backups files or show all files in bucket
     * @param  integer $order ascending or descending
     * @return array
     */
    public function getUploadedFiles($stacksFolder = '', $match = true, $order = amazonModelBup::ORDER_DESCENDING) {
        $credentials = $this->getCredentials();
        $bucket      = $this->getBucket();
        $files       = array();
//        $pattern     = '/([a-z_]+[0-9_-]{20}[a-z]{4,8}_id[0-9]+(.sql|.zip))/';
        $pattern     = '/(backup_.*)/';

        $client = Aws\S3\S3Client::factory(array(
            'key'    => $credentials['access'],
            'secret' => $credentials['secret'],
        ));

        $filesIterator = $client->getIterator('ListObjects', array(
            'Bucket' => $bucket,
			'Prefix' => $this->getCurrentDomain() . '/' . $stacksFolder,
        ));

        foreach($filesIterator as $storageFile) {
            if($match === true) {
                if(preg_match($pattern, $storageFile['Key'])) {
                    $files[] = $storageFile['Key'];
                }
            }
            else {
                $files[] = $storageFile['Key'];
            }
        }

        if($order = amazonModelBup::ORDER_DESCENDING) {
            krsort($files);
        }

        if($stacksFolder) {
            return $files;
        } else {
            // Formatting uploading data files for use their on backups page
            $newFiles = array();
            foreach ($files as $file) {
                $pathElementsCount = explode('/', $file);
                // if $pathElementsCount contains 3 element - so this is filesystem backup with stacks, else backup with one big archive
                $oneFileBackup = count($pathElementsCount) > 2 ? false : true;
                $extension = pathinfo($file, PATHINFO_EXTENSION);

                if ($extension === 'sql'|| ($extension === 'zip' && $oneFileBackup))
                    $backupInfo = $this->getBackupInfoByFilename($file);
                else
                    $backupInfo = $this->getBackupInfoByFilename(pathinfo($file, PATHINFO_DIRNAME));

                if (!empty($backupInfo['ext']) && $backupInfo['ext'] == 'sql') {
                    $newFiles[$backupInfo['id']]['amazon']['sql']['file'] = $file;
                    $newFiles[$backupInfo['id']]['amazon']['sql']['backupInfo'] = $backupInfo;
                    $newFiles[$backupInfo['id']]['amazon']['sql']['backupInfo'] = dispatcherBup::applyFilters('addInfoIfEncryptedDb', $newFiles[$backupInfo['id']]['amazon']['sql']['backupInfo']);
                } elseif (!empty($backupInfo['ext']) && $backupInfo['ext'] == 'zip' && $oneFileBackup) {
                    $newFiles[$backupInfo['id']]['amazon']['zip']['file'] = $file;
                    $newFiles[$backupInfo['id']]['amazon']['zip']['backupInfo'] = $backupInfo;
                } else {
                    $newFiles[$backupInfo['id']]['amazon']['zip']['file'] = pathinfo($file, PATHINFO_DIRNAME);
                    $newFiles[$backupInfo['id']]['amazon']['zip']['backupInfo'] = $backupInfo;
                }
            }
            return $newFiles;
        }
    }
    
    /**
     * Returns selected bucket
     *
     * @since  1.0
     * @uses   frameBup
     * @uses   optionsBup
     * @return string
     */
    public function getBucket() {
        return frameBup::_()->getModule('options')
                            ->get(amazonModelBup::BUCKET_KEY_INDEX);
    }
    
    /**
     * Set bucket to upload
     *
     * @since  1.0
     * @param  string $bucketName
     * @return \amazonModelBup
     */
    public function setBucket($bucketName) {
        frameBup::_()->getModule('options')
                     ->set($bucketName, amazonModelBup::BUCKET_KEY_INDEX);
        
        return $this;
    }
    
    /**
     * Reset bucket
     *
     * @since  1.1
     * @return \amazonModelBup
     */
    public function resetBucket() {
        frameBup::_()->getModule('options')
                     ->set('', amazonModelBup::BUCKET_KEY_INDEX);

        return $this;
    }

    protected function getCurrentDomain() {
        $homeUrl = parse_url(get_home_url());
        $host = str_replace('.', '_', $homeUrl['host']);

        return $host;
    }

    public function isUserAuthorizedInService($destination = null) {
        $isAuthorized = $this->isCredentialsSaved() ? true : false;
        if(!$isAuthorized)
            $this->pushError($this->backupPlaceAuthErrorMsg . 'Amazon!');
        return $isAuthorized;
    }
}