<?php

$plugin = "zfs.master";
$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
$urlzmadmin = "/plugins/".$plugin."/include/ZFSMAdmin.php";
$csrf_token = $_GET['csrf_token'];

require_once "$docroot/webGui/include/Helpers.php";
require_once "$docroot/plugins/$plugin/include/ZFSMBase.php";
require_once "$docroot/plugins/$plugin/include/ZFSMHelpers.php";

$zpool_datasets = $_GET['zpool_datasets'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>ZFS Master - Create Dataset</title>

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
	<div id="dataset-form-div" class="zfsm-dialog">
		<form id="dataset-form" name="dataset-form" class="zfsm-form" method="POST">
			<hr>
				<div class="zfsm-title">Base Options</div>
			<hr>
			<div id="dataset-base-options">
				<dl>
					Dataset Name<br>
					<input type="hidden" id="zpool" name="zpool" value="<?echo $zpool?>">
					<span class="zfsm-zpool" id="pool" name="<?echo $zpool?>"><?echo $zpool?></span> / <input id="name" class="zfsm-input zfsm-w75 zfsm-unraid-border" name="name" placeholder="Complete path, without the pool name." list="zpool-datasets" required>
					<datalist id="zpool-datasets">
					<?foreach ($zpool_datasets as $zdataset):
						if ($zdataset['Name'] == $zpool):
							continue;
						endif;
						$option = str_replace($zpool."/", "", $zdataset['Name'])."/";
					?>
						<option value="<?echo $option?>">
					<?endforeach;?>
					</datalist>
				</dl>
				<dl>
					Mount
					<select id="mount" name="mount" class="zfsm-input">
						<option value="yes" selected>Yes</option>
						<option value="no">No</option>
					</select>
					<input id="mountpoint" name="mountpoint" class="zfsm-input zfsm-w70 zfsm-unraid-border" placeholder="Empty for default, otherwise complete mountpoint path. ">
				</dl>
				<dl>
					Access Time
					<select id="atime" name="atime" class="zfsm-input">
						<option value="inherit" selected>Inherit</option>
						<option value="off">Off</option>
						<option value="on">On</option>
					</select>
					Case Sensitivity
					<select id="casesensitivity" name="casesensitivity" class="zfsm-input">
						<option value="sensitive" selected>Sensitive (Default)</option>
						<option value="insensitive">Insensitive</option>
						<option value="mixed">Mixed</option>
					</select>
				</dl>
				<dl>
					Compression
					<select id="compression" name="compression" class="zfsm-input">
						<option value="inherit" selected>Inherit</option>
						<option value="off">Off</option>
						<option value="lz4">lz4</option>
						<option value="gzip">gzip</option>
						<option value="zstd">zstd</option>
					</select>
					Quota
					<input id="quota" name="quota" class="zfsm-input zfsm-w10" maxlength="7">
					Set Permissions
					<input id="permissions" name="permissions" class="zfsm-input zfsm-w10" maxlength="7">
				</dl>
			</div>
			<hr>
				<div class="zfsm-title">Advanced Options</div>
			<hr>
			<div id="dataset-advanced-options">
				<dl>
					Extended Attributes:
					<select id="xattr" name="xattr" class="zfsm-input">
						<option value="inherit" selected>Inherit</option>
						<option value="sa">sa</option>
						<option value="on">on</option>
						<option value="off">off</option>
					</select>
					Record Size:
					<select id="recordsize" name="recordsize" class="zfsm-input">
						<option value="inherit" selected>Inherit</option>
						<option value="512">512</option>
						<option value="4K">4K</option>
						<option value="8K">8K</option>
						<option value="16K">16K</option>
						<option value="64K">64K</option>
						<option value="128K">128K</option>
						<option value="1MB">1MB</option>
					</select>
				</dl>
				<dl>
					Primary Cache:
					<select id="primarycache" name="primarycache" class="zfsm-input">
						<option value="inherit" selected>Inherit</option>
						<option value="all">All</option>
						<option value="metadata">Metadata</option>
						<option value="none">None</option>
					</select>
					Read Only:
					<select id="readonly" name="readonly" class="zfsm-input">
						<option value="off" selected>Off (Default)</option>
						<option value="on">On</option>
					</select>
					Sync:
					<select id="sync" name="sync" class="zfsm-input">
						<option value="standard" selected>Standard (Default)</option>
						<option value="always">Always</option>
						<option value="disabled">Disabled</option>
					</select>
				</dl>
			</div>
			<hr>
			<div id="dataset-footer" class="zfsm-footer">
				<button type="submit">Create</button>
			</div>
		</form>
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
   
  $("#dataset-form").submit(function(e){
        e.preventDefault();
		createDataset();
  });
  
  $("select[name='mount']").change(function() {
	 $("#mountpoint").attr('disabled', $(this).val() == 'no');
  });
   
  function createDataset() {
	formData = getFormData("#dataset-form");
		
	$.post('<?=$urlzmadmin?>',{cmd: 'createdataset', 'data': formData, 'csrf_token': '<?=$csrf_token?>'}, function(data){
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
	});
  }
</script>