succeeded = {}
failed = {}

function force_promote(ds)
    errno, details = zfs.check.promote(ds)

    if (errno == EEXIST) then
        assert(details ~= Nil)
        for i, snap in ipairs(details) do
            zfs.sync.destroy(ds .. "@" .. snap)
        end
    elseif (errno ~= 0) then
        failed[ds] = errno
        return
    end

    errno = zfs.sync.promote(ds)

    if (errno ~= 0) then
        failed[ds] = errno
    else
        succeeded[ds] = errno
    end
 end

function promote(ds)
    errno, details = zfs.check.promote(ds)

    if (errno ~= 0) then
        failed[ds] = errno
        return
    end

    errno = zfs.sync.promote(ds)

    if (errno ~= 0) then
        failed[ds] = errno
    else
        succeeded[ds] = errno
    end
end

args = ...
argv = args["argv"]

if (argv[2] == 'true') then
    force_promote(argv[1])
else
    promote(argv[1])
end

results = {}
results["succeeded"] = succeeded
results["failed"] = failed

return results