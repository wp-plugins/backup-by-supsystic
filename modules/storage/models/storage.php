<?php
class storageModelBup extends modelBup {

	public function getStorage()
	{
		$warehouse = frameBup::_()->getModule('warehouse')->getPath();

		$arrFile = @scandir($warehouse);
		$arrFile = @array_diff($arrFile, array('.', '..'));

		@arsort($arrFile);

		$arrId = array();
		foreach($arrFile as $file) {
			preg_match_all('~_id(\d+)~', $file, $out);
			if ( !empty($out[1][0]) && !in_array($out[1][0], $arrId) ) {
					$arrId[] = $out[1][0]; // get file array
			}
		}

		$arrBlock = array();
		foreach($arrId as $id) {
			$pattern = "~_id$id\.~";
			foreach($arrFile as $file) {
				if (preg_match($pattern, $file)){
					$arrBlock[$id][] = $file; // put in block array
				}
			}
		}

		return $arrBlock;
	}


	/**
	 * To avoid calling getStorage() each time we need data in one request.
	 */
	public function getStorageCache() {
		static $list, $set;
		if(!$set) {
			$list = $this->getStorage();
			$set = true;
		}
		return $list;
	}

	/*public function getList($d = array()){
		return $this->getModule()->getController()->getView()->getAdminOptionsLimit($d);
	}*/
	public function getList($d = array()) {
		$storageData = $this->getStorageCache();
		$list = array();
		if(!empty($storageData)) {
			$limitFrom = isset($d['limitFrom']) ? (int) $d['limitFrom'] : 0;
			$limitTo = isset($d['limitTo']) ? (int) $d['limitTo'] : 0;
			$i = 0;
			//foreach($storageData as $id => $fNames) {

				//foreach($fNames as $name) {
					//$i++;
					//print_r($storageData);
			  foreach ($storageData as $key=>$files) {
				  	$i++;
					if($limitFrom && $limitFrom >= $i) {
						continue;
					}
					//var_dump($i, $id, $limitFrom);
					$list[] = array(
						'id' => $key,
						//'name' => $name,
						'name' => $this->getModule()->getView()->getBlockBup($key, $files), //getController()->
					);
					if($limitTo && ($i - $limitFrom) >= $limitTo) {
						break 1;
					}
			  }
				//}

			//}
		}
		return $list;
	}

	public function getList_A($d = array()) {
		$warehouse = frameBup::_()->getModule('warehouse')->getPath();
		if (!file_exists($warehouse)) {
			return array();
		}
		$arrFile = scandir($warehouse);
		$arrFile = array_diff($arrFile, array('.', '..'));

		arsort($arrFile);

		$arrId = array();
		foreach($arrFile as $file) {
			preg_match_all('~_id(\d+)~', $file, $out);
			if ( !empty($out[1][0]) && !in_array($out[1][0], $arrId) ) {
					$arrId[] = $out[1][0]; // get file array
			}
		}

		$arrBlock = array();
		foreach($arrId as $id) {
			if ($id > $d['limitFrom'] && $id <= $d['limitTo']){
			  $pattern = "~_id$id~";
			  foreach($arrFile as $file) {
				  if (preg_match($pattern, $file)){
					 $arrBlock[$id][] = $file;
				  }
			  }
			}
		}

		return $arrBlock;
	}

	public function getCount(){
		/*$warehouse = substr(ABSPATH, 0, strlen(ABSPATH)-1).frameBup::_()->getModule('options')->get('warehouse');
		return frameBup::_()->getModule('backup')->getModel()->lastID($warehouse);*/
		/*$count = 0;
		$list = $this->getStorageCache();
		if(!empty($list)) {
			foreach($list as $names) {
				$count += empty($names) ? 0 : count($names);
			}
		}
		return $count;*/
		return count($this->getStorageCache());
	}

}
