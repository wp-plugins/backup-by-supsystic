<?php
if(!class_exists('wpUpdaterBup')) {
	class wpUpdaterBup {
		protected $_plugDir = '';
		protected $_plugFile = '';
		protected $_plugSlug = '';
		private $_userAgentHash = '';
		private $_apiUrl = '';

		public function __construct($pluginDir, $pluginFile = '', $pluginSlug = '') {
			$this->_plugDir = $pluginDir;
			$this->_plugFile = $pluginFile;
			$this->_plugSlug = $pluginSlug;
		}
		static public function getInstance($pluginDir, $pluginFile = '', $pluginSlug = '') {
			static $instances = array();
			// Instance key
			$instKey = $pluginDir. '/'. $pluginFile;
			if(!isset($instances[ $instKey ])) {
				$instances[ $instKey ] = new wpUpdaterBup($pluginDir, $pluginFile, $pluginSlug);
			}
			return $instances[ $instKey ];
		}
		public function checkForPluginUpdate($checkedData) {
			if (empty($checkedData->checked))
				return $checkedData;
			// For old versions of our addons
			if(empty($this->_plugSlug))
				return $checkedData;
			$request_args = array(
				'slug' => $this->_plugSlug,
				'hash' => constant('S_YOUR_SECRET_HASH_'. $this->_plugSlug),
				'version' => $checkedData->checked[$this->_plugDir .'/'. $this->_plugFile],
			);
			$request_string = $this->prepareRequest('basic_check', $request_args);

			// Start checking for an update
			$raw_response = wp_remote_post($this->_getApiUrl(), $request_string);
			if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
				$response = unserialize($raw_response['body']);
			if (is_object($response) && !empty($response)) // Feed the update data into WP updater
				$checkedData->response[$this->_plugDir .'/'. $this->_plugFile] = $response;
			return $checkedData;
		}
		public function myPluginApiCall($def, $action, $args) {
			if ($args->slug != $this->_plugSlug)
				return $def;
			// For old versions of our addons
			if(empty($this->_plugSlug))
				return $def;
			// Get the current version
			$plugin_info = get_site_transient('update_plugins');
			$current_version = $plugin_info->checked[$this->_plugDir .'/'. $this->_plugFile];
			$args->version = $current_version;

			$request_string = $this->prepareRequest($action, $args);

			$request = wp_remote_post($this->_getApiUrl(), $request_string);

			if (is_wp_error($request)) {
				$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
			} else {
				$res = unserialize($request['body']);

				if ($res === false)
					$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
			}
			return $res;
		}
		public function prepareRequest($action, $args) {
			global $wp_version;

			return array(
				'body' => array(
					'action' => $action, 
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url'))
				),
				'user-agent' => $this->_getUserAgentHash(). '/' . $wp_version . '; ' . get_bloginfo('url'). ';'. $this->getIP()
			);	
		}
		public function getIP() {
			return (empty($_SERVER['HTTP_CLIENT_IP']) ? (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR']) : $_SERVER['HTTP_CLIENT_IP']);
		}
		private function _getApiUrl() {
			if(empty($this->_apiUrl)) {
				$this->_apiUrl = 'http://54.68.191.217/?pl=com&mod=updater&action=requestAction';
			}
			return $this->_apiUrl;
		}
		private function _getUserAgentHash() {
			if(empty($this->_userAgentHash)) {
				$this->_userAgentHash = 'f323f89F#Ur32424u39842354254(*%5%#($#$OEf9ir3r3d893#$';
			}
			return $this->_userAgentHash;
		}
	}
}