<?php
class templatesModelBup extends modelBup {
    protected $_allTemplates = array();
    public function get($d = array()) {
        parent::get($d);
        if(empty($this->_allTemplates)) {
            $directories = utilsBup::getDirList(BUP_TEMPLATES_DIR);
            if(!empty($directories)) {
                foreach($directories as $code => $dir) {
                    if($xml = utilsBup::getXml($dir['path']. 'settings.xml')) {
                        $this->_allTemplates[$code] = $xml;
                        $this->_allTemplates[$code]->prevImg = BUP_TEMPLATES_PATH. $code. '/screenshot.png';
                    }
                }
            }
            if(is_dir( utilsBup::getCurrentWPThemeDir(). 'csp'. DS )) {
                if($xml = utilsBup::getXml( utilsBup::getCurrentWPThemeDir(). 'csp'. DS. 'settings.xml' )) {
                    $code = utilsBup::getCurrentWPThemeCode();
					if(strpos($code, '/') !== false) {	// If theme is in sub-folder
						$code = explode('/', $code);
						$code = trim( $code[count($code)-1] );
					}
                    $this->_allTemplates[$code] = $xml;
					if(is_file(utilsBup::getCurrentWPThemeDir(). 'screenshot.jpg'))
						$this->_allTemplates[$code]->prevImg = utilsBup::getCurrentWPThemePath(). '/screenshot.jpg';
					else
						$this->_allTemplates[$code]->prevImg = utilsBup::getCurrentWPThemePath(). '/screenshot.png';
                }
            }
        }
        if(isset($d['code']) && isset($this->_allTemplates[ $d['code'] ]))
            return $this->_allTemplates[ $d['code'] ];
        return $this->_allTemplates;
    }
}
