local zfs_get_prop = zfs.get_prop

function get_dataset_properties(dataset, dataset_properties) 
	local dataset = {}
	
	dataset['name'] = root
	
	for idx, property in ipairs(dataset_properties) do
		dataset[property] = zfs_get_prop(root, property)
	end
	
	return dataset;
end 
 
args = ... 
argv = args["argv"]

ret = get_dataset_properties(argv[1], argv[2])

return ret