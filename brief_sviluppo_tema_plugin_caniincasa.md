# Brief strutturato per sviluppo tema e plugin WordPress – caniincasa.it&#x20;

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
     - Se l’URL richiesto non esiste, controlla se è presente in un campo `old_slug`.
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

- `allevamenti`  file da importare (Allevamenti-Export-2025-November-17-1454.csv)
- `veterinari`  file da importare (Pensioni-per-Cani-Export-2025-November-17-1518.csv)
- `canili`  file da importare (Canili-Export-2025-November-17-1510.csv)
- `pensioni_per_cani`  file da importare (Pensioni-per-Cani-Export-2025-November-17-1518.csv)
- `centri_cinofili`  file da importare (Centri-Cinofili-Export-2025-November-17-1516.csv)

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
- Permetti agli utenti registrati di proporre e inserire la propria struttura all’interno delle directory. Prevedi quindi uno o più form di inserimento dedicati, con solo i campi essenziali (quelli visibili nelle schede pubbliche, non tutti i campi utilizzati per l’import CSV). Le pagine di inserimento/modifica dati devono essere accessibili solo dopo login e raggiungibili anche dalle schede delle strutture tramite apposito pulsante o collegamento.
- Routing:
  - Mantenere i vecchi permalink quando presenti in `permalink_esistente`.
  - Se necessario, usare filtro `post_type_link` per forzare struttura URL compatibile.

#### Template singola struttura

- Layout desktop: \*\*2/3 contenuto + 1/3 sidebar (alcuni screenshot screenshotallevamento.png , archiviallevamenti.png)
- Layout mobile: colonna unica full-width (stack verticale)
- Contenuti principali:
  - Titolo struttura
  - Indirizzo + mappa (Google Maps / Leaflet, via campi lat/long)
  - Descrizione / servizi
  - Orari, contatti, sito web
- Sidebar:
  - Navigazione contestuale: link a archivio CPT (es. "Torna all’elenco allevamenti")
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

- `tipo_annuncio`\* (select: cerco / offro) – obbligatorio
- `eta`\* (cucciolo / adulto) – obbligatorio
- `tipo_cane`\* (meticcio / razza) – obbligatorio
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

## 12. Sviluppi Realizzati

### Data: Novembre 2025

#### 12.1. Sistema Gestione Autore Annunci

**Funzionalità implementata**: Sistema completo per la gestione degli autori degli annunci da parte degli amministratori.

**Componenti sviluppati**:

1. **Colonna Autore in Admin**
   - Aggiunta colonna "Autore" nella lista annunci (CPT `annunci_4zampe` e `annunci_dogsitter`)
   - Visualizzazione nome autore con link al profilo
   - Ordinamento per autore disponibile
   - Badge "Anonimo" per annunci di utenti non registrati

2. **Filtro Dropdown per Autore**
   - Dropdown nella barra filtri admin per filtrare annunci per autore
   - Opzione "Tutti gli autori" per rimuovere filtro
   - Mostra solo autori che hanno effettivamente pubblicato annunci

3. **Meta Box Cambio Autore**
   - Meta box nella sidebar dell'editor annunci
   - Dropdown con elenco completo autori WordPress
   - Salvataggio sicuro con verifica nonce e capabilities

4. **Quick Edit Support**
   - Campo autore disponibile nel quick edit inline
   - Aggiornamento rapido senza aprire l'editor completo

**File modificati**:
- `wp-content/plugins/caniincasa-core/includes/cpt-annunci.php` (linee 634-880)

---

#### 12.2. Sistema Utenti Anonimi per Annunci

**Funzionalità implementata**: Possibilità per amministratori di creare annunci per utenti non registrati, inserendo manualmente i dati di contatto.

**Componenti sviluppati**:

1. **Meta Box "Utente Anonimo"**
   - Checkbox per attivare modalità anonima
   - Campi dedicati:
     - Nome completo (obbligatorio)
     - Email (obbligatorio, con validazione)
     - Telefono (opzionale)
   - Mostra/nascondi campi con JavaScript in base allo stato checkbox
   - Validazione completa lato server con messaggi admin notice

2. **Filtro Admin "Tipo Utente"**
   - Dropdown per filtrare: Tutti / Solo Anonimi / Solo Registrati
   - Integrato nella barra filtri admin degli annunci

