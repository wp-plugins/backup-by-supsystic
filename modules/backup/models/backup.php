<?php

/**
 * Class backupModelBup
 * Filesystem & Database facade
 * Database and filesystem models need to implement some interface, but i think
 * in here it is not critical
 */
class backupModelBup extends modelBup {

    /**
     * @var filesystemModelBup
     */
    protected $filesystem;

    /**
     * @var databaseModelBup
     */
    protected $database;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $warehouseError;

    /**
     * @var int
     */
    public $id;

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var backupBup $backup */
        $backup = frameBup::_()->getModule('backup');
        /** @var backupControllerBup $controller */
        $controller = $backup->getController();

        if ($this->filesystem === null) {
            $this->filesystem = $controller->getModel('filesystem');
        }

        if ($this->database === null) {
            $this->database = $controller->getModel('database');
        }

        /* Set configuration array in the database model. For backward compatibility */
        $this->database->setConfig($this->getConfig());
    }

    /**
     * Generate filename with specified extensions
     * @param array $extensions An array of required extensions
     * @return array An associative array with filename
     */
    public function generateFilename(array $extensions)
    {
        $pattern = 'backup_{datetime}_id{id}.{extension}';
        $search = array('{datetime}', '{id}', '{extension}');
        $replace = array(date('Y_m_d-H_i_s'), $this->getId());
        $warehouse = frameBup::_()->getModule('warehouse')->getPath();
        $names = array();

        foreach ($extensions as $extension) {
            $replace = array_merge($replace, array('{extension}' => $extension));
            $names[$extension] = rtrim($warehouse, '/') . '/' . str_replace($search, $replace, $pattern);
        }

        return $names;
    }

    /**
     * Get next identifier
     * @return int|mixed
     */
    public function getId()
    {
        if ($this->id === null) {
            $files = scandir($this->getConfig('warehouse'));

            $matches = array();
            $results = array();

            foreach($files as $file) {
                if(preg_match('/id([\d]+)/', $file, $matches)) {
                    $results[] = $matches[1];
                }
            }

            if(!empty($results)) {
                $this->id = max($results) + 1;
            }
            else {
                $this->id = 1;
            }
        }

        return $this->id;
    }
    /**
     * Return all founded backups
     * @return null|array
     */
    public function getBackupsList() {
        $config  = $this->getConfig();
        $pattern = '/(backup_([0-9_-]*)_id([0-9]+))\.(zip|sql)/ui';
        $backups = array();

        $dir = @scandir($config['warehouse']);

        if (!is_array($dir) || empty($dir)) {
            return array();
        }

        foreach ($dir as $file) {
            $backupInfo = $this->getBackupInfoByFilename($file);

            if (!empty($backupInfo)) {

                $backups[$backupInfo['id']]['ftp'][strtolower($backupInfo['ext'])] = array(
                    'id'   => $backupInfo['id'],
                    'name' => $backupInfo['name'],
                    'raw'  => $backupInfo['raw'],
                    'ext'  => $backupInfo['ext'],
                    'date' => $backupInfo['date'],
                    'time' => $backupInfo['time']
                );
                $backups[$backupInfo['id']]['ftp'][strtolower($backupInfo['ext'])] = dispatcherBup::applyFilters('addInfoIfEncryptedDb', $backups[$backupInfo['id']]['ftp'][strtolower($backupInfo['ext'])]);
            }
        }
        krsort($backups);
        return $backups;
    }

    /**
     * Remove backup
     * @param string $filename File name of the backup
     * @return bool TRUE if file exists and successfully removed, FALSE otherwise.
     */
    public function remove($filename)
    {
        if (file_exists($file = $this->getConfig('warehouse') . $filename)) {
            if (unlink($file)) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Restore from backup
     * @param string $filename
     * @return bool
     */
    public function restore($filename)
    {
        if (file_exists($file = $this->getConfig('warehouse') . $filename)) {

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if ($ext === 'sql') {
                return $this->database->restore($file);
            } elseif ($ext === 'zip') {
                return $this->filesystem->restore($file);
            }

            return false;
        }

        return false;
    }

    /**
     * Is database backup required?
     * @return bool TRUE if required, FALSE otherwise.
     */
    public function isDatabaseRequired()
    {
        return $this->isSomethingRequired(array(
            'full_backup', 'database'
        ));
    }

    /**
     * Is filesystem backup required?
     * @return bool TRUE if require, FALSE otherwise.
     */
    public function isFilesystemRequired()
    {
        return $this->isSomethingRequired(array(
            'full_backup', 'any_dir', 'plugins_dir', 'themes_dir', 'uploads_dir', 'wp_core'
        ));
    }

    /**
     * Returns an array of excluded directories
     * @return array
     */
    public function getExcludedDirectories()
    {
        $directories = $this->getConfig('exclude');

        if (empty($directories)) {
            return array();
        }

        return $this->filesystem->splitExcludedDirectories($directories);
    }

    /**
     * Set excluded directories
     * @param string|array $directories Comma-separated string or an array of directories
     * @param bool $replace If TRUE then specified directories will replace current, FALSE - append to the end.
     */
    public function setExcludedDirectories($directories, $replace = false)
    {
        /** @var optionsBup $options */
        $options = frameBup::_()->getModule('options');

        if (is_array($directories)) {
            $directories = implode(',', $directories);
        }

        if (!$replace) {
            $exclude = $this->getConfig('exclude');

            $directories = implode(',', array(rtrim($exclude, ','), $directories));
        }

        $this->config['exclude'] = $directories;
    }

    /**
     * Is specified directory is in excluded list?
     * @param string $directory Name of the directory
     * @return bool TRUE if it in the list, FALSE otherwise.
     */
    public function isExcluded($directory)
    {
        $excluded = $this->getConfig('exclude');

        foreach ($this->filesystem->splitExcludedDirectories($excluded) as $excludedDirectory) {
            if (strtolower($excludedDirectory) === strtolower($directory)) {
                return true;
            }
        }

        return false;
    }

    public function getFilesList($optionsModel = false)
    {
        $excluded = array(BUP_PLUG_NAME, BUP_PLUG_NAME_PRO);
        if(!$optionsModel)
            $options  = frameBup::_()->getModule('options');
        else
            $options  = $optionsModel;

        // Where we are need to look for files.
        $directory = realpath(ABSPATH);

        // Is full backup?
        $isFull = $options->get('full');

        // Default folders inside wp-content
        $defaults = array('themes', 'plugins', 'uploads');

        // Excluded folder by user.
        $dbexcluded = $options->get('exclude');

        // Folder that contains backups.
        $warehouseDir = $options->get('warehouse_ignore');

        $excluded = array_merge(
            $excluded,
            array_map('trim', explode(',', $dbexcluded))
        );

        // Exclude plugin's "warehouse".
        if (!in_array($warehouseDir, $excluded)) {
            $excluded[] = $warehouseDir;
        }

        // If any directory inside "wp-content" isn't selected, then exclude all nodes from it.
        if (0 == $options->get('any_directories')) {

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
        if (0 == $options->get('plugins')) {
            $excluded[] = 'plugins';
        }

        if (0 == $options->get('themes')) {
            $excluded[] = 'themes';
        }

        if (0 == $options->get('uploads')) {
            $excluded[] = 'uploads';
        }

        // If it is not full backup then we need to looking for files only inside wp-content.
        if (0 == $isFull) {
            $directory = trailingslashit($directory) . BUP_WP_CONTENT_DIR;
        }

        $fileList = $this->filesystem->getFilesList($directory, $excluded);

        if(1 == $options->get('wp_core')){
            $directory = realpath(ABSPATH);
            unset($excluded);
            $excluded = array(BUP_WP_CONTENT_DIR);
            $wpCoreFileList = $this->filesystem->getFilesList($directory, $excluded);
            $fileList = array_merge($fileList,  $wpCoreFileList);
        }

        return $fileList;
    }

    /**
     * Get the database model
     * @return databaseModelBup
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get the filesystem model
     * @return filesystemModelBup
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    public function getDestinationHandlers() {
        $handlers = array();
        $handlers = dispatcherBup::applyFilters('adminBackupUpload', $handlers);

        return $handlers;
    }

    /**
     * Get backup configurations
     * @param string|array $item The keys to get from config
     * @return mixed
     */
    public function getConfig($item = null)
    {
        if ((!is_array($this->config) || empty($this->config)) || count($this->config) < 1) {

            /** @var optionsBup $options */
            $options = frameBup::getInstance()->getModule('options');

            $this->config = array(
                'full_backup'   => (bool)$options->get('full'),
                'wp_core'       => (bool)$options->get('wp_core'),
                'plugins_dir'   => (bool)$options->get('plugins'),
                'themes_dir'    => (bool)$options->get('themes'),
                'uploads_dir'   => (bool)$options->get('uploads'),
                'database'      => (bool)$options->get('database'),
                'any_dir'       => (bool)$options->get('any_directories'),
                'exclude'       => $options->get('exclude'),
                'warehouse'     => frameBup::_()->getModule('warehouse')->getPath() . DIRECTORY_SEPARATOR,
                'dest'          => $options->get('glb_dest'),
                // Since 2.0
                'force_update'  => (bool)$options->get('force_update'),
                'safe_update'   => (bool)$options->get('safe_update'),
                'replace_newer' => (bool)$options->get('replace_newer'),
            );
        }

        if ($item === null) {
            return $this->config;
        }

        if (is_string($item)) {
            return (isset($this->config[$item]) ? $this->config[$item] : null);
        }

        if (is_array($item)) {
            $config = array();

            foreach ($item as $key) {
                if (isset($this->config[$key])) {
                    $config[$key] = $this->config[$key];
                }
            }

            return $config;
        }

        return null;
    }

    public function checkWarehouse()
    {
        if (!frameBup::_()->getModule('warehouse')->getWarehouseStatus()) {
            $this->warehouseError = 'Can\'t create warehouse directory or it\'s not writable.';

            return false;
        }

        return true;
    }

    public function getWarehouseError()
    {
        return $this->warehouseError;
    }

    /**
     * Is some backup type is required or not
     * @param array $keys An array of keys to check
     * @return bool TRUE if one of the keys is TRUE, FALSE otherwise.
     */
    protected function isSomethingRequired(array $keys)
    {
        foreach ($keys as $key) {
            $value = $this->getConfig($key);
            if (null !== $value) {
                if ((bool)$value) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * This function return backup start date and time
     * @param string $logContent
     * @return string
     */
    public function getBackupStartTimeFromLog($logContent)
    {
        $dateTime = null;
        if(is_string($logContent)){
            $logFileArray = explode(']', $logContent);
            foreach($logFileArray as $logString) {
                if(false !== $datePos = strpos($logString, '[')) {
                    $dateTime = substr($logString, $datePos + 1);
                    break;
                }
            }
            return $dateTime;
        } else {
            return false;
        }
    }

    /**
     * This function return backup finish date and time
     * @param string $logContent
     * @return string
     */
    public function getBackupFinishTimeFromLog($logContent)
    {
        if(is_string($logContent)){
            $logFileArray = explode('[', $logContent);
            $content = array_pop($logFileArray);
            $dateTime = substr($content, 0, strpos($content, ']'));
            return ($dateTime) ? $dateTime : false;
        } else {
            return false;
        }
    }
}
