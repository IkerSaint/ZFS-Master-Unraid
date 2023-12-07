<?php

require_once __ROOT__."/include/ZFSMError.php";

function loadConfig($config) {	
	$zfsm_ret['refresh_interval'] = isset($config['general']['refresh_interval']) ? intval($config['general']['refresh_interval']) : 30;
	$zfsm_ret['lazy_load'] = isset($config['general']['lazy_load']) ? intval($config['general']['lazy_load']) : "0";

	$zfsm_ret['destructive_mode'] = isset($config['general']['destructive_mode']) ? intval($config['general']['destructive_mode']) : 0;

	if (isset($config['general']['exclussion']) && $config['general']['exclussion'] != '' && !isset($config['general']['exclusion'])):
		$config['general']['exclusion'] = $config['general']['exclussion'];
	endif;

	if (!isset($config['general']['exclusion']) || $config['general']['exclusion'] == '' || $config['general']['exclusion'] == ' '):
		$zfsm_ret['dataset_exclusion'] = '';
	else:
		$zfsm_ret['dataset_exclusion'] = $config['general']['exclusion'];
	endif;
		
	$zfsm_ret['snap_max_days_alert'] = isset($config['general']['snap_max_days_alert']) ? intval($config['general']['snap_max_days_alert']) : 30;
	$zfsm_ret['snap_prefix'] = isset($config['general']['snap_prefix']) ? $config['general']['snap_prefix'] : '';

	if (!isset($config['general']['snap_pattern']) || $config['general']['snap_pattern'] == ''):
		$zfsm_ret['snap_pattern'] = 'Y-m-d-His';
	else:
		$zfsm_ret['snap_pattern'] = $config['general']['snap_pattern'];
	endif;

	if (!isset($config['general']['directory_listing'])):
		$zfsm_ret['directory_listing'] = array();
	else:
		$zfsm_ret['directory_listing'] = preg_split('/\r\n|\r|\n/', $config['general']['directory_listing']);
	endif;
	
	return $zfsm_ret;
}

function zfsnotify( $subject, $description, $message, $type="normal") {	
	$command = $GLOBALS["docroot"].'/plugins/dynamix/scripts/notify -e "ZFS Master" -s "'.$subject.'" -d "'.$description.'" -m "'.$message.'" -i "'.$type.'"';

	shell_exec($command);
}

function fromBytesToString($bytes) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		
	$bytes = max($bytes, 0); 
   	$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
   	$pow = min($pow, count($units) - 1); 

   	$bytes /= pow(1024, $pow);

   return round($bytes, 2) . ' ' . $units[$pow]; 
}
	  
function implodeWithKeys($glue, $array, $symbol = ': ') {
	return implode( $glue, array_map( function($k, $v) use($symbol) {
			return $k . $symbol . $v;
		},
		array_keys($array),
		array_values($array))
	);
}
	
function execCommand($cmd_line, &$exec_out) {
	exec($cmd_line, $out_arr, $val);

	$tmpout = str_replace("\n", '', implode(' ',$out_arr));
		
	$exec_out = escapeshellarg($tmpout);
		
	return $val;
}
	
function cleanZFSCreateDatasetParams($params) {
	$retParams = $params;

	unset($retParams['zpool']);
	unset($retParams['name']);
		
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

	if ($retParams['encryption'] == 'no'):
		$retParams['encryption'] = 'off';
	else:
		if (!isset($retParams['passphrase']) || $retParams['passphrase'] == ''):
			unset($retParams['encryption']);
		else:
			$retParams['encryption'] = 'on';
			$retParams['keylocation'] = 'prompt';
			$retParams['keyformat'] = 'passphrase';
		endif;
	endif;
		
	unset($retParams['mount']);
	
	if (!isset($retParams['quota']) || $retParams['quota'] == '' || $retParams['quota'] == '0'):
		unset($retParams['quota']);
	else:
		$retParams['quota'] = $retParams['quota'].$retParams['quotaunit'];
	endif;

	unset($retParams['quotaunit']);
		
	return $retParams;
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

function executeZFSProgram($zprogram, $zpool, $zargs) {
	$cmd_line = "zfs program -jn -m 20971520 ".escapeshellarg($zpool)." ".$zprogram." ".implode(" ", array_map("escapeshellarg", $zargs));
	$json_ret = shell_exec($cmd_line.' 2>&1');
	
	return json_decode($json_ret, true)['return'];
}

function executeSyncZFSProgram($zprogram, $zpool, $zargs) {
	$cmd_line = "zfs program -j -m 20971520 ".escapeshellarg($zpool)." ".$zprogram." ".implode(" ", array_map("escapeshellarg", $zargs));
	$json_ret = shell_exec($cmd_line.' 2>&1');
	
	return json_decode($json_ret, true)['return'];
}
	
function cleanupZPoolInfo($matched) {
	return array(
		'Pool' => trim($matched['pool']),
		'Health' => trim($matched['health']),
		'Name' => '',
		'Size' => trim($matched['size']),
		'MountPoint' => '',
		'Refer' => '',
		'Used' => trim($matched['used']),
		'Free' => trim($matched['free']),
		'Snapshots' => '',
		'Origin' => ''
	);
}

function sortDatasetArray($datasetArray) {
	if (isset($datasetArray['snapshots']) && $datasetArray['snapshots'] > 0):
		usort($datasetArray['snapshots'], function($item1, $item2) { 
			return $item1['creation'] <=> $item2['creation'];
		});
	endif;

	if (is_null($datasetArray['child']) || count($datasetArray['child']) <= 0):
		return $datasetArray;
	endif;

	ksort($datasetArray['child']);
	
	foreach ($datasetArray['child'] as $dataset):
		$datasetArray['child'][$dataset['name']] = sortDatasetArray($dataset);
	endforeach;
	
	return $datasetArray;
}

function generatePoolDatasetOptions($dataset_array) {
	if (count($dataset_array['child']) < 0):
		return;
	endif;
	
	foreach ($dataset_array['child'] as $zdataset):
		$option = ltrim(stristr($zdataset['name'], '/'), '/')."/";
		echo '<option value="'.$option.'">';

		if (count($zdataset['child']) > 0):
			generatePoolDatasetOptions($zdataset);
		endif;
	endforeach;
}

?>
