<?php
class scheduleViewBup extends viewBup {
    public function getAdminOptions() {
		frameBup::_()->addScript('adminScheduleOptions', $this->getModule()->getModPath(). 'js/admin.schedule.js');
			
		return parent::getContent('schedulePage');
	}
}
