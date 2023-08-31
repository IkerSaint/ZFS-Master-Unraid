succeeded = {}
failed = {}

function rollback(snap)
    errno, details = zfs.check.rollback(snap)

    if (errno ~= 0) then
        failed[snap] = errno
        return 
    end

    errno = zfs.sync.rollback(snap)

    if (err ~= 0) then
        failed[snap] = errno
    else
        succeeded[snap] = errno
    end
end

args = ...
argv = args["argv"]

results = {}
results["succeeded"] = succeeded
results["failed"] = failed

return rollback(argv[1])