3. **Badge Identificativo**
   - Badge "Anonimo" nella colonna autore per identificazione rapida
   - Styling distintivo per differenziare da utenti registrati

4. **Sistema di Validazione**
   - Controllo obbligatorietà campi se modalità anonima attiva
   - Validazione formato email
   - Admin notice informativi su salvataggio/errori
   - Uso transient per messaggi persistenti dopo redirect

**Meta fields creati**:
- `_is_anonymous_user` (boolean 1/0)
- `_anonymous_name` (testo)
- `_anonymous_email` (email validata)
- `_anonymous_phone` (testo opzionale)

**File modificati**:
- `wp-content/plugins/caniincasa-core/includes/cpt-annunci.php` (linee 881-1192)

**Documentazione aggiunta**:
- `wp-content/plugins/caniincasa-core/includes/ANONYMOUS_USERS_USAGE.md`

---

#### 12.3. Campi Contatto Specifici per Annuncio

**Funzionalità implementata**: Email e telefono specifici per ogni annuncio, inizialmente popolati dal profilo autore ma modificabili indipendentemente.

**Componenti sviluppati**:

1. **Meta Box "Dati di Contatto Annuncio"**
   - Campo Email annuncio (con validazione)
   - Campo Telefono annuncio
   - Auto-popolamento da profilo autore alla creazione
   - Visualizzazione dati profilo autore come riferimento
   - Salvataggio indipendente rispetto al profilo

2. **Helper Function**
   - Funzione `caniincasa_get_annuncio_contact_info($post_id)`
   - Gestisce priorità: dati anonimi > dati annuncio > dati profilo
   - Ritorna array strutturato con tutti i dati contatto
   - Fallback automatico a profilo autore se campi vuoti

3. **Sistema di Priorità Dati**
   ```
   Priorità 1: Se anonimo → usa dati anonimi
   Priorità 2: Se campo annuncio compilato → usa dato annuncio
   Priorità 3: Fallback → usa dato profilo autore
   ```

**Meta fields creati**:
- `_annuncio_email` (email specifica annuncio)
- `_annuncio_phone` (telefono specifico annuncio)

**File modificati**:
- `wp-content/plugins/caniincasa-core/includes/cpt-annunci.php` (linee 1193-1408)

**Documentazione aggiornata**:
- `wp-content/plugins/caniincasa-core/includes/ANONYMOUS_USERS_USAGE.md` (aggiunta sezione campi specifici)

---

#### 12.4. Sistema GDPR Cookie Banner

**Funzionalità implementata**: Banner cookie conforme GDPR con gestione consensi e modal impostazioni avanzate.

**Componenti sviluppati**:

1. **Banner Cookie**
   - Apparizione automatica dopo 1 secondo (solo se consenso non dato)
   - Testo personalizzabile
   - 3 pulsanti azione: Accetta tutti / Rifiuta / Impostazioni
   - Animazioni CSS smooth (translateY + opacity)
   - Persistenza consenso 365 giorni

2. **Modal Impostazioni Cookie**
   - 4 categorie gestibili:
     - Necessari (sempre attivi, non disattivabili)
     - Funzionali (toggle on/off)
     - Analytics (toggle on/off)
     - Marketing (toggle on/off)
   - Toggle switch personalizzati
   - Descrizione per ogni categoria
   - Pulsanti: Salva Preferenze / Accetta Tutti

3. **API Pubblica JavaScript**
   - `CaniincasaCookieConsent.getPreferences()` - Legge preferenze correnti
   - `CaniincasaCookieConsent.openSettings()` - Apre modal impostazioni
   - `CaniincasaCookieConsent.revokeConsent()` - Revoca consenso e ricarica

4. **Strumenti Debug**
   - Script `gdpr-debug.js` per troubleshooting
   - Console logging completo (API, banner, cookie, CSS)
   - UI debug controls con pulsanti test overlay
   - Verifica automatica caricamento componenti

**Cookie salvato**:
```json
{
  "necessary": true,
  "functional": boolean,
  "analytics": boolean,
  "marketing": boolean
}
```

