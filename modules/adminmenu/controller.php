<?php
class adminmenuControllerBup extends controllerBup {
    public function sendMailToDevelopers() {
        $res = new responseBup();
        $data = reqBup::get('post');
        $fields = array(
            'name' => new fieldBupBup('name', __('Your name field is required.', BUP_LANG_CODE), '', '', 'Your name', 0, array(), 'notEmpty'),
            'website' => new fieldBupBup('website', __('Your website field is required.', BUP_LANG_CODE), '', '', 'Your website', 0, array(), 'notEmpty'),
            'email' => new fieldBupBup('email', __('Your e-mail field is required.', BUP_LANG_CODE), '', '', 'Your e-mail', 0, array(), 'notEmpty, email'),
            'subject' => new fieldBupBup('subject', __('Subject field is required.', BUP_LANG_CODE), '', '', 'Subject', 0, array(), 'notEmpty'),
            'category' => new fieldBupBup('category', __('You must select a valid category.', BUP_LANG_CODE), '', '', 'Category', 0, array(), 'notEmpty'),
            'message' => new fieldBupBup('message', __('Message field is required.', BUP_LANG_CODE), '', '', 'Message', 0, array(), 'notEmpty'),
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
            $res->addMessage(__('Done', BUP_LANG_CODE));
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

