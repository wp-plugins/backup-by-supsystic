<?php
class tableHtmltypeBup extends tableBup {
    public function __construct() {
        $this->_table = '@__htmltype';
        $this->_id = 'id';     
        $this->_alias = 'toe_htmlt';
        $this->_addField('id', 'hidden', 'int', 0, __('ID', BUP_LANG_CODE))
            ->_addField('label', 'text', 'varchar', 0, __('Method', BUP_LANG_CODE), 32)
            ->_addField('description', 'text', 'varchar', 0, __('Description', BUP_LANG_CODE), 255);
    }
}
?>
