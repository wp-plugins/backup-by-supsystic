<?php
class tableModulesBup extends tableBup {
    public function __construct() {
        $this->_table = '@__modules';
        $this->_id = 'id';     /*Let's associate it with posts*/
        $this->_alias = 'toe_m';
        $this->_addField('label', 'text', 'varchar', 0, langBup::_('Label'), 128)
                ->_addField('type_id', 'selectbox', 'smallint', 0, langBup::_('Type'))
                ->_addField('active', 'checkbox', 'tinyint', 0, langBup::_('Active'))
                ->_addField('params', 'textarea', 'text', 0, langBup::_('Params'))
                ->_addField('has_tab', 'checkbox', 'tinyint', 0, langBup::_('Has Tab'))
                ->_addField('description', 'textarea', 'text', 0, langBup::_('Description'), 128)
                ->_addField('code', 'hidden', 'varchar', '', langBup::_('Code'), 64)
                ->_addField('ex_plug_dir', 'hidden', 'varchar', '', langBup::_('External plugin directory'), 255);
    }
}
?>