**File creati/modificati**:
- `wp-content/themes/caniincasa-theme/template-parts/cookie-banner.php`
- `wp-content/themes/caniincasa-theme/assets/css/gdpr-cookie.css`
- `wp-content/themes/caniincasa-theme/assets/js/gdpr-cookie.js`
- `wp-content/themes/caniincasa-theme/assets/js/gdpr-debug.js`
- `wp-content/themes/caniincasa-theme/functions.php` (enqueue scripts/styles)

**Documentazione aggiunta**:
- `GDPR_TEST_HELPER.md` - Guida completa testing e troubleshooting

---

#### 12.5. Pagina Contatti Personalizzabile

**Funzionalità implementata**: Pagina contatti completamente gestibile da Customizer WordPress, integrazione Contact Form 7.

**Componenti sviluppati**:

1. **Sezioni Customizer**
   - **Hero Contatti**: Immagine background, titolo, sottotitolo
   - **Form Contatti**: Shortcode Contact Form 7 (obbligatorio)
   - **Informazioni Contatto**: Indirizzo, telefono, email, WhatsApp
   - **Orari Apertura**: Toggle on/off, testo HTML personalizzabile
   - **Social Media**: Facebook, Instagram, Twitter, YouTube
   - **Mappa Google**: Toggle on/off, embed iframe

2. **Integrazione Contact Form 7**
   - Rimozione form HTML hardcoded
   - Campo shortcode nel Customizer
   - Messaggio admin se shortcode mancante con link diretto
   - Messaggio generico per utenti se form non configurato
   - Supporto placeholder e descrizioni estese

3. **Sistema Messaggi**
   - Admin: Warning giallo con link a Customizer se shortcode mancante
   - Utenti: Messaggio generico "disponibile a breve"
   - Autofocus automatico su sezione Customizer tramite link

4. **Icone SVG Inline**
   - Icone vettoriali per: indirizzo, telefono, email, WhatsApp
   - Ottimizzazione caricamento (no font icon esterni)
   - Colorazione dinamica tramite `currentColor`

**Campi Customizer implementati**:
- `contatti_hero_image` - Immagine hero
- `contatti_title` - Titolo principale
- `contatti_subtitle` - Sottotitolo
- `contatti_form_title` - Titolo form
- `contatti_form_text` - Testo intro form
- `contatti_form_shortcode` - Shortcode CF7 ⭐
- `contatti_show_info` - Toggle info contatto
- `contatti_address`, `contatti_phone`, `contatti_email`, `contatti_whatsapp`
- `contatti_show_hours` - Toggle orari
- `contatti_hours_text` - Testo orari (HTML)
- `contatti_show_social` - Toggle social
- `contatti_social_facebook/instagram/twitter/youtube`
- `contatti_show_map` - Toggle mappa
- `contatti_map_embed` - Iframe Google Maps

**File modificati**:
- `wp-content/themes/caniincasa-theme/template-contatti.php` (rimozione form HTML, shortcode CF7)
- `wp-content/themes/caniincasa-theme/inc/customizer.php` (sezioni Customizer)

**Documentazione aggiunta**:
- `GUIDA_PAGINA_CONTATTI.md` - Guida completa utente per configurazione

---

#### 12.6. Fix CSS e Styling

**12.6.1. Back to Top Button**

**Problema risolto**: Bottone "torna in alto" presente ma senza formattazione e position fixed.

**Soluzioni implementate**:
- Position fixed (bottom: 30px, right: 30px)
- Styling completo: cerchio 50x50px, background primario, ombra
- Animazioni smooth: opacity + visibility + translateY
- Stati: nascosto di default, visibile con classe `.show`
- Hover effect: scale 1.1 + ombra aumentata
- Responsive: su mobile bottom: 90px (sopra bottom nav)
- Z-index appropriato per sovrapposizione

**CSS aggiunto**:
```css
.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--color-primary);
    border-radius: 50%;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: all 0.3s ease;
    z-index: var(--z-fixed);
}

.back-to-top.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
```

**File modificato**:
- `wp-content/themes/caniincasa-theme/assets/css/main.css` (linee 710-766)

---

**12.6.2. Footer Widget Menu**

**Problema risolto**: Menu footer con titoli e link invisibili (stesso colore sfondo scuro), elenchi puntati visibili.

