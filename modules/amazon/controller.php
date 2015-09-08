<?php

/**
 * Amazon S3 Controller
 * @package BackupBySupsystic\Modules\Amazon
 * @version 1.1
 */
class amazonControllerBup extends controllerBup {
    
    /**
     * Index Action
     *
     * @since  1.0
     */
    public function indexAction() {
        $model = $this->getModel();

        if($model->isSupported() === false) {
            return $this->notSupportAction();
        }

        if($model->isCredentialsSaved() === false) {  
            return $this->getView()->getContent('amazon.form', array(
                'form' => $this->getCredentialsForm(),
            ));
        }
		try {
        return $this->getView()->getContent('amazon.index', array(
            'credentials' => $model->getCredentials(), 
            'bucket'      => $model->getBucket(),
        ));
		} catch(Aws\S3\Exception\InvalidAccessKeyIdException $e) {
			 return $this->getView()->getContent('amazon.form', array(
                'form' => $this->getCredentialsForm(),
				'errors' => array($e->getMessage()),
            ));
		} catch(Exception $e) {
			 return $this->getView()->getContent('amazon.form', array(
                'form' => $this->getCredentialsForm(),
				'errors' => array($e->getMessage()),
            ));
		}
    }
    
    /**
     * System will trigger this action if PHP < 5.3.3 or cURL isnt installed
     *
     * @since  1.0
     */
    public function notSupportAction() {
        
        $messages = array(
            __(sprintf('You server is not support AWS SDK: see %s', '<a href="http://docs.aws.amazon.com/aws-sdk-php/guide/latest/requirements.html" target="_blank">AWS SDK Requirments for PHP</a>'), BUP_LANG_CODE),
            __(sprintf('Your PHP version: %s', PHP_VERSION), BUP_LANG_CODE),
            __(sprintf('cURL extension: %s', extension_loaded('curl') ? 'installed' : 'not installed'), BUP_LANG_CODE),
        );
        
        return $this->getView()->getContent('amazon.error', array(
            'message' => nl2br(implode(PHP_EOL, $messages))
        ));
    }
    
    /**
     * Manage Credentials Action
     * Save credentials to database and if they are not empty ask user for bucket
     *
     * @since  1.0
     */
    public function manageCredentialsAction() {
        $request  = reqBup::get('post');
        $response = new responseBup();
        $model    = $this->getModel();
        //save backup destination
        frameBup::_()->getTable('options')->update(array('value' => 'amazon'), array('code' => 'glb_dest'));

        if($model->storeCredentials($request) === true) {
            $response->addMessage(__('Credentials are stored in the database', BUP_LANG_CODE));
        } else {
            $response->addError(__('Invalid or empty credentials', BUP_LANG_CODE));
        }
        
        if(!empty($request['bucket'])) {
            $model->setBucket($request['bucket']);
            $response->addMessage(__('Bucket stored in the database', BUP_LANG_CODE));
        } else {
            $response->addError(__('Invalid bucket', BUP_LANG_CODE));
        }

        $response->ajaxExec();
    }

    /**
     * Reset Options Action
     * Reset credentials and bucket
     *
     * @since  1.1
     */
    public function resetOptionsAction() {
        $response = new responseBup();

        $this->getModel()
             ->resetCredentials()
             ->resetBucket();

        $response->addMessage(array(__('Please, wait...', BUP_LANG_CODE)));

        return $response->ajaxExec();
    }

    /**
     * Upload Action
     * Upload backups to Amazon S3
     *
     * @since  1.0
     */
    public function uploadAction($files = array()) {
        $request  = reqBup::get('post');
        $response = new responseBup();
        $model    = $this->getModel();
        $stack    = array();
		
		if (empty($files)) {
			$stack = $request['sendArr'];
		} else {
			$stack = $files;
		}
		
		if (!is_array($stack)) {
			$stack = explode(',', $stack);
		}
        
        $result = $model->upload(explode(',', $request['sendArr']));
        
        // Like http responses
        switch($result) {
            case 201:
                $response->addMessage(array(__('Done!', BUP_LANG_CODE)));
                break;
            case 403:
                $response->addError(array(__('You did not specify credentials or credentials are invalid', BUP_LANG_CODE)));
                break;
            case 404:
                $response->addError(array(__('Bucket not found', BUP_LANG_CODE)));
                break;
            default:
                $response->addError(array($result));
        }

        $response->ajaxExec();
    }

