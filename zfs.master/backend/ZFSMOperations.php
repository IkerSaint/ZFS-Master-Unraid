<?php

define('__ROOT__', dirname(dirname(__FILE__)));

require_once __ROOT__."/include/ZFSMBase.php";
require_once __ROOT__."/include/ZFSMHelpers.php";

#region helpers

function refreshData() {
	touch("/tmp/zfsm_reload");
}

function buildArrayRet() {
	$array_ret = array();
	$array_ret['succeeded'] = array();
	$array_ret['failed'] = array();

	return $array_ret;
}

function addToDirectoryListing($zdataset) {
	
}

function removeFromDirectoryListing($zdataset) {
	
}

#endregion helpers

#region zpools

function getZFSPools() {
	$regex = "/^(?'pool'[\w\._\-]+)\s+(?'size'[\d.]+.)\s+(?'used'[\d.]+.)\s+(?'free'[\d.]+.)\s+(?'checkpoint'([\d.]+.)|-)\s+(?'expandz'([\d.]+.)|-)\s+(?'fragmentation'([\d.]+.)|-)\s+(?'usedpercent'[\d.]+.)\s+(?'dedup'[\d.]+.x)\s+(?'health'\w+)/";
	  
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

function getDatasetDirectories($zdataset_path) {
	$dirs = glob($zdataset_path."/{,.}*" , GLOB_ONLYDIR | GLOB_BRACE);

	if (!isset($dirs) || !is_array($dirs)):
    	return array();
	endif;
	
	$array_ret = array_diff($dirs, array($zdataset_path."/..", $zdataset_path."/."));

	return $array_ret;
}

function getAllDatasetProperties($zdataset) {
	$zpool = explode("/", $zdataset)[0];
	
	$array_ret = executeZFSProgram($GLOBALS["script_dataset_get_properties"], $zpool, array($zdataset));

	return $array_ret;
}

function getDatasetSnapshots($zpool, $zdataset) {
	$array_ret = executeZFSProgram($GLOBALS["script_dataset_get_snapshots"], $zpool, array($zdataset));

	if ($array_ret == null):
		return array();
	endif;

	if (count($array_ret) > 0):
		usort($array_ret, function($item1, $item2) { 
			return $item1['creation'] <=> $item2['creation'];
		});
	endif;

	return $array_ret;
}

function createDataset( $zdataset, $zoptions) {
	$array_ret = buildArrayRet();

	$passphrase = $zoptions["passphrase"] ?? "";
	unset($zoptions["passphrase"]);
		
	$cmd_line = "zfs create -vP";
	$cmd_line .= " -o ".implodeWithKeys(" -o ", $zoptions, "=");
	$cmd_line .= ' '.escapeshellarg($zdataset).$boutput_str;

	if ($zoptions["encryption"] == 'on'):
		$cmd_line = "echo ".escapeshellarg($passphrase)." | echo ".escapeshellarg($passphrase)." | ".$cmd_line;
	endif;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		$array_ret['succeeded'][$zdataset] = 0;

		$zpool = explode("/", $zdataset)[0];
		
		$mountpoint = getDatasetProperty($zpool, $zdataset, 'mountpoint');

		chown($mountpoint, 'nobody');
		chgrp($mountpoint, 'users');
	else:
		$array_ret['failed'][$zdataset] = $ret;
	endif;
	
	return $array_ret;
}

function renameDataset($zdataset, $zdataset_new_name, $force) {
	$array_ret = buildArrayRet();

	$force = ($_POST['force'] == '1') ? '-f ' : '';
	$cmd_line = "zfs rename ".$force.escapeshellarg($zdataset)." ".escapeshellarg($zdataset_new_name).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);
	
	if ($ret == 0):
		$array_ret['succeeded'][$zdataset_new_name] = 0;
	else:
		$array_ret['failed'][$zdataset_new_name] = $ret;
	endif;
	
	return $array_ret;
}

function setDatasetProperty( $zdataset, $zproperty, $zvalue) {
	if ($zvalue == "inherit"):
		$cmd_line = "zfs inherit ".$zproperty." ".escapeshellarg($zdataset).$boutput_str;
	else:
		$cmd_line = "zfs set ".$zproperty."=".$zvalue." ".escapeshellarg($zdataset).$boutput_str;
	endif;

	return execCommand($cmd_line, $exec_result);
}

function setDatasetProperties( $zdataset, $zproperties) {
	$array_ret = buildArrayRet();

	foreach ($zproperties as $key => $value):
		$ret = setDatasetProperty($zdataset, $key, $value);

		if ($ret == 0):
			$array_ret['succeeded'][$key] = 0;
		else:
			$array_ret['failed'][$key] = $ret;
		endif;
	endforeach;

	return $array_ret;
}

