<?php

class backupLogTxtModelBup extends modelBup {
    private $_logFileName;
    private $_logDirPath;

    public function __construct() {
        parent::__construct();
        $this->_logDirPath = untrailingslashit(frameBup::_()->getModule('warehouse')->getPath()) . DS;
    }

    public function setLogName($filename) {
        $this->_logFileName = $this->_logDirPath . $filename . '.txt';
    }

    public function getContent($reverse = true) {
        if(file_exists($this->_logFileName) && $content = file($this->_logFileName))
            $content = $reverse ? array_reverse($content) : $content;
        return !empty($content) ? nl2br(implode(null, $content)) : false;
    }

    public function add($string, $time = true) {
        $string = ($time) ? sprintf('[%s] %s', date('Y-m-d H:i:s'), $string) . PHP_EOL : $string . PHP_EOL;

        if(file_exists($this->_logFileName)) {
            file_put_contents($this->_logFileName, $string, FILE_APPEND);
        } else {
            file_put_contents($this->_logFileName, $string);
        }
    }

    /**
     * Write to the log backup settings
     * @param array $settingsArray
     */
    public function writeBackupSettings($settingsArray){
        $text = __('Backup settings: ', BUP_LANG_CODE);
        $settingsStringArray = array();
        if(!empty($settingsArray['full']))
            $settingsStringArray[] = __('Full backup', BUP_LANG_CODE);

        if(!empty($settingsArray['wp_core']))
            $settingsStringArray[] = __('Wordpress Core', BUP_LANG_CODE);

        if(!empty($settingsArray['plugins']))
            $settingsStringArray[] = __('Plugins folder', BUP_LANG_CODE);

        if(!empty($settingsArray['themes']))
            $settingsStringArray[] = __('Themes folder', BUP_LANG_CODE);

        if(!empty($settingsArray['uploads']))
            $settingsStringArray[] = __('Uploads folder', BUP_LANG_CODE);

        if(!empty($settingsArray['any_directories']))
            $settingsStringArray[] = __('Any folder inside wp-content', BUP_LANG_CODE);

        if(!empty($settingsArray['database']))
            $settingsStringArray[] = dispatcherBup::applyFilters('changeDBSettingStringInLog', 'Database backup');

        if(!empty($settingsArray['exclude']))
            $settingsStringArray[] = __('Exclude: ', BUP_LANG_CODE) . $settingsArray['exclude'];

        if(!empty($settingsArray['email_ch']))
            $settingsStringArray[] = __('Email notification: ', BUP_LANG_CODE) . $settingsArray['email'];

        $text .= implode('; ', $settingsStringArray) . '.';
        $this->add($text);
    }

    public function saveBackupDirSetting($backupSize = array()){
        global $wpdb;
        $fields = array('full', 'wp_core', 'plugins', 'themes', 'uploads', 'any_directories', 'exclude');
        $dirSettingsArray = array();

        $query = 'SELECT `value`, `code` FROM `' . $wpdb->prefix . BUP_DB_PREF . 'options` WHERE code IN("' . implode('", "', $fields) . '")';
        $options = $wpdb->get_results($query);

        if(!empty($options)) {
            foreach($options as $option) {
                $dirSettingsArray[$option->code] = $option->value;
            }

            $dirSettingsArray = array_merge($dirSettingsArray, $backupSize);

            if(!empty($dirSettingsArray)) {
                $this->add(__('Please, don\'t delete the line that is lower, it is used for technical purposes!', BUP_LANG_CODE));
                $this->add(serialize($dirSettingsArray), false);
            }
        }
    }

    public function removeLog() {
        if(file_exists($this->_logFileName))
            unlink($this->_logFileName);
    }
}