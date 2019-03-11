# Borbackup
## Einführung
BorgBackup (kurz: Borg) ist ein deduplizierendes Backupprogramm. Optional wird Kompression und authentifizierte Verschlüsselung unterstützt.

Das Hauptziel von Borg ist es eine effiziente und sichere Backup- Lösung anzubieten. Dank der Deduplizierung läuft der Backupprozess mit Borg sehr schnell ab und macht Borg auch für tägliche Backups sehr interessant. Je nach Datenmenge und Anzahl der Änderungen sind auch deutlich geringere zeitliche Abstände denkbar. Alle Daten werden bereits clientseitig verschlüsselt, was Borg auch für den Einsatz auf gehosteten Systemen interessant macht.

Mehr Informationen zu BorgBackup finden Sie [hier](https://www.borgbackup.org/)


## Installation

Es gibt drei Wege Borg zu installieren.

* Distribution Package
* Standalone Binary
* From Source 

In der [Borg Dokumentation](https://borgbackup.readthedocs.io/en/stable/installation.html) finden Sie sehr detaillierte Beschreibungen zu den unterschiedlichen Möglichkeiten. Deswegen gehen wir an dieser Stelle nicht weiter darauf ein.

Denken Sie bitte aus Kompatibilitätsgründen daran eine aktuelle Version von Borg zu verwenden! (>=1.0.9)

## Arbeitsablauf mit Borg
### Aktivierung von Borg und Konfiguration Ihrer Storagebox

Damit Borg auf Ihrer Storagebox aktiviert ist, müssen Sie den Dienst zuerst im Robot aktivieren. Gehen Sie dazu im Robot auf die Einstellungsseite Ihrer Storagebox und klicken bei `SSH-Support` auf aktivieren.

Da SSH auf der Storagebox nicht verfügbar ist, müssen sie beispielsweise SFTP für diesen Schritt verwenden.

Für Borg können Sie zum einen Passwort-Authentifizierung verwenden, empfohlen ist aber die Authentifizierung über den Public Key. Dies ist besonders auch dann empfohlen, wenn Sie die Backups mit Cronjobs automatisieren möchten.

Für die Verwendung von Borg wird ihr SSH Key nicht(!) im RFC4716 Format wie bei SFTP/SCP benötigt. Sie müssen Ihren normalen Public Key hinterlegen. Sollten Sie sowohl Borg, also auch SFTP/SCP verwenden, müssen beide Keys (RFC4716 Format und normal) hinterlegt werden.

Erstellen Sie in Ihrer Storagebox den Ordner `.ssh`und legen darin die Datei `authorized_keys` ab. Diese muss Ihren Public Key enthalten:

`ssh-rsa AAAAB3NzaC1yc2EAAAA.......rdj7eitNUjlIV8ovvAH/6SAsKD6`

Setzen Sie die Berechtigungen für den `.ssh` Ordner auf `0700` und für die `authorized_keys` auf `0600`.

Ihr Homeverzeichnis auf Ihrer Storage Box / Backup Space darf für Group und Others keine Schreibrechte haben, da sonst eine Authentifizierung über Keyfile nicht möglich ist. Standardmäßig ist dies so gesetzt, kann von Ihnen jedoch verändert werden.

Nun muss noch das Verzeichnis für das Backup-Repository in der Storagebox angelegt werden. Dazu legen Sie beispielsweise einen Ordner `backups` und darunter einen Ordner `server1` an. Der Ordner `server1` wird dann im nächsten Schritt als Borg Repository initialisiert. Unter Backups könnten Sie dann noch weitere Verzeichnisse für andere Server die gesichert werden sollen angelegt werden.

`/backups/server1`

### Borg Repository initialisieren

Wenn Sie einen SSH-Key verwenden, und dieser nicht der Standard-Key ist, haben Sie die Option über die Umgebungsvariable BORG_RSH den gewünschten Key anzugeben. Dabei können Sie den SSH Befehl angeben, den Borg verwenden soll. Der Standard wäre nur `ssh`: 

`$ export BORG_RSH='ssh -i /home/userXY/.ssh/id_ed25519'`

Beim initialisieren von Borg werden Sie nach einem Passwort für Ihr Repository gefragt. Nur mit diesem Passwort kann in Zukunft auf das Repository zugegriffen werden. Es wird also bei jeder Schreib- oder Leseoperation auf dem Repository benötigt. Das Passwort sollten sie sich gut merken, da es nicht wiederhergestellt werden kann! Damit Sie das Passwort nicht bei jedem Aufruf von Borg eingeben müssen, können Sie optional die Umgebungsvariable `BORG_PASSPHRASE` setzen.

`$ export BORG_PASSPHRASE="top_secret_passphrase"`

Als erstes muss das Borg Repository initialisiert werden. Das Repository ist nichts anders als ein Ordner auf Ihrer Storagebox, der von Borg mit ein paar grundlegenden Strukturen versorgt wird. Darin werden dann alle Backups gespeichert.

Der folgende Befehl initialisiert den Ordner `/backups/server1` auf Ihrer Storagebox.

`$ borg init --encryption=repokey ssh://u123456@u123456.your-storagebox.de:23/./backups/server1`

### Erstes Backup erstellen

Mit dem folgenden Befehl sichern Sie beispielsweise die Ordner `src` und `built` aus Ihrem Homedirectory in das Repository auf Ihrer Storagebox. Jedes Backup muss mit einem eindeutigen Namen versehen werden. Ein Timestamp bietet sich hierfür an:

`$ borg create ssh://u123456@u123456.your-storagebox.de:23/./backups/server1::2017_11_11_initial ~/src ~/built`

Borg create kann noch mit vielen weiteren Optionen aufgerufen werden. So kann beispielsweise der Fortschritt während oder eine Statistik zum Ende des Backups angezeigt werden. Außerdem können exclude patterns und einiges mehr angegeben werden.

Für weitere Informationen dazu besuchen Sie bitte die [Borg create Dokumentation](http://borgbackup.readthedocs.io/en/stable/usage/create.html).

### Folgebackups

Die weiteren Backups laufen identisch wie das erste ab. Dank der Deduplizierung, sind diese jedoch viel schneller und extrem speichereffizient.

Lediglich der Name des Backups muss beim Folgebackup angepasst werden, da dieser wie oben erwähnt eindeutig sein muss.

Versuchen Sie beim Folgebackup einfach mal die Option `--stats` um zu sehen, wie effizient die Speicherung ist.

`$ borg create --stats ssh://u123456@u123456.your-storagebox.de:23/./backups/server1::2017_11_12 ~/src ~/built`

### Weitere Borg Befehle wie: Archive auflisten, Backups wiederherstellen

Die Borg Dokumentation bietet eine sehr detaillierte Beschreibung aller Borg Befehle.

Am Besten starten Sie mit einem Blick in den [quick-start Abschnitt](https://borgbackup.readthedocs.io/en/stable/quickstart.html) und tauchen dann im [usage Abschnitt](https://borgbackup.readthedocs.io/en/stable/usage/general.html) tiefer in die Details ein.

Hier finden Sie dann viele Beispiele zum Auflisten von Archiven, oder wiederherstellen von Backups. Es ist beispielsweise auch möglich Diffs zwischen Backup anzuzeigen oder alte Backups zu löschen, um Speicherplatz zurück zu gewinnen.

## Automatisierung der Backups mit Cron

Legen Sie ein Verzeichnis für die Logdatei an.

`$ mkdir -p /var/log/borg`

Zunächst muss ein Skript erstellt werden, welches die Backups ausführt. Dies könnte wie folgt aussehen und unter `/usr/local/bin/backup.sh` liegen.

```
#!/usr/bin/env bash

##
## Setzten von Umgebungsvariablen
##

## falls nicht der Standard SSH Key verwendet wird können
## Sie hier den Pfad zu Ihrem private Key angeben
# export BORG_RSH="ssh -i /home/userXY/.ssh/id_ed25519"

## Damit das Passwort vom Repository nicht eingegeben werden muss
## kann es in der Umgepungsvariable gesetzt werden
# export BORG_PASSPHRASE="top_secret_passphrase"

##
## Setzten von Variablen
##

LOG="/var/log/borg/backup.log"
BACKUP_USER="u602"
REPOSITORY_DIR="server1"

## Hinweis: Für die Verwendung mit einem Backup-Account muss
## 'your-storagebox.de' in 'your-backup.de' geändert werden.

REPOSITORY="ssh://${BACKUP_USER}@${BACKUP_USER}.your-storagebox.de:23/./backups/${REPOSITORY_DIR}"

##
## Ausgabe in Logdatei schreiben
##

exec > >(tee -i ${LOG})
exec 2>&1

echo "###### Backup gestartet: $(date) ######"

##
## An dieser Stelle können verschiedene Aufgaben vor der
## Übertragung der Dateien ausgeführt werden, wie z.B.
##
## - Liste der installierten Software erstellen
## - Datenbank Dump erstellen
##

##
## Dateien ins Repository übertragen
## Gesichert werden hier beispielsweise die Ordner root, etc,
## var/www und home
## Ausserdem finden Sie hier gleich noch eine Liste Excludes,
## die in kein Backup sollten und somit per default ausgeschlossen
## werden.
##

echo "Übertrage Dateien ..."
borg create -v --stats                   \
    $REPOSITORY::'{now:%Y-%m-%d_%H:%M}'  \
    /root                                \
    /etc                                 \
    /var/www                             \
    /home                                \
    --exclude /dev                       \
    --exclude /proc                      \
    --exclude /sys                       \
    --exclude /var/run                   \
    --exclude /run                       \
    --exclude /lost+found                \
    --exclude /mnt                       \
    --exclude /var/lib/lxcfs

echo "###### Backup beendet: $(date) ######"
```


Jetzt sollte das Skript getestet werden, bevor der Cronjob erstellt wird.

```
$ chmod u+x /usr/local/bin/backup.sh
$ /usr/local/bin/backup.sh
```

Wenn alles fehlerfrei läuft, können Sie das Skript nun als Cronjob laufen lassen. Öffnen Sie dazu crontab als root:

`crontab -e`

Und fügen Sie die folgende Zeile hinzu um ein tägliches backup um 00.00 Uhr auszuführen.

`0 0 * * * /usr/local/bin/backup.sh > /dev/null 2>&1`

## Hinweise
### Full-System Backup

Wenn Sie auf Ihrem Linux Server ein Backup des kompletten Systems machen möchten, sollten Sie daran denken, dass nicht alle Dateien und Ordner in ein Backup gehören. Einige sollte per default ausgeschlossen werden.

Dafür verfügt der create Befehl über eine `--exclude` Option oder es kann eine Exclude-Datei angegeben werden. Die Verwendung wird in der [Borg create Dokumentation](https://borgbackup.readthedocs.io/en/stable/usage/create.html) genau beschrieben.

Hier ein beispielhafter Aufruf von `borg create` für ein Backup des kompletten Systems:

```
borg create -v --stats                   \
    $REPOSITORY::'{now:%Y-%m-%d_%H:%M}'  \
    /                                    \
    --exclude /dev                       \
    --exclude /proc                      \
    --exclude /sys                       \
    --exclude /var/run                   \
    --exclude /run                       \
    --exclude /lost+found                \
    --exclude /mnt                       \
    --exclude /var/lib/lxcfs
```

### Deduplizierung und Verlässlichkeit

Die Deduplizierung sorgt bei Borg Backups für einen sehr effizienten Speicherverbrauch und hohe Geschwindigkeit.

Man muss sich jedoch auch bewusst sein, dass dadurch jede Datei genau einmal gespeichert wird. Sollte eine Datei z.B. durch einen Festplattenfehler beschädigt sein, ist diese in allen Backups beschädigt.

Deshalb gehört es zum Best Practice sehr wichtige Daten in mehr als einem Repository zu speichern!

### Borg Version am Server

Zur Vermeidung von Kompatibilitätsproblemen ist es empfehlenswert, dass Sie an Ihrem Server und auf der Storage Box / Backup Space die gleiche Version von Borg Backup verwenden.

Für jedes Major-Update stehe eine Version zur Verfügung, die von uns regelmäßig und zeitnah aktualisiert wird. Die Version, die Sie auf Ihrer Storage Box / Backup Space verwenden möchten können Sie mit dem --remote-path Parameter von Borg angeben. Wird der Parameter nicht angegeben wird die neueste Version verwendet, die auf der Storage Box / Backup Space vorhanden ist.

Momentan sind die Versionen 1.0 und 1.1 installiert. Die neueste Version, also 1.1. ist die Default-Version. Falls Sie noch Version 1.0 verwenden möchten verwenden Sie:

```
$ borg init --encryption=repokey --remote-path=borg-1.0
ssh://u123456@u123456.your-storagebox.de:23/./backups/server1
```

borg-1.0 steht hierbei für die Version 1.0.x.

Im Changelog der BorgBackup Dokumentation finden Sie Informationen zu den Änderungen zwischen Versionen und zu möglichen Kompatibilitätsproblemen, falls welche auftreten können.
### Borg und SSH

BorgBackup nutzt SSH über Port 23. Der SSH Zugriff ist jedoch auf Borg beschränkt und ein Login ist nicht möglich!

### Borg und SFTP/SCP parallel mit Keyfile nutzen

Wie oben beschrieben, benötigt Borg den normalen Public Key, SFTP/SCP hingegen den SSH Key im RFC4716 Format. Sollten Sie sowohl Borg, also auch SFTP/SCP verwenden, müssen beide Keys (RFC4716 Format und normal) in der authorized_keys Datei hinterlegt werden.

### Borg Keyfile und Passwort

Das Passwort, das Sie für Ihr Borg-Repository wählen, wird bei uns nicht gespeichert und kann von uns nicht wiederhergestellt werden! Bewahren Sie es sicher auf.

Im Repokey mode (default), liegt der Repo-Key in der Repo-Config, also auf der Storage-Box. Es ist empfehlenswert, dass Sie ein Backup des Key bei sich speichern. Mehr Informationen dazu finden Sie in der [Borg Dokumentaion](https://borgbackup.readthedocs.io/en/stable/usage/key.html#borg-key-export). 

## Fazit
Hiermit haben sie mit Borgbackup eine platzsparende und automatisierte Software für ihre Backups installiert.