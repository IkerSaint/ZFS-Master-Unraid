succeeded = {}
failed = {}

function destroy_recursive(root, recursive)
    for child in zfs.list.children(root) do
        destroy_recursive(child)
    end

    for snap in zfs.list.snapshots(root) do
        err = zfs.sync.destroy(snap)
        if (err ~= 0) then
            failed[snap] = err
        else
            succeeded[snap] = err
        end
    end

    err = zfs.sync.destroy(root)

    if (err ~= 0) then
        failed[root] = err
    else
        succeeded[root] = err
    end
end

function destroy(root)
    err = zfs.sync.destroy(root)

    if (err ~= 0) then
        failed[root] = err
    else
        succeeded[root] = err
    end
end

args = ...
argv = args["argv"]

if (argv[2] == "1") then
    destroy_recursive(argv[1], argv[2])
else
    destroy(argv[1])
end

results = {}
results["succeeded"] = succeeded
results["failed"] = failed

return results