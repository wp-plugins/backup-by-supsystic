<?php
/**
 * Backup Module for Supsystic Backup
 * @package SupsysticBackup\Modules\Backup
 * @version 2.0
 */
class backupControllerBup extends controllerBup {
    private $_tablesPerStack = 20;

    public function indexAction() {
		$model   = $this->getModel();
        $backups = dispatcherBup::applyFilters('adminGetUploadedFiles', array());
        if(!empty($backups))
            krsort($backups);

		$providers = array();
        $logs = frameBup::_()->getModule('log')->getModel()->getFilesList();

		return $this->render('index', array(
			'backups'   => $backups,
			'logs'     => $logs,
            'model'     => $model,
			'providers' => dispatcherBup::applyFilters('adminSendToLinks', $providers),
		));
	}

    public function createBackupAction() {
        $request = reqBup::get('post');
//        $request = reqBup::get('get');

        if(!empty($request['auth']) && $request['auth'] === AUTH_KEY)
            $this->getModel('backup')->createBackup($request);
    }

	/**
	 * Create Action
	 * Create new backup
	 */
	public function createAction() {
        $request = reqBup::get('post');
        $response = new responseBup();

        /** @var optionsModelBup $optionsModel */
        $optionsModel = frameBup::_()->getModule('options')->getModel();
        /** @var backupLogTxtModelBup $logTxt */
        $logTxt = $this->getModel('backupLogTxt');
        /** @var backupTechLogModelBup $techLog */
        $techLog = $this->getModel('backupTechLog');
        /** @var warehouseBup $bupFolder */
        $bupFolder = frameBup::_()->getModule('warehouse');
        $uploadingList = array();
        $backupComplete = false;

        if(!empty($request['opt_values'])){
            do_action('bupBeforeSaveBackupSettings', $request['opt_values']);
            $optionsModel->saveMainFromDestGroup($request);
            $optionsModel->saveGroup($request);
            $optionsModel->refreshOptions();

            // if warehouse changed - create necessary dir
            $bupFolder = frameBup::_()->getModule('warehouse');
            if (!$bupFolder->getFolder()->exists())
                $bupFolder->getFolder()->create();
        }

        $destination = $this->getModel()->getConfig('dest');
        if($destination !== 'ftp') {
            $isAuthorized = $this->getModel()->checkCloudServiceRemoteServerIsAuth($destination);
            if(!$isAuthorized){
                $response->addError($this->getModel()->getErrors());
                return $response->ajaxExec();
            }
        }

        // We are need to check "warehouse" directory (usually: wp-content/upsupsystic)
        if (!$this->getModel()->checkWarehouse()) {
            $response->addError($this->getModel()->getWarehouseError());

            return $response->ajaxExec();
        }

        if($this->getModel()->isFilesystemRequired() && !$this->checkExtensions($response)) {
            return $response->ajaxExec();
        }

        $currentBackupPath = $this->getModel()->generateFilename(array('zip', 'sql', 'txt'));
        $logTxt->setLogName(basename($currentBackupPath['folder']));
        $logTxt->writeBackupSettings($request['opt_values']);
        $logTxt->add(__('Clear temporary directory', BUP_LANG_CODE));
        $techLog->deleteOldLogs();
        $techLog->setLogName(basename($currentBackupPath['folder']));

        if ($this->getModel()->isDatabaseRequired()) {
            $logTxt->add(__(sprintf('Start database backup: %s', $currentBackupPath['sql']), BUP_LANG_CODE));
            $this->getModel()->getDatabase()->create($currentBackupPath['sql']);
            $dbErrors = $this->getModel()->getDatabase()->getErrors();

            if (!empty($dbErrors)) {
                $logTxt->add(__(sprintf('Errors during creation of database backup, errors count %d', count($dbErrors)), BUP_LANG_CODE));
                $response->addError($dbErrors);
                return $response->ajaxExec();
            }

            $logTxt->add(__('Database backup complete.'), BUP_LANG_CODE);
            $uploadingList[] = $currentBackupPath['sql'];
            $backupComplete = true;
        }

        if ($this->getModel()->isFilesystemRequired()) {
            if(!file_exists($currentBackupPath['folder'])) {
                $bupFolder->getController()->getModel('warehouse')->create($currentBackupPath['folder'] . DS);
            }
            $logTxt->add(__('Scanning files.', BUP_LANG_CODE));
            $files = $this->getModel()->getFilesList();
            // $files = array_map('realpath', $files);

            $logTxt->add(sprintf('%s files scanned.', count($files, true) - count($files)));
            $logTxt->add(__('Total stacks: ' . count($files), BUP_LANG_CODE));
            $techLog->set('stacks', $files);
            $techLog->set('totalStacksCount', count($files));
            $uploadingList[] = $currentBackupPath['folder'];
            $backupComplete = false;
        }

        // if need create filesystem backup or send DB backup on cloud - backup not complete
        if(!empty($files) || $destination !== 'ftp') {
            $backupComplete = false;
            $techInfoArray = array(
                'destination'        => $destination,
                'uploadingList'      => $uploadingList,
                'emailNotifications' => (frameBup::_()->getModule('options')->get('email_ch') == 1) ? true : false
            );
            $techLog->set($techInfoArray);

            $data = array(
                'page' => 'backup',
                'action' => 'createBackupAction',
                'backupId' => $currentBackupPath['folder'],
            );

            if(!empty($files))
                $logTxt->add(__('Send request to generate backup file stacks', BUP_LANG_CODE));

            $this->getModel('backup')->sendSelfRequest($data);
        }

        if($backupComplete && frameBup::_()->getModule('options')->get('email_ch') == 1) {
            $email = frameBup::_()->getModule('options')->get('email');
            $subject = __('Backup by Supsystic Notifications', BUP_LANG_CODE);

            $logTxt->add(__('Email notification required.', BUP_LANG_CODE));
            $logTxt->add(sprintf(__('Sending to ', BUP_LANG_CODE) . '%s', $email));

            $message = $logTxt->getContent(false);
            wp_mail($email, $subject, $message);
        }

        $response->addData(array(
            'backupLog' => $logTxt->getContent(),
            'backupId' => basename($currentBackupPath['folder']),
            'backupComplete' => $backupComplete
        ));

        if($backupComplete) {
            $response->addMessage(__(
                sprintf(
                    'Backup complete. You can restore backup <a href="%s">here</a>.', uriBup::_(array('baseUrl' => get_admin_url(0, 'admin.php?page=' . BUP_PLUGIN_PAGE_URL_SUFFIX . '&tab=' . 'bupLog')))
                ), BUP_LANG_CODE
            ));
        }

        return $response->ajaxExec();
	}

