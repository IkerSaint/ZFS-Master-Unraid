function get_dataset_property(name, property)
    return zfs.get_prop(name, property)
end

args = ...
argv = args["argv"]

ret = get_dataset_property(argv[1], argv[2])

return ret