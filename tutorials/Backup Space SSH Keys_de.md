# Backup Space SSH Keys
## Einführung
Wenn Sie SCP, SFTP, rsync oder BorgBackup nutzen, können Sie sich mittels SSH-Key-Authentifizierung ohne Passworteingabe einloggen.

Bitte beachten Sie, dass je nach genutztem SSH-Port des Backup-Accounts/Storage Box ein anderes Format für den Public-SSH-Key erforderlich ist: 
* Für den SSH-Port 22 (nur SFTP und SCP) wird ein Public-SSH-Key im RFC4716-Format benötigt
* für den SSH-Port 23 (SFTP, SCP, rsync und BorgBackup) ein gängiger Public-SSH-Key im OpenSSH-Format

Wenn Sie die Dienste über beide Ports nutzen möchten, muss der Public-SSH-Key in beiden Formaten hinterlegt werden.

Die Verwendung eines ed25519 Keys, wird auf SSH Port 22 nicht unterstützt.

Bitte beachten Sie das für jeden Sub-Account eine eigene `authorized_keys` Datei benötigt wird.


## Generieren eines SSH-Keys

Sie können mittels ssh-keygen ein neues SSH-Schlüsselpaar generieren:

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

Bitte beachten Sie, dass ssh-keygen mit den Standardeinstellungen einen bereits existierenden SSH-Key überschreibt! Sie können alternativ mit dem Parameter -f einen anderen Dateipfad angeben.

## Optional: In das RFC4716-Format konvertieren

Dies ist nur erforderlich, wenn Sie SCP oder SFTP über den SSH-Port 22 nutzen möchten.

Um den Public-SSH-Key in das korrekte Format zu konvertieren, führen Sie bitte folgenden Befehl aus:

`server> ssh-keygen -e -f .ssh/id_rsa.pub | grep -v "Comment:" > .ssh/id_rsa_rfc.pub`

Der Public-SSH-Key sollte nun wie folgt aussehen:

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

## authorized_keys-Datei erstellen

Binden Sie die benötigten Public-SSH-Keys in eine neue lokale authorized_keys-Datei ein.

Für SSH über den Port 23 (SCP, SFTP, Rsync und BorgBackup) fügen Sie den Public-SSH-Key im OpenSSH-Format hinzu:

`server> cat .ssh/id_rsa.pub >> storagebox_authorized_keys`

Falls Sie Ihren Public-SSH-Key im Schritt zuvor in das RFC4716-Format konvertiert haben, ergänzen Sie diesen ebenfalls:

`server> cat .ssh/id_rsa_rfc.pub >> storagebox_authorized_keys`

Sie können den Public-SSH-Key auch in beiden Formaten integrieren.


## authorized_keys hochladen

Laden Sie nun die generierte `authorized_keys`-Datei auf die Storage Box/Backup-Account hoch. Legen Sie dazu das Verzeichnis `.ssh` mit den Dateirechten 0700 (rwx------) an und erstellen Sie in diesem die Datei `authorized_keys` mit den Public-SSH-Keys und den Dateirechten 0600 (rw-------).

Dies kann zum Beispiel mit folgendem Befehl erfolgen:

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

Anschließend ist ein Login ohne Passwort möglich:

```
sftp <Benutzername>@<Benutzername>.your-storagebox.de
Connected to <Benutzername>.your-storagebox.de.
sftp> quit
```
Hinweis: Der Befehl `ssh-copy-id` kann nicht zum Hochladen des Public-SSH-Keys verwendet werden. 

## Fazit