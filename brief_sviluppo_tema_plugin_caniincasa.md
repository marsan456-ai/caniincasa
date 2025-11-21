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

## 12. Espansione Contenuti e Funzionalità (PRIORITÀ)

### 12.1. Comparatore Razze (PRIORITÀ MASSIMA)

**Obiettivo**: Permettere confronto visivo fino a 3 razze simultaneamente.

**Funzionalità chiave**:
- Interfaccia side-by-side con colonne per ogni razza selezionata
- Confronto parametri: fisici, caratteriali, cure necessarie, ambiente ideale, famiglia
- Sistema salvataggio comparazioni (utenti loggati)
- Condivisione social della comparazione
- Scoring compatibilità basato su profilo utente (se disponibile dal quiz)

**Caratteristiche da confrontare**:

| Categoria | Dettagli |
|-----------|----------|
| **Fisici** | Taglia, peso, altezza, tipo pelo, colori |
| **Caratteriali** | Energia, socialità, territorialità, addestrabilità |
| **Cure** | Toelettatura, esercizio necessario, costi medi |
| **Ambiente** | Adatto appartamento, necessità giardino, clima ideale |
| **Famiglia** | Con bambini, con altri cani, con gatti, per primo cane |

**Posizionamento**:
- Pagina dedicata `/comparatore-razze/`
- Widget/shortcode in archivio razze
- Link rapido in ogni scheda razza

---

### 12.2. Mega Menu con Categorizzazione Taglia (PRIORITÀ MASSIMA)

**Obiettivo**: Riorganizzare navigazione principale per facilitare scoperta contenuti.

**Struttura Menu Proposta**:

```
RAZZE
├─ Per Taglia
│  ├─ Razze Piccole (< 10 kg)
│  ├─ Razze Medie (10-25 kg)
│  ├─ Razze Grandi (25-45 kg)
│  └─ Razze Giganti (> 45 kg)
├─ Per Carattere
│  ├─ Cani da Famiglia
│  ├─ Cani Attivi/Sportivi
│  ├─ Cani da Guardia
│  ├─ Cani da Compagnia
│  └─ Cani da Caccia
├─ Razze Italiane
│  └─ [Lista dinamica top razze italiane]
└─ Tutte le Razze A-Z

GUIDA CANI (nuova sezione)
├─ Primo Cane
│  ├─ Come Scegliere
│  ├─ Preparare la Casa
│  ├─ Primi Giorni
│  └─ Attrezzatura Necessaria
├─ Salute & Benessere
│  ├─ Vaccinazioni
│  ├─ Parassiti
│  ├─ Malattie Comuni
│  └─ Quando Andare dal Veterinario
├─ Educazione
│  ├─ Comandi Base
│  ├─ Socializzazione
│  ├─ Problemi Comportamentali
│  └─ Addestramento Avanzato
└─ Vita Quotidiana
   ├─ Alimentazione
   ├─ Toelettatura
   ├─ Esercizio Fisico
   └─ Viaggiare col Cane

STRUMENTI (nuova sezione)
├─ Calcolatori
│  ├─ Età Umana
│  ├─ Peso Ideale
│  ├─ Costo Mantenimento
│  └─ Quantità Cibo
├─ Comparatore Razze
├─ Quiz Compatibilità (già esistente)
└─ Directory (link a sezioni esistenti)

MAGAZINE (nuova sezione)
├─ Ultimi Articoli
├─ Guide Approfondite
├─ News & Aggiornamenti
└─ Storie di Cani

ANNUNCI (già esistente)
SERVIZI (directory esistenti)
```

**Caratteristiche Mega Menu**:
- Dropdown espanso con colonne multiple (desktop)
- Icone per ogni categoria
- Contatori dinamici (es. "23 razze piccole")
- Preview immagine razza in evidenza
- Mobile: accordion collassabile con stessa gerarchia

---

### 12.3. Calcolatori Interattivi (4 tool)

Nuova sezione "Strumenti" con 4 calcolatori dedicati.

#### 12.3.1. Calcolatore Età Umana

