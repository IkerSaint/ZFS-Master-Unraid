//region utils

function crc16(str) {
	const crcTable = [];
	const polynomial = 0xA001;
  
	for (let i = 0; i < 256; i++) {
		let crc = i;
		for (let j = 0; j < 8; j++) {
			crc = (crc & 1) ? ((crc >>> 1) ^ polynomial) : (crc >>> 1);
		}
		crcTable[i] = crc;
	}
	
	let crc = 0xFFFF;

	for (let i = 0; i < str.length; i++) {
		const charCode = str.charCodeAt(i);
		crc = ((crc >>> 8) ^ crcTable[(crc ^ charCode) & 0xFF]) & 0xFFFF;
	}
	
	return crc.toString(16).toUpperCase().padStart(4, '0');
}

function nl2br(str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function fromBytesToString(bytes) {
	const units = ['B', 'KB', 'MB', 'GB', 'TB'];
  
	bytes = Math.max(bytes, 0);
	const pow = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
	const limitedPow = Math.min(pow, units.length - 1);
  
	bytes /= Math.pow(1024, limitedPow);
  
	return bytes.toFixed(2) + ' ' + units[limitedPow];
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

function implodeWithKeys(glue, array, symbol = ': ') {
    return Object.keys(array)
	  .map((key) => key + symbol + array[key])
	  .join(glue);
}

function daysToNow(timestamp) {
    const currentDate = new Date();
    const diffDate = new Date(timestamp * 1000);

    const timeDifference = currentDate - diffDate; 

    const daysDifference = Math.floor(timeDifference / (1000 * 60 * 60 * 24));

    return daysDifference;
}

function setCookie(key, value, daysToLive=365) {
	var expires = new Date();
	expires.setTime(expires.getTime() + (daysToLive * 24 * 60 * 60));
	document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
}

function getCookie(key) {
	var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
	return keyValue ? keyValue[2] : null;
}

function eraseCookie(key) {
	var keyValue = getCookie(key);
    setCookie(key, keyValue, '-1');
}

//endregion utils


function getLastSnap(zsnapshots) {
	var lastsnap = zsnapshots[0];

	for (const snap of zsnapshots) {
		if (snap['creation'] > lastsnap['creation']) {
			lastsnap = snap;
		}
	}

	return lastsnap;
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

function getPoolShowButtonText(show_status) {
	return show_status == true ? "Hide Datasets" : "Show Datasets";
}

function getPoolShowStatus(zpool) {
	var cookie = getCookie('zdataset-'+zpool);

	if (cookie === undefined)
		return false;

	if (cookie == 'none')
		return false;

	return true;
}

function generateDatasetRow(zpool, zdataset, parent, show_status, destructive_mode, snap_max_days_alert) {
	var tr = '<tr id="tr-'+zdataset['name']+'" class="zdataset-'+zpool+' '+parent+'" style="display: '+(show_status ? 'table-row' : 'none')+'">';
	tr += '<td></td><td></td><td>';

	const creationDate = new Date(zdataset['creation'] * 1000);

	const properties = {
		'Creation Date' : creationDate.toISOString(),
		'Compression' : zdataset['compression'],
		'Compress Ratio' : zdataset['compressratio']/100,
		'Record Size' : fromBytesToString(zdataset['recordsize']),
		'Access Time' : zdataset['atime'],
		'XAttr' : zdataset['xattr'],
		'Primary Cache' : zdataset['primarycache'],
		'Encryption' : zdataset['encryption'],
		'Key Status' : zdataset['keystatus'],
		'Quota' : fromBytesToString(zdataset['quota']),
		'Read Only' : zdataset['readonly'],
		'Case Sensitive' : zdataset['casesensitivity'],
		'Sync' : zdataset['sync'],
		'Origin' : zdataset['origin'] ?? '',
		'Space used by Snaps' : fromBytesToString(zdataset['usedbysnapshots'])
	};

	var icon_color = 'grey';
	var snap_count = 0;

	if (zdataset['snapshots'] !== undefined && zdataset['snapshots'].length > 0) {
		const snap = getLastSnap(zdataset['snapshots']);

		snapdate = new Date(snap['creation'] * 1000);

		if (daysToNow(snap['creation']) > snap_max_days_alert) {
			icon_color = 'orange';
		} else {
			icon_color = '#486dba';
		}

		properties['Last Snap Date'] = snapdate.toISOString();
		properties['Last Snap'] = snap['name'];

		snap_count = zdataset['snapshots'].length;
	}

	const depth = zdataset['name'].split('/').length - 1;

	for (let i = 1; i <= depth; i++) {
    	tr += '&emsp;&emsp;';
	}

	tr += '<a class="info hand"><i class="fa fa-hdd-o icon" style="color:'+icon_color+'" onclick="toggleDataset(\''+zdataset['name']+'\');"></i>';
	tr += '<span>'+implodeWithKeys('<br>', properties)+'</span></a>';


	if (Object.keys(zdataset.child).length > 0) {
		tr += '<i class="fa fa-minus-square fa-append" name="'+zdataset['name']+'"></i>';
	}

	if (zdataset['origin'] !== undefined) {
		tr += '<i class="fa fa-clone fa-append"></i>';
	}

	if (zdataset['keystatus'] != 'none') {
		if (zdataset['keystatus'] == 'available') {
			tr += '<i class="fa fa-unlock fa-append"></i>';
		} else {
			tr += '<i class="fa fa-lock fa-append"></i>';
		}
	}

	tr += zdataset['name'].substring(zdataset['name'].lastIndexOf("/") + 1);
	tr += '</td>';

	// Actions

	tr += '<td>';
	var id = crc16(zdataset['name']);

	tr += '<button type="button" id="'+id+'" onclick="addDatasetContext(\''+zpool+'\', \''+zdataset['name']+'\', '+snap_count+', \''+id+'\', '+destructive_mode+', \''+zdataset['keystatus']+'\'';
	
	if (zdataset['origin'] !== undefined) {
		tr += ',\''+zdataset['origin']+'\'';
	}

	tr += ');" class="zfs_compact">Actions</button></span>';
	tr += '</td>';

	//mountpoint
	tr += '<td>';
	if (zdataset['mountpoint'] != "none") {
		tr += zdataset['mountpoint'];
	}

	tr += '</td>';

	// Referr
	tr += '<td>';
	tr += fromBytesToString(zdataset['referenced']);
	tr += '</td>';

	// Used
	tr += '<td>';
	var percent = 100-Math.round(calculateFreePercent(zdataset['used'], zdataset['available']));
	tr += '<div class="usage-disk"><span style="position:absolute; width:'+percent+'%" class=""><span>'+fromBytesToString(zdataset['used'])+'</span></div>';
	tr += '</td>';

	// Free
	tr += '<td>';
	tr += '<div class="usage-disk"><span style="position:absolute; width:'+(100-percent)+'%" class=""><span>'+fromBytesToString(zdataset['available'])+'</span></div>';
	tr += '</td>';

	// Snapshots

	tr += '<td>';
	tr += '<i class="fa fa-camera-retro icon" style="color:'+icon_color+'"></i><span>'+snap_count+'</span>';

	// Mountpoint

	if (zdataset['mountpoint'] != "none") {
		tr += ' <a href="/Main/Browse?dir='+zdataset['mountpoint']+'"><i class="icon-u-tab zfs_bar_button" title="Browse '+zdataset['mountpoint']+'"></i></a>';
	}

	tr += '</td>';
	tr += '</tr>';

	return tr;
}

function generateDatasetArrayRows(zpool, dataset, parent, show_status, destructive_mode, snap_max_days_alert) {
	if (Object.keys(dataset.child).length == 0) {
		return generateDatasetRow(zpool, zdataset, parent, show_status, destructive_mode, snap_max_days_alert);
	}

	var tr = '';

	Object.values(dataset.child).forEach((zdataset) => {
		tr += generateDatasetRow(zpool, zdataset, dataset['name'], show_status, destructive_mode, snap_max_days_alert);

		if (Object.keys(zdataset.child).length > 0) {
			tr += generateDatasetArrayRows(zpool, zdataset, dataset['name'], show_status, destructive_mode, snap_max_days_alert);
		}
	});

	return tr;
}

function generatePoolTableRows(zpool, devices, show_status) {
	const show_button_text = getPoolShowButtonText(show_status);
	const status_color = getPoolStatusColor(zpool['Health']);
	const status_msg = getPoolStatusMsg(zpool['Health']);

	// Name and devices
	var tr = '<td id="'+zpool['Pool']+'-attribute-pool"><a class="info hand"><i id="zpool-'+zpool['Pool']+'" class="fa fa-circle orb '+status_color+'-orb"></i><span>'+nl2br(devices)+'</span></a> '+zpool['Pool']+'</td>';

	// Health
	tr += '<td id="'+zpool['Pool']+'-attribute-health"><a class="info hand"><i class="fa fa-heartbeat" style="color:'+status_color+'"></i><span>'+status_msg+'</span></a> '+zpool['Health']+'</td>';

	// Buttons
	tr += '<td id="'+zpool['Pool']+'-attribute-name"><button type="button" id="show-zpool-'+zpool['Pool']+'" onclick="togglePoolTable(\'show-zpool-'+zpool['Pool']+'\', \'zdataset-'+zpool['Pool']+'\');">'+show_button_text+'</button>'; 
	tr += '<button type="button" onclick="createDataset(\''+zpool['Pool']+'\')";">Create Dataset</button></td>';

	// Size
	tr += '<td id="'+zpool['Pool']+'-attribute-size">'+zpool['Size']+'</td>'; 

	// Mountpoint
	tr += '<td id="'+zpool['Pool']+'-attribute-mountpoint">'+zpool['MountPoint']+'</td>'; 

	// Refer
	tr += '<td id="'+zpool['Pool']+'-attribute-refer">'+zpool['Refer']+'</td>'; 

	// Used
	var percent = 100-Math.round(calculateFreePercent(zpool['Used'], zpool['Free']));
	tr += '<td id="'+zpool['Pool']+'-attribute-used"><div class="usage-disk"><span style="position:absolute; width:'+percent+'%" class=""><span>'+zpool['Used']+'B</span></div></td>';

	// Free
	tr += '<td id="'+zpool['Pool']+'-attribute-free"><div class="usage-disk"><span style="position:absolute; width:'+(100-percent)+'%" class=""><span>'+zpool['Free']+'B</span></div></td>';

	// Snapshots
	tr += '<td id="'+zpool['Pool']+'-attribute-snapshots"><i class="fa fa-camera-retro icon"></i><snap>'+(zpool['Snapshots'] == null ? 0 : zpool['Snapshots'])+'</snap></td>';

	return tr; 
}

function updateFullBodyTable(data, destructive_mode, snap_max_days_alert) {
	var html_pools = "";

	Object.values(data.pools).forEach((zpool) => {
		const show_status = getPoolShowStatus(zpool['Pool']);

		zfs_table_body = document.getElementById('zfs_master_body');

		html_pools += '<tr>';
		html_pools += generatePoolTableRows( zpool, data['devices'][zpool['Pool']], show_status);
		html_pools += generateDatasetArrayRows( zpool['Pool'], data['datasets'][zpool['Pool']], zpool['Pool'], show_status, destructive_mode, snap_max_days_alert);
		html_pools += '</tr>';
	});

	zfs_table_body.innerHTML = html_pools;
}

function updateSnapshotInfo(data, snap_max_days_alert) {
	if (data['snapshots'].length <= 0) {
		return;
	}

	var icon_color = 'grey';
	var snap_count = 0;
	const snap = getLastSnap(data['snapshots']);

	snapdate = new Date(snap['creation'] * 1000);

	if (daysToNow(snap['creation']) > snap_max_days_alert) {
		icon_color = 'orange';
	} else {
		icon_color = '#486dba';
	}

	//properties['Last Snap Date'] = snapdate.toISOString();
	//properties['Last Snap'] = snap['name'];

	snap_count = data['snapshots'].length;	
}