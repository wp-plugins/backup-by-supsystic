<?php

class logModelBup extends modelBup {

	/**
	 * Returns all finded log files
	 * @return array
	 */
    public function getFilesList() {
        $path    = frameBup::_()->getModule('warehouse')->getPath() . DIRECTORY_SEPARATOR;
        $files   = array();
        $matches = array();

        $nodes = @scandir($path);

        if (!is_array($nodes) || empty($nodes)) {
            return $files;
        }

        foreach ($nodes as $node) {
            if (preg_match('/([\d]+).txt/', $node, $matches)) {

                $backupInfo = $this->getBackupInfoByFilename($node, true);
                $contentArray = file($path . $node, FILE_SKIP_EMPTY_LINES);
                $dirSettings = @unserialize(array_pop($contentArray));
                $backupFolderSize = !empty($dirSettings['backupFolderSize']) ? $dirSettings['backupFolderSize'] : null;
                $settings = !empty($contentArray[0]) ? substr($contentArray[0], strpos($contentArray[0], ']') + 1) : __('Settings not found!', BUP_LANG_CODE);

                $files[$backupInfo['id']] = array(
                    'filepath'  => $path . $node,
                    'filename'  => $node,
                    'backup_id' => $matches[1],
                    'content'   => htmlspecialchars(implode(null, $contentArray)),
                    'settings'  => $settings,
                    'backupFolderSize'  => $backupFolderSize,
                );
            }
        }
        krsort($files);
        return $files;
    }

}
