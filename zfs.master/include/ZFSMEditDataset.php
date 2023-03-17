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

	.zfs_snap_table {
		display: table;
		width: 90%;
		border: 1px solid #ccc;
		max-height: 360px;
		overflow: auto;
		margin: 2%;
	}

	.zfs_status_box {
		width: 80%;
		border: 1px solid #ccc;
		margin-left: 10%;
	}

	.zfs_table tr>td{
		width:auto!important;
		white-space: normal!important;
	}

	.zfs_update_btn {
		text-align: center !important;
  	}

	input[type=checkbox]{
		height: 0;
		width: 0;
		visibility: hidden;
	}

	label {
		cursor: pointer;
		text-indent: -99px;
		width: 30px;
		height: 15px;
		background: grey;
		display: block;
		border-radius: 3px;
		position: relative;
	}

	label:after {
		content: '';
		position: absolute;
		top: 1px;
		left: 1px;
		width: 12px;
		height: 12px;
		background: #fff;
		border-radius: 7px;
		transition: 0.3s;
	}

	input:checked + label {
		background: #bada55;
	}

	input:checked + label:after {
		left: calc(100% - 1px);
		transform: translateX(-100%);
	}

	label:active:after {
		width: 130px;
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
	<table id="zfs_master" class="zfs_snap_table disk_status wide">
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
		echo '<td><input type="checkbox" id="readonly" '.($zdataset["xattr"] == "off" ? 'unchecked' : 'checked').'';
		echo '/><label for="switch"></label>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Primary Cache</td>';
		echo '<td>'.$zdataset["primarycache"].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Read Only</td>';
		echo '<td><input type="checkbox" id="readonly" '.($zdataset["readonly"] == "off" ? 'unchecked' : 'checked').'';
		echo '/><label for="switch"></label>';
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