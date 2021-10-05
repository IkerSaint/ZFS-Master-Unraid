<?php
	$statusColor = array(
		'ONLINE' => 'green',
		'DEGRADED' => 'yellow',
		'FAULTED' => 'red',
		'OFFLINE' => 'blue',
		'UNAVAIL' => 'grey',
		'REMOVED' => 'grey'
	);
	  
	$statusMsg = array(
		'ONLINE' => 'The pool is in normal working order',
		'DEGRADED' => 'One or more devices with problems. Functioning in a degraded state',
		'FAULTED' => 'One or more devices could not be used. Pool unable to continue functioning',
		'OFFLINE' => 'One or more devices has been explicitly taken offline by the administrator ',
		'UNAVAIL' => 'One or more devices or virtual devices cannot be opened',
		'REMOVED' => 'One or more devices were physically removed while the system was running'
	);
?>