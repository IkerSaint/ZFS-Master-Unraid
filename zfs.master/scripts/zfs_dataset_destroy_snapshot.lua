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

function destroy_one(dataset, snap)
    err = zfs.sync.destroy(snap)
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