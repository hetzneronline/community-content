# DNS Zone File

## Introduction
A DNS Zone file is a text file that describes a dns zone.

## Example of a zone file using the Hetzner Standard Template

The following zone file has been compiled for the domain `example.com`:

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

### SOA record

```
$TTL 86400
@ IN SOA ns1.first-ns.de. postmaster.robot.first-ns.de. (
     2000091604  ; Serial
     14400       ; Refresh
     1800        ; Retry
     604800      ; Expire
     86400  )    ; Minimum
The dns zone has a TTL (Time To Live) of 86400 seconds ($TTL 86400)
```

* The nameserver `ns1.first-ns.de` is responsible for the internet domain (the @ character is a placeholder for the domain `example.com` itself)
* The period at the end of `ns1.first-ns.de.` prevents the primary nameserver from being called `ns1.first-ns.de.example.com`
* The email address for the Administrator is `postmaster@robot.first-ns.de` (the first period is always replaced by the @ character)
* The zone file was last changed on 16.09.2000, this was the fourth change made on that day
* The secondary nameserver undertakes changes from the primary nameserver every four hours (TTL = 14,400 seconds; Time To Live).
* In the event of error, the secondary nameserver attempts synchronization again after 30 minutes (1800 seconds)
* Should the secondary nameserver not have created synchronization with the primary nameserver after 7 days (604800 seconds), it declares the domain invalid
* The entries are normally valid for 24 hours (86400 seconds), if no other value is defined
* Other nameservers remember "negative" answers, so requests for non-existant hosts are likewise 24 hours


### Nameservers

```
@           IN NS    ns1.first-ns.de.
@           IN NS    robotns2.second-ns.de.
@           IN NS    robotns3.second-ns.com.
```

* The `ns1.first-ns.de`, `robotns2.second-ns.de` and `robotns3.second-ns.com` are responsible for the nameservers
* The period at the end of the lines here also prevents the search for `ns1.first-ns.de.example.com`, which in this case would be nonsense
* IP addresses are not permitted in NS records (if an own nameserver is used, whose hostname should be `ns1.example.com`: define the appropriate A record and specify Glue when registering the domain and register the nameserver) in advance with the Registrars.


### Hosts

```
localhost   IN A     127.0.0.1
@           IN A     1.2.3.4
www         IN A     2.3.4.5
www         IN AAAA  2001:db8::1
mail        IN A     2.3.4.5
```

* `localhost.example.com` is resolved as loopback address `127.0.0.1`
* Enquiries, for example in the web browser, for `example.com` (without "www.") are resolved to `1.2.3.4`
* `www.example.com` has the IP address `2.3.4.5` (IPv4) and `2001:db8::1` (IPv6)
* A host called `mail.example.com` exists, but it is not clear from this entry whether this is also the responsible mail server

### Aliases

```
loopback    IN CNAME localhost
pop         IN CNAME www
smtp        IN CNAME www
relay       IN CNAME www
imap        IN CNAME www
ftp    3600 IN CNAME ftp.anderedomain.de.
```

* `localhost.example.com` can also be controlled as `loopback.example.com`
`www.example.com` has the following additional names `pop.example.com`, `smtp.example.com`, `relay.example.com` and `imap.example.com`
* `ftp.example.com` is forwarded as `ftp.anderedomain.de`, as the period at the end prevents resolution to `ftp.anderedomain.de.example.com`
* `ftp.example.com` is valid for one hour only (3600 seconds), therefore changes to the entries become known relatively quickly to the nameservers on the world-wide Internet. Important: as long as the secondary nameserver still publishes the old values, this results in a delay in possible changes to the data, therefore the Refresh time should also be shortened in the SOA record

Note: if a subdomain already has a CNAME record, then no further record types can be set for this subdomain.

### Mail servers
`@           IN MX 10 mail`

* There is only one mail server and this is `mail.example.com`
* IP addresses are not allowed for MX records
* CNAME's are not allowed in MX records, only as aliases for A records
* Further mail servers could be listed in an additional line, but this doesn't often make much sense
* With several mail servers, the one with the least priority (here 10) is given preference

### "Sub domain"

```
technik     IN A     5.6.7.8
technik     IN MX 10 technik
```

A "sub domain" is created within the zone file, however without being delegated to an external nameserver.
The host `technik.example.com` is responsible for the sub domain `technik.example.com`, which resolves to IP address 5.6.7.8.

### TXT records

`@ IN TXT "v=spf1 mx -all"`

* `example.com` has a TXT record `v=spf1 mx -all`

* This record type can be used for SPF (Sender Policy Framework)

## Delegation of a subdomain to a new zone

As an alternative to the procedure described under "Sub domain", a delegation of subdomains to another DNS server is possible.

Note: In Robot, it is not possible to create DNS zones for subdomains! Here subdomains can only be defined as described in the section "Sub domain".

For example, a subdomain for the "technology" department of an example company needs to be setup for short-term internal tests. The DNS records of the subdomain need to be independent of the entries for the domain `example.com` (hosted at a large and possibly inflexible provider).

### Preparing the main domain

In the zone file of the domain `example.com` the following entries are added:

```
technik     IN NS    ns.technik
ns.technik  IN A     5.6.7.8
```

This lets name server queries for, as an example, `www.technik.example.com` be passed on to `ns.technik.example.com`. Since this host name should be resolved even by this same name server in the parent domain a "glue record" is entered: `ns.technik.example.com -> 5.6.7.8.`

### Configuring the Zone File for the new Subdomain

On the new name server a zone file needs to be created for the new subdomain:

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

The administrator has the email address `admin@technik.example.com`.

* The primary name server has the hostname `ns.technik.example.com`.
* It is the only name server (there are no secondary name servers).
* It has the IP address `5.6.7.8`.
*  host `mail.technik.example.com` with the IP address of `2.3.4.5` exists and is also responsible for the receipt of the subdomain mail.
* There is another host named `www.technik.example.com` which resolves to `2.3.4.5`.

## Conclusion
By now you shold have created and configured your own dns zone file for your server. 