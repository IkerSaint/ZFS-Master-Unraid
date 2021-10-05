<?php
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

	function zfsnotify($subject, $description, $message, $type="normal") {
		$command = '/usr/local/emhttp/plugins/dynamix/scripts/notify -e "ZFS Master" -s "'.$subject.'" -d "'.$description.'" -m "'.$message.'" -i "'.$type.'"';
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
		
		$exec_out = escapeshellcmd($tmpout);
		
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