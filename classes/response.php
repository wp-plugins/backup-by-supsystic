<?php
class responseBup {
    public $code = 0;
    public $error = false;
    public $errors = array();
    public $messages = array();
    public $html = '';
    public $data = array();
    
    public function ajaxExec() {
        $reqType = reqBup::getVar('reqType');
        $redirect = reqBup::getVar('redirect');
        if(count($this->errors) > 0)
            $this->error = true;
        if($reqType == 'ajax')
            exit( json_encode($this) );
        if($redirect)
            redirect($redirect);
        return $this;
    }
    public function error() {
        return $this->error;
    }
    public function addError($error, $key = '') {
        if(empty($error)) return;
        $this->error = true;
        if(is_array($error))
            $this->errors = array_merge($this->errors, $error);
        else {
            if(empty($key))
                $this->errors[] = $error;
            else
                $this->errors[$key] = $error;
        }
    }
    /**
     * Alias for responseBup::addError, @see addError method
     */
    public function pushError($error, $key = '') {
        return $this->addError($error, $key);
    }
    public function addMessage($msg) {
        if(empty($msg)) return;
        if(is_array($msg))
            $this->messages = array_merge($this->messages, $msg);
        else
            $this->messages[] = $msg;
    }
	public function getMessages() {
		return $this->messages;
	}
    public function setHtml($html) {
        $this->html = $html;
    }
    public function addData($data, $value = NULL) {
        if(empty($data)) return;
        if(is_array($data))
            $this->data = array_merge($this->data, $data);
        else
            $this->data[$data] = $value;
    }
    public function getErrors() {
        return $this->errors;
    }
}

