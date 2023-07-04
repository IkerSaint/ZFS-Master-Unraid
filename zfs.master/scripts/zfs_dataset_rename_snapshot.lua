local zfs_sync_rename_snapshot = zfs.sync.rename_snapshot

function rename_snapshot(dataset, old_snapshot_name, new_snapshot_name)  
    return zfs_sync_rename_snapshot(dataset, old_snapshot_name, new_snapshot_name)
end

args = ...
argv = args["argv"]

return rename_snapshot(argv[1], argv[2], argv[3])