---
SPDX-License-Identifier: MIT
path: "/tutorials/analyzing-disk-usage-with-ncdu-on-linux-systems"
slug: "analyzing-disk-usage-with-ncdu-on-linux-systems"
date: "2023-12-21"
title: "Analyzing Disk Usage with ncdu on Linux Systems"
short_description: "This tutorial explains how to analyze disk usage in linux server using ncdu."
tags: ["linux", "server", "management", "ncdu"]
author: "Faleddo"
author_link: "https://github.com/faleddo"
author_img: "https://avatars3.githubusercontent.com/u/6542937"
author_description: ""
language: "en"
available_languages: ["en"]
header_img: "header-6"
cta: "cloud"
---

Disk space management is a critical task for Linux system administrators. As files and directories grow in number and size, keeping track of disk usage becomes essential to maintain system performance and ensure that critical processes have enough space to operate. One of the tools that can aid in this task is `ncdu` (NCurses Disk Usage). This tutorial provides an in-depth look at `ncdu`, detailing what it is, how to install it, how to use it, and an overview of its optional parameters.

## What is ncdu?

Ncdu, short for NCurses Disk Usage, is a command-line utility designed to help users and system administrators find and manage disk space usage on Linux systems. Unlike traditional disk usage tools such as `du`, `ncdu` provides an interactive interface, making it easier to navigate through directories and get a visual representation of space consumption.

The tool is built on the `ncurses` library, which provides a text-based graphical interface in the terminal. This allows `ncdu` to present a user-friendly way to explore directories, sort files and directories by size, and delete unnecessary files directly from the interface.

## How to Install ncdu

Before you can use `ncdu`, you must install it on your system. Most Linux distributions include `ncdu` in their default repositories, making installation straightforward using the system's package manager.

### On Debian/Ubuntu-based systems:

```bash
sudo apt update
sudo apt install ncdu

```

### On Red Hat/CentOS systems:

For Red Hat-based systems, you may need to enable the EPEL repository to find the `ncdu` package.

```bash
sudo yum install epel-release
sudo yum install ncdu

```

### On Fedora systems:

```bash
sudo dnf install ncdu

```

### On Arch Linux:

```bash
sudo pacman -S ncdu

```

After the installation is complete, you can start using `ncdu` to analyze disk usage.

## How to Use Ncdu

To begin analyzing disk usage with `ncdu`, simply run the command followed by the path you want to investigate. If no path is provided, `ncdu` will analyze the current working directory.

```bash
ncdu /path/to/directory

```

Once you execute the command, `ncdu` will scan the specified directory and present an interactive interface. The interface shows a list of files and subdirectories, along with their sizes and the percentage of disk space they occupy.

You can navigate through the list using the arrow keys:

- `Up/Down` to move through the file list.
- `Enter` to enter a directory.
- `n` to sort by name (ascending/descending).
- `s` to sort by size (largest/smallest).
- `g` to toggle between showing percentages, graph, or both.
- `d` to delete a file or directory (be cautious with this operation).

Press `q` to quit `ncdu` and return to the command line.

## Optional ncdu Parameters

`ncdu` offers several command-line options that can modify its behavior or alter its output. Here are some of the optional parameters that can be particularly useful:

- `1` -- Show a single level of directories; do not descend into subdirectories automatically.
- `x` -- Only count files and directories on the same filesystem as the specified directory. This is useful for not including mounted drives or network filesystems.
- `q` -- Quick mode. Skip directories that are not readable.
- `o filename` -- Export the scanned data to a file, which can be read later with `ncdu -f filename`.
- `r` -- Enable read-only mode, which disables the ability to delete files from within the interface.

For a full list of options, you can refer to the `ncdu` man page by typing `man ncdu` in the terminal.

## Advanced Usage and Tips

Beyond basic disk usage analysis, `ncdu` can be used in more advanced scenarios. For example, scan in remote servers, or check disk usage of another user.

1. **Scan remote directories**: ncdu can also scan remote directories over SSH. Use the following syntax: `ssh -C user@system ncdu -o- / | ./ncdu -f-`. Make sure you have SSH access to the remote host and ncdu installed on both the local and remote machines.
2. **Delete Files and Directories**. Be cautious with this, but `ncdu` allows you to delete files and directories from within its interface. Navigate to the file or directory and press `d` to delete it.
3. **Viewing Hidden Files**. By default, `ncdu` shows hidden files (those starting with a dot). If you want to ignore these, you can start `ncdu` with the `-I` option.

## Conclusion

`ncdu` is a powerful and user-friendly tool that can greatly simplify disk usage analysis on Linux systems. By providing an interactive interface and a variety of command-line options, it allows both novice and experienced system administrators to efficiently manage disk space. As with any system tool that can alter or delete files, it should be used with caution. Regular use of `ncdu`, combined with good disk management practices, can help ensure that your Linux systems run smoothly and remain free of space-related issues.

##### License: MIT

<!--
Contributor's Certificate of Origin
By making a contribution to this project, I certify that:
(a) The contribution was created in whole or in part by me and I have
    the right to submit it under the license indicated in the file; or
(b) The contribution is based upon previous work that, to the best of my
    knowledge, is covered under an appropriate license and I have the
    right under that license to submit that work with modifications,
    whether created in whole or in part by me, under the same license
    (unless I am permitted to submit under a different license), as
    indicated in the file; or
(c) The contribution was provided directly to me by some other person
    who certified (a), (b) or (c) and I have not modified it.
(d) I understand and agree that this project and the contribution are
    public and that a record of the contribution (including all personal
    information I submit with it, including my sign-off) is maintained
    indefinitely and may be redistributed consistent with this project
    or the license(s) involved.
Signed-off-by: Faleddo mail@faleddo.com
-->
