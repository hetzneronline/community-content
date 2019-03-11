# VMware ESXi
## Einführung
### Hardware
* VMware vSphere Hypervisor 5.x und 6.0 (ESXi) verwenden als Dateisystem mit dem Namen vmfs5 (vorher vmfs3), welches GPT verwendet und Festplatten bis zu 64 TiB (vorher 2 TB) unterstützt.
Software-RAID wird von ESXi nicht unterstützt.
* Die kostenlose Version von ESXi unterstützt seit Version 5.5 maximal 4 TB RAM (vorher 32 GB RAM).

### Netzkonfiguration
* VMware vSphere Hypervisor ist ein "Betriebssystem", welches auf die reine Virtualisierung ausgelegt ist und unterstützt daher weder NAT noch Routing. Es kann daher nur ein echtes Bridged Setup verwendet werden.
* Für die Verwendung eines Subnetzes ist die Einrichung einer Router-VM notwendig.

### Installation
Die Installation und Konfiguration von ESXi dauert ca. 20-30 Minuten. Sie können die Installation auch ohne Fachkenntnisse durchführen. Bitte beachten Sie die Anleitung [Installationsleitfaden](https://wiki.hetzner.de/index.php/VMware_ESXi#Installationsleitfaden) für die Installation von ESXi.

### Lizenz
Nach der Installation hat das System eine Testlizenz mit vollem Funktionsumfang, die nach 60 Tagen abläuft. Für den dauerhaften Betrieb wird entweder eine kostenpflichtige oder eine eigene kostenlose Lizenz benötigt. Die kostenfreie - und eingeschränkte - Lizenz erhält man nach der Registrierung bei VMware:

* [Lizenz für ESXi 5.x](https://my.vmware.com/web/vmware/evalcenter?p=free-esxi5)
* [Lizenz für ESXi 6.0](https://my.vmware.com/web/vmware/evalcenter?p=free-esxi6)

Das Eintragen der Lizenz erfolgt über den vSphere Client. Im Tab `Konfiguration` im Abschnitt `Software` unter dem Menüpunkt `Lizenzierte Funktionen`. Nach Auswahl des Punktes im Fenster rechts auf `Bearbeiten` klicken und dem Host den Lizenzschlüssel zuweisen.

![alt text](https://wiki.hetzner.de/images/2/2a/Lizenzzuweisung_mit_vSphereClient.jpg "Logo Title Text 1")


## Hetznerspezifische Anmerkungen
### Verbaute Hardware
Die Dell PowerEdge Modelle sind zertifiziert und vollständig kompatibel mit allen Versionen ab 5.0 (DX150, DX290) bzw. 5.5U3 (DX141, DX151 und DX291) bzw. 6.5 (DX152, DX292)

Alle anderen Modelle sind nicht von VMware zertifiziert, können jedoch in den meisten Fällen mit VMware vSphere/ESXi betrieben werden.

#### Kompatibilität
(Angaben ohne Gewähr)

```
Modell	vSphere/ESXi Version
AX50-SSD/AX60-SSD	ab 6.5a mit Zusatz-Intel-NIC
AX160	ab 6.5a
PX92	ab 6.5
PX91/PX121	ab 5.5 Update 1
PX90/PX120	ab 5.1
PX61	ab 5.5 Update 3 / 6.0 Update 2
PX61-NVMe	ab 6.0 Update 2
PX60/PX70	ab 5.5 Update 1
EX61/EX61-NVMe	ab 6.5
EX41/EX41S/EX51/EX42
ab 5.5 Update 3 / 6.0 Update 2
(evtl. Zusatz-NIC nötig)
EX40/EX60	5.0 - 5.1 Update 2, ab 5.5 mit Zusatz-NIC
EX6/EX6S/EX8/EX8S	ab 5.0
EX4/EX4S/EX10	ab 5.0 (mit Zusatz-NIC)
EQ4/EQ6/EQ8/EQ9	5.0 - 5.1 Update 2, ab 5.5 mit Zusatz-NIC
```

HINWEISE:

* Die angegebenen Modelle sollten mit der jeweiligen Version funktionieren. Neuere Versionen müssen jedoch nicht zwangsläufig kompatibel sein! Um sicher zu gehen, holen Sie bitte vorab diesbezüglich weitere Informationen ein (als gute Anlaufstelle kann hierbei zum Beispiel das offizielle Hetzner-Kundenforum dienen).
* Bei dem in ESXi 5.0 bis 5.1 Update 2 enthaltenen Realtek-Treiber handelte es sich nur um eine TechDemo, der nicht aktualisiert und in neueren Versionen entfernt wurde. Für einen reibungslosen Betrieb ist daher ein System mit Intel-Netzwerkkarte (PX60, PX91, PX121, DX151, etc.) zu empfehlen.
* Die Installation von vSphere 5.5 oder neuer auf den EX4, EX40, EX60 und einigen EX41/EX51 Modellen erfordert die Installation einer zusätzlichen kompatiblen Netzwerkkarte. Die Kosten für eine zusätzliche kompatible Netzwerkkarte finden Sie bitte hier: [Root Server Hardware](https://wiki.hetzner.de/index.php/Root_Server_Hardware#Sonstiges)
* Alternativ kann bei Versionen ab 5.5 das Installationsmediums um Treiber für Realtek Netzwerkkarten erweitert werden. Dies kann beispielsweise via [ESXi Customizer](http://www.v-front.de/p/esxi-customizer-ps.html) erfolgen.
* Eine Installation auf älterer Hardware der DS und X-Modelle ist nicht möglich.
* Bei Installation auf einem Server mit mehreren identischen Festplatten ist zu beachten, dass vSphere/ESXi die Platten potentiell in einer anderen Reihenfolge anzeigt als das BIOS. Sollte also nach Installation der Bootversuch von Festplatte mit einem schwarzen Bildschirm und blinkendem Cursor fehlschlagen, sollten die anderen Festplatten als Bootplatte durchprobiert werden.

### Netzkonfiguration
Für die Erreichbarkeit von mehreren ESXi im gleichen Subnetz, müssen Hostrouten auf die anderen System über das Gateway angelegt werden:

```
Host A
esxcfg-route -a <IP Host B> 255.255.255.255 <Gateway-ip>
```

```
Host B
esxcfg-route -a <IP Host A> 255.255.255.255 <Gateway-ip>
```

### Einzelne IP Adresse
Standardmäßig sind die IP Adressen an die MAC Adresse des Hosts gebunden. Man kann sich jedoch für die einzelnen zusätzlichen IP-Adressen mittels Robot MAC-Adressen zuweisen lassen. Diese muss man für die virtuellen Server dann fest konfigurieren und verwenden. Die Anfrage erfolgt über `Robot -> Server -> Tab IP`. Rechts neben der Zusatz-IP ist ein entsprechender Button.

![alt text](https://wiki.hetzner.de/images/b/ba/Esxi-mac-setzen.png "Logo Title Text 1")


### Subnetze
Für die Nutzung eines Subnetzes (sowohl für IPv4 als auch IPv6) unter ESXi benötigt man mindestens eine zusätzliche IP für eine Router-VM, da ESXi selbst nicht routen kann. Bei der Bestellung des Subnetz sollte angeben werden, dass ESXi verwendet wird und darum bitten dieses auf die zusätzlichen IP-Adresse zu routen.
WICHTIG Da IPv6-Subnetze auf Link-Lokal-Adressen (MAC-basierend) geroutet werden, kann IPv6 bei Einzel-IPs nur eingeschränkt (d.h. nur in einer VM) genutzt werden.

#### IPv4
Die Bestätigungs-Email des eingerichteten Subnetzes enthält z.B. folgende Angaben:

```
nachstehend finden Sie Ihre zusätzlichen IP-Adressen,
die dem Server 192.168.13.156 zugewiesen sind.

IP: 192.168.182.16 /28
Maske: 255.255.255.240
Broadcast: 192.168.182.31

Verwendbare IP-Adressen:
192.168.182.17 bis 192.168.182.30
```

Sie erhalten also NICHT für jede IP des Subnetzes eine seperate MAC.

#### IPv6
Alle Server werden automatisch mit einem /64 IPv6 Subnetz bereitgestellt. Welches IPv6 Subnetz Ihrem Server zugewiesen ist, erfahren Sie im Robot unter dem Tab `IPs`.

Wenn Sie Ihren Server vor Februar 2013 bestellt haben, dann können Sie das Subnetz kostenfrei im Robot bestellen und es wird automatisch aktiviert.

Das IPv6-Subnet wird standardmäßig auf die link-lokale IPv6-Adresse (die sich aus der Hardware/MAC-Adresse der Netzwerkkarte ergibt) geroutet. Hierbei handelt es sich um die MAC-Adresse, die auch (unabänderbar) an die Haupt-IPv4-Adresse geknüpft ist. Über den Robot kann man das Routing auf die IPv6 link-lokale Adresse einer virtuellen MAC (d.h. die an eine der Zusatz-IPv4-Adressen, falls vorhanden, geknüpft ist) umstellen. Hierfür gibt es im Robot das gleiche Symbol neben dem IPv6-Subnetz wie für die Beantragung der virtuellen MAC-Adressen. Das Host-System, also der ESXi selbst, erhält dadurch keine IPv6-Adresse. Dies ist auch nicht notwendig.

Um nun zu erreichen, dass man diese IPs auch direkt VMs zuweisen kann, bedarf es z.B. einer "Router-VM", die durch eine zusätzliche virtuelle NIC im zugewiesenen Subnetz ergänzt wird. Hierfür ist auf dem ESXi ein vSwitch erforderlich, in dem die VMs des Subnetzes liegen.

#### Hinweise
Hinweis betreffend VMware ESXi 4.1:

Als Netzwerkkartentyp für die Router-VMs sollte weder VMXNET2 noch VMXNET3 verwendet werden, da sonst die TCP Performance sehr schlecht sein kann. Als Workaround kann auch in der VM das LRO mittels `disable_lro=1` deaktiviert werden. Weitere Informationen zu diesem Bug gibt es [hier](http://www.vmware.com/support/vsphere4/doc/vsp_esxi41_vc41_rel_notes.html).

Nach einem Upgrade auf VMware ESXi 5 kann dieses Problem wieder auftreten. Um LRO unter ESXi 5 zu deaktivieren, sind folgende Schritte notwendig:

* Login auf dem ESXi Host mit dem vSphere Client.
* Auswählen des Hosts -> Konfiguration -> Software:Erweiterte Einstellungen
* Auswahl Netzwerk und etwas weiter als die Hälfte herunterscrollen
* Setzen der folgenden Parameter von 1 auf 0:

```
Net.VmxnetSwLROSL
Net.Vmxnet3SwLRO
Net.Vmxnet3HwLRO
Net.Vmxnet2SwLRO
Net.Vmxnet2HwLRO
```

Reboot des ESXi Hosts um die Änderungen zu aktivieren.

Falls es zu Verbindungsproblemen in Systemen mit Realtek-Netzwerkkarten kommt, kann dies unter Umständen durch Deaktivierung von Offloading und Aktivierung von Polling behoben werden. Im Gegenzug reduziert dies jedoch auch die Performance.

* checksum offload: deactivated
* segmentation offload: deactivated
* large receive offload: deactivated
* device polling: enabled

#### Vorbereitung im vSphere-Client
vSwitch anlegen unter `Bestandsliste -> Konfiguration -> Netzwerk` (hier verwendeter Name: `subnetz`)

![alt text](https://wiki.hetzner.de/images/3/30/Esxi-vswitch1.png "Logo Title Text 1")

![alt text](https://wiki.hetzner.de/images/c/ca/Esxi-vswitch2.png "Logo Title Text 1")

![alt text](https://wiki.hetzner.de/images/0/07/Esxi-vswitch3.png "Logo Title Text 1")

![alt text](https://wiki.hetzner.de/images/1/10/Esxi-vswitch4.png "Logo Title Text 1")


Der Router-VM eine zweite NIC hinzufügen, verwendetes Netzwerk: subnetz (der gerade angelegte vSwitch)

![alt text](https://wiki.hetzner.de/images/f/f8/Esxi-router-nic.png "Logo Title Text 1")

NIC der VM im Subnetz, verwendetes Netzwerk: `subnetz`

In der Konfigurationsansicht des Netzwerks sollte es dann wie folgt aussehen:

![alt text](https://wiki.hetzner.de/images/8/87/Esxi-subnet.png "Logo Title Text 1")


#### Konfiguration der Router-VM
Beispiel der `/etc/network/interfaces` auf der Router-VM

```
# The loopback network interface
auto lo
iface lo inet loopback
# The primary network interface
# WAN-NIC im VMnetwork
auto eth0
iface eth0 inet dhcp
# für das IPv6 Subnetz erfolgt die Konfiguration analog zu den anderen
# Virtualisierungen.
iface eth0 inet6 static
  address 2a01:4f8:61:20e1::2
  netmask 128
  gateway fe80::1
# LAN NIC im SubNet
auto eth1
iface eth1 inet static
  address     192.168.182.30
  broadcast   192.168.182.31
  netmask     255.255.255.240
# Der Präfix/Netzmaske kann/muss je nach Anzahl der gewünschten Netzsegmente
# angepasst werden
iface eth1 inet6 static
  address    2a01:4f8:61:20e1::2
  netmask    64
```
Beispiel der `/etc/network/interfaces` auf einer Linux-VM im Subnetz

```
# The loopback network interface
auto lo
iface lo inet loopback
# The primary network interface
auto eth0
iface eth0 inet static
  address 192.168.182.17
  netmask 255.255.255.240
  broadcast 192.168.182.31
  gateway 192.168.182.30
#
iface eth0 inet6 static
  address 2a01:4f8:61:20e1::4
  netmask 64
  gateway 2a01:4f8:61:20e1::2
```

Somit ist die Router-VM in beiden Netzen vertreten und die VMs im Subnetz können diese nun als Gateway verwenden. Abschließend muss natürlich noch das IP-Forwarding im Kernel aktiviert werden:

```
echo 1 > /proc/sys/net/ipv4/ip_forward
echo 1 > /proc/sys/net/ipv6/conf/all/forwarding
```

Damit dies nach einem Neustart wieder aktiviert wird, empfiehlt es sich die Option in der `/etc/sysctl.conf` einzutragen.

```
net.ipv4.ip_forward=1
net.ipv6.conf.all.forwarding=1
```

Die VMs sollten nun über die jeweiligen IPs direkt ansprechbar sein (z.B. via SSH)

## Installationsleitfaden
Bitte wählen Sie das [Rescue System](https://wiki.hetzner.de/index.php/Hetzner_Rescue-System) als Betriebssystem für den bestellten Server.

Falls gewünscht, können Sie einen 4-Port Hardware RAID Controller hinzu bestellen, denn das ESXi unterstützt keinen Software-RAID.

Nach der Bereitstellung des Servers bekommen Sie die Zugangsdaten von uns und Sie können anschließend die KVM-Konsole bestellen. Die Konsole unterstützt virtual media und Sie können dort die .ISO Datei mit der gewünschten ESXi Version einbinden. Weitere Informationen zur Bestellung und Benutzung der KVM-Konsole für die Installation von Ihrem Betriebssystem finden Sie auf die KVM-Konsole Seite.

Danach sollte man folgendes Bild sehen:

![alt text](https://wiki.hetzner.de/images/thumb/7/7e/Esxi-installed.png/794px-Esxi-installed.png "Logo Title Text 1")

Nach dem Reboot mit dem bei der Installation festgelegten Passwort einloggen. Dieses ist auch das Root Passwort für SSH sowie das Passwort für den VMware vSphere Client (benötigt Windows). Diesen kann man dann via Webbrowser herunterladen.

![alt text](https://wiki.hetzner.de/images/9/9b/Esxi-vsphere.png "Logo Title Text 1")

Nach Installation des Systems können im Robot bis zu drei zusätzliche einzelnen IP-Adressen bestellt werden. Die nötigen virtuellen MAC-Adressen können nach Zuteilung über den Hetzner Robot zugewiesen werden.

Die MAC-Adressen den virtuellen Servern mit den entsprechenden IP-Adressen mittels vSphere den Netzwerkkarten statisch konfigurieren. Damit funktioniert sogar DHCP aus dem Hetzner Netz!

Weitere Informationen zum ESXi und dessen Handhabung können der [offiziellen Webseite](http://www.vmware.com/products/esxi/) entnommen werden:

### Manuelle Installation von Updates

Für die Installation von Updates kann in der kostenfreien Version nur noch über die Konsole oder über VMware Go erfolgen. Da ein Update von mehreren hundert Megabyte mit einem DSL Anschluß sehr lange dauert, kann dies auch mit Hilfe der folgende Anleitung realisiert werden. Dies geschieht auf eigenes Risiko. Es wird keine Gewährleistung und Garantie für Korrektheit übernommen!

Vorrausetzung ist ein SSH aktivierter Zugang und das sich das System im Wartungsmodus befindet. Dieser kann mittels:

`vim-cmd hostsvc/maintenance_mode_enter`

aktiviert werden.

#### Update von vSphere 5.1 auf 5.5 mit Realtek-Treiber

Die Servermodelle mit Realtek-Netwerkkarten (z.B. EQ, EX4, EX40) können nicht direkt mit VMware vSphere ab Version 5.5 installiert werden, da hier der entsprechende Netzwerktreiber nicht mehr enthalten ist.

Alternativ kann der Server aber mit VMware 5.1 installiert und anschließend auf 5.5 aktualisiert werden.

Nach der Installation kann der Patch auf verschiedenen Wege installiert werden.

Eine Variante ist den Patch (`VMware-ESXi-5.5.0-1331820-depot.zip` bzw. `update-from-esxi5.5-5.5_update02.zip` für 5.5U2) selbst von der [Self-Support Seite](http://www.vmware.com/patchmgr/download.portal) herunterzuladen und auf den Server zu kopieren. Alternativ kann der Patch vom Host selbst heruntergeladen werden. Dazu müssen zunächst ausgehende HTTP Verbindungen erlaubt werden:

`esxcli network firewall ruleset set -e true -r httpClient`

Anschließend kann das Update auf den ESXi Host installiert werden:

```
esxcli software profile update -d 
https://hostupdate.vmware.com/software/VUM/PRODUCTION/main/vmw-depot-index.xml -p 
ESXi-5.5.0-20140902001-standard
```
Alternativ, auf eigenes Risiko, ohne Garantie oder Haftung kann der Patch auch von [hetzner.de](download.hetzner.de) bezogen werden. Diesen via `wget` herunterladen und lokal installieren.

```
esxcli software profile update -d 
/vmfs/volumes/datastore1/update-from-esxi5.5-5.5_update02.zip -p 
ESXi-5.5.0-20140902001-standard
```


#### Update von VSphere 5.0 auf 5.1

Als erstes muss der Patch VMware-ESXi-5.1.0-799733-depot.zip von der [Self-Support Seite](http://www.vmware.com/patchmgr/download.portal) oder, auf eigenes Risiko, ohne Garantie oder Haftung von [hetzner.de](download.hetzner.de) auf den ESXi Host heruntergeladen werden.

Nachdem alle VMs heruntergefahren sind und das System mittels `vim-cmd hostsvc/maintenance_mode_enter` in den Wartungsmodus versetzt wurde, kann das Bundle auf zwei Arten installiert werden. Mit dem folgenden Befehl wird das System aktualisiert und alle nicht im Update-Bundle enthaltenen Pakete werden entfernt. Dies entspricht etwa einer Neuinstallation.

```
esxcli software profile install -d 
/vmfs/volumes/datastore1/VMware-ESXi-5.1.0-799733-depot.zip -p 
ESXi-5.1.0-799733-standard
```

Alternativ können auch nur die Pakete aus dem Update-Bundle mit ihren neueren Versionen ersetzt werden, wobei die nicht enthaltenen Pakete erhalten bleiben:

```
esxcli software profile update -d 
/vmfs/volumes/datastore1/VMware-ESXi-5.1.0-799733-depot.zip -p 
ESXi-5.1.0-799733-standard
```

Abschließend muss das System neugestartet werden. Wenn die VMs nach dem Reboot das erste Mal angeschaltet werden, kann es passieren, dass eine Meldung erscheint, dass die VM kopiert oder verschoben wurde. Dies liegt daran, dass die UUIDs sich durch das Upgrade geändert haben. Hier kann man ohne Bedenken `VM wurde verschoben` auswählen (siehe [KB1010675](https://kb.vmware.com/s/article/1010675)).

#### Installation von Patches

Nachdem die Patches auf das System transferiert sind, können diese installiert werden. Wichtig ist hierbei den vollständigen absoluten Pfad anzugegeben, z.B:

```
esxcli software vib install --depot="/vmfs/volumes/datastore1/patches/ESXi510-201210001.zip"
Installation Result
Message: The update completed successfully, but the system needs to be rebooted for the changes to be effective.
Reboot Required: true
[...]
```

Nach dem Reboot muss der Wartungsmodus wieder verlassen werden.

`vim-cmd hostsvc/maintenance_mode_exit`

## Überwachung RAID-Controller

#### 3ware Controller

Es existiert sowohl ein CIM Provider als auch eine CLI. Die 64bit CLI für Linux kann ab der Version 9.5.2 verwendet werden.

Hinweis: 3ware Controller werden ab ESXi 5.0 nur über einen externen Treiber unterstützt.

#### Adaptec Controller

Hier muss der CIM Provider und die CLI (arcconf) händisch installiert werden. Voraussetzung ist auch eine aktuelle Version des Treibers. Eine englische Installationsanleitung findet sich auf der [Adaptec-Seite](http://download.adaptec.com/pdfs/installation_guides/vmware_esxi_41_cim_remotearcconf_installation_guide_3_2011.pdf)

* RAID-Treiber in der Version 5.2.1.29800
* Remote Arcconf
* Adaptec-CIM-Providers
Die Überwachung kann nach der Installation von remoteARCCONF über ein Windows/Linux System erfolgen.

`$ arcconf GETCONFIG 1 AD`

#### LSI Controller

LSI stellt einen sogenannten CIM/SMIS-Provider bereit. Nach dessen Installation wird auf der Hardware-Monitoring-Seite im vSphere-Client der Status des RAIDs angezeigt. Eine aktive Alarmierung ist aber nur in den kostenpflichtigen Versionen und bei Betrieb eines vCenter möglich.

Alternativ kann das Kommandozeilentool `megacli` installiert werden, welches auch zum Management des RAID-Controllers verwendet wird. Über ein Skript lassen sich so automatisiert Status-Informationen auslesen. Die Auswertung und etwaige Benachrichtung muss auf einem anderen Server erfolgen.

### Parallelbetrieb Onboard-Controller/Hardware-RAID
Bei der Installation "sieht" ESXi nur ein Typ Speichermedium. Also entweder den Onboard-SATA-Controller oder einen zusätzlichen RAID-Controller. Sind Festplatten an beiden angeschlossen, wird der Hardware-Controller bevorzugt und die Festplatten am Onboard-Controller bleiben unsichtbar. Durch manuelles Laden des entsprechenden Kernelmoduls können diese trotzdem genutzt werden.

`/sbin/vmkload_mod ahci`

Damit dieses Modul automatisch beim Start geladen wird, muss die Zeile in `/etc/rc.local` und `/sbin/auto-backup.sh` eingetragen werden.

## Hardwaretausch

### MAC Adresse ändern

Im Falle eines Hardware Tausches, im speziellen dem Motherboard, ist zu beachten das der ESXi Host seine ursprüngliche MAC Adresse beibehält. Dies hat zur Folge das der davor hängende Switch die Haupt IP nicht zum Server weiterleitet, da die MAC Adresse die der ESXi Host aussendet ihm unbekannt ist. Man muss die MAC Adresse über die ESXi shell neu setzen. Dazu hat der [Knowledge Base Artikel](http://kb.vmware.com/selfservice/microsites/search.do?language=en_US&cmd=displayKC&externalId=1031111) bei VMWare mehrere Ansätze. Der wohl eleganteste ist das der ESXi Host automatisch die neue MAC Adresse bei Plattformwechsel erkennt und verwendet. Dazu dient folgendes Kommando:

`esxcfg-advcfg -s 1 /Net/FollowHardwareMac`

Entweder führt man diesen Befehl vor dem Plattformwechsel aus und lässt dann die Hardware tauschen oder wenn der Hardware Tausch schon passiert ist hat man zwei Lösungswege:

* KVM-Konsole bestellen und ESXi Shell aktivieren und mit ALT + F1 in die Konsole wechseln, Befehl absetzen. ALT + F2 bringt einen wieder in die GUI zurück.
* Temporär die neue MAC Adresse dem Switch anlernen indem man in das Rescue System bootet und dann wieder zurück in den ESXi Host. Dies hat zur Folge das der ESXi Host nun eine begrenzte Zeit wieder über seine Haupt IP erreichbar ist. Allerdings ist die Zeitspanne abhängig davon wie lange der Switch den ARP Cache Eintrag für diese MAC Adresse nicht leert. Im Normalfall sollte dies allerdings für ein kurzes anmelden via ssh, Befehl absetzen reichen. Vorausgesetzt das man SSH Zugang aktiviert hat. Aber auch dies wäre noch konfigurierbar, da man jetzt auch wieder mit dem ESXi Client verbinden kann.

Am Ende beider Wege ist ein Neustart erforderlich. Dieser kann auch via Konsole initiiert werden:

`reboot`

Nachdem Neustart sollte die MAC Adresse korrekt gesetzt sein und dies kann man via ESXi shell verifzieren mit dem Kommando:

`esxcfg-vmknic -l`

Hier sollte jetzt die aktuelle MAC Adresse mit der Haupt IP in einer Zeile auftauchen.

## Fazit

Diese Anleitung hat ihnen hoffentlich einen Überblick über die Vorraussetzungen, die Installation und Verwendung von ESXi gegeben.