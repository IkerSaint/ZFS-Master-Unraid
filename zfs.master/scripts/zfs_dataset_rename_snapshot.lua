succeeded = {}
failed = {}

function rename_snapshot(dataset, old_snapshot_name, new_snapshot_name)  
    errno = zfs.sync.rename_snapshot(dataset, old_snapshot_name, new_snapshot_name)

    if (errno ~= 0) then
        failed[dataset] = errno
    else
        succeeded[dataset] = errno
    end
end

args = ...
argv = args["argv"]

results = {}
results["succeeded"] = succeeded
results["failed"] = failed

return rename_snapshot(argv[1], argv[2], argv[3])