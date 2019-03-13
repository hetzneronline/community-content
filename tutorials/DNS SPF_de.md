# DNS SPF
## Einführung
SPF steht für Sender Policy Framework und ist eine Technik zur Vermeidung von Spam- oder Virenmails mit gefälschtem Absender.

Da bei SPF in der Zonendatei des Nameservers der Absenderdomain ein spezieller Eintrag eingebaut wird, ist gewährleistet, dass Unbefugte keine Manipulationen vornehmen können.

SPF verhindert aber kein Spam, für den der Versender eine eigene Domain ordnungsgemäss angemeldet hat und greift auch nicht bei nicht existierenden Domains.

## Funktion im Detail
Bei SPF wird ein Record vom Typ TXT in die Zonendatei der Domain eingetragen. In diesem Eintrag werden die berechtigten SMTP-Server zu einer Domäne genannt. Mailserver können bei eingehenden Mails anhand der Absenderdomäne und den Informationen aus dem SPF-Eintrag dieser Domäne feststellen, ob der sendende SMTP-Server überhaupt berechtigt war, diese Mails zu versenden.

Ein SPF-Record sieht beispielsweise so aus:

```
  @		IN	TXT	"v=spf1 mx ip4:10.0.0.1 
  a:test.example.com -all"
```

* es sind alle Rechner, für die MX-Records in dieser Domäne existieren, gültig
* zusätzlich sind Mails vom Rechner mit der IP `<10.0.0.1>` erlaubt
* Mails vom Rechner `test.example.com` werden ebenfalls akzeptiert
* alle anderen Mailserver sind Spam-/Virenschleudern und nicht autorisiert

## Einfaches Praxisbeispiel
Sie haben bei Hetzner einen dedizierten Rechner und hosten damit Ihre eigene Domain `example.com`. Mails werden ausschliesslich über diesen Rechner versendet und empfangen.

Es genügt in diesem Fall folgender TXT-Record in der Zonendatei Ihres Nameservers:

` @		IN	TXT	"v=spf1 mx -all"`

* es ist nur der Rechner, der in der Domain als Mailserver (=MX) eingetragen ist, berechtigt zum senden von Mails mit Absender `@example.com`
* allen anderen Mailservern bzw. virenverseuchten Rechnern ist es nicht gestattet, die Domain `example.com` als Absender zu verwenden

## Weiterleitungen von Mails
Weiterleitungen von E-Mails werden nur unterstützt, wenn die Absenderadresse im Envelope vom weiterleitenden Server so umgeschrieben wird, dass die SPF-Einträge der unsprünglichen Absenderdomain nicht mehr stören.

### Beispiel A:

Eine Bestellung ist bei `example.com` eingegangen. Die Bestellbestätigung wird versendet:

```
Absender:       vertrieb@example.com
Sendeserver:    mail.example.com
Empfänger:      kunde@coole-adresse.de
Empfangsserver: mail.coole-adresse.de     ---> SPF-Prüfung "example.com": ok
```

Sie landet beim Mailserver von `coole-adresse.de`. Wir nehmen mal an, dass diese Adresse auf `kunde@aol.com` weitergeleitet wird:

```
Absender:       vertrieb@example.com
Sendeserver:    mail.coole-adresse.de
Empfänger:      kunde@aol.com
Empfangsserver: mail.aol.com              ---> SPF-Prüfung "example.com": fehlgeschlagen
```

Die Mail wird also nicht zugestellt, weil der empfangende Mailserver von AOL bei der SPF-Prüfung feststellt, dass der weiterleitende Server `mail.coole-adresse.de` nicht für das Senden von `@example.com`-Mails freigegeben ist.

Das Problem lässt sich mit SRS umgehen: SRS (Sender Rewriting Scheme) ist ein Verfahren, mit dem weiterleitende Mailsever standardkonform Absenderadressen anpassen können.

### Beispiel B mit SRS:

Die Bestellbestätigung wird wieder versendet:

```
Absender:       vertrieb@example.com
Sendeserver:    mail.example.com
Empfänger:      kunde@coole-adresse.de
Empfangsserver: mail.coole-adresse.de      ---> SPF-Prüfung "grossefirma.de": ok
```

Bis hierher ist noch alles unverändert. Doch der weiterleitende Server ändert nun den Absender:

```
Absender:       kunde+vertrieb#example.com@coole-adresse.de
Sendeserver:    mail.coole-adresse.de
Empfänger:      kunde@aol.com
Empfangsserver: mail.aol.com               ---> SPF-Prüfung "coole-adresse.de": ok
```

In der Praxis wird allerdings nicht einfach nur die Domain durch die neue Domain ersetzt, da dies von Spammern gezielt für Bounce-Attacken ausgenutzt werden könnte. Eine genaue Beschreibung des SRS-Verfahrens findet man bei [libsrs2](http://www.libsrs2.org/) unter `I want to find out about SRS` (PDF-Datei).

## Nachteile von SPF
* leider haben sich die SPF-Einträge noch nicht sehr verbreitet, daher zeigen SPF-Filter noch relativ wenig Treffer
* das für Mail-Weiterleitungen wichtige SRS-Verfahren hat sich in der Praxis ebenfalls noch nicht sehr weit herumgesprochen.
* ein Providerwechsel erfordert genaue Planung und Anpassung der SPF-Einträge während der Umzugsphase
* viele Anwender wissen nichts von Ihren SPF-Einträgen (bzw. den SPF-Einträgen Ihrer Firma) und verwenden nicht autorisierte Mailserver Ihrer lokalen Einwahlprovider. Dies führt natürlich zu Bounces.

Die Nachteile von SPF sollten aber nicht überbewertet werden, SPF ist eine hervorragende Möglichkeit, sich gegen den Missbrauch der eigenen Domain zu schützen.

## Weitere Informationen

Sehr ausführliche Informationen zu SPF findet man an folgenden Stellen:

[SMTP+SPF, Sender Policy Framework](http://www.openspf.org/)

[SPF-Mechanismus und Syntax](http://www.openspf.org/SPF_Record_Syntax)

[SPF-Tester](http://www.dnsstuff.com/)

[SRS-Verfahren](http://www.openspf.org/SRS)

## Fazit 
Dieser Artikel hat ihnen hoffentlich einen Überblick über SPF und die Möglichkeiten zur Weiterleitung gegeben.