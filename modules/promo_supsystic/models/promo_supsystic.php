<?php
class promo_supsysticModelBup extends modelBup {
	private $_apiUrl = 'http://54.68.191.217';
	public function welcomePageSaveInfo($d){
		//print_r($d);
		$d['where_find_us'] = (int) $d['where_find_us'];
		$desc = '';
		if(in_array($d['where_find_us'], array(4, 5))) {
			$desc = $d['where_find_us'] == 4 ? $d['find_on_web_url'] : $d['other_way_desc'];
		}
		$reqUrl = $this->_apiUrl. '?mod=options&action=saveWelcomePageInquirer&pl=rcs';
		wp_remote_post($reqUrl, array(
			'body' => array(
				'site_url' => get_bloginfo('wpurl'),
				'site_name' => get_bloginfo('name'),
				'where_find_us' => $d['where_find_us'],
				'desc' => $desc,
				'plugin_code' => BUP_CLASS_PREFIX,
			)
		));
		// In any case - give user posibility to move futher
		return true;
	}
    public function sendUsageStat($allStat) {
        $reqUrl = $this->_apiUrl . '?mod=options&action=saveUsageStat&pl=rcs';
        $res = wp_remote_post($reqUrl, array(
            'body' => array(
                'site_url' => get_bloginfo('wpurl'),
                'site_name' => get_bloginfo('name'),
                'plugin_code' => 'backup_by_supsystic',
                'all_stat' => array($allStat),
            )
        ));
        return true;
    }
}
