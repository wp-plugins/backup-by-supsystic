<?php

/**
 * Class filesystemModelBup
 */
class filesystemModelBup extends modelBup {

    /**
     * Create filesystem backup
     * @param string $filename File name with full path to the file
     * @return int Number of processed files
     */
    public function create($filename)
    {
        $files = $this->getTemporaryFiles();

        $warehouse = frameBup::_()->getModule('warehouse')->getTemporaryPath();

        $this->getArchive($filename, $files, $warehouse);
    }

    public function restore($filename)
    {
        if (!file_exists($filename)) {
            $this->pushError(sprintf(langBup::_('Filesystem backup %s does not exists'), basename($filename)));
            return false;
        }

        if (!class_exists('PclZip', false)) {
            /** @var backupBup $backup */
            $backup = $this->getModule();
            $backup->loadLibrary('pcl');
        }

        $pcl = new PclZip($filename);

        if ($files = $pcl->extract(PCLZIP_OPT_PATH, ABSPATH, PCLZIP_OPT_REPLACE_NEWER) === 0) {
            $this->pushError(langBup::_('An error has occurred while unpacking the archive'));
            return false;
        }

        unset($pcl);

        // Unpack stacks
        $warehouse = frameBup::_()->getModule('options')->get('warehouse');;
        $stacksPath = realpath(ABSPATH . $warehouse . DS . 'tmp') . DS;
        $stacks = glob($stacksPath . 'BUP*');

        if (empty($stacks)) {
            return true;
        }

        foreach ($stacks as $stack) {
            if (file_exists($stack)) {
                $pcl = new PclZip($stack);

                $pcl->extract(PCLZIP_OPT_PATH, ABSPATH, PCLZIP_OPT_REPLACE_NEWER);

                unlink($stack);
                unset($pcl);
            }
        }

        return true;
    }

    /**
     * Creates a new archive in temporary folder and returns path to the archive
     * @param array $files A numeric array of files
     * @return null|string Path to the tmp file if one or more files has been handled or null.
     */
    public function getTemporaryArchive(array $files)
    {
        $temporary = frameBup::_()->getModule('warehouse')->getTemporaryPath()
            . DIRECTORY_SEPARATOR
            . uniqid('BUP', true);

        $this->getArchive($temporary, $files);

        return $temporary;
    }

    /**
     * Creates a new archive with the specified name and files
     * @param string $name Path to the archive with name and extension
     * @param array $files A numeric array of files
     * @param string $replace
     * @return int How many files has been successfully handled
     */
    public function getArchive($name, array $files, $replace = ABSPATH)
    {
        set_time_limit(300);

        if (!class_exists('Zip', false)) {
            /** @var backupBup $backup */
            $backup = $this->getModule();
            $backup->loadLibrary('zip');
        }

        $zip = new Zip();
        $zip->setZipFile($name);
        $absPath = str_replace('/', DS, ABSPATH);

        foreach ($files as $filename) {

            $file = $absPath . $filename;

            if ((file_exists($file) && is_readable($file))
                && (substr(basename($file), 0, 3) != 'pcl' && substr($file, -2) != 'gz')) {

                $stream = @fopen($file, 'rb');

                if ($stream) {
                    $zip->addLargeFile($stream, $filename);
                }
            }
        }

        $zip->finalize();

        /* backward */
        return rand(100, 1000);
    }

    /**
     * Recursively collect all files and directories from specified path
     * @param string $directory Path
     * @param array $exclude An array of excluded directories
     * @return array|bool
     */
    public function getFilesList($directory, array $exclude = array())
    {
        @set_time_limit(0);
        if (!is_dir($directory) || in_array(basename($directory), $exclude)) {
            return false;
        }

        $absPath = str_replace('/', DS, ABSPATH);

        $nodes = array();

        $directory = glob(realpath($directory) . '/*');

        if ($directory === false) {
            return false;
        }

        foreach ($directory as $node) {
            if (is_dir($node) && file_exists($node)) {

                if (!in_array(basename($node), $exclude)) {
					$addNodes = $this->getFilesList($node, $exclude);
					if(!empty($addNodes) && is_array($addNodes))
						$nodes = array_merge($nodes, $addNodes);
                }

            } elseif (is_file($node) && is_readable($node)) {
                $nodes[] = str_replace($absPath, '', $node);
            }
        }

        return $nodes;
    }

    /**
     * Split comma-separated string to the array and trim any array value
     * @param string $str Comma-separated string with directories to exclude
     * @return array
     */
    public function splitExcludedDirectories($str)
    {
        return array_map('trim', explode(',', $str));
    }

    /**
     * Get list of temporary files
     * @return array
     */
    public function getTemporaryFiles()
    {
        if (is_file($file = frameBup::_()->getModule('warehouse')->getTemporaryPath() . '/stacks.dat')) {
            $data = explode(PHP_EOL, file_get_contents($file));
            unlink($file);

            return $data;
        }

        return array();
    }
}
