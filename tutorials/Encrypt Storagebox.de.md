# StorageBox verschlüsseln

## Einführung

In diesem Artikel soll kurz eine Möglichkeit gezeigt werden, eine StorageBox einzubinden und transparent mittels ECryptFS zu verschlüsseln.

## StorageBox per SMB/CIFS einbinden

Zunächst muß die StorageBox als Dateisystem eingebunden werden. Hier bietet sich das CIFS bzw SMB Protokoll an, da es block-orientiert arbeitet.

```
# mkdir /srv/storage
# mount -t cifs -o username=u123 //u123.your-storagebox.de/backup /srv/storage/
```

### Automatisches Mounten

Um das Dateisystem beim Booten automatisch einzubinden, muß ein entsprechender Eintrag in der `/etc/fstab` angelegt werden. Das Password sollte in einer für normale Nutzer nicht zugänglichen Datei abgelegt werden, da es sonst im Klartext in der `/etc/fstab` stehen müsste. Die Zugangsdaten müssen in folgendem Format angegeben werden (z.b. unter `/root/.storagecred`):

```
 username=u123
 password=<password>
```

Anschließend kann man in der `/etc/fstab` den entsprechenden Eintrag hinzufügen:

`//u123.your-storagebox.de/backup /srv/storage cifs credentials=/root/.storagecred 0 0`

Bei Verwendung von `systemd` ist die Option `nofail` zu empfehlen, damit der Server noch bootet, falls die StorageBox nicht erreichbar sein sollte:

`//u123.your-storagebox.de/backup /srv/storage cifs credentials=/root/.storagecred,nofail 0 0`

## ECryptFS manuell anlegen

Die Standard-Tools nutzen fest-konfigurierte Pfade und erlauben nur ein verschlüsseltes Verzeichnis pro Nutzer, welches mit dem Login-Passwort entschlüsselt wird. Daher ist eine manuelle Konfiguration nötig.

Verzeichnisse anlegen 

```
mkdir /srv/storage/data
mkdir /srv/storage/.data
mkdir /root/.ecryptfs
```

Password generieren oder ausdenken 

```
# printf "%s\n%s" $(od -x -N 100 --width=30 /dev/random | head -n 1 | sed "s/^0000000//" | sed "s/\s*//g") "SecurePassword" | ecryptfs-wrap-passphrase /root/.ecryptfs/wrapped-passphrase
```

Passwort in den Keyring laden 

```
# printf "%s" "SecurePassword" | ecryptfs-insert-wrapped-passphrase-into-keyring /root/.ecryptfs/wrapped-passphrase
Passphrase:
Inserted auth tok with sig [9fb823671ebca685] into the user session keyring
```

Enryptfs unter Verwendung der Keyring-Signatur mounten 

```
# mount -i -t ecryptfs /srv/storage/.data/ /srv/storage/data/ -o ecryptfs_sig=9fb823671ebca685,ecryptfs_fnek_sig=9fb823671ebca685,ecryptfs_cipher=aes,ecryptfs_key_bytes=32,ecryptfs_unlink_sigs
```
* `ecryptfs_sig` - setzt die data passphrase key signatur.
* `ecryptfs_fnek_sig` - setzt die Dateinamen 
* `passphrase key signatur` - kann weggelassen werden, wenn die Dateinamen nicht verschlüsselt werden sollen
* `ecryptfs_key_bytes` - Größe des Encryption-Schlüssels (16, 24 oder 32 bytes)
* `ecryptfs_unlink_sigs` - entfernt das Passwort aus dem Keyring wenn das Dateisystem ausgehängt wird. 

## Ecryptfs halb-automatisch einhängen

Hinzufügen eines neuen Eintrags in der /etc/fstab unter Verwendung der eigenen Key-Signatur

```
# /etc/fstab
/srv/storage/.data/ /srv/storage/data/ ecryptfs ecryptfs_sig=9fb823671ebca685,ecryptfs_fnek_sig=9fb823671ebca685,ecryptfs_cipher=aes,ecryptfs_key_bytes=32,ecryptfs_unlink_sigs,noauto 0 0
```

Vor dem Mounten muß der Key in den Kernel Keyring geladen werden:

```
printf "%s" "SecurePassword" | ecryptfs-insert-wrapped-passphrase-into-keyring /root/.ecryptfs/wrapped-passphrase
Passphrase:
Inserted auth tok with sig [9fb823671ebca685] into the user session keyring
```

Mounten ohne den ecryptfs-Helper (`-i`)

`mount -i /srv/storage/data/`

## Fazit
Nun sollten sie Storagebox installiert und ihr Passwort mittels Ecryptfs abgesichert haben.
