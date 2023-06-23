<?php

require_once "$docroot/webGui/include/Helpers.php";
require_once "$docroot/plugins/$plugin/include/ZFSMBase.php";

#region zpools

function getZFSPools() {
	$regex = "/^(?'pool'[\w-]+)\s+(?'size'\d+.?\d+.)\s+(?'used'\d+.?\d+.)\s+(?'free'\d+.?\d+.)\s+(?'checkpoint'(\d+.?\d+.)|-)\s+(?'expandz'(\d+.?\d+.)|-)\s+(?'fragmentation'\d+.)\s+(?'usedpercent'\d+.)\s+(?'dedup'\d+.?\d+x)\s+(?'health'\w+)/";
	  
	$tmpPools = processCmdLine($regex, 'zpool list -v', 'cleanupZPoolInfo');
	$retPools = array();
	  
	foreach ($tmpPools as $pool):
		$retPools[$pool['Pool']] = $pool;
	endforeach;
  
	return $retPools;
}

function getZFSPoolDevices($zpool) {
	$cmd_line = "zpool status -v ".$zpool." | awk '/config:/{flag=1;next}/errors:/{flag=0}flag{if($1!=\"NAME\" && NF>1)print $1}'|tail -n+2"; 
	return trim(shell_exec($cmd_line.' 2>&1'));
}

function getZFSPoolDatasets($zpool, $exc_pattern) {
	$cmd_line = "zfs program -jn -m 20971520 ".escapeshellarg($zpool)." ".$GLOBALS["script_get_pool_data"]." ".escapeshellarg($zpool)." ".escapeshellarg($exc_pattern);

	$json_ret = shell_exec($cmd_line.' 2>&1');
	
	return sortDatasetArray(json_decode($json_ret, true)['return']);
}

#endregion zpools

#region datasets

#region getters

function getDatasetProperty($zpool, $zdataset, $zproperty) {
	$cmd_line = "zfs program -jn -m 20971520 ".escapeshellarg($zpool)." ".$GLOBALS["script_get_pool_dataset_properties"]." ".escapeshellarg($zdataset)." ".escapeshellarg($zproperty)." ";
	
	$json_ret = shell_exec($cmd_line.' 2>&1');
	$array_ret = json_decode($json_ret, true)['return'];

	return $array_ret;
}

function getAllDatasetProperties($zpool, $zdataset) {
	$cmd_line = "zfs program -jn -m 20971520 ".escapeshellarg($zpool)." ".$GLOBALS["script_get_pool_dataset_properties"]." ".escapeshellarg($zdataset)." ";
	
	$json_ret = shell_exec($cmd_line.' 2>&1');
	$array_ret = json_decode($json_ret, true)['return'];

	return $array_ret;
}

function getDatasetSnapshots($zpool, $zdataset) {
	$cmd_line = "zfs program -jn -m 20971520 ".escapeshellarg($zpool)." ".$GLOBALS["script_get_snapsthots_data"]." ".escapeshellarg($zdataset);

	$json_ret = shell_exec($cmd_line.' 2>&1');
	$array_ret = json_decode($json_ret, true)['return'];

	if (count($array_ret) > 0):
		usort($array_ret, function($item1, $item2) { 
			return $item1['creation'] <=> $item2['creation'];
		});
	endif;

	return $array_ret;
}

#endregion getters

#region setters


#endregion setters

#endregion datasets

?>
