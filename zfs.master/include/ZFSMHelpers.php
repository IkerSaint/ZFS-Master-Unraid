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
			return $k . $symbol . $v;},
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
?>