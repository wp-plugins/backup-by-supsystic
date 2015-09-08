<?php

/**
 * Class filesystemModelBup
 */
class filesystemModelBup extends modelBup {
    private $_maxFileSizeInStack; //in mb

    public function __construct() {
        parent::__construct();
        $this->_maxFileSizeInStack = frameBup::_()->getModule('options')->get('max_file_size_in_stack_mb');
        $this->_maxFileSizeInStack = ($this->_maxFileSizeInStack > 0) ? $this->_maxFileSizeInStack * 1024 * 1024 : 30 * 1024 * 1024;
    }
    /**
     * Create filesystem backup
     * @param string $filename File name with full path to the file
     * @return int Number of processed files
     */
    public function create($filename)
    {
        $files = $this->getTemporaryFiles();

        $warehouse = frameBup::_()->getModule('warehouse')->getTemporaryPath();
        if(frameBup::_()->getModule('options')->get('warehouse_abs') == 0)
            $this->getArchive($filename, $files, $warehouse, true);
        else
            $this->getArchive($filename, $files, $warehouse);
    }

    public function restore($filename, $oneFileBackup = false)
    {
        if (!file_exists($filename)) {
            $this->pushError(sprintf(__('Filesystem backup %s does not exists', BUP_LANG_CODE), basename($filename)));
            return false;
        }
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $logFilename = empty($extension) ? $filename . '.txt' : str_replace('.zip', '.txt', $filename);
        if(file_exists($logFilename)) {
            $logContent = file($logFilename);
            $backupDirSettings = unserialize(array_pop($logContent));
            $files = $this->getFilesListByBUPDirSettingArray($backupDirSettings);

            if(!empty($files))
                return $files;
        }

        if (!class_exists('PclZip', false)) {
            /** @var backupBup $backup */
            $backup = $this->getModule();
            $backup->loadLibrary('pcl');
        }
        if($oneFileBackup) {
            /**/
            $this->clearTmpDirectory(); // remove all temporary files from tmp directory, before filesystem restore process started
            $absolutePath = frameBup::_()->getModule('options')->get('warehouse_abs') ? false : true;

            $pcl = new PclZip($filename);
            if($absolutePath) {
                $absPath = explode(DS, ABSPATH);
                $absPath = $absPath[0].DS;
            } else {
                $absPath = ABSPATH;
            }

            if ($files = $pcl->extract(PCLZIP_OPT_PATH, $absPath, PCLZIP_OPT_REPLACE_NEWER) === 0) {
                $this->pushError(__('An error has occurred while unpacking the archive', BUP_LANG_CODE));
                return false;
            }

            unset($pcl);

            // Unpack stacks
            $warehouse = frameBup::_()->getModule('options')->get('warehouse');

            if($absolutePath)
                $stacksPath = realpath($warehouse . DS . 'tmp') . DS;
            else
                $stacksPath = realpath(ABSPATH . $warehouse . DS . 'tmp') . DS;

            $stacks = glob($stacksPath . 'BUP*');

        } else {
            $stacks = glob($filename . DS . 'BUP*');
        }

        if (empty($stacks)) {
            return true;
        }

        foreach ($stacks as $stack) {
            if (file_exists($stack)) {
                $pcl = new PclZip($stack);

                $pcl->extract(PCLZIP_OPT_PATH, ABSPATH, PCLZIP_OPT_REPLACE_NEWER);

                if($oneFileBackup)
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
    public function getTemporaryArchive(array $files, $backupFolder)
    {
        $temporary = $backupFolder
            . DS
            . uniqid('BUP')
            . '.zip';

        $this->getArchive($temporary, $files);

        return $temporary;
    }

    /**
     * Creates a new archive with the specified name and files
     * @param string $name Path to the archive with name and extension
     * @param array $files A numeric array of files
     * @param string $replace
     * @param string $fullPath
     * @return int How many files has been successfully handled
     */
    public function getArchive($name, array $files, $replace = ABSPATH, $fullPath = false)
    {
        set_time_limit(300);

        if (!class_exists('Zip', false)) {
            /** @var backupBup $backup */
            $backup = $this->getModule();
            $backup->loadLibrary('zip');
        }

        $zip = new Zip();
        $zip->setZipFile($name);
        if($fullPath)
            $absPath = null;
        else
            $absPath = str_replace('/', DS, ABSPATH);

        foreach ($files as $filename) {

            $file = $absPath . $filename;
            if($fullPath)
                $file = str_replace('\\\\', DS, $filename );

            if ((file_exists($file) && is_readable($file))
                && (substr(basename($file), 0, 3) != 'pcl' && substr($file, -2) != 'gz')) {

                $stream = @fopen($file, 'rb');

                if ($stream) {
                    $zip->addLargeFile($stream, $filename);
                }
            }
        }

        $zip->finalize();

        if(false !== strpos($name, 'backup_')) // if backup created - remove all temporary files from tmp directory
            $this->clearTmpDirectory();

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
            if(stripos($directory, BUP_PLUG_NAME) !== false || stripos($directory, BUP_PLUG_NAME_PRO) !== false) {
                return false;
            }

            $continue = false;

            if( stripos($directory, 'themes') !== false ) {
                if(in_array('themes', $exclude)){
                    return false;
                } else {
                    $continue = true;
                }
            } elseif( stripos($directory, 'plugins') !== false ) {
                if(in_array('plugins', $exclude)){
                    return false;
                } else {
                    $continue = true;
                }
            }

            if(!$continue) {
                return false;
            }
        }

//        $absPath = str_replace('/', DS, ABSPATH);
        $absPath = rtrim(rtrim(ABSPATH, '/'), '\\');

        $nodes = array();

        $directory = glob(realpath($directory) . '/*');

        if ($directory === false) {
            return false;
        }

        foreach ($directory as $node) {
            if (is_dir($node) && file_exists($node)) {
                if ( in_array(basename($node), $exclude) && ( stripos($node, 'themes')===false && stripos($node, 'plugins')===false ) ) {
                    continue;
                } else {
                    $addNodes = $this->getFilesList($node, $exclude);
                    if(!empty($addNodes) && is_array($addNodes))
                        $nodes = array_merge($nodes, $addNodes);
                }

            } elseif (is_file($node) && is_readable($node) && (($fileSize = filesize($node)) <= $this->_maxFileSizeInStack)) {
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

    public function getFilesListByBUPDirSettingArray($options)
    {
        $excluded = $this->getModule('backup')->getController()->getModel('backup')->getDefaultExcludedFolders();

        // Where we are need to look for files.
        $directory = realpath(ABSPATH);

        // Is full backup?
        $isFull = $options['full'];

        // Default folders inside wp-content
        $defaults = array('themes', 'plugins', 'uploads');

        // Excluded folder by user.
        $dbexcluded = $options['exclude'];

        // Folder that contains backups.
        $warehouseDir = 'upsupsystic'; // this value writing in 'options' only in plugin installation process

        $excluded = array_merge(
            $excluded,
            array_map('trim', explode(',', $dbexcluded))
        );

        // Exclude plugin's "warehouse".
        if (!in_array($warehouseDir, $excluded)) {
            $excluded[] = $warehouseDir;
        }

        // If any directory inside "wp-content" isn't selected, then exclude all nodes from it.
        if (0 == $options['any_directories']) {

            $nodes = glob(untrailingslashit(WP_CONTENT_DIR) . '/*');

            if (is_array($nodes) && !empty($nodes)) {
                foreach ($nodes as $node) {
                    if (!in_array($nodeName = basename($node), $defaults)) {
                        $excluded[] = $nodeName;
                    }
                }
            }
        }

        // What about plugins, themes and uploads?
        if (0 == $options['plugins']) {
            $excluded[] = 'plugins';
        }

        if (0 == $options['themes']) {
            $excluded[] = 'themes';
        }

        if (0 == $options['uploads']) {
            $excluded[] = 'uploads';
        }

        // If it is not full backup then we need to looking for files only inside wp-content.
        if (0 == $isFull) {
            $directory = trailingslashit($directory) . BUP_WP_CONTENT_DIR;
        }

        $fileList = $this->getNotWritableFiles($directory, $excluded);

        if(1 == $options['wp_core']){
            $directory = realpath(ABSPATH);
            unset($excluded);
            $excluded = array(BUP_WP_CONTENT_DIR);
            $wpCoreFileList = $this->getNotWritableFiles($directory, $excluded);
            $fileList = array_merge($fileList,  $wpCoreFileList);
        }

        return $fileList;
    }

    public function getNotWritableFiles($directory, $exclude){
        @set_time_limit(0);
        $nodes = array();
        $directory = glob(realpath($directory) . '/*');

        if ($directory === false) {
            return false;
        }

        foreach($directory as $node){
            if(!is_writable($node) && file_exists($node) && !in_array(basename($node), $exclude)){
                $nodes[] = $node;
            }
        }

        return $nodes;
    }

    private function clearTmpDirectory(){
        $tmpPath = untrailingslashit(frameBup::_()->getModule('warehouse')->getPath()) . DS . 'tmp' . DS;
        $tmpFiles = glob($tmpPath . 'BUP*');
        if (is_array($tmpFiles)) {
            foreach ($tmpFiles as $file) {
                if (file_exists($file))
                    unlink($file);
            }
        }
    }
    /** Delete local backup after uploading to cloud
     * @param $backups
     */
    public function deleteLocalBackup(array $backups){
        foreach($backups as $backup){
            $extension = pathinfo($backup, PATHINFO_EXTENSION);

            if($extension != 'txt' && file_exists($backup)) {
                if(is_dir($backup)){
                    $files = scandir($backup);

                    foreach($files as $file) {
                        $file = $backup . DS . $file;
                        if(is_file($file))
                            unlink($file);
                    }

                    rmdir($backup);
                } else {
                    unlink($backup);
                }
            }
        }
    }
}
