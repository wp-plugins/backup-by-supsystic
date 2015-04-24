<?php
class tableModulesBup extends tableBup {
    public function __construct() {
        $this->_table = '@__modules';
        $this->_id = 'id';     /*Let's associate it with posts*/
        $this->_alias = 'toe_m';
        $this->_addField('label', 'text', 'varchar', 0, __('Label', BUP_LANG_CODE), 128)
                ->_addField('type_id', 'selectbox', 'smallint', 0, __('Type', BUP_LANG_CODE))
                ->_addField('active', 'checkbox', 'tinyint', 0, __('Active', BUP_LANG_CODE))
                ->_addField('params', 'textarea', 'text', 0, __('Params', BUP_LANG_CODE))
                ->_addField('has_tab', 'checkbox', 'tinyint', 0, __('Has Tab', BUP_LANG_CODE))
                ->_addField('description', 'textarea', 'text', 0, __('Description', BUP_LANG_CODE), 128)
                ->_addField('code', 'hidden', 'varchar', '', __('Code', BUP_LANG_CODE), 64)
                ->_addField('ex_plug_dir', 'hidden', 'varchar', '', __('External plugin directory', BUP_LANG_CODE), 255);
    }
}
?>