	/**
	 * Restore Action
	 * Restore system and/or database from backup
	 */
	public function restoreAction() {
		$request  = reqBup::get('post');
		$response = new responseBup();
		$filename = $request['filename'];
		$model    = $this->getModel();

        // This block for pro-version module 'scrambler'
        $needKeyToDecryptDB = dispatcherBup::applyFilters('checkIsNeedSecretKeyToEncryptedDB', false, $filename, $request);
        if($needKeyToDecryptDB) {
            $response->addData(array('need' => 'secretKey'));
            return $response->ajaxExec();
        }

		$result = $model->restore($filename);

		if (false === $result) {
            $errors = array_merge($model->getDatabase()->getErrors(), $model->getFilesystem()->getErrors());
            if (empty($errors)) {
                $errors = __('Unable to restore from ' . $filename, BUP_LANG_CODE);
            }
			$response->addError($errors);
		} elseif(is_array($result) && array_key_exists('error', $result)) {
            $response->addError($result['error']);
        } elseif(is_array($result) && !empty($result)) {
            $content = __('Unable to restore backup files. Check folder or files writing permissions. Try to set 766 permissions to the:', BUP_LANG_CODE) . ' <br>'. implode('<br>', $result);
            $response->addError($content);
        } else {
			$response->addData($result);
			$response->addMessage(__('Done!', BUP_LANG_CODE));
		}

        $response->addData(array('result' => $result));
        return $response->ajaxExec();
	}

