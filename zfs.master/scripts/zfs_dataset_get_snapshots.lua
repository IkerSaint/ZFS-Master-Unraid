local snap_properties = {'used','referenced','defer_destroy','userrefs','creation'}

function list_snapshots(dataset)
    local snapshot_list = {}
    
    for snap in zfs.list.snapshots(dataset) do
        local snapshot = {}

        for idx, property in ipairs(snap_properties) do
            snapshot[property] = zfs.get_prop(snap, property)
        end

        snapshot_list[snap] = snapshot
    end

    return snapshot_list
end

args = ...
argv = args["argv"]

ret = list_snapshots(argv[1])

return ret