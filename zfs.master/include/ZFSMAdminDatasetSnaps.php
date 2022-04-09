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


<style>
#spinner_image{position:fixed;left:46%;top:46%;width:16px;height:16px;display:none}
#control_panel{position:fixed;left:0;right:0;top:0;padding-top:8px;line-height:24px;white-space:nowrap}
.four{text-align:center;box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box}
.four label:first-child{margin-left:0}
.four label{margin-left:2%;cursor:pointer}
.allpanels{display:none;position:absolute;left:0;right:0;top:40px;bottom:25px;overflow:auto;margin:15px}
#footer_panel{position:fixed;left:0;right:0;bottom:0;height:30px;line-height:10px;text-align:center}
textarea{width:96%;height:250px;margin:10px 0;resize:none}
input[type=button]{margin-right:0;float:right}
input[type=email]{margin-top:8px;float:left}
</style>

<style type="text/css">	
  .zfsm-dialog {
	  width: 90%;
	  height: 90%;
	  margin: auto;
  }
  
  .zfsm-form {
	  font-size: 14px;
	  text-align: center;
  }
  
  .zfsm-title {
	  font-weight: 400;
	  position: relative;
	  display: block;
	  font-size: 25px;
	  text-align: center;
	  margin-bottom: 0;
  }
  
  .zfsm-input:disabled {
	  background-color: lightgrey;
  }
  
  .zfsm-input {
	  background-color: #fff;
	  width: auto;
	  border: 1px solid rgba(0,0,0,.10);
	  display: inline-block;
	  padding: 10px 5px;
	  border-radius: 5px;
  }
  
  .zfsm-zpool {
	  color: #ff8c2f;
	  width: auto;
	  border: 1px solid rgba(0,0,0,.10);
	  display: inline-block;
	  padding: 10px 5px;
	  border-radius: 5px;
	  background: linear-gradient(90deg,#e22828 0,#ff8c2f) 0 0 no-repeat,linear-gradient(90deg,#e22828 0,#ff8c2f) 0 100% no-repeat,linear-gradient(0deg,#e22828 0,#e22828) 0 100% no-repeat,linear-gradient(0deg,#ff8c2f 0,#ff8c2f) 100% 100% no-repeat;
	  background-size: 100% 2px,100% 2px,2px 100%,2px 100%;
  }
  
  .zfsm-unraid-border {
	  border: 1px solid rgba(0,0,0,.10);
	  background-color: #fff!important;
	  background: linear-gradient(90deg,#e22828 0,#ff8c2f) 0 0 no-repeat,linear-gradient(90deg,#e22828 0,#ff8c2f) 0 100% no-repeat,linear-gradient(0deg,#e22828 0,#e22828) 0 100% no-repeat,linear-gradient(0deg,#ff8c2f 0,#ff8c2f) 100% 100% no-repeat;
	  background-size: 100% 2px,100% 2px,2px 100%,2px 100%;
  }
  
  .zfsm-wauto {
	  width: auto;
  }
  
  .zfsm-w10 {
	  width: 10%;
  }
  
  .zfsm-w15 {
	  width: 15%;
  }
  
  .zfsm-w50 {
	  width: 50%;
  }
  
  .zfsm-w70 {
	  width: 70%;
  }
  
  .zfsm-w75 {
	  width: 75%;
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
					echo '<span>'.$zdetail.'B</span>';
				elseif ($key == "Refer"):
					echo '<span>'.$zdetail.'B</span>';
				elseif ($key == "Defer Destroy"):
					echo '<span>'.$zdetail.'</span>';
				elseif ($key == "Holds"):
					echo '<span>'.$zdetail.'</span>';
				elseif ($key == 'Creation Date'):
					$snapdate = new DateTime();
					$snapdate->setTimestamp($zdetail);
					$detail = $snapdate->format('Y-m-d H:i:s');
					echo '<span>'.$zdetail.'</span>';
				endif;
				?>
			</td>
			<?endforeach;?>
			<td id="snapl-attribute-actions">
				<?
				echo '<button type="button" class="zfs_compact" onclick="destroySnapshot(\''.$snap['Name'].'\');">Destroy</button>';
				echo '<button type="button" class="zfs_compact" onclick="rollbackSnapshot(\''.$snap['Name'].'\');">Rollback</button>';
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
  function getFormData(formId) {
	let formData = {};
    let inputs = $(formId).serializeArray();
    $.each(inputs, function (i, input) {
        formData[input.name] = input.value;
    });
    
	return formData;
   }
   
  $("#adminsnaps-form").submit(function(e){
        e.preventDefault();
		alert('your option was');
		//adminSnaps();
  });
   
  function rollbackSnapshot(snapshot) {
	formData = getFormData("#adminsnaps-form");
		
	$.post('<?=$urlzmadmin?>',{cmd: 'rollbacksnapshot', 'data': formData, 'csrf_token': '<?=$csrf_token?>'}, function(data){
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
</script>