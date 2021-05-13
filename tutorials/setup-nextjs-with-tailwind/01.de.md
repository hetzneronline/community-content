---
SPDX-License-Identifier: MIT
path: "/tutorials/setup-nextjs-with-tailwind/de"
slug: "setup-nextjs-with-tailwind"
date: "2021-05-06"
title: "Einrichten von Next.js und TailwindCSS"
short_description: "Dieses Tutorial liefert eine Anleitung für die Einrichtung einer Next.js-Anwendung mit TailwindCSS."
tags: ["Next.js", "Tailwind", "Next", "Tailwind", "Frontend"]
author: "Fabian Huth"
author_link: "https://github.com/fabianhuth"
author_img: "https://avatars.githubusercontent.com/u/46992669"
author_description: ""
language: "de"
available_languages: ["en", "de"]
header_img: "header-1"
---

## Einleitung

Dieses Tutorial zeigt, wie man Next.js in Kombination mit TailwindCSS installiert, um ein Grund-Setup für
unterschiedliche Frontend Projekte zu schaffen. Next.js ist ein ReactJS-Frontend-Framework, das eine Vielzahl von
nützlichen Funktionen bietet. Es kann nicht nur als Static Site Generator dienen, sondern verfügt auch über erweiterte
Strategien zum Abrufen von Daten aus verschiedenen Quellen, um dynamische Inhalte anzuzeigen. TailwindCSS ist ein
CSS-Framework, welches einen Utility-First-Ansatz zur Anwendung von CSS-Klassennamen verfolgt. Dies bietet eine
effiziente Möglichkeit, moderne Layouts und Designs zu erstellen, ohne sich zu sehr mit der Namensvergabe der
CSS-Klassen zu beschäftigen.

**Voraussetzungen**

- Node.js 12.13.0 oder höher
- npm 6.12 oder höher
- MacOS, Windows oder Linux

## Installation von Next.js

Um Next.js automatisch einzurichten, kann folgender Befehl verwendet werden:

`npx create-next-app my-project [--use-npm]`

Um Next manuell zu installieren, kann aber auch dieser Befehl genutzt werden:

`npm install next react react-dom`

Falls die manuelle Installation gewählt wurde, müssen folgende Skripte zur `package.json` hinzugefügt werden:

```json
{
  "scripts": {
    "dev": "next dev",
    "build": "next build",
    "start": "next start"
  }
}
```

## Installation von TailwindCSS

TailwindCSS verwendet PostCSS als Präprozessor und Autoprefixer als zusätzliche Dependency. Dies ist optional, aber
empfohlen, da Tailwind bei Verwendung dieser Plugins einige nette Features bietet, z.B. das Anwenden von nicht
standardisierten Keywords wie `@apply`, `@theme` oder `theme()`, die anschließend in einer eigenen CSS-Datei verwendet
werden können:

`npm install -D tailwindcss@latest postcss@latest autoprefixer@latest`

Im Anschluss daran können wir eine Konfigurationsdatei für TailwindCSS erstellen:

`npx tailwindcss init -p`

Dies erzeugt eine `tailwind.config.js` und eine `postcss.config.js` Datei in unserem Root-Verzeichnis des Projekts.

## Konfiguration von Next.js