	/**
	 * Download Action
	 */
	public function downloadAction() {
		$request  = reqBup::get('get');
		$filename = $request['download'];

        $file = frameBup::_()->getModule('warehouse')->getPath() . DS . $filename;

        if (is_file($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;
        }
	}

	/**
	 * Remove Action
	 */
	public function removeAction() {
		$request     = reqBup::get('post');
		$response    = new responseBup();
		$model       = $this->getModel();

        if(!empty($request['deleteLog'])){
            $logFilename = pathinfo($request['filename']);
            $model->remove($logFilename['filename'].'.txt');
        }

		if ($model->remove($request['filename']) === true) {
			$response->addMessage(__('Backup successfully removed', BUP_LANG_CODE));
		}
		else {
			$response->addError(__('Unable to delete backup', BUP_LANG_CODE));
		}

		$response->ajaxExec();
	}

    public function resetAction()
    {
        $request  = reqBup::get('post');
        $response = new responseBup();

        $this->unlock();

        $response->addMessage(__('Successfully!', BUP_LANG_CODE));

        return $response->ajaxExec();
    }

    public function saveRestoreSettingAction(){
        $request     = reqBup::get('post');
        $response    = new responseBup();
        $settingKey = (!empty($request['setting-key'])) ? trim($request['setting-key']) : null;
        $value = (!empty($request['value'])) ? 1 : 0;
        $result = frameBup::_()->getTable('options')->update(array('value' => $value), array('code' => $settingKey));

        if($result)
            $response->addMessage(__('Setting saved!', BUP_LANG_CODE));
        else
            $response->addError(__('Database error, please try again', BUP_LANG_CODE));

        $response->ajaxExec();
    }

	/**
	 * Get model
	 * @param  string $name
	 * @return \backupModelBup
	 */
	public function getModel($name = '') {
		return parent::getModel($name);
	}

	/**
	 *
	 * @param  string $template
	 * @param  array  $data
	 * @return string
	 */
	public function render($template, $data = array()) {
		return $this->getView()->getContent('backup.' . $template, $data);
	}
	public function checkExtensions($res=false) {
		if(!function_exists('gzopen')) {
            $msg = __('There are no zlib extension on your server. This mean that you can make only database backup.<br/>Check this link <a target="_blank" href="http://php.net/manual/en/zlib.installation.php">http://php.net/manual/en/zlib.installation.php</a> or contact your hosting provider and ask them to resolve this issue for you.', BUP_LANG_CODE);
            if(is_a($res, 'responseBup')){
                $res->addError($msg);
                $msg = false;
            }
			return $msg;
		}
		if(!class_exists('ZipArchive')) {
            $msg = __('There are no ZipArchive library on your server. This mean that you can make only database backup.<br/>Check this link <a target="_blank" href="http://php.net/manual/en/book.zip.php">http://php.net/manual/en/book.zip.php</a> or contact your hosting provider and ask them to resolve this issue for you.', BUP_LANG_CODE);
            if(is_a($res, 'responseBup')){
                $res->addError($msg);
                $msg = false;
            }
            return $msg;
		}
		return true;
	}

	public function getPermissions() {
		return array(
			BUP_USERLEVELS => array(
				BUP_ADMIN => array('render', 'getModel', 'removeAction', 'downloadAction', 'restoreAction',
					'createAction', 'indexAction')
			),
		);
	}

    public function checkProcessAction()
    {
        $response = new responseBup();

        $response->addData(
            array(
                'in_process' => frameBup::_()->getModule('backup')->isLocked()
            )
        );

        return $response->ajaxExec();
    }

    public function unlockAction()
    {
        $this->unlock();

        $response = new responseBup();

        $response->addData(array(
            'success' => true,
        ));

        return $response->ajaxExec();
    }

    /**
     * Disallows to do new backups while backup is doing now.
     */
    public function lock()
    {
        frameBup::_()->getModule('backup')->lock();
    }

    /**
     * Allows to do backups.
     */
    public function unlock()
    {
        frameBup::_()->getModule('backup')->unlock();
    }

    public function getBackupLog() {
        $response = new responseBup();
        $request = reqBup::get('post');
        /** @var backupTechLogModelBup $techLog */
        $techLog = $this->getModel('backupTechLog');
        $techLog->setLogName($request['backupId']);
        /** @var backupLogTxtModelBup $log */
        $log = $this->getModel('backupLogTxt');
        $log->setLogName($request['backupId']);
        $backupComplete = $techLog->get('complete');
        $backupMessage = $techLog->get('backupMessage');
        $backupProcessPercent = $techLog->get('backupProcessPercent');
        $filesystemBackupComplete = $techLog->get('filesystemBackupComplete');

        $backupProcessData = array(
            'backupLog' => $log->getContent(),
            'backupComplete' => $backupComplete,
            'backupMessage' => $backupMessage,
            'backupProcessPercent' => $backupProcessPercent,
            'filesystemBackupComplete' => $filesystemBackupComplete,
        );

        $response->addData($backupProcessData);

        if($backupComplete) {
            $techLog->deleteOldLogs();
            $response->addMessage(__('Backup complete!'), BUP_LANG_CODE);
        }

        return $response->ajaxExec();
    }
}
