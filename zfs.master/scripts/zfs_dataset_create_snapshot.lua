local zfs_sync_snapshot = zfs.sync.snapshot
local zfs_check_snapshot = zfs.check.snapshot

function snapshot(snap)
    errno, details = zfs_check_snapshot(snap)

    if (errno ~= 0) then
        return errno
    end
    
    return zfs_sync_snapshot(snap)
end

args = ...
argv = args["argv"]

## Add the recursive

return snapshot(argv[1])