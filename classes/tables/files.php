<?php
class tableFilesBup extends tableBup {
    public function __construct() {
        $this->_table = '@__files';
        $this->_id = 'id';
        $this->_alias = 'toe_f';
        $this->_addField('pid', 'hidden', 'int', '', __('Product ID', BUP_LANG_CODE))
                ->_addField('name', 'text', 'varchar', '255', __('File name', BUP_LANG_CODE))
                ->_addField('path', 'hidden', 'text', '', __('Real Path To File', BUP_LANG_CODE))
                ->_addField('mime_type', 'text', 'varchar', '32', __('Mime Type', BUP_LANG_CODE))
                ->_addField('size', 'text', 'int', 0, __('File Size', BUP_LANG_CODE))
                ->_addField('active', 'checkbox', 'tinyint', 0, __('Active Download', BUP_LANG_CODE))
                ->_addField('date','text','datetime','',__('Upload Date', BUP_LANG_CODE))
                ->_addField('download_limit','text','int','',__('Download Limit', BUP_LANG_CODE))
                ->_addField('period_limit','text','int','',__('Period Limit', BUP_LANG_CODE))
                ->_addField('description', 'textarea', 'text', 0, __('Descritpion', BUP_LANG_CODE))
                ->_addField('type_id','text','int','',__('Type ID', BUP_LANG_CODE));
    }
}
