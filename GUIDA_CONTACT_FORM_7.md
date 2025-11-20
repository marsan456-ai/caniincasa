# Guida Contact Form 7 - Stili Ottimizzati

Guida completa all'utilizzo degli stili ottimizzati per Contact Form 7 nel tema Caniincasa.

## Indice

1. [Introduzione](#introduzione)
2. [Caratteristiche](#caratteristiche)
3. [Esempi di Form](#esempi-di-form)
4. [Campi Supportati](#campi-supportati)
5. [Layout Multi-Colonna](#layout-multi-colonna)
6. [Messaggi di Validazione](#messaggi-di-validazione)
7. [Personalizzazione](#personalizzazione)
8. [Best Practices](#best-practices)

---

## Introduzione

Il tema Caniincasa include un foglio di stile ottimizzato (`cf7.css`) che viene automaticamente caricato quando Contact Form 7 è attivo. Gli stili sono:

- **Mobile-first**: Ottimizzati per dispositivi touch
- **Accessibili**: Conformi alle linee guida WCAG
- **Coerenti**: Integrati con il design del tema
- **Performanti**: Animazioni smooth e transizioni fluide

---

## Caratteristiche

### Design

- ✅ Palette colori integrata con il tema
- ✅ Font personalizzati (Open Sans + Baloo 2)
- ✅ Border radius e ombre coerenti
- ✅ Transizioni smooth su tutti gli stati

### Funzionalità

- ✅ Validazione in tempo reale con feedback visivo
- ✅ Messaggi di successo/errore stilizzati
- ✅ Loading spinner durante l'invio
- ✅ Stati focus ottimizzati per tastiera
- ✅ Supporto dark mode e high contrast

### Accessibilità

- ✅ Contrasto colori WCAG AA compliant
- ✅ Focus indicators chiari
- ✅ Screen reader support
- ✅ Tastiera navigabile
- ✅ Touch target minimi 44x44px (mobile)

---

## Esempi di Form

### 1. Form Contatti Base

```
<label> Nome *
    [text* nome-utente placeholder "Il tuo nome"] </label>

<label> Email *
    [email* email-utente placeholder "la-tua-email@esempio.it"] </label>

<label> Telefono
    [tel telefono placeholder "+39 123 456 7890"] </label>

<label> Messaggio *
    [textarea* messaggio placeholder "Scrivi qui il tuo messaggio..."] </label>

<label class="wpcf7-acceptance">
    [acceptance accettazione] Accetto la [link-privacy "Privacy Policy"] *
</label>

[submit "Invia Messaggio"]
```

### 2. Form con Quiz Antispam

```
<label> Nome *
    [text* nome] </label>

<label> Email *
    [email* email] </label>

<label> Quanto fa 2+2?
    [quiz quiz-antispam "2+2=?|4"] </label>

[submit "Invia"]
```

### 3. Form con Upload File

```
<label> Nome *
    [text* nome] </label>

<label> Email *
    [email* email] </label>

<label> Allega curriculum (PDF, max 5MB)
    [file curriculum filetypes:pdf limit:5mb] </label>

[submit "Invia Candidatura"]
```

### 4. Form con Checkbox Multipli

```
<label> Servizi di interesse: </label>

[checkbox servizi "Addestramento" "Toelettatura" "Pensione" "Dog sitting"]

<label> Preferenza di contatto: </label>

[radio contatto default:1 "Email" "Telefono" "WhatsApp"]

[submit "Richiedi Info"]
```

---

## Campi Supportati

### Input Testuali

| Campo | Codice | Note |
|-------|--------|------|
| Testo | `[text nome]` | Campo testo generico |
| Email | `[email email]` | Con validazione email |
| Telefono | `[tel telefono]` | Tastiera numerica su mobile |
| URL | `[url sito-web]` | Con validazione URL |
| Numero | `[number numero]` | Solo numeri |
| Data | `[date data]` | Date picker nativo |

**Attributi comuni:**
- `*` dopo il tipo = campo obbligatorio
- `placeholder "Testo"` = testo segnaposto
- `class:mia-classe` = classe CSS custom

### Textarea

```
[textarea* messaggio placeholder "Scrivi qui..." class:form-full]
```

- Altezza minima: 150px (desktop), 120px (mobile)
- Resize solo verticale
- Caratteri illimitati (configurabile in CF7)

### Select Dropdown

```
<label> Provincia *
    [select* provincia include_blank "-- Seleziona --" "Verona" "Milano" "Roma" "Napoli"] </label>
```

- Icona freccia personalizzata
- Cambio colore su focus
- Supporta `include_blank` per opzione vuota

### Checkbox e Radio

```
[checkbox servizi "Opzione 1" "Opzione 2" "Opzione 3"]

[radio scelta default:1 "Scelta A" "Scelta B" "Scelta C"]
```

- Dimensione touch-friendly: 20x20px
- Color accent del tema
- Layout orizzontale (desktop), verticale (mobile)

### File Upload

```
[file allegato limit:5mb filetypes:pdf|jpg|png]
```

- Border dashed (visualmente diverso)
- Hover effect
- Messaggio errore se file troppo grande

### Acceptance (Privacy)

```
<label class="wpcf7-acceptance">
    [acceptance privacy] Accetto i [link "termini e condizioni"] *
</label>
```

- Checkbox obbligatorio
- Link cliccabili stilizzati
- Font size ridotto (90%)

---

## Layout Multi-Colonna

### Due Colonne Affiancate

Usa la classe `form-row` per creare layout a 2 colonne:

```html
<div class="form-row">
    <p>
        <label> Nome *
            [text* nome] </label>
    </p>
    <p>
        <label> Cognome *
            [text* cognome] </label>
    </p>
</div>

<div class="form-row">
    <p>
        <label> Email *
            [email* email] </label>
    </p>
    <p>
        <label> Telefono
            [tel telefono] </label>
    </p>
</div>

<p class="form-full">
    <label> Messaggio *
        [textarea* messaggio] </label>
</p>
```

**Note:**
- `.form-row` crea un grid 2 colonne
- `.form-full` occupa entrambe le colonne
- Su mobile (< 768px) le colonne si impilano automaticamente

---

## Messaggi di Validazione

### Tipologie Messaggi

Gli stili CF7 supportano tutti i messaggi standard:

#### ✅ Successo (Mail inviata)
```
Messaggio inviato correttamente! Ti risponderemo al più presto.
```
- Background verde chiaro
- Bordo verde
- Icona checkmark (✓)

#### ❌ Errore di Validazione
```
Uno o più campi presentano errori. Controlla e riprova.
```
- Background rosso chiaro
- Bordo rosso
- Icona warning (⚠)

#### ⚠️ Spam Bloccato
```
Il messaggio è stato identificato come spam.
```
- Background giallo chiaro
- Bordo giallo

#### 🔒 Privacy Non Accettata
```
Devi accettare i termini e condizioni per procedere.
```
- Background giallo chiaro
- Bordo giallo

### Messaggi per Campo

Ogni campo invalido mostra un messaggio sotto di sé:

```html
<!-- Input invalido -->
<input class="wpcf7-not-valid" />
<span class="wpcf7-not-valid-tip">Questo campo è obbligatorio.</span>
```

Stile automatico:
- Bordo rosso sul campo
- Background rosa chiaro
- Messaggio rosso sotto il campo

---

## Personalizzazione

### Classi CSS Disponibili

#### Classi per Campi

```
[text nome class:mia-classe-custom]
```

Puoi aggiungere classi custom per override specifici.

#### Classi per Layout

- `.form-row` - Layout 2 colonne
- `.form-full` - Occupacolonna intera in un grid
- `.wpcf7-acceptance` - Stile checkbox privacy

### Override CSS

Se vuoi personalizzare ulteriormente, crea un file `custom-cf7.css` e sovrascrivi le variabili:

```css
/* custom-cf7.css */

/* Cambia colore primario */
.wpcf7-form-control:focus {
    border-color: #your-color;
    box-shadow: 0 0 0 3px rgba(your-color, 0.2);
}

/* Cambia stile pulsante */
.wpcf7-submit {
    background-color: #your-color;
    font-size: 1.2rem;
    border-radius: 20px;
}

/* Cambia dimensione input */
.wpcf7-form-control {
    padding: 16px 20px;
    font-size: 1.1rem;
}
```

Poi caricalo in `functions.php`:

```php
wp_enqueue_style( 'custom-cf7', get_template_directory_uri() . '/assets/css/custom-cf7.css', array( 'caniincasa-cf7' ), '1.0.0' );
```

---

## Best Practices

### 1. Etichette Chiare

✅ **Buono:**
```
<label> Indirizzo Email *
    [email* email] </label>
```

❌ **Evitare:**
```
[email* email placeholder "Email"]  <!-- Nessuna label -->
```

**Perché:** Le label sono essenziali per accessibilità e screen reader.

---

### 2. Campi Obbligatori

Usa l'asterisco `*` dopo il tipo di campo:

```
[text* nome]   <!-- Obbligatorio -->
[text cognome]  <!-- Opzionale -->
```

L'asterisco verrà visualizzato in rosso accanto alla label.

---

### 3. Placeholder Informativi

✅ **Buono:**
```
[tel telefono placeholder "+39 123 456 7890"]
```

❌ **Evitare:**
```
[tel telefono placeholder "Inserisci telefono"]
```

**Perché:** Il placeholder dovrebbe mostrare un esempio di formato, non ripetere la label.

---

### 4. Font Size Mobile

Usa sempre **font-size minimo 16px** sui campi per evitare lo zoom automatico su iOS:

```css
/* Già incluso in cf7.css */
@media (max-width: 768px) {
    .wpcf7-form-control {
        font-size: 16px; /* Previene zoom su iOS */
    }
}
```

---

### 5. Touch Target

Assicurati che i pulsanti abbiano dimensioni minime 44x44px per essere facilmente cliccabili su touch:

```css
/* Già incluso in cf7.css */
.wpcf7-submit {
    padding: 15px 40px;  /* > 44px altezza */
}
```

---

### 6. Messaggi Personalizzati

In CF7 > Impostazioni, personalizza i messaggi:

**Successo:**
```
Grazie! Il tuo messaggio è stato inviato. Ti risponderemo entro 24 ore.
```

**Errore Validazione:**
```
Ops! Alcuni campi necessitano di correzioni. Controlla i campi evidenziati.
```

**Errore Invio:**
```
Si è verificato un errore. Riprova o contattaci via email a info@caniincasa.it
```

---

### 7. Quiz Antispam

Usa quiz semplici invece di reCAPTCHA per migliore UX:

```
[quiz antispam "Quanto fa 5+3?|8|otto"]
```

Accetta risposte multiple separate da `|`.

---

### 8. Validazione Email

CF7 valida automaticamente le email, ma puoi aggiungere validazione custom:

```
[email* email class:validate-email]
```

Poi in CF7 > Validazione Aggiuntiva:
```
email~email@dominio\.it$ "Usa solo email @dominio.it"
```

---

### 9. Limiti File Upload

Specifica sempre limite dimensione e tipi file:

```
[file curriculum limit:5mb filetypes:pdf]
```

Messaggi automatici se limiti superati.

---

### 10. Testing Multi-Device

Testa sempre il form su:

- ✅ Desktop (Chrome, Firefox, Safari, Edge)
- ✅ Mobile (iOS Safari, Chrome Android)
- ✅ Tablet (iPad, Android tablet)
- ✅ Con tastiera (tab navigation)
- ✅ Con screen reader (NVDA, VoiceOver)

---

## Supporto

Per problemi o domande:

1. **Documentazione CF7**: https://contactform7.com/docs/
2. **Tema Caniincasa**: Consulta il file `brief_sviluppo_tema_plugin_caniincasa.md`
3. **Debug**: Attiva WP_DEBUG in `wp-config.php` per vedere errori PHP

---

## Changelog

### Versione 1.0.0 (Novembre 2025)

- ✅ Rilascio iniziale stili CF7 ottimizzati
- ✅ Supporto completo tutti i campi CF7
- ✅ Layout responsive mobile-first
- ✅ Messaggi validazione stilizzati
- ✅ Animazioni e transizioni smooth
- ✅ Accessibilità WCAG AA compliant
- ✅ Supporto dark mode e high contrast
- ✅ Loading spinner personalizzato
- ✅ Integrazione palette tema Caniincasa

---

**Autore**: Caniincasa Team
**Versione Tema**: 1.0.0
**Data**: Novembre 2025