**Input**:
- Età del cane (anni)
- Taglia/razza

**Output**:
- 3 metodi di calcolo: tradizionale (×7), scientifico (formula logaritmica), specifico per taglia
- Grafico invecchiamento progressivo
- Fase della vita (cucciolo/adulto/maturo/senior)
- Consigli salute per età/taglia

**Funzionalità extra**:
- Salvare calcolo nel profilo
- Condivisione risultato social
- Link a guide correlate per fase vita

#### 12.3.2. Calcolatore Peso Ideale

**Input**:
- Peso attuale
- Razza (se conosciuta)
- Quiz valutazione visiva (Body Condition Score 1-9)

**Quiz BCS interattivo**:
- Domande con immagini (vista dall'alto, laterale)
- Valutazione palpazione costole
- Presenza vita/addome

**Output**:
- Score BCS (1-9) con interpretazione
- Peso ideale stimato per razza
- Kg da perdere/guadagnare
- Piano settimanale con obiettivi sicuri
- Timeline raggiungimento peso forma

**Funzionalità extra**:
- Tracker peso nel tempo (utenti loggati)
- Promemoria controlli settimanali
- Link a calcolatore cibo per dieta

#### 12.3.3. Calcolatore Costo Mantenimento

**Input**:
- Peso/taglia cane
- Età
- Tipo cibo (economy/premium/super premium/BARF)
- Tipo pelo (toelettatura)
- Metodo toelettatura (fai-da-te/professionale)
- Regione Italia (prezzi variano)
- Giorni pensione/anno
- Assicurazione (sì/no)

**Output**:
- Costo totale annuale
- Breakdown per categoria: cibo, salute, toelettatura, accessori, assicurazione, altro
- Costo mensile e giornaliero
- Grafico percentuali spesa

**Categorie costo**:
- Alimentazione (base su peso e tipo)
- Salute (visite, vaccini, antiparassitari, emergenze)
- Toelettatura
- Accessori e giochi
- Assicurazione (opzionale)
- Addestramento (primo anno)
- Pensione/dog sitter

**Funzionalità extra**:
- Confronto risparmio con scelte diverse
- Consigli personalizzati per ridurre costi
- Esportazione budget PDF

#### 12.3.4. Calcolatore Quantità Cibo

**3 modalità di calcolo**:

**A) Crocchette**
- Input: peso, età, livello attività, kcal/kg del prodotto
- Output: grammi/giorno, porzioni per pasto, kg/mese
- Programma alimentazione consigliato (orari pasti)

**B) Dieta BARF**
- Input: peso cane
- Output: quantità totale (2-3% peso corporeo)
- Breakdown composizione: 70% carne/ossa, 10% frattaglie, 15% verdure, 5% integratori
- Grammi per categoria
- Kg/mese necessari

**C) Alimentazione Casalinga**
- Input: peso, età, livello attività
- Output: grammi totali/giorno
- Composizione bilanciata: 40% proteine, 30% carboidrati, 25% verdure, 5% grassi
- Ricetta esempio giornaliera
- Lista spesa settimanale

**Funzionalità extra**:
- Salva ricetta preferita
- Alert importanti (ingredienti tossici, consultare veterinario)
- Collegamento a calcolatore costi
- Video tutorial preparazione pasti

---

### 12.4. Nuova Architettura Contenuti

#### CPT "Guida" (`guida_cani`)

**Obiettivo**: Creare sezione educativa completa per proprietari.

**Categorie principali**:

1. **Primo Cane**
   - Scegliere razza giusta
   - Preparare casa per arrivo cucciolo
   - Checklist primi giorni
   - Attrezzatura indispensabile
   - Budget primo anno

2. **Salute & Benessere**
   - Calendario vaccinazioni
   - Guida antiparassitari completa
   - Sintomi malattie comuni
   - Quando preoccuparsi e chiamare veterinario
   - Pronto soccorso base

3. **Educazione**
   - Comandi base step-by-step
   - Socializzazione cucciolo (finestre temporali)
   - Risolvere problemi comportamentali comuni
   - Tecniche addestramento avanzato
   - Capire linguaggio del cane

