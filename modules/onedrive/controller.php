<?php

/**
 * Class onedriveControllerBup.
 * The controller of the OneDrive module for the Backup By Supsystic.
 */
class onedriveControllerBup extends controllerBup
{
    /**
     * Shows the files list.
     */
    public function indexAction()
    {
        $onedrive = $this->getModel();

        if (!$onedrive->isAuthenticated()) {
            return $this->authAction();
        }

        $onedrive->refreshAccessToken();

        if ($onedrive->haveErrors()) {
            return $this->getView()->getContent('onedrive.error', array(
                'errors' => $onedrive->getErrors(),
            ));
        }

        return $this->getView()->getContent('onedrive.index');
    }

    /**
     * Authorizes the user.
     */
    public function authAction()
    {
        $onedrive = $this->getModel();

        if (!isset($_GET['onedrive'])) {
            return $this->getView()->getContent('onedrive.auth', array(
                'url' => $onedrive->getAuthorizationUrl(),
            ));
        }

        if (!$onedrive->isAuthenticated()) {
            if(!$onedrive->authorize($_GET['onedrive'])) {
                return $this->getView()->getContent('onedrive.authError', array(
                    'errors' => $onedrive->getErrors(),
                ));
            }

            $request = reqBup::get('get');
            $uri = null;
            if(is_array($request)){
                $uri = array();
                foreach($request as $key => $value){
                    if($key != 'onedrive')
                        $uri[] = $key . '=' . $value;
                }
                $uri = 'admin.php?' . join('&', $uri);
            }
            $redirectURI = !empty($uri) ? $uri : 'admin.php?page='.BUP_PLUGIN_PAGE_URL_SUFFIX;

            redirectBup(admin_url($redirectURI));
        }
    }

    /**
     * Destroys the access token from the user's session.
     */
    public function logoutAction()
    {
        $this->getModel()->logout();

        $response = new responseBup();
        $response->addMessage(
            __('Please, wait...', BUP_LANG_CODE)
        );

        return $response->ajaxExec();
    }

    /**
     * Removes files from the OneDrive.
     */
    public function deleteAction()
    {
        $request  = reqBup::get('post');
        $response = new responseBup();

        if(!empty($request['deleteLog'])){
            $model = frameBup::_()->getModule('backup')->getModel();
            $logFilename = pathinfo($request['filename']);
            $model->remove($logFilename['filename'].'.txt');
        }

        $onedrive = $this->getModel();
        $onedrive->refreshAccessToken();

        $result = $onedrive->deleteObject($request['id']);
        if (!$result && $onedrive->haveErrors()) {
            $response->addError($onedrive->getErrors());
        } else {
            $response->addMessage(
                __('Deleted successfully', BUP_LANG_CODE)
            );
        }

        return $response->ajaxExec();
    }

    public function uploadAction()
    {
        $request  = reqBup::get('post');
        $response = new responseBup();

        $files = $request['sendArr'];
        $drive = $this->getModel();

        switch ($drive->upload($files)) {
            case 401:
                $response->addError(
                    __('Authorization required.', BUP_LANG_CODE)
                );
                break;
            case 201:
                $response->addMessage(
                    __('Uploaded successfully.', BUP_LANG_CODE)
                );
                break;
            default:
                if ($drive->haveErrors()) {
                    $response->addError($drive->getErrors());
                } else {
                    $response->addError(
                        __('Unexpected error.', BUP_LANG_CODE)
                    );
                }
        }

        return $response->ajaxExec();
    }

    public function downloadAction()
    {
        $request  = reqBup::get('post');
        $response = new responseBup();
        /**@var onedriveModelBup $onedrive*/
        $onedrive = $this->getModel();
        $extension = pathinfo($request['fileName'], PATHINFO_EXTENSION);

        if($extension === 'sql' || $extension === 'zip') {
            if (file_exists($onedrive->getBackupsPath() . $request['fileName']) || $onedrive->download($request['file_id'])) {
                $response->addMessage(__('File downloaded.', BUP_LANG_CODE));
            } else {
                $response->addError($onedrive->getErrors());
            }
        } else {
            $stacksFolder = !empty($request['fileName']) ? $request['fileName'] : '';
            $stacksFileList = $onedrive->getUserFiles($stacksFolder);

            if(!empty($stacksFileList)) {
                $backupPath = $onedrive->getBackupsPath();
                $result = true;

                if(!file_exists($backupPath . $stacksFolder)) {
                    frameBup::_()->getModule('warehouse')->getController()->getModel('warehouse')->create($backupPath . $stacksFolder . DS);
                }

                foreach($stacksFileList as $stack){
                    if(!file_exists($backupPath . $stacksFolder . DS . $stack->name))
                        $result = ($onedrive->download($stack->id, false, $stacksFolder . DS) && $result) ? true : false;
                }
            } else {
                $response->addError(__('Files not found on OneDrive', BUP_LANG_CODE));
            }
        }

        return $response->ajaxExec();
    }

    public function saveBackupDestinationOnAuthenticate(){
        frameBup::_()->getTable('options')->update(array('value' => 'onedrive'), array('code' => 'glb_dest'));
    }
}
