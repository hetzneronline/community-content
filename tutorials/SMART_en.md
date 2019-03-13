# S.M.A.R.T. self-monitoring, analysis and reporting technology
## Introduction

S.M.A.R.T. is a function of modern drives that allows them to monitor themselves and, where appropriate, send an error status to the Host controller.

## Installation

In Linux these functions can be controlled and the status read by using the program [smartctl](https://sourceforge.net/projects/smartmontools/). With Debian `apt-get` can simply be used to install S.M.A.R.T.

## Configuring and using S.M.A.R.T
Activating:

`smartctl -s on -d ata /dev/sda`

Querying:

```
smartctl -a -d ata /dev/sda
smartctl -A -d ata /dev/sda
```
Drive info:

`smartctl -i -d ata /dev/sda`

Drive health status:

`smartctl -H /dev/sda`

Drive capabilities:

`smartctl -c -d ata /dev/sda`


Self test:

`smartctl -t short -d ata /dev/sda`

Self test results:

`smartctl -l selftest -d ata /dev/sda`

Show errors (if any):

`smartctl -l error -d ata /dev/sda`


### NVMe
See: [Show the SMART log](https://wiki.hetzner.de/index.php/NVMe/en#Show_the_SMART_log)

## Conclusion
By now you should be able to read out your HDDs SMART-Values.