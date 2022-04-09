<?php

	function loadConfig($config, $explode=true) {	
		$zfsm_ret['refresh_interval'] = isset($config['general']['refresh_interval']) ? intval($config['general']['refresh_interval']) : 30;
		$zfsm_ret['destructive_mode'] = isset($config['general']['destructive_mode']) ? intval($config['general']['destructive_mode']) : 0;
		
		$zfsm_dataset_exclussion = isset($config['general']['exclussion']) ? $config['general']['exclussion'] : '';
		
		$zfsm_ret['snap_max_days_alert'] = isset($config['general']['snap_max_days_alert']) ? intval($config['general']['snap_max_days_alert']) : 30;
		
		if ($explode):
			$zfsm_ret['dataset_exclussion'] = preg_split('/\r\n|\r|\n/', $zfsm_dataset_exclussion);
		else:
			$zfsm_ret['dataset_exclussion'] = $zfsm_dataset_exclussion;
		endif;
		
		return $zfsm_ret;
	}

	function zfsnotify($docroot, $subject, $description, $message, $type="normal") {	
		$command = $docroot.'/plugins/dynamix/scripts/notify -e "ZFS Master" -s "'.$subject.'" -d "'.$description.'" -m "'.$message.'" -i "'.$type.'"';

		shell_exec($command);
	}

	function fromLetterToBytes($spacestr) {
		$return_number = (double)$spacestr;
		
		switch ($spacestr[-1]) {
			case 'T':
				$return_number *= 1024;
			case 'G':
				$return_number *= 1024;
			case 'M':
				$return_number *= 1024;
			case 'K':
				$return_number *= 1024;
			default:
				break;
		}
		  
		return $return_number;
	}
	  
	function calculateFreePercent($used,$free) {
		$used_tmp = fromLetterToBytes($used);
		$free_tmp = fromLetterToBytes($free);
		
		$result = $free_tmp/($free_tmp+$used_tmp);
		return $result*100;
	}
	  
	function implodeWithKeys($glue, $array, $symbol = ': ') {
		return implode( $glue, array_map( function($k, $v) use($symbol) {
				return $k . $symbol . $v;
			},
			array_keys($array),
			array_values($array))
		);
	}
	  
	function daysToNow($timestamp) {					
		$currentdate = new DateTime();
		$diffdate = new DateTime();
		
		$diffdate->setTimestamp($timestamp);
		$difference = $currentdate->diff($diffdate);

		return $difference->days;
	}
	
	function execCommand($cmd_line, &$exec_out) {
		exec($cmd_line, $out_arr, $val);

		$tmpout = str_replace("\n", '', implode(' ',$out_arr));
		
		$exec_out = escapeshellarg($tmpout);
		
		return $val;
	}
	
	function cleanZFSCreateDatasetParams($params) {
		$retParams = $params;
		
		foreach ($retParams as $key => $value):
			if ($value == 'inherit'):
				unset($retParams[$key]);
			endif;
		endforeach;
		
		if ($retParams['mount'] == 'no'):
			$retParams['mountpoint'] = 'none';
		else:
			if (!isset($retParams['mountpoint']) || $retParams['mountpoint'] == ''):
				unset($retParams['mountpoint']);
			endif;
		endif;
		
		unset($retParams['mount']);
		
		if (!isset($retParams['quota']) || $retParams['quota'] == '' || $retParams['quota'] == '0'):
			unset($retParams['quota']);
		endif;
		
		return $retParams;
	}
	
	function createZFSCreateDatasetCMDLine($params) {
		$zdataset_name = $params['zpool'].'/'.$params['name'];
		
		unset($params['zpool']);
		unset($params['name']);
		
		$cmd_line = 'zfs create -vP';		
		$cmd_line .= ' -o '.implodeWithKeys(' -o ', $params, '=');
		$cmd_line .= ' '.$zdataset_name;
		
		return $cmd_line;
	}
	
	function processCmdLine($regex, $cmd_line, $cleanfunction) {
		$data = shell_exec($cmd_line.' 2>&1');
		$dataArr = @preg_split("/\n/", $data, -1, PREG_SPLIT_NO_EMPTY);
		$returnData = array();
		
		foreach ($dataArr as $dataline):
			@preg_match($regex, $dataline, $matches);

			if (count($matches) <= 0):
				continue;
			endif;

			$returnData[] = $cleanfunction($matches);
		endforeach;
		return $returnData;
	}
	
	function cleanupZPoolInfo($matched) {
		return array(
			'Pool' => trim($matched['pool']),
			'Health' => trim($matched['health']),
			'Name' => '',
			'Size' => trim($matched['size']),
			'Used' => trim($matched['used']),
			'Free' => trim($matched['free']),
			'Refer' => '',
			'MountPoint' => '',
			'Snapshots' => ''
		);
	}
  
	function cleanupZDatasetInfo($matched) {
		return array(
			'Pool' => '',
			'Health' => '',
			'Name' => trim($matched['name']),
			'Size' => '',
			'Used' => trim($matched['used']),
			'Free' => trim($matched['free']),
			'Refer' => trim($matched['refer']),
			'MountPoint' => trim($matched['mount']),
			'Snapshots' => '',
			'Attributes' => array(
				'Creation Date' => trim($matched['creation']),
				'Compression' => trim($matched['compression']),
				'Compress Ratio' => trim($matched['cratio']),
				'RecordSize' => trim($matched['recordsize']),
				'Access Time' => trim($matched['atime']),
				'XAttr' => trim($matched['xattr']),
				'Primary Cache' => trim($matched['primarycache']),
				'Quota' => trim($matched['quota']),
				'Read Only' => trim($matched['readonly']),
				'Case Sensitive' => trim($matched['case']),
				'Sync' => trim($matched['sync']),
				'Space used by Snaps' => trim($matched['snapused'])
			)
		);
	}
	
	function filterDataset($dataset_name, $regex_array) {
		if (count($regex_array) == 0):
			return false;
		endif;
		
		foreach($regex_array as $regex):
			if ($regex != '' && @preg_match($regex, $dataset_name)):
				return true;
			endif;
		endforeach;
		
		return false;
	}

	function getZFSDatasetSnapshots($zdataset_name) {
		$regex ="/^(?'name'[\w-]+(\/[\S-]+)?+)\s+(?'used'\d+.?)\s+(?'refer'\d+.?\d+.)\s+(?'defer_destroy'[\w-]+)\s+(?'userrefs'\d+)\s+(?'creation'.*)/";
		$cmd_line = 'zfs list -o name,used,refer,defer_destroy,userrefs,creation -Hp -t snapshot '.$zdataset_name;

		return processCmdLine($regex, $cmd_line, 'cleanupZDatasetInfo');
	}
	
	function getZFSDatasetSnapInfo(&$zdataset) {
		$zdataset['Snapshots'] = 0;
		$zdataset['Attributes']['Last Snap Date'] = 'N/A';
		$zdataset['Attributes']['Last Snap'] = 'N/A';
	  
		$regex = "/^(?'snapscount'\d+)\s(?'lsnap'[\w-]+(\/[\S-]+)+)\s+(?'lsnapdate'\d+)/";
		$cmd_line = 'zfs list -o name,creation -Hp -t snapshot '.$zdataset['Name'].' | awk \'{++count } END {printf "%d %s", count, $0}\' 2>&1';
		$data = shell_exec($cmd_line);
	  
		if (@preg_match($regex, $data, $matches)):
			$zdataset['Snapshots'] = $matches['snapscount'];
			$zdataset['Attributes']['Last Snap Date'] = $matches['lsnapdate'];
			$zdataset['Attributes']['Last Snap'] = $matches['lsnap'];
		endif;
		
		return $zdataset;
	}
	
	function getZFSPoolDatasets($zpool_name, &$snapCount, $regex_array, $extended_info=true) {
		$regex = "/^(?'name'[\w-]+(\/[\S-]+)?+)\s+(?'used'\d+.?\d+.)\s+(?'free'\d+.?\d+.)\s+(?'refer'\d+.?\d+.)\s+(?'mount'\/?[\w-]+(\/[\S-]+)?+)\s+(?'compression'[\w-]+)\s+(?'cratio'\d+.?\d+.)\s+(?'snapused'\d+.?\d*.?)\s+(?'quota'\w+)\s+(?'recordsize'\d+.)\s+(?'atime'\w+)\s+(?'xattr'\w+)\s+(?'primarycache'\w+)\s+(?'readonly'\w+)\s+(?'case'\w+)\s+(?'sync'\w+)\s+(?'creation'.*)/";
		$cmd_line = 'zfs list -o name,used,avail,refer,mountpoint,compress,compressratio,usedbysnapshots,quota,recordsize,atime,xattr,primarycache,readonly,case,sync,creation -r '.$zpool_name;
		
		$tmpDatasets = processCmdLine($regex, $cmd_line, 'cleanupZDatasetInfo');
		$retDatasets = array();
		$snapCount = 0;
		
		if (!$extended_info):
			return $tmpDatasets;
		endif;
		
		foreach ($tmpDatasets as $dataset):
			if (filterDataset($dataset['Name'], $regex_array)):
				continue;
			endif;
			
			$tmpdataset = getZFSDatasetSnapInfo($dataset);
			$retDatasets[] = $tmpdataset;
			$snapCount += $tmpdataset['Snapshots'];
		endforeach;
		
		return $retDatasets;
	}
	
	function getZFSPoolDevices($zpool) {
		$cmd_line = "zpool status -v ".$zpool." | awk 'NR > 8 {print last} {last=$1}'";
		return trim(shell_exec($cmd_line.' 2>&1'));
	}
	
	function getZFSPools() {
		$regex = "/^(?'pool'[\w-]+)\s+(?'size'\d+.?\d+.)\s+(?'used'\d+.?\d+.)\s+(?'free'\d+.?\d+.)\s+-\s+-\s+(?'fragmentation'\d+.)\s+(?'usedpercent'\d+.)\s+(?'dedup'\d+.?\d+x)\s+(?'health'\w+)/";
	  
		$tmpPools = processCmdLine($regex, 'zpool list -v', 'cleanupZPoolInfo');
		$retPools = array();
	  
		foreach ($tmpPools as $pool):
			$retPools[$pool['Pool']] = $pool;
		endforeach;
	  
		return $retPools;
	}
?>
