---
SPDX-License-Identifier: MIT
path: "/tutorials/deploy-img-to-cloud-server/de"
slug: "deploy-img-to-cloud-server"
date: "2025-01-31"
title: "Deployment einer `.img` Datei auf einem Cloud Server via Rescue-System"
short_description: "Dieses Tutorial zeigt, wie eine `.img`-Datei mit Hilfe des Rescue-Systems erfolgreich auf einem Cloud Server bereitgestellt werden kann."
tags: ["Cloud", "Rescue-System", "Linux"]
author: "tim.stich"
author_link: "https://github.com/T-stich"
author_img: "https://avatars.githubusercontent.com/u/83845082"
author_description: ""
language: "de"
available_languages: ["en", "de"]
header_img: "header-7"
cta: "cloud"
---

## Einführung 

In diesem Tutorial wird gezeigt, wie eine `.img`-Datei mit Hilfe des Rescue-Systems erfolgreich auf einem Cloud Server bereitgestellt werden kann.  
Dies ist notwendig, wenn ein eigenes Betriebssystem-Image auf dem Server installiert werden soll.

**Voraussetzungen**

- Ein Cloud Server
  - Aktiviertes Rescue-System
  - SSH-Zugang zum Server
- Ein Volume zum Zwischenspeichern der `.img` Datei (da die primäre Festplatte überschrieben wird)
- `.img` Datei eines Betriebssystems (alternativ `.qcow2`, siehe Schritt 3)
- Grundkenntnisse im Umgang mit der Linux-Konsole

## Schritt 1 - Rescue-System aktivieren und verbinden

* **Rescue-System aktivieren**
  
  1. An der Cloud-Konsole anmelden.
  2. Den gewünschten Server auswählen und das **Rescue-System** aktivieren.
  3. Bei Bedarf einen **SSH-Schlüssel** hinterlegen, um die Anmeldung zu erleichtern.
  4. Den Server neu starten, damit er im Rescue-Modus bootet.

<br>

* **Mit dem Server verbinden**
  
  Verbindung zum Rescue-System über SSH herstellen:
  
  ```bash
  ssh root@<deine_server_ip>
  ```
  
  Nach dem Login erscheint das Rescue-System-Banner.

## Schritt 2 - Zieldatenträger identifizieren und einhängen

Da die `.img`-Datei nicht direkt auf der primären Festplatte abgelegt werden kann, wird ein **Volume als Speicherort** verwendet.  
Der folgende Befehl zeigt die verfügbaren Volumes an:

```bash
lsblk
```

Beispielausgabe:

```
NAME    MAJ:MIN RM  SIZE RO TYPE MOUNTPOINTS
sda       8:0    0  76G  0 disk 
sdb       8:16   0  40G  0 disk 
```

Hier ist `sda` die **primäre Festplatte**, die überschrieben wird, während `sdb` das **Volume für die `.img`-Datei** ist.

### Datenträger manuell einhängen

Im **Rescue-System** werden zusätzliche **Volumes standardmäßig nicht automatisch gemountet**.  
Nun wird ein **Mount-Verzeichnis** angelegt und das Volume eingehängt:

```bash
mkdir -p /mnt/vol1
mount /dev/sdX /mnt/vol1
```

*Dabei wird `/dev/sdX` durch den Volume-Namen (z. B. `/dev/sdb`) ersetzt.*

Falls unklar ist, welches Volume verwendet werden soll, kann die Partitionstabelle überprüft werden:

```bash
fdisk -l
```

## Schritt 3 - Image-Datei bereitstellen

Falls das hochgeladene oder heruntergeladene Image im `.qcow2`-Format vorliegt, muss es konvertiert werden:

```bash
qemu-img convert -f qcow2 -O raw /mnt/vol1/source.qcow2 /mnt/vol1/destination.img
```

Bei einem Image im `.img`-Format, können Sie aus drei Methoden wählen:

