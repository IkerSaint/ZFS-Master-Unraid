<?php

define('__ROOT__', dirname(dirname(__FILE__)));

require_once __ROOT__."/include/ZFSMBase.php";
require_once __ROOT__."/include/ZFSMError.php";
require_once __ROOT__."/include/ZFSMHelpers.php";
require_once "/usr/local/emhttp/webGui/include/Helpers.php";
require_once "$docroot/webGui/include/publish.php";

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

function listDirectories($path, $childs) {
	$remove = array($path."/..", $path."/.");

	foreach ($childs as $child):
		$remove[] = $child['mountpoint'];
	endforeach;

	$dirs = glob($path."/{,.}*" , GLOB_ONLYDIR | GLOB_BRACE);

	if (!isset($dirs) || !is_array($dirs)):
    	return array();
	endif;
	
	$array_ret = array_diff($dirs, $remove);

	return $array_ret;
}

function saveConfig($array) {
    $content = '';

    foreach ($array as $key => $elem) {
        if (is_array($elem)) {
            $content .= "[" . $key . "]\n";
            foreach ($elem as $key2 => $elem2) {
                if (is_array($elem2)) {
                    foreach ($elem2 as $value) {
                        $content .= $key2 . "[] = \"" . $value . "\"\n";
                    }
                } else {
                    $content .= $key2 . " = " . (empty($elem2) ? '' : "\"" . $elem2 . "\"") . "\n";
                }
            }
        } else {
            if (is_array($elem)) {
                foreach ($elem as $value) {
                    $content .= $key . "[] = \"" . $value . "\"\n";
                }
            } else {
                $content .= $key . " = " . (empty($elem) ? '' : "\"" . $elem . "\"") . "\n";
            }
        }
    }

    if (!$handle = fopen("/boot/config/plugins/zfs.master/zfs.master.cfg", 'w')) {
        return false;
    }

    if (!fwrite($handle, $content)) {
        fclose($handle);
        return false;
    }

    fclose($handle);
    return true;
}

function addToDirectoryListing($zdataset) {
	$array_ret = buildArrayRet();

	$config = parse_plugin_cfg( 'zfs.master', true);

	if (str_contains($config['general']['directory_listing'], $zdataset)):
		$array_ret['failed'][$zdataset] = ZFSM_ERR_ALREADY_SET_IN_CONFIG;
		return $array_ret;
	endif;

	if (!isset($config['general']['directory_listing']) || $config['general']['directory_listing'] == ""):
		$config['general']['directory_listing'] = $zdataset;
	else:
		$config['general']['directory_listing'] = $config['general']['directory_listing']."\r\n".$zdataset;
	endif;

	$ret = saveConfig($config);

	if ($ret == true):
		$array_ret['succeeded'][$zdataset] = 0;
	else:
		$array_ret['failed'][$zdataset] = ZFSM_ERR_UNABLE_TO_SAVE;
	endif;

	return $array_ret;
}

