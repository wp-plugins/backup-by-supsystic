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
				langBup::_('Your server do not have curl extension installed.'),
				langBup::_('This extension is required by Google API SDK.'),
				langBup::_('Please install it at first.'),
				langBup::_('You can also check following links for more info:'),
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

		return $this->getView()->getContent('gdrive.index');
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
				langBup::_('Your server is not support Google API Client Library.'),
				langBup::_(sprintf('Your PHP version: %s (5.2.1 or higher required)', PHP_VERSION)),
				langBup::_('PHP JSON extension required'),
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
        $this->addMessage(langBup::_('Please wait...'));

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
					$response->addMessage(langBup::_('Done!'));
					break;
				case 401:
					$response->addError(langBup::_('Authentication required'));
					break;
				default:
					$response->addMessage($result);
			}
		}
		else {
			$response->addError(langBup::_('Nothing to upload'));
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
			$response->addError(langBup::_('Nothing to delete'));
		}

		if($this->getModel()->remove($request['file']) === true) {
			$response->addMessage(langBup::_('File successfully deleted'));
		}
		else {
			$response->addError(langBup::_('Authentication required'));
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
		$model    = $this->getModel();

		if($model->isAuthenticated() === false) {
			$response->addError(langBup::_('Authentication required'));
		}

		// if there is a local file, then we do not have sense to download it
		// from the server, so just immediately recover from it
		if($model->isLocalFileExists($request['filename']) === false) {
			$result = $model->download($request['download_url'], $request['filename']);

			if($result === true) {
				$response->addData(array('filename' => $request['filename']));
			}
//			if($result) {
//				$response->addData(array('responseBody' => $result));
//			}
			elseif($result === null) {
				$response->addError(langBup::_('File not found on Google Drive'));
			}
			elseif($result === false) {
				$response->addError(langBup::_('Failed to download file'));
			}
		}
		else {
			$response->addData(array('filename' => $request['filename']));
		}
		$response->ajaxExec();
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
