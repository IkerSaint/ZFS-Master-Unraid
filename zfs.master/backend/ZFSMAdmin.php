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

function returnAnswer($ret, $title, $success_text, $failed_text, $refresh) {
	if ($refresh):
		refreshData();
	endif;

	$answer = resolveAnswerCodes($ret);

	if (count($answer['succeeded']) > 0):
		zfsnotify( $title, $success_text." for:<br>".implodeWithKeys("<br>", $answer['succeeded']), $err,"normal");
	endif;

	if (count($answer['failed']) > 0):
		zfsnotify( $title, $failed_text." for:<br>".implodeWithKeys("<br>", $answer['failed']), $err,"warning");
	endif;

	echo json_encode($answer);

	return;
}

switch ($_POST['cmd']) {
	case 'refresh':
		refreshData();
		break;
	case 'createdataset':
		$zdataset = $_POST['data']['zpool']."/".$_POST['data']['name'];
		$zfs_cparams = cleanZFSCreateDatasetParams($_POST['data']);

		$ret = createDataset( $zdataset, $zfs_cparams);

		returnAnswer($ret, "ZFS Dataset Creation", "Dataset created successfully", "Unable to create dataset", true);

		break;
	case 'editdatasetproperty':
		$array_ret = buildArrayRet();

		$ret = setDatasetProperty($_POST['zdataset'], $_POST['property'], $_POST['value']);

		if ($ret == 0):
			$array_ret['succeeded'][$_POST['property']] = 0;
		else:
			$array_ret['failed'][$key] = $ret;
		endif;

		returnAnswer($array_ret, "ZFS Dataset Edit", "Dataset edited successfully", "Unable to edit dataset", true);
		break;
	case 'getdatasetproperties':
		$ret = getAllDatasetProperties($_POST['zdataset']);

		echo json_encode($ret);

		break;
	case 'adddirectortlisting':
		$ret = addToDirectoryListing($_POST['zdataset']);

		returnAnswer($ret, "Directory Listing", "Dataset added successfully", "Unable to add dataset", false);
		break;
	case 'removedirectorylisting':
		$ret = removeFromDirectoryListing($_POST['zdataset']);

		returnAnswer($ret, "Directory Listing", "Dataset removed successfully", "Unable to remove dataset", false);
		break;
	case 'renamedataset':
		$ret = renameDataset($_POST['zdataset'], $_POST['newname'], $_POST['force']);

		returnAnswer($ret, "ZFS Dataset Rename", "Dataset renamed successfully", "Unable to rename dataset", true);

		break;
	case 'destroydataset':
		$ret = destroyDataset($_POST['zdataset'], $_POST['force']);

		returnAnswer($ret, "ZFS Dataset Destroy", "Dataset destroyed successfully", "Unable to destroy dataset", true);

		break;
	case 'lockdataset':
		$ret = lockDataset($_POST['zdataset']);
		
		returnAnswer($ret, "ZFS Dataset Lock", "Dataset Locked successfully", "Unable to Lock dataset", true);

		break;
	case 'unlockdataset':
		$ret = unlockDataset($_POST['zdataset'], $_POST['passphrase']);
		
		returnAnswer($ret, "ZFS Dataset Unlock", "Dataset Unlocked successfully", "Unable to Unlock dataset", true);
		
		break;
	case 'promotedataset':
		$ret = promoteDataset($_POST['zdataset'], 0);

		returnAnswer($ret, "ZFS Dataset Promote", "Dataset promoted successfully", "Unable to promote dataset", true);
		
		break;
	case 'renamedirectory':
		$ret = renameDirectory($_POST['directory'], $_POST['newname']);

		returnAnswer($ret, "ZFS Directory rename", "Directory renamed successfully", "Unable to rename directory", true);

		break;
	case 'deletedirectory':
		$ret = deleteDirectory($_POST['directory'], $_POST['force']);

		returnAnswer($ret, "ZFS Directory Delete", "Directory deleted successfully", "Unable to delete directory", true);

		break;
	case 'rollbacksnapshot':
		$ret = rollbackDatasetSnapshot($_POST['snapshot']);

		returnAnswer($ret, "ZFS Snapshot Rollback", "Snapshot rolled back successfully", "Unable to rollback snapshot", true);

		break;
	case 'holdsnapshot':
		$ret = holdDatasetSnapshot($_POST['snapshot']);

		returnAnswer($ret, "ZFS Snapshot Reference", "Snapshot reference added successfully", "Unable to add reference", false);

		break;
	case 'releasesnapshot':
		$ret = releaseDatasetSnapshot($_POST['snapshot']);

		returnAnswer($ret, "ZFS Snapshot Release", "Snapshot reference removed successfully", "Unable to remove reference", false);

		break;
	case 'clonesnapshot':
		$ret = cloneDatasetSnapshot($_POST['snapshot'], $_POST['clone']);

		returnAnswer($ret, "ZFS Snapshot Clone", "Snapshot cloned successfully", "Unable to clone snapshot", true);

		break;
	case 'destroysnapshot':
		$ret = destroyDataset($_POST['snapshot'], 0);

		returnAnswer($ret, "ZFS Snapshot Destroy", "Snapshot destroyed successfully", "Unable to destroy snapshot", true);

		break;
	case 'snapshotdataset':
		$snapshot = $zfsm_cfg['snap_prefix'].date($zfsm_cfg['snap_pattern']);

		$ret = createDatasetSnapshot( $_POST['zdataset'], $snapshot, $_POST['recursive']);

		returnAnswer($ret, "ZFS Snapshot Create", "Snapshot created successfully", "Unable to create snapshot", true);

		break;
	default:
		echo 'unknown command';
		break;
}

?>
