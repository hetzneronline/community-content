# Backup Space SSH Keys
## Introduction
If you use SCP, SFTP, rsync or BorgBackup, you can log in using SSH key authentication without entering a password.

Important note: Depending on the SSH port of the backup account/storage box you use, you may need to use a specific format for the public SSH key. 
* For SSH port 22 (SFTP and SCP only), you are required to use a public SSH key in RFC4716 format
* For SSH port 23 (SFTP, SCP, rsync and BorgBackup) you are required to use a common public SSH key in OpenSSH format.

If you want to use the services over both ports, then you must store the public SSH key in both formats.

Using an `ed25519 key` is not supported on SSH port 22.

Please note that each sub-account requires its own `authorized_keys` file. 


## Generating SSH keys

You can use ssh-keygen to generate a new pair of SSH keys: 

```
server> ssh-keygen
Generating public/private rsa key pair.
Enter file in which to save the key (/root/.ssh/id_rsa):
Enter passphrase (empty for no passphrase):
Enter same passphrase again:
Your identification has been saved in /root/.ssh/id_rsa.
Your public key has been saved in /root/.ssh/id_rsa.pub.
The key fingerprint is:
cb:3c:a0:39:69:39:ec:35:d5:66:f3:c5:92:99:2f:e1 root@server
The key's randomart image is:
+--[ RSA 2048]----+
|                 |
|                 |
|                 |
|         .   =   |
|      . S = * o  |
|   . = = + + =   |
|    X o =   E .  |
|   o + . .   .   |
|    .            |
+-----------------+

```

Warning: With the default settings, using ssh-keygen will overwrite an existing SSH key! As an alternative, with the parameter -f you can specify a different file path. 

## Optional: Converting your key to RFC4716 format

This is only necessary if you would like to use SCP or SFTP via SSH port 22.

To convert the public SSH key into the correct format, enter the following command: 

`server> ssh-keygen -e -f .ssh/id_rsa.pub | grep -v "Comment:" > .ssh/id_rsa_rfc.pub`

The public SSH key should now look like the one below: 

```
server> cat .ssh/id_rsa_rfc.pub
---- BEGIN SSH2 PUBLIC KEY ----
AAAAB3NzaC1yc2EAAAABIwAAAQEAz+fh731CVfH3FPM0vK5hX7NT5HogdBEQ4ryGJIeVMv
mCQJWwrFtdWh1pXMyXsYzXq1xbjILgCZGn+H0qUBKopJaa/Pzsw5U0UyRgiFhU2k0eiHUq
pkiixTbHcLsCj3kjAv5i07wZJ/ot246hLQD1PtSQtcX7nHvhdhenOTGO+ccpM2KEdX1E64
eaTtO9Bf7X4OTXnRxS7tjYH9sls5DOunpvoIZLvbmcVw1+wMdJBXOAU6/tnkN5N3mYE4Hu
JjnRtBAI9MS9Tt3DNAp1K/udUHA6hfYf08fxYs9uwsCM793b7FczmVvHEIwIKszG7Jwiwo
Dqit4EExR8bNNCeD6D3Q==
---- END SSH2 PUBLIC KEY ----
```

## Creating authorized_keys file

nsert the required public SSH keys into a new local authorized_keys file.

For SSH over port 23 (SCP, SFTP, Rsync and Borg Backup), add the public SSH key in OpenSSH format: 

`server> cat .ssh/id_rsa.pub >> storagebox_authorized_keys`

If you converted your public SSH key to RFC4716 format in the previous step, add it as well: 

`server> cat .ssh/id_rsa_rfc.pub >> storagebox_authorized_keys`

You can also add the public SSH key in both formats. 


## Uploading authorized_keys

Now you need to upload the generated authorized_keys file to the storage box/backup account. To do this, create the directory .ssh with the file rights 0700 (rwx------) and create the file authorized_keys with the public SSH keys and the file rights 0600 (rw-------).

You can do this with the following command, for example: 

```
server> echo -e "mkdir .ssh \n chmod 700 .ssh \n put storagebox_authorized_keys .ssh/authorized_keys \n chmod 600 .ssh/authorized_keys" | sftp <Benutzername>@<Benutzername>.your-storagebox.de
u12345@u12345.your-storagebox.de's password:
Connected to u12345.your-storagebox.de'.
sftp> mkdir .ssh 
sftp>  chmod 700 .ssh
Changing mode on /.ssh
sftp>  put storagebox_authorized_keys .ssh/authorized_keys
Uploading storagebox_authorized_keys to /.ssh/authorized_keys
storagebox_authorized_keys                               100% 2916     2.0MB/s   00:00
sftp>  chmod 600 .ssh/authorized_keys
Changing mode on /.ssh/authorized_keys
```

Now you should be able to log in without a password: 

```
sftp <Benutzername>@<Benutzername>.your-storagebox.de
Connected to <Benutzername>.your-storagebox.de.
sftp> quit
```
Hinweis: Der Befehl `ssh-copy-id` kann nicht zum Hochladen des Public-SSH-Keys verwendet werden. 

## Conclusion