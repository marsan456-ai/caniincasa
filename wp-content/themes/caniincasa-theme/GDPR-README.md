# GDPR e Disclaimer - Guida Utilizzo

Questo documento spiega come utilizzare le funzionalità GDPR e i disclaimer implementati nel tema Caniincasa.

## Panoramica

Il tema include un sistema completo di disclaimer e conformità GDPR per proteggere il sito da responsabilità legali e garantire la conformità alle normative europee sulla privacy.

## Funzionalità Implementate

### 1. Cookie Consent Banner

Un banner GDPR-compliant che appare automaticamente agli utenti alla prima visita.

**Caratteristiche:**
- Tre opzioni di consenso: Accetta Tutto, Solo Necessari, Impostazioni
- Modal personalizzabile per gestire i consensi granulari
- Salvataggio delle preferenze cookie
- Integrazione con Google Analytics e Facebook Pixel

**Personalizzazione:**
- **Aspetto → Personalizza → GDPR e Disclaimer**
- Abilita/Disabilita il banner cookie
- Il banner si nasconde automaticamente dopo il consenso

### 2. Disclaimer Strutture

Disclaimer automatico per le pagine di strutture (allevamenti, veterinari, pensioni, canili, centri cinofili).

**Dove appare:** Nelle pagine single delle strutture

**Come usarlo:**
```php
<?php caniincasa_structure_data_disclaimer(); ?>
```

**Personalizzazione:**
- **Aspetto → Personalizza → GDPR e Disclaimer → Disclaimer Strutture**
- Abilita/Disabilita
- Modifica testo del disclaimer

**Testo predefinito:**
> I dati presenti in questa scheda sono stati raccolti da fonti pubbliche disponibili su internet, inclusi albi professionali, registri ufficiali e associazioni di categoria...

### 3. Disclaimer Annunci

Disclaimer per le pagine degli annunci (Annunci 4 Zampe, Dogsitter).

**Dove appare:** Nelle pagine single degli annunci

**Come usarlo:**
```php
<?php caniincasa_annunci_disclaimer(); ?>
```

**Personalizzazione:**
- **Aspetto → Personalizza → GDPR e Disclaimer → Disclaimer Annunci**

**Testo predefinito:**
> Gli annunci pubblicati sono inseriti direttamente dagli utenti. [Nome Sito] non si assume alcuna responsabilità...

### 4. Disclaimer Razze

Disclaimer per le schede informative sulle razze canine.

**Dove appare:** Nelle pagine single delle razze

**Come usarlo:**
```php
<?php caniincasa_razze_disclaimer(); ?>
```

**Personalizzazione:**
- **Aspetto → Personalizza → GDPR e Disclaimer → Disclaimer Razze**

### 5. Disclaimer Footer

Disclaimer generale che appare nel footer di tutte le pagine.

**Dove appare:** Footer di ogni pagina

**Come usarlo:**
```php
<?php caniincasa_footer_disclaimer(); ?>
```

**Personalizzazione:**
- **Aspetto → Personalizza → GDPR e Disclaimer → Disclaimer Footer**

### 6. Checkbox Privacy per Form

Checkbox obbligatorio di consenso privacy per tutti i form.

**Come usarlo:**
```php
<?php caniincasa_form_privacy_checkbox( 'form_id' ); ?>
```

**Parametri:**
- `form_id`: ID univoco del form (es. 'contact', 'registration', 'annuncio')

**Caratteristiche:**
- Checkbox obbligatorio
- Link automatico alla Privacy Policy
- Testo conforme GDPR
- Note informative sul trattamento dati

### 7. Indicatore Fonte Dati

Mostra l'origine dei dati in modo trasparente.

**Come usarlo:**
```php
<?php caniincasa_data_source_notice( 'albi' ); ?>
```

**Tipi disponibili:**
- `general`: Dati raccolti da fonti pubbliche e ufficiali
- `albi`: Dati da Albi Professionali pubblici
- `associazioni`: Dati da Associazioni di Categoria riconosciute
- `enci`: Dati da ENCI (Ente Nazionale Cinofilia Italiana)
- `user`: Dati inseriti dall'utente

## Implementazione nei Template

### Esempio: Pagina Struttura (Allevamento)

