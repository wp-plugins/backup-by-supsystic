<?php
class tableLogBup extends tableBup {
    public function __construct() {
        $this->_table = '@__log';
        $this->_id = 'id';     /*Let's associate it with posts*/
        $this->_alias = 'toe_log';
        $this->_addField('id', 'text', 'int', 0, __('ID', BUP_LANG_CODE), 11)
                ->_addField('type', 'text', 'varchar', '', __('Type', BUP_LANG_CODE), 64)
                ->_addField('data', 'text', 'text', '', __('Data', BUP_LANG_CODE))
                ->_addField('date_created', 'text', 'int', '', __('Date created', BUP_LANG_CODE))
				->_addField('uid', 'text', 'int', 0, __('User ID', BUP_LANG_CODE))
				->_addField('oid', 'text', 'int', 0, __('Order ID', BUP_LANG_CODE));
    }
}