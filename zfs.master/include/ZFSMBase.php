<?php
	$plugin = "zfs.master";
	$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
	$plugin_scripts = $docroot."/plugins/".$plugin."/scripts/";
	$plugin_include = $docroot."/plugins/".$plugin."/include/";
	$plugin_session_file = "/tmp/".$plugin."-session.data";
	
	$script_get_pool_data = $plugin_scripts."zfs_get_pool_data.lua";
	
	$urlzmadmin = "/plugins/".$plugin."/include/ZFSMAdmin.php";
	$urlcreatedataset = "/plugins/".$plugin."/include/ZFSMCreateDataset.php";
	$urladmindatasetsnaps = "/plugins/".$plugin."/include/ZFSMAdminDatasetSnaps.php";
	
	$statusColor = array(
		'ONLINE' => 'green',
		'DEGRADED' => 'yellow',
		'FAULTED' => 'red',
		'OFFLINE' => 'blue',
		'UNAVAIL' => 'grey',
		'REMOVED' => 'grey'
	);
	  
	$statusMsg = array(
		'ONLINE' => 'The pool is in normal working order',
		'DEGRADED' => 'One or more devices with problems. Functioning in a degraded state',
		'FAULTED' => 'One or more devices could not be used. Pool unable to continue functioning',
		'OFFLINE' => 'One or more devices has been explicitly taken offline by the administrator ',
		'UNAVAIL' => 'One or more devices or virtual devices cannot be opened',
		'REMOVED' => 'One or more devices were physically removed while the system was running'
	);

	$boutput_str = " 2>&1";
	$cmdoutput_str = "CMD output: ";
?>