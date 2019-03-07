# Changing the TLER time
## Introduction
Some drives have a feature called Time Limited Error Recovery = TLER. This TLER variable determines the amount of time a drive is allowed to repair bad sectors or perform other repairs. If the TLER time is greater than the preset timeout period for the RAID controller, the RAID controller could mark the drive as defective, even though the drive is just trying to repair bad sectors. The TLER time can be configured with `smartctl`.

## Configuring TLER with an Adaptec controller
Boot into the [Hetzner Rescue System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System/en).

Activate the sg devices:

`modprobe sg`
The individual drives can be shown with this command:

`ls /dev/sg*`(e.g. sg1)

Change the TLER time:

`smartctl -d sat -l scterc,70,70 /dev/sg1`
## Configuring TLER with a 3ware controller
Boot into the [Hetzner Rescue System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System/en).

Identify the device:

`ls /dev/tw*`
(e.g. twa)

Identify the controller number:

`tw_cli show | grep ^c | cut -c 2`(e.g. 0)

In this example the device would be `/dev/twa0`

Show the drives using the controller number:

`tw_cli /c0 show | cut -c 2`
(e.g. 0)

Change the TLER time using the drive number and the device:
`smartctl -d 3ware,0 -l scterc,70,70 /dev/twa0`
## Configuring TLER with an LSI controller
Boot into the [Hetzner Rescue System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System/en).

Identify the device number:

`megacli -pdlist -aall | grep "Device Id:" | cut -c 12-`(e.g. 4)

Change the TLER time using the device number:

`smartctl -d megaraid,4 -l scterc,70,70 /dev/sda
## Conclusion
By now you should have removed the defective drive message.