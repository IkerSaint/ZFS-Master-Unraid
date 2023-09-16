local dataset_properties = {'used','available','referenced','encryption', 'keystatus', 'mountpoint','compression','compressratio','usedbysnapshots','quota','recordsize','atime','xattr','primarycache','readonly','casesensitivity','sync','creation', 'origin'}

local function isempty(str)
	return str == nil or str == ''
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
	
	return dataset;
end 
 
args = ... 
argv = args["argv"]

ret = list_datasets(argv[1], argv[2])

return ret