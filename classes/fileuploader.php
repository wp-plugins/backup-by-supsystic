<?php
	class fileuploaderBup {
		private $_error = '';
		private $_dest = '';
		private $_inputname = '';
        private $_fileInfo = array();
        /**
         * Result filename, if empty - it will be randon generated string
         */
        private $_destFilename = '';
        /**
         * Product ID to attach file, can be 0
         */
        private $_pid = 0;
        /**
         * File type ID, if no product specified or if file is for selling can be 1
         */
        private $_typeId = 1;

        /**
         * Return last error
         * @return string error message
         */
		public function getError() {
			return langBup::_($this->_error);
		}
        public function __construct() {
            
        }
        public function setPid($pid) {
            $this->_pid = (int) $pid;
        }
        public function getPid() {
            return $this->_pid;
        }
        public function setTypeId($typeId) {
            $this->_typeId = (int) $typeId;
        }
        public function getTypeId() {
            return $this->_typeId;
        }
        /**
         * Validate before upload
         * @param string $inputname name of the input HTML document (key in $_FILES array)
         * @param string $destSubDir destination for uploaded file, for wp this should be directory in wp-content/uploads/ dir
         * @param string $filename name of a file that be uploaded
         */
		public function validate($inputname, $destSubDir, $destFilename = '') {
			$res = false;
			if(!empty($_FILES[$inputname]['error'])) {
				switch($_FILES[$inputname]['error']) {
					case '1':
						$this->_error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
						break;
					case '2':
						$this->_error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
						break;
					case '3':
						$this->_error = 'The uploaded file was only partially uploaded';
						break;
					case '4':
						$this->_error = 'No file was uploaded.';
						break;
					case '6':
						$this->_error = 'Missing a temporary folder';
						break;
					case '7':
						$this->_error = 'Failed to write file to disk';
						break;
					case '8':
						$this->_error = 'File upload stopped by extension';
						break;
					case '999':
					default:
						$this->_error = 'No error code avaiable';
				}
			} elseif(empty($_FILES[$inputname]['tmp_name']) || $_FILES[$inputname]['tmp_name'] == 'none') {
				$this->_error = 'No file was uploaded..';
			} else {
				$res = true;
			}
			if($res) {
				//$this->_fileSize = $_FILES[$inputname]['size'];
				$this->_dest = $destSubDir;
				$this->_inputname = $inputname;
                $this->_destFilename = $destFilename;
			}
			return $res;
		}
        /**
         * Upload valid file
         */
		public function upload() {
			$res = false;
            add_filter('upload_dir', array($this,'changeUploadDir'));
            add_filter('wp_handle_upload_prefilter', array($this,'changeFileName'));
            $file = $_FILES[ $this->_inputname ];
            $upload = wp_handle_upload($file, array('test_form' => FALSE));
            if (isset($upload['type']) && !empty($upload['type'])) {
                $this->_fileInfo = $file;
                $this->_fileInfo['name'] = $_FILES[ $this->_inputname ]['name'];
                $this->_fileInfo['path'] = $file['name'];
                $data_to_store = array(
                    'pid' => $this->_pid,
                    'name' => $_FILES[ $this->_inputname ]['name'],
                    'path' => $file['name'],
                    'mime_type' => $upload['type'],
                    'size' => $file['size'],
                    'date' => date('Y-m-d H:i:s'),
                    'active' => 1,
                    'description' => 'some usual file',
                    'download_limit' => 0,
                    'period_limit' => 0,
                    'type_id' => $this->_typeId,
                );
				// We will not use this functionality in this plugin
                //$this->_fileInfo['id'] = frameBup::_()->getTable('files')->insert($data_to_store);
                $res = true;
            } elseif(isset($upload['error']))
				$this->_error = $upload['error'];
            remove_filter('upload_dir', array($this,'changeUploadDir'));
            remove_filter('wp_handle_upload_prefilter', array($this,'changeFileName'));
			return $res;
		}
        public function getFileInfo() {
            return $this->_fileInfo;
        }
        public function changeUploadDir($uploads) {
            $uploads['subdir'] = $this->_dest;
            if(empty($uploads['subdir'])) {
                $uploads['path'] = $uploads['basedir'];
                $uploads['url'] = $uploads['baseurl'];
            } else {
				if(strpos($uploads['subdir'], DS) !== 0)
						$uploads['subdir'] = DS. $uploads['subdir'];
                $uploads['path'] = $uploads['basedir'] . $uploads['subdir'];
                $uploads['url'] = $uploads['baseurl'] . '/'.$uploads['subdir'];
            }
            return $uploads;
        }
        public function changeFileName($file) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if(empty($this->_destFilename))
                $file['name'] = $this->createFileName().'.'.$ext;
            else
                $file['name'] = $this->_destFilename;
            return $file;
        }
        private function createFileName() {
            return utilsBup::getRandStr(). '-'. utilsBup::getRandStr(). '-'. utilsBup::getRandStr(). '-'. utilsBup::getRandStr();
        }
        /**
         * Delete uploaded file
         * @param int $fid ID of file in files table
         */
		public function delete($fid) {
			return false;
		}
	}
?>