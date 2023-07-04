local zfs_sync_rollback = zfs.sync.rollback
local zfs_check_rollback = zfs.check.rollback

function rollback(snap)
    errno, details = zfs_check_rollback(snap)

    if (errno ~= 0) then
        return errno
    end

    return zfs_sync_promote(snap)
end

args = ...
argv = args["argv"]

# This one seems a little bit spooky

return rollback(argv[1])