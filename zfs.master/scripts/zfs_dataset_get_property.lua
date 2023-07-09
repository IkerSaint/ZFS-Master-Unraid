local zfs_get_prop = zfs.get_prop

function get_dataset_property(name, property)
    local dataset  = {}
    
    dataset['name'] = name
    dataset[property] = zfs_get_prop(name, property)

    return ret;
end

args = ...
argv = args["argv"]

ret = get_dataset_property(argv[1], argv[2])

return ret