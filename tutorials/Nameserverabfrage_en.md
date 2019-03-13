# Name server query

## Introduction

Even the simple entering of a URL in a browser or sending an email causes complex database queries in the DNS system. The following article describes the internal processes through which the home PC figures out the IP address of the required server.

## Process
Example: a mail server wants to send an email to `name@hetzner.de`.

### Mail server --> Name server
The mail server sends a request to its name server for the MX record for the domain `hetzner.de`. Since the name server has never had anything to do with Hetzner before it does not have a matching entry in its cache.

### Name server --> Dedicated server
The name server must first determine who is responsible for the top level domain (TLD) .de.

Each name server contains a list of the appropriate [name servers](http://www.root-servers.org/):

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

The name server now contacts one of these servers and asks for the MX records for `hetzner.de` (in anticipation of the authoritative name servers for the TLD .de).

`dig @199.7.83.42 hetzner.de mx`

The answer is:

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

The servers don't know who is resposible for the `hetzner.de`, but they do know that name servers of DeNIC are responsible for .de domains. Therefore, they respond with at least the name server addresses for the TLD .de.

### Name server --> Name server of the TLD .de

Now one of the .de name servers can be queried:

`dig @194.0.0.53 hetzner.de mx`

Answer:

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

The interesting thing here is that glue records for `ns1.your-server.de` and `ns3.second-ns.de` are given. This is only possible since the .de name servers are also responsible for these domains and the appropriate glue records were created for them.

The .de name servers don't know the MX records of the domain `hetzner.de`, just like the main name servers didn't either. However, in the answer above the name servers that are responsible for `hetzner.de` can be found.

### Name server --> Name server ns1.your-server.de

We choose the name server `ns1.your-server.de`:

`dig @213.133.106.251 hetzner.de mx`

Answer:

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

The responsible mail server is `lms.your-server.de`. The number 10 indicates the priority.

The name server was also kind enough to give us the IP address of `lms.your-server.de`, thereby sparing us the additional time to query for further information about the domain `your-server.de`.

### Mail server <-- Name server
Our name server now gives the mail server the correct MX records:

`lms.your-server.de    213.133.106.252    Priorität 10`

## Conclusion
The mail server will try to connect to `213.133.106.252` via SMTP.