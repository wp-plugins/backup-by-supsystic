<?php
class tableLogBup extends tableBup {
    public function __construct() {
        $this->_table = '@__log';
        $this->_id = 'id';     /*Let's associate it with posts*/
        $this->_alias = 'toe_log';
        $this->_addField('id', 'text', 'int', 0, langBup::_('ID'), 11)
                ->_addField('type', 'text', 'varchar', '', langBup::_('Type'), 64)
                ->_addField('data', 'text', 'text', '', langBup::_('Data'))
                ->_addField('date_created', 'text', 'int', '', langBup::_('Date created'))
				->_addField('uid', 'text', 'int', 0, langBup::_('User ID'))
				->_addField('oid', 'text', 'int', 0, langBup::_('Order ID'));
    }
}