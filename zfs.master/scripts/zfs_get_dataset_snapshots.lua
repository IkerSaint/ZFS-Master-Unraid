local zfs_get_prop = zfs.get_prop
local zfs_list_snapshots = zfs.list.snapshots
local snap_properties = {'used','referenced','defer_destroy','userrefs','creation'}

function list_snapshots(dataset)
    local snapshot_list = {}
    
    for snap in zfs_list_snapshots(dataset) do
        local snapshot = {}
        snapshot['name'] = snap

        for idx, property in ipairs(snap_properties) do
            snapshot[property] = zfs_get_prop(snap, property)
        end

        table.insert(snapshot_list, snapshot)
    end

    return snapshot_list
end

args = ...
argv = args["argv"]

ret = list_snapshots(argv[1])

return ret