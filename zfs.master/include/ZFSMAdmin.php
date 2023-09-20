<?php

$plugin = "zfs.master";
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

require_once $docroot."/webGui/include/Helpers.php";
require_once $docroot."/plugins/".$plugin."/include/ZFSMBase.php";
require_once $docroot."/plugins/".$plugin."/include/ZFSMError.php";
require_once $docroot."/plugins/".$plugin."/include/ZFSMHelpers.php";
require_once $docroot."/plugins/".$plugin."/backend/ZFSMOperations.php";

$zfsm_cfg = loadConfig(parse_plugin_cfg($plugin, true));

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

function resolveAnswerCodes($answer) {
	foreach($answer['succeeded'] as $key => $value):
		$answer['succeeded'][$key] = resolve_error($value);
	endforeach;

	foreach($answer['failed'] as $key => $value):
		$answer['failed'][$key] = resolve_error($value);
	endforeach;

	return $answer;
}

switch ($_POST['cmd']) {
	case 'refreshdata':
		file_put_contents("/tmp/zfsm_reload", "");
		break;
	case 'createdataset':
		$zfs_cparams = cleanZFSCreateDatasetParams($_POST['data']);

		$passphrase = $zfs_cparams['passphrase'] ?? "";
		unset($zfs_cparams['passphrase']);

		$cmd_line = createZFSCreateDatasetCMDLine($zfs_cparams).$boutput_str;

		if ($zfs_cparams['encryption'] == 'on'):
			$cmd_line = "echo ".escapeshellarg($passphrase)." | echo ".escapeshellarg($passphrase)." | ".$cmd_line;
		endif;

		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify( "ZFS Create", "Creation of dataset ".$zfs_cparams['zpool']."/".$zfs_cparams['name']." successful", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Create", "Creation of dataset ".$zfs_cparams['zpool']."/".$zfs_cparams['name']." failed, return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
			
		# chgrp users <mountpoint>
		# chown nobody <mountpoint>

		break;
	case 'updatedataset':
		$cmd_line = createZFSUpdateDatasetCMDLine($_POST['data']).$boutput_str;

		$ret = execCommand($cmd_line, $exec_result);

		if ($ret == 0):
			zfsnotify( "ZFS Update", "Dataset update successful", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Update", "Dataset update fail, return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;

		$cmd_line = createZFSInheritDatasetCMDLine($_POST['data']).$boutput_str;

		if ($cmd_line == '' || $ret != 0):
			break;
		endif;

		$ret = execCommand($cmd_line, $exec_result);

		if ($ret != 0):
			zfsnotify( "ZFS Update", "Dataset update partially failed, return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;

		break;
	case 'renamedataset':
		$force = ($_POST['force'] == '1') ? '-f ' : '';
		$cmd_line = 'zfs rename '.$force.escapeshellarg($_POST['data'])." ".escapeshellarg($_POST["newname"]).$boutput_str;

		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify( "ZFS Rename ", "Dataset ".$_POST['data']." renamed successfully to ".$_POST['newname'], $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Rename", "Unable to rename dataset ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;

		break;
	case 'destroydataset':
		$force = ($_POST['force'] == '1') ? '-fRr ' : '';
		$cmd_line = 'zfs destroy -vp '.$force.escapeshellarg($_POST['data']).$boutput_str;
		
		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify( "ZFS Destroy ", "Dataset ".$_POST['data']." destroyed successfully", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Destroy", "Unable to destoy dataset ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
		break;
	case 'lockdataset':
		$cmd_line = "zfs umount -f ".escapeshellarg($_POST['data']).$boutput_str;

		$ret = execCommand($cmd_line, $exec_result);

		if ($ret != 0):
			zfsnotify( "ZFS Umount", "Unable to unmount dataset, return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
			break;
		endif;

		$cmd_line = "zfs unload-key -r ".escapeshellarg($_POST['data']).$boutput_str;
		$ret = execCommand($cmd_line, $exec_result);

		if ($ret == 0):
			zfsnotify( "ZFS Dataset Lock", "Dataset ".$_POST['data']." Locked successfully", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Dataset Lock", "Unable to unload the encryption key ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;

		break;
	case 'unlockdataset':
		$cmd_line = "echo ".escapeshellarg($_POST['passphrase'])."| zfs mount -l ".escapeshellarg($_POST['data']).$boutput_str;

		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify( "ZFS Dataset Unlock", "Dataset ".$_POST['data']." Unlocked successfully", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Dataset Unlock", "Unable to Unlock dataset ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
		
		break;
	case 'promotedataset':
		$cmd_line = 'zfs promote '.escapeshellarg($_POST['data']).$boutput_str;
		
		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify( "ZFS Promote ", "Dataset ".$_POST['data']." promoted successfully", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Promote", "Unable to promote dataset ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
		break;
	case 'rollbacksnapshot':
		$cmd_line = "zfs rollback -rf ".escapeshellarg($_POST['data']).$boutput_str;

		$ret = execCommand($cmd_line, $exec_result);

		if ($ret == 0):
			zfsnotify( "ZFS Rollback ", "Snapshot ".$_POST['data']." restored successfully", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Rollback", "Unable to rollback to snapshot ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
		break;
	case 'holdsnapshot':
		$cmd_line = "zfs hold zfsmaster ".escapeshellarg($_POST['data']).$boutput_str;

		$ret = execCommand($cmd_line, $exec_result);

		if ($ret == 0):
			zfsnotify( "ZFS Hold", "Snapshot ".$_POST['data']." reference added successfully", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Hold", "Unable to add reference to snapshot ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
		break;
	case 'releasesnapshot':
		$cmd_line = "zfs release zfsmaster ".escapeshellarg($_POST['data']).$boutput_str;

		$ret = execCommand($cmd_line, $exec_result);

		if ($ret == 0):
			zfsnotify( "ZFS Release", "Snapshot ".$_POST['data']." reference removed successfully", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Release", "Unable to remove reference from snapshot ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
		break;
	case 'clonesnapshot':
		$cmd_line = "zfs clone ".escapeshellarg($_POST['data'])." ".escapeshellarg($_POST['destination']).$boutput_str;

		$ret = execCommand($cmd_line, $exec_result);

		if ($ret == 0):
			zfsnotify( "ZFS Clone", "Snapshot ".$_POST['data']." cloned successfully", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Clone", "Unable to clone snapshot ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
		break;
	case 'destroysnapshot':
		$cmd_line = "zfs destroy -r ".escapeshellarg($_POST['data']).$boutput_str;

		$ret = execCommand($cmd_line, $exec_result);

		if ($ret == 0):
			zfsnotify( "ZFS Destroy", "Snapshot ".$_POST['data']." destroyed successfully", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify( "ZFS Destroy", "Unable to destroy snapshot ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
		break;
	case 'snapshotdataset':
		$snapshot = $zfsm_cfg['snap_prefix'].date($zfsm_cfg['snap_pattern']);

		$ret = createDatasetSnapshot( $_POST['zdataset'], $snapshot, $_POST['recursive']);

		if (count($ret['succeeded']) > 0):
			zfsnotify( "ZFS Snapshot", "Snapshot created successfully for:<br>".implodeWithKeys("<br>", $ret['succeeded']), $err,"normal");
		endif;

		if (count($ret['failed']) > 0):
			zfsnotify( "ZFS Snapshot", "Unable to create snapshot for:<br>".implodeWithKeys("<br>", $ret['failed']), $err,"normal");
		endif;

		$ret = resolveAnswerCodes($ret);

		echo json_encode($ret);
		break;
	default:
		echo 'unknown command';
		break;
}

?>
