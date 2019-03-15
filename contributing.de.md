# Hetzner Tutorial Richtlinien

## Allgemein
* Alle Tutorials sollten auf Englisch verfasst sein.
  * Wenn Sie eine andere Sprache fließend beherrschen und eine Übersetzung anfertigen können, können Sie Ihr Tutorial in mehreren Sprachen einreichen, sofern mindestens eine Englisch ist.
  * Wenn Sie nicht fließend Englisch sprechen, aber ein hochwertiges Tutorial haben, dass Sie in einer anderen Sprache teilen können, kontaktieren Sie uns bitte. Wir sind offen für die Möglichkeit, Ausnahmen für bestimmte Tutorials zu machen.
* Es werden nur Originalarbeiten akzeptiert.
  * Das bedeutet, dass Tutorials, die an anderer Stelle im Web gefunden werden, nicht (wieder) eingereicht werden können.
* Wenn Ihr Tutorial einen Server benötigt, sollte es auf einem neuen Server funktionieren.
  * Wenn ein Benutzer gerade einen Server bestellt hat, sollte er das Tutorial Schritt für Schritt durchlaufen können, ohne vorher etwas installieren oder konfigurieren zu müssen. Wenn dies jedoch eine Voraussetzung für Ihr Tutorial ist, stellen Sie bitte sicher, dass es bereits ein Tutorial gibt, das dies erklärt, und vergewissern Sie sich dann, dass Sie zu Beginn Ihres Tutorials auf dieses Tutorial verweisen.
* Schreiben Sie auf eine klare, leicht verständliche Weise.
  * Diese Tutorials werden von Anwendern mit einem breiten Erfahrungsspektrum gelesen. Stellen Sie sicher, dass Anfänger den Schritten noch folgen können. Das bedeutet, dass es wichtig ist, keine Schritte zu überspringen, egal wie offensichtlich oder selbsterklärend sie erscheinen mögen. Fühlen Sie sich frei, Screenshots beizufügen, um genau zu zeigen, was der Benutzer sehen sollte.
  * Wenn Sie Abkürzungen verwenden, stellen Sie sicher, dass Sie diese bei der ersten Verwendung ausschreiben.
  * Verwenden Sie keinen übermäßigen Jargon oder Techspeak. Auch hier gilt: Wenn Sie ein Wort verwenden, das nicht jeder verstehen könnte, erklären Sie es entweder oder verwenden Sie ein leichter verständliches Wort oder einen Satz.
  * Witze sind erlaubt, aber übertreib es nicht.
  
## Wie man ein Tutorial einreicht

1. Fork des Projekts anlegen
2. Fügen Sie ihren Tutorial-Ordner hinzu:
   `mkdir -p tutorials/my-tutorial-name`
3. Fügen Sie die Templates hinzu: 
   `cat tutorial-template.md > tutorials/my-tutorial-name/01.en.md`
4. Erstellen Sie den Inhalt
5. Erstellen Sie einen Pull-Request und fügen Sie folgendes in Ihren Pull-Request ein:

```
I have read and understood the Contributor's Certificate of Origin
available at the end of https://raw.githubusercontent.com/hetzneronline/community-content/master/tutorial-template.md and I hereby certify that I meet the contribution criteria described in it.
Signed-off-by: YOUR NAME <YOUR@EMAILPROVIDER.TLD>
```

6. Wenn Ihr Tutorial angenommen wird, erhalten Sie eine E-Mail von einem Hetzner Online Community Manager. Bitte antworten Sie auf diese Mail unter Angabe Ihrer Hetzner Kundenummer, damit die Prämie Ihrem Konto als Guthaben gutgeschrieben werden kann.

## Layout
Tutorials sollten alle das gleiche grundlegende Layout haben:

 * Titel
 * Einführung
 * Schritte
 * Ergebnis
 
### Title
Der Titel sollte deutlich machen, was das Ziel Ihres Tutorials ist. Stecken Sie aber nicht alles in den Titel, dies sollte eine Zusammenfassung sein, die dem Benutzer eine sofortige Vorstellung davon vermittelt, worum es im Tutorial geht. z.B. Installation von `<software>` auf `<Betriebssystem>`.

### Einführung
Der erste Absatz oder die ersten Absätze sind dafür da, um zu erklären, was Ihr Tutorial tun wird. Stellen Sie sicher, dass die Benutzer genau wissen, was sie am Ende erreichen werden, wenn sie Ihrem Tutorial folgen. Lassen Sie sie wissen, wenn sie bestimmte Voraussetzungen benötigen. Sie können auf andere Tutorials verweisen, auf denen Ihr Tutorial aufbaut, und Empfehlungen hinzufügen, was Benutzer wissen sollten.

### Schritte
Die Schritte sind die eigentlichen Schritte, die Benutzer durchführen werden, um Ihr Tutorial abzuschließen. Jeder Schritt sollte auf dem vorherigen aufbauen, bis zum letzten Schritt, der das Tutorial beendet. Es ist wichtig, keine Schritte zu überspringen, egal wie offensichtlich oder selbsterklärend sie erscheinen mögen. Fühlen Sie sich frei, Screenshots beizufügen, um genau zu zeigen, was der Benutzer sehen sollte. Die Anzahl der Schritte hängt ganz davon ab, wie lang/kompliziert das Tutorial ist.

### Ergebnis
Am Ende Ihres Tutorials, sobald der Benutzer alle Schritte abgeschlossen hat, können Sie eine kurze Schlussfolgerung hinzufügen. Fassen Sie zusammen, was der Benutzer getan hat, und schlagen Sie vielleicht verschiedene Maßnahmen vor, die er jetzt ergreifen kann.

## Formatierung
Die Tutorials in den "Hetzner Tutorials" werden alle mit Markdown geschrieben. Dies ist eine Auszeichnungssprache, die im gesamten Web verwendet wird. Eine gute Übersicht findet sich auf Github:
[Markdown-Cheatsheet](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet "Github")

Während der Titel ein H1-Header sein sollte, sollten alle anderen Header H2 sein. Wenn es zwei oder mehr Unterpunkte gibt, können Sie erwägen, diese Unterpunkte mit einem H3-Header zu formatieren.
Für konkrete Beispiele, wie Sie ein Tutorial formatieren können, schauen Sie sich bitte das Tutorial-Template an.

## Code Beispiel
Sie können Code Beispiele in fast jeder Programmiersprache erstellen. Geben Sie einfach die Sprache nach den ersten drei Backticks in Ihrer Markdown-Datei an.

```javascript
var s = "JavaScript syntax highlighting";
alert(s);
```
 
```python
s = "Python syntax highlighting"
print s
```

## Begrifflichkeiten
Viele Tutorials müssen beispielsweise Benutzernamen, Hostnamen, Domänen und IPs enthalten. Um dies zu vereinfachen, sollten alle Tutorials die gleichen Standardbeispiele verwenden, wie unten beschrieben.

* Benutzername: `holu` (Abkürzung für Hetzner OnLine User)
* Hostname: `<your_host>`
* Domain: `<example.com>`
* IPv4: `<10.0.0.1>`
* IPv6: `<2001:db8:1234::1>`

## Grafik
Gerne können Sie uns auch eine Grafik für den Einführungsbereich zusenden. Grafiken sollten im Verhältnis 16:9 erstellt werden und max. 250kb groß.

## Template
Um Ihnen den Einstieg zu erleichtern, haben wir eine Vorlage vorbereitet, auf der Sie aufbauen können. Es enthält ein grundlegendes Layout für Ihr Tutorial, einige Beispiele für die Formatierung und eine Reihe von Tipps und Tricks für die Einrichtung. Das finden Sie hier:

[Tutorial Template](tutorial-template.md)

## Einsendungen
Wenn Sie der Meinung sind, dass Sie ein Tutorial haben, dass die oben genannten Kriterien erfüllt und interessant für andere Nutzer ist, dann erstellen Sie einen Pull request in dem Hetzner Online Community-Content Repository auf GitHub.
 
