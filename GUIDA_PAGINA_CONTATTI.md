# Guida Personalizzazione Pagina Contatti

## Panoramica
La pagina Contatti è completamente personalizzabile dal **Customizer di WordPress**. Tutti i contenuti possono essere modificati senza toccare codice.

## Come Accedere al Customizer

1. Vai su **Aspetto → Personalizza**
2. Apri il pannello **"Pagina Contatti"**

Oppure, se sei sulla pagina Contatti, clicca su **"Personalizza"** nella barra admin in alto.

## Sezioni Disponibili

### 1. 📝 Hero Contatti
- **Immagine Background**: Carica immagine sfondo hero
- **Titolo**: Testo principale (default: titolo pagina)
- **Sottotitolo**: Testo descrittivo sotto il titolo

### 2. 📋 Form Contatti ⭐ IMPORTANTE
- **Titolo Form**: "Inviaci un Messaggio" (modificabile)
- **Testo Introduttivo**: Descrizione sopra il form
- **⭐ Shortcode Contact Form 7**: **OBBLIGATORIO**

  **Come Configurare:**
  1. Vai su **Contatti → Contact Form 7**
  2. Crea un nuovo form (o usa uno esistente)
  3. **Copia lo shortcode** (es: `[contact-form-7 id="123" title="Contatti"]`)
  4. **Incolla nel Customizer** nel campo "Shortcode Contact Form 7"
  5. Salva e pubblica

  **⚠️ Se non inserisci lo shortcode:**
  - Gli amministratori vedranno un avviso giallo con link al Customizer
  - I visitatori vedranno: "Il modulo di contatto sarà disponibile a breve."

### 3. 📞 Informazioni Contatto
- **Mostra Informazioni**: Toggle on/off sezione
- **Titolo Sezione**: "Informazioni di Contatto" (modificabile)
- **Indirizzo**: Via, città, CAP
- **Telefono**: Numero telefono (cliccabile)
- **Email**: Email di contatto (cliccabile)
- **WhatsApp**: Numero con prefisso (es: +39123456789)

**Esempio:**
```
Indirizzo: Via Roma 123, 00100 Roma RM
Telefono: +39 06 1234567
Email: info@caniincasa.it
WhatsApp: +393331234567
```

### 4. 🕐 Orari di Apertura (Opzionale)
- **Mostra Orari**: Toggle on/off
- **Titolo**: "Orari di Apertura" (modificabile)
- **Testo Orari**: Supporta HTML

**Esempio:**
```html
<strong>Lunedì - Venerdì:</strong> 9:00 - 18:00<br>
<strong>Sabato:</strong> 9:00 - 13:00<br>
<strong>Domenica:</strong> Chiuso
```

### 5. 🌐 Social Media
- **Mostra Social**: Toggle on/off
- **Titolo**: "Seguici sui Social" (modificabile)
- **Link Social**: Inserisci URL completi

**Social Disponibili:**
- Facebook (es: `https://facebook.com/tuapagina`)
- Instagram (es: `https://instagram.com/tuoaccount`)
- Twitter (es: `https://twitter.com/tuoaccount`)
- YouTube (es: `https://youtube.com/c/tuocanale`)

**⚠️ URL Completi**: Inserisci sempre l'URL completo con `https://`

### 6. 🗺️ Mappa (Google Maps)
- **Mostra Mappa**: Toggle on/off
- **Titolo**: "Dove Siamo" (modificabile)
- **Embed Mappa**: Codice iframe Google Maps

**Come Ottenere il Codice:**
1. Vai su [Google Maps](https://maps.google.com)
2. Cerca il tuo indirizzo
3. Clicca **"Condividi"**
4. Tab **"Incorpora una mappa"**
5. Copia il codice `<iframe>...</iframe>`
6. Incolla nel Customizer

## Workflow Consigliato

### Prima Configurazione:
1. ✅ Installa e configura **Contact Form 7**
2. ✅ Crea un form di contatto
3. ✅ Copia shortcode e inserisci nel Customizer
4. ✅ Compila informazioni di contatto
5. ✅ Aggiungi link social
6. ✅ (Opzionale) Aggiungi mappa Google
7. ✅ Salva e pubblica

### Modifica Contenuti:
1. Vai su **Aspetto → Personalizza → Pagina Contatti**
2. Modifica i campi desiderati
3. **Anteprima Live** prima di pubblicare
4. Clicca **"Pubblica"** quando sei soddisfatto

## Messaggi di Stato

### ⚠️ "Nessun form di contatto configurato" (Solo Admin)
**Causa**: Non hai inserito lo shortcode CF7
**Soluzione**: Clicca sul link nel messaggio per andare direttamente al Customizer

### ℹ️ "Il modulo di contatto sarà disponibile a breve" (Utenti)
**Causa**: Non c'è shortcode configurato
**Soluzione**: Inserisci lo shortcode dal Customizer (solo admin)

## Campi Obbligatori vs Opzionali

### Obbligatori:
- **Shortcode Contact Form 7**: Senza questo, non c'è form

### Opzionali (ma consigliati):
- Informazioni di contatto (almeno email o telefono)
- Social media (almeno 1-2 link)
- Mappa Google Maps

### Completamente Opzionali:
- Orari di apertura
- WhatsApp
- Immagine hero

## Best Practices

### ✅ Fare:
- Testare il form CF7 prima di pubblicare
- Inserire almeno 2 modi per contattarti (email + telefono)
- Usare URL completi per i social
- Verificare che mappa Google mostri posizione corretta
- Usare anteprima live prima di pubblicare

### ❌ Evitare:
- Lasciare shortcode vuoto (gli utenti non vedranno il form)
- Inserire numeri telefono senza prefisso internazionale
- URL social incompleti (es: solo "facebook.com")
- Codici iframe da fonti non verificate

## Supporto Contact Form 7

### Plugin Necessari:
1. **Contact Form 7** (obbligatorio)
2. **Flamingo** (consigliato - salva messaggi in database)
3. **Really Simple CAPTCHA** o **reCAPTCHA** (consigliato - antispam)

### Configurazione CF7 Consigliata:
- Abilita **notifiche email** per nuovi messaggi
- Configura **email di conferma** per utenti
- Aggiungi **protezione spam**
- Testa invio prima di pubblicare

## Troubleshooting

### Il form non appare
- Verifica che hai inserito lo shortcode corretto
- Controlla che Contact Form 7 sia attivo
- Verifica che l'ID del form esista

### Mappa non si vede
- Verifica che il codice iframe sia completo
- Controlla che non ci siano caratteri extra
- Assicurati di aver abilitato "Mostra Mappa"

### Social icons non appaiono
- Inserisci URL completo (https://...)
- Verifica che "Mostra Social" sia attivo
- Controlla che almeno un link sia inserito

## Link Utili

- [Contact Form 7 Official](https://contactform7.com/)
- [Google Maps Embed](https://www.google.com/maps)
- [Customizer WordPress](https://wordpress.org/documentation/article/customizer/)
