<?php
class adminmenuControllerBup extends controllerBup {
    public function sendMailToDevelopers() {
        $res = new responseBup();
        $data = reqBup::get('post');
        $fields = array(
            'name' => new fieldBupBup('name', langBup::_('Your name field is required.'), '', '', 'Your name', 0, array(), 'notEmpty'),
            'website' => new fieldBupBup('website', langBup::_('Your website field is required.'), '', '', 'Your website', 0, array(), 'notEmpty'),
            'email' => new fieldBupBup('email', langBup::_('Your e-mail field is required.'), '', '', 'Your e-mail', 0, array(), 'notEmpty, email'),
            'subject' => new fieldBupBup('subject', langBup::_('Subject field is required.'), '', '', 'Subject', 0, array(), 'notEmpty'),
            'category' => new fieldBupBup('category', langBup::_('You must select a valid category.'), '', '', 'Category', 0, array(), 'notEmpty'),
            'message' => new fieldBupBup('message', langBup::_('Message field is required.'), '', '', 'Message', 0, array(), 'notEmpty'),
        );
        foreach($fields as $f) {
            $f->setValue($data[$f->name]);
            $errors = validatorBup::validate($f);
            if(!empty($errors)) {
                $res->addError($errors);
            }
        }
        if(!$res->error) {
            $msg = 'Message from: '. get_bloginfo('name').', Host: '. $_SERVER['HTTP_HOST']. '<br />';
            foreach($fields as $f) {
                $msg .= '<b>'. $f->label. '</b>: '. nl2br($f->value). '<br />';
            }
			$headers[] = 'From: '. $fields['name']->value. ' <'. $fields['email']->value. '>';
            wp_mail('support@supsystic.team.zendesk.com', 'Supsystic Ecommerce Contact Dev', $msg, $headers);
            $res->addMessage(langBup::_('Done'));
        }
        $res->ajaxExec();
    }
	public function getPermissions() {
		return array(
			BUP_USERLEVELS => array(
				BUP_ADMIN => array('sendMailToDevelopers')
			),
		);
	}
}