function lockDataset($zdataset) {
	$array_ret = buildArrayRet();

	$cmd_line = "zfs umount -f ".escapeshellarg($zdataset).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret != 0):
		$array_ret['failed'][$znapshot] = $ret;
		return $array_ret;
	endif;

	$cmd_line = "zfs unload-key -r ".escapeshellarg($zdataset).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		$array_ret['succeeded'][$zdataset] = 0;
	else:
		$array_ret['failed'][$zdataset] = $ret;
	endif;
	
	return $array_ret;
}

function unlockDataset($zdataset, $zpass) {
	$array_ret = buildArrayRet();

	$cmd_line = "echo ".escapeshellarg($zpass)."| zfs mount -l ".escapeshellarg($zdataset).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		$array_ret['succeeded'][$zdataset] = 0;
	else:
		$array_ret['failed'][$zdataset] = $ret;
	endif;
	
	return $array_ret;
}

function promoteDataset($zdataset, $zforce) {
	$zpool = explode("/", $zdataset)[0];

	$array_ret = executeSyncZFSProgram($GLOBALS["script_promote_dataset"], $zpool, array($zdataset, $zforce));
	
	return $array_ret;
}

function destroyDataset($zdataset, $zforce) {
	$array_ret = buildArrayRet();
	
	$force = ($zforce == '1') ? '-fRr ' : '';

	$cmd_line = 'zfs destroy -vp '.$force.escapeshellarg($zdataset).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		$array_ret['succeeded'][$zdataset] = 0;
	else:
		$array_ret['failed'][$zdataset] = $ret;
	endif;
	
	return $array_ret;
}

#region snapshots

function createDatasetSnapshot($zdataset, $znapshot, $zrecursive) {
	$zpool = explode("/", $zdataset)[0];

	$array_ret = executeSyncZFSProgram($GLOBALS["script_dataset_create_snapshot"], $zpool, array($zdataset, $znapshot, $zrecursive));
	
	return $array_ret;
}

function rollbackDatasetSnapshot($znapshot) {
	$array_ret = buildArrayRet();

	$cmd_line = "zfs rollback -rf ".escapeshellarg($znapshot).$boutput_str;
	
	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		$array_ret['succeeded'][$znapshot] = 0;
	else:
		$array_ret['failed'][$znapshot] = $ret;
	endif;
	
	return $array_ret;
}

function renameDatasetSnapshot($zpool, $zsnapshot, $znapshot_new_name) {
	$array_ret = executeZFSProgram($GLOBALS["script_dataset_rename_snapshot"], $zpool, array($zdataset, $zsnapshot, $znapshot_new_name));
	
	return $array_ret;
}

function sendDatasetSnapshot($zpool, $znapshot, $zoptions) {
	// TODO
	return $array_ret;
}

function receiveDatasetSnapshot($zpool, $znapshot, $zoptions) {
	// TODO
	return $array_ret;
}

function holdDatasetSnapshot($znapshot) {
	$array_ret = buildArrayRet();
	
	$cmd_line = "zfs hold zfsmaster ".escapeshellarg($znapshot).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		$array_ret['succeeded'][$znapshot] = 0;
	else:
		$array_ret['failed'][$znapshot] = $ret;
	endif;
	
	return $array_ret;
}

function releaseDatasetSnapshot($znapshot) {
	$array_ret = buildArrayRet();

	$cmd_line = "zfs release zfsmaster ".escapeshellarg($znapshot).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		$array_ret['succeeded'][$znapshot] = 0;
	else:
		$array_ret['failed'][$znapshot] = $ret;
	endif;
	
	return $array_ret;
}

function cloneDatasetSnapshot($znapshot, $zclone) {
	$array_ret = buildArrayRet();

	$cmd_line = "zfs clone ".escapeshellarg($znapshot)." ".escapeshellarg($zclone).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		$array_ret['succeeded'][$znapshot] = 0;
	else:
		$array_ret['failed'][$znapshot] = $ret;
	endif;
	
	return $array_ret;
}

function deleteDatasetSnapshot($znapshot, $destroy_all) {
	$zpool = explode("/", $znapshot)[0];

	$array_ret = executeSyncZFSProgram($GLOBALS["script_dataset_destroy_snapshot"], $zpool, array($znapshot, $destroy_all));

	return $array_ret;
}

#endregion snapshots

#endregion datasets


?>