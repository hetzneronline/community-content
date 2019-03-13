# DNS Zonendatei
## Einführung
Eine Zonendatei ist Teil der Konfiguration des DNS.

## Zonendatei am Beispiel des Robot Standard-Template
Nachfolgende Zonendatei wurde für die Domain `example.com` erstellt:

```
$TTL 86400
@ IN SOA ns1.first-ns.de. postmaster.robot.first-ns.de. (
     2000091604  ; Serial
     14400       ; Refresh
     1800        ; Retry
     604800      ; Expire
     86400  )    ; Minimum

@           IN NS    ns1.first-ns.de.
@           IN NS    robotns2.second-ns.de.
@           IN NS    robotns3.second-ns.com.

localhost   IN A     127.0.0.1
@           IN A     1.2.3.4
www         IN A     2.3.4.5
www         IN AAAA  2001:db8::1
mail        IN A     2.3.4.5

loopback    IN CNAME localhost
pop         IN CNAME www
smtp        IN CNAME www
relay       IN CNAME www
imap        IN CNAME www
ftp    3600 IN CNAME ftp.anderedomain.de.

@           IN MX 10 mail

technik     IN A     5.6.7.8
technik     IN MX 10 technik

@           IN TXT   "v=spf1 mx -all"
```

### SOA-Record

```
$TTL 86400
@ IN SOA ns1.first-ns.de. postmaster.robot.first-ns.de. (
     2000091604  ; Serial
     14400       ; Refresh
     1800        ; Retry
     604800      ; Expire
     86400  )    ; Minimum
```

* Die TTL (Time To Live) der Zone beträgt 86400 Sekunden ($TTL 86400)
* Für die Internet-Domäne (das Zeichen @ ist Platzhalter für die Domäne `example.com` selbst) ist der Nameserver `ns1.first-ns.de` zuständig
* Der Punkt am Ende von `ns1.first-ns.de.` verhindert, dass der primäre Nameserver `ns1.first-ns.de.example.com` genannt wird
* Der Administrator hat die E-Mailadresse `postmaster@robot.first-ns.de` (der erste Punkt wird immer durch das @-Zeichen ersetzt)
* Die Zonendatei wurde zuletzt am 16.09.2000 geändert, dies war die 4. Änderung an jenem Tag
* Der sekundäre Nameserver übernimmt alle 4 Stunden (TTL = 14.400 Sekunden; Time To Live) Änderungen vom primären Nameserver
* Im Fehlerfall versucht der sekundäre Nameserver den Abgleich nach 30 Minuten (1800 Sekunden) erneut
* Sollte der sekundäre Nameserver nach 7 Tagen (604800 Sekunden) keinen Abgleich mit dem primären Nameserver geschafft haben, erklärt er die Domain für ungültig
* Die Einträge sind normalerweise 24 Stunden (86400 Sekunden) gültig, falls kein anderer Wert definiert wird
* Andere Nameserver merken sich "negative" Antworten, also Anfragen nach nicht existierenden Hosts ebenfalls 24 Stunden

### Nameserver

```
@           IN NS    ns1.first-ns.de.
@           IN NS    robotns2.second-ns.de.
@           IN NS    robotns3.second-ns.com.
```

Zuständig für die Nameserver sind `ns1.first-ns.de`, `robotns2.second-ns.de` und `robotns3.second-ns.com`

