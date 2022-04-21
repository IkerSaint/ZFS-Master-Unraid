<?php

function loadConfig($config) {	
	$zfsm_ret['refresh_interval'] = isset($config['general']['refresh_interval']) ? intval($config['general']['refresh_interval']) : 30;
	$zfsm_ret['destructive_mode'] = isset($config['general']['destructive_mode']) ? intval($config['general']['destructive_mode']) : 0;

	if (!isset($config['general']['exclussion']) || $config['general']['exclussion'] == ''):
		$zfsm_ret['dataset_exclussion'] = ' ';
	else:
		$zfsm_ret['dataset_exclussion'] = $config['general']['exclussion'];
	endif;
		
	$zfsm_ret['snap_max_days_alert'] = isset($config['general']['snap_max_days_alert']) ? intval($config['general']['snap_max_days_alert']) : 30;
	$zfsm_ret['snap_prefix'] = isset($config['general']['snap_prefix']) ? $config['general']['snap_prefix'] : '';

	if (!isset($config['general']['snap_pattern']) || $config['general']['snap_pattern'] == ''):
		$zfsm_ret['snap_pattern'] = 'Y-m-d-His';
	else:
		$zfsm_ret['snap_pattern'] = $config['general']['snap_pattern'];
	endif;
	
	return $zfsm_ret;
}

