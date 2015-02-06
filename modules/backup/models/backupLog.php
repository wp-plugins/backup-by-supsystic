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
}
