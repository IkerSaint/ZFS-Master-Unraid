<?php

define('__ROOT__', dirname(dirname(__FILE__)));

require_once __ROOT__."/include/ZFSMBase.php";
require_once __ROOT__."/include/ZFSMHelpers.php";

#region zpools

function getZFSPools() {
	$regex = "/^(?'pool'[\w-]+)\s+(?'size'\d+.?\d+.)\s+(?'used'\d+.?\d+.)\s+(?'free'\d+.?\d+.)\s+(?'checkpoint'(\d+.?\d+.)|-)\s+(?'expandz'(\d+.?\d+.)|-)\s+(?'fragmentation'\d+.)\s+(?'usedpercent'\d+.)\s+(?'dedup'\d+.?\d+x)\s+(?'health'\w+)/";
	  
	$tmpPools = processCmdLine($regex, "zpool list -v", "cleanupZPoolInfo");
	$retPools = array();
	  
	foreach ($tmpPools as $pool):
		$retPools[$pool["Pool"]] = $pool;
	endforeach;
  
	return $retPools;
}

function getZFSPoolDevices($zpool) {
	$cmd_line = "zpool status -v ".$zpool." | awk '/config:/{flag=1;next}/errors:/{flag=0}flag{if($1!=\"NAME\" && NF>1)print $1}'|tail -n+2"; 
	return trim(shell_exec($cmd_line.' 2>&1'));
}

function getZFSPoolDatasets($zpool, $zexc_pattern) {
	$result = executeZFSProgram($GLOBALS["script_pool_get_datasets"], $zpool, array($zpool, $zexc_pattern));
	
	return sortDatasetArray($result);
}

function getZFSPoolDatasetsAndSnapshots($zpool, $zexc_pattern) {
	$result = executeZFSProgram($GLOBALS["script_pool_get_datasets_snapshots"], $zpool, array($zpool, $zexc_pattern));
	
	return sortDatasetArray($result);
}

#endregion zpools

#region datasets

function getDatasetProperty($zpool, $zdataset, $zproperty) {
	$array_ret = executeZFSProgram($GLOBALS["script_dataset_get_property"], $zpool, array($zdataset, $zproperty));

	return $array_ret;
}

function getAllDatasetProperties($zpool, $zdataset) {
	$array_ret = executeZFSProgram($GLOBALS["script_dataset_get_properties"], $zpool, array($zdataset));

	return $array_ret;
}

function getDatasetSnapshots($zpool, $zdataset) {
	$array_ret = executeZFSProgram($GLOBALS["script_dataset_get_snapshots"], $zpool, array($zdataset));

	if (count($array_ret) > 0):
		usort($array_ret, function($item1, $item2) { 
			return $item1['creation'] <=> $item2['creation'];
		});
	endif;

	return $array_ret;
}

