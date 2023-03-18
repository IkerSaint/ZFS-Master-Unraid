<?php
$plugin = "zfs.master";
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
$urlzmadmin = "/plugins/".$plugin."/include/ZFSMAdmin.php";

require_once "$docroot/webGui/include/Helpers.php";
require_once "$docroot/plugins/$plugin/include/ZFSMBase.php";
require_once "$docroot/plugins/$plugin/include/ZFSMHelpers.php";

$csrf_token = $_GET['csrf_token'];

$zpool = $_GET['zpool'];
$zdataset_name = $_GET['zdataset'];
$zdataset = getDatasetProperties($zpool, $zdataset_name);
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Security-Policy" content="block-all-mixed-content">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="same-origin">


<style type="text/css">	
	.zfsm_dialog {
		width: 90%;
		height: 90%;
		margin: auto;
		text-align: center;
	}

	.zfs_table {
		display: table;
		width: 90%;
		border: 1px solid #ccc;
		max-height: 360px;
		overflow: auto;
		margin: 2%;
	}

	.zfs_table tr>td{
		width:auto!important;
		white-space: normal!important;
	}

	.zfs_update_btn {
		text-align: center !important;
  	}

	#zfs_dataset.zfs_table thead tr td{
		text-align: center !important ;
	}

	#zfs_dataset.zfs_table tbody tr td{
		padding-left: 0;
		text-align: center;
		white-space: nowrap;
	}

	#zfs_dataset.zfs_table tbody tr td:first-child{
		padding-left: 12px;
		white-space: normal;
		word-break: break-all; 
	}

	#zfs_dataset.zfs_table tbody tr td:last-child{
		display: table-cell;
		vertical-align: middle;
		padding: 0 10px;
	}

	#zfs_dataset.zfs_table tbody tr td:last-child span:first-child{
		margin-left: 0;
	}

	#zfs_dataset.zfs_table tbody tr td:last-child span{
		margin: 0 5px;
		float: none;
		padding: 0;
	}

	#zfs_dataset.zfs_table tbody tr td:last-child span:last-child{
		margin-right: 0;
	}

	#zfs_dataset thead tr>td+td+td+td, #zfs_dataset thead tr>td+td+td, #zfs_dataset thead tr>td{
		padding: 0;
		text-align: center;
	}

	.switch {
		position: relative;
		display: inline-block;
		width: 28px;
		height: 16px;
	}

	.switch input {
		opacity: 0;
		width: 0;
		height: 0;
	}

	.slider {
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #ccc;
		-webkit-transition: .4s;
		transition: .4s;
		border-radius: 16px;
	}

	.slider:before {
		position: absolute;
		content: "";
		height: 12px;
		width: 12px;
		left: 2px;
		bottom: 2px;
		background-color: white;
		-webkit-transition: .4s;
		transition: .4s;
		border-radius: 50%;
	}

	input:checked + .slider {
		background-color: #2196F3;
	}

	input:focus + .slider {
		box-shadow: 0 0 1px #2196F3;
	}

	input:checked + .slider:before {
		-webkit-transform: translateX(12px);
		-ms-transform: translateX(12px);
		transform: translateX(12px);
	}

	.slider.round {
		border-radius: 16px;
	}
</style>

<script src="<?autov('/webGui/javascript/dynamix.js')?>"></script>
<link type="text/css" rel="stylesheet" href="<?autov('/webGui/styles/default-fonts.css');?>">
<link type="text/css" rel="stylesheet" href="<?autov('/webGui/styles/default-popup.css');?>">

<script src="<?autov('/webGui/javascript/jquery.filetree.js')?>"></script>

<script type="text/javascript" src="<?autov('/plugins/zfs.master/assets/sweetalert2.all.min.js');?>"></script>
<link type="text/css" rel="stylesheet" href="<?autov('/plugins/zfs.master/assets/sweetalert2.min.css');?>">

</head>

<body>
	<div id="editdataset-form-div" class="zfsm_dialog">
	<table id="zfs_dataset" class="zfs_table wide">
	<thead>
		<tr>
		<td>Property</td>
		<td>Value</td>
		</tr>
	</thead>
	<tbody id="zpools">
	<?
		$creationdate = new DateTime();
		$creationdate->setTimestamp($zdataset['creation']);

		echo '<tr>';
		echo '<td colspan="2">Read Only</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Creation</td>';
		echo '<td>'.$creationdate->format('Y-m-d H:i:s').'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Used</td>';
		echo '<td>'.fromBytesToString($zdataset["used"]).'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Available</td>';
		echo '<td>'.fromBytesToString($zdataset["available"]).'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Referenced</td>';
		echo '<td>'.fromBytesToString($zdataset["referenced"]).'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Encryption</td>';
		echo '<td>'.$zdataset["encryption"].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Key Status</td>';
		echo '<td>'.$zdataset["keystatus"].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Compress Ratio</td>';
		echo '<td>'.($zdataset["compressratio"]/100).'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Used by Snapshots</td>';
		echo '<td>'.fromBytesToString($zdataset["usedbysnapshots"]).'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Origin</td>';
		echo '<td>'.$zdataset['origin'] ?? ''.'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="2">Editable</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Mount Point</td>';
		echo '<td>'.$zdataset["mountpoint"].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Compression</td>';
		echo '<td>'.$zdataset["compression"].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Quota</td>';
		echo '<td>'.fromBytesToString($zdataset["quota"]).'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Record Size</td>';
		echo '<td>'.fromBytesToString($zdataset["recordsize"]).'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Access Time (atime)</td>';
		echo '<td>'.$zdataset["atime"].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Extended Attributes (xattr)</td>';
		echo '<td><label class="switch"><input type="checkbox" id="xattr" '.($zdataset["xattr"] == "off" ? 'unchecked' : 'checked').'';
		echo '><span class="slider round"></span></label>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Primary Cache</td>';
		echo '<td>'.$zdataset["primarycache"].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Read Only</td>';
		echo '<td><label class="switch"><input type="checkbox" id="readonly" '.($zdataset["readonly"] == "off" ? 'unchecked' : 'checked').'';
		echo '><span class="slider round"></span></label>';
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Case Sentitivity</td>';
		echo '<td>'.$zdataset["casesensitivity"].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Sync</td>';
		echo '<td>'.$zdataset["sync"].'</td>';
		echo '</tr>';
	?>
	</tbody>
	</table>
	<button id="update-dataset" class="zfs_update_btn" type="button">Update Dataset</button>
	</div>
</body>
</html>

<script>

</script>