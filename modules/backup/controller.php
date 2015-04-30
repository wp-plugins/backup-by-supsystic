<?php
/**
 * Backup Module for Supsystic Backup
 * @package SupsysticBackup\Modules\Backup
 * @version 2.0
 */
class backupControllerBup extends controllerBup {

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

	/**
	 * Create Action
	 * Create new backup
	 */
	public function createAction() {
        $request = reqBup::get('post');
        $response = new responseBup();
        /** @var backupLogModelBup $log */
        $log = $this->getModel('backupLog');

        if(!empty($request['opt_values'])){
            do_action('bupBeforeSaveBackupSettings', $request['opt_values']);
            $log->writeBackupSettings($request['opt_values']);
            frameBup::_()->getModule('options')->getModel('options')->saveMainFromDestGroup($request);
            frameBup::_()->getModule('options')->getModel('options')->saveGroup($request);
            frameBup::_()->getModule('options')->getModel('options')->refreshOptions();

            // if warehouse changed - create necessary dir
            $bupFolder = frameBup::_()->getModule('warehouse');
            if (!$bupFolder->getFolder()->exists())
                $bupFolder->getFolder()->create();
        }

        // We are need to check "warehouse" directory (usually: wp-content/upsupsystic)
        if (!$this->getModel()->checkWarehouse()) {
            $response->addError($this->getModel()->getWarehouseError());

            return $response->ajaxExec();
        }

        if($this->getModel()->isFilesystemRequired() && !$this->_checkExtensions($response)) {
            return $response->ajaxExec();
        }

        $filename = $this->getModel()->generateFilename(array('zip', 'sql', 'txt'));
        $cloud = array();

        if ($this->getModel()->isFilesystemRequired()) {
            if(!empty($request['opt_values']))
                $log->saveBackupDirSetting($request['opt_values']);
            if (!isset($request['complete'])) {
                // Disallow to do backups while backup already in proccess.
                $this->lock();

                $files = $this->getModel()->getFilesList();
                // $files = array_map('realpath', $files);

                $log->string(sprintf('%s files scanned.', count($files)));

                $warehouse = frameBup::_()->getModule('warehouse')->getPath();
                $dir = frameBup::_()->getModule('warehouse')->getTemporaryPath();

                $log->string(__('Clear out old temporary files', BUP_LANG_CODE));
                if (file_exists($file = $dir . '/stacks.dat')) {
                    if (@unlink($file)) {
                        $log->string(__(sprintf('%s successfully deleted', basename($file)), BUP_LANG_CODE));
                    } else {
                        $log->string(__(sprintf('Cannot delete file %s. If you notice a problem with archives - delete the file manually', $file), BUP_LANG_CODE));
                    }
                }
                $tmpDirFiles = glob($dir . '/*');
                if(!empty($tmpDirFiles) && is_array($tmpDirFiles)) {
                    foreach ($tmpDirFiles as $tmp) {
                        if (substr(basename($tmp), 0, 3) === 'BUP') {
                            if (@unlink($tmp)) {
                                $log->string(__(sprintf('%s successfully deleted', $tmp), BUP_LANG_CODE));
                            } else {
                                $log->string(__(sprintf('Cannot delete file %s', $tmp), BUP_LANG_CODE));
                            }
                        }
                    }
                }

                // Defined in ./config.php
                if (!defined('BUP_FILES_PER_STACK')) {
                    define('BUP_FILES_PER_STACK', 500);
                }

                $response->addData(array(
                    'files'     => $files,
                    'per_stack' => BUP_FILES_PER_STACK,
                ));

                $log->string(__('Send request to generate temporary file stacks', BUP_LANG_CODE));

                return $response->ajaxExec();
            }

            $log->string(__(sprintf('Create a backup of the file system: %s', $filename['zip']), BUP_LANG_CODE));
            $this->getModel()->getFilesystem()->create($filename['zip']);
            $cloud[] = $filename['zip'];
        }

        if ($this->getModel()->isDatabaseRequired()) {
            // Disallow to do backups while backup already in proccess.
            $this->lock();

            $log->string(__(sprintf('Create a backup of the database: %s', $filename['sql']), BUP_LANG_CODE));
            $this->getModel()->getDatabase()->create($filename['sql']);
            $dbErrors = $this->getModel()->getDatabase()->getErrors();
            if(!empty($dbErrors)) {
                $log->string(__(sprintf('Errors during creation of database backup, errors count %d', count($dbErrors)), BUP_LANG_CODE));
                $response->addError( $dbErrors );
                return $response->ajaxExec();
            }
            $cloud[] = $filename['sql'];
        }

        $log->string(__('Backup complete', BUP_LANG_CODE));

        $destination = $this->getModel()->getConfig('dest');
        $handlers    = $this->getModel()->getDestinationHandlers();

        if (array_key_exists($destination, $handlers)) {

            $cloud = array_map('basename', $cloud);

            $log->string(__(sprintf('Upload to the "%s" required', ucfirst($destination)), BUP_LANG_CODE));
            $log->string(sprintf('Files to upload: %s', rtrim(implode(', ', $cloud), ', ')));
            $handler = $handlers[$destination];
            $result  = call_user_func_array($handler, array($cloud));
            if ($result === true || $result == 200 || $result == 201) {
                $log->string(__(sprintf('Successfully uploaded to the "%s"', ucfirst($destination)), BUP_LANG_CODE));

                $path = frameBup::_()->getModule('warehouse')->getPath();
                $path = untrailingslashit($path);

                foreach ($cloud as $file) {
                    $log->string(__(sprintf('Removing %s from the local storage.', $file), BUP_LANG_CODE));
                    if (@unlink($path . '/' . $file)) {
                        $log->string(__(sprintf('%s successfully removed.', $file), BUP_LANG_CODE));
                    } else {
                        $log->string(__(sprintf('Failed to remove %s', $file), BUP_LANG_CODE));
                    }
                }
            } else {
                switch ($result) {
                    case 401:
                        $error = __('Authentication required.', BUP_LANG_CODE);
                        break;
                    case 404:
                        $error = __('File not found', BUP_LANG_CODE);
                        break;
                    case 500:
                        $error = is_object($handler[0]) ? $handler[0]->getErrors() : __('Unexpected error (500)', BUP_LANG_CODE);
                        break;
                    default:
                        $error = __('Unexpected error', BUP_LANG_CODE);
                }

                $log->string(__(
                    sprintf(
                        'Cannot upload to the "%s": %s',
                        ucfirst($destination),
                        is_array($error) ? array_pop($error) : $error
                    )
                , BUP_LANG_CODE));
            }
        }



        $response->addMessage(__('Backup complete', BUP_LANG_CODE));

        // Allow to do new backups.
        $this->unlock();

        if (frameBup::_()->getModule('options')->get('email_ch') == 1) {
            $email = frameBup::_()->getModule('options')->get('email');
            $subject = __('Backup by Supsystic Notifications', BUP_LANG_CODE);

            $log->string(__('Email notification required.', BUP_LANG_CODE));
            $log->string(sprintf(__('Sending to', BUP_LANG_CODE) . '%s', $email));

            $message = $log->getContents();

            wp_mail($email, $subject, $message);
        }

        $log->save($filename['txt']);
        $log->clear();

        return $response->ajaxExec();
	}