4. **Vita Quotidiana**
   - Alimentazione sana e bilanciata
   - Toelettatura casalinga (per tipo pelo)
   - Esercizio fisico per età e taglia
   - Viaggiare col cane (documenti, consigli)
   - Gestire cane in appartamento

**Caratteristiche template guida**:
- Table of Contents auto-generato (da H2)
- Tempo lettura stimato
- Livello difficoltà (principiante/intermedio/esperto)
- Guide correlate sidebar
- Download PDF guida
- Checklist stampabili
- Video embedded (quando disponibili)

#### CPT "Magazine" (`magazine`)

**Obiettivo**: Contenuti editoriali, news, storie.

**Categorie**:
- News & Attualità cinofila
- Storie di Cani (user-generated benvenuto)
- Interviste Esperti (veterinari, educatori, allevatori)
- Prodotti & Recensioni
- Viaggi & Destinazioni Dog-Friendly
- Nutrizione & Ricette
- Sport & Attività (agility, disc dog, etc.)

**Differenza Blog vs Magazine**:
- **Blog**: articoli brevi, consigli rapidi, contenuti evergreen
- **Magazine**: articoli approfonditi, reportage, contenuti stagionali/temporali

---

### 12.5. Roadmap Implementazione

#### Fase 1: Foundation (Settimane 1-3)
- [ ] Implementazione mega menu con nuova struttura
- [ ] Template archivi per taglia (piccola/media/grande/gigante)
- [ ] Template archivi per carattere
- [ ] Pagina "Razze Italiane" dedicata
- [ ] Setup ACF per tutti i nuovi campi necessari

#### Fase 2: Comparatore (Settimane 4-5)
- [ ] UI comparatore frontend
- [ ] Sistema selezione e confronto razze
- [ ] Salvataggio comparazioni (utenti loggati)
- [ ] Condivisione social

#### Fase 3: Calcolatori (Settimane 6-9)
- [ ] Calcolatore Età Umana + grafici
- [ ] Calcolatore Peso Ideale + BCS quiz
- [ ] Calcolatore Costi + breakdown dettagliato
- [ ] Calcolatore Cibo (3 modalità) + ricette
- [ ] Pagina principale "Strumenti" che raccoglie tutto

#### Fase 4: Sistema Contenuti (Settimane 10-12)
- [ ] CPT Guida con categorie gerarchiche
- [ ] Template singola guida (TOC, sidebar, related)
- [ ] CPT Magazine con categorie
- [ ] Template magazine con focus visual
- [ ] Sistema autori/contributor
- [ ] Widget "Articoli correlati" intelligente

#### Fase 5: Ottimizzazione & Launch (Settimane 13-14)
- [ ] Ottimizzazione mobile tutti i nuovi elementi
- [ ] SEO: schema markup per guide/calcolatori
- [ ] Performance: lazy loading, caching
- [ ] Test utente su calcolatori
- [ ] Scrittura prime 10 guide prioritarie
- [ ] Soft launch nuove sezioni

---

### 12.6. Contenuti Prioritari da Creare

**Guide da scrivere subito (top 10)**:

1. Come scegliere la razza giusta per te (Primo Cane)
2. Preparare casa per arrivo cucciolo (Primo Cane)
3. Calendario vaccinazioni completo (Salute)
4. Comandi base: seduto, terra, resta, vieni (Educazione)
5. Socializzazione cucciolo: guida settimana per settimana (Educazione)
6. Alimentazione sana: guida completa (Vita Quotidiana)
7. Toelettatura casalinga per tipo pelo (Vita Quotidiana)
8. Quanto esercizio serve al tuo cane (Vita Quotidiana)
9. Viaggiare col cane: documenti e consigli (Vita Quotidiana)
10. Cane in appartamento: guida completa (Vita Quotidiana)

**Magazine da lanciare subito**:

