<?php

$plugin = "zfs.master";
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
$urlzmadmin = "/plugins/".$plugin."/include/ZFSMAdmin.php";

require_once "$docroot/webGui/include/Helpers.php";
require_once "$docroot/plugins/$plugin/include/ZFSMBase.php";
require_once "$docroot/plugins/$plugin/include/ZFSMHelpers.php";

$csrf_token = $_GET['csrf_token'];

$zpool = $_GET['zpool'];
$zdataset = $_GET['zdataset'];

$compressedJSON = $_COOKIE['zpool_datasets'];
$zpool_datasets = gzinflate($compressedJSON)[$zpool];
$dataset = findDatasetInArray($zdataset, $zpool_datasets);
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
#zfs_master.disk_status.zfs_snap_table thead tr td{
  text-align: center !important ;
}
#zfs_master.disk_status.zfs_snap_table thead tr td:nth-child(4){
  white-space: normal;
  padding: 0 12px;
}
#zfs_master.disk_status.zfs_snap_table tbody tr td{
  padding-left: 0;
  text-align: center;
  white-space: nowrap;
}
#zfs_master.disk_status.zfs_snap_table tbody tr td:first-child{
  padding-left: 12px;
  white-space: normal;
  word-break: break-all; 
}

#zfs_master.disk_status.zfs_snap_table tbody tr td:last-child{
  display: table-cell;
  vertical-align: middle;
  padding: 0 10px;
}
#zfs_master.disk_status.zfs_snap_table tbody tr td:last-child span:first-child{
  margin-left: 0;
}

#zfs_master.disk_status.zfs_snap_table tbody tr td:last-child span{
  margin: 0 5px;
  float: none;
  padding: 0;
}
#zfs_master.disk_status.zfs_snap_table tbody tr td:last-child span:last-child{
  margin-right: 0;
}
#zfs_master.disk_status thead tr>td+td+td+td, #zfs_master.disk_status thead tr>td+td+td, #zfs_master.disk_status thead tr>td{
  padding: 0;
  text-align: center;
}

#adminsnaps-form-div #zfs_master.disk_status tr>td{
  width: auto;
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

