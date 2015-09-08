<?php

abstract class modelBup extends baseObjectBup {
    protected $_data = array();
	protected $_code = '';
    public $backupPlaceAuthErrorMsg;

    public function __construct() {
        $this->backupPlaceAuthErrorMsg = __('Before start backup, You must login to ');
    }
    
    public function init() {

    }
    public function get($d = array()) {

    }
    public function put($d = array()) {

    }
    public function post($d = array()) {

    }
    public function delete($d = array()) {

    }
    public function store($d = array()) {
        
    }
	public function setCode($code) {
        $this->_code = $code;
    }
    public function getCode() {
        return $this->_code;
    }
	public function getModule() {
		return frameBup::_()->getModule( $this->_code );
	}
    
    public function getBackupInfoByFilename($filename, $logTxt=false) {
        $pathInfo = pathinfo($filename);
        $folder = empty($pathInfo['extension']) ? true : false;

        if($logTxt)
            $pattern = '/(backup_([0-9_-]*)_id([0-9]+))\.(txt)/ui';
        elseif($folder)
            $pattern = '/(backup_([0-9_-]*)_id([0-9]+))/ui';
        else
            $pattern = '/(backup_([0-9_-]*)_id([0-9]+))\.(zip|sql)/ui';
        $matches = array();

        if (preg_match($pattern, $filename, $matches)) {
            if($folder)
                list ($name, $rawname, $date, $id) = $matches;
            else
                list ($name, $rawname, $date, $id, $extension) = $matches;

            $e = explode('-', $date);
            $datetime['date'] = str_replace('_', '-', $e[0]);
            $datetime['time'] = str_replace('_', ':', $e[1]);

            return array(
                'id'   => $id,
                'name' => $name,
                'raw'  => $rawname,
                'ext'  => !empty($extension) ? $extension : null,
                'date' => $datetime['date'],
                'time' => $datetime['time'],
            );
        }
    }

    /**
     * This method used to check is user authorized in cloud service or remote server, where backup files will be stored
     */
    public function isUserAuthorizedInService($destination = null) {
        $this->pushError(__('Unexpected error.', BUP_LANG_CODE));
        return false;
    }

    public function sendSelfRequest(array $data) {
        $data['auth'] = AUTH_KEY;
        $data['pl'] = BUP_CODE;
        $url = get_option('siteurl');
        $string = http_build_query($data);
        $response = wp_remote_post($url, array(
                'body' => $data
            )
        );

        return ($response) ? true : false;
    }
}
