<?php
class pagesViewBup extends viewBup {
    public function displayDeactivatePage() {
        $this->assign('GET', reqBup::get('get'));
        $this->assign('POST', reqBup::get('post'));
        $this->assign('REQUEST_METHOD', strtoupper(reqBup::getVar('REQUEST_METHOD', 'server')));
        $this->assign('REQUEST_URI', basename(reqBup::getVar('REQUEST_URI', 'server')));
        parent::display('deactivatePage');
    }
}

