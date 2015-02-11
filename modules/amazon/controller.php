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
            'files'       => $model->getUploadedFiles(),
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
            langBup::_(sprintf('You server is not support AWS SDK: see %s', '<a href="http://docs.aws.amazon.com/aws-sdk-php/guide/latest/requirements.html" target="_blank">AWS SDK Requirments for PHP</a>')),
            langBup::_(sprintf('Your PHP version: %s', PHP_VERSION)),
            langBup::_(sprintf('cURL extension: %s', extension_loaded('curl') ? 'installed' : 'not installed')),
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
        
        if($model->storeCredentials($request) === true) {
            $response->addMessage(langBup::_('Credentials are stored in the database'));
        }
        else {
            $response->addError(langBup::_('Invalid or empty credentials'));
        }
        
        if(!empty($request['bucket'])) {
            $model->setBucket($request['bucket']);
            $response->addMessage('Bucket stored in the database');
        }
        else {
            $response->addError('Invalid bucket');
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

        $response->addMessage(array(langBup::_('Please, wait...')));

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
		}
		else {
			$stack = $files;
		}
		
		if (!is_array($stack)) {
			$stack = explode(',', $stack);
		}
        
        $result = $model->upload(explode(',', $request['sendArr']));
        
        // Like http responses
        switch($result) {
            case 201:
                $response->addMessage(array(langBup::_('Done!')));
                break;
            case 403:
                $response->addError(array(langBup::_('You did not specify credentials or credentials are invalid')));
                break;
            case 404:
                $response->addError(array(langBup::_('Bucket not found')));
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
        
        $result = $this->getModel()->remove($request['filename']);

        switch($result) {
            case 200: 
                $response->addMessage(array('File successfully deleted'));
                break;
            case 400:
                $response->addError(array('Not enough input parameters'));
                break;
            case 404:
                $response->addError(array('File not found on server'));
                break;
            case 500:
                $response->addError(array('Failed to delete the selected file'));
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
        $request  = reqBup::get('post');
        $response = new responseBup();
        $model    = $this->getModel();

        if($model->download($request['filename']) === 201) {
            $response->addData(array('filename' => $request['filename']));
        }
        else {
            $response->addError(array(langBup::_('File not found on Amazon S3')));
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
            'legend' => langBup::_('Enter your AWS Access Key, Secret Key and Bucket name'),
            'fields' => array(
                array(
                    'label' => langBup::_('Access Key'),
                    'field' => htmlBup::text('access', array(
                        'value' => (isset($defaults['access']) ? $defaults['access'] : ''),
                    )),
                ),
                array(
                    'label' => langBup::_('Secret Key'),
                    'field' => htmlBup::text('secret', array(
                        'value' => (isset($defaults['access']) ? $defaults['access'] : ''),
                    )),
                ),
                array(
                    'label' => langBup::_('Bucket'),
                    'field' => htmlBup::text('bucket', array(
                        'value' => (isset($defaults['bucket']) ? $defaults['bucket'] : ''),
                    )),
                ),
            ),
            'extra' => array(
                htmlBup::hidden('reqType', array('value' => 'ajax')),
                htmlBup::hidden('page',    array('value' => 'amazon')),
                htmlBup::hidden('action',  array('value' => 'manageCredentialsAction')),
                htmlBup::submit('save',    array(
                    'value' => langBup::_('Store credentials'), 
                    'attrs' => 'class="button button-primary button-large"',
                )),
            ),
        );
    }
}