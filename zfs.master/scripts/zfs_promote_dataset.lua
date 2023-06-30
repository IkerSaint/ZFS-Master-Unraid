local zfs_sync_promote = zfs.sync.promote
local zfs_check_promote = zfs.check.promote



args = ...
argv = args["argv"]

ret = list_snapshots(argv[1], argv[2])

return ret