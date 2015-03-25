<?php
class modulesModelBup extends modelBup {
    public function get($d = array()) {
        if(isset($d['id']) && $d['id'] && is_numeric($d['id'])) {
            $fields = frameBup::_()->getTable('modules')->fillFromDB($d['id'])->getFields();
            $fields['types'] = array();
            $types = frameBup::_()->getTable('modules_type')->fillFromDB();
            foreach($types as $t) {
                $fields['types'][$t['id']->value] = $t['label']->value;
            }
            return $fields;
        } elseif(!empty($d)) {
            $data = frameBup::_()->getTable('modules')->get('*', $d);
            return $data;
        } else {
            return frameBup::_()->getTable('modules')
                ->innerJoin(frameBup::_()->getTable('modules_type'), 'type_id')
                ->getAll(frameBup::_()->getTable('modules')->alias().'.*, '. frameBup::_()->getTable('modules_type')->alias(). '.label as type');
        }
    }
    public function put($d = array()) {
        $res = new responseBup();
        $id = $this->_getIDFromReq($d);
        $d = prepareParamsBup($d);
        if(is_numeric($id) && $id) {
            if(isset($d['active']))
                $d['active'] = ((is_string($d['active']) && $d['active'] == 'true') || $d['active'] == 1) ? 1 : 0;           //mmm.... govnokod?....)))
           /* else
                 $d['active'] = 0;*/
            
            if(frameBup::_()->getTable('modules')->update($d, array('id' => $id))) {
                $res->messages[] = __('Module Updated', BUP_LANG_CODE);
                $mod = frameBup::_()->getTable('modules')->getById($id);
                $newType = frameBup::_()->getTable('modules_type')->getById($mod['type_id'], 'label');
                $newType = $newType['label'];
                $res->data = array(
                    'id' => $id, 
                    'label' => $mod['label'], 
                    'code' => $mod['code'], 
                    'type' => $newType,
                    'active' => $mod['active'], 
                );
            } else {
                if($tableErrors = frameBup::_()->getTable('modules')->getErrors()) {
                    $res->errors = array_merge($res->errors, $tableErrors);
                } else
                    $res->errors[] = __('Module Update Failed', BUP_LANG_CODE);
            }
        } else {
            $res->errors[] = __('Error module ID', BUP_LANG_CODE);
        }
        return $res;
    }
    protected function _getIDFromReq($d = array()) {
        $id = 0;
        if(isset($d['id']))
            $id = $d['id'];
        elseif(isset($d['code'])) {
            $fromDB = $this->get(array('code' => $d['code']));
            if($fromDB[0]['id'])
                $id = $fromDB[0]['id'];
        }
        return $id;
    }
}
