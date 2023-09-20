succeeded = {}
failed = {}

function destroy_all(dataset)
    for snap in zfs.list.snapshots(dataset) do
        err = zfs.sync.destroy(snap)
        if (err ~= 0) then
            failed[snap] = err
        else
            succeeded[snap] = err
        end
    end
end

function destroy_one(snap)
    err = zfs.sync.destroy(snap)
    if (err ~= 0) then
        failed[snap] = err
    else
        succeeded[snap] = err
    end
end

args = ...
argv = args["argv"]

if (argv[2] == "1")
    destroy_all(argv[1])
else
    destroy_one(argv[1])
end

results = {}
results["succeeded"] = succeeded
results["failed"] = failed

return results