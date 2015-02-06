<?php
class scheduleBup extends moduleBup {
	public function init() {
		add_action('bup_cron_hour', array($this, 'backupSchedule')); 
		add_action('bup_cron_day', array($this, 'backupSchedule')); 
		add_action('bup_cron_day_twice', array($this, 'backupSchedule')); 
		add_action('bup_cron_weekly', array($this, 'backupSchedule')); 
		add_action('bup_cron_monthly', array($this, 'backupSchedule')); 
		
		add_filter('cron_schedules', array($this, 'bup_cron_add_interval'));  
		
		//dispatcherBup::addFilter('cron_schedules', array($this, 'bup_cron_add_interval'));
		parent::init();
	}
	
	
	function bup_cron_add_interval( $schedules ) {  
    
	  $schedules['weekly'] = array(  
		  'interval' => 604800,  //
		  'display' => __( 'Once Weekly' )  
	  );  
	  
	  $schedules['monthly'] = array(
		'interval' => 2635200,
		'display' => __('Once a month')
	  );

	  
      return $schedules;  
	  
	}
	// -- test
	function test_schedule() { 
	$my_post = array('post_title' => 'Новая запись создана:' . date('Y-m-d в H:i:s'), 
                     'post_content' => 'Это очередная запись <br> Unix-время создания:' . time(), 
					 'post_status' => 'publish', 
                     'post_author' => 1, 
                     'post_category' => array(0)
                      ); 
		wp_insert_post( $my_post ); 
	}
	
	public function backupSchedule(){
		/** @var backupModelBup $model */
        $model = frameBup::_()->getModule('backup')->getModel();

        $filename = $model->generateFilename(array('sql', 'zip'));
        $files = array();

        if ($model->isFilesystemRequired()) {
            $model->getFilesystem()->getArchive($filename['zip'], $model->getFilesList());
            $files[] = $filename['zip'];
        }

        if ($model->isDatabaseRequired()) {
            $model->getDatabase()->create($filename['sql']);
            $files[] = $filename['sql'];
        }

        if ('ftp' !== $dest = frameBup::_()->getModule('options')->get('sch_dest')) {
            $handlers = $model->getDestinationHandlers();

            foreach ($handlers as $handle => $callback) {
                if ($handle === $dest) {
                    $result = call_user_func_array($callback, array($files, true));

                    file_put_contents(WP_CONTENT_DIR . '/schedule.log', array(
                        'Handle: ' . $handle . PHP_EOL,
                        'Result: ' . $result,
                    ));
                }
            }

            foreach ($files as $file) {
                @unlink($file);
            }
        }
	}
	
}