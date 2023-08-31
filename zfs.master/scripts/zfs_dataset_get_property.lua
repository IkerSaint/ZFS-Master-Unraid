function get_dataset_property(name, property)
    local dataset  = {}
    
    dataset[property] = zfs.get_prop(name, property)

    return dataset;
end

args = ...
argv = args["argv"]

ret = get_dataset_property(argv[1], argv[2])

return ret