* Der Punkt am Ende der Zeilen verhindert auch hier die Suche nach `ns1.first-ns.de.example.com`, was in diesem Fall unsinnig wäre
* IP-Adressen sind in NS-Records nicht erlaubt (wird ein eigener Nameserver verwendet, dessen Hostname `ns1.example.com` lauten soll: Zusätzlich passenden A-Record definieren und [Glue](https://wiki.hetzner.de/index.php/Robot-Tutorial-Domainregistrierung#Glue-Records_.28.de.2F.at_Domains.29) bei der Domainregistation angeben bzw. die Nameserver vorher bei den Registraren [registrieren](https://wiki.hetzner.de/index.php/Robot-Tutorial-Domainregistrierung#Server_registrieren_notwendig.3F)).

### Hosts

```
localhost   IN A     127.0.0.1
@           IN A     1.2.3.4
www         IN A     2.3.4.5
www         IN AAAA  2001:db8::1
mail        IN A     2.3.4.5
```

* `localhost.example.com` wird zur Loopback-Adresse `127.0.0.1` aufgelöst
* Anfagen z.B. im Webbrowser nach `example.com` (ohne "www.") werden nach `1.2.3.4` aufgelöst.
* `www.example.com` hat die IP-Adresse `2.3.4.5` (IPv4) bzw. `2001:db8::1` (IPv6)
* Es existiert ein Host mit dem Namen `mail.example`, aber ob dieser auch der zuständige Mailserver ist, geht aus diesem Eintrag nicht hervor

### Aliase

```
loopback    IN CNAME localhost
pop         IN CNAME www
smtp        IN CNAME www
relay       IN CNAME www
imap        IN CNAME www
ftp    3600 IN CNAME ftp.anderedomain.de.
```

* `localhost.example.com`kann auch als `loopback.example.com` angesteuert werden
* `www.example.com` hat die zusätzlichen Namen `pop.example.com`, `smtp.example.com`, `relay.example.com` und `imap.example.com`
* `ftp.example.com` wird weitergeleitet zu `ftp.anderedomain.de`, da der Punkt am Ende die Auflösung nach `ftp.anderedomain.de.example.com` verhindert
* `ftp.example.com` hat eine Gültigkeit von nur 1 Stunde (3600 Sekunden), daher sind Änderungen in den Einträgen relativ schnell bei den Nameservern im weltweiten Internet bekannt. Wichtig: solange der sekundäre Nameserver noch die veralteten Werte publiziert, verzögert sich eine eventuelle Änderung der Daten, daher sollte evtl. auch die Refresh-Zeit im SOA-Record verkürzt werden

Hinweis: Ist für eine Subdomain bereits ein CNAME-Record definiert, können für diese Subdomain keine weiteren Record-Typen gesetzt werden.

### Mailserver

`@           IN MX 10 mail`

* Es gibt nur einen Mailserver und das ist `mail.example.com`
* IP-Adressen sind bei MX-Records nicht erlaubt
* CNAME's sind in MX-Records nicht erlaubt, nur Verweise auf A-Records
* Weitere Mailserver könnten in eine zusätzliche Zeile eingetragen werden, dies ist aber oft nicht sinnvoll
* Bei mehreren Mailservern würde der mit der geringeren Priorität (hier 10) bevorzugt verwendet

### "Subdomain"

```
technik     IN A     5.6.7.8
technik     IN MX 10 technik
```

* Es ist innerhalb der Zonendatei eine "Subdomain" angelegt, allerdings ohne Delegation an einen externen Nameserver.
* Für die Subdomain `technik.example.com` ist der Host `technik.example.com` zuständig, der zur IP-Adresse 5.6.7.8 auflöst.

TXT records

```
@           IN TXT   "v=spf1 mx -all"
```

* Für `example.com` existiert ein TXT record mit dem Inhalt `v=spf1 mx -all`
* Nutzung beispielsweise für [SPF](https://wiki.hetzner.de/index.php/DNS_SPF)

## Delegation einer Subdomain an eine neue Zone

Alternativ zum unter Subdomain beschriebenen Vorgehen, ist eine Delegation von Subdomains an einen anderen DNS-Server möglich.

Hinweis: Im [Robot](https://wiki.hetzner.de/index.php/Robot) ist es nicht möglich, DNS-Zonen für Subdomains anzulegen! Hier können Subdomains nur wie im Abschnitt "Subdomain" beschrieben definiert werden.

Beispiel: Zu Testzwecken soll eine Subdomain für die Abteilung "Technik" einer Beispielfirma angelegt werden, die dann für kurzfristige interne Tests etc. verwendet werden kann. Die DNS-Einträge der Subdomain sollen unabhängig von den Einträgen der Domäne `example.com` verwaltet werden (die evtl. ein grosser und unflexibler Provider hostet).

### Vorbereiten der Haupt-Domain
In der Zonendatei der Domain `example.com` werden folgende Einträge ergänzt:

```
technik     IN NS    ns.technik
ns.technik  IN A     5.6.7.8
```

Hier werden Nameserveranfragen nach beispielsweise `www.technik.example.com` an `ns.technik.example.com` weitergeleitet. Da dieser Hostname ja selbst von eben diesem Nameserver aufgelöst werden müsste, wird in der übergeordneten Domain ein "Glue-Record" eingetragen: `ns.technik.example.com --> 5.6.7.8.`

### Zonendatei für die neue Subdomain konfigurieren
Am neuen Nameserver muss nun noch eine Zonendatei für die neue Subdomain angelegt werden:

```
@ 86400 IN SOA ns1 admin (
     2000091604  ; Serial
     14400       ; Refresh
     1800        ; Retry
     604800      ; Expire
     86400  )    ; Minimum

@           IN NS    ns.technik
ns          IN A     5.6.7.8

@           IN MX 10 mail
mail        IN A     2.3.4.5

www         IN A     2.3.4.5
```

Der Administrator hat die Mailadresse "admin@technik.example.com".

* Der primäre Nameserver hat den Hostnamen "ns.technik.example.com".
* Es ist der einzige Nameserver (kein sekundärer Nameserver vorhanden).
* Er hat die IP-Adresse `5.6.7.8`
Ein Host namens `mail.technik.example.com` mit der IP-Adresse `2.3.4.5` existiert und ist gleichzeitig zuständig für den Mailempfang der Subdomain.
Es gibt einen weiteren Host mit Namen `www.technik.example.com`, der zu `2.3.4.5` auflöst.

## Fazit
In diesem Artikel wurde die Erstellung und Konfiguration einer Zonendatei für ihren Server erläutert.