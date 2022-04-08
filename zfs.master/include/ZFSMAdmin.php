<?php
$plugin = "zfs.master";
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

require_once "$docroot/webGui/include/Helpers.php";
require_once "ZFSMConstants.php";
require_once "ZFSMHelpers.php";

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

// String literals
$boutput_str = " 2>&1";
$cmdoutput_str = "CMD output: ";

switch ($_POST['cmd']) {
	case 'scrubpool':
		$cmd_line = 'zpool scrub '.escapeshellarg($_POST['data']).$boutput_str;
		
		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify($docroot, "ZPool Scrub", "Scrub of pool ".$_POST['data']." Started", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify($docroot, "ZPool Scrub", "Scrub of pool ".$_POST['data']." failed to start, return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
		
		break;
	case 'createdataset':
		$permissions = isset($_POST['data']['permissions']) ? $_POST['data']['permissions'] : '';

		unset($_POST['data']['permissions']);

		$zfs_cparams = cleanZFSCreateDatasetParams($_POST['data']);
		$cmd_line = createZFSCreateDatasetCMDLine($zfs_cparams).$boutput_str;
		
		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify($docroot, "ZFS Create", "Creation of dataset ".$zfs_cparams['zpool']."/".$zfs_cparams['name']." successful", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify($docroot, "ZFS Create", "Creation of dataset ".$zfs_cparams['zpool']."/".$zfs_cparams['name']." failed, return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;

		if ($permissions == '' || $ret != 0):
			break;
		endif;
			
		$cmd_line = 'chmod '.$permissions.' /'.$zfs_cparams['zpool'].'/'.$zfs_cparams['name'];
		$ret = execCommand($cmd_line, $exec_result);

		if ($ret != 0):
			zfsnotify($docroot, "ZFS Create", "Unable to set permissions for dataset ".$zfs_cparams['zpool']."/".$zfs_cparams['name'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;

		break;
	case 'destroydataset':
		$force = ($_POST['force'] == '1') ? '-fRr ' : '';
		$cmd_line = 'zfs destroy -vp '.$force.escapeshellarg($_POST['data']).$boutput_str;
		
		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify($docroot, "ZFS Destroy ", "Dataset ".$_POST['data']." destroyed successfully", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify($docroot, "ZFS Destroy", "Unable to destoy dataset ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
		break;
	case 'exportpool':
		$force = ($_POST['force'] == '1') ? '-f ' : '';
		$cmd_line = 'zfs export '.$force.escapeshellarg($_POST['data']).$boutput_str;
		
		$ret = execCommand($cmd_line, $exec_result);
		
		if ($ret == 0):
			zfsnotify($docroot, "ZPool Export ", "Pool ".$_POST['data']." exported successfully", $cmdoutput_str.$exec_result."","normal");
			echo 'Ok';
		else:
			zfsnotify($docroot, "ZPool Export", "Unable to export pool ".$_POST['data'].", return code (".$ret.")", $cmdoutput_str.$exec_result."","warning");
			echo $exec_result;
		endif;
		break;
	default:
		echo 'unknown command';
		break;
}

?>