**Soluzioni implementate**:
- Titoli widget: color primario (#FFCC70), visibili su sfondo scuro
- Link: rgba(255, 255, 255, 0.8) per contrasto ottimale
- Rimozione bullet points: `list-style: none` su tutti `ul` e `li`
- Hover effect: cambio colore + animazione slide a destra (padding-left)
- Reset completo margin/padding su liste

**CSS aggiunto**:
```css
.footer-widgets .widget h2,
.footer-widgets .widget h3,
.footer-widgets .widget .widgettitle {
    color: var(--color-primary);
}

.footer-widgets .widget ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.footer-widgets .widget a {
    color: rgba(255, 255, 255, 0.8);
    transition: color var(--transition-fast);
}

.footer-widgets .widget a:hover {
    color: var(--color-primary);
    padding-left: 5px;
}
```

**File modificato**:
- `wp-content/themes/caniincasa-theme/assets/css/main.css` (linee 559-616)

---

**12.6.3. Mobile Bottom Navigation**

**Problema risolto**: Menu mobile inferiore con elementi impilati verticalmente invece che orizzontalmente.

**Soluzioni implementate**:
- Consolidamento dichiarazioni CSS duplicate
- Explicit `flex-direction: row` per layout orizzontale
- `width: 100%` per occupare tutta la larghezza
- `justify-content: space-around` per distribuzione equa
- Item: `flex: 1 1 auto` per flessibilità
- Item: `max-width: 80px` per evitare dimensioni eccessive
- Item: `flex-direction: column` per icon sopra testo
- Padding e gap ottimizzati per touch target (min 44x44px)

**CSS aggiornato**:
```css
.mobile-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100%;
    display: none;
    flex-direction: row;
    justify-content: space-around;
    align-items: center;
    z-index: var(--z-header);
}

.mobile-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    flex: 1 1 auto;
    max-width: 80px;
    min-width: 60px;
}
```

**File modificato**:
- `wp-content/themes/caniincasa-theme/assets/css/main.css` (linee 432-465)

---

### Riepilogo File Modificati (Sessione Novembre 2025)

**Plugin Core**:
- `wp-content/plugins/caniincasa-core/includes/cpt-annunci.php` (+774 linee circa)

**Tema**:
- `wp-content/themes/caniincasa-theme/template-contatti.php` (refactoring form)
- `wp-content/themes/caniincasa-theme/inc/customizer.php` (sezioni Contatti)
- `wp-content/themes/caniincasa-theme/assets/css/main.css` (+250 linee circa)
- `wp-content/themes/caniincasa-theme/template-parts/cookie-banner.php` (nuovo)
- `wp-content/themes/caniincasa-theme/assets/css/gdpr-cookie.css` (nuovo)
- `wp-content/themes/caniincasa-theme/assets/js/gdpr-cookie.js` (nuovo)
- `wp-content/themes/caniincasa-theme/assets/js/gdpr-debug.js` (nuovo)

**Documentazione Aggiunta**:
- `GDPR_TEST_HELPER.md` - Guida testing cookie banner
- `GUIDA_PAGINA_CONTATTI.md` - Guida configurazione pagina contatti
- `REPORT_ANALISI_STRUTTURE.md` - Analisi strutture directory
- `wp-content/plugins/caniincasa-core/includes/ANONYMOUS_USERS_USAGE.md` - Guida utenti anonimi

**Branch di Sviluppo**:
- `claude/fix-menu-dropdown-01D2DrV73N7ds551ex9Ntk2F`

**Compatibilità**:
- WordPress 6.x
- PHP 8.1+
- Contact Form 7 (richiesto per pagina Contatti)
- Advanced Custom Fields Pro (per custom fields)

**Testing Effettuato**:
- Test responsività mobile/tablet/desktop
- Test form Contatti con CF7
- Test cookie banner cross-browser
- Test filtri admin annunci
- Test validazione campi anonimi

**Note Tecniche**:
- Tutti i form includono nonce verification per sicurezza
- Sanitizzazione input con `sanitize_text_field()`, `sanitize_email()`, `esc_html()`, `esc_url()`
- Capabilities check: `current_user_can('edit_theme_options')`, `current_user_can('edit_posts')`
- Transient utilizzati per messaggi admin persistenti
- AJAX non utilizzato in questa fase (filtri admin nativi WordPress)
- CSS variabili custom properties per manutenibilità
- Approccio mobile-first confermato

---