    /**
     * Delete Action
     * Delete file on Amazon S3
     *
     * @since  1.1
     * @return void
     */
    public function deleteAction() {
        $request  = reqBup::get('post');
        $response = new responseBup();

        if(!empty($request['deleteLog'])){
            $model = frameBup::_()->getModule('backup')->getModel();
            $logFilename = pathinfo($request['filename']);
            $model->remove($logFilename['filename'].'.txt');
        }

        $result = $this->getModel()->remove($request['filename'], pathinfo($request['filename'], PATHINFO_EXTENSION) == false ? true : false);

        switch($result) {
            case 200: 
                $response->addMessage(array(__('File successfully deleted', BUP_LANG_CODE)));
                break;
            case 400:
                $response->addError(array(__('Not enough input parameters', BUP_LANG_CODE)));
                break;
            case 404:
                $response->addError(array(__('File not found on server', BUP_LANG_CODE)));
                break;
            case 500:
                $response->addError(array(__('Failed to delete the selected file', BUP_LANG_CODE)));
        }

        $response->ajaxExec();
    }

    /**
     * Download Action
     * Downloads file from Amazon S3 to local storage
     * This action triggers before restoring from Amazon S3
     *
     * @since  1.1
     */
    public function downloadAction() {
        $request   = reqBup::get('post');
        $response  = new responseBup();
        /**@var amazonModelBup $model*/
        $model     = $this->getModel();
        $extension = pathinfo($request['filename'], PATHINFO_EXTENSION);
        $filename = pathinfo($request['filename'], PATHINFO_BASENAME);

        if($extension === 'sql' || $extension === 'zip') {
            if (file_exists($model->getBackupsPath() . $filename) || $model->download($request['filename']) === 201) {
                $response->addData(array('filename' => $filename));
            } else {
                $response->addError(array(__('File not found on Amazon S3', BUP_LANG_CODE)));
            }
        } else {
            $stacksFolder = basename($request['filename']) . '/';
            $stacksFileList = $model->getUploadedFiles($stacksFolder);

            if(!empty($stacksFileList)) {
                $backupPath = $model->getBackupsPath();
                $result = true;

                if(!file_exists($backupPath . $stacksFolder)) {
                    frameBup::_()->getModule('warehouse')->getController()->getModel('warehouse')->create($backupPath . $stacksFolder . DS);
                }

                foreach($stacksFileList as $stack) {
                    if(!file_exists($backupPath . $stacksFolder . basename($stack)))
                        $result = ($model->download($stack, $stacksFolder) === 201 && $result) ? true : false;
                }

                if($result) {
                    $response->addData(array('filename' => basename($stacksFolder)));
                } else {
                    $response->addError(__('All stacks not downloaded!', BUP_LANG_CODE));
                    frameBup::_()->getModule('backup')->getController()->getModel('filesystem')->deleteLocalBackup(array($backupPath . $stacksFolder));
                }
            } else {
                $response->addError(__('Files not found on Amazon S3!', BUP_LANG_CODE));
            }
        }

        $response->ajaxExec();
    }

    /**
     * Returns credentials form
     *
     * @since  1.1
     * @param  array  $defaults 
     * @return array
     */
    protected function getCredentialsForm($defaults = array()) {
        return array(
            'legend' => __('Enter your AWS Access Key,<br/>Secret Key and Bucket name', BUP_LANG_CODE),
            'fields' => array(
                array(
                    'label' => __('Access Key', BUP_LANG_CODE),
                    'field' => htmlBup::text('access', array(
                        'value' => (isset($defaults['access']) ? $defaults['access'] : ''),
                        'attrs' => 'class="inputField100per"',
                    )),
                ),
                array(
                    'label' => __('Secret Key', BUP_LANG_CODE),
                    'field' => htmlBup::text('secret', array(
                        'value' => (isset($defaults['access']) ? $defaults['access'] : ''),
                        'attrs' => 'class="inputField100per"',
                    )),
                ),
                array(
                    'label' => __('Bucket', BUP_LANG_CODE),
                    'field' => htmlBup::text('bucket', array(
                        'value' => (isset($defaults['bucket']) ? $defaults['bucket'] : ''),
                        'attrs' => 'class="inputField100per"',
                    )),
                ),
            ),
            'extra' => array(
                htmlBup::button(array('value' => __('&nbsp;&nbsp; Save &nbsp;&nbsp;', BUP_LANG_CODE), 'attrs' => 'class="button button-primary button-large" id="bupAmazonCredentials"')),
            ),
        );
    }
}