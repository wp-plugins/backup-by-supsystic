<?php

/**
 * Google Drive Controller
 * @package BackupBySupsystic\Modules\GDrive
 * @version 1.3
 */
class gdriveControllerBup extends controllerBup {

	/**
	 * Index Action
	 * If client isn't logged in - show login page, else show backups
	 *
	 * @since  1.1
	 */
    public function indexAction() {
        $model = $this->getModel();
		// Google API SDK require curl in any case
		if(!function_exists('curl_version')) {
			return $this->notSupportAction(array(
				__('Your server do not have curl extension installed.', BUP_LANG_CODE),
				__('This extension is required by Google API SDK.', BUP_LANG_CODE),
				__('Please install it at first.', BUP_LANG_CODE),
				__('You can also check following links for more info:', BUP_LANG_CODE),
				'<a href="http://php.net/manual/en/book.curl.php" target="_blank">http://php.net/manual/en/book.curl.php</a>',
				'<a href="http://php.net/manual/en/curl.installation.php" target="_blank">http://php.net/manual/en/curl.installation.php</a>',
			));
		}
        if($model->isSupported() === false) {
            return $this->notSupportAction();
        }

        if($model->isAuthenticated() === false) {
            return $this->authAction();
        }

        $error = null;
        $refreshToken = frameBup::_()->getTable('options')->get('value', array('code' => 'gdrive_refresh_token'), '', 'row');
        if($model->isAuthenticated() && empty($refreshToken['value']))
            $error = __('For long-term storage of Google Drive authorization, please re-connect our application in your personal account of Google Drive.', BUP_LANG_CODE);

		return $this->getView()->getContent('gdrive.index', array('error' => $error));
    }

	/**
	 * Not Support Action
	 * System will trigger this action when Google API Client Library cant run
	 * on this servicer software
	 *
	 * @since  1.1
	 */
    public function notSupportAction($messages = array()) {
		if(empty($messages)) {
			$messages = array(
				__('Your server is not support Google API Client Library.', BUP_LANG_CODE),
				__(sprintf('Your PHP version: %s (5.2.1 or higher required)', PHP_VERSION), BUP_LANG_CODE),
				__('PHP JSON extension required', BUP_LANG_CODE),
			);
		} elseif(!is_array($messages)) {
			$messages = array($messages);
		}

        return $this->getView()->getContent('gdrive.notSupport', array(
			'messages' => nl2br(implode(PHP_EOL, $messages)),
		));
    }

	/**
	 * Authentication Action
	 * Authenticate client with stored credentials and authentication code.
	 * If credentials aren't stored yet - print form
	 *
	 * @since  1.1
	 */
	public function authAction() {
        $url = $this->getModel()->authenticate();
        $errors = $this->getModel()->getErrors();

        if ($url !== null) {
            return $this->getView()->getContent('gdrive.auth', array(
                'url' => $url,
                'errors' => $errors,
            ));
        }
    }

    /**
     * Reset Credentials Action
     * Reset credentials and client's session
     *
     * @since  1.1
     */
    public function resetCredentialsAction() {
        $response = new responseBup();

        $this->getModel()->resetCredentials();
        $this->addMessage(__('Please wait...', BUP_LANG_CODE));

        return $response->ajaxExec();
    }

    /**
     * Upload Action
     * Upload backup to Google Drive
     *
     * @since  1.1
     */
    public function uploadAction() {
        $request  = reqBup::get('post');
		$response = new responseBup();

		if(isset($request['sendArr'])) {
			$result = $this->getModel()->upload($request['sendArr']);

			switch($result) {
				case 201:
					$response->addMessage(__('Done!', BUP_LANG_CODE));
					break;
				case 401:
					$response->addError(__('Authentication required', BUP_LANG_CODE));
					break;
				default:
					$response->addMessage($result);
			}
		}
		else {
			$response->addError(__('Nothing to upload', BUP_LANG_CODE));
		}

		$response->ajaxExec();
    }

	/**
	 * Delete Action
	 * Removes the selected file from the cloud storage
	 *
	 * @since  1.1
	 */
	public function deleteAction() {
		$request  = reqBup::get('post');
		$response = new responseBup();

        if(!empty($request['deleteLog'])){
            $model    = frameBup::_()->getModule('backup')->getModel();
            $logFilename = pathinfo($request['filename']);
            $model->remove($logFilename['filename'].'.txt');
        }

		if(!isset($request['file']) OR empty($request['file'])) {
			$response->addError(__('Nothing to delete', BUP_LANG_CODE));
		}

		if($this->getModel()->remove($request['file']) === true) {
			$response->addMessage(__('File successfully deleted', BUP_LANG_CODE));
		}
		else {
			$response->addError(__('Authentication required', BUP_LANG_CODE));
		}

		$response->ajaxExec();
	}

    /**
     * Download Action
     * Download file from Google Drive to local server
     */
    public function downloadAction() {
		$request  = reqBup::get('post');
		$response = new responseBup();
        /** @var gdriveModelBup $model*/
		$model    = $this->getModel();
        $result = false;

		if($model->isAuthenticated() === false) {
			$response->addError(__('Authentication required', BUP_LANG_CODE));
		}

		// if there is a local file, then we do not have sense to download it
		// from the server, so just immediately recover from it
        if(!empty($request['download_url'])) {
            if($model->isLocalFileExists($request['filename']) === false) {
                $result = $model->download($request['download_url'], $request['filename']);
            } else {
                $result = true;
            }
        } else {
            $stacksFolder = !empty($request['filename']) ? $request['filename'] : '';
            $stacksList = $model->getUploadedFiles($stacksFolder, true);

            if(!empty($stacksList)){
                $backupPath = $model->getBackupsPath();
                $result = true;

                if(!file_exists($backupPath . $stacksFolder)) {
                    frameBup::_()->getModule('warehouse')->getController()->getModel('warehouse')->create($backupPath . $stacksFolder . DS);
                }

                foreach($stacksList as $stack){
                    if(!file_exists($backupPath . $stacksFolder . DS . $stack['title']))
                        $result = ($model->download($stack['downloadUrl'], $stacksFolder . DS . $stack['title']) && $result) ? true : false;
                }
            } else {
                $response->addError(__('Files not found on Google Drive', BUP_LANG_CODE));
            }
        }

        if($result === true) {
            $response->addData(array('filename' => $request['filename']));
        } elseif($result === null) {
            $response->addError(__('File not found on Google Drive', BUP_LANG_CODE));
        } elseif($result === false) {
            $response->addError(__('Failed to download file', BUP_LANG_CODE));
        }

        return $response->ajaxExec();
	}

    /**
     * Get module's model
     * Please, don't remove this method. It's very useful for development
     * in NetBeans IDE
     *
     * @param string $name
     *
     * @return \gdriveModelBup
     */
	public function getModel($name = '') {
		return parent::getModel();
	}

    /**
     * Save backup destination 'googledrive', when user clicked on Authenticate button
     */
    public function saveBackupDestinationOnAuthenticate(){
        frameBup::_()->getTable('options')->update(array('value' => 'googledrive'), array('code' => 'glb_dest'));
    }
}
