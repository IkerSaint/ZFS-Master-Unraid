local zfs_get_prop = zfs.get_prop
local dataset_properties = {'used','available','referenced','encryption', 'keystatus', 'mountpoint','compression','compressratio','usedbysnapshots','quota','recordsize','atime','xattr','primarycache','readonly','casesensitivity','sync','creation', 'origin'}

function get_properties(name) 
	local dataset = {}
	
	dataset['name'] = name
	
	for idx, property in ipairs(dataset_properties) do
        dataset[property] = zfs_get_prop(name, property)
	end

	return dataset;
end 
 
args = ... 
argv = args["argv"]

ret = get_properties(argv[1])

return ret