```php
<?php get_header(); ?>

<div class="struttura-content">
    <!-- Contenuto struttura -->
    <h1><?php the_title(); ?></h1>

    <!-- Informazioni struttura -->
    <div class="struttura-info">
        <!-- ... -->

        <!-- Indicatore fonte dati -->
        <?php caniincasa_data_source_notice( 'albi' ); ?>
    </div>

    <!-- Disclaimer GDPR -->
    <?php caniincasa_structure_data_disclaimer(); ?>
</div>

<?php get_footer(); ?>
```

### Esempio: Pagina Annuncio

```php
<?php get_header(); ?>

<div class="annuncio-content">
    <h1><?php the_title(); ?></h1>

    <!-- Contenuto annuncio -->

    <!-- Disclaimer responsabilità -->
    <?php caniincasa_annunci_disclaimer(); ?>
</div>

<?php get_footer(); ?>
```

### Esempio: Form di Contatto

```php
<form method="post" class="contact-form">
    <!-- Campi form -->
    <input type="text" name="name" required>
    <input type="email" name="email" required>
    <textarea name="message" required></textarea>

    <!-- Checkbox Privacy GDPR -->
    <?php caniincasa_form_privacy_checkbox( 'contact' ); ?>

    <button type="submit">Invia</button>
</form>
```

### Esempio: Footer

```php
<footer class="site-footer">
    <!-- Contenuto footer -->

    <!-- Disclaimer generale -->
    <?php caniincasa_footer_disclaimer(); ?>

    <!-- Copyright, etc -->
</footer>

<?php wp_footer(); ?>
```

## Personalizzazione Avanzata

### Modificare i Testi dei Disclaimer

1. Vai in **Aspetto → Personalizza**
2. Apri la sezione **GDPR e Disclaimer**
3. Modifica i testi nelle textarea

### Disabilitare un Disclaimer

1. Vai in **Aspetto → Personalizza → GDPR e Disclaimer**
2. Deseleziona la checkbox "Abilita [Nome Disclaimer]"
3. Clicca "Pubblica"

### Stili CSS Personalizzati

I disclaimer utilizzano queste classi CSS:

```css
.gdpr-disclaimer { /* Container disclaimer */ }
.gdpr-disclaimer.structure-disclaimer { /* Disclaimer strutture */ }
.gdpr-disclaimer.annunci-disclaimer { /* Disclaimer annunci */ }
.gdpr-disclaimer.razze-disclaimer { /* Disclaimer razze */ }
.gdpr-footer-disclaimer { /* Disclaimer footer */ }
.cookie-consent-banner { /* Banner cookie */ }
.form-privacy-consent { /* Checkbox privacy form */ }
.data-source-notice { /* Indicatore fonte dati */ }
```

Puoi sovrascrivere gli stili nel tuo CSS personalizzato.

## Cookie Consent

### Gestione Consensi

Il sistema salva le preferenze dell'utente in un cookie chiamato `caniincasa_cookie_consent`.

**Valori possibili:**
- `all`: Tutti i cookie consentiti
- `necessary`: Solo cookie tecnici necessari
- `analytics`: Cookie tecnici + analytics
- `marketing`: Cookie tecnici + marketing

### Integrazione Analytics

Il sistema è già integrato con:
- Google Analytics (gtag.js)
- Facebook Pixel

Quando l'utente accetta/rifiuta i cookie, vengono automaticamente abilitati/disabilitati i tracking.

## Conformità GDPR

### Cosa è incluso:

✅ Cookie consent banner
✅ Disclaimer responsabilità
✅ Informativa origine dati
✅ Checkbox privacy nei form
✅ Link alla Privacy Policy
✅ Gestione granulare consensi cookie
✅ Trasparenza fonte dati

### Cosa devi fare tu:

1. **Creare una Privacy Policy completa** in WordPress (Impostazioni → Privacy)
2. **Nominare un DPO** (Data Protection Officer) se richiesto
3. **Configurare i disclaimer** secondo le tue esigenze
4. **Verificare la conformità** con un legale specializzato in GDPR

## File Importanti

- `/inc/gdpr-disclaimers.php`: Funzioni PHP per i disclaimer
- `/assets/css/gdpr.css`: Stili CSS
- `/assets/js/gdpr.js`: JavaScript per cookie consent
- `/inc/customizer.php`: Impostazioni Customizer

## Supporto

Per domande o problemi, contatta il team di sviluppo.

---

**Nota Legale:** Questo sistema fornisce strumenti per facilitare la conformità GDPR, ma non sostituisce la consulenza legale. Consulta sempre un avvocato specializzato in privacy e protezione dati.
