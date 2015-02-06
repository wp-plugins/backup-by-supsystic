<?php
/**
 * Backup Module for Supsystic Backup
 * @package SupsysticBackup\Modules\Backup
 * @version 2.0
 */
class backupControllerBup extends controllerBup {

    public function indexAction() {
		$model   = $this->getModel();
        $backups = $model->getBackupsList();
        $gDrive  = frameBup::_()->getModule('gdrive')->getModel()->getUploadedFiles();
        if(!empty($gDrive))
		    $backups +=  $gDrive;
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
        frameBup::_()->getModule('options')->getModel('options')->saveMainFromDestGroup($request);
        frameBup::_()->getModule('options')->getModel('options')->saveGroup($request);

        // We are need to check "warehouse" directory (usually: wp-content/upsupsystic)
        if (!$this->getModel()->checkWarehouse()) {
            $response->addError($this->getModel()->getWarehouseError());

            return $response->ajaxExec();
        }

        if(!$this->_checkExtensions($response)) {
            return $response->ajaxExec();
        }

        $filename = $this->getModel()->generateFilename(array('zip', 'sql', 'txt'));

        /** @var backupLogModelBup $log */
        $log = $this->getModel('backupLog');
        $cloud = array();

        if ($this->getModel()->isFilesystemRequired()) {
            if (!isset($request['complete'])) {
                // Disallow to do backups while backup already in proccess.
                $this->lock();

                $files = $this->getModel()->getFilesList();
                // $files = array_map('realpath', $files);

                $log->string(sprintf('%s files scanned.', count($files)));

                $warehouse = frameBup::_()->getModule('warehouse')->getPath();
                $dir = frameBup::_()->getModule('warehouse')->getTemporaryPath();

                $log->string('Clear out old temporary files');
                if (file_exists($file = $dir . '/stacks.dat')) {
                    if (@unlink($file)) {
                        $log->string(sprintf('%s successfully deleted', basename($file)));
                    } else {
                        $log->string(sprintf('Cannot delete file %s. If you notice a problem with archives - delete the file manually', $file));
                    }
                }
                $tmpDirFiles = glob($dir . '/*');
                if(!empty($tmpDirFiles) && is_array($tmpDirFiles)) {
                    foreach ($tmpDirFiles as $tmp) {
                        if (substr(basename($tmp), 0, 3) === 'BUP') {
                            if (@unlink($tmp)) {
                                $log->string(sprintf('%s successfully deleted', $tmp));
                            } else {
                                $log->string(sprintf('Cannot delete file %s', $tmp));
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

                $log->string('Send request to generate temporary file stacks');

                return $response->ajaxExec();
            }

            $log->string(sprintf('Create a backup of the file system: %s', $filename['zip']));
            $this->getModel()->getFilesystem()->create($filename['zip']);
            $cloud[] = $filename['zip'];
        }

        if ($this->getModel()->isDatabaseRequired() && !isset($request['complete'])) {
            // Disallow to do backups while backup already in proccess.
            $this->lock();

            $log->string(sprintf('Create a backup of the database: %s', $filename['sql']));
            $this->getModel()->getDatabase()->create($filename['sql']);
            $dbErrors = $this->getModel()->getDatabase()->getErrors();
            if(!empty($dbErrors)) {
                $log->string(sprintf('Errors during creation of database backup, errors count %d', count($dbErrors)));
                $response->addError( $dbErrors );
                return $response->ajaxExec();
            }
            $cloud[] = $filename['sql'];
        }

        $log->string('Backup complete');

        $destination = $this->getModel()->getConfig('dest');
        $handlers    = $this->getModel()->getDestinationHandlers();

        if (array_key_exists($destination, $handlers)) {

            $cloud = array_map('basename', $cloud);

            $log->string(sprintf('Upload to the <%s> required', ucfirst($destination)));
            $log->string(sprintf('Files to upload: %s', rtrim(implode(', ', $cloud), ', ')));
            $handler = $handlers[$destination];
            $result  = call_user_func_array($handler, array($cloud));
            if ($result === true || $result == 200 || $result == 201) {
                $log->string(sprintf('Successfully uploaded to the <%s>', ucfirst($destination)));

                $path = frameBup::_()->getModule('warehouse')->getPath();
                $path = untrailingslashit($path);

                foreach ($cloud as $file) {
                    $log->string(sprintf('Removing %s from the local storage.', $file));
                    if (@unlink($path . '/' . $file)) {
                        $log->string(sprintf('%s successfully removed.', $file));
                    } else {
                        $log->string(sprintf('Failed to remove %s', $file));
                    }
                }
            } else {
                switch ($result) {
                    case 401:
                        $error = 'Authentication required.';
                        break;
                    case 404:
                        $error = 'File not found';
                        break;
                    case 500:
                        $error = is_object($handler[0]) ? $handler[0]->getErrors() : 'Unexpected error (500)';
                        break;
                    default:
                        $error = 'Unexpected error';
                }

                $log->string(
                    sprintf(
                        'Cannot upload to the <%s>: %s',
                        ucfirst($destination),
                        is_array($error) ? array_pop($error) : $error
                    )
                );
            }
        }



        $response->addMessage(langBup::_('Backup complete'));

        // Allow to do new backups.
        $this->unlock();

        if (frameBup::_()->getModule('options')->get('email_ch') == 1) {
            $email = frameBup::_()->getModule('options')->get('email');
            $subject = 'Backup by Supsystic Notifications';

            $log->string('Email notification required.');
            $log->string(sprintf('Sending to %s', $email));

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

        $log->string(sprintf('Trying to generate a stack of %s files', count($request['files'])));

        $filesystem = $this->getModel()->getFilesystem();
        $filename = $filesystem->getTemporaryArchive($request['files']);

        if ($filename === null) {
            $log->string('Unable to create the temporary archive');
            $response->addError(langBup::_('Unable to create the temporary archive'));
        } else {
            $log->string(sprintf('Temporary stack %s successfully generated', $filename));
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

		$result = $model->restore($filename);

		if (false === $result) {
            $errors = array_merge($model->getDatabase()->getErrors(), $model->getFilesystem()->getErrors());
            if (empty($errors)) {
                $errors = langBup::_('Unable to restore from ' . $filename);
            }
			$response->addError($errors);
		}
		else {
			$response->addData($result);
			$response->addMessage(langBup::_('Done!'));
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

        $file = frameBup::_()->getModule('warehouse')->getPath() . DIRECTORY_SEPARATOR
            . $filename;

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
        $logFilename = pathinfo($request['filename']);


		if ($model->remove($request['filename']) === true &&  $model->remove($logFilename['filename'].'.txt') === true) {
			$response->addMessage(langBup::_('Backup successfully removed'));
		}
		else {
			$response->addError(langBup::_('Unable to delete backup'));
		}

		$response->ajaxExec();
	}

    public function resetAction()
    {
        $request  = reqBup::get('post');
        $response = new responseBup();

        $this->unlock();

        $response->addMessage(langBup::_('Successfully!'));

        return $response->ajaxExec();
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
			$res->addError(langBup::_('There are no zlib extension on your server. Check this link <a target="_blank" href="http://php.net/manual/en/zlib.installation.php">http://php.net/manual/en/zlib.installation.php</a>'));
			return false;
		}
		if(!class_exists('ZipArchive')) {
			$res->addError(langBup::_('There are no zib extension on your server. Check this link <a target="_blank" href="http://php.net/manual/en/book.zip.php">http://php.net/manual/en/book.zip.php</a>'));
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
