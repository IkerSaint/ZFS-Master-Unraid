local zfs_get_prop = zfs.get_prop
local zfs_list_children = zfs.list.children
local zfs_list_snapshots = zfs.list.snapshots
local snap_properties = {'used','referenced','defer_destroy','userrefs','creation'}
local dataset_properties = {'used','available','referenced','encryption', 'keystatus', 'mountpoint','compression','compressratio','usedbysnapshots','quota','recordsize','atime','xattr','primarycache','readonly','casesensitivity','sync','creation', 'origin'}
local total_snapshots = 0

local function isempty(str)
	return str == nil or str == ''
end

function list_snapshots(dataset)
	local snapshot_list = {}
	
	for snap in zfs_list_snapshots(dataset) do
		local snapshot = {}
		snapshot['name'] = snap
		
		for idx, property in ipairs(snap_properties) do
			snapshot[property] = zfs_get_prop(snap, property)
		end
		
		total_snapshots = total_snapshots+1
		
		table.insert(snapshot_list, snapshot)
	end 
	
	return snapshot_list
end

function list_datasets(root, exclussion_pattern) 
	local dataset = {}
	
	dataset['name'] = root
	
	for idx, property in ipairs(dataset_properties) do
		dataset[property] = zfs_get_prop(root, property)
	end

	dataset['child'] = {}
	
    for child in zfs_list_children(root) do
		if (not isempty(exclussion_pattern) and string.match(child, exclussion_pattern)) then
			goto continue
		end
		dataset['child'][child] = list_datasets(child, exclussion_pattern);
		::continue::
    end
	
	dataset['snapshots'] = list_snapshots(root)
	
	return dataset;
end 
 
args = ... 
argv = args["argv"]

ret = list_datasets(argv[1], argv[2])
ret['total_snapshots'] = total_snapshots

return ret