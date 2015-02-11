<?php

/**
 * Dropbox Module Controller
 *
 * @package BackupBySupsystic\Modules\Dropbox
 * @version 1.2
 */
class dropboxControllerBup extends controllerBup {

	/**
	 * Instance of Dropbox model
	 * @var \dropboxModelBup
	 */
	protected $model = null;

    public $modelType = null;

	/**
	 * Prefix for view files
	 * @var string
	 */
	protected $templatePrefix = 'dropbox';

    public function __construct($code = '') {
        parent::__construct($code);

        $model = frameBup::_()->getModule('options')->get('dropbox_model');
        $this->model = $this->getModel($model);
    }

	/**
	 * Index Action
	 *
	 * @since  1.0
	 */
	public function indexAction() {

		if($this->model->isAuthenticated() === false) {
			return $this->authenticateAction();
		}
		try {
			$tabHtml = $this->render('index', array(
				'files' => $this->model->getUploadedFiles(),
				'info'  => $this->model->getQuota(),
			));
		} catch(RuntimeException $e) {
			return $this->authenticateAction(array('errors' => $e->getMessage()));
		} catch (Exception $e) {
			return $this->authenticateAction(array('errors' => $e->getMessage()));
		}
		return $tabHtml;
	}

	/**
	 * Authenticate Action
	 *
	 * @since  1.0
	 */
	public function authenticateAction($errors = array()) {
		$request = reqBup::get('get');

        if (BUP_PLUGIN_PAGE_URL_SUFFIX !== $request['page']) {
            return;
        }

		if(!isset($request['dropboxToken'])) {
			$url  = 'http://supsystic.com/authenticator/index.php/authenticator/dropbox';
			$slug = frameBup::_()->getModule('adminmenu')->getView()->getFile();
			if(!empty($errors) && !is_array($errors))
				$errors = array($errors);
			return $this->render('auth', array(
                'authUrl' => $url . '?ref=' . base64_encode(admin_url('admin.php?page=' . $slug)),
				'errors' => $errors,
            ));
		}
		else {
			$authResult = $this->model->authenticate($request['dropboxToken']);

			if($authResult === false) {
				return $this->model->getErrors();
			}
			else {
                redirect(admin_url('admin.php?page='.BUP_PLUGIN_PAGE_URL_SUFFIX));
			}
		}
	}

    /**
     * Logout Action
     *
     * @since 1.2
     */
    public function logoutAction() {
		$response = new responseBup();

		session_destroy();
        $this->model->removeToken();

		$response->addMessage(langBup::_('Please, wait...'));
		$response->ajaxExec();
	}

	/**
	 * Not Support Action
	 *
	 * @since  1.0
	 */
	public function notSupportAction() {
		$curl = curl_version();

		$messages = array(
			langBup::_('Your server not meet the requirements Dropbox SDK' . PHP_EOL),
			langBup::_(sprintf('Your PHP version: %s (5.3.1 or higher required)', PHP_VERSION)),
			langBup::_(sprintf('cURL extension: %s (cURL extension is required)', extension_loaded('curl') ? 'installed' : 'not installed')),
			langBup::_(sprintf('cURL SSL version: %s (OpenSSL is required)', $curl['ssl_version'])),
		);

		return $this->render('notSupport', array(
			'messages' => nl2br(implode(PHP_EOL, $messages)),
		));
	}

	/**
	 * Upload Action
	 *
	 * @since  1.0
	 */
	public function uploadAction($files = array()) {
        $request  = reqBup::get('post');
		$response = new responseBup();
		$stack    = array();

		if(!empty($files)) {
			$stack = array_merge($stack, $files);
		}

		if(isset($request['sendArr']) && !empty($request['sendArr'])) {
			if(!is_array($request['sendArr'])) {
				$request['sendArr'] = explode(',', $request['sendArr']);
			}
		}

		$stack = array_merge($stack, $request['sendArr']);

		$result = $this->model->upload($stack);

		switch($result) {
			case 200:
				$response->addMessage(langBup::_('Done!'));
				break;
			case 401:
				$response->addError(langBup::_('Authentication required'));
				break;
			case 404:
				$response->addError(langBup::_('Nothing to upload'));
				break;
			case 500:
				$response->addError($this->model->getErrors());
				break;
			default:
				$response->addMessage($result);
		}

		return $response->ajaxExec();
	}

	/**
	 * Delete Action
	 *
	 * @since  1.0
	 */
	public function deleteAction() {
		$request  = reqBup::get('post');
		$response = new responseBup();

		if(!isset($request['file']) OR empty($request['file'])) {
			$response->addError(langBup::_('Nothing to delete'));
		}

		if($this->model->remove($request['file']) === true) {
			$response->addMessage(langBup::_('File successfully deleted'));
		}
		else {
			$response->addError($this->model->getErrors());
		}

		$response->ajaxExec();
	}

	/**
	 * Restore Action
	 *
	 * @since  1.0
	 */
	public function restoreAction() {
		$request  = reqBup::get('post');
		$response = new responseBup();

		if(!isset($request['file']) OR empty($request['file'])) {
			$response->addError(langBup::_('There was an error during recovery'));
		}

		if($this->model->download($request['file']) === true) {
			$response->addData(array('filename' => $request['file']));
		}
		else {
			$response->addError($this->model->getErrors());
		}

		return $response->ajaxExec();
	}

	/**
	 * Render view file
	 *
	 * @since  1.0
	 * @param  string $template
	 * @param  array  $data
	 * @return string
	 */
	public function render($template, $data = array()) {
		return $this->getView()->getContent($this->templatePrefix . '.' . $template, $data);
	}
}