* **Methode 1:** Hochladen über SCP (Kommandozeile)
  
  Wenn sich die `.img` Datei lokal auf einem Rechner befindet, kann sie mit **SCP** hochgeladen werden:
  
  ```bash
  scp /path/to/image.img root@<your_server_ip>:/mnt/vol1/
  ```

<br>

* **Methode 2:** Hochladen per SFTP (FileZilla)
  
  Falls eine grafische Oberfläche bevorzugt wird, kann das Tool **FileZilla** verwendet werden:
  
  1. FileZilla starten
  2. Neue Verbindung einrichten:
     
     | Option             | Wert               |
     | ------------------ | ------------------ |
     | **Server**         | `<your_server_ip>` |
     | **Benutzername**   | root               |
     | **Passwort**       | `<your_password>`  |
     | **Port**           | 22                 |
     | **Verbindungstyp** | SFTP - SSH-Dateiübertragungsprotokoll |
  
  3. Die `.img`-Datei per **Drag & Drop** in das `/mnt/vol1/`-Verzeichnis auf den Server hochladen.

<br>

* **Methode 3:** Herunterladen mit `wget`
  
  Wenn die `.img`-Datei auf einem externen Server gehostet wird, kann sie direkt auf den Server heruntergeladen werden:
  
  ```bash
  wget <URL_zur_image.img> -O /mnt/vol1/image.img
  ```

## Schritt 4 - Image auf den Datenträger schreiben

* **Empfohlene Methode: `pv | dd`**
  
  Nun wird `pv | dd` verwendet, um die Fortschrittsanzeige zu verbessern:
  
  ```bash
  pv /mnt/vol1/image.img | dd of=/dev/sda bs=4M status=progress
  ```
  
  Falls `pv` nicht installiert ist, kann es mit folgendem Befehl installiert werden:
  
  ```bash
  apt update && apt install -y pv
  ```

<br>

* **Alternative: `dd` direkt**
  
  Falls `pv` nicht verwendet werden soll, kann das Image auch direkt mit `dd` geschrieben werden:
  
  ```bash
  dd if=/mnt/vol1/image.img of=/dev/sda bs=4M status=progress
  ```

## Schritt 5 - Neustart und Überprüfung des Systems

Nachdem das Image erfolgreich geschrieben wurde, kann der Server nun neu gestartet werden.  

Der folgende Befehl startet das System neu und bootet in das lokale Betriebssystem:

```bash
reboot
```

Nach dem Neustart kann überprüft werden, ob das System erfolgreich bootet und erreichbar ist.

### Fehlersuche bei Bootproblemen
Treten Bootprobleme auf oder bootet der Server nicht korrekt, sollte zunächst die Dokumentation des verwendeten Betriebssystems konsultiert werden.  
Gegebenenfalls ist eine Überprüfung des Bootloaders erforderlich.

Allgemeine Informationen zur Konfiguration des Bootloaders finden Sie hier:
- **GNU GRUB - Offizielle Dokumentation:**  
  [https://www.gnu.org/software/grub/manual/grub/grub.html](https://www.gnu.org/software/grub/manual/grub/grub.html)

- **UEFI Spezifikation und Dokumentation:**  
  [https://uefi.org/specifications](https://uefi.org/specifications)

## Hinweis zu alternativen Image-Formaten
Einige alternative Image-Formate können möglicherweise ebenfalls konvertiert und bereitgestellt werden.  
Es besteht jedoch das Risiko, dass sie aufgrund von Treiber- oder Hardwarekompatibilitätsproblemen nicht korrekt funktionieren.  

**Cloud Server basieren auf KVM-Virtualisierung**, daher sollte sichergestellt werden, dass das verwendete Betriebssystem KVM-kompatibel ist.

## Ergebnis

Das eigene Betriebssystem sollte nun auf dem Server installiert sein.

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

Signed-off-by: [submitter's name and email address here]

-->
