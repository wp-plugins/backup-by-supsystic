<?php
/**
 * Class for templates module tab at options page
 */
class templatesViewBup extends viewBup {
    /**
     * Get the content for templates module tab
     * 
     * @return type 
     */
    public function getTabContent(){
       $templates = frameBup::_()->getModule('templatesBup')->getModel()->get();
       if(empty($templates)) {
           $tpl = 'noTemplates';
       } else {
           $this->assign('templatesBup', $templates);
           $this->assign('default_theme', frameBup::_()->getModule('optionsBup')->getModel()->get('default_theme'));
           $tpl = 'templatesTab';
       }
       return parent::getContent($tpl);
   }
}

