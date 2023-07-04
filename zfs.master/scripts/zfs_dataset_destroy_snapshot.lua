local zfs_sync_destroy = zfs.sync.destroy
local zfs_list_snapshots = zfs.list.snapshots

succeeded = {}
failed = {}

function destroy_all(dataset)
    for snap in zfs_list_snapshots(dataset) do
        err = zfs_sync_destroy(snap)
        if (err ~= 0) then
            failed[snap] = err
        else
            succeeded[snap] = err
        end
    end
end

function destroy_one(dataset, snap)
    err = zfs_sync_destroy(snap)
    if (err ~= 0) then
        failed[snap] = err
    else
        succeeded[snap] = err
    end
end

args = ...
argv = args["argv"]

if (argv[3] == "true")
    destroy_all(argv[1])
else
    destroy_one(argv[2])
end

results = {}
results["succeeded"] = succeeded
results["failed"] = failed

return results