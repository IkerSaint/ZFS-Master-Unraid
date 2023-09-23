function get_dataset_property(name, property)
    return zfs.get_prop(name, property)
end

args = ...
argv = args["argv"]

return get_dataset_property(argv[1], argv[2])