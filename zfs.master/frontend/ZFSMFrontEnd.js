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

function generatePoolTableRow(zpool, devices, show_status) {
	const show_button_status = getPoolShowButtonStatus(show_status);
	const status_color = getPoolStatusColor(zpool['Health']);
	const status_msg = getPoolStatusMsg(zpool['Health']);

	var tr = '<tr>';

	// Name and devices
	tr += '<td id="zpool-attribute-pool"><a class="info hand"><i id="zpool-'+zpool['Pool']+'" class="fa fa-circle orb '+status_color+'-orb"></i><span>'+nl2br(devices)+'</span></a> '+zpool['Pool']+'</td>';

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

	tr += '</tr>';

	return tr; 
}

function generateDatasetRow(zpool, datasets, show_status) {
	return '';
}

function generateDatasetArrayRows(zpool, datasets, show_status) {
	var tr = '<tr class="zdataset-'+zpool+' '+zclass+'" style="display: '+show_status+'">';

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

		html_pools += generatePoolTableRow( zpool, data['devices'][zpool['Pool']], show_status);

		html_pools += generateDatasetArrayRows( zpool['Pool'], data['datasets'][zpool['Pool']], show_status);
	});

	zfs_table_body.innerHTML = html_pools;
}

/*
	<?foreach ($zpool_global as $zpool):?>
    <tr>
	</tr>
		<?
			generateDatasetArrayRows($zpool['Pool'], $zpool_datasets[$zpool['Pool']], $_COOKIE['zdataset-'.$zpool['Pool']] ?? 'none', $zfsm_cfg, $zpool['Pool']);
		?>
	<?endforeach;?>
    */