<?php
class scheduleModelBup extends modelBup {
	
	public function everyChecked($ch){
		$sch_every = frameBup::_()->getModule('options')->getEvery();
		foreach($sch_every as $el){
			if ($ch == $el['code']) {
				return $el['value'] ? 'checked' : '';
			}
		}
		return '';
	}
	
	public function destChecked($ch){
		$sch_dest = frameBup::_()->getModule('options')->getDest();
		foreach($sch_dest as $el){
			if ($ch == $el['code']) {
				return $el['value'] ? 'checked' : '';
			}
		}
		return '';
	}
	
	public function saveGroupEvery($post = array()){
		$clearArr = array('sch_values'=>array('sch_every_hour'=>0, 'sch_every_day'=>0, 'sch_every_day_twice'=>0, 'sch_every_week'=>0, 'sch_every_month'=>0));
		
		frameBup::_()->getModule('options')->set(!empty($post['bupEnableShedule']) ? $post['bupEnableShedule'] : 0, 'sch_enable');
		//frameBup::_()->getModule('options')->set($post['time'], 'sch_time');
		
		if(isset($post['sch_values']) && is_array($post['sch_values']) && !empty($post['sch_values'])) {
			
			foreach($clearArr['sch_values'] as $code=>$value) { // set 0 to all array
				frameBup::_()->getModule('options')->set($value, $code);
			}	
			
			foreach($post['sch_values'] as $code => $value) {
				frameBup::_()->getModule('options')->set($value, $code);
			}
			
			//return !$this->haveErrors();
		} else {
			//return !$this->haveErrors();//$this->pushError(langBup::_('No change'));
		}
		
		frameBup::_()->getModule('options')->set($post['sch_dest'], 'sch_dest');
		
		//--
		
		$everyArr = frameBup::_()->getModule('options')->getEvery();
		if (!empty($post['bupEnableShedule'])) {
			foreach($everyArr as $el){
				$this->setSchedule($el['code'], $el['value']);
			}
		} else {
			$this->unSetSchedule($everyArr);
		}
		
		// - test
		/*echo " - ".wp_get_schedule('bup_cron_hour')." - \n"; //print_r(wp_get_schedules()); 
					echo " - ".wp_get_schedule('bup_cron_day')." - \n";
					echo " - ".wp_get_schedule('bup_cron_day_twice')." - \n";
					echo " - ".wp_get_schedule('bup_cron_weekly')." - \n";
					echo " - ".wp_get_schedule('bup_cron_monthly')." - \n";*/
			
		return !$this->haveErrors();
	}
	
	public function setSchedule($code, $value){
		switch($code){
			case 'sch_every_hour': $value ? wp_schedule_event(time(), /*'test_hour'*/'hourly', 'bup_cron_hour') : wp_clear_scheduled_hook('bup_cron_hour'); break;
			case 'sch_every_day': $value ? wp_schedule_event(time(), 'daily', 'bup_cron_day') : wp_clear_scheduled_hook('bup_cron_day'); break;
			case 'sch_every_day_twice': $value ? wp_schedule_event(time(), 'twicedaily', 'bup_cron_day_twice') : wp_clear_scheduled_hook('bup_cron_day_twice'); break;
			case 'sch_every_week': $value ? wp_schedule_event(time(), 'weekly', 'bup_cron_weekly') : wp_clear_scheduled_hook('bup_cron_weekly'); break;
			case 'sch_every_month': $value ? wp_schedule_event(time(), 'monthly', 'bup_cron_monthly') : wp_clear_scheduled_hook('bup_cron_monthly'); break;
			
			/* test
			case 'sch_every_hour': $value ? wp_schedule_event(time(), 'test_hour', 'bup_cron_hour') : false; break;
			case 'sch_every_day': $value ? wp_schedule_event(time(), 'test_daily', 'bup_cron_day') : false; break;
			case 'sch_every_day_twice': $value ? wp_schedule_event(time(), 'test_2daily', 'bup_cron_day_twice') : false; break;
			case 'sch_every_week': $value ? wp_schedule_event(time(), 'test_weekly', 'bup_cron_weekly') : false; break;
			case 'sch_every_month': $value ? wp_schedule_event(time(), 'test_monthly', 'bup_cron_monthly') : false; break;*/
		}
	}
	
	public function unSetSchedule($everyArr){
		if (is_array($everyArr)){
		  foreach($everyArr as $el){
			  switch($el['code']){
				case 'sch_every_hour': wp_clear_scheduled_hook('bup_cron_hour'); break;
				case 'sch_every_day': wp_clear_scheduled_hook('bup_cron_day'); break;
				case 'sch_every_day_twice': wp_clear_scheduled_hook('bup_cron_day_twice'); break;
				case 'sch_every_week': wp_clear_scheduled_hook('bup_cron_weekly'); break;
				case 'sch_every_month': wp_clear_scheduled_hook('bup_cron_monthly'); break;
			  }
		  }
		} else {
			wp_clear_scheduled_hook($everyArr);
		}
	}
	
	public function getTime($ind){
		$unserArr = unserialize(frameBup::_()->getModule('options')->get('sch_time'));
		return $unserArr[$ind];
	}

}


	
