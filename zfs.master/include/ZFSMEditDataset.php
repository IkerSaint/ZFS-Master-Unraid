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

	.zfsm-input {
		background-color: #fff;
		width: auto;
		border: 1px solid rgba(0,0,0,.10);
		display: inline-block;
		padding: 2px 2px;
		border-radius: 2px;
  	}

	.switch {
		position: relative;
		display: inline-block;
		width: 32px;
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

<script type="text/javascript">
window.onload = function() {
    if (parent) {
        var oHead = document.getElementsByTagName("head")[0];
        var arrStyleSheets = parent.document.getElementsByTagName("style");
        for (var i = 0; i < arrStyleSheets.length; i++)
            oHead.appendChild(arrStyleSheets[i].cloneNode(true));

		var arrStyleSheets = parent.document.getElementsByTagName("link");

        for (var i = 0; i < arrStyleSheets.length; i++)
            oHead.appendChild(arrStyleSheets[i].cloneNode(true));
    }
}
</script>

<script src="<?autov('/webGui/javascript/dynamix.js')?>"></script>
<link type="text/css" rel="stylesheet" href="<?autov('/webGui/styles/default-fonts.css');?>">
<link type="text/css" rel="stylesheet" href="<?autov('/webGui/styles/default-popup.css');?>">

<script src="<?autov('/webGui/javascript/jquery.filetree.js')?>"></script>

<script type="text/javascript" src="<?autov('/plugins/zfs.master/assets/sweetalert2.all.min.js');?>"></script>
<link type="text/css" rel="stylesheet" href="<?autov('/plugins/zfs.master/assets/sweetalert2.min.css');?>">

</head>

<body>
	<div id="editdataset-form-div" class="zfsm_dialog">
	<table id="zfs_dataset" class="zfs_table disk_status wide">
	<thead>
		<tr>
		<td>Property</td>
		<td>Value</td>
		</tr>
	</thead>
	<tbody id="zpools">
	<?
		echo '<tr>';
		echo '<td>Mount Point</td>';
		echo '<td><input id="mountpoint" name="mountpoint" class="zfsm-input zfsm-w0 " value="'.$zdataset["mountpoint"].'""></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Compression</td>';
		echo '<td><select id="compression" name="compression" class="zfsm-input">
		<option value="inherit">Inherit</option>
		<option value="off">Off</option>
		<option value="lz4">lz4</option>
		<option value="gzip">gzip</option>
		<option value="zstd">zstd</option>
		</select></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Quota</td>';
		echo '<td><input id="quota" name="quota" class="zfsm-input zfsm-w10" maxlength="7" value="'.fromBytesToString($zdataset["quota"]).'"></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Record Size</td>';
		echo '<td><select id="recordsize" name="recordsize" class="zfsm-input">
		<option value="inherit" selected>Inherit</option>
		<option value="512">512</option>
		<option value="4 KB">4 KB</option>
		<option value="8 KB">8 KB</option>
		<option value="16 KB">16 KB</option>
		<option value="64 KB">64 KB</option>
		<option value="128 KB">128 KB</option>
		<option value="1 MB">1 MB</option>
		</select></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Access Time (atime)</td>';
		echo '<td><label class="switch"><input type="checkbox" id="atime" '.($zdataset["atime"] == "off" ? 'unchecked' : 'checked').'';
		echo '><span class="slider round"></span></label>';
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Extended Attributes (xattr)</td>';
		echo '<td><label class="switch"><input type="checkbox" id="xattr" '.($zdataset["xattr"] == "off" ? 'unchecked' : 'checked').'';
		echo '><span class="slider round"></span></label>';
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Primary Cache</td>';
		echo '<td><select id="primarycache" name="primarycache" class="zfsm-input">
		<option value="inherit" selected>Inherit</option>
		<option value="all">All</option>
		<option value="metadata">Metadata</option>
		<option value="none">None</option>
		</select></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Read Only</td>';
		echo '<td><label class="switch"><input type="checkbox" id="readonly" '.($zdataset["readonly"] == "off" ? 'unchecked' : 'checked').'';
		echo '><span class="slider round"></span></label>';
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Case Sentitivity</td>';
		echo '<td><select id="casesensitivity" name="casesensitivity" class="zfsm-input">
		<option value="sensitive">Sensitive (Default)</option>
		<option value="insensitive">Insensitive</option>
		<option value="mixed">Mixed</option>
		</select></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td>Sync</td>';
		echo '<td><select id="sync" name="sync" class="zfsm-input">
		<option value="standard">Standard (Default)</option>
		<option value="always">Always</option>
		<option value="disabled">Disabled</option>
		</select></td>';
		echo '</tr>';
	?>
	</tbody>
	</table>
	<button id="update-dataset" class="zfs_update_btn" type="button" onclick="updateDataset()">Update Dataset</button>
	</div>
</body>
</html>

<script>
  $(document).ready(function() {
	  setDefaults();
  });

  function setDefaults() {
	$("#compression").val('<?=$zdataset["compression"]?>');
	$("#recordsize").val('<?=fromBytesToString($zdataset["recordsize"])?>');
	$("#casesensitivity").val('<?=$zdataset["casesensitivity"]?>');
	$("#sync").val('<?=$zdataset["sync"]?>');
  }

  function updateDataset() {
	var inputs = {};

	$(":input").each(function(){
		inputs[$(this).id]=$(this).value;
    });

	console.log(inputs);
		
	/*$.post('<?=$urlzmadmin?>',{cmd: 'createdataset', 'data': formData, 'csrf_token': '<?=$csrf_token?>'}, function(data){
		if (data == 'Ok') {
			top.Swal2.fire({
				title: 'Success!',
				icon:'success',
				html: 'Dataset '+formData['zpool']+'/'+formData['name']+' created'
			});
		} else {
			top.Swal2.fire({
				title: 'Error!',
				icon:'error',
				html: 'Unable to create dataset '+formData['zpool']+'/'+formData['name']+'<br>Output: '+data
			}); 
		}
		top.Shadowbox.close();
	});*/
  }

</script>