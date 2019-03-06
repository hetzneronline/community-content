## Introduction

**Prerequisites**

* Hetzner Cloud API Token 

  * Visit Hetzner Cloud Console at [https://console.hetzner.cloud](https://console.hetzner.cloud), select your project, and create a new API Token.

* hcloud-ci is installed

  * Windows, FreeBSD
    	* Grab your pre-built binary from [Github](https://github.com/hetznercloud/cli/releases/latest)
  * Linux
    	* Using [Linuxbrew](http://linuxbrew.sh/)
      		* `brew install hcloud`
    	* Grab your pre-built binary from [Github](https://github.com/hetznercloud/cli/releases/latest)
  * MacOS
    	* Using [Homebrew](http://homebrew.sh/)
      		* `brew install hcloud`
    	* Grab your pre-built binary from [Github](https://github.com/hetznercloud/cli/releases/latest)
 
 
We will ...

 1. learn the basic usage
 2. add a context
 3. create a server
 4. list all servers
 5. delete a server
 6. create a volume and attach it to a server
 
 
 
## Steps

**Basic Usage**

After the installation, you should open a terminal and just type:

```bash
hcloud
```

You should see an overview of all available commands like, `server`, `volume` or `context`.
You can see the current version of your hcloud-cli installation with:

```bash
hcloud version
```

The most commands have some subcommands like:

```bash
hcloud server list
```

You can see an overview of all available subcommands by just typing the command. See a sample output for `hcloud server` below

```bashUsage:  hcloud server  hcloud server [command]Available Commands:  add-label          Add a label to a server  attach-iso         Attach an ISO to a server  change-type        Change type of a server  create             Create a server  create-image       Create an image from a server  delete             Delete a server  describe           Describe a server  detach-iso         Detach an ISO from a server  disable-backup     Disable backup for a server  disable-protection Disable resource protection for a server  disable-rescue     Disable rescue for a server  enable-backup      Enable backup for a server  enable-protection  Enable resource protection for a server  enable-rescue      Enable rescue for a server  list               List servers  poweroff           Poweroff a server  poweron            Poweron a server  reboot             Reboot a server  rebuild            Rebuild a server  remove-label       Remove a label from a server  reset              Reset a server  reset-password     Reset the root password of a server  set-rdns           Change reverse DNS of a server  shutdown           Shutdown a server  ssh                Spawn an SSH connection for the server  update             Update a serverFlags:  -h, --help   help for serverGlobal Flags:      --poll-interval duration   Interval at which to poll information, for example action progress (default 500ms)Use "hcloud server [command] --help" for more information about a command.
```

If you want to see all available parameters for a (sub-)command you can allways use the `--help`-flag, the output below is a sample output for `hcloud server list --help`:

```bash
Displays a list of servers.Output can be controlled with the -o flag. Use -o noheader to suppress thetable header. Displayed columns and their order can be set with-o columns=backup_window,datacenter (see available columns below).Columns: - backup_window - datacenter - id - ipv4 - ipv6 - labels - location - locked - name - protection - rescue_enabled - status - type - volumesUsage:  hcloud server list [FLAGS]Flags:  -h, --help                 help for list  -o, --output stringArray   output options: noheader|columns=...  -l, --selector string      Selector to filter by labelsGlobal Flags:      --poll-interval duration   Interval at which to poll information, for example action progress (default 500ms)
```

**Add a context**

Before you can start using the hcloud-cli you need to have a context available. A context is a specifc API Token from the Hetzner Cloud Console. We have choosed `context` as a reference to the `kubectl` for Kubernetes. So you can assume in our hcloud-cli a context is a project in the [Hetzner Cloud Console](https://console.hetzner.cloud).

You can add as many contexts as you want.

Create a hcloud-cli context with the command `hcloud context create` and add a free chooseable name.

```bash
hcloud context create my-super-project
```

This command will create a new context called `my-super-project`. After the command you will be promted to enter your API token. Keep in mind, the token is not visible while you are entering it. Press enter when you have entered the token. You should see a confirmation message `Context my-super-project created and activated`.

Now you should see an active context when you run

```bash
hcloud context list
```

The output should be similar to:
