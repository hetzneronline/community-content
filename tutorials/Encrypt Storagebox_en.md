# Encrypt StorageBox
## Introduction
This article demonstrates one possible way to implement StorageBox and encrypt it va ECryptFS.

## Embedding StorageBox via SMB/CIFS 

First, Storagebox needs to be embedded as a file-system. Block oriented protocols like CIFS/SMB are a good choice here.

```
# mkdir /srv/storage
# mount -t cifs -o username=u123 //u123.your-storagebox.de/backup /srv/storage/
```

### Mounting automatically

To embedd the file-system automatically on boot, we need to create an entry in `/etc/fstab`. The password should be deposited in a file that is not accessbile for normal users, as otherwise it would have to be storedin plain in `/etc/fstab`. The Logindetails need to be in the following format (e.g. under `/root/.storagecred`):

```
 username=u123
 password=<password>
```

Now we can add the following entry in `/etc/fstab`:

`//u123.your-storagebox.de/backup /srv/storage cifs credentials=/root/.storagecred 0 0`

When using `systemd` it is recommended to use the `nofail` option so that your server may still boot when StorageBox is inaccessible.


`//u123.your-storagebox.de/backup /srv/storage cifs credentials=/root/.storagecred,nofail 0 0`

## ECryptFS manual installation

The standard-tools only allow pre-configured paths and one encrypted Directory per user, which can be decrypted with the user password. Therefore a manual configuration is neccessary.

Creating the directories: 

```
mkdir /srv/storage/data
mkdir /srv/storage/.data
mkdir /root/.ecryptfs
```

Generating the password:

```
# printf "%s\n%s" $(od -x -N 100 --width=30 /dev/random | head -n 1 | sed "s/^0000000//" | sed "s/\s*//g") "SecurePassword" | ecryptfs-wrap-passphrase /root/.ecryptfs/wrapped-passphrase
```

Loading the password into the keyring:

```
# printf "%s" "SecurePassword" | ecryptfs-insert-wrapped-passphrase-into-keyring /root/.ecryptfs/wrapped-passphrase
Passphrase:
Inserted auth tok with sig [9fb823671ebca685] into the user session keyring
```

Mounting Enryptfs while using the keyring-signature: 

```
# mount -i -t ecryptfs /srv/storage/.data/ /srv/storage/data/ -o ecryptfs_sig=9fb823671ebca685,ecryptfs_fnek_sig=9fb823671ebca685,ecryptfs_cipher=aes,ecryptfs_key_bytes=32,ecryptfs_unlink_sigs
```
* `ecryptfs_sig` - defines the data passphrase key signatur.
* `ecryptfs_fnek_sig` - defines the filename 
* `passphrase key signatur` can be left out if filenames should not be encrypted 
* `ecryptfs_key_bytes` - Encryption Key size (16, 24 or 32 bytes)
* `ecryptfs_unlink_sigs` - removes the password from the keyring, when the filesystem is dismounted 

## Mounting Ecryptfs semi-automatically

Adding a new entry in `/etc/fstab` while using your own key-signature:

```
# /etc/fstab
/srv/storage/.data/ /srv/storage/data/ ecryptfs ecryptfs_sig=9fb823671ebca685,ecryptfs_fnek_sig=9fb823671ebca685,ecryptfs_cipher=aes,ecryptfs_key_bytes=32,ecryptfs_unlink_sigs,noauto 0 0
```

The key needs to be loaded into the keyring before mounting:

```
printf "%s" "SecurePassword" | ecryptfs-insert-wrapped-passphrase-into-keyring /root/.ecryptfs/wrapped-passphrase
Passphrase:
Inserted auth tok with sig [9fb823671ebca685] into the user session keyring
```

Mounting without ecryptfs-Helper (`-i`)

`mount -i /srv/storage/data/`

## Conclusion
By now you should have installed Storagebox and encrypted the access with Ecryptfs