<style type="text/css">	
  .zfs_snap_table {
	  display: block;
	  width: 96%;
	  border: 1px solid #ccc;
	  max-height: 600px;
	  overflow: auto;
	  margin: 2%;
  }

  .zfs_table tr>td{
	  width:auto!important;
	  white-space: normal!important;
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
	<div id="adminsnaps-form-div" class="zfsm-dialog">
	<table id="zfs_master" class="zfs_snap_table disk_status wide">
	<thead>
		<tr>
		<td>Name</td>
		<td>Used</td>
		<td>Refer</td>
		<td>Defer Destroy</td>
		<td>Holds</td>
		<td>Creation Date</td>
		<td>Actions</td>
		</tr>
	</thead>
	<tbody id="zpools">
		<?
		foreach ($dataset['snapshots'] as $snap):
		echo '<tr>';
		echo '<td class="snapl-attribute-name">';
			echo '<i class="fa fa-hdd-o icon" style="color:#486dba"></i>';
			echo $snap['name'];
		echo '</td>';
		echo '<td class="snapl-attribute-used">';
			echo fromBytesToString($snap['used']);
		echo '</td>';
		echo '<td class="snapl-attribute-referenced">';
			echo fromBytesToString($snap['referenced']);
		echo '</td>';
		echo '<td class="snapl-attribute-defer_destroy">';
			echo $snap['defer_destroy'];
		echo '</td>';
		echo '<td class="snapl-attribute-userrefs">';
			echo $snap['userrefs'];
		echo '</td>';
		echo '<td class="snapl-attribute-creation">';
			$snapdate = new DateTime();
			$snapdate->setTimestamp($snap['creation']);
			$detail = $snapdate->format('Y-m-d H:i:s');
			echo '<span>'.$detail.'</span>';
		echo '</td>';
		echo '<td id="snapl-attribute-actions">';
			echo '<span class="zfs_bar_button"><a style="cursor:pointer" class="tooltip" title="Rollback Snapshot" onclick="rollbackSnapshot(\''.$snap['name'].'\')"><i id="zfsm-rollback" class="fa fa-backward" style="color:orange"></i></a></span>';
			echo '<span class="zfs_bar_button"><a style="cursor:pointer" class="tooltip" title="Hold Snapshot" onclick="holdSnapshot(\''.$snap['name'].'\')"><i id="zfsm-hold" class="fa fa-pause"></i></a></span>';
			echo '<span class="zfs_bar_button"><a style="cursor:pointer" class="tooltip" title="Release Snapshot" onclick="releaseSnapshot(\''.$snap['name'].'\')"><i id="zfsm-release" class="fa fa-play"></i></a></span>';
			echo '<span class="zfs_bar_button"><a style="cursor:pointer" class="tooltip" title="Destroy Snapshot" onclick="destroySnapshot(\''.$snap['name'].'\')"><i id="zfsm-destroy" class="fa fa-trash" style="color:red"></i></a></span>';
		echo '</td>';
		echo '</tr>';
		endforeach;?>
	</tbody>
	</table>
	</div>
</body>
</html>

<script>
  
  function rollbackSnapshot(snapshot) {
	$.post('<?=$urlzmadmin?>',{cmd: 'rollbacksnapshot', 'data': snapshot, 'csrf_token': '<?=$csrf_token?>'}, function(data){
		if (data == 'Ok') {
			top.Swal2.fire({
				title: 'Success!',
				icon:'success',
				html: 'Rollback of Snapshot '+snapshot+' Successful'
			});
		} else {
			top.Swal2.fire({
				title: 'Error!',
				icon:'error',
				html: 'Unable to rollback snapshot '+snapshot+'<br>Output: '+data
			}); 
		}
		top.Shadowbox.close();
	});
  }

  function holdSnapshot(snapshot) {
	$.post('<?=$urlzmadmin?>',{cmd: 'holdsnapshot', 'data': snapshot, 'csrf_token': '<?=$csrf_token?>'}, function(data){
		if (data == 'Ok') {
			top.Swal2.fire({
				title: 'Success!',
				icon:'success',
				html: 'Hold of Snapshot '+snapshot+' Successful'
			});
		} else {
			top.Swal2.fire({
				title: 'Error!',
				icon:'error',
				html: 'Unable to add reference to snapshot '+snapshot+'<br>Output: '+data
			}); 
		}
		top.Shadowbox.close();
	});
  }

  function releaseSnapshot(snapshot) {
	$.post('<?=$urlzmadmin?>',{cmd: 'releasesnapshot', 'data': snapshot, 'csrf_token': '<?=$csrf_token?>'}, function(data){
		if (data == 'Ok') {
			top.Swal2.fire({
				title: 'Success!',
				icon:'success',
				html: 'Release of Snapshot '+snapshot+' Successful'
			});
		} else {
			top.Swal2.fire({
				title: 'Error!',
				icon:'error',
				html: 'Unable to remove reference from snapshot '+snapshot+'<br>Output: '+data
			}); 
		}
		top.Shadowbox.close();
	});
  }

  function destroySnapshot(snapshot) {
	$.post('<?=$urlzmadmin?>',{cmd: 'destroysnapshot', 'data': snapshot, 'csrf_token': '<?=$csrf_token?>'}, function(data){
		if (data == 'Ok') {
			top.Swal2.fire({
				title: 'Success!',
				icon:'success',
				html: 'Destroy of Snapshot '+snapshot+' Successful'
			});
		} else {
			top.Swal2.fire({
				title: 'Error!',
				icon:'error',
				html: 'Unable to destroy snapshot '+snapshot+'<br>Output: '+data
			}); 
		}
		top.Shadowbox.close();
	});
  }
</script>