local dataset_properties = {'used','available','referenced','encryption', 'keystatus', 'mountpoint','compression','compressratio','usedbysnapshots','quota','recordsize','atime','xattr','primarycache','readonly','casesensitivity','sync','creation', 'origin', 'type', 'volblocksize'}

function get_properties(name) 
	local dataset = {}
	
	for idx, property in ipairs(dataset_properties) do
        dataset[property] = zfs.get_prop(name, property)
	end

	return dataset;
end 
 
args = ... 
argv = args["argv"]

ret = get_properties(argv[1])

return ret