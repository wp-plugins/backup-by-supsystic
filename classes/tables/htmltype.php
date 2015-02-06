<?php
class tableHtmltypeBup extends tableBup {
    public function __construct() {
        $this->_table = '@__htmltype';
        $this->_id = 'id';     
        $this->_alias = 'toe_htmlt';
        $this->_addField('id', 'hidden', 'int', 0, langBup::_('ID'))
            ->_addField('label', 'text', 'varchar', 0, langBup::_('Method'), 32)
            ->_addField('description', 'text', 'varchar', 0, langBup::_('Description'), 255);
    }
}
?>
