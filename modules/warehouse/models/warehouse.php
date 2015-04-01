<?php

class warehouseModelBup extends modelBup
{
    const WAREHOUSE_KEY          = 'warehouse';
    const WAREHOUSE_RELATIVE_KEY = 'warehouse_abs';

    /**
    * Checks whether the warehouse is relative path.
    * @return bool
    */
    public function isRelativePath()
    {
        $relative = $this->getOptions()->get(self::WAREHOUSE_RELATIVE_KEY);

        if ($relative == 0) {
            return false;
        }

        return true;
    }

    /**
     * Returns path to the warehouse.
     * @return string
     */
    public function getPath()
    {
        $options = $this->getOptions();

        if ($this->isRelativePath()) {
            return $this->getAbsolutePath() . $this->getOptionValue();
        }

        return $this->getOptionValue();
    }

    /**
     * Checks whether the current warehouse values is directory and exists.
     * @return bool
     */
    public function exists()
    {
        $path = $this->getPath();

        return is_dir($path) && file_exists($path);
    }

    /**
     * Checks whether the warehouse directory is writable.
     * @return bool
     */
    public function isWritable($path = false)
    {
        if(!$path)
            $path = $this->getPath();

        if (!$this->exists()) {
            return false;
        }

        return is_writable($path);
    }

    /**
     * Try to create warehouse folder.
     * @return bool
     */
    public function create($path = false)
    {
        if(!$path)
            $path = $this->getPath() . DIRECTORY_SEPARATOR;

        if (@mkdir($path, 0775, true)) {
            $htaccess = $path . '.htaccess';
            $indexphp = $path . 'index.php';

            @file_put_contents($htaccess, 'DENY FROM ALL', FILE_APPEND);
            @file_put_contents($indexphp, '<?php die("Hacking attempt");');

            return true;
        }
    }

    protected function getOptions()
    {
        return frameBup::_()->getModule('options');
    }

    protected function getOptionValue()
    {
        return $this->getOptions()->get(self::WAREHOUSE_KEY);
    }

    protected function getAbsolutePath()
    {
        return untrailingslashit(ABSPATH);
    }
}