function removeFromDirectoryListing($zdataset) {
	$array_ret = buildArrayRet();

	$config = parse_plugin_cfg( 'zfs.master', true);

	if (!isset($config['general']['directory_listing'])):
		$array_ret['failed'][$zdataset] = ZFSM_ERR_NOT_IN_CONFIG;
		return $array_ret;
	endif;

	$tmp_array = preg_split('/\r\n|\r|\n/', $config['general']['directory_listing']);

	if (!in_array($zdataset, $tmp_array)):
		$array_ret['failed'][$zdataset] = ZFSM_ERR_NOT_IN_CONFIG;
		return $array_ret;
	endif;
	
	$key = array_search($zdataset, $tmp_array);
	unset($tmp_array[$key]);

	if (count($tmp_array)):
		$config['general']['directory_listing'] = implode(PHP_EOL, $tmp_array);
	else:
		$config['general']['directory_listing'] = "";
	endif;

	$ret = saveConfig($config);

	if ($ret == true):
		$array_ret['succeeded'][$zdataset] = 0;
	else:
		$array_ret['failed'][$zdataset] = ZFSM_ERR_UNABLE_TO_SAVE;
	endif;

	return $array_ret;
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

function getZFSPoolDatasets($zpool, $zexc_pattern, $directory_listing = array()) {
	$result = executeZFSProgram($GLOBALS["script_pool_get_datasets"], $zpool, array($zpool, $zexc_pattern));

	$result['directories'] = listDirectories($result['mountpoint'], $result['child']);

	if (count($directory_listing)):
		
		$result['child'] = getDatasetDirectories($result['child'], $directory_listing);
	endif;
	
	return sortDatasetArray($result);
}

function getZFSPoolDatasetsAndSnapshots($zpool, $zexc_pattern, $directory_listing = array()) {
	$result = executeZFSProgram($GLOBALS["script_pool_get_datasets_snapshots"], $zpool, array($zpool, $zexc_pattern));

	$result['directories'] = listDirectories($result['mountpoint'], $result['child']);
	
	if (count($directory_listing)):
		$result['child'] = getDatasetDirectories($result['child'], $directory_listing);
	endif;
	
	return sortDatasetArray($result);
}

#endregion zpools

#region datasets

function getDatasetDirectories($dataset_tree, $directory_listing) {
	foreach ($dataset_tree as &$dataset):
		if (in_array($dataset['name'], $directory_listing)):
			$dataset['directories'] = listDirectories($dataset['mountpoint'], $dataset['child']);
		endif;

		if (isset($dataset['child']) && count($dataset['child'])):
			$dataset['child'] = getDatasetDirectories($dataset['child'], $directory_listing);
		endif;
	endforeach;

	return $dataset_tree;
}

function getDatasetProperty($zpool, $zdataset, $zproperty) {
	$array_ret = executeZFSProgram($GLOBALS["script_dataset_get_property"], $zpool, array($zdataset, $zproperty));

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
	
	if (count($zoptions)):
		$cmd_line .= " -o ".implodeWithKeys(" -o ", $zoptions, "=");
	endif;

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

#region directories

function convertDirectory($directory, $zpool) {
	$array_ret = buildArrayRet();
	$directory_new_name =  $directory."_tmp_".date("Ymdhis");

	$mv_dir = moveDirectory($directory, $directory_new_name);

	if (count($mv_dir['succeeded']) <= 0 ):
		return $mv_dir;
	endif;

	$pool_pos = stripos($directory, $zpool);
	$dataset_name = substr($directory, $pool_pos);

	$dataset = createDataset( $dataset_name, array());

	if (count($dataset['succeeded']) <= 0 ):
		moveDirectory( $directory_new_name, $directory);
		return $dataset;
	endif;

	$mountpoint = getDatasetProperty($zpool, $dataset_name, 'mountpoint');

	$descriptorspec = array(
		0 => array("pipe", "r"),
		1 => array("pipe", "w"),
		2 => array("pipe", "w")
	);

	$rsync_cmd_line = "rsync -ra --stats --info=progress2 ".$directory_new_name."/ ".$mountpoint."/";

	$process = proc_open( $rsync_cmd_line, $descriptorspec, $pipes);

	if (is_resource($process)):
		publish('zfs_master', '{"op":"start_directory_copy"}');

		do {	
			$line = fread($pipes[1], 2048);
			
			if ($line):
				$message = array();
				$message['data'] = $line;
				$message['op'] = "directory_copy";
				
				publish('zfs_master', json_encode($message));
			endif;

			$status = proc_get_status($process);
			
			sleep(0.1);
		} while ($status['running']);
	
		fclose($pipes[1]);

		publish('zfs_master', '{"op":"stop_directory_copy"}');

		proc_close($process);

		sleep(1);
	
		$array_ret['succeeded'][$directory] = $status["exitcode"];
	else:
		$array_ret['failed'][$directory] = ZFSM_ERR_UNABLE_TO_CREATE_PROC;
	endif;

	return $array_ret;
}

function moveDirectory($directory, $directory_new_name) {
	$array_ret = buildArrayRet();

	$cmd_line = "mv ".$force.escapeshellarg($directory)." ".escapeshellarg($directory_new_name).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);
	
	if ($ret == 0):
		$array_ret['succeeded'][$directory_new_name] = 0;
	else:
		$array_ret['failed'][$directory_new_name] = $ret;
	endif;
	
	return $array_ret;
}

function deleteDirectory($directory, $zforce) {
	$array_ret = buildArrayRet();
	
	$force = ($zforce == '1') ? 'f ' : ' ';

	$cmd_line = 'rm -r'.$force.escapeshellarg($directory).$boutput_str;

	$ret = execCommand($cmd_line, $exec_result);

	if ($ret == 0):
		$array_ret['succeeded'][$directory] = 0;
	else:
		$array_ret['failed'][$directory] = $ret;
	endif;
	
	return $array_ret;
}

#endregion directories

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