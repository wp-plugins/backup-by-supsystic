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

            return redirectBup(admin_url(
                'admin.php?page='.BUP_PLUGIN_PAGE_URL_SUFFIX
            ));
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
            langBup::_('Please, wait...')
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
                langBup::_('Deleted successfully')
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

        $drive->refreshAccessToken();

        switch ($drive->upload($files)) {
            case 401:
                $response->addError(
                    langBup::_('Authorization required.')
                );
                break;
            case 201:
                $response->addMessage(
                    langBup::_('Uploaded successfully.')
                );
                break;
            default:
                if ($drive->haveErrors()) {
                    $response->addError($drive->getErrors());
                } else {
                    $response->addError(
                        langBup::_('Unexpected error.')
                    );
                }
        }

        return $response->ajaxExec();
    }

    public function downloadAction()
    {
        $request  = reqBup::get('post');
        $response = new responseBup();
        $onedrive = $this->getModel();
        $onedrive->refreshAccessToken();

        if ($onedrive->download($request['file_id'])) {
            $response->addMessage('File downloaded.');
        } else {
            $response->addError($onedrive->getErrors());
        }

        return $response->ajaxExec();
    }

    public function saveBackupDestinationOnAuthenticate(){
        frameBup::_()->getTable('options')->update(array('value' => 'onedrive'), array('code' => 'glb_dest'));
    }
}
