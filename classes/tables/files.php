<?php
class tableFilesBup extends tableBup {
    public function __construct() {
        $this->_table = '@__files';
        $this->_id = 'id';
        $this->_alias = 'toe_f';
        $this->_addField('pid', 'hidden', 'int', '', langBup::_('Product ID'))
                ->_addField('name', 'text', 'varchar', '255', langBup::_('File name'))
                ->_addField('path', 'hidden', 'text', '', langBup::_('Real Path To File'))
                ->_addField('mime_type', 'text', 'varchar', '32', langBup::_('Mime Type'))
                ->_addField('size', 'text', 'int', 0, langBup::_('File Size'))
                ->_addField('active', 'checkbox', 'tinyint', 0, langBup::_('Active Download'))
                ->_addField('date','text','datetime','',langBup::_('Upload Date'))
                ->_addField('download_limit','text','int','',langBup::_('Download Limit'))
                ->_addField('period_limit','text','int','',langBup::_('Period Limit'))
                ->_addField('description', 'textarea', 'text', 0, langBup::_('Descritpion'))
                ->_addField('type_id','text','int','',langBup::_('Type ID'));
    }
}
