<?php
class optionsModelBup extends modelBup {
    protected $_allOptions = array();


	public function saveGroup($d = array()) {
		$clearArr = array('opt_values' => array('full' => 0, 'plugins' => 0, 'themes' =>0 , 'uploads' => 0, 'database' => 0, 'any_directories' => 0, 'wp_core' => 0));

		if(isset($d['opt_values']) && is_array($d['opt_values']) && !empty($d['opt_values'])) {

			foreach($clearArr['opt_values'] as $code=>$value) { // set 0 to all array
				$clearArr['code'] = $code;
				$this->save($clearArr);
			}

			if (isset($d['opt_values']['email_ch']) && !empty($d['opt_values']['email_ch'])) {
                $this->setValueType('email', $d['opt_values']['email_ch']);
            } else {
                $this->setValueType('email', '');
            }

			foreach($d['opt_values'] as $code => $value) {
				$d['code'] = $code;
				// if ($code == 'email_ch') continue;
				if ($code == 'email') {
					if (!$this->validateEmail($value)) {
                        $this->pushError(__('Incorrect email address', BUP_LANG_CODE));
                        continue;
                    }
                }
				$this->save($d);
				if ($code == 'full') {
					//frameBup::_()->getTable('options')->update(array('value' => 1), array('code' => 'database')); //outdated
				}
			}

			return !$this->haveErrors();
		} else
			$this->pushError(__('No change', BUP_LANG_CODE));
	}

	public function set($value, $code){
        frameBup::_()->getTable('options')->update(array('value' => $value), array('code' => $code));
	}

	public function getEvery(){
        return frameBup::_()->getTable('options')->get('*', "value_type = 'every'", BUP_WPDB_PREF.BUP_DB_PREF.'options', 'all');
	}

	public function saveMainFromDestGroup($d = array()) {
		if (isset($d['dest_opt']) && !empty($d['dest_opt'])){
            if(isset($d['opt_values']['warehouse']) && isset($d['opt_values']['warehouse_abs']))
                $this->saveBackupPath(array('warehouse' => $d['opt_values']['warehouse'], 'warehouse_abs' => (int)$d['opt_values']['warehouse_abs']));
			if (utilsBup::checkPRO() || $d['dest_opt'] == 0){
				$this->set($d['dest_opt'], 'glb_dest');
			} else {
				$this->pushError(__('PRO version is not activated', BUP_LANG_CODE));
			}
			return !$this->haveErrors();
		} else
			$this->pushError(__('No selected options', BUP_LANG_CODE));
	}

// ---- old -----
    public function get($d = array()) {
        $this->_loadOptions();
        $code = false;
        if(is_string($d))
            $code = $d;
        elseif(is_array($d) && isset($d['code']))
            $code = $d['code'];
        if($code) {
            $opt = $this->_getByCode($code);
            if(isset($d['what']) && isset($opt[$d['what']]))
                return $opt[$d['what']];
            else
                return $opt['value'];
        } else {
            return $this->_allOptions;
        }
    }
	public function getValueType($d) {
		$ret = frameBup::_()->getTable('options')->get('value_type', "code = '".$d."'", BUP_WPDB_PREF.BUP_DB_PREF.'options', 'all');
		return $ret[0]['value_type'];
	}
	public function setValueType($code, $value) {
		frameBup::_()->getTable('options')->update(array('value_type' => $value), array('code' => $code));
	}
	public function validateEmail($email){
		if ($this->getValueType('email')){
		  if (preg_match('~^([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}$~', $email, $regs)) {
			  return true;
		  } else {
			  return false;
		  }
		} else
			return true; // if email_ch disabled
	}



