<?php

class backupTechLogModelBup extends modelBup {
    private $_logArray = array();
    private $_logFileName;
    private $_logDirPath;

    public function __construct() {
        parent::__construct();
        $this->_logDirPath = frameBup::_()->getModule('warehouse')->getTemporaryPath() . DS;
    }

    public function setLogName($filename) {
        $this->_logFileName = $this->_logDirPath . $filename . '.log';
    }

    public function get($key = false) {
        if(empty($this->_logArray) && file_exists($this->_logFileName))
            $this->_logArray = @unserialize(file_get_contents($this->_logFileName));

        return isset($this->_logArray[$key]) ? $this->_logArray[$key] : false;
    }

    public function set($key, $value = null) {
        $logChanged = false;

        if(is_array($key) && !empty($key)) {
            foreach($key as $newKey => $newValue) {
                if($newValue === null && isset($this->_logArray[$newKey])) {
                    unset($this->_logArray[$newKey]);
                } elseif($newValue !== null) {
                    $this->_logArray[$newKey] = $newValue;
                }
            }
            $logChanged = true;
        } elseif ($value !== null) {
            $this->_logArray[$key] = $value;
            $logChanged = true;
        } elseif ($value === null && isset($this->_logArray[$key])) {
            unset($this->_logArray[$key]);
            $logChanged = true;
        }

        if($logChanged)
            $this->save();
    }

    protected function save() {
        file_put_contents($this->_logFileName, serialize($this->_logArray));
    }

    public function removeLog() {
        if(file_exists($this->_logFileName))
            unlink($this->_logFileName);
    }

    public function deleteOldLogs() {
        $logs = glob($this->_logDirPath . '*.log');
        if(is_array($logs)) {
            foreach($logs as $log) {
                if(file_exists($log))
                    @unlink($log);
            }
        }
    }
}