    /**
     * Create Stack Action
     * Creates stacks of files with BUP_FILER_PER_STACK files limit and returns temporary file name
     */
    public function createStackAction() {
		@set_time_limit(0);

        $request = reqBup::get('post');
        $response = new responseBup();

        /** @var backupLogModelBup $log */
        $log = $this->getModel('backupLog');

        if (!isset($request['files'])) {
            return;
        }

        $log->string(__(sprintf('Trying to generate a stack of %s files', count($request['files'])), BUP_LANG_CODE));

        $filesystem = $this->getModel()->getFilesystem();
        $filename = $filesystem->getTemporaryArchive($request['files']);
        if(frameBup::_()->getModule('options')->get('warehouse_abs') == 1){
            $absPath = str_replace('/', DS, ABSPATH);
            $filename = str_replace('/', DS, $filename);
            $filename = str_replace($absPath, '', $filename);
        }

        if ($filename === null) {
            $log->string(__('Unable to create the temporary archive', BUP_LANG_CODE));
            $response->addError(__('Unable to create the temporary archive', BUP_LANG_CODE));
        } else {
            $log->string(__(sprintf('Temporary stack %s successfully generated', $filename), BUP_LANG_CODE));
            $response->addData(array('filename' => $filename));
        }

        return $response->ajaxExec();
    }

    public function writeTmpDbAction()
    {
        $request = reqBup::get('post');

        if (isset($request['tmp'])) {
            $file = frameBup::_()->getModule('warehouse')->getTemporaryPath()
                . DIRECTORY_SEPARATOR
                . 'stacks.dat';

            file_put_contents($file, $request['tmp'] . PHP_EOL, FILE_APPEND);
        }
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
        if($needKeyToDecryptDB){
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
		}
        elseif(is_array($result) && array_key_exists('error', $result)) {
            $response->addError($result['error']);
        }
        elseif(is_array($result) && !empty($result)) {
            $content = __('Unable to restore backup files. Check folder or files writing permissions. Try to set 766 permissions to the:', BUP_LANG_CODE) . ' <br>'. implode('<br>', $result);
            $response->addError($content);
        }
		else {
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
	private function _checkExtensions($res) {
		if(!function_exists('gzopen')) {
			$res->addError(__('There are no zlib extension on your server. You need to install it. How to install check this link <a target="_blank" href="http://php.net/manual/en/zlib.installation.php">http://php.net/manual/en/zlib.installation.php</a>', BUP_LANG_CODE));
			return false;
		}
		if(!class_exists('ZipArchive')) {
			$res->addError(__('There are no ZipArchive library on your server. You need to install it. How to install check this link <a target="_blank" href="http://php.net/manual/en/book.zip.php">http://php.net/manual/en/book.zip.php</a>', BUP_LANG_CODE));
			return false;
		}
		return true;
	}
	public function getPermissions() {
		return array(
			BUP_USERLEVELS => array(
				BUP_ADMIN => array('render', 'getModel', 'removeAction', 'downloadAction', 'restoreAction', 'writeTmpDbAction',
					'createStackAction', 'createAction', 'indexAction')
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

    public function getBackupLog()
    {
        $response = new responseBup();

        $response->addData(
            array(
                'backupLog' => frameBup::_()->getModule('backup')->getModel('backupLog')->getBackupLog(),
            )
        );

        return $response->ajaxExec();
    }
}