	public function isEmpty($d = array()) {
		$value = $this->get($d);
		return empty($value);
	}
	public function getByCategories($category = '') {
		$this->_loadOptions();
		$categories = array();
		$returnForCat = !empty($category);	// If this is not empty - will be returned anly for one category
		foreach($this->_allOptions as $opt) {
			if(empty($category)
				|| (is_numeric($category) && $category == $opt['cat_id'])
				|| ($category == $opt['cat_label'])
			) {
				if(empty($categories[ (int)$opt['cat_id'] ]))
					$categories[ (int)$opt['cat_id'] ] = array('cat_id' => $opt['cat_id'], 'cat_label' => $opt['cat_label'], 'opts' => array());
				$categories[ (int)$opt['cat_id'] ]['opts'][] = $opt;
				if($returnForCat)	// Save category ID for returning
					$returnForCat = (int)$opt['cat_id'];
			}
		}
		if($returnForCat)
			return $categories[ $returnForCat ];
		ksort($categories);
		return $categories;
	}
	public function getByCode($d = array()) {
		$res = array();
		$codeData = $this->get($d);
		if(empty($d)) {
			// Sort by code
			foreach($codeData as $opt) {
				$res[ $opt['code'] ] = $opt;
			}
		} else
			$res = $codeData;
		return $res;
	}
    /**
     * Load all options data into protected array
     */
    protected function _loadOptions() {
        if(empty($this->_allOptions)) {
            $options = frameBup::_()->getTable('options');
            $htmltype = frameBup::_()->getTable('htmltype');
			$optionsCategories = frameBup::_()->getTable('options_categories');
            $this->_allOptions = $options->innerJoin($htmltype, 'htmltype_id')
					->leftJoin($optionsCategories, 'cat_id')
					->orderBy(array('cat_id', 'sort_order'))
                    ->getAll($options->alias(). '.*, '. $htmltype->alias(). '.label AS htmltype, '. $optionsCategories->alias(). '.label AS cat_label');
            foreach($this->_allOptions as $i => $opt) {
                if(!empty($this->_allOptions[$i]['params'])) {
                    $this->_allOptions[$i]['params'] = utilsBup::unserialize($this->_allOptions[$i]['params']);
                }
				if($this->_allOptions[$i]['value_type'] == 'array') {
					$this->_allOptions[$i]['value'] = utilsBup::unserialize($this->_allOptions[$i]['value']);
					if(!is_array($this->_allOptions[$i]['value']))
						$this->_allOptions[$i]['value'] = array();
				}
				if(empty($this->_allOptions[$i]['cat_id'])) {	// Move all options that have no category - to Other
					$this->_allOptions[$i]['cat_id'] = 6;
					$this->_allOptions[$i]['cat_label'] = 'Other';
				}
            }
        }
    }
    /**
     * Refresh all options data into protected array
     */
    public function refreshOptions() {
        $options = frameBup::_()->getTable('options');
        $htmltype = frameBup::_()->getTable('htmltype');
        $optionsCategories = frameBup::_()->getTable('options_categories');
        $this->_allOptions = $options->innerJoin($htmltype, 'htmltype_id')
            ->leftJoin($optionsCategories, 'cat_id')
            ->orderBy(array('cat_id', 'sort_order'))
            ->getAll($options->alias(). '.*, '. $htmltype->alias(). '.label AS htmltype, '. $optionsCategories->alias(). '.label AS cat_label');
        foreach($this->_allOptions as $i => $opt) {
            if(!empty($this->_allOptions[$i]['params'])) {
                $this->_allOptions[$i]['params'] = utilsBup::unserialize($this->_allOptions[$i]['params']);
            }
            if($this->_allOptions[$i]['value_type'] == 'array') {
                $this->_allOptions[$i]['value'] = utilsBup::unserialize($this->_allOptions[$i]['value']);
                if(!is_array($this->_allOptions[$i]['value']))
                    $this->_allOptions[$i]['value'] = array();
            }
            if(empty($this->_allOptions[$i]['cat_id'])) {	// Move all options that have no category - to Other
                $this->_allOptions[$i]['cat_id'] = 6;
                $this->_allOptions[$i]['cat_label'] = 'Other';
            }
        }
    }
    /**
     * Returns option data by it's code
     * @param string $code option's code
     * @return array option's data
     */
    protected function _getByCode($code) {
        $this->_loadOptions();
        if(!empty($this->_allOptions)) {
            foreach($this->_allOptions as $opt) {
                if($opt['code'] == $code)
                    return $opt;
            }
        }
        return false;
    }

