# Changelog

Alle belangrijke wijzigingen in **AI Site Chatbot** worden in dit bestand bijgehouden.

---

## [2.1.2] - 2025-11-19

### Added
- Plugin iconen toegevoegd (`icon-128x128.png` en `icon-256x256.png`) voor WordPress admin en automatische updates via GitHub.
- Twee WordPress.org banners ontworpen en toegevoegd (`banner-772x250.png` en `banner-1554x500.png`) ter voorbereiding op publicatie in de Plugin Directory.

### Improved
- Plugin metadata en visuele presentatie geoptimaliseerd voor betere herkenbaarheid in WordPress.

---

## [2.1.1] - 2025-11-19
### Added
- Minor fixes.

---

## [2.1.0] - 2025-11-19
### Added
- Eerste publieke release van **AI Site Chatbot**.
- Floating chatbubble en modern chatvenster op de front-end.
- Indexering van pagina-inhoud naar `wp-content/uploads/ai-site-chatbot/index.json`.
- Integratie met Websitetoday-proxy inclusief client key.
- Instellingenpagina voor botnaam, sitenaam, telefoonnummer en e-mailadres.
- Maandelijkse limiet op chatverkeer (standaard 50) met automatische reset.
- Plugin Update Checker geïntegreerd voor automatische GitHub-updates.
- Automatische laadfunctie voor index-samenvatting (`aisc_get_index_summary_text`).
- Contactknoppen (bel & mail) onderin het venster.

### Improved
- Front-end styling geoptimaliseerd voor mobiele weergave.
- Verbeterde nonces & AJAX-validatie.

---

## [2.0.0] - 2025-10-30
### Added
- Volledig nieuwe chat-UI met:
  - Herstart-knop (Nieuwe chat)
  - Sluit-knop
  - Betere berichtstructuur
- Introductie van **aisc_log_chat_usage** met maandelijkse limiet.

### Improved
- Betere indexeringskwaliteit door verbeterde trimming en HTML-strip.
- Fix voor ongeldige JSON in indexeringsbestand.
- Chatvenster toont sitenaam en avatar-icoon.

---

## [1.6.0] - 2025-10-10
### Added
- Volledig nieuw indexeringssysteem (`indexer.php`) met AJAX live statusupdates.
- Opslag van indexeringsstatus in realtime op adminpagina.
- Verbeterde settings UI + instructies.

### Improved
- Index-processing snelheid sterk verbeterd.
- Betere foutafhandeling in de proxy request.

---

## [1.5.0] - 2025-09-25
### Added
- Eerste versie van admin-instellingenpagina.
- Opties toegevoegd:
  - Botnaam instellen
  - Sitenaam instellen
  - Telefoonnummer + e-mail voor de contactsectie

### Improved
- CSS voor chatbubble geoptimaliseerd.
- Betere fallback styling voor oudere thema’s.

---

## [1.0.0] - 2025-09-01
### Added
- Eerste stabiele interne build van de AI chatbot.
- Chatwidget verschijnt op elke pagina.
- Basale AJAX-chatfunctionaliteit via Websitetoday-proxy.
- Opslagmap `uploads/ai-site-chatbot/` wordt automatisch aangemaakt.
- Simpele content-indexering (alleen ruwe tekst).
- Basisstructuur van plugin opgezet:
  - assets/
  - includes/
  - plugin-update-checker/

---

## [0.5.0] - 2025-08-20
### Added
- Eerste versie van de front-end chatbubble.
- Basisversie van chatvenster UI.
- API-handler voor proxy requests.
- Eerste ruwe index.json testmethode.

---

## [0.1.0] - 2025-08-10
### Added
- Eerste proof-of-concept:
  - Chatbox opent via een knop.
  - Berichten worden naar proxy gestuurd en getoond.
- Plugin skeleton aangemaakt:
  - `ai-site-chatbot.php`
  - `includes/`
  - `assets/`
