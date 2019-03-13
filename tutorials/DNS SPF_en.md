# DNS SPF

## Introduction

SPF stands for Sender Policy Framework and is a technique for preventing email spam or bogus virus emails.

SPF incorporates a special entry into the zone file of the name server for the sender domain which guards against manipulation by unauthorised parties.

SPF does not prevent spam which originates from a domain that has been properly registered by the sender and also does not cover non-existant domains.

## Detailed Function

With SPF a specific TXT record is added zone file of the domain. This entry specifies the SMTP servers authorised for a domain. For incoming emails, mail servers can determine whether the sending SMTP server was allowed to send these emails by means of the sender domain and information from the SPF entry.

An SPF record looks, for example, like this:
```
  @		IN	TXT	"v=spf1 mx ip4:10.0.0.1 
  a:test.example.com -all"
```

* all computers which have MX records in this domain are valid
* additionally, emails from the computer with IP `<10.0.0.1>` are permitted
* emails from the computer "test.example.com" are also accepted
* all other mail servers are not authorised

## Simple Practical Example

You have a dedicated server at Hetzner and host on it your own domain `example.com`. Emails are solely sent and received via this server.

In this case, the following TXT record in your name server zone file is sufficient:

`  @		IN	TXT	"v=spf1 mx -all"`

* only the computer specified in the domain as mail server (=MX) is allowed to send emails with the sender address `@example.com`
* all other mail servers and/or virus infected servers are not allowed to use the domain `example.com` as sender

## Forwarding of Emails

Email forwarding is only supported if the sender address from the forwarding server is transcribed in such a way that the SPF entries for the original sender domain no longer interfere.

### Example A:

An order is received at `example.com`. The confirmation of the order is sent:

```
Sender:           sales@example.com
Sending server:   mail.example.com
Receiver:         client@cool-address.com
Receiving server: mail.cool-address.com     ---> SPF check "example.com": ok
```
The email arrives at the `cool-address.com` mail server. Let us suppose that this address is now forwarded on to `client@aol.com`:

```
Sender:           sales@example.com
Sending server:   mail.cool-address.com
Receiver:         client@aol.com
Receiving server: mail.aol.com              ---> SPF check "example.com": failed
```

The email is not delivered because the receiving AOL mail server establishes during the SPF check that the forwarding server `mail.cool-address.com`is not cleared to send emails from `@example.com`

The problem can be solved by SRS: SRS (Sender Rewriting Scheme) is a means of enabling forwarding mail servers to adjust and conform sender addresses.

### Example B with SRS:

The order confirmation is sent once more:

```
Sender:           sales@example.com
Sending server:   mail.example.com
Receiver:         client@cool-address.com
Receiving server: mail.cool-address.com     ---> SPF check "bigcompany.com": ok
```

Nothing has changed up to now. However the forwarding server now changes the sender:

```
Sender:           client+sales#bigcompany.com@example.com
Sending server:   mail.example.com
Receiver:         client@aol.com
Receiving server: mail.aol.com               ---> SPF check "cool-address.com": ok
```

In practise, the domain alone is not simply replaced by the new domain as this could be exploited by spammers for bounce attacks. An exact description of SRS procedure can be found [here](http://www.libsrs2.org/) under `I want to find out about SRS` (PDF document).

## Disadvantages of SPF
* unfortunately, SPF entries are not very widespread, therefore SPF filters show relatively few "matches"
* the SRS procedure important for email forwarding is similarly not very penetrant in practice
* a change of provider necessitates exact planning and adjustment of SPF entries during the relocation phase
* many users do not know anything about their SPF entries (or those of their company) and use non-authorised mail servers from a local provider. This naturally leads to bounces.

The disadvantages of SPF should not be overstated however, as SPF is an ideal way to protect one's own domain from abuse.

## Further Information

Very comprehensive information on SPF can be found at:

[SMTP+SPF, Sender Policy Framework](http://www.openspf.org/)

[SPF mechanics and syntax](http://www.openspf.org/SPF_Record_Syntax)

[SPF testing](http://www.dnsstuff.com/)

[SRS procedure](http://www.openspf.org/SRS)

## Conclusion
By now you should understand the principle of SPF and how to use it to forward mails.