	/**
     * Set option value by code, do no changes in database
     * @param string $code option's code
	 * @param string $value option's new value
     */
	protected function _setByCode($code, $value) {
        $this->_loadOptions();
        if(!empty($this->_allOptions)) {
            foreach($this->_allOptions as $id => $opt) {
                if($opt['code'] == $code) {
					$this->_allOptions[ $id ]['value'] = $value;
                    break;
				}
            }
        }
    }
    public function save($d = array()) {
        $id = 0;
		if(isset($d['opt_values']) && is_array($d['opt_values']) && !empty($d['opt_values'])) {
			if(isset($d['code']) && !empty($d['code'])) {
				$d['what'] = 'id';
				$id = $this->get($d);
				$id = intval($id);
				//echo $id.'|';
			}
			if($id) {
				$updateData = array('value' => $d['opt_values'][ $d['code'] ]);
				$checkArr = $this->get(array('code' => $d['code'], 'what' => 'value_type'));
				if($checkArr == 'array' && !empty($checkArr)) {
					$updateData['value'] = utilsBup::serialize( $updateData['value'] );
				}
				if(frameBup::_()->getTable('options')->update($updateData, array('id' => $id))) {
					// Let's update data in current options params to avoid reload it from database
					if(isset($d['code']))
						$this->_setByCode($d['code'], $d['opt_values'][ $d['code'] ]);
					return true;
				} else
					$this->pushError(__('Option '. $d['code']. ' update Failed', BUP_LANG_CODE));
			} else {
				$this->pushError(__('Invalid option ID or Code', BUP_LANG_CODE));
			}
		} else
			$this->pushError(__('Empty data to save option', BUP_LANG_CODE));
        return false;
    }
	public function saveCodeVal($code, $val) {
		if(frameBup::_()->getTable('options')->exists($code, 'code')) {
			frameBup::_()->getTable('options')->update(array(
				'value' => $val,
			), array('code' => $code));
			$this->_setByCode($code, $val);
		} else {
			frameBup::_()->getTable('options')->insert(array(
				'code' => $code,
				'value' => $val,
			));
		}
		
	}
	/*public function saveGroup($d = array()) {
		if(isset($d['opt_values']) && is_array($d['opt_values']) && !empty($d['opt_values'])) {
			foreach($d['opt_values'] as $code => $value) {
				$d['code'] = $code;
				$this->save($d);
			}
			return !$this->haveErrors();
		} else
			$this->pushError(__('Empty data to setup', BUP_LANG_CODE));
	}*/
	public function saveBgImg($d = array()) {
		if(!empty($d) && isset($d['bg_image']) && !empty($d['bg_image'])) {
			$uploader = toeCreateObjBup('fileuploader', array());
			if($uploader->validate('bg_image', frameBup::_()->getModule('options')->getBgImgDir()) && $uploader->upload()) {
				// Remove prev. image
				utilsBup::deleteFile( frameBup::_()->getModule('options')->getBgImgFullDir() );
				$fileInfo = $uploader->getFileInfo();
				// Save info for this option
				$this->save(array('code' => 'bg_image', 'opt_values' => array('bg_image' => $fileInfo['path'])));
				return true;
			} else
				 $this->pushError( $uploader->getError() );
		} else
			$this->pushError(__('Empty data to setup', BUP_LANG_CODE));
		return false;
	}
	public function saveLogoImg($d = array()) {
		if(!empty($d) && isset($d['logo_image']) && !empty($d['logo_image'])) {
			$uploader = toeCreateObjBup('fileuploader', array());
			if($uploader->validate('logo_image', frameBup::_()->getModule('options')->getLogoImgDir()) && $uploader->upload()) {
				// Remove prev. image
				utilsBup::deleteFile( frameBup::_()->getModule('options')->getLogoImgFullDir() );
				$fileInfo = $uploader->getFileInfo();
				// Save info for this option
				$this->save(array('code' => 'logo_image', 'opt_values' => array('logo_image' => $fileInfo['path'])));
				return true;
			} else
				 $this->pushError( $uploader->getError() );
		} else
			$this->pushError(__('Empty data to setup', BUP_LANG_CODE));
		return false;
	}
	public function setTplDefault($d = array()) {
		$code = isset($d['code']) ? $d['code'] : '';
		if(!empty($code)) {
			$plTemplate = $this->get('template');		// Current plugin template
			if($plTemplate && frameBup::_()->getModule($plTemplate)) {
				$newValue = frameBup::_()->getModule($plTemplate)->getDefOptions($code);
				if($newValue !== NULL) {
					if($this->save(array('opt_values' => array($code => $newValue), 'code' => $code))) {
						return $newValue;
					}
				} else
					$this->pushError(__('There is no default for this option and current template', BUP_LANG_CODE));
			} else
				$this->pushError(__('There is no default for this option and current template', BUP_LANG_CODE));
		} else
			$this->pushError(__('Empty option code', BUP_LANG_CODE));
		return false;
	}
	public function setBgImgDefault($d = array()) {
		$code = isset($d['code']) ? $d['code'] : '';
		if(!empty($code)) {
			$plTemplate = $this->get('template');		// Current plugin template
			if($plTemplate && frameBup::_()->getModule($plTemplate)) {
				$newValue = frameBup::_()->getModule($plTemplate)->getDefOptions($code);

				if($newValue !== NULL && file_exists(frameBup::_()->getModule($plTemplate)->getModDir(). $newValue)) {
					// Remove prev. image
					utilsBup::deleteFile( frameBup::_()->getModule('options')->getBgImgFullDir() );
					// Copy new image from tpl module directory to uploads dirctory
					copy( frameBup::_()->getModule($plTemplate)->getModDir(). $newValue, utilsBup::getUploadsDir(). DS. $this->getModule()->getBgImgDir(). DS. $newValue);
					if($this->save(array('opt_values' => array($code => $newValue), 'code' => $code))) {
						return $this->getModule()->getBgImgFullPath();
					}
				} else
					$this->pushError(__('There is no default for this option and current template', BUP_LANG_CODE));
			} else
				$this->pushError(__('There is no default for this option and current template', BUP_LANG_CODE));
		} else
			$this->pushError(__('Empty option code', BUP_LANG_CODE));
		return false;
	}
	public  function removeBgImg($d = array()) {
		$bgImgDirPath = frameBup::_()->getModule('options')->getBgImgFullDir();
		if($this->save(array('opt_values' => array('bg_image' => ''), 'code' => 'bg_image'))
			&& utilsBup::deleteFile( $bgImgDirPath )
		) {
			return true;
		} else
			$this->pushError(__('Unable to remove image', BUP_LANG_CODE));
	}
	public function setLogoDefault($d = array()) {
		$code = isset($d['code']) ? $d['code'] : '';
		if(!empty($code)) {
			$plTemplate = $this->get('template');		// Current plugin template
			if($plTemplate && frameBup::_()->getModule($plTemplate)) {
				$newValue = frameBup::_()->getModule($plTemplate)->getDefOptions($code);

				if($newValue !== NULL && file_exists(frameBup::_()->getModule($plTemplate)->getModDir(). $newValue)) {
					// Remove prev. image
					utilsBup::deleteFile( frameBup::_()->getModule('options')->getLogoImgFullDir() );
					// Copy new image from tpl module directory to uploads dirctory
					copy( frameBup::_()->getModule($plTemplate)->getModDir(). $newValue, utilsBup::getUploadsDir(). DS. $this->getModule()->getLogoImgDir(). DS. $newValue);
					if($this->save(array('opt_values' => array($code => $newValue), 'code' => $code))) {
						return $this->getModule()->getLogoImgFullPath();
					}
				} else
					$this->pushError(__('There is no default for this option and current template', BUP_LANG_CODE));
			} else
				$this->pushError(__('There is no default for this option and current template', BUP_LANG_CODE));
		} else
			$this->pushError(__('Empty option code', BUP_LANG_CODE));
		return false;
	}
	public function removeLogoImg($d = array()) {
		$logoImgDirPath = frameBup::_()->getModule('options')->getLogoImgFullDir();
		if($this->save(array('opt_values' => array('logo_image' => ''), 'code' => 'logo_image'))
			&& utilsBup::deleteFile( $logoImgDirPath )
		) {
			return true;
		} else
			$this->pushError(__('Unable to remove image', BUP_LANG_CODE));
	}
	public function setTitleParamsDefault($d = array()) {
		$res = true;
		$plTemplate = $this->get('template');		// Current plugin template
		if($plTemplate && frameBup::_()->getModule($plTemplate)) {
			$msgTitleColor = frameBup::_()->getModule($plTemplate)->getDefOptions('msg_title_color');
			if($msgTitleColor !== NULL) {
				$this->save(array('opt_values' => array('msg_title_color' => $msgTitleColor), 'code' => 'msg_title_color'));
			}
			$msgTitleFont = frameBup::_()->getModule($plTemplate)->getDefOptions('msg_title_font');
			if($msgTitleFont !== NULL) {
				$this->save(array('opt_values' => array('msg_title_font' => $msgTitleFont), 'code' => 'msg_title_font'));
			}
			if($msgTitleColor !== NULL || $msgTitleFont !== NULL) {
				$res = array('msg_title_color' => $msgTitleColor, 'msg_title_font' => $msgTitleFont);
			}
		}
		// good in any case
		return $res;
	}
	public function setTextParamsDefault($d = array()) {
		$res = true;
		$plTemplate = $this->get('template');		// Current plugin template
		if($plTemplate && frameBup::_()->getModule($plTemplate)) {
			$msgTextColor = frameBup::_()->getModule($plTemplate)->getDefOptions('msg_text_color');
			if($msgTextColor !== NULL) {
				$this->save(array('opt_values' => array('msg_text_color' => $msgTextColor), 'code' => 'msg_text_color'));
			}
			$msgTextFont = frameBup::_()->getModule($plTemplate)->getDefOptions('msg_text_font');
			if($msgTextFont !== NULL) {
				$this->save(array('opt_values' => array('msg_text_font' => $msgTextFont), 'code' => 'msg_text_font'));
			}
			if($msgTextColor !== NULL || $msgTextFont !== NULL) {
				$res = array('msg_text_color' => $msgTextColor, 'msg_text_font' => $msgTextFont);
			}
		}
		// good in any case
		return $res;
	}
    public function saveBackupPath($newBackupPathArray) {
        $backupsPath = frameBup::_()->getTable('options')->get('value', array('code' => 'serialized_backups_path'), '', 'row');
        $backupsPath = !empty($backupsPath['value']) ? unserialize($backupsPath['value']) : null;
        if(is_array($backupsPath)) {
            $newPathExist = false;
            $serializedNewPath = serialize($newBackupPathArray);

            foreach($backupsPath as $path) {
                if($serializedNewPath === serialize($path))
                    $newPathExist=true;
            }

            if(!$newPathExist) {
                $backupsPath[] = $newBackupPathArray;
                frameBup::_()->getTable('options')->update(array('value' => serialize($backupsPath)), array('code' => 'serialized_backups_path'));
            }
        } elseif (is_array($newBackupPathArray) && isset($newBackupPathArray['warehouse']) && isset($newBackupPathArray['warehouse_abs'])) {
            $backupsPath = array();
            $backupsPath[] = $newBackupPathArray;
            frameBup::_()->getTable('options')->update(array('value' => serialize($backupsPath)), array('code' => 'serialized_backups_path'));
        }
    }
}
