# Recovery of LVM Volumes
## Introcution
If you see the warning below after a SSH login, then it's about time to recover the said logical partitions.

`*** /dev/md2 should be checked for errors ***`
`*** /dev/md1 should be checked for errors ***`

This article shows you how to repair them.

## Repair
Warning: The logical partitions should not be mounted. Using the command `mount`, double check which partitions have which file systems, and which partitions are mounted. Here's an excerpt:

```
mount
/dev/md1 on /boot type ext3 (rw)
/dev/md2 on / type ext3 (rw)
```

Write down the output of mount on your system for `md1` and `md2. Now start the [Hetzner Rescue System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System/en).

Unmount md1 and md2:

`umount /dev/md1`

The following command will detect the file system type:

`fsck -C0 -y /dev/md1`

If you already know the file system type of the partition (command "mount"), then you can directly name the file system type in the command (if necessary replace ext3 with the detected file system type):

`/sbin/fsck -t ext3 /dev/md1`

## Conclusion
By now you should have repaired the errors in your logical partitions 