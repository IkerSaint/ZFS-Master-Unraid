import * as utils from "./ZFSMUtils.js";

function getLastSnap(zsnapshots) {
	var lastsnap = zsnapshots[0];

	for (snap in zsnapshots) {
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

function getPoolShowButtonStatus(show_status) {
	return show_status == true ? "Hide Datasets" : "Show Datasets";
}

function getPoolShowStatus(zpool) {
	var cookie = document.cookie;

	return cookie['zdataset-'+zpool] == true ? true : false;
}

function generatePoolTableRows(zpool, devices, show_status) {
	const show_button_status = getPoolShowButtonStatus(show_status);
	const status_color = getPoolStatusColor(zpool['Health']);
	const status_msg = getPoolStatusMsg(zpool['Health']);

	// Name and devices
	var tr = '<td id="zpool-attribute-pool"><a class="info hand"><i id="zpool-'+zpool['Pool']+'" class="fa fa-circle orb '+status_color+'-orb"></i><span>'+utils.nl2br(devices)+'</span></a> '+zpool['Pool']+'</td>';

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
	var percent = 100-Math.round(utils.calculateFreePercent(zpool['Used'], zpool['Free']));
	tr += '<td id="zpool-attribute-used"><div class="usage-disk"><span style="position:absolute; width:'+percent+'%" class=""><span>'+zpool['Used']+'B</span></div></td>';

	// Free
	tr += '<td id="zpool-attribute-free"><div class="usage-disk"><span style="position:absolute; width:'+(100-percent)+'%" class=""><span>'+zpool['Free']+'B</span></div></td>';

	// Snapshots
	tr += '<td id="zpool-attribute-snapshots"><i class="fa fa-camera-retro icon"></i>'+(zpool['Snapshots'] == null ? 0 : zpool['Snapshots'])+'</td>';

	return tr; 
}

function generateDatasetRow(zpool, zdataset, show_status, destructive_mode, snap_max_days_alert) {
	var tr = '<tr class="zdataset-'+zpool+' '+zdataset+'" style="display: '+show_status+'">';
	tr += '<td></td><td></td><td>';

	const creationDate = new Date(zdataset['creation'] * 1000);

	const properties = {
		'Creation Date' : creationDate.toISOString(),
		'Compression' : zdataset['compression'],
		'Compress Ratio' : zdataset['compressratio']/100,
		'Record Size' : utils.fromBytesToString(zdataset['recordsize']),
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
		'Space used by Snaps' : utils.fromBytesToString(zdataset['usedbysnapshots'])
	};

	const icon_color = 'grey';
			
	if (zdataset['snapshots'] > 0) {
		const snap = getLastSnap(zdataset['snapshots']);

		snapdate = new Date(snap['creation']);
				
		if (utils.daysToNow(snap['creation']) > snap_max_days_alert) {
			icon_color = 'orange';
		} else {
			icon_color = '#486dba';
		}
				
		properties['Last Snap Date'] = snapdate.toISOString();
		properties['Last Snap'] = snap['name'];
	}

	const depth = zdataset['name'].split('/').length - 1;

	for (let i = 1; i <= depth; i++) {
    	tr += '&emsp;&emsp;';
	}

	tr += '<a class="info hand"><i class="fa fa-hdd-o icon" style="color:'+icon_color+'" onclick="toggleDataset(\''+zdataset['name']+'\');"></i>';
	tr += '<span>'+utils.implodeWithKeys('<br>', properties)+'</span></a>';


	if (zdataset['child'] > 0) {
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
	var id = utils.crc16(zdataset['name']);
	var snap_count = 0;

	if (zdataset['snapshots'] !== undefined ) {
		snap_count = zdataset.snapshots.length;
	} else {
	}

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
	tr += utils.fromBytesToString(zdataset['referenced']);
	tr += '</td>';

	// Used
	tr += '<td>';
	var percent = 100-Math.round(utils.calculateFreePercent(zdataset['used'], zdataset['available']));
	tr += '<div class="usage-disk"><span style="width:'+percent+'%" class=""><span>'+utils.fromBytesToString(zdataset['used'])+'</span></div>';
	tr += '</td>';

	// Free
	tr += '<td>';
	tr += '<div class="usage-disk"><span style="width:'+(100-percent)+'%" class=""><span>'+utils.fromBytesToString(zdataset['available'])+'</span></div>';
	tr += '</td>';

	// Snapshots

	tr += '<td>';
	
	tr += '<i class="fa fa-camera-retro icon" style="color:'+icon_color+'"></i> ';
	tr += zdataset['snapshots'] !== undefined ? zdataset['snapshots'] : 0 ;

	if (zdataset['mountpoint'] != "none") {
		tr += ' <a href="/Main/Browse?dir='+zdataset['mountpoint']+'"><i class="icon-u-tab zfs_bar_button" title="Browse '+zdataset['mountpoint']+'"></i></a>';
	}

	tr += '</td>';
	tr += '</tr>';

	return tr;
}

function generateDatasetArrayRows(zpool, datasets, show_status, destructive_mode, snap_max_days_alert) {
	var tr = '<tr class="zdataset-'+zpool+' '+zpool+'" style="display: '+show_status+'">';

	if (Object.keys(datasets.child).length == 0) {
		return tr;
	}

	Object.values(datasets.child).forEach((zdataset) => {
		tr += generateDatasetRow(zpool, zdataset, show_status, destructive_mode, snap_max_days_alert);

		if (Object.keys(zdataset.child).length > 0) {
			tr += generateDatasetArrayRows(zpool, zdataset, show_status, destructive_mode, snap_max_days_alert);
		}
	});

	return tr;
}

function updateFullBodyTable(data, destructive_mode, snap_max_days_alert) {
	var html_pools = "";

	Object.values(data.pools).forEach((zpool) => {
		const show_status = getPoolShowStatus(zpool['Pool']);

		zfs_table_body = document.getElementById('zfs_master_body');

		html_pools += '<tr>';
		html_pools += generatePoolTableRows( zpool, data['devices'][zpool['Pool']], show_status);
		html_pools += generateDatasetArrayRows( zpool['Pool'], data['datasets'][zpool['Pool']], show_status, destructive_mode, snap_max_days_alert);
		html_pools += '</tr>';
	});

	zfs_table_body.innerHTML = html_pools;
}