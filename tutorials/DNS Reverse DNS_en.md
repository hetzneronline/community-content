#DNS Reverse DNS

## Introduction

### What is a Reverse DNS Entry?
"Normal" DNS queries determine the unknown IP address for a known host name. This is required, for example, for a browser to establish a TCP connection to the correct server on entering an address into the URL line.

`forum.hetzner.de ---> 213.133.106.33`

Reverse DNS works in a completely different way; the host name belonging to an IP address is determined.

`213.133.106.33 ---> dedi33.your-server.de`

As you can see, the host names for Forward and Reverse Lookups do not need to match!

### What is the purpose of a Reverse DNS Entry?
* Trace routes show IP addresses and intelligible host names. This makes it considerably easier to diagnose errors.
* Many mail servers only accept incoming emails if the IP address of the sender has a Reverse DNS Entry.
* In SPF records ([Sender Policy Framework](https://wiki.hetzner.de/index.php/DNS_SPF); technology for the prevention of spam and virus emails from spoof senders) Reverse DNS Entries can be included.

### How are Reverse Lookups technically performed via name servers?

The detailed procedure for queries on Reverse DNS Entries is described in this article: "Reverse DNS-Lookup im Detail" (in German)

## Practice
### How can I assign several names to my IP address, if different domains are hosted on my server?
This is not possible. Only one host name is assigned to each IP address (except bizarre PTR Round Robin tinkering).

Furthermore, it is not important which Reverse Entries are on the server. The browser only resolves forwards (Name --> IP) and here there can, of course, be several names e.g. several A records or several CNAME records, which point to an A record.

It is not necessary to have several host names per IP address for operating mail servers. The Reverse DNS Entry should correspond to the host name of the SMTP server (please see the configuration of the respective SMTP server).

If several domains are administrated via an IP address (as is the usual case) a neutral host name can be used which has nothing in common with the customer domains. Spam filters simply check whether the Reverse DNS Entry matches the HELO host name. This has nothing to do with the domain names or sender addresses from transferred emails.

The following allocation guidelines are recommended:

* The Reverse DNS Entry should match the host name given by the mail server on building up the connection to the corresponding IP address.
* The Reverse DNS Entry should also resolve "forwards" - to the same IP address.
* The Reverse DNS Entry should not take the form of an automatically generated name such as `162-105-133-213-static.hetzner.de`, as this is often adversely assessed by spam filters.
* The domain, which the name derives from, should exist - please do not invent any names.

Example of an unproblematic entry:

```
srv01.example.com ---> 213.133.105.162
213.133.105.162 --> srv01.example.com
```

```
> telnet 213.133.105.162 25
220 srv01.example.com ESMTP ready
```

### If I set up Reverse Entries (PTR) for my IPs on my name server, why are these not accepted?
The own name server is only responsible for "forward" resolution.

The owner of the IPexample.com address block, ie. Hetzer, operates the authoritative name servers for Reverse Entries.

Reverse DNS Entries can only be created via the corresponding Robot function (menu item `Server` -> click on the server -> `IPs` -> click on the text field at right next to the desired `IP address`).

### The Reverse DNS Entry for my server is different to the host name specified in the HELO command of my mail server. Is this a problem?
Example: The Reverse DNS Entry for the IP address of a server is `www.example.com`. The mail server on this server logs into the HELO command, however, as `mail.example.com`

Some spam filters grade emails from such senders as "spam", therefore such kinds of inconsistency should be avoided. In the above example, the Reverse DNS Entry and the host name for the mail server could be `srv01.example.com` for example, `www.example.com` could be redirected as a CNAME entry (alias) without any visible effect on `srv01.example.com`.

Detailed tests of DNS Entries can be performed using [DNSReport](http://www.dnsstuff.com/).

### How can I automatically create or change a large number of Reverse DNS Entries in the Robot?

The [Robot-Webservice](https://robot.your-server.de/doc/webservice/en.html#reverse-dns) can be used for this.

## Conclusion