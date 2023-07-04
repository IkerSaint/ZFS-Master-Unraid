local zfs_sync_destroy = zfs.sync.destroy
local zfs_check_destroy = zfs.check.destroy
local zfs_list_children = zfs.list.children
local zfs_list_snapshots = zfs.list.snapshots

succeeded = {}
failed = {}

function destroy_recursive(root, force)
    for child in zfs_list_children(root) do
        destroy_recursive(child)
    end

    for snap in zfs_list_snapshots(root) do
        err = zfs_sync_destroy(snap)
        if (err ~= 0) then
            failed[snap] = err
        else
            succeeded[snap] = err
        end
    end

    err = zfs_sync_destroy(root)

    if (err ~= 0) then
        failed[root] = err
    else
        succeeded[root] = err
    end
end

args = ...
argv = args["argv"]

destroy_recursive(argv[1], argv[2])

results = {}
results["succeeded"] = succeeded
results["failed"] = failed

return results