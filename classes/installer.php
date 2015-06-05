<?php
class installerBup {
	static public $update_to_version_method = '';
	static public function init() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$current_version = get_option(BUP_DB_PREF. 'db_version', 0);
		$installed = (int) get_option(BUP_DB_PREF. 'db_installed', 0);

		if (!dbBup::exist($wpPrefix.BUP_DB_PREF."htmltype")) {
			dbDelta("CREATE TABLE IF NOT EXISTS `".$wpPrefix.BUP_DB_PREF."htmltype` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `label` varchar(32) NOT NULL,
			  `description` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `label` (`label`)
			) DEFAULT CHARSET=utf8");
			dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."htmltype` VALUES
				(1, 'text', 'Text'),
				(2, 'password', 'Password'),
				(3, 'hidden', 'Hidden'),
				(4, 'checkbox', 'Checkbox'),
				(5, 'checkboxlist', 'Checkboxes'),
				(6, 'datepicker', 'Date Picker'),
				(7, 'submit', 'Button'),
				(8, 'img', 'Image'),
				(9, 'selectbox', 'Drop Down'),
				(10, 'radiobuttons', 'Radio Buttons'),
				(11, 'countryList', 'Countries List'),
				(12, 'selectlist', 'List'),
				(13, 'countryListMultiple', 'Country List with posibility to select multiple countries'),
				(14, 'block', 'Will show only value as text'),
				(15, 'statesList', 'States List'),
				(16, 'textFieldsDynamicTable', 'Dynamic table - multiple text options set'),
				(17, 'textarea', 'Textarea'),
				(18, 'checkboxHiddenVal', 'Checkbox with Hidden field')");
		}
		/**
		 * modules
		 */
		if (!dbBup::exist($wpPrefix.BUP_DB_PREF."modules")) {
			dbDelta("CREATE TABLE IF NOT EXISTS `".$wpPrefix.BUP_DB_PREF."modules` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `code` varchar(64) NOT NULL,
			  `active` tinyint(1) NOT NULL DEFAULT '0',
			  `type_id` smallint(3) NOT NULL DEFAULT '0',
			  `params` text,
			  `has_tab` tinyint(1) NOT NULL DEFAULT '0',
			  `label` varchar(128) DEFAULT NULL,
			  `description` text,
			  `ex_plug_dir` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `code` (`code`)
			) DEFAULT CHARSET=utf8;");
			dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."modules` (id, code, active, type_id, params, has_tab, label, description) VALUES
				(NULL, 'adminmenu',1,1,'',0,'Admin Menu',''),
				(NULL, 'options',1,1,'',1,'Options',''),
				(NULL, 'log', 1, 1, '', 1, 'Log', 'Internal system module to log some actions on server'),
				(NULL, 'templates',1,1,'',0,'Templates for Plugin',''),
				(NULL, 'backup', 1, 1, '', 1, 'Backup by Supsystic!', 'Backup by Supsystic!'),
				(NULL, 'storage', 1, 1, '', 1, 'Storage', 'Storage'),
				(NULL, 'gdrive', 1, 1, '', 1, 'gdrive', 'gdrive'),
				(NULL, 'onedrive', 1, 1, '', 1, 'onedrive', 'onedrive'),
				(NULL, 'amazon', 1, 1, '', 1, 'amazon', 'amazon'),
				(NULL, 'dropbox', 1, 1, '', 1, 'dropbox', 'dropbox')");
		}
        if(!dbBup::exist($wpPrefix.BUP_DB_PREF."modules", 'code', 'gdrive')){
            dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."modules` (id, code, active, type_id, params, has_tab, label, description) VALUES
		    (NULL, 'gdrive', 1, 1, '', 1, 'gdrive', 'gdrive')");
        }
        if(!dbBup::exist($wpPrefix.BUP_DB_PREF."modules", 'code', 'dropbox')){
            dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."modules` (id, code, active, type_id, params, has_tab, label, description) VALUES
            (NULL, 'dropbox', 1, 1, '', 1, 'dropbox', 'dropbox')");
        }
        if(!dbBup::exist($wpPrefix.BUP_DB_PREF."modules", 'code', 'amazon')){
            dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."modules` (id, code, active, type_id, params, has_tab, label, description) VALUES
		    (NULL, 'amazon', 1, 1, '', 1, 'amazon', 'amazon')");
        }
        if(!dbBup::exist($wpPrefix.BUP_DB_PREF."modules", 'code', 'onedrive')){
            dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."modules` (id, code, active, type_id, params, has_tab, label, description) VALUES
		    (NULL, 'onedrive', 1, 1, '', 1, 'onedrive', 'onedrive')");
        }
		/**
		 *  modules_type
		 */
		if(!dbBup::exist($wpPrefix.BUP_DB_PREF."modules_type")) {
			dbDelta("CREATE TABLE IF NOT EXISTS `".$wpPrefix.BUP_DB_PREF."modules_type` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `label` varchar(64) NOT NULL,
			  PRIMARY KEY (`id`)
			) AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;");
			dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."modules_type` VALUES
				(1,'system'),
				(2,'addons')");
		}
		/**
		 * options
		 */
		$eol = "\n";
		$warehouse = '/'.BUP_WP_CONTENT_DIR.'/upsupsystic/';
		$msgText = 'We apologize, but at this time our site does not work. But we promise you, very soon we will resume work. '. $eol. 'We just want to improve our site for your comfort.Be among the first to see our new website! Just send your email using the form below and we will inform you.';
		if(!dbBup::exist($wpPrefix.BUP_DB_PREF."options")) {
			dbDelta("CREATE TABLE IF NOT EXISTS `".$wpPrefix.BUP_DB_PREF."options` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `code` varchar(64) CHARACTER SET latin1 NOT NULL,
			  `value` longtext NULL,
			  `label` varchar(128) CHARACTER SET latin1 DEFAULT NULL,
			  `description` text CHARACTER SET latin1,
			  `htmltype_id` smallint(2) NOT NULL DEFAULT '1',
			  `params` text NULL,
			  `cat_id` mediumint(3) DEFAULT '0',
			  `sort_order` mediumint(3) DEFAULT '0',
			  `value_type` varchar(16) CHARACTER SET latin1 DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `id` (`id`),
			  UNIQUE INDEX `code` (`code`)
			) DEFAULT CHARSET=utf8");
			dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
				(NULL,'full','1','Full backup','on/off full backup',1,'',0,0,'dest_backup'),
				(NULL,'wp_core','1','Wordpress core backup','on/off Wordpress core backup',1,'',0,0,'dest_backup'),
				(NULL,'plugins','1','Plugins','on/off backup plugins',1,'',0,0,'dest_backup'),
				(NULL,'themes','1','Themes','on/off backup themes',1,'',0,0,'dest_backup'),
				(NULL,'uploads','1','Uploads','on/off backup uploads',1,'',0,0,'dest_backup'),
				(NULL,'database','1','Database','on/off backup database',1,'',0,0,'db_backup'),
				(NULL,'any_directories','1','Any','Any other directories found inside wp-content',1,'',0,0,'dest_backup'),
				(NULL,'warehouse','".$warehouse."','Warehouse','path to storage',1,'',0,0,''),
				(NULL,'warehouse_ignore','upsupsystic','Warehouse_ignore','Name ignore directory storage',1,'',0,0,''),
				(NULL,'safe_array','','Safe array','Safe file array',1,'',0,0,''),
				(NULL,'dropbox_model','','Dropbox model','Module uses two models: for PHP 5.2.x and for PHP >= 5.3.x', '1','', '', '',''),
				(NULL,'aws_access_key','','AWS Access Key','Amazon Web Services Access Key to work with the Amazon S3', '1','', '', '',''),
				(NULL,'aws_secret_key','','AWS Secret Key','Amazon Web Services Secret Key to work with Amazon S3', '1','', '', '',''),
				(NULL,'aws_s3_bucket','','S3 Bucket','Name of bucket to upload backups', '1','', '', '','');");
		}
		//(NULL,'exclude','upgrade,cache','Exclude','Exclude directories',1,'',0,0,'')
        if(!dbBup::exist($wpPrefix.BUP_DB_PREF."options", 'code', 'dropbox_model')){
            dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
			    (NULL,'dropbox_model','','Dropbox model','Module uses two models: for PHP 5.2.x and for PHP >= 5.3.x', '1','', '', '','');");
        }
        if(!dbBup::exist($wpPrefix.BUP_DB_PREF."options", 'code', 'gdrive_refresh_token')){
            dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
			    (NULL,'gdrive_refresh_token','','Google Refresh Token','GoogleDrive refresh token using for automatically extend token time', '1','', '', '','');");
        }
        if(!dbBup::exist($wpPrefix.BUP_DB_PREF."options", 'code', 'aws_access_key')){
            dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
			    (NULL,'aws_access_key','','AWS Access Key','Amazon Web Services Access Key to work with the Amazon S3', '1','', '', '','');");
        }
        if(!dbBup::exist($wpPrefix.BUP_DB_PREF."options", 'code', 'aws_secret_key')){
            dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
			    (NULL,'aws_secret_key','','AWS Secret Key','Amazon Web Services Secret Key to work with Amazon S3', '1','', '', '','');");
        }
        if(!dbBup::exist($wpPrefix.BUP_DB_PREF."options", 'code', 'aws_s3_bucket')){
            dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
			    (NULL,'aws_s3_bucket','','S3 Bucket','Name of bucket to upload backups', '1','', '', '','');");
        }
        if(!dbBup::exist($wpPrefix.BUP_DB_PREF."options", 'code', 'wp_core')){
            dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
			    (NULL,'wp_core','1','Wordpress core backup','on/off Wordpress core backup',1,'',0,0,'dest_backup');");
        }
        if(!dbBup::exist($wpPrefix.BUP_DB_PREF."options", 'code', 'serialized_backups_path')){
            dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
			    (NULL,'serialized_backups_path','','Serialized backups path','Store all backups path in serialized data',0,'',0,0,'');");
        }
		/* options categories */
		if(!dbBup::exist($wpPrefix.BUP_DB_PREF."options_categories")) {
			dbDelta("CREATE TABLE IF NOT EXISTS `".$wpPrefix.BUP_DB_PREF."options_categories` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `label` varchar(128) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `id` (`id`)
			) DEFAULT CHARSET=utf8");
			dbBup::query("INSERT INTO `".$wpPrefix.BUP_DB_PREF."options_categories` VALUES
				(1, 'General'),
				(2, 'Template'),
				(3, 'Subscribe'),
				(4, 'Social');");
		}
		/**
		 * Log table - all logs in project
		 */
		// I didn't see that it was used somwhere - all log's was done in log files for backups, is this correct?
		/*if(!dbBup::exist($wpPrefix.BUP_DB_PREF."log")) {
			dbDelta("CREATE TABLE `".$wpPrefix.BUP_DB_PREF."log` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `type` varchar(64) NOT NULL,
			  `data` text,
			  `date_created` int(11) NOT NULL DEFAULT '0',
			  `uid` int(11) NOT NULL DEFAULT 0,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8");
		}*/
		installerDbUpdaterBup::runUpdate();

		update_option(BUP_DB_PREF. 'db_version', BUP_VERSION);
		add_option(BUP_DB_PREF. 'db_installed', 1);
		dbBup::query("UPDATE `".$wpPrefix.BUP_DB_PREF."options` SET value = '". BUP_VERSION. "' WHERE code = 'version' LIMIT 1");

		$warehouse = ABSPATH.$warehouse;
		if (!file_exists($warehouse)) {
			utilsBup::createDir($warehouse, $params = array('chmod' => 0755, 'httpProtect' => 2));
		}
	}
	static public function delete() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;

        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.BUP_DB_PREF."modules`");
        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.BUP_DB_PREF."modules_type`");
        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.BUP_DB_PREF."options`");
        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.BUP_DB_PREF."options_categories`");
        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.BUP_DB_PREF."htmltype`");
        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.BUP_DB_PREF."log`");

		//frameBup::_()->getModule('schedule')->getModel()->unSetSchedule(frameBup::_()->getModule('options')->getEvery());
		
		delete_option(BUP_DB_PREF. 'db_version');
		delete_option(BUP_DB_PREF. 'db_installed');
	}
	static protected function _addPageToWP($post_title, $post_parent = 0) {
		return wp_insert_post(array(
			 'post_title' => __($post_title, BUP_LANG_CODE),
			 'post_content' => __($post_title. ' Page Content', BUP_LANG_CODE),
			 'post_status' => 'publish',
			 'post_type' => 'page',
			 'post_parent' => $post_parent,
			 'comment_status' => 'closed'
		));
	}
	static public function update() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Versiom */
		$currentVersion = get_option(BUP_DB_PREF. 'db_version', 0);
		$installed = (int) get_option(BUP_DB_PREF. 'db_installed', 0);

		if($installed && version_compare(BUP_VERSION, $currentVersion, '>')) {
			self::init();
			update_option($wpPrefix. 'db_version', BUP_VERSION);
		}
	}
}
