# DNS Reverse DNS
## Einführung
### Was ist ein Reverse-DNS-Eintrag?

Bei "normalen" DNS-Abfragen wird bei einem bekannten Hostnamen die zugehörige unbekannte IP-Adresse ermittelt. Diese benötigt ja z.B. der Browser zum Herstellen einer TCP-Verbindung zum richtigen Server nach der Eingabe einer Adresse in die URL-Zeile.

 `forum.hetzner.de ---> 213.133.106.33`
Bei Reverse-DNS funktioniert das genau anders herum: Es soll der zu einer IP-Adresse gehörende Hostname ermittelt werden.

`213.133.106.33 ---> dedi33.your-server.de`
Wie man sieht müssen die Hostnamen beim Forward- und Reverse-Lookup nicht übereinstimmen!

### Welchen Zweck haben Reverse-DNS-Einträge?

* Bei Traceroutes werden nicht nur IP-Adressen, sondern eben auch verständliche Hostnamen angezeigt. Die Fehlerdiagnose fällt wesentlich leichter
* Viele Mailserver akzeptieren eingehende Mails nur dann, wenn die IP-Adresse des Senders über einen Reverse-DNS-Eintrag verfügt
* In SPF-Tags (Sender Policy Framework; Technik zur Vermeidung von Spam-/Virenmails mit gefälschten Absendern) können Reverse-DNS-Einträge berücksichtigt werden

### Wie ist der technische Ablauf beim Reverse-Lookups durch den Nameserver?

Der detaillierte Ablauf bei Abfragen zu Reverse-DNS-Einträgen ist im Abschnitt "Reverse DNS-Lookup im Detail" beschrieben.

## Praxis

### Wie kann ich meiner IP-Adresse mehrere Namen zuweisen, da ich verschiedene Domains auf meinem Server hoste?

Dies ist nicht möglich. Für jede IP-Adresse kann es nur einen Hostnamen geben (von skurilen PTR-Round-Robin-Basteleien mal abgesehen).

Zudem ist es dem Web-Browser auch egal, welche Reverse-Einträge ein Rechner hat. Der Browser löst ja nur vorwärts (Name-->IP) auf und hier kann es selbstverständlich mehrere Namen geben, z.B. mehrere A-Records oder mehrere CNAME-Records, die auf einen A-Record verweisen.

Auch beim Betrieb von Mailservern werden nie mehrere Hostnamen pro IP-Adresse benötigt. Der Reverse-DNS-Eintrag sollte mit dem Hostnamen des SMTP-Servers (siehe Konfiguration des jeweiligen SMTP-Servers) übereinstimmen.

Werden mehrere Domains über eine IP-Adresse verwaltet (was ja eigentlich der Normalfall ist), dann kann ein neutraler Hostname verwendet werden, der mit den Kundendomains nichts gemeinsam hat. Spamfilter prüfen lediglich auf Übereinstimmung des Reverse-DNS-Eintrags mit dem im HELO genannten Hostnamen, dies hat aber nichts mit den Domainnamen oder Absenderadressen aus den übertragenen E-Mails zu tun.

Empfehlenswert sind folgende Vergaberichtlinien:

* Der Reverse-DNS-Eintrag sollte mit dem Hostnamen, den der Mailserver beim Verbindungsaufbau an der jeweiligen IP-Adresse nennt, übereinstimmen.
* Der Reverse-DNS-Eintrag sollte auch "vorwärts" auflösbar sein - und zwar zur selben IP-Adresse.
* Der Reverse-DNS-Eintrag sollte möglichst nicht wie ein automatisch generierter Name in der Form von "162-105-133-213-static.hetzner.de" aussehen, da dies von Spamfiltern oft nachteilig bewertet wird.
* Die Domain, aus der der Name gebildet wird, sollte natürlich existieren - also bitte keine reinen Phantasienamen angeben.


Beispiel für einen unproblematischen Eintrag:

```
srv01.example.com ---> 213.133.105.162
213.133.105.162 --> srv01.example.com
```

```
> telnet 213.133.105.162 25
220 srv01.example.com ESMTP ready
```

### Wenn ich an meinem Nameserver Reverse-Einträge (PTR) für meine IP's anlege, warum werden diese nicht übernommen?

Der eigene Nameserver ist nur für das "Vorwärts"-Auflösen zuständig.

Die zuständigen (authoritative) Nameserver für Reverse-Einträge betreibt der Eigentümer des IP-Adress-Blocks, also Hetzner.

Reverse-DNS-Einträge können ausschliesslich über die entsprechende [Robot-Funktion](https://robot.your-server.de/) erzeugt werden (Menüpunkt `Server` -> Klick auf den Server -> `IPs -> Klick auf das Textfeld rechts neben der gewünschten IP-Adresse).

### Der Reverse-DNS-Eintrag meines Rechners lautet anders als der im HELO-Befehl meines Mailservers genannte Hostname. Ist das ein Problem?

Beispiel: Der Reverse-DNS-Eintrag zur IP-Adresse eines Rechners lautet `www.example.com`, der Mailserver auf diesem Rechner meldet sich im HELO-Befehl aber als `mail.example.com`

Manche Spamfilter stufen Mails von solchen Absendern eher als "spammig" ein, daher sollten derartige Inkonsistenzen vermieden werden. Im obigen Beispiel könnte der Reverse-DNS-Eintrag und der Hostname des Mailservers beispielsweise `srv01.example.com` lauten, `www.example.com` könnte als CNAME-Eintrag (Alias) ohne sichtbare Auswirkungen auf `srv01.example.com` umgeleitet werden.

Ausfürliche Tests der DNS-Einträge können mit [DNSReport](http://www.dnsstuff.com/) durchgefürt werden.

### Wie kann ich eine große Anzahl Reverse-DNS-Einträge im Robot automatisiert anlegen oder verändern?
Hierzu kann der [Robot-Webservice](https://robot.your-server.de/doc/webservice/de.html#reverse-dns) genutzt werden.

## Reverse DNS-Lookup im Detail

### Inhalt
In diesem Artikel werden die genauen Abläufe bei Reverse DNS Abfragen erklärt.

Reverse DNS Abfragen ermitteln den Hostnamen zu einer IP-Adresse, "normale" Abfragen ermitteln dagegen die IP-Adresse zu einem Hostnamen (also anders herum).

Wichtig für diese Abfragen sind neben A-Records die sogenannten PTR-Records (Pointer; Reverse-Einträge) sowie die spezielle Domain `in-addr.arpa`.

### Ablauf
Beispiel: Wir wissen, dass `forum.hetzner.de` die IP-Adresse `213.133.106.33` hat, gilt das aber auch anders herum?

#### Client --> Nameserver
Der Arbeitsplatz sendet an den Nameserver eine Anfrage nach dem PTR-Record zur IP-Adresse `213.133.106.33`.

Dazu werden die 4 Teile der IP-Adresse in umgekehrter Reihenfolge platziert und die Domain `in-addr.arpa` angehängt:

```
   213.133.106.33
         |
         |
         V
   33.106.133.213
         |
         |
         V
   33.106.133.213.in-addr.arpa
   
```

Beispiel für die Abfrage mit dem Tool `dig`:

`dig @213.133.100.100 33.106.133.213.in-addr.arpa ptr`

#### Nameserver --> Rootserver

 Jetzt funktioniert die Abfrage wie in [DNS_Nameserverabfrage](https://wiki.hetzner.de/index.php/DNS_Nameserverabfrage) beschrieben, nur mit dem Typ PTR:

`dig @198.32.64.12 33.106.133.213.in-addr.arpa ptr`

Antwort:

```
;; QUESTION SECTION:
;33.106.133.213.in-addr.arpa.   IN      PTR

;; AUTHORITY SECTION:
213.in-addr.arpa.       86400   IN      NS      NS3.NIC.FR.
213.in-addr.arpa.       86400   IN      NS      SEC1.APNIC.NET.
213.in-addr.arpa.       86400   IN      NS      SEC3.APNIC.NET.
213.in-addr.arpa.       86400   IN      NS      SUNIC.SUNET.SE.
213.in-addr.arpa.       86400   IN      NS      AUTH00.NS.UU.NET.
213.in-addr.arpa.       86400   IN      NS      NS-PRI.RIPE.NET.
213.in-addr.arpa.       86400   IN      NS      TINNIE.ARIN.NET.

;; ADDITIONAL SECTION:
NS3.NIC.FR.             172800  IN      A       192.134.0.49
SEC1.APNIC.NET.         172800  IN      A       202.12.29.59
SEC3.APNIC.NET.         172800  IN      AAAA    2001:dc0:1:0:4777::140
SEC3.APNIC.NET.         172800  IN      A       202.12.28.140
SUNIC.SUNET.SE.         172800  IN      A       192.36.125.2
AUTH00.NS.UU.NET.       172800  IN      A       198.6.1.65
```

Interessant: 
Es wurden gleich vom Root-Server die zuständigen Nameserver für die Domain `213.in-addr.arpa` statt der erwarteten Infos für die TLD `arpa` genannt, eine Abfrage nach `in-addr.arpa` und `213.in-addr.arpa` kann man sich also in diesem Fall sparen.

#### Nameserver --> Nameserver für Domain "213.in-addr.arpa"

Wir wählen den `NS3.NIC.FR` für weitere Anfragen:

`dig @192.134.0.49 33.106.133.213.in-addr.arpa ptr`

und erhalten als Antwort:

```
;; QUESTION SECTION:
;33.106.133.213.in-addr.arpa.   IN      PTR

;; AUTHORITY SECTION:
106.133.213.in-addr.arpa. 172800 IN     NS      ns.second-ns.de.
106.133.213.in-addr.arpa. 172800 IN     NS      ns2.your-server.de.
```

Auch hier wurde die Hierarchie etwas übersprungen, der `NS3.NIC.FR` kennt direkt die zuständigen Nameserver der Domäne `106.133.213.in-addr.arpa`.

#### Nameserver --> Rootserver für ns2.your-server.de
Wir wählen den Server `ns2.your-server.de`. Dessen IP-Adresse muss nun erst mal ermittelt werden, da uns diese in der vorherigen Abfrage nicht genannt wurde:

`dig @198.32.64.12 ns2.your-server.de a`

Antwort:

```
;; QUESTION SECTION:
;ns2.your-server.de.               IN      A

;; AUTHORITY SECTION:
de.                     172800  IN      NS      A.NIC.de.
de.                     172800  IN      NS      F.NIC.de.
de.                     172800  IN      NS      C.DE.NET.
de.                     172800  IN      NS      L.DE.NET.
de.                     172800  IN      NS      S.DE.NET.
de.                     172800  IN      NS      Z.NIC.de.

;; ADDITIONAL SECTION:
A.NIC.de.               172800  IN      A       193.0.7.3
F.NIC.de.               172800  IN      AAAA    2001:608:6::5
F.NIC.de.               172800  IN      A       81.91.161.4
C.DE.NET.               172800  IN      A       208.48.81.43
L.DE.NET.               172800  IN      A       217.51.137.213
S.DE.NET.               172800  IN      A       193.159.170.149
Z.NIC.de.               172800  IN      AAAA    2001:628:453:4905::53
Z.NIC.de.               172800  IN      A       194.246.96.1
```
Na gut, dann also zu den Rootservern für die TLD "de".

#### Nameserver --> Namserver für die TLD "de" für ns2.your-server.de

Wir fragen mal `F.NIC.DE`:

`dig @81.91.161.4 ns2.your-server.de a`

Antwort:

```
;; QUESTION SECTION:
;ns2.your-server.de.            IN      A

;; AUTHORITY SECTION:
your-server.de.         86400   IN      NS      ns2.your-server.de.
your-server.de.         86400   IN      NS      ns.second-ns.de.
your-server.de.         86400   IN      NS      www.hos-ext1.de.
your-server.de.         86400   IN      NS      sql1a.your-server.co.za.

;; ADDITIONAL SECTION:
ns2.your-server.de.     86400   IN      A       213.133.106.251
ns.second-ns.de.        86400   IN      A       213.133.105.2
```

Perfekt, der Nameserver für die TLD "de" kennt über einen Glue-Record direkt die IP-Adresse des (ür die Reverse DNS Abfrage) gesuchten Nameserver: `ns2.your-server.de` hat die IP-Adresse `213.133.106.251`.

#### Nameserver --> Nameserver ns2.your-server.de

Da jetzt nach obigen Abstecher die IP-Adresse des `ns2.your-server.de` bekannt ist, fragen wir diesen Rechner gleich nach dem PTR-Record:

`dig @213.133.106.251 33.106.133.213.in-addr.arpa ptr`

Antwort:

```
;; QUESTION SECTION:
;33.106.133.213.in-addr.arpa.   IN      PTR

;; ANSWER SECTION:
33.106.133.213.in-addr.arpa. 86400 IN   PTR     dedi33.your-server.de.

;; AUTHORITY SECTION:
106.133.213.in-addr.arpa. 86400 IN      NS      ns2.your-server.de.
106.133.213.in-addr.arpa. 86400 IN      NS      ns.second-ns.de.

;; ADDITIONAL SECTION:
ns.second-ns.de.        86400   IN      A       213.133.105.2
ns2.your-server.de.     86400   IN      A       213.133.106.251
```

In der Antwort-Sektion der Rückantwort steht nun endlich der gesuchte Name: `dedi33.your-server.de`

#### Client <-- Nameserver

Der Nameserver teilt dem Client die Antwort `dedi33.your-server.de` mit.

### Ergebnis

Der Name zur IP-Adresse `213.133.106.33` lautet also `dedi33.your-server.de`.

Damit die Nameserver nicht bei jeder Zeile eines Traceroutes diese aufwendigen Abfragen erledigen müssen, werden die Antworten im Nameserver grundsätzlich zwischengespeichert (der A-Record zu `ns2.your-server.de` beispielsweise 24 Stunden = 86400 Sekunden). Dadurch können viele Abfragen bereits aus dem Cache der eigenen Nameserver beantwortet werden.

## Fazit
