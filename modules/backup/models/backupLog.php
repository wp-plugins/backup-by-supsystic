<?php


class backupLogModelBup extends modelBup
{

    /** Session key */
    const KEY = 'bup_logger';

    /**
     * Write heading message
     * @param $text
     */
    public function header($text)
    {
        $separator = str_repeat('-', 50);

        $this->write(implode(PHP_EOL, array(
            $separator, $text, $separator
        )));
    }

    /**
     * Write string
     * @param $text
     */
    public function string($text)
    {
        $this->write(sprintf('[%s] %s', date('Y-m-d H:i:s'), $text));
    }

    /**
     * Clear session
     */
    public function clear()
    {
        if (isset($_SESSION[self::KEY])) {
            unset ($_SESSION[self::KEY]);
        }
    }

    /**
     * Save to the log file
     * @param $filename
     * @return int
     */
    public function save($filename)
    {
        return file_put_contents($filename, $this->getContents());
    }

    public function getContents()
    {
        return implode(PHP_EOL, $_SESSION[self::KEY]);
    }

    /** Write to the session */
    protected function write($text)
    {
        $_SESSION[self::KEY][] = $text;
    }

    public function getBackupLog()
    {
        return $_SESSION[self::KEY];
    }

    /**
     * Write to the log backup settings
     * @param array $settingsArray
     */
    public function writeBackupSettings($settingsArray){
        $text = 'Backup settings: ';
        $settingsStringArray = array();
        if(!empty($settingsArray['full']))
            $settingsStringArray[] = 'Full backup';
        if(!empty($settingsArray['wp_core']))
            $settingsStringArray[] = 'Wordpress Core';
        if(!empty($settingsArray['plugins']))
            $settingsStringArray[] = 'Plugins folder';
        if(!empty($settingsArray['themes']))
            $settingsStringArray[] = 'Themes folder';
        if(!empty($settingsArray['uploads']))
            $settingsStringArray[] = 'Uploads folder';
        if(!empty($settingsArray['any_directories']))
            $settingsStringArray[] = 'Any folder inside wp-content';
        if(!empty($settingsArray['safe_update']))
            $settingsStringArray[] = 'Safe Update';
        if(!empty($settingsArray['force_update']))
            $settingsStringArray[] = 'Force Update';
        if(!empty($settingsArray['database']))
            $settingsStringArray[] = 'Database backup';
        if(!empty($settingsArray['exclude']))
            $settingsStringArray[] = 'Exclude: ' . $settingsArray['exclude'];
        if(!empty($settingsArray['email_ch']))
            $settingsStringArray[] = 'Email notification: ' . $settingsArray['email'];

        $text .= implode('; ', $settingsStringArray) . '.';
        $this->string($text);
    }
}
