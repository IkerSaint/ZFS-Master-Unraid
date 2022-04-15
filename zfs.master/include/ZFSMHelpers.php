<?php

require_once "ZFSMBase.php";

function loadConfig($config, $explode=true) {	
	$zfsm_ret['refresh_interval'] = isset($config['general']['refresh_interval']) ? intval($config['general']['refresh_interval']) : 30;
	$zfsm_ret['destructive_mode'] = isset($config['general']['destructive_mode']) ? intval($config['general']['destructive_mode']) : 0;
		
	$zfsm_dataset_exclussion = isset($config['general']['exclussion']) ? $config['general']['exclussion'] : '';
		
	$zfsm_ret['snap_max_days_alert'] = isset($config['general']['snap_max_days_alert']) ? intval($config['general']['snap_max_days_alert']) : 30;
	$zfsm_ret['snap_prefix'] = isset($config['general']['snap_prefix']) ? $config['general']['snap_prefix'] : '';

	if (!isset($config['general']['snap_pattern']) || $config['general']['snap_pattern'] == ''):
		$zfsm_ret['snap_pattern'] = 'Y-m-d-His';
	else:
		$zfsm_ret['snap_pattern'] = $config['general']['snap_pattern'];
	endif;
		
	if ($explode):
		$zfsm_ret['dataset_exclussion'] = preg_split('/\r\n|\r|\n/', $zfsm_dataset_exclussion);
	else:
		$zfsm_ret['dataset_exclussion'] = $zfsm_dataset_exclussion;
	endif;
	
	return $zfsm_ret;
}

function zfsnotify( $subject, $description, $message, $type="normal") {	
	$command = $docroot.'/plugins/dynamix/scripts/notify -e "ZFS Master" -s "'.$subject.'" -d "'.$description.'" -m "'.$message.'" -i "'.$type.'"';

	shell_exec($command);
}

function fromStringToBytes($spacestr) {
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

function fromBytesToString($bytes) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		
	$bytes = max($bytes, 0); 
   	$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
   	$pow = min($pow, count($units) - 1); 

   	$bytes /= pow(1024, $pow);

   return round($bytes, 2) . ' ' . $units[$pow]; 
}
	  
function calculateFreePercent($used,$free) {
	$used_tmp = fromStringToBytes($used);
	$free_tmp = fromStringToBytes($free);
		
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

function getPoolInfo($zpool) {
	$cmd_line = "zfs program -jn -m 20971520 ".escapeshellarg($zpool)." script.lua ".escapeshellarg($zpool);
		
	return shell_exec($cmd_line.' 2>&1');
}

function getZFSPoolDatasets($zpool, $exc_pattern) {
	$exc_pattern = '/.*dockerfiles.*/';
	$cmd_line = "zfs program -jn -m 20971520 ".escapeshellarg($zpool)." ".$script_get_pool_data." ".escapeshellarg($zpool)." ".escapeshellarg($exc_pattern);

	$json_ret = shell_exec($cmd_line.' 2>&1');
	
	return json_decode($json_ret, true)['return'];
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
