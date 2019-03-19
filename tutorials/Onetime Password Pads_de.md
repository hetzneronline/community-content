# Onetime Password Pads
## Einführung

Die sogenannten Onetime Password Pads, OTPs sind eine gangbare Lösung zum Login aus ungeschützten Netzwerken, wie zum Beispiel einem Internetcafe, dem Arbeitsplatz oder anderen öffentlichen Terminals.

Der Vorteil des Prinzips besteht darin, dass das Login-Passwort nicht festgelegt ist, sondern bei jedem Loginversuch aus einem vom System gegebenen Hash-Wert und einer privaten Passphrase erzeugt werden muss. Dies setzt effektiv jedwegliche Keylogger ausser Kraft.

__Wichtig:__ Bitte trotzdem bei jedem Login den Host Key prüfen. Abweichender Host Key = mögliche Man in the middle-Attacke am Laufen, oder einfach nur ein übermässig neugieriger Netzwerkadministrator.

## HOWTO: OTP mit OPIE und PAM unter debian Linux

### Pakete installieren
Zuerst installieren wir die Pakete mit:
`apt-get install opie-client opie-server libpam-opie`

### sshd konfigurieren
Zur sshd Konfiguration müssen in `/etc/ssh/sshd_config` müssen folgende Konfigurations-Schlüssel gesetzt werden:

```
ChallengeResponseAuthentication yes
UsePAM yes
```

Sowie ist es gute Praktik, nur public keys für Login, auch an sicheren Terminals, zu verwenden:

`PasswordAuthentication no`

Diese Option jedoch nicht aktivieren, wenn keine pubkeys verwendet werden.

### pam konfigurieren

Die Konfigurationsdatei `/etc/pam.d/ssh` editieren und folgende Zeilen lokalisieren:

```
# Standard Un*x authentication.
@include common-auth
```

Investigation der Datei `common-auth` im selben Verzeichnis wird die generelle Passwort-Authentifikation über die normalen unix-Account-Passwörter (in `/etc/shadow`) offenbaren.

Diese Datei wird von allen Diensten verwendet, die pam zur Authentifizierung verwenden. Dies ist beispielsweise auch der imap-Login.
Bei Bedarf kann man diese Datei editieren, um die generellen Richtlinien für Authentifizierung über PAM zu verändern - dies ist von mir jedoch nicht empfohlen, da die wenigsten nicht-ssh-Clients (wie zB ein Mail-Programm) sich auf von normalem password-auth abweichende Methoden verstehen.

Daher belassen wir die common-auth wie sie ist, und kommentieren sie stattdessen in `/etc/pam.d/ssh` aus, um unsere eigenen Richtlinien einzufügen:

```
# Standard Un*x authentication.
# @include common-auth
# auth    sufficient      pam_unix.so
auth    sufficient      pam_opie.so
auth    required        pam_deny.so
```

Wenn man möchte, kann man die Zeile bezüglich pam_unix.so ebenso einfügen. Dies ermöglicht einen Doppel-Login für weitere Sicherheit (sowohl das Standard-Login-Passwort als auch der OTP-Key müssen übereinstimmen). Dies hat den Vorteil, dass eine verlorene Liste keine Kompromittierung des Systems bedeutet, da noch immer das unix-Passworts in Erfahrung gebracht werden muss - den Nachteil jedoch, dass das Passwort via keylogger mitgelesen werden kann und damit möglicherweise zum Login in nicht-OTP-geschützte Bereiche missbraucht werden kann!

## Als User einloggen und OTP-Keys generieren

```
elven@avariel ~ $ opiepasswd -cf
Adding elven:
Only use this method from the console; NEVER from remote. If you are using
telnet, xterm, or a dial-in, type ^C now or exit with no password.
Then run opiepasswd without the -c parameter.
Using MD5 to compute responses.
Enter new secret pass phrase:
Secret pass phrases must be between 10 and 127 characters long.
Enter new secret pass phrase:
Again new secret pass phrase:
ID elven OTP key is 499 av3573
KNIT BETA FROG ELSE LIVE OLGA
```

Dies wird nach einer passphrase fragen - daher stelle sicher, dass du an einem sicheren Terminal sitzt. Die Warnung der Konsole kann bei Hetzner ignoriert werden, da man im Normalfalle keinen physikalischen Zugang zum System (oder via KVM-Konsole) haben wird.
`opiepasswd` wird, neben einer Sequenznummer, einen sogenannten seed ausgeben, in der Form bbnnnn (hier: `av3573`).
Anhand der Sequenznummer (per Standard 499 - dies ist in Ordnung) und dieses seeds kann sich der Benutzer dann eine Liste an `onetime passwords` generieren:

```
elven@avariel ~ $ opiekey -n 10 499 av3573
Using the MD5 algorithm to compute response.
Reminder: Don't use opiekey from telnet or dial-in sessions.
Enter secret pass phrase:
490: OINT CHAT SKAT LAUD DOOM GIST
491: CADY HOWL DAB AIM HER TIC
492: CUE BOAR BEAU DISK HIP DAME
493: UN SAIL BY IRK GILT WORN
494: SULK HEAT FAD BAG SEAT ROOK
495: VEAL LIME JUST LYLE RUBE YELL
496: GIRD THY ROOK BARN NOR SWAY
497: RUIN FAIR THEY RISE TALL LURK
498: LESK HALO ELAN JIBE HATH WOOD
499: FAT NAIR OWNS SHAY TRY SHY
```

Der Parameter `-n` gibt die Anzahl an Schlüsseln an, die generiert werden soll. Pro login wird ein Schlüssel verbraucht. Diese Liste kann ausgedruckt werden und am Körper mitgetragen werden - doch Vorsicht: Nicht verlieren.

Als Alternative zu einer treeware-Liste kann auch ein Programm fuer Palm-PDAs verwendet werden, um S/Keys on the fly zu generieren: [pilOTP!](http://www.valdes.us/palm/pilOTP/)

### Testen

Nun kannst du, mit deinen Daten, dich einloggen:

```
elven@avariel .ssh $ ssh localhost
otp-md5 498 av3573 ext, Response: LESK HALO ELAN JIBE HATH WOOD
```

Wenn alles geklappt hat, landet man auf der shell.

## Fazit

Nun sollten sie den Zugang zu ihrem Server über OTPs ermöglicht haben.