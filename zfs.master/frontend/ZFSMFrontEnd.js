function nl2br(str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function getPoolStatusColor(status) {
	switch(status) {
		case 'ONLINE':
			return 'green';
		case 'DEGRADED':
			return 'yellow';
		case 'FAULTED':
			return 'red';
		case 'OFFLINE':
			return 'blue';
		case 'UNAVAIL':
			return 'grey';
		case 'REMOVED':
			return 'grey';
	}

	return 'grey';
}

function getPoolStatusMsg(status) {
	switch (status) {
		case 'ONLINE':
			return 'The pool is in normal working order';
		case 'DEGRADED':
			return 'One or more devices with problems. Functioning in a degraded state';
		case 'FAULTED':
			return 'One or more devices could not be used. Pool unable to continue functioning';
		case 'OFFLINE':
			return 'One or more devices has been explicitly taken offline by the administrator ';
		case 'UNAVAIL':
			return 'One or more devices or virtual devices cannot be opened';
		case 'REMOVED':
			return 'One or more devices were physically removed while the system was running';
	}

	return 'Status Unknown'
}

function getPoolShowButtonStatus(show_status) {
	return show_status == true ? "Hide Datasets" : "Show Datasets";
}

function getPoolShowStatus(zpool) {
	var cookie = document.cookie;

	return cookie['zdataset-'+zpool] == true ? true : false;
}

function fromStringToBytes(spacestr) {
	let returnNumber = parseFloat(spacestr);

	switch (spacestr.slice(-1)) {
	  case 'T':
		returnNumber *= 1024;
	  case 'G':
		returnNumber *= 1024;
	  case 'M':
		returnNumber *= 1024;
	  case 'K':
		returnNumber *= 1024;
	  default:
		break;
	}

	return returnNumber;
}

function calculateFreePercent(used, free) {
	const usedTmp = typeof used === "string" ? fromStringToBytes(used) : used;
	const freeTmp = typeof free === "string" ? fromStringToBytes(free) : free;

	const result = freeTmp / (freeTmp + usedTmp);
	return result * 100;
}

function generatePoolTableRows(zpool, devices, show_status) {
	const show_button_status = getPoolShowButtonStatus(show_status);
	const status_color = getPoolStatusColor(zpool['Health']);
	const status_msg = getPoolStatusMsg(zpool['Health']);

	// Name and devices
	var tr = '<td id="zpool-attribute-pool"><a class="info hand"><i id="zpool-'+zpool['Pool']+'" class="fa fa-circle orb '+status_color+'-orb"></i><span>'+nl2br(devices)+'</span></a> '+zpool['Pool']+'</td>';

	// Health
	tr += '<td id="zpool-attribute-health"><a class="info hand"><i class="fa fa-heartbeat" style="color:'+status_color+'"></i><span>'+status_msg+'</span></a> '+zpool['Health']+'</td>';

	// Buttons
	tr += '<td id="zpool-attribute-name"><button type="button" id="show-zpool-'+zpool['Pool']+'" onclick="togglePoolTable(\'show-zpool-'+zpool['Pool']+'\', \'zdataset-'+zpool['Pool']+'\');">'+show_button_status+'</button>'; 
	tr += '<button type="button" onclick="createDataset(\''+zpool['Pool']+'\')";">Create Dataset</button></td>';

	// Size
	tr += '<td id="zpool-attribute-size">'+zpool['Size']+'</td>'; 

	// Mountpoint
	tr += '<td id="zpool-attribute-mountpoint">'+zpool['MountPoint']+'</td>'; 

	// Refer
	tr += '<td id="zpool-attribute-refer">'+zpool['Refer']+'</td>'; 

	// Used
	var percent = 100-Math.round(calculateFreePercent(zpool['Used'], zpool['Free']));
	tr += '<td id="zpool-attribute-used"><div class="usage-disk"><span style="position:absolute; width:'+percent+'%" class=""><span>'+zpool['Used']+'B</span></div></td>';

	// Free
	tr += '<td id="zpool-attribute-free"><div class="usage-disk"><span style="position:absolute; width:'+(100-percent)+'%" class=""><span>'+zpool['Free']+'B</span></div></td>';

	// Snapshots
	tr += '<td id="zpool-attribute-snapshots"><i class="fa fa-camera-retro icon"></i>'+(zpool['Snapshots'] == null ? 0 : zpool['Snapshots'])+'</td>';

	return tr; 
}

function generateDatasetRow(zpool, zdataset, show_status) {
	const creationDate = new Date(zdataset['creation'] * 1000).format('Y-m-d H:i:s');

	const properties = {
		'Creation Date' : creationDate,
		'Compression' :  zdataset['compression'],
		'Compress Ratio' : zdataset['compressratio']/100,
		'Record Size' :  fromBytesToString(zdataset['recordsize']),
		'Access Time' :  zdataset['atime'],
		'XAttr' :  zdataset['xattr'],
		'Primary Cache' :  zdataset['primarycache'],
		'Encryption' : zdataset['encryption'],
		'Key Status' : zdataset['keystatus'],
		'Quota' :  fromBytesToString(zdataset['quota']),
		'Read Only' :  zdataset['readonly'],
		'Case Sensitive' :  zdataset['casesensitivity'],
		'Sync' :  zdataset['sync'],
		'Origin' :  zdataset['origin'] ?? '',
		'Space used by Snaps' : fromBytesToString(zdataset['usedbysnapshots'])
	};

	var tr = '<tr class="zdataset-'+zpool+' '+zpool+'" style="display: '+show_status+'">';
	tr += '<td></td><td></td><td>';

	return tr;
}


/*
	echo '<tr class="zdataset-'.$zpool.' '.$zclass.'" style="display: '.$display.'">';
	echo '<td>';
	echo '</td>';
	echo '<td>';
	echo '</td>';
	echo '<td>';
		$creationdate = new DateTime();
		$creationdate->setTimestamp($zdataset['creation']);

		$tmp_array = ["Creation Date" => $creationdate->format('Y-m-d H:i:s'),
				"Compression" =>  $zdataset['compression'],
				"Compress Ratio" => ($zdataset['compressratio']/100),
				"Record Size" =>  fromBytesToString($zdataset['recordsize']),
				"Access Time" =>  $zdataset['atime'],
				"XAttr" =>  $zdataset['xattr'],
				"Primary Cache" =>  $zdataset['primarycache'],
				"Encryption" => $zdataset['encryption'],
				'Key Status' => $zdataset['keystatus'],
				"Quota" =>  fromBytesToString($zdataset['quota']),
				"Read Only" =>  $zdataset['readonly'],
				"Case Sensitive" =>  $zdataset['casesensitivity'],
				"Sync" =>  $zdataset['sync'],
				"Origin" =>  $zdataset['origin'] ?? "",
				"Space used by Snaps" =>  fromBytesToString($zdataset['usedbysnapshots'])];

		$icon_color = 'grey';
			
		if (count($zdataset['snapshots']) > 0):
			$snap = getLastSnap($zdataset['snapshots']);
									
			$snapdate = new DateTime();
			$snapdate->setTimestamp($snap['creation']);
				
			if (daysToNow($snap['creation']) > $zfsm_cfg['snap_max_days_alert']):
				$icon_color = 'orange';
			else:
				$icon_color = '#486dba';
			endif;
				
			$tmp_array['Last Snap Date'] = $snapdate->format('Y-m-d H:i:s');
			$tmp_array['Last Snap'] = $snap['name'];
		endif;

		$depth = substr_count($zdataset['name'], '/');

		for ( $i = 1; $i <= $depth; $i++) {
			echo '&emsp;&emsp;';
		}

		echo '<a class="info hand">';
		echo '<i class="fa fa-hdd-o icon" style="color:'.$icon_color.'" onclick="toggleDataset(\''.$zdataset['name'].'\');"></i>';
		echo '<span>'.implodeWithKeys('<br>', $tmp_array).'</span>';
		echo '</a>';

		if (count($zdataset['child']) > 0):
			echo '<i class="fa fa-minus-square fa-append" name="'.$zdataset['name'].'"></i>';
		endif;

		if (isset($zdataset['origin'])):
			echo '<i class="fa fa-clone fa-append"></i>';
		endif;

		if ($zdataset['keystatus'] != 'none'):
			if ($zdataset['keystatus'] == 'available'):
				echo '<i class="fa fa-unlock fa-append"></i>';
			else:
				echo '<i class="fa fa-lock fa-append"></i>';
			endif;
		endif;

		echo substr( $zdataset['name'], strrpos($zdataset['name'], "/")  + 1,  strlen($zdataset['name']) );
	echo '</td>';

	// Actions

	echo '<td>';
		$id = md5($zdataset['name']);
		echo '<button type="button" id="'.$id.'" onclick="addDatasetContext(\''.$zpool.'\', \''.$zdataset['name'].'\', '.count($zdataset['snapshots']).', \''.$id.'\', '.$zfsm_cfg['destructive_mode'].', \''.$zdataset['keystatus'].'\'';
		if (isset($zdataset['origin'])):
			echo ',\''.$zdataset['origin'].'\'';
		endif;
		echo ');" class="zfs_compact">Actions</button></span>';
	echo '</td>';

	//mountpoint
	echo '<td>';
		if ($zdataset['mountpoint'] != "none"): 
			echo $zdataset['mountpoint'];
		endif;
	echo '</td>';

	// Referr
	echo '<td>';
		echo fromBytesToString($zdataset['referenced']);
	echo '</td>';

	// Used
	echo '<td>';
		$percent = 100-round(calculateFreePercent($zdataset['used'], $zdataset['available']));
		echo '<div class="usage-disk"><span style="width:'.$percent.'%" class=""><span>'.fromBytesToString($zdataset['used']).'</span></div>';
	echo '</td>';

	// Free
	echo '<td>';
		$percent = round(calculateFreePercent($zdataset['used'], $zdataset['available']));
		echo '<div class="usage-disk"><span style="width:'.$percent.'%" class=""><span>'.fromBytesToString($zdataset['available']).'</span></div>';
	echo '</td>';

	//snapshots
	echo '<td>';
		$icon_color = 'grey';
		
		if (count($zdataset['snapshots']) > 0):
			$snap = getLastSnap($zdataset['snapshots']);
			$days = daysToNow($snap['creation']);
			
			if ($days > $zfsm_cfg['snap_max_days_alert']):
				$icon_color = 'orange';
			else:
				$icon_color = '#486dba';
			endif;
		endif;
	
		echo '<i class="fa fa-camera-retro icon" style="color:'.$icon_color.'"></i> ';
		echo count($zdataset['snapshots']);

		if ($zdataset['mountpoint'] != "none"): 
			echo ' <a href="/Main/Browse?dir='.$zdataset['mountpoint'].'"><i class="icon-u-tab zfs_bar_button" title="Browse '.$zdataset['mountpoint'].'"></i></a>';
		endif;
	echo '</td>';
	echo '</tr>';*/


function generateDatasetArrayRows(zpool, datasets, show_status) {
	var tr = '<tr class="zdataset-'+zpool+' '+zpool+'" style="display: '+show_status+'">';

	if ( Object.keys(datasets.child).length == 0) {
		return tr;
	}

	Object.values(datasets.child).forEach((zdataset) => {
		tr += generateDatasetRow(zpool, zdataset, show_status);

		if (Object.keys(zdataset.child).length > 0) {
			tr += generateDatasetArrayRows(zpool, zdataset, show_status);
		}
	});

	return tr;
}

function updateFullBodyTable(data, document) {
	var html_pools = "";

	Object.values(data.pools).forEach((zpool) => {
		const show_status = getPoolShowStatus(zpool['Pool']);

		zfs_table_body = document.getElementById('zfs_master_body');

		html_pools += '<tr>';
		html_pools += generatePoolTableRow( zpool, data['devices'][zpool['Pool']], show_status);
		html_pools += generateDatasetArrayRows( zpool['Pool'], data['datasets'][zpool['Pool']], show_status);
		html_pools += '</tr>';
	});

	zfs_table_body.innerHTML = html_pools;
}