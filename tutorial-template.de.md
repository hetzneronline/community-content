---
SPDX-License-Identifier: MIT
path: "/tutorials/tutorial-template/de"
slug: "tutorial-template"
date: "2025-01-01"
title: "Ein tolles Beispiel-Tutorial!"
short_description: "Das ist ein Beispiel-Tutorial mit Metadaten (die ersten paar Zeilen vor dem eigentlichen Tutorial). Bitte fülle so viel wie möglich selbst aus. Wenn du dir an einer Stelle nicht sicher bist, kannst du es leer lassen und der Community Manager wird es für dich anpassen. Die 'short_description' sollte bei dir nicht mehr als 160 Zeichen haben."
tags: ["Development", "Lang:Go", "Lang:JS"]
author: "Dein Name"
author_link: "https://github.com/....."
author_img: "https://avatars3.githubusercontent.com/u/....."
author_description: "Kurze Beschreibung über dich selbst."
language: "de"
available_languages: ["en", "de", "Ergänze hier alle Sprachen (ISO-639-1-Codes), in denen das Tutorial verfügbar ist"]
header_img: "header-x"
cta: "product"
---

## Einführung

> Die obenstehenden Metadaten werden von der Community verwendet, um das Tutorial zu kategorisieren und zu beschreiben. Diese müssen am Anfang von jedem Tutorial enthalten sein.

Beachte, dass alle Tutorials auf Englisch verfasst sein müssen. Wenn du zusätzlich eine deutsche Übersetzung bereitstellen willst, kannst du dieses Beispiel-Tutorial als Vorlage verwenden.

Der erste Absatz oder die ersten Absätze in der Einführung sind dafür da, um zu erklären, was im Tutorial behandelt wird. Liste bitte nicht einfach die einzelnen Schritte auf, da ein Inhaltsverzeichnis automatisch hinzugefügt wird. Stelle sicher, dass die Benutzer genau wissen, was sie am Ende erreichen werden, wenn sie deinem Tutorial folgen. Lasse sie wissen, wenn sie bestimmte Voraussetzungen benötigen.
Du kannst auf andere Tutorials verweisen, auf denen dein Tutorial aufbaut, und Empfehlungen hinzufügen, was Benutzer wissen sollten.

Beachte außerdem, dass die Tutorials wie in diesem Beispiel-Tutorial gezeigt, mit Markdown geschrieben werden (siehe [Markdown-Cheatsheet](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet)).

**Voraussetzungen**

Falls dein Tutorial nur genutzt werden kann, wenn bestimmte Voraussetzungen erfüllt werden, sollten diese hier angegeben werden.
Falls es bereits ein Tutorial gibt, in dem eine der Voraussetzungen erklärt wird, sollte dieses verlinkt werden.

Zum Beispiel:

* Hetzner Cloud [API-Token](https://docs.hetzner.com/de/cloud/api/getting-started/generating-api-token) in der [Cloud Console](https://console.hetzner.cloud/)
* [SSH-Key](https://community.hetzner.com/tutorials/howto-ssh-key/de)

**Beispiel-Benennungen**

Viele Tutorials müssen beispielsweise Benutzernamen, Hostnamen, Domänen und IPs enthalten. Um dies zu vereinfachen, sollten alle Tutorials die gleichen Standardbeispiele verwenden, wie unten beschrieben.

* Benutzername: `holu` (Abkürzung für Hetzner OnLine User)
* Hostname: `<your_host>`
* Domain: `<example.com>`
* Subdomain: `<sub.example.com>`
* IP-Adressen (IPv4 und IPv6):
   * Server: `<10.0.0.1>` und `<2001:db8:1234::1>`
   * Gateway `<192.0.2.254>` und `<2001:db8:1234::ffff>`
   * Client privat: `<198.51.100.1>` und `<2001:db8:9abc::1>`
   * Client öffentlich: `<203.0.113.1>` und `<2001:db8:5678::1>`

Verwende in deinem Tutorial **niemals** echte IP-Adressen.

## Schritt 1 - Zusammenfassender Titel

Die Schritte sind die eigentlichen Schritte, die Benutzer durchführen werden, um das Tutorial abzuschließen.

Jeder Schritt sollte auf dem vorherigen aufbauen, bis zum letzten Schritt, der das Tutorial beendet.

Es ist wichtig, keine Schritte zu überspringen, egal wie offensichtlich oder selbsterklärend sie erscheinen mögen.

Du kannst gerne Screenshots hinzufügen, um genau zu zeigen, was der Benutzer sehen sollte. Füge alle Screenshots in einen separaten `images`-Ordner ein.

Die Anzahl der Schritte hängt ganz davon ab, wie lang/kompliziert das Tutorial ist.

## Schritt 2 - Zusammenfassender Titel

Kurze Einleitung.

Zuerst...

![Screenshot Description](images/screenshot_description.png)

Dann...

Abschließend...

### Schritt 2.1 - Zusammenfassender Titel

Du kannst Code-Beispiele in fast jeder Programmiersprache erstellen.
Gib die Sprache einfach nach den ersten drei Backticks in der Markdown-Datei an.

Hier ist ein Code-Beispiel

```javascript
var s = "JavaScript syntax highlighting";
alert(s);
```

### Schritt 2.2 - Zusammenfassender Titel

Noch ein Code-Beispiel

```python
s = "Python syntax highlighting"
print s
```

## Schritt 3 - Zusammenfassender Titel (Optional)

Anweisungen für einen Schritt, der nicht zwingend notwendig ist, um das Tutorial zu beenden, der aber hilfreich sein kann.

## Schritt N - Zusammenfassender Titel

Noch mehr Anweisungen.

## Ergebnis

Am Ende des Tutorials, sobald der Benutzer alle Schritte abgeschlossen hat, kannst du eine kurze Schlussfolgerung hinzufügen. Fasse zusammen, was der Benutzer getan hat, und schlage vielleicht verschiedene Maßnahmen vor, die er jetzt ergreifen kann. Zusätzlich kannst du bei Bedarf noch hilfreiche Links ergänzen.

**Nächste Schritte:**

* Links zu weiterführenden/interessanten Tutorials
* Support Links

##### License: MIT

<!--

Contributor's Certificate of Origin

By making a contribution to this project, I certify that:

(a) The contribution was created in whole or in part by me and I have
    the right to submit it under the license indicated in the file; or

(b) The contribution is based upon previous work that, to the best of my
    knowledge, is covered under an appropriate license and I have the
    right under that license to submit that work with modifications,
    whether created in whole or in part by me, under the same license
    (unless I am permitted to submit under a different license), as
    indicated in the file; or

(c) The contribution was provided directly to me by some other person
    who certified (a), (b) or (c) and I have not modified it.

(d) I understand and agree that this project and the contribution are
    public and that a record of the contribution (including all personal
    information I submit with it, including my sign-off) is maintained
    indefinitely and may be redistributed consistent with this project
    or the license(s) involved.

Signed-off-by: [submitter's name and email address here]

-->