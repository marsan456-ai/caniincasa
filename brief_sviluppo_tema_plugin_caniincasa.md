# Brief strutturato per sviluppo tema e plugin WordPress – caniincasa.it

## 1. Contesto e obiettivo

Sito: [**www.caniincasa.it**](http://www.caniincasa.it)\
Progetto: **Restyling completo** del portale cinofilo con sviluppo **tema custom** e **plugin dedicati**, mantenendo il posizionamento SEO esistente.

### Obiettivo principale

- Modernizzare grafica e UX
- Aggiungere funzionalità interattive (annunci, quiz, dashboard utente, ecc.)
- **Mantenere invariato il valore SEO** (permalink, meta, struttura URL)

---

## 2. Requisito critico: SEO & permalink

⚠️ **REQUISITO CRITICO: Mantenimento SEO**

Implementare nel tema/plugin:

1. **Preservare tutti i permalink esistenti**

   - Non modificare la struttura URL delle pagine e CPT già indicizzate.(verifica anche sito [www.caniincasa.it](http://www.caniincasa.it))
   - Per i CPT già esistenti, mantenere gli slug attuali.

2. **Redirect 301 automatici (solo se e quando attivati in produzione)**

   - Gestore redirect 301 basato su campo `old_slug` (per ogni post/CPT dove necessario).
   - Sistema che:
     - Se l'URL richiesto non esiste, controlla se è presente in un campo `old_slug`.
     - Se trovato, effettua redirect 301 verso il nuovo permalink.
   - NB: Implementare la logica ma attivare i redirect **solo quando esplicitamente richiesto (fase go-live)**.

3. **Struttura URL e meta esistenti**

   - Campo `permalink_esistente` per i contenuti importati via CSV.
   - Preservare **meta title** e **meta description** (Yoast SEO o equivalente) durante la migrazione.
   - Sitemap XML compatibile con la nuova struttura.

4. **Fallback 404 intelligente**

   - Per URL non trovati, usare:
     - Ricerca nel campo `old_slug`.
     - Se non trovato, mostrare pagina 404 ottimizzata (con ricerca interna e contenuti suggeriti).

---

## 3. Architettura dati e Custom Post Types

### 3.1. CPT Directory Strutture (5 tipologie)

Creare 5 CPT separati per le strutture:

- `allevamenti`
- `veterinari`
- `canili`
- `pensioni_per_cani`
- `centri_cinofili`

#### Requisiti tecnici

- Plugin di **importazione CSV dedicato** (unico plugin, configurabile per CPT diversi):
  - Mappatura colonne CSV → custom fields ACF.
  - Campo `permalink_esistente` per mantenere URL attuali.
  - Opzione per **aggiornare** post già esistenti (match su ID o altro campo chiave) senza cambiare permalink.
- Struttura campi:
  - Un custom field per ogni colonna dei CSV (analizzare struttura CSV da GitHub – previsione: nome, indirizzo, provincia, servizi, ecc.).
  - Campo `provincia` normalizzato (es. sigle tipo `VR`, `MI`, ecc.).
  - Campi per contatti (telefono, email, sito web, social, eventuale WhatsApp).
  - Campi per geolocalizzazione (latitudine, longitudine) per mappe.
- Routing:
  - Mantenere i vecchi permalink quando presenti in `permalink_esistente`.
  - Se necessario, usare filtro `post_type_link` per forzare struttura URL compatibile.

#### Template singola struttura

- Layout desktop: **2/3 contenuto + 1/3 sidebar**
- Layout mobile: colonna unica full-width (stack verticale)
- Contenuti principali:
  - Titolo struttura
  - Indirizzo + mappa (Google Maps / Leaflet, via campi lat/long)
  - Descrizione / servizi
  - Orari, contatti, sito web
- Sidebar:
  - Navigazione contestuale: link a archivio CPT (es. "Torna all'elenco allevamenti")
  - Link a altre strutture correlate (es. stessa provincia)
  - Form "Segnala modifiche" (contact form o endpoint custom)
  - **Miglioria**: Pulsante WhatsApp (solo mobile) se presente numero cellulare.

#### Archivi filtrabili

- Archivi per ogni CPT con filtri AJAX:
  - Filtro **provincia** (tutti i CPT)
  - Filtro **razze** (solo per `allevamenti`, se collegati a CPT `razze_di_cani`)
  - Filtro **servizi** (se disponibile nei dati)
- **Miglioria**:
  - Ricerca per vicinanza geografica (utente → calcolo distanza da lat/long strutture).

---

### 3.2. CPT Razze di Cani – `razze_di_cani`

CPT dedicato con struttura dati ricca.

#### Campi numerici (range 1–5)

- `affettuosita`
- `socievolezza_cani`
- `adattabilita_appartamento`
- `tolleranza_estranei`
- `intelligenza`
- `facilita_toelettatura`
- `livello_esperienza_richiesto`
- `costo_mantenimento`

#### Campi sidebar

- `nazione_origine` (testo)
- `colorazioni` (textarea / elenco)
- `temperamento_breve` (testo corto, max 100 caratteri)
- `taglia` (select: piccola / media / grande / gigante)
- `peso_medio` (range min–max)
- `aspettativa_vita` (range anni min–max)

#### Sezioni contenuto principale

- `descrizione_generale`
- `origini_storia`
- `aspetto_fisico`
- `carattere_temperamento`
- `salute_cura`
- `attivita_addestramento`
- `ideale_per`
- `pro_contro` (lista con PRO e CONTRO separati)

#### Requisiti aggiuntivi

- Plugin importazione dati + immagini:
  - Supporto upload/associazione immagini da URL nel CSV.
- Galleria immagini con **lazy loading**.
- Template singola razza come da screenshot `screenshotrazza.png` (layout: immagine + scheda dati + sezioni testuali).
- Archivio razze come da `screenshotarchiviorazza.png`:
  - Filtri per taglia, livello esperienza, adattabilità appartamento, ecc.
  - Ricerca testuale.
- **Miglioria**: Comparatore razze (max 3):
  - Utente seleziona fino a 3 razze e vede tabella comparativa dei valori (1–5) e dati principali.

---

### 3.3. Quiz Selezione Razza

Quiz guidato con 9 domande, responsivo e mobile-first.

#### Domande (scelte predefinite)

1. Esperienza con cani (principiante / intermedia / esperto)
2. Tipo abitazione (appartamento / casa con giardino / fattoria)
3. Tempo disponibile (poco / medio / molto)
4. Livello attività (sedentario / moderato / molto attivo)
5. Bambini in casa (no / sì piccoli / sì grandi)
6. Altri animali (no / gatti / cani)
7. Clima (freddo / temperato / caldo)
8. Manutenzione pelo (bassa / media / alta)
9. Scopo adozione (compagnia / guardia / sport / famiglia)

#### Algoritmo

- Implementare algoritmo di matching (codice fornito esternamente) che calcola una **% di compatibilità** tra risposte e ogni razza.

#### Output

- Top **10 razze** con % match.
- Card **meticcio** sempre inclusa come opzione extra.
- Funzionalità:
  - Per utenti loggati: invio risultati via email.
  - Generazione **PDF** scaricabile con risultati.
  - **Miglioria**: salvataggio risultati nel profilo utente (storico quiz).
  - **Miglioria**: pulsanti share social (Facebook, WhatsApp) per il risultato.

---

### 3.4. CPT Annunci Amici 4 Zampe – `annunci_4zampe`

Sistema annunci per cani (adozione, ricerca compagni, ecc.).

#### Campi principali

- `tipo_annuncio`* (select: cerco / offro) – obbligatorio
- `eta`* (cucciolo / adulto) – obbligatorio
- `tipo_cane`* (meticcio / razza) – obbligatorio
- `razza` (select collegata al CPT `razze_di_cani` + opzione "altro")
- `provincia` (select standardizzata)
- `descrizione` (textarea, con limite caratteri consigliato)
- `immagini` (max 3, **obbligatorie se tipo\_annuncio = "offro"**)
- `data_pubblicazione` (auto)
- `scadenza_annuncio` (nuovo campo: 30 / 60 / 90 giorni – default configurabile)
- `contatto_preferito` (telefono / email / WhatsApp)

#### Funzionalità

- Invio annuncio da frontend (form dedicato per utenti registrati).
- **Approvazione admin obbligatoria** prima della pubblicazione.
- **Miglioria**:
  - Notifiche email sul cambio stato annuncio (in attesa, approvato, respinto, scaduto).
  - Sistema di segnalazione annunci inappropriati (bottone + form + flag interno).
- Template singolo annuncio come `screenshotannuncio.png`.
- Archivio annunci con filtri AJAX:
  - Tipo annuncio, età, tipo cane, provincia.
- Liste carte annuncio con:
  - Badge (es. "Cucciolo", "Urgente", ecc. – gestiti via taxonomy o meta).

---

### 3.5. CPT Annunci Dogsitter – `annunci_dogsitter`

Campi principali:

- `tipo` (cerco / offro servizio)
- `provincia`
- `disponibilita` (giorni/orari)
- `servizi_offerti` (checkbox multipli: passeggiate, pensione, visita a domicilio, ecc.)
- `esperienza` (testo o select livello)
- `prezzo_indicativo` (range o testo)
- `messaggio` (descrizione libera)
- `contatti` (email/telefono/WhatsApp)

Funzionalità:

- Invio e gestione da frontend.
- Filtri archivio simili agli annunci 4 zampe.

---

## 4. Frontend & Responsività Mobile

### Breakpoint principali

- Mobile: < 768px
- Tablet: 768px – 1024px
- Desktop: > 1024px

### Navigazione mobile

- **Hamburger menu** con slide laterale (off-canvas).
- **Bottom navigation bar** con azioni principali (es. Home, Annunci, Razze, Profilo).
- **Sticky header compatto**: logo + icona menu.

### Layout mobile

- Container: 100% con padding laterale 20px.
- Bottoni e elementi cliccabili: min 44x44 px.
- Font size minimo 16px per evitare zoom automatico.
- Spaziatura aumentata tra elementi cliccabili.

### Form e interazioni

- Usare `type="tel"`, `email`, `number` dove opportuno.
- Per campi numerici, tastiera numerica su mobile.
- Supporto upload foto da **fotocamera** (attributi `accept` corretti).
- Swipe per gallerie immagini.

### Performance mobile

- Lazy loading per immagini.
- Infinite scroll o caricamento progressivo per archivi lunghi.
- Service Worker per funzionalità offline base (fase PWA).
- Uso immagini in WebP con fallback JPG/PNG.

---

## 5. Layout, Tema e Customizer

### Struttura base

- Desktop: container max 1280px.
- Tablet: 100% larghezza con padding 40px.
- Mobile: 100% larghezza con padding 20px.
- Sezioni hero sempre full width.

### Header

**Top bar desktop (non sticky, grigio chiaro):**

- Link: Login | Registrazione | Dashboard | Contatti.

**Top bar mobile:**

- Nascosta; contenuto accessibile da hamburger menu.

**Main header (bianco, sticky):**

- Desktop: logo, menu principale, icona/box ricerca.
- Mobile: logo, hamburger menu, icona ricerca.

### Customizer Tema

Opzioni configurabili:

- Palette colori: primario, secondario, overlay, accent.
- Selezione **30+ Google Fonts**.
- Dimensioni font responsive (desktop/tablet/mobile, scala tipografica).
- Testi/etichette UI modificabili (es. CTA, bottoni, messaggi di sistema).
- Immagini background per CPT e sezioni specifiche.
- Layout globale: boxed / full width.
- **Nuovo**: toggle **dark mode** con salvataggio preferenza utente.

---

## 6. Sistema Utenti & Dashboard

### Registrazione

- Form multi-step (soprattutto per mobile) con progress indicator.
- Verifica email (link di conferma) e opzionale verifica via SMS.
- Social login (Google, Facebook) tramite plugin/integrazione.

⚠️ **Blocco accesso wp-admin**

- Gli utenti non admin **non devono mai** poter accedere al backend.
- Reindirizzare richieste a `/wp-admin` verso la dashboard frontend.

### Dashboard utente (frontend only)

- Design **mobile-first**.
- Navigazione a tab (es. Profilo, Annunci, Quiz, Preferiti).
- Funzionalità:
  - Gestione annunci: elenco con stati (bozza, in revisione, pubblicato, scaduto).
  - Modifica / rinnovo annunci.
  - Messaggistica interna (fase 2, opzionale):
    - Scambio messaggi tra utenti senza mostrare email.
  - Sezione preferiti/salvati (annunci, razze, strutture).
  - **Miglioria**: Notifiche push (via PWA, fase successiva).

---

## 7. Homepage

### Hero section

- Desktop: slider o video background leggero.
- Mobile: immagine statica ottimizzata (peso ridotto, focus contenuto).
- CTA principali "above the fold" (es. Cerca Annunci, Fai il Quiz, Scopri le Razze).

### Sezioni focus

**Annunci 4 Zampe**

- Ultimi 6 annunci.
- Quick filters (tipo, provincia, cucciolo/adulto).
- CTA "Inserisci annuncio".

**Database Razze**

- Carousel razze popolari.
- Ricerca rapida (campo testo + filtro taglia).
- "Razza del giorno" (random o basato su visite).

**Quiz Interattivo**

- Breve descrizione.
- Highlight statistiche (es. "Più di 1200 utenti hanno trovato la loro razza ideale").
- Anteprima risultati (es. top razze consigliate ultime 24h).

**Blog**

- Ultimi 3 articoli.
- Link a categorie principali.

---

## 8. Stack tecnico consigliato

### Core

- WordPress 6.x
- PHP 8.1+
- MySQL 8.0+

### Plugin essenziali

- ACF Pro (per tutti i custom fields).
- Compatibilità con WPML/Polylang (non obbligatoria subito, ma da tenere pronta).
- WP Rocket (cache) o equivalente.
- Yoast SEO (usare configurazione esistente, non resettare).

### Frontend

- Build tool: **Vite** o Webpack.
- CSS: **Tailwind** o SCSS modulare (decidere in base al flusso di lavoro).
- JS: Vanilla JS o **Alpine.js** per interattività (modale, filtri, quiz, ecc.).
- AJAX via REST API WordPress (endpoint custom per filtri, quiz, annunci).

---

## 9. Ottimizzazioni, UX e funzionalità avanzate

### SEO & performance

- Implementare **Schema.org**:
  - `LocalBusiness` / `VeterinaryCare` per strutture.
  - `Breed` o schema custom per razze.
- Sitemap XML dinamica e aggiornata.
- Canonical URL automatici.
- Core Web Vitals ottimizzati:
  - Minificazione CSS/JS.
  - Delay caricamento script non critici.
  - Preload font principali.
- CDN per asset statici (immagini, CSS, JS).

### UX migliorata

- Ricerca predittiva con autocomplete (API che restituisce razze, annunci, articoli).
- Filtri con contatore risultati (badge numerico sui filtri).
- Breadcrumbs con output JSON-LD.
- Contenuti correlati:
  - Su razze: articoli del blog, annunci correlati, strutture rilevanti.
- Sistema recensioni/rating per strutture (fase successiva):
  - Rating 1–5, recensioni testuali, moderazione admin.

### Funzionalità aggiuntive (fasi successive / opzionali)

- Newsletter con segmentazione (interessi, provincia, tipo utente).
- Coupon / promozioni per veterinari o strutture.
- Calendario eventi cinofili (CPT dedicato + pagina calendario).
- Mini forum/community (fase 2, opzionale).

---

## 10. Deliverables per fasi

### Fase 1 – Core

- Tema custom responsive completo.
- Plugin/e per:
  - CPT (strutture, razze, annunci, dogsitter).
  - Importazione CSV personalizzata.
  - Quiz razze.
  - Dashboard utente frontend.
- Migrazione dati con mantenimento URL esistenti.
- Sistema annunci (4 zampe, dogsitter) con moderazione.

### Fase 2 – Enhancement

- App PWA (manifest + service worker + push notification).
- Sistema notifiche push.
- Integrazione social avanzata (share, login, feed).
- Dashboard analytics admin (statistiche annunci, quiz, razze, traffico interno).

---

## 11. Note critiche, sicurezza e testing

### Priorità assolute

- **Mantenimento permalink esistenti** per preservare SEO.
- **Blocco accesso wp-admin** per tutti gli utenti non admin.
- Approccio **mobile-first** per tutto il frontend.
- Performance: tempo di caricamento < 3 secondi su connessioni standard.

### Sicurezza

- Validazione e sanitizzazione di tutti gli input frontend.
- Rate limiting sui form (anti spam / brute force).
- Captcha su registrazione e invio annunci.
- Backup automatici (full DB + files) pre-migrazione.

### Testing obbligatorio

- Test su dispositivi reali (iOS, Android, tablet, desktop).
- Test redirect 301 (vecchi URL → nuovi) prima del go-live.
- Test form mobile (tastiere, autofocus, autocomplete, errori).
- Cross-browser testing (Chrome, Firefox, Safari, Edge).
- Stress test filtri AJAX con numerosi risultati.

### Nota operativa

- Implementare la logica di redirect e mappatura URL, ma **attivare i reindirizzamenti 301 solo come ultimo passo, su esplicita conferma, quando il progetto sarà portato live.**
- NB: Importa tutti i dati ma poi mostra solo quelli strettamente necessari indicati negli screenshot
- NB ogni volta che fai aggiornamenti al sito inserisci in coda a questo file gli sviluppi realizzati

---

## 12. Log Sviluppi e Implementazioni

_In questa sezione verranno documentate tutte le implementazioni e modifiche apportate al progetto, in ordine cronologico._

### [2025-11-17] - Inizio progetto restyling
- Ripristinato file brief di sviluppo nel repository
- Avviata analisi sito esistente www.caniincasa.it

### [2025-11-17] - Implementazione Tema WordPress Custom "Caniincasa Theme"

#### Struttura Base Tema
**Percorso:** `wp-content/themes/caniincasa-theme/`

**File principali creati:**
- `style.css` - Stylesheet principale con CSS variables, reset e utility classes
- `functions.php` - Core del tema con setup, enqueue scripts, widget areas
- `index.php` - Template principale per loop articoli
- `header.php` - Header con top bar, navigazione desktop/mobile, search overlay
- `footer.php` - Footer con widget areas e mobile bottom navigation

**Caratteristiche implementate:**
1. **Sistema di Colori CSS Variables**
   - Colori personalizzabili tramite Customizer
   - Supporto dark mode (preparato)
   - Palette: primary (#FFCC70), secondary (#4d3319), accent (#FF9F40)

2. **Layout Responsive**
   - Breakpoints: mobile (<768px), tablet (768-1024px), desktop (>1024px)
   - Container max-width: 1280px configurabile
   - Padding responsivi: 20px mobile, 40px tablet, 60px desktop

3. **Header e Navigazione**
   - Top bar desktop (non sticky) con link login/registrazione/dashboard
   - Main header sticky con logo e menu
   - Hamburger menu mobile con slide laterale (off-canvas)
   - Mobile bottom navigation bar con 4 link principali
   - Search overlay full-screen con animazione

4. **Footer**
   - 4 widget areas a colonne (responsive)
   - Menu footer secondario
   - Copyright dinamico

5. **Widget Areas Registrate**
   - Sidebar principale (`sidebar-1`)
   - 4 colonne footer (`footer-1` a `footer-4`)
   - Sidebar razze (`sidebar-razze`)
   - Sidebar strutture (`sidebar-strutture`)

6. **Menu Locations**
   - `primary` - Menu principale desktop
   - `top-bar` - Menu top bar
   - `mobile` - Menu mobile
   - `footer` - Menu footer

#### File Include del Tema

**inc/customizer.php**
- WordPress Customizer con live preview
- Sezioni: Colori, Tipografia, Layout, Dark Mode, Labels
- 30+ Google Fonts disponibili
- Controlli per:
  - Colori primari, secondari, accent
  - Font primario e secondario
  - Dimensione font base
  - Larghezza container
  - Layout type (full-width/boxed)
  - Dark mode toggle
  - Testi CTA personalizzabili

**inc/template-functions.php**
- Body classes dinamiche
- Reading time calculator
- Primary category detection (Yoast SEO compatible)
- Breadcrumbs generator
- Responsive image helper
- Social share buttons
- Phone number formatting
- WhatsApp detection

**inc/template-tags.php**
- `caniincasa_entry_meta()` - Meta post (autore, data, commenti)
- `caniincasa_entry_categories()` - Categorie post
- `caniincasa_entry_tags()` - Tag post
- `caniincasa_post_thumbnail()` - Thumbnail responsive
- `caniincasa_pagination()` - Paginazione custom
- `caniincasa_related_posts()` - Articoli correlati
- `caniincasa_display_reading_time()` - Tempo di lettura

**inc/seo-redirects.php**
- Sistema redirect 301 (DISATTIVATO di default)
- Ricerca per campo `old_slug` custom
- Preservazione permalink esistenti durante import
- Schema.org breadcrumbs JSON-LD
- Schema.org Organization
- Pagina admin per gestione redirect
- Notice admin sullo stato redirect

#### Assets CSS

**assets/css/main.css**
- Header styles (top bar, main header, navigation)
- Search overlay con animazioni
- Mobile navigation off-canvas
- Mobile bottom nav fixed
- Footer styles multi-colonna
- Content styles (posts grid, entry meta)
- Utility classes responsive

**assets/css/responsive.css**
- Media queries per tablet (769-1024px)
- Media queries per mobile (<768px)
- Small mobile (<480px) ottimizzazioni
- Landscape orientation adjustments
- Print styles
- Retina display support
- Prefers-reduced-motion accessibility
- Dark mode media query

#### Assets JavaScript

**assets/js/navigation.js**
- Mobile menu toggle con animazioni
- Search overlay toggle
- Sticky header con shadow on scroll
- Mobile bottom nav active state
- Dropdown menu support
- Window resize handler
- Smooth scroll per anchor links
- Keyboard navigation (ESC to close)

**assets/js/main.js (jQuery)**
- Lazy loading images (native + fallback)
- AJAX form handler generico
- Back to top button
- Accordion component
- Tabs component
- Modal/popup system
- Copy to clipboard
- External links auto target="_blank"
- Print page handler
- Tooltip support (if library loaded)

**assets/js/customizer.js**
- Live preview per Customizer
- Real-time color updates
- Font size updates
- Container width updates

#### Template Parts

**template-parts/content/content.php**
- Template post generico
- Support singolo e archivio
- Thumbnail responsive
- Meta informazioni
- Read more link
- Tag footer (solo singolo)

**template-parts/content/content-none.php**
- No results template
- Messaggi contestuali (home, search, generic)
- Search form fallback

#### Funzionalità Sicurezza e Performance

**Sicurezza:**
- Blocco accesso wp-admin per non-admin
- Login redirect verso frontend dashboard
- Remove WordPress version
- Disable XML-RPC
- Nonce verification per AJAX

**Performance:**
- Lazy loading immagini nativo
- WebP support
- Emoji scripts rimossi
- CSS/JS versioning per cache busting
- Image sizes custom (small, medium, large, hero)

**SEO:**
- Title tag support
- Schema.org breadcrumbs
- Schema.org Organization
- Canonical URLs automatici
- RSS feed links

---

### [2025-11-17] - Implementazione Plugin "Caniincasa Core"

#### Struttura Base Plugin
**Percorso:** `wp-content/plugins/caniincasa-core/`

**File principale:** `caniincasa-core.php`
- Singleton pattern per inizializzazione
- Auto-loading file includes
- Activation/deactivation hooks
- Custom database table creation
- Enqueue scripts e styles (admin e public)
- AJAX localization

**Tabella Database Creata:**
- `wp_caniincasa_quiz_results` - Salvataggio risultati quiz
  - Campi: id, user_id, session_id, answers, results, created_at
  - Indici: user_id, session_id

#### Custom Post Types Implementati

**1. CPT Razze di Cani (`razze_di_cani`)**
**File:** `includes/cpt-razze.php`

Caratteristiche:
- Slug permalink: `razze-di-cani`
- Supports: title, editor, thumbnail, excerpt, custom-fields, revisions
- REST API enabled (base: `razze`)
- Archive page abilitato

Taxonomies custom:
- `razza_taglia` - Taglie (Piccola, Media, Grande, Gigante)
- `razza_gruppo` - Gruppi FCI (10 gruppi standard)

Funzionalità admin:
- Colonne custom: thumbnail, taglia, nazione origine
- Colonne sortable
- Auto-insert default terms all'attivazione

Campi previsti (da implementare con ACF):
- Numerici 1-5: affettuosità, socievolezza, adattabilità, tolleranza, intelligenza, toelettatura, esperienza, costo
- Info base: nazione, colorazioni, temperamento, taglia, peso, aspettativa vita
- Contenuti: descrizione, storia, aspetto, carattere, salute, addestramento, ideale_per, pro_contro

**2. CPT Strutture (5 tipologie)**
**File:** `includes/cpt-strutture.php`

Post Types creati:
1. `allevamenti` - slug: `allevamenti`
2. `veterinari` - slug: `veterinari`
3. `canili` - slug: `canili`
4. `pensioni_per_cani` - slug: `pensioni-per-cani`
5. `centri_cinofili` - slug: `centri-cinofili`

Caratteristiche comuni:
- Menu padre unificato "Strutture" (dashicon: location-alt)
- Supports: title, editor, thumbnail, excerpt, custom-fields, revisions
- REST API enabled
- Archive pages abilitate

Taxonomy condivisa:
- `provincia` - 107 province italiane pre-caricate
  - Slug: sigla provincia (es. "mi", "rm")
  - Description: sigla completa
  - Utilizzata da tutti i 5 CPT strutture

Funzionalità admin:
- Colonne custom: indirizzo, telefono
- Menu strutture centralizzato

**3. CPT Annunci 4 Zampe (`annunci_4zampe`)**
**File:** `includes/cpt-annunci.php`

Caratteristiche:
- Slug permalink: `annunci`
- Supports: title, editor, thumbnail, author, custom-fields
- REST API enabled (base: `annunci-4zampe`)
- Archive page: `annunci`

Funzionalità implementate:
- Moderazione automatica (pending status per non-admin)
- Sistema scadenza annunci configurabile (default 30 giorni)
- Notifiche email automatiche:
  - Annuncio ricevuto (pending)
  - Annuncio approvato (publish)
  - Annuncio scaduto
  - Annuncio rimosso
- Cron job giornaliero per check scadenze
- Auto-calcolo data scadenza alla pubblicazione

Campi previsti (da implementare con ACF):
- tipo_annuncio (cerco/offro)
- eta (cucciolo/adulto)
- tipo_cane (meticcio/razza)
- razza (relation a CPT razze)
- provincia
- descrizione
- immagini (max 3)
- scadenza_annuncio (auto)
- giorni_scadenza (custom override)
- contatto_preferito

Colonne admin custom:
- Tipo annuncio
- Stato (pending/publish/draft)
- Scadenza (con highlight scaduti)

**4. CPT Annunci Dogsitter (`annunci_dogsitter`)**
**File:** `includes/cpt-annunci.php`

Caratteristiche:
- Slug permalink: `annunci-dogsitter`
- Supports: title, editor, author, custom-fields
- REST API enabled (base: `annunci-dogsitter`)
- Stesse funzionalità moderazione/scadenza degli annunci 4 zampe

Campi previsti (da implementare con ACF):
- tipo (cerco/offro servizio)
- provincia
- disponibilita
- servizi_offerti (checkbox multipli)
- esperienza
- prezzo_indicativo
- messaggio
- contatti

Menu unificato:
- Menu padre "Annunci" per entrambi i CPT (dashicon: format-status)

#### Helper Functions

**File:** `includes/helpers.php`

Funzioni utility create:
- `caniincasa_get_province_array()` - Array completo province italiane
- `caniincasa_sanitize_rating()` - Sanitize valore 1-5
- `caniincasa_get_rating_stars()` - HTML stelle rating
- `caniincasa_user_can_edit_annuncio()` - Permission check
- `caniincasa_get_annuncio_status_badge()` - Badge HTML stato
- `caniincasa_is_annuncio_expired()` - Check scadenza
- `caniincasa_days_until_expiration()` - Calcolo giorni rimanenti
- `caniincasa_format_phone_display()` - Formattazione numero italiano
- `caniincasa_get_whatsapp_link()` - Genera link WhatsApp
- `caniincasa_get_breadcrumb_data()` - Dati breadcrumb Schema.org
- `caniincasa_verify_nonce()` - Verifica nonce con error handling
- `caniincasa_require_login()` - Login check per AJAX

#### Opzioni Plugin

Opzioni salvate all'attivazione:
- `caniincasa_annunci_moderation` - true (moderazione obbligatoria)
- `caniincasa_annunci_expiry_days` - 30 (giorni scadenza default)
- `caniincasa_quiz_enabled` - true (quiz abilitato)

#### Scheduled Events

Cron jobs registrati:
- `caniincasa_check_expiration` - Giornaliero, verifica scadenza annunci
  - Marca come draft gli annunci scaduti
  - Invia email notifica autori

---

### [2025-11-17] - Riepilogo Stato Implementazione

#### ✅ Completato

**Tema WordPress:**
- [x] Struttura completa tema responsive
- [x] Header con top bar e navigazione mobile
- [x] Footer con widget areas
- [x] Mobile bottom navigation
- [x] Search overlay
- [x] WordPress Customizer (colori, font, layout, dark mode)
- [x] Template functions e template tags
- [x] Sistema SEO e redirect 301 (disattivato di default)
- [x] Schema.org breadcrumbs e Organization
- [x] CSS responsive completo
- [x] JavaScript navigazione e interattività
- [x] Template parts per contenuti
- [x] Blocco wp-admin per non-admin
- [x] Lazy loading immagini
- [x] WebP support

**Plugin Caniincasa Core:**
- [x] Struttura base plugin con singleton
- [x] CPT Razze di Cani con taxonomies
- [x] CPT 5 Strutture (allevamenti, veterinari, canili, pensioni, centri cinofili)
- [x] CPT Annunci 4 Zampe
- [x] CPT Annunci Dogsitter
- [x] Taxonomy Province (107 province italiane)
- [x] Sistema moderazione annunci
- [x] Sistema scadenza annunci con notifiche email
- [x] Helper functions complete
- [x] Database table per quiz results
- [x] Cron job scadenza annunci

#### 🚧 Da Implementare

**ACF Fields Configuration:**
- [ ] Campi ACF per Razze di Cani (tutti i campi numerici e testuali)
- [ ] Campi ACF per Strutture (indirizzo, contatti, geolocalizzazione)
- [ ] Campi ACF per Annunci 4 Zampe
- [ ] Campi ACF per Annunci Dogsitter

**Funzionalità Core:**
- [ ] Sistema Quiz interattivo (9 domande + algoritmo matching)
- [ ] CSV Importer per tutti i CPT
- [ ] REST API endpoints custom
- [ ] Dashboard utente frontend
- [ ] AJAX handlers per filtri
- [ ] Shortcodes per frontend

**Template Files:**
- [ ] Template homepage (front-page.php)
- [ ] Template singola razza (single-razze_di_cani.php)
- [ ] Template archivio razze (archive-razze_di_cani.php)
- [ ] Template singola struttura (single-{struttura}.php)
- [ ] Template archivi strutture
- [ ] Template annunci (single e archive)
- [ ] Template dashboard utente (template-dashboard.php)

**Admin Features:**
- [ ] Admin menus e settings pages
- [ ] Admin CSS e JavaScript
- [ ] Bulk actions per annunci

**Assets Mancanti:**
- [ ] public.css e public.js per plugin
- [ ] admin.css e admin.js per plugin

#### 📊 Statistiche Progetto

**File Creati:** 23
**Linee di Codice:** ~3,800
**Custom Post Types:** 7
**Taxonomies:** 3
**Database Tables:** 1
**Widget Areas:** 6
**Menu Locations:** 4

#### 🎯 Prossimi Passi Prioritari

1. Configurare campi ACF per tutti i CPT
2. Creare sistema Quiz con algoritmo matching
3. Implementare CSV Importer
4. Creare template files per frontend
5. Implementare Dashboard utente
6. Sviluppare filtri AJAX per archivi
7. Testing e debugging completo
