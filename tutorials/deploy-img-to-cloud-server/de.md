---
SPDX-License-Identifier: MIT
path: "/tutorials/deploy-img-hetzner-rescue/de"
slug: "deploy-img-hetzner-rescue"
date: "2025-01-29"
title: "Deployment einer `.img` Datei auf einem Cloud-Server via Rescue-System"
short_description: "Dieses Tutorial zeigt, wie man eine `.img`-Datei auf einem Cloud-Server mithilfe des Rescue-Systems erfolgreich deployed."
tags: ["Cloud", "Rescue-System", "Linux", "Custom-OS"]
author: "tim.stich"
author_link: "https://github.com/T-stich"
author_img: "https://avatars.githubusercontent.com/u/83845082?v=4&s=50"
author_description: "Ich bin der Tim und ich bin auch dabei."
language: "de"
available_languages: ["en", "de"]
header_img: "header-deploy-img"
cta: "product"
---

## **Einführung**

In diesem Tutorial wird gezeigt, wie eine `.img`-Datei auf einem Cloud-Server mithilfe des Rescue-Systems erfolgreich deployed werden kann. Dies ist notwendig, wenn ein eigenes Betriebssystem-Image auf dem Server installiert werden soll.

Behandelt werden folgende Themen:

- Aktivieren des Rescue-Systems und Herstellen einer SSH-Verbindung
- Einbinden eines **Volumes**, um die `.img`-Datei darauf zu speichern
- Hochladen oder direktes Herunterladen der `.img`-Datei
- Deployment mit `pv | dd` zur Fortschrittsanzeige oder `dd` als Standardbefehl
- Neustart des Servers und Überprüfung des Boot-Prozesses

### **Voraussetzungen**

- Ein Cloud-Server
- Aktiviertes Rescue-System
- SSH-Zugang zum Server
- Ein Volume zur Zwischenspeicherung der `.img`-Datei (da die primäre Festplatte überschrieben wird)
- `.img`-Datei eines Betriebssystems (alternativ `.qcow2`, siehe Schritt 3)
- Grundkenntnisse zur Navigation mit der Linux-Konsole

---

## **Schritt 1 - Rescue-System aktivieren und verbinden**

### **Rescue-System aktivieren**

1. Auf der Cloud-Console anmelden.
2. Den gewünschten Server auswählen und das **Rescue-System** aktivieren.
3. Falls erforderlich, einen **SSH-Schlüssel** hinterlegen, um die Anmeldung zu erleichtern.
4. Den Server neu starten, damit er im Rescue-Modus bootet.

### **Mit dem Server verbinden**

Eine Verbindung per SSH mit dem Rescue-System herstellen:

```bash
ssh root@<your_server_ip>
```

Nach der Anmeldung erscheint das Rescue-System-Banner.

---

## **Schritt 2 - Zieldatenträger identifizieren und Volume einbinden**

Da das `.img`-File nicht direkt auf der primären Festplatte abgelegt werden kann, wird ein **Volume als Speicherort** verwendet.  
Um die verfügbaren Volumes anzuzeigen, wird folgender Befehl ausgeführt:

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

### **Volume manuell mounten**

Im **Rescue-System** werden zusätzliche **Volumes standardmäßig nicht automatisch gemountet**.  
Nun wird ein **Mount-Verzeichnis** erstellt und das Volume eingebunden:

```bash
mkdir -p /mnt/vol1
mount /dev/sdX /mnt/vol1
```

*Dabei wird `/dev/sdX` durch den Volume-Namen ersetzt (z. B. `/dev/sdb`).*

Falls unklar ist, welches Volume verwendet werden soll, kann die Partitionstabelle überprüft werden:

```bash
fdisk -l
```

---

## **Schritt 3 - Image-Datei bereitstellen**

### **Methode 1: Upload via SCP (Kommandozeile)**

Falls sich das `.img`-File lokal auf einem Rechner befindet, kann es mit **SCP** hochgeladen werden:

```bash
scp /Pfad/zur/image.img root@<your_server_ip>:/mnt/vol1/
```

### **Methode 2: Upload via SFTP (FileZilla)**

Falls eine grafische Oberfläche bevorzugt wird, kann das **FileZilla**-Tool verwendet werden:

1. **FileZilla starten**.
2. **Neue Verbindung einrichten:**
   - **Server**: `<your_server_ip>`
   - **Benutzername**: `root`
   - **Passwort**: `<your_password>`
   - **Port**: `22`
   - **Verbindungsart**: **SFTP - SSH File Transfer Protocol**
3. Die `.img`-Datei per **Drag & Drop** in das `/mnt/vol1/`-Verzeichnis auf den Server hochladen.

### **Methode 3: Download via `wget`**

Falls das `.img`-File auf einem externen Server gehostet wird, kann es direkt auf den Server heruntergeladen werden:

```bash
wget <URL_zur_image.img> -O /mnt/vol1/image.img
```

### **Falls das Image im `.qcow2`-Format vorliegt**

Falls das hochgeladene oder heruntergeladene Image im `.qcow2`-Format vorliegt, muss es konvertiert werden:

```bash
qemu-img convert -f qcow2 -O raw /mnt/vol1/source.qcow2 /mnt/vol1/destination.img
```

---

## **Schritt 4 - Image auf den Datenträger schreiben**

### **Empfohlene Methode: `pv | dd`**
Nun wird `pv | dd` verwendet, um die Fortschrittsanzeige zu verbessern:

```bash
pv /mnt/vol1/image.img | dd of=/dev/sda bs=4M status=progress
```

Falls `pv` nicht installiert ist, kann dies mit folgendem Befehl nachgeholt werden:

```bash
apt update && apt install -y pv
```

### **Alternative: `dd` direkt**
Falls `pv` nicht genutzt werden soll, kann das Image auch direkt mit `dd` geschrieben werden:

```bash
dd if=/mnt/vol1/image.img of=/dev/sda bs=4M status=progress
```

---

## **Schritt 5 - Neustart und Prüfung des Systems**

Nach dem erfolgreichen Schreiben des Images kann der Server nun neu gestartet werden.  

Mit folgendem Befehl wird das System neu gestartet und in das lokale Betriebssystem gebootet:

```bash
reboot
```

Nach dem Neustart kann geprüft werden, ob das System erfolgreich bootet und erreichbar ist.

### **Fehlersuche bei Boot-Problemen**  
Falls es beim Start Probleme gibt oder der Server nicht korrekt hochfährt, sollte zunächst die Dokumentation des verwendeten Betriebssystems konsultiert werden.  
Gegebenenfalls ist eine Überprüfung des Bootloaders erforderlich.

Allgemeine Informationen zur Bootloader-Konfiguration sind hier zu finden:
- **GNU GRUB – Offizielle Dokumentation:**  
  [https://www.gnu.org/software/grub/manual/grub/grub.html](https://www.gnu.org/software/grub/manual/grub/grub.html)

- **UEFI-Spezifikation und Dokumentation:**  
  [https://uefi.org/specifications](https://uefi.org/specifications)

### **Hinweis zu alternativen Image-Formaten**  
Einige alternative Image-Formate können möglicherweise ebenfalls konvertiert und deployed werden.  
Jedoch besteht das Risiko, dass sie aufgrund von Treiber- oder Hardwarekompatibilitätsproblemen nicht korrekt funktionieren.  

**Cloud-Server basieren auf KVM-Virtualisierung**, daher sollte sichergestellt werden, dass das verwendete Betriebssystem KVM-kompatibel ist.