function zfsnotify( $subject, $description, $message, $type="normal") {	
	$command = $GLOBALS["docroot"].'/plugins/dynamix/scripts/notify -e "ZFS Master" -s "'.$subject.'" -d "'.$description.'" -m "'.$message.'" -i "'.$type.'"';

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

function sortDatasetArray($datasetArray) {
	ksort($datasetArray['child']);
	usort($datasetArray['snapshots'], function($item1, $item2) { 
		return $item1['creation'] > $item2['creation'];
	});
	
	foreach ($datasetArray['child'] as $dataset):
		$datasetArray['child'][$dataset['name']] = sortDatasetArray($dataset);
	endforeach;
	
	return $datasetArray;
}

function findDatasetInArray($dataset_name, $datasetArray) {
	foreach ($datasetArray['child'] as $dataset):
		if (strcmp($dataset['name'], $dataset_name) == 0):
			return $dataset;
		endif;

		$ret = findDatasetInArray($dataset_name, $dataset);

		if ($ret != null):
			return $ret;
		endif;
	endforeach;

	return null;
}

function getZFSPoolDatasets($zpool, $exc_pattern) {
	$cmd_line = "zfs program -jn -m 20971520 ".escapeshellarg($zpool)." ".$GLOBALS["script_get_pool_data"]." ".escapeshellarg($zpool)." ".escapeshellarg($exc_pattern);

	$json_ret = shell_exec($cmd_line.' 2>&1');
	
	return sortDatasetArray(json_decode($json_ret, true)['return']);
}

function getLastSnap($zsnapshots) {
	$lastsnap = $zsnapshots[0];

	foreach ($zsnapshots as $snap):
		if ($snap['creation'] > $lastsnap['creation']):
			$lastsnap = $snap;
		endif;
	endforeach;

	return $lastsnap;
}

function generateDatasetRow($zpool, $zdataset, $display, $zfsm_cfg) {
	echo '<tr class="zdataset-'.$zpool.'" style="display: '.$display.'">';
	echo '<td>';
	echo '</td>';
	echo '<td>';
	echo '</td>';
	echo '<td>';
		$creationdate = new DateTime();
		$creationdate->setTimestamp($zdataset['creation']);

		$tmp_array = ["Creation Date" => $creationdate->format('Y-m-d H:i:s'),
				"Compression" =>  $zdataset['compression'],
				"Compress Ratio" => ($zdataset['compressratio']/100),
				"Record Size" =>  fromBytesToString($zdataset['recordsize']),
				"Access Time" =>  $zdataset['atime'],
				"XAttr" =>  $zdataset['xattr'],
				"Primary Cache" =>  $zdataset['primarycache'],
				"Quota" =>  fromBytesToString($zdataset['quota']),
				"Read Only" =>  $zdataset['readonly'],
				"Case Sensitive" =>  $zdataset['casesensitivity'],
				"Sync" =>  $zdataset['sync'],
				"Space used by Snaps" =>  fromBytesToString($zdataset['usedbysnapshots'])];

		$icon_color = 'grey';
			
		if (count($zdataset['snapshots']) > 0):
			$snap = getLastSnap($zdataset['snapshots']);
									
			$snapdate = new DateTime();
			$snapdate->setTimestamp($snap['creation']);
				
			if (daysToNow($snap['creation']) > $zfsm_cfg['snap_max_days_alert']):
				$icon_color = 'orange';
			else:
				$icon_color = '#486dba';
			endif;
				
			$tmp_array['Last Snap Date'] = $snapdate->format('Y-m-d H:i:s');
			$tmp_array['Last Snap'] = $snap['name'];
		endif;
			
		echo '<a class="info hand">';
		echo '<i class="fa fa-hdd-o icon" style="color:'.$icon_color.'"></i>';
		echo '<span>'.implodeWithKeys('<br>', $tmp_array).'</span>';
		echo '</a>';
		echo $zdataset['name'];
	echo '</td>';
	echo '<td>';
		$id = md5($zdataset['name']);
		echo '<button type="button" id="'.$id.'" onclick="addDatasetContext(\''.$zpool.'\', \''.$zdataset['name'].'\', '.count($zdataset['snapshots']).', \''.$id.'\', '.$zfsm_cfg['destructive_mode'].');" class="zfs_compact">Actions</button></span>';
	echo '</td>';

	echo '<td>';
		$percent = 100-round(calculateFreePercent($zdataset['used'], $zdataset['available']));
		echo '<div class="usage-disk"><span style="position:absolute; width:'.$percent.'%" class=""><span>'.fromBytesToString($zdataset['used']).'</span></div>';
	echo '</td>';
	echo '<td>';
		$percent = round(calculateFreePercent($zdataset['used'], $zdataset['available']));
		echo '<div class="usage-disk"><span style="position:absolute; width:'.$percent.'%" class=""><span>'.fromBytesToString($zdataset['available']).'</span></div>';
	echo '</td>';
	echo '<td>';
		echo fromBytesToString($zdataset['referenced']);
	echo '</td>';
	echo '<td>';
		echo $zdataset['mountpoint'];
	echo '</td>';
	echo '<td>';
		$icon_color = 'grey';
		
		if (count($zdataset['snapshots']) > 0):
			$snap = getLastSnap($zdataset['snapshots']);
			$days = daysToNow($snap['creation']);
			
			if ($days > $zfsm_cfg['snap_max_days_alert']):
				$icon_color = 'orange';
			else:
				$icon_color = '#486dba';
			endif;
		endif;
	
		echo '<i class="fa fa-camera-retro icon" style="color:'.$icon_color.'"></i> ';
		echo count($zdataset['snapshots']);
	echo '</td>';
	echo '</tr>';
}

function generateDatasetArrayRows($zpool, $dataset_array, $display, $zfsm_cfg){
	foreach ($dataset_array['child'] as $zdataset):
		generateDatasetRow($zpool, $zdataset, $display, $zfsm_cfg);

		if (count($zdataset['child']) > 0):
			generateDatasetArrayRows($zpool, $zdataset, $display, $zfsm_cfg);
		endif;
	endforeach;
}

function generatePoolDatasetOptions($dataset_array) {
	foreach ($dataset_array['child'] as $zdataset):
		$option = ltrim(stristr($zdataset['name'], '/'), '/')."/";
		echo '<option value="'.$option.'">';

		if (count($zdataset['child']) > 0):
			generatePoolDatasetOptions($zdataset);
		endif;
	endforeach;
}
	
function getZFSPoolDevices($zpool) {
	$cmd_line = "zpool status -v ".$zpool." | awk 'NR > 8 {print last} {last=$1}'";
	return trim(shell_exec($cmd_line.' 2>&1'));
}
	
function getZFSPools() {
	$regex = "/^(?'pool'[\w-]+)\s+(?'size'\d+.?\d+.)\s+(?'used'\d+.?\d+.)\s+(?'free'\d+.?\d+.)\s+(?'checkpoint'(\d+.?\d+.)|-)\s+(?'expandz'(\d+.?\d+.)|-)\s+(?'fragmentation'\d+.)\s+(?'usedpercent'\d+.)\s+(?'dedup'\d+.?\d+x)\s+(?'health'\w+)/";
	  
	$tmpPools = processCmdLine($regex, 'zpool list -v', 'cleanupZPoolInfo');
	$retPools = array();
	  
	foreach ($tmpPools as $pool):
		$retPools[$pool['Pool']] = $pool;
	endforeach;
  
	return $retPools;
}

?>