function createDataset($zpool, $zdataset, $zoptions) {
	$passphrase = $zoptions["passphrase"] ?? "";
	unset($zoptions["passphrase"]);
		
	$cmd_line = "zfs create -vP";
	$cmd_line .= " -o ".implodeWithKeys(" -o ", $params, "=");
	$cmd_line .= ' '.escapeshellarg($zpool.'/'.$zdataset).$boutput_str;

	if ($zoptions["encryption"] == 'on'):
		$cmd_line = "echo ".escapeshellarg($passphrase)." | echo ".escapeshellarg($passphrase)." | ".$cmd_line;
	endif;

	$ret = execCommand($cmd_line, $exec_result);
		
	if ($ret == 0):
		zfsnotify( "ZFS Create", "Creation of dataset ".$zpool."/".$zdataset." successful", $cmdoutput_str.$exec_result."","normal");
		return true;
	endif;
	
	zfsnotify( "ZFS Create", "Creation of dataset ".$zpool."/".$zdataset." failed, return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
	return false;
}

function renameDataset($zpool, $zdataset, $zdataset_new_name, $force) {
	$cmd_line = "zfs rename ".$force.escapeshellarg($zdataset_new_name). " ".escapeshellarg($zdataset_new_name).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);
	
	if ($ret == 0):
		zfsnotify( "ZFS Rename ", "Dataset ".$zdataset." renamed successfully to ".$zdataset_new_name, $cmdoutput_str.$exec_result."","normal");
		return true;
	endif;
	
	zfsnotify( "ZFS Rename", "Unable to rename dataset ".$zdataset.", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
	return false;
}

function setDatasetProperty($zpool, $zdataset, $zproperty) {
	# Currently zfs programs only support "user properties" so, this has to be done the hard way

	return $array_ret;
}

function setDatasetProperties($zpool, $zdataset, $zproperties) {
	# Currently zfs programs only support "user properties" so, this has to be done the hard way
	
	return $array_ret;
}

function lockDataset($zpool, $zdataset) {
	$cmd_line = "zfs umount -f ".escapeshellarg($zpool."/".$zdataset).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret != 0):
		zfsnotify( "ZFS Umount", "Unable to unmount dataset, return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
		return false;
	endif;

	$cmd_line = "zfs unload-key -r ".escapeshellarg($zpool."/".$zdataset).$boutput_str;
	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		zfsnotify( "ZFS Dataset Lock", "Dataset ".$zdataset." Locked successfully", $cmdoutput_str.$exec_result."","normal");
		return true;
	endif;
	
	zfsnotify( "ZFS Dataset Lock", "Unable to unload the encryption key ".$zdataset.", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
	return false;
}

function unlockDataset($zpool, $zdataset, $zpass) {
	$cmd_line = "echo ".escapeshellarg($zpass)."| zfs mount -l ".$zpool.$zdataset.$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);
	
	if ($ret == 0):
		zfsnotify( "ZFS Dataset Unlock", "Dataset ".$zdataset." Unlocked successfully", $cmdoutput_str.$exec_result."","normal");
		return true;
	endif;

	zfsnotify( "ZFS Dataset Unlock", "Unable to Unlock dataset ".$zdataset.", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
	
	return false;
}

function promoteDataset($zpool, $zdataset, $zforce) {
	$array_ret = executeZFSProgram($GLOBALS["script_promote_dataset"], $zpool, array($zdataset, $zforce));
	
	return $array_ret;
}

function destroyDataset($zpool, $zdataset, $zforce) {
	$array_ret = executeSyncZFSProgram($GLOBALS["script_dataset_destroy"], $zpool, array($zdataset, $zforce));
	
	return $array_ret;
}

#region snapshots

function createDatasetSnapshot($zdataset, $znapshot, $zrecursive) {
	$zpool = explode("/", $zdataset)[0];

	$array_ret = executeSyncZFSProgram($GLOBALS["script_dataset_create_snapshot"], $zpool, array($zdataset, $znapshot, $zrecursive));
	
	return $array_ret;
}

function rollbackDatasetSnapshot($zpool, $znapshot_name) {
	$array_ret = executeZFSProgram($GLOBALS["script_dataset_rollback_snapshot"], $zpool, array($zdataset, $znapshot_name));
	
	return $array_ret;
}

function renameDatasetSnapshot($zpool, $zsnapshot, $znapshot_new_name) {
	$array_ret = executeZFSProgram($GLOBALS["script_dataset_rename_snapshot"], $zpool, array($zdataset, $zsnapshot, $znapshot_new_name));
	
	return $array_ret;
}

function sendDatasetSnapshot($zpool, $znapshot_name, $zoptions) {
	// TODO
	return $array_ret;
}

function receiveDatasetSnapshot($zpool, $znapshot_name, $zoptions) {
	// TODO
	return $array_ret;
}

function holdDatasetSnapshot($znapshot) {
	$cmd_line = "zfs hold zfsmaster ".$znapshot.$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		zfsnotify( "ZFS Hold", "Snapshot ".$znapshot." reference added successfully", $cmdoutput_str.$exec_result."","normal");
		return true;
	endif;
	
	zfsnotify( "ZFS Hold", "Unable to add reference to snapshot ".$znapshot.", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
	
	return false;
}

function releaseDatasetSnapshot($znapshot) {
	$cmd_line = "zfs release zfsmaster ".escapeshellarg($znapshot).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		zfsnotify( "ZFS Release", "Snapshot ".$znapshot." reference removed successfully", $cmdoutput_str.$exec_result."","normal");
		return true;
	endif;
	
	zfsnotify( "ZFS Release", "Unable to remove reference from snapshot ".$znapshot.", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
	
	return false;
}

function cloneDatasetSnapshot($znapshot, $zclone) {
	$cmd_line = "zfs clone ".escapeshellarg($znapshot)." ".escapeshellarg($zclone).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		zfsnotify( "ZFS Clone", "Snapshot ".$znapshot." cloned successfully", $cmdoutput_str.$exec_result."","normal");
		return true;
	endif;
	
	zfsnotify( "ZFS Clone", "Unable to clone snapshot ".$znapshot.", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
	
	return false;
}

function deleteDatasetSnapshot($zdataset, $znapshot, $destroy_all) {
	$zpool = explode("/", $zdataset)[0];

	$array_ret = executeZFSProgram($GLOBALS["script_dataset_destroy_snapshot"], $zpool, array($zdataset, $zsnapshot, $destroy_all));

	return $array_ret;
}

#endregion snapshots

#endregion datasets


?>