Um Routen für unsere Anwendung zu definieren, können wir einfach zusätzliche JavaScript-Dateien in das
Verzeichnis `pages` einfügen, welches Next.js standardmäßig generiert. In diesem Tutorial werden wir uns auf Seiten
konzentrieren, die nicht dynamisch generiert werden. In vielen Fällen ist es nämlich auch notwendig, Routen anhand von
dynamischen Daten zu generieren, z. B. das Erstellen von Routen in Abhängigkeit von einer `id`. Eine ausgezeichnete
Anleitung, wie dies möglich ist, ist in der [Next.js-Dokumentation](https://nextjs.org/docs/basic-features/pages) zu
finden.

Next.js erzeugt standardmäßig eine Datei `index.js` im Verzeichnis `pages`. Wir werden diese Datei wiederverwenden und
ihren Inhalt ändern, da Next.js standardmäßig einige Styles zu dieser Komponente hinzufügt, die wir nicht verwenden
möchten:

```javascript
// ./pages/index.js

const Home = () => (
  <div className="p-4 shadow rounded bg-white">
    <h1 className="text-purple-500 leading-normal">Next.js and Tailwind CSS</h1>
    <p className="text-gray-500">with Tailwind CSS</p>
  </div>
)

export default Home
```

### Variante 1: Hinzufügen von TailwindCSS via Javascript

Um Tailwind-CSS in unser Projekt einzubinden, fügen wir die folgende Import-Anweisung am Anfang der
Datei `pages/_app.js` ein und entfernen alle Verweise auf die Standard-Regeln von Next:

```javascript
// ./pages/_app.js

import 'tailwindcss/tailwind.css'

function MyApp ({ Component, pageProps }) {
  return <Component {...pageProps} />
}

export default MyApp
```

Dies ist eine bequeme Möglichkeit, TailwindCSS hinzuzufügen, ohne weitere Stylesheets selbst schreiben zu müssen.

### Variante 2: Hinzufügen von TailwindCSS via CSS

Eine andere Möglichkeit ist, die Style-Definitionen von Tailwind per CSS hinzuzufügen. Um dies zu erreichen, behalten
wir einfach das globale Stylesheet in der Datei `_app.js`:

```javascript
 // ./pages/_app.js
import '../styles/globals.css'

function MyApp ({ Component, pageProps }) {
  return <Component {...pageProps} />
}

export default MyApp
```

und verwenden Tailwind, indem wir die Direktiven in der Datei `global.css` hinzufügen:

```css
/* ./styles/globals.css */

@tailwind base;
@tailwind components;
@tailwind utilities;
```

Leider bietet Tailwind keine ausführliche Erklärung, was die Direktive `@tailwind` im Detail bewirkt, außer dass
generierte Klassen (basierend auf der Konfiguration von Tailwind) zur Build-Zeit in das Stylesheet inkludiert werden.

## Konfiguration von TailwindCSS

Durch das Ausführen von `npx tailwindcss init -p` haben wir bereits zwei Konfigurationsdateien, `tailwind.config.js`
und `postcss.config.js`, erstellt. Wenn wir PostCSS als Präprozessor verwenden wollen, können wir die
Datei `postcss.config.js` für zusätzliche Funktionen verwenden. Wie etwa das Hinzufügen von Vendor-Präfixen, das
Hinzufügen von globalen CSS Resets oder das Generieren von`@font-face`-Regeln. Die Art und Weise, wie Tailwind die
Utility-Klasse Tailwind erzeugt, würde dazu führen, dass wir eine sehr große CSS-Datei erstellen, wenn wir die Anwendung
ohne weiteres Zutun erstellen. Um dies zu verhindern, werden wir das Tree-Shaking-Verfahren anwenden. Das bedeutet, dass
wir Klassen, die wir nicht in unseren React-Komponenten verwenden, aus unserer finalen CSS-Datei entfernen. Deshalb
ändern wir die Datei `tailwind.config.js` wie folgt:

```javascript
// ./tailwind.config.js

module.exports = {
  purge: ['./pages/**/*.{js,ts,jsx,tsx}', './components/**/*.{js,ts,jsx,tsx}'],
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {},
  },
  variants: {
    extend: {},
  },
  plugins: [],
}
``` 

Ein weiteres nützliches Feature von TailwindCSS ist, dass das mitgelieferte Standard-Theme leicht an unsere
Anforderungen angepasst werden kann. Durch Ändern der Eigenschaft `theme` in der `tailwind.config.js` können wir z.B.
eigene Breakpoints, Farben, Schriftarten oder detailliertere Eigenschaften, wie Abstände, Border-Radien oder Box-Shadows
definieren. Durch Ändern des Keys `colors` ist es möglich, eine eigene Farbpalette zum bestehenden Theme hinzuzufügen:

```javascript
// ./tailwind.config.js

const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
  theme: {
    colors: {
      ...defaultTheme.colors,
      black: '#242424',
      midnight: {
        100: '#03060a',
        200: '#001a40',
        300: '#060b17',
        // add more color variants if you want to
      }
      // ...
    }
  }
}
```

Zusätzlich verwenden wir eine Spread-Operation auf das Standard-Farbschema von Tailwind, so dass wir immer noch in der
Lage sind Tailwinds Standard-Farbpalette zu verwenden. Nun können wir unsere Farben auf mehrere Utility-Klassen
anwenden, indem wir unsere eben definierte Farbe und die entsprechende Variante kombinieren, `bg-{color}-{variant}`.
Beispiel:

```javascript
<div className="bg-midnight-300">
  ...
</div>
```

## Komponenten erstellen und Styling anwenden

Oft möchten wir ein Grundlayout erstellen, das in unserer gesamten Anwendung wiederverwendet werden kann. Deshalb
erstellen wir eine Layout-Komponente, die als Wrapper für andere Komponenten fungiert. Dafür erstellen wir
eine `layout.js`-Datei in `/components`:

```javascript
// ./components/layout.js

import Head from 'next/head'

const Layout = ({ children }) => {
  return (
    <>
      <Head>
        <title>Next.js and TailwindCSS</title>
        <link rel="icon" href="/favicon.ico"/>
      </Head>
      <main
        className="min-h-screen bg-gradient-to-tr from-midnight-100 via-midnight-200 to-midnight-300 flex flex-col justify-center">
        {children}
      </main>
    </>
  )
}

export default Layout
```

Und umschließen die Anwendung mit der Komponente:

```javascript
// ./pages/_app.js

// ...
import Layout from './components/layout'

function MyApp ({ Component, pageProps }) {
  return (
    <Layout>
      <Component {...pageProps} />
    </Layout>
  )
}

//... 
```

Zusätzlich wollen wir eine Komponente erstellen, die wir auf unserer Startseite anzeigen wollen. Um dies zu erreichen,
erstellen wir eine weitere Datei namens `FancyCard.js`:

```javascript
// ./components/FancyCard.js

import React from 'react'

const FancyCard = ({ children }) => {
  return (
    <div className="max-w-xl mx-auto">
      <div className="p-8 bg-midnight-200 shadow-xl rounded-3xl border-4 border-gray-600">
        <div className="grid grid-cols-6 gap-0 divide-x divide-gray-600">
          {children}
        </div>
      </div>
    </div>
  )
}

export default FancyCard
```

Wir ersetzen die Ausgabe der `index.js`-Datei durch unsere neu erstellte Komponente:

```javascript
// ./pages/index.js

import FancyCard from '../components/FancyCard'

export default function Home () {
  return (
    <FancyCard>
      <div className="flex flex-col place-content-center items-center col-span-1 pr-3">
        <div className="border-2 rounded-full p-0.5 border-gray-600 mb-2">
          <img
            className="rounded-full w-100 h-100"
            src="https://source.unsplash.com/random/800x800"
            alt="random image from unsplash"
          />
        </div>
      </div>
      <div className="col-span-5 pl-3">
        <h2 className="text-white text-3xl font-semibold mb-3">Welcome to Next.js and TailwindCSS</h2>
        <span className="text-gray-500 text-lg font-bold block mb-3">Bringing both frameworks together</span>
        <p className="text-white leading-7">
          Cats are believed to be the only mammals who don't taste sweetness. Cats are nearsighted, but their peripheral
          vision and night vision are much better than that of humans. Cats are supposed to have 18 toes (five toes on
          each front paw; four toes on each back paw). Cats can jump up to six times their length.
        </p>
      </div>
    </FancyCard>
  )
}
```

Jetzt führen wir den Befehl `npm run dev` aus, um unser Ergebnis zu betrachten. Dies sollte wie folgt aussehen:

![Final result](images/component-example.png)

## Fazit

Wir haben erfolgreich eine Next.js-Anwendung aufgesetzt, die Tailwind als CSS-Framework nutzt.

Die Verwendung von Utility-First-CSS-Frameworks erzeugt eine große Bandbreite an Meinungen und wie üblich gibt es kein
universelles Urteil, ob dies nun gut oder schlecht ist. Manche sagen, dass es unübersichtlich und schwer zu lesen ist,
dass es keinen Unterschied zu Inline-Styles gibt oder dass es gegen "Separation of concerns" verstößt. Ich persönlich
denke, dass alle diese Punkte widerlegt werden können und empfehle die folgenden beiden Artikel, um einen Eindruck von
beiden Seiten zu bekommen. Auch fällt die Entscheidung vielleicht etwas leichter, ob TailwindCSS das richtige Framework
für das nächste Projekt sein kann.

- [TailwindCSS: Adds complexity, does nothing. by Brian Boyoko (Englisch)](https://dev.to/brianboyko/tailwindcss-adds-complexity-does-nothing-3hpn)
- [In Defense of Utility-First CSS by Sarah Dayan (Englisch)](https://frontstuff.io/in-defense-of-utility-first-css)

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

Signed-off-by: Fabian Huth, f.huth@headtrip.eu

-->
