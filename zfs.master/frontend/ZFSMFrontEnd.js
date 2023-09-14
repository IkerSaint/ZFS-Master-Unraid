
function createFullBodyTable(data, document) {
    data.pools.forEach((zpool) => {
        zfs_table_body = document.getElementById('zfs_master_body');

        tr = document.createElement(tr);

        Object.keys(zpool).forEach(key => {
            var td = document.createElement('td');
            td.setAttribute('id', 'zpool-attribute-'+key);
			tr.appendChild(td);
        });

		zfs_table_body.appendChild(tr);
    });
}

/*
	<?foreach ($zpool_global as $zpool):?>
    <tr>
		<?foreach ($zpool as $key => $zdetail):?>
		 <td id=<?echo '"zpool-attribute-'.$key.'"'?>>
	        <?
			if ($key == "Pool"):
				$zcolor = $statusColor[$zpool['Health']];
				
				echo '<a class="info hand">';
				echo '<i id="zpool-'.$zdetail.'" class="fa fa-circle orb '.$zcolor.'-orb"></i>';
				echo '<span>'.nl2br($zpool_devices[$zdetail]).'</span>';
				echo '</a>';
				echo $zdetail;
			elseif ($key == 'Health'):
				$zcolor = $statusColor[$zdetail];
				$zmsg = $statusMsg[$zdetail];
				
				echo '<a class="info hand">';
				echo '<i class="fa fa-heartbeat" style="color:'.$zcolor.'"></i>';
				echo '<span>'.$zmsg.'</span>';
				echo '</a> ';
				echo $zdetail;
			elseif ($key == "Name"):
				$showTableButtonText = "Show Datasets";
				if (isset($_COOKIE['zdataset-'.$zpool['Pool']]) == true && $_COOKIE['zdataset-'.$zpool['Pool']] != "none"):
					$showTableButtonText = "Hide Datasets";
				endif;

				echo '<button type="button" id="show-zpool-'.$zpool['Pool'].'" onclick="togglePoolTable(\'show-zpool-'.$zpool['Pool'].'\', \'zdataset-'.$zpool['Pool'].'\');">'.$showTableButtonText.'</button>';
				echo '<button type="button" onclick="createDataset(\''.$zpool['Pool'].'\')";">Create Dataset</button>';
			elseif ($key == "Used"):
				$percent = 100-round(calculateFreePercent($zpool['Used'], $zpool['Free']));
				echo '<div class="usage-disk"><span style="position:absolute; width:'.$percent.'%" class=""><span>'.$zdetail.'B</span></div>';
			elseif ($key == "Free"):
				$percent = round(calculateFreePercent($zpool['Used'], $zpool['Free']));
				echo '<div class="usage-disk"><span style="position:absolute; width:'.$percent.'%" class=""><span>'.$zdetail.'B</span></div>';
			elseif ($key == 'Snapshots'):
				echo '<i class="fa fa-camera-retro icon"></i> ';
				echo $zdetail;
			else:
				echo $zdetail;
			endif;
			?>
	     </td>
		<?endforeach;?>
	</tr>
		<?
			generateDatasetArrayRows($zpool['Pool'], $zpool_datasets[$zpool['Pool']], $_COOKIE['zdataset-'.$zpool['Pool']] ?? 'none', $zfsm_cfg, $zpool['Pool']);
		?>
	<?endforeach;?>
    */