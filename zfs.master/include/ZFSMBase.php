<?php
	$plugin = "zfs.master";
	$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
	$plugin_config = "/boot/config/plugins/".$plugin."/".$plugin.".cfg";
	$plugin_scripts = $docroot."/plugins/".$plugin."/scripts/";
	$plugin_include = $docroot."/plugins/".$plugin."/include/";
	$plugin_session_file = "/tmp/".$plugin."-session.data";
	
	$script_pool_get_datasets = $plugin_scripts."zfs_pool_get_datasets.lua";
	$script_pool_get_datasets_ext = $plugin_scripts."zfs_pool_get_datasets_ext.lua";
	$script_pool_get_datasets_snapshots = $plugin_scripts."zfs_pool_get_datasets_snapshots.lua";
	$script_pool_get_datasets_snapshots_ext = $plugin_scripts."zfs_pool_get_datasets_snapshots_ext.lua";

	$script_dataset_get_property = $plugin_scripts."zfs_dataset_get_property.lua";
	$script_dataset_get_properties = $plugin_scripts."zfs_dataset_get_properties.lua";
	$script_dataset_get_properties_ext = $plugin_scripts."zfs_dataset_get_properties_ext.lua";
	$script_dataset_get_snapshots = $plugin_scripts."zfs_dataset_get_snapshots.lua";

	//$script_dataset_rollback_snapshot = $plugin_scripts."zfs_dataset_rollback_snapshot.lua";
	$script_dataset_rename_snapshot = $plugin_scripts."zfs_dataset_rename_snapshot.lua";
	$script_dataset_promote = $plugin_scripts."zfs_dataset_promote.lua";
	
	
	$script_dataset_destroy = $plugin_scripts."zfs_dataset_destroy.lua";
	$script_dataset_destroy_snapshot = $plugin_scripts."zfs_dataset_destroy_snapshot.lua";
	$script_dataset_create_snapshot = $plugin_scripts."zfs_dataset_create_snapshot.lua";
		
	$urlzmadmin = "/plugins/".$plugin."/backend/ZFSMAdmin.php";
	$urlcreatedataset = "/plugins/".$plugin."/frontend/ZFSMCreateDataset.php";
	$urladmindatasetsnaps = "/plugins/".$plugin."/frontend/ZFSMAdminDatasetSnaps.php";

	$boutput_str = " 2>&1";
?>