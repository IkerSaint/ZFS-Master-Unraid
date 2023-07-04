local zfs_sync_promote = zfs.sync.promote
local zfs_check_promote = zfs.check.promote

function force_promote(ds)
    errno, details = zfs_check_promote(ds)

    if (errno == EEXIST) then
        assert(details ~= Nil)
        for i, snap in ipairs(details) do
            zfs_sync_destroy(ds .. "@" .. snap)
        end
    elseif (errno ~= 0) then
        return errno
    end

    return zfs_sync_promote(ds)
 end

function promote(ds)
    errno, details = zfs_check_promote(ds)

    if (errno ~= 0) then
        return errno
    end

    return zfs_sync_promote(ds)
end

args = ...
argv = args["argv"]

if (argv[2] == 'true') then
    return force_promote(argv[1])
end

return promote(argv[1])