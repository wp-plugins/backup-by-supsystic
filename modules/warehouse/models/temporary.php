<?php

class temporaryModelBup extends modelBup
{
    private $basePath;

    public function setBasePath($basePath)
    {
        $this->basePath = untrailingslashit($basePath);
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getPath()
    {
        return $this->basePath
            . DIRECTORY_SEPARATOR
            . 'tmp';
    }

    public function exists()
    {
        $path = $this->getPath();

        return is_dir($path) && file_exists($path);
    }

    public function isWritable()
    {
        $path = $this->getPath();

        return $this->exists() && is_writable($path);
    }

    public function create()
    {
        $path = $this->getPath() . DIRECTORY_SEPARATOR;

        if (@mkdir($path, 0775, true)) {
            $htaccess = $path . '.htaccess';
            $indexphp = $path . 'index.php';

            @file_put_contents($htaccess, 'DENY FROM ALL', FILE_APPEND);
            @file_put_contents($indexphp, '<?php die("Hacking attempt");');

            return true;
        }

        return false;
    }

    public function clearAll()
    {
        return $this->clearByPattern('*');
    }

    public function clearByPattern($pattern)
    {
        $files = glob($this->getPath() . DIRECTORY_SEPARATOR . $pattern);

        if (!$files || is_array($files)) {
            $this->pushError(
                sprintf(
                    langBup::_(
                        'Failed to clear temporary folder by pattern "%s"'
                    ),
                    htmlspecialchars($pattern)
                )
            );

            return false;
        }

        foreach ($files as $file) {
            @unlink($file);
        }

        return true;
    }
}