1. Top 10 destinazioni dog-friendly Italia
2. Intervista veterinario: errori comuni proprietari
3. Razze emergenti 2025: tendenze adozioni
4. Storia commovente: cane adottato cambia vita famiglia
5. Recensione: migliori crocchette rapporto qualità/prezzo

---

### 12.7. Metriche di Successo

**KPI da monitorare**:

**Engagement**:
- % visitatori che usano comparatore (target: >30%)
- Calcoli completati/mese per tool (target: >500 ciascuno)
- Tempo medio su guide (target: >3 min)
- Bounce rate sezioni nuove (target: <40%)

**Conversione**:
- % utenti che si registrano dopo aver usato calcolatori (target: >15%)
- Quiz → Annunci visualizzati (target: >25%)
- Guide → Directory cliccate (target: >20%)

**Contenuti**:
- Guide visualizzate/mese (target: >5000)
- Download PDF guide (target: >200/mese)
- Condivisioni social calcolatori (target: >100/mese)

**SEO**:
- Posizionamento "comparatore razze cani" (target: top 5)
- Posizionamento "calcolatore età cane" (target: top 3)
- Traffico organico sezione guide (target: +40% in 6 mesi)

---

### 12.8. Note Implementative

**Integrazione con esistente**:
- Calcolatori utilizzano dati CPT `razze_di_cani` esistente
- Comparatore si basa su campi ACF già definiti (sezione 3.2)
- Guide linkano a razze, directory, annunci pertinenti
- Magazine può incorporare widget quiz/calcolatori

**Mobile-first**:
- Calcolatori con form step-by-step su mobile
- Risultati calcolatori: card scrollabili verticalmente
- Comparatore: swipe tra razze su mobile (non side-by-side)
- Mega menu: accordion con ricerca rapida

**SEO specifico**:
- Schema markup `HowTo` per guide procedurali
- Schema `SoftwareApplication` per calcolatori
- Breadcrumb completi su tutte le nuove sezioni
- Meta description dinamiche con risultati calcolatori

**Performance**:
- Calcolatori: calcolo client-side (no server load)
- Cache risultati comuni (es. peso ideale razze note)
- Lazy load grafici Chart.js
- Preload font/CSS critici per mega menu

---

### 12.9. Budget Aggiuntivo Stimato

**Sviluppo nuove funzionalità**:
- Comparatore razze: 20 ore
- 4 Calcolatori interattivi: 40 ore
- Mega menu responsive: 12 ore
- CPT Guida + template: 15 ore
- CPT Magazine + template: 12 ore
- Testing & ottimizzazione: 15 ore
- **Totale sviluppo**: ~114 ore

**Contenuti**:
- Scrittura 10 guide (€150 cad): €1.500
- Scrittura 5 articoli magazine (€200 cad): €1.000
- Immagini/illustrazioni calcolatori: €500
- Video tutorial (opzionale): €1.500
- **Totale contenuti**: €3.000 (€4.500 con video)

**Totale espansione**: €3.000-4.500 contenuti + sviluppo

---

## 13. Sviluppi Realizzati

### 2025-11-21: Sistema Messaggistica con Threading

**Implementato**:
- Sistema completo messaggi privati tra utenti
- Funzionalità risposta con threading (campo `parent_id`)
- Visualizzazione thread conversazioni
- Caricamento lazy risposte via AJAX
- Differenziazione visiva messaggi inviati/ricevuti
- Migrazione automatica database per `parent_id`
- Nonce security su tutti endpoint
- Fix modal caricamento (get_footer vs wp_footer)

**File modificati**:
- `wp-content/plugins/caniincasa-core/includes/messaging-system.php`
- `wp-content/themes/caniincasa-theme/template-dashboard.php`
- `wp-content/themes/caniincasa-theme/assets/js/messaging.js`
- `wp-content/themes/caniincasa-theme/assets/css/messaging.css`

**Commit**: Branch `claude/review-project-brief-01HAw2pN3fajanEyQ7zUSDdV`
- 15 commit totali
- Status: ✅ Pronto per merge
- Documentazione: `PR_DESCRIPTION.md`, `CREA_PR.md`

