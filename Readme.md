# AI Site Chatbot

**AI Site Chatbot** is een WordPress plugin die automatisch de inhoud van je website indexeert en via een AI-chatbot vragen van bezoekers beantwoordt.  
De gratis editie gebruikt een veilige proxy via Websitetoday en hanteert een maandelijkse limiet op het aantal chats.

- 🔎 Indexeert je pagina's en maakt hier een compacte samenvatting van
- 💬 Chatwidget op elke pagina, als floating bubble
- 🔐 Proxy-verbinding via Websitetoday (API key gebaseerd)
- 📊 Maandelijkse chatlimiet (bijvoorbeeld 50 chats)
- ⚙️ Eenvoudige instellingenpagina in WordPress

> Dit is de **Free Edition**. Een aparte **Pro** plugin (met extra features, modellen en integraties) volgt later.

---

## Features

- Automatische indexatie van pagina-inhoud
- Opslag van index in de uploads-map (`wp-content/uploads/ai-site-chatbot/index.json`)
- Moderne chat UI:
  - Floating bubble rechtsonder
  - Chatvenster met naam van de bot en sitenaam
  - Contactknoppen (telefoon en e-mail) onder in het venster
- Beperkingen:
  - Proxy is verplicht in de free edition
  - Maandelijkse limiet op aantal chats (standaard 50)

---

## Requirements

- WordPress 5.2 of hoger
- PHP 7.4 of hoger
- cURL ingeschakeld
- Toegang tot `https://api.websitetoday.nl/` vanaf de server

---

## Installatie

1. Download de laatste release (`ai-site-chatbot.zip`) vanaf de [GitHub releases](https://github.com/Websitetoday/ai-site-chatbot/releases).
2. Ga in je WordPress dashboard naar **Plugins → Nieuwe plugin → Plugin uploaden**.
3. Upload het ZIP-bestand en activeer de plugin.
4. Ga naar **Instellingen → AI Site Chatbot** om de botnaam, sitenaam en contactgegevens in te stellen.
5. Start vervolgens de indexering van de inhoud via de instellingenpagina.

De chatbubble verschijnt automatisch op de front-end van je site.

---

## Configuratie

In **Instellingen → AI Site Chatbot** kun je o.a.:

- Botnaam instellen (bijv. "Rickerd")
- Sitenaam (standaard = WordPress sitenaam)
- Telefoonnummer (voor "Bel ons"-knop)
- E-mailadres (voor "Mail ons"-knop)

De plugin maakt bij activatie automatisch een map aan in de uploads:

```text
wp-content/uploads/ai-site-chatbot/
