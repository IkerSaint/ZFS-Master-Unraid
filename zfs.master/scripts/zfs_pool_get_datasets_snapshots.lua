local snap_properties = {'used','referenced','written','defer_destroy','userrefs','creation'}
local dataset_properties = {'used','available','referenced','encryption', 'keystatus', 'mountpoint','compression','compressratio','usedbysnapshots','quota','recordsize','atime','xattr','primarycache','readonly','casesensitivity','sync','creation', 'origin', 'type', 'volblocksize'}
local total_snapshots = 0

local function isempty(str)
	return str == nil or str == ''
end

function list_snapshots(dataset)
	local snapshot_list = {}
	
	for snap in zfs.list.snapshots(dataset) do
		local snapshot = {}
		snapshot['name'] = snap
		
		for idx, property in ipairs(snap_properties) do
			snapshot[property] = zfs.get_prop(snap, property)
		end
		
		total_snapshots = total_snapshots+1
		
		table.insert(snapshot_list, snapshot)
	end 
	
	return snapshot_list
end

function list_datasets(root, exclusion_pattern) 
	local dataset = {}
	
	dataset['name'] = root
	
	for idx, property in ipairs(dataset_properties) do
		dataset[property] = zfs.get_prop(root, property)
	end

	dataset['child'] = {}
	
    for child in zfs.list.children(root) do
		if (not isempty(exclusion_pattern) and string.match(child, exclusion_pattern)) then
			goto continue
		end
		dataset['child'][child] = list_datasets(child, exclusion_pattern);
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