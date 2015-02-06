<?php
class tableOptionsBup extends tableBup {
     public function __construct() {
        $this->_table = '@__options';
        $this->_id = 'id';     /*Let's associate it with posts*/
        $this->_alias = 'toe_opt';
        $this->_addField('id', 'text', 'int', 0, langBup::_('ID'))->
                _addField('code', 'text', 'varchar', '', langBup::_('Code'), 64)->
                _addField('value', 'text', 'varchar', '', langBup::_('Value'), 134217728)->
                _addField('label', 'text', 'varchar', '', langBup::_('Label'), 255)->
                _addField('description', 'text', 'text', '', langBup::_('Description'))->
                _addField('htmltype_id', 'selectbox', 'text', '', langBup::_('Type'))->
				_addField('cat_id', 'hidden', 'int', '', langBup::_('Category ID'))->
				_addField('sort_order', 'hidden', 'int', '', langBup::_('Sort Order'))->
				_addField('value_type', 'hidden', 'varchar', '', langBup::_('Value Type'));;
    }
}
?>
