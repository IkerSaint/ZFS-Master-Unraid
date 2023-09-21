succeeded = {}
failed = {}

function force_promote(dataset)
    errno, details = zfs.check.promote(dataset)

    if (errno == EEXIST) then
        assert(details ~= Nil)
        for i, snap in ipairs(details) do
            zfs.sync.destroy(dataset .. "@" .. snap)
        end
    elseif (errno ~= 0) then
        failed[dataset] = errno
        return
    end

    errno = zfs.sync.promote(dataset)

    if (errno ~= 0) then
        failed[dataset] = errno
    else
        succeeded[dataset] = errno
    end
 end

function promote(dataset)
    errno, details = zfs.check.promote(dataset)

    if (errno ~= 0) then
        failed[dataset] = errno
        return
    end

    errno = zfs.sync.promote(dataset)

    if (errno ~= 0) then
        failed[dataset] = errno
    else
        succeeded[dataset] = errno
    end
end

args = ...
argv = args["argv"]

if (argv[2] == '1') then
    force_promote(argv[1])
else
    promote(argv[1])
end

results = {}
results["succeeded"] = succeeded
results["failed"] = failed

return results