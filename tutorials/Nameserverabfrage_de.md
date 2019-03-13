# Nameserverabfrage
## Einführung
Bereits das simple Absenden einer URL im Browser oder das Verschicken einer E-Mail verursacht komplexe Datenbankabfragen im DNS-System. Der folgende Artikel erläutert die internen Abläufe, über die der Arbeitsplatzrechner an die IP-Adresse des gewünschten Rechners gelangt.

## Ablauf
Beispiel: Ein Mailserver will eine Mail an `name@hetzner.de` senden.

### Mailserver --> Nameserver
Der Mailserver stellt seinem Nameserver eine Anfrage nach dem MX-Record für die Domain `hetzner.de`. Da der Nameserver mit Hetzner noch nie was zu tun hatte, enthält dessen Cache keinen passenden Eintrag.

### Nameserver --> Root-Server
Der eigene Nameserver muss zunächst ermitteln, welcher fremde Nameserver für die Top Level Domain (TLD) .de zuständig ist.

Dazu enthält jede Nameserversoftware eine Liste mit den [Root-Servern](http://www.root-servers.org/):

```
.                         518400     IN  NS    l.root-servers.net.
l.root-servers.net.       3600000    IN  A     199.7.83.42
.                         518400     IN  NS    m.root-servers.net.
m.root-servers.net.       3600000    IN  A     202.12.27.33
.                         518400     IN  NS    a.root-servers.net.
a.root-servers.net.       3600000    IN  A     198.41.0.4
.                         518400     IN  NS    b.root-servers.net.
b.root-servers.net.       3600000    IN  A     192.228.79.201
.                         518400     IN  NS    c.root-servers.net.
c.root-servers.net.       3600000    IN  A     192.33.4.12
.                         518400     IN  NS    d.root-servers.net.
d.root-servers.net.       3600000    IN  A     199.7.91.13
.                         518400     IN  NS    e.root-servers.net.
e.root-servers.net.       3600000    IN  A     192.203.230.10
.                         518400     IN  NS    f.root-servers.net.
f.root-servers.net.       3600000    IN  A     192.5.5.241
.                         518400     IN  NS    g.root-servers.net.
g.root-servers.net.       3600000    IN  A     192.112.36.4
.                         518400     IN  NS    h.root-servers.net.
h.root-servers.net.       3600000    IN  A     128.63.2.53
.                         518400     IN  NS    i.root-servers.net.
i.root-servers.net.       3600000    IN  A     192.36.148.17
.                         518400     IN  NS    j.root-servers.net.
j.root-servers.net.       3600000    IN  A     192.58.128.30
.                         518400     IN  NS    k.root-servers.net.
k.root-servers.net.       3600000    IN  A     193.0.14.129
```

Der eigene Nameserver kontaktiert nun einen dieser Root-Server und bittet um die MX-Einträge für `hetzner.de (in Erwartung der zuständigen Nameserver für die TLD .de)

`dig @199.7.83.42 hetzner.de mx`

Die Antwort lautet:

```
;; QUESTION SECTION:
;hetzner.de.           IN   MX

;; AUTHORITY SECTION:
de.            172800   IN   NS     a.nic.de.
de.            172800   IN   NS     f.nic.de.
de.            172800   IN   NS     l.de.net.
de.            172800   IN   NS     n.de.net.
de.            172800   IN   NS     s.de.net.
de.            172800   IN   NS     z.nic.de.

;; ADDITIONAL SECTION:
a.nic.de.      172800   IN   A      194.0.0.53
f.nic.de.      172800   IN   A      81.91.164.5
l.de.net.      172800   IN   A      77.67.63.105
n.de.net.      172800   IN   A      194.146.107.6
s.de.net.      172800   IN   A      195.243.137.26
z.nic.de.      172800   IN   A      194.246.96.1
a.nic.de.      172800   IN   AAAA   2001:678:2::53
f.nic.de.      172800   IN   AAAA   2a02:568:0:2::53
l.de.net.      172800   IN   AAAA   2001:668:1f:11::105
n.de.net.      172800   IN   AAAA   2001:67c:1011:1::53
```

Die Root-Server kennen die Zuständigkeiten für hetzner.de nicht, wissen aber, dass für .de-Domains die Nameserver der DeNIC zuständig sind. Daher geben Sie als Antwort wenigstens die Nameserveradressen für die TLD .de zurück.

### Nameserver --> Nameserver der TLD .de

Jetzt kann einer der .de-Nameserver befragt werden:

`dig @194.0.0.53 hetzner.de mx`
Antwort:

```
;; QUESTION SECTION:
;hetzner.de.                  IN   MX

;; AUTHORITY SECTION:
hetzner.de.           86400   IN   NS     ns1.your-server.de.
hetzner.de.           86400   IN   NS     ns3.second-ns.de.
hetzner.de.           86400   IN   NS     ns.second-ns.com.

;; ADDITIONAL SECTION:
ns1.your-server.de.   86400   IN   A      213.133.106.251
ns1.your-server.de.   86400   IN   AAAA   2a01:4f8:d0a:2006::2
ns3.second-ns.de.     86400   IN   A      193.47.99.4
ns3.second-ns.de.     86400   IN   AAAA   2001:67c:192c::add:b3
```

Interessant hier: Es werden Glue-Records für `ns1.your-server.de` und `ns3.second-ns.de` ausgegeben. Dies funktioniert nur, weil die .de-Nameserver ebenfalls für diese Domains zuständig sind und die passenden Glue-Records zu diesen Domains angelegt wurden.

Die .de-Nameserver kennen die mx-Einträge der Domain `hetzner.de` allerdings genauso wenig wie die Rootserver vorher, doch in der Antwort findet man ja jetzt die zuständigen Nameserveradressen der Domain `hetzner.de`.

### Nameserver --> Nameserver ns1.your-server.de

Wir wählen den Nameserver `ns1.your-server.de`:

`dig @213.133.106.251 hetzner.de mx`

Antwort:

```
;; QUESTION SECTION:
;hetzner.de.                 IN   MX

;; ANSWER SECTION:
hetzner.de.           3600   IN   MX     10 lms.your-server.de.

;; AUTHORITY SECTION:
hetzner.de.           3600   IN   NS     ns1.your-server.de.
hetzner.de.           3600   IN   NS     ns.second-ns.com.
hetzner.de.           3600   IN   NS     ns3.second-ns.de.

;; ADDITIONAL SECTION:
lms.your-server.de.   7200   IN   A      213.133.106.252
ns1.your-server.de.   7200   IN   A      213.133.106.251
ns1.your-server.de.   600    IN   AAAA   2a01:4f8:d0a:2006::2
ns.second-ns.com.     7200   IN   A      213.239.204.242
ns.second-ns.com.     600    IN   AAAA   2a01:4f8:0:a101::b:1
ns3.second-ns.de.     600    IN   AAAA   2001:67c:192c::add:b3
ns3.second-ns.de.     86400  IN   A      193.47.99.4
```
Die zuständige Mailserver ist also `lms.your-server.de`. Die Zahl 10 gibt die Priorität an.

Der Nameserver war auch so freundlich, uns die IP-Adresse von `lms.your-server.de` gleich mitzuteilen, daher ersparen wir uns auch hier die zusätzliche Abfrage nach weiteren Information über die Domain `your-server.de`.

### Mailserver <-- Nameserver

Unser Nameserver übergibt nun an den Mailserver die gewünschten MX-Einträge:

`lms.your-server.de    213.133.106.252    Priorität 10`

## Fazit
Der Mailserver wird versuchen, mit `213.133.106.252` per SMTP-Protokoll Kontakt aufzunehmen.