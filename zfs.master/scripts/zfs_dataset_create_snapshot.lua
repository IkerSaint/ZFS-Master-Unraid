succeeded = {}
failed = {}

function snapshot(dataset, snap, recursive)
	snap_name = dataset .. "@" .. snap
	err = zfs.check.snapshot(snap_name)

    if (err ~= 0) then
        failed[snap_name] = err
    else
        err = zfs.sync.snapshot(snap_name)

        if (err ~= 0) then
            failed[snap_name] = err
        else
            succeeded[snap_name] = err
        end
    end

    if recursive then 
        for child in zfs.list.children(dataset) do
            snapshot(child)
        end
    end
end

args = ...
argv = args["argv"]

local dataset_name = argv[1]
local snap_name = argv[2]

snapshot(dataset_name, snap_name, argv[3])

results = {}
results["succeeded"] = succeeded
results["failed"] = failed

return results