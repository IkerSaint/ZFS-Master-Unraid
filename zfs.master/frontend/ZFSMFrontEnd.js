
function createFullBodyTable(data, document) {
	var html_pools = "";
	
    Object.values(data.pools).forEach((zpool) => {
        zfs_table_body = document.getElementById('zfs_master_body');

		var tmp_tr = '<tr>';

        Object.keys(zpool).forEach(key => {
            tmp_tr += '<td id="zpool-attribute-'+key+'"></td>';
        });

		tmp_tr += "</tr>";

		html_pools += tmp_tr;

		//var HTML = "<table border=1 width=100%><tr>";
		//for(j=1;j<=10;j++)
		//{
		//	HTML += "<td align=center>"+String.fromCharCode(j+64)+"</td>";
		//}
		//HTML += "</tr></table>";
		//document.getElementById("outputDiv").innerHTML = HTML;

		//zfs_table_body.appendChild(tr);
    });

	zfs_table_body.innerHTML = html_pols;
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