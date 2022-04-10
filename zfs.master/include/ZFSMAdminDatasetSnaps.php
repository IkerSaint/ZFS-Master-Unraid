<?php

$plugin = "zfs.master";
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
$urlzmadmin = "/plugins/".$plugin."/include/ZFSMAdmin.php";
$csrf_token = $_GET['csrf_token'];

require_once "$docroot/webGui/include/Helpers.php";
require_once "$docroot/plugins/$plugin/include/ZFSMConstants.php";
require_once "$docroot/plugins/$plugin/include/ZFSMHelpers.php";

$zfsm_cfg = loadConfig(parse_plugin_cfg($plugin, true));

$zdataset = $_GET['zdataset'];
$zdataset_snaps = getZFSDatasetSnapshots($zdataset);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>ZFS Master - Admin Dataset Snaps</title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Security-Policy" content="block-all-mixed-content">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="same-origin">

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
  .zfs_table {
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
<script src="<?autov('/webGui/javascript/jquery.filetree.js')?>"></script>
<script type="text/javascript" src="<?autov('/plugins/zfs.master/assets/sweetalert2.all.min.js');?>"></script>

</head>

<body>
	<div id="adminsnaps-form-div" class="zfsm-dialog">
	<table id="zfs_master" class="zfs_table disk_status wide">
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
		<?foreach ($zdataset_snaps as $snap):?>
		<tr>
			<?foreach ($snap as $key => $zdetail):?>
			<td id=<?echo '"snapl-attribute-'.$key.'"'?>>
				<?
				if ($key == "Name"):
					echo '<i class="fa fa-hdd-o icon" style="color:#486dba"></i>';
					echo $zdetail;
				elseif ($key == 'Used'):
					echo '<span>'.fromBytesToString($zdetail).'</span>';
				elseif ($key == "Refer"):
					echo '<span>'.fromBytesToString($zdetail).'</span>';
				elseif ($key == "Defer Destroy"):
					echo '<span>'.$zdetail.'</span>';
				elseif ($key == "Holds"):
					echo '<span>'.$zdetail.'</span>';
				elseif ($key == 'Creation Date'):
					$snapdate = new DateTime();
					$snapdate->setTimestamp($zdetail);
					$detail = $snapdate->format('Y-m-d H:i:s');
					echo '<span>'.$detail.'</span>';
				endif;
				?>
			</td>
			<?endforeach;?>
			<td id="snapl-attribute-actions">
				<?
				echo '<span class="zfs_bar_button"><a style="cursor:pointer" class="tooltip" title="Rollback Snapshot" onclick="rollbackSnapshot(\''.$snap['Name'].'\')"><i id="zfsm-rollback" class="fa fa-backward" style="color:orange"></i></a></span>';
				echo '<span class="zfs_bar_button"><a style="cursor:pointer" class="tooltip" title="Hold Snapshot" onclick="holdSnapshot(\''.$snap['Name'].'\')"><i id="zfsm-hold" class="fa fa-pause"></i></a></span>';
				echo '<span class="zfs_bar_button"><a style="cursor:pointer" class="tooltip" title="Release Snapshot" onclick="releaseSnapshot(\''.$snap['Name'].'\')"><i id="zfsm-release" class="fa fa-play"></i></a></span>';
				echo '<span class="zfs_bar_button"><a style="cursor:pointer" class="tooltip" title="Destroy Snapshot" onclick="destroySnapshot(\''.$snap['Name'].'\')"><i id="zfsm-destroy" class="fa fa-trash" style="color:red"></i></a></span>';
				?>
			</td>
		</tr>
		<?endforeach;?>
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
  }

  function releaseSnapshot(snapshot) {
  }

  function destroySnapshot(snapshot) {
  }
</script>