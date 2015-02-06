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
				$content = file_get_contents($path . $node);
				$linesArray = preg_split('/\n|\r/', $content);
				$lines = count($linesArray);

				$files[$backupInfo['id']] = array(
					'filepath'  => $path . $node,
					'filename'  => $node,
					'backup_id' => $matches[1],
					'lines'     => $lines,
					'content'   => $content,
				);
			}
		}
		krsort($files);
		return $files;
	}

}
