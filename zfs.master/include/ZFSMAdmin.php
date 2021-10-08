<?php
$plugin = "zfs.master";
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

require_once "$docroot/webGui/include/Helpers.php";
require_once "ZFSMConstants.php";
require_once "ZFSMHelpers.php";

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

switch ($_POST['cmd']) {
	case 'scrubpool':
		$cmd_line = 'zpool scrub '.escapeshellarg($_POST['data']).' 2>&1';
		
		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify($docroot, "Zpool Scrub", "Scrub of pool ".$_POST['data']." Started", "CMD output: ".$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify($docroot, "Zpool Scrub", "Scrub of pool ".$_POST['data']." failed to start, return code (".$ret.")", "CMD output: ".$exec_result."","warning");
			echo $exec_result;
		endif;
		
		break;
	case 'createdataset':
		$zfs_cparams = cleanZFSCreateDatasetParams($_POST['data']);
		$cmd_line = createZFSCreateDatasetCMDLine($zfs_cparams).' 2>&1';
		
		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify($docroot, "ZFS Create", "Creation of dataset ".$zfs_cparams['zpool']."/".$zfs_cparams['name']." successful", "CMD output: ".$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify($docroot, "ZFS Create", "Creation of dataset ".$zfs_cparams['zpool']."/".$zfs_cparams['name']." failed, return code (".$ret.")", "CMD output: ".$exec_result."","warning");
			echo $exec_result;
		endif;
		
		break;
	case 'destroydataset':
		$force = ($_POST['force'] == '1') ? '-fRr ' : '';
		$cmd_line = 'zfs destroy -vp '.$force.escapeshellarg($_POST['data']).' 2>&1';
		
		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify($docroot, "ZFS Destroy ", "Dataset ".$_POST['data']." destroyed successfully", "CMD output: ".$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify($docroot, "ZFS Destroy", "Unable to destoy dataset ".$_POST['data'].", return code (".$ret.")", "CMD output: ".$exec_result."","warning");
			echo $exec_result;
		endif;
		break;
	default:
		echo 'unknown command';
		break;
}

?>
