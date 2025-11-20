# Helper per Testare il Cookie Banner GDPR

## Problema: Non Vedo il Banner

Il cookie banner appare solo se non hai già dato il consenso. Se lo hai già accettato/rifiutato, il banner resta nascosto.

## Come Testare il Banner

### Metodo 1: Console JavaScript (CONSIGLIATO)

Apri la Console del browser (F12 → Console) e esegui:

```javascript
// Resetta il consenso cookie
document.cookie = "caniincasa_cookie_consent=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;";

// Ricarica la pagina
window.location.reload();
```

### Metodo 2: DevTools Application

1. Apri DevTools (F12)
2. Vai su **Application** (o **Storage**)
3. Espandi **Cookies** nella sidebar
4. Seleziona il tuo dominio
5. Trova `caniincasa_cookie_consent` e **eliminalo**
6. Ricarica la pagina (F5)

### Metodo 3: Navigation privata

Apri il sito in **modalità incognito** - il banner apparirà sempre

### Metodo 4: API Pubblica

Dalla console JavaScript:

```javascript
// Revoca il consenso (elimina cookie e ricarica)
CaniincasaCookieConsent.revokeConsent();
```

## Verificare che i File Siano Caricati

Nella console JavaScript, verifica:

```javascript
// Controlla se l'API è disponibile
console.log(CaniincasaCookieConsent);

// Controlla le preferenze correnti
console.log(CaniincasaCookieConsent.getPreferences());

// Controlla se il banner esiste nel DOM
console.log(document.querySelector('.cookie-banner'));

// Forza la visualizzazione (solo per test)
document.querySelector('.cookie-banner').classList.add('show');
```

## Forzare la Visualizzazione del Banner (Debug)

Se vuoi vedere il banner anche se hai già dato il consenso:

```javascript
// Mostra il banner manualmente
document.querySelector('.cookie-banner').classList.add('show');

// Apri le impostazioni cookie
CaniincasaCookieConsent.openSettings();
```

## Verificare Console Errors

Controlla se ci sono errori JavaScript:

1. Apri DevTools (F12)
2. Vai su **Console**
3. Cerca errori in rosso
4. Cerca warning relativi a `gdpr-cookie.js` o `cookie-banner`

## File da Verificare

### 1. CSS Caricato?
```javascript
// Nella console
const styles = [...document.styleSheets].find(s => s.href && s.href.includes('gdpr-cookie.css'));
console.log('CSS GDPR caricato:', !!styles);
```

### 2. JavaScript Caricato?
```javascript
// Nella console
console.log('JS GDPR caricato:', typeof CaniincasaCookieConsent !== 'undefined');
```

### 3. HTML Banner Presente?
```javascript
// Nella console
console.log('Banner HTML presente:', !!document.querySelector('.cookie-banner'));
console.log('Modal presente:', !!document.querySelector('.cookie-settings-modal'));
```

## Comportamento Atteso

1. **Prima visita**: Banner appare dopo 1 secondo
2. **Dopo consenso**: Banner non appare più per 365 giorni
3. **Click Accetta**: Salva tutti i consensi e nasconde banner
4. **Click Rifiuta**: Salva solo cookie necessari e nasconde banner
5. **Click Impostazioni**: Apre modal per gestire preferenze

## Clear Cache

Se hai modificato i file CSS/JS:

1. **Hard Refresh**: `Ctrl+F5` (Windows) o `Cmd+Shift+R` (Mac)
2. **Clear Browser Cache**: Settings → Privacy → Clear browsing data
3. **Disable Cache in DevTools**: DevTools → Network tab → ✓ "Disable cache"

## Test Checklist

- [ ] Banner appare dopo aver eliminato il cookie
- [ ] Click "Accetta tutti" nasconde il banner
- [ ] Click "Rifiuta" nasconde il banner
- [ ] Click "Impostazioni" apre il modal
- [ ] Toggle switches funzionano nel modal
- [ ] "Salva Preferenze" salva e chiude modal
- [ ] "Accetta Tutti" dal modal salva tutti i consensi
- [ ] Cookie persiste per 365 giorni
- [ ] Banner non riappare dopo reload (se consenso dato)

## Cookie Salvato

Formato del cookie `caniincasa_cookie_consent`:
```json
{
  "necessary": true,
  "functional": true/false,
  "analytics": true/false,
  "marketing": true/false
}
```

Visualizza il cookie corrente:
```javascript
const consent = document.cookie
  .split('; ')
  .find(row => row.startsWith('caniincasa_cookie_consent='))
  ?.split('=')[1];

if (consent) {
  console.log('Consenso salvato:', JSON.parse(decodeURIComponent(consent)));
} else {
  console.log('Nessun consenso salvato');
}
```
