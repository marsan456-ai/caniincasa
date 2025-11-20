# Guida Sistema Messaggistica - Caniincasa.it

**Data Implementazione**: 20 Novembre 2025
**Branch**: `claude/review-project-brief-01HAw2pN3fajanEyQ7zUSDdV`

---

## 📋 Panoramica

Sistema completo di messaggistica privata tra utenti con supporto per:
- ✅ Conversazioni/Thread (risposte ai messaggi)
- ✅ Blocco utenti bidirezionale
- ✅ Notifiche email
- ✅ Gestione messaggi letti/non letti
- ✅ Conteggio messaggi non letti in tempo reale

---

## 🗄️ Struttura Database

### Tabella: `wp_caniincasa_messages`

```sql
CREATE TABLE wp_caniincasa_messages (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    parent_id bigint(20) unsigned DEFAULT NULL,  -- 🆕 Per thread/conversazioni
    sender_id bigint(20) unsigned NOT NULL,
    recipient_id bigint(20) unsigned NOT NULL,
    subject varchar(255) NOT NULL,
    message text NOT NULL,
    related_post_id bigint(20) unsigned DEFAULT NULL,
    related_post_type varchar(50) DEFAULT NULL,
    is_read tinyint(1) DEFAULT 0,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    read_at datetime DEFAULT NULL,
    PRIMARY KEY (id),
    KEY parent_id (parent_id),
    KEY sender_id (sender_id),
    KEY recipient_id (recipient_id),
    KEY is_read (is_read),
    KEY created_at (created_at)
);
```

### Tabella: `wp_caniincasa_blocked_users` 🆕

```sql
CREATE TABLE wp_caniincasa_blocked_users (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned NOT NULL,
    blocked_user_id bigint(20) unsigned NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_block_unique (user_id, blocked_user_id),
    KEY user_id (user_id),
    KEY blocked_user_id (blocked_user_id)
);
```

---

## 🔧 Funzioni PHP Disponibili

### Messaggistica Base

#### `caniincasa_send_message( $sender_id, $recipient_id, $subject, $message, $parent_id, $related_post_id, $related_post_type )`

Invia un nuovo messaggio o risposta.

**Parametri:**
- `$sender_id` (int) - ID utente mittente
- `$recipient_id` (int) - ID utente destinatario
- `$subject` (string) - Oggetto del messaggio
- `$message` (string) - Contenuto del messaggio
- `$parent_id` (int, opzionale) - ID messaggio padre per risposte 🆕
- `$related_post_id` (int, opzionale) - ID post correlato (annuncio, razza, ecc.)
- `$related_post_type` (string, opzionale) - Tipo di post correlato

**Return:** `int|false` - ID messaggio o false se fallito

**Esempio:**
```php
// Nuovo messaggio
$message_id = caniincasa_send_message(
    get_current_user_id(),
    25,
    'Domanda su annuncio',
    'Ciao, è ancora disponibile?',
    null,
    123,
    'annunci_4zampe'
);

// Risposta a un messaggio
$reply_id = caniincasa_send_message(
    get_current_user_id(),
    25,
    'Re: Domanda su annuncio',
    'Sì, è ancora disponibile!',
    $message_id // Parent ID
);
```

---

#### `caniincasa_get_user_messages( $user_id, $type, $limit, $offset )`

Ottiene i messaggi di un utente.

**Parametri:**
- `$user_id` (int) - ID utente
- `$type` (string) - 'inbox' o 'sent'
- `$limit` (int) - Numero massimo di messaggi (default: 20)
- `$offset` (int) - Offset per paginazione (default: 0)

**Return:** `array` - Array di oggetti messaggio

**Note:**
- 🆕 Esclude automaticamente messaggi da utenti bloccati
- 🆕 Include conteggio risposte (`reply_count`)
- Mostra solo messaggi root (non le risposte nella lista)

---

#### `caniincasa_get_message( $message_id )`

Ottiene un singolo messaggio con dati utente completi.

---

#### `caniincasa_mark_message_read( $message_id, $user_id )`

Segna un messaggio come letto.

---

#### `caniincasa_delete_message( $message_id, $user_id )`

Elimina un messaggio (solo se sei mittente o destinatario).

---

#### `caniincasa_get_unread_count( $user_id )`

Ottiene il numero di messaggi non letti.

---

### Conversazioni/Thread 🆕

#### `caniincasa_get_conversation_thread( $message_id )`

Ottiene tutti i messaggi di una conversazione.

**Return:** `array` - Array di messaggi ordinati cronologicamente

**Esempio:**
```php
$conversation = caniincasa_get_conversation_thread( 123 );

foreach ( $conversation as $msg ) {
    echo '<div class="message">';
    echo '<strong>' . $msg->sender->display_name . '</strong>: ';
    echo wpautop( $msg->message );
    echo '</div>';
}
```

---

#### `caniincasa_count_conversation_replies( $message_id )`

Conta il numero di risposte in un thread.

**Return:** `int`

---

### Blocco Utenti 🆕

#### `caniincasa_block_user( $user_id, $blocked_user_id )`

Blocca un utente.

**Return:** `bool`

**Effetti:**
- L'utente bloccato non può più inviare messaggi
- I messaggi esistenti non vengono eliminati
- L'utente bloccato non appare più nella lista messaggi

---

#### `caniincasa_unblock_user( $user_id, $blocked_user_id )`

Sblocca un utente precedentemente bloccato.

---

#### `caniincasa_is_user_blocked( $user_id, $blocked_user_id )`

Verifica se un utente ha bloccato un altro utente.

**Return:** `bool`

---

#### `caniincasa_get_blocked_users( $user_id )`

Ottiene la lista degli utenti bloccati.

**Return:** `array` - Array di user IDs

---

## 🔌 AJAX Endpoints

Tutti gli endpoint AJAX richiedono:
- Nonce: `caniincasa_nonce`
- Utente loggato

### `wp_ajax_send_message`

Invia un nuovo messaggio o risposta.

**Parametri POST:**
- `recipient_id` (int, required)
- `subject` (string, required)
- `message` (string, required)
- `parent_id` (int, optional) - Per risposte 🆕
- `related_post_id` (int, optional)
- `related_post_type` (string, optional)

**Response:**
```javascript
{
    success: true,
    data: {
        message: "Messaggio inviato con successo!",
        message_id: 123
    }
}
```

**Errori:**
- "Devi essere loggato per inviare messaggi."
- "Tutti i campi sono obbligatori."
- "Non puoi inviare messaggi a te stesso."
- "Non puoi inviare messaggi a questo utente." (bloccato)
- "Questo utente ti ha bloccato."

---

### `wp_ajax_block_user` 🆕

Blocca un utente.

**Parametri POST:**
- `blocked_user_id` (int, required)

**Response:**
```javascript
{
    success: true,
    data: {
        message: "Utente bloccato con successo."
    }
}
```

---

### `wp_ajax_unblock_user` 🆕

Sblocca un utente.

**Parametri POST:**
- `blocked_user_id` (int, required)

---

### `wp_ajax_get_conversation` 🆕

Ottiene l'intera conversazione/thread.

**Parametri POST:**
- `message_id` (int, required)

**Response:**
```javascript
{
    success: true,
    data: {
        conversation: [ /* array di messaggi */ ],
        count: 5
    }
}
```

---

### `wp_ajax_mark_message_read`

Segna messaggio come letto.

**Parametri POST:**
- `message_id` (int, required)

---

### `wp_ajax_delete_message`

Elimina un messaggio.

**Parametri POST:**
- `message_id` (int, required)

---

### `wp_ajax_get_unread_count`

Ottiene conteggio messaggi non letti.

**Response:**
```javascript
{
    success: true,
    data: {
        count: 3
    }
}
```

---

## 🎨 JavaScript API

Il file `/assets/js/messaging.js` fornisce un'API completa.

### Metodi Disponibili

#### `Messaging.openModal(e)`

Apre il modal per nuovo messaggio.

**Attributi data richiesti sul pulsante:**
```html
<button class="btn-send-message"
        data-recipient-id="25"
        data-recipient-name="Mario Rossi"
        data-post-id="123"
        data-post-type="annunci_4zampe"
        data-subject="Domanda su annuncio">
    Invia Messaggio
</button>
```

---

#### `Messaging.openReplyModal(e)` 🆕

Apre il modal per rispondere a un messaggio.

**Attributi data richiesti:**
```html
<button class="btn-reply-message"
        data-message-id="456"
        data-recipient-id="25"
        data-recipient-name="Mario Rossi"
        data-subject="Domanda su annuncio">
    Rispondi
</button>
```

**Comportamento:**
- Aggiunge automaticamente "Re:" al subject se non presente
- Imposta il `parent_id` per creare un thread

---

#### `Messaging.blockUser(e)` 🆕

Blocca un utente.

**Attributi data richiesti:**
```html
<button class="btn-block-user btn-danger"
        data-user-id="25">
    Blocca Utente
</button>
```

---

#### `Messaging.unblockUser(e)` 🆕

Sblocca un utente.

**Attributi data richiesti:**
```html
<button class="btn-unblock-user btn-secondary"
        data-user-id="25">
    Sblocca Utente
</button>
```

---

#### `Messaging.updateUnreadCount()`

Aggiorna il badge con i messaggi non letti.

**Auto-refresh:** Ogni 60 secondi

---

## 📱 HTML Modal Structure

Il modal per inviare messaggi:

```html
<div id="message-modal" class="message-modal">
    <div class="message-modal-overlay"></div>
    <div class="message-modal-content">
        <div class="message-modal-header">
            <h2>Invia Messaggio</h2>
            <button class="message-modal-close">&times;</button>
        </div>

        <div class="message-modal-body">
            <p>A: <strong id="message-recipient-name"></strong></p>

            <form id="message-form">
                <input type="hidden" id="message-recipient-id" name="recipient_id">
                <input type="hidden" id="message-parent-id" name="parent_id">
                <input type="hidden" id="message-related-post-id" name="related_post_id">
                <input type="hidden" id="message-related-post-type" name="related_post_type">

                <div class="form-group">
                    <label for="message-subject">Oggetto</label>
                    <input type="text" id="message-subject" name="subject" required>
                </div>

                <div class="form-group">
                    <label for="message-content">Messaggio</label>
                    <textarea id="message-content" name="message" rows="6" required></textarea>
                </div>

                <div class="message-response"></div>

                <button type="submit" class="btn btn-primary">Invia Messaggio</button>
            </form>
        </div>
    </div>
</div>
```

---

## 🎨 CSS Classes

File: `/assets/css/messaging.css`

### Message List

```css
.message-item           /* Singolo messaggio nella lista */
.message-item.unread    /* Messaggio non letto */
.message-header         /* Header con mittente e data */
.message-subject        /* Oggetto del messaggio */
.message-preview        /* Anteprima contenuto */
.message-meta           /* Info extra (data, stato letto) */
.message-actions        /* Pulsanti azione */
.reply-count            /* Badge conteggio risposte 🆕 */
```

### Modal

```css
.message-modal          /* Modal container */
.message-modal.active   /* Modal visibile */
.message-modal-overlay  /* Sfondo scuro */
.message-modal-content  /* Contenuto modal */
.message-modal-header   /* Header modal */
.message-modal-body     /* Body modal */
.message-modal-close    /* Bottone chiusura */
```

### Buttons

```css
.btn-send-message       /* Pulsante invia messaggio */
.btn-reply-message      /* Pulsante rispondi 🆕 */
.btn-block-user         /* Pulsante blocca 🆕 */
.btn-unblock-user       /* Pulsante sblocca 🆕 */
.mark-read-btn          /* Pulsante segna come letto */
.delete-message-btn     /* Pulsante elimina */
```

---

## 🔔 Sistema Notifiche

### Notifiche Email

Inviate automaticamente quando:
- ✅ Nuovo messaggio ricevuto
- ✅ Risposta a un messaggio esistente

**Template Email:**
```
Oggetto: [Caniincasa.it] Nuovo messaggio da {SENDER_NAME}

Ciao,

Hai ricevuto un nuovo messaggio da {SENDER_NAME}.

Oggetto: {SUBJECT}

Per leggere il messaggio e rispondere, accedi alla tua dashboard:
{DASHBOARD_URL}

Grazie!
```

### Badge Non Letti

Il badge viene aggiornato automaticamente:
- ✅ Alla navigazione nella dashboard
- ✅ Dopo aver inviato/letto un messaggio
- ✅ Ogni 60 secondi (polling automatico)

**HTML Badge:**
```html
<a href="?tab=messaggi">
    Messaggi
    <span class="messages-badge">3</span>
</a>
```

---

## 🔄 Flussi di Lavoro

### 1. Invio Nuovo Messaggio da Annuncio

```
Utente → Click "Invia Messaggio" su annuncio
       → Modal aperto con dati precompilati:
          - recipient_id (autore annuncio)
          - related_post_id (ID annuncio)
          - related_post_type ('annunci_4zampe')
       → Compila messaggio
       → Submit AJAX
       → Controllo utenti non bloccati
       → Salvataggio database
       → Invio notifica email
       → Chiusura modal
       → Messaggio successo
```

---

### 2. Risposta a Messaggio Ricevuto 🆕

```
Utente → Dashboard → Tab Messaggi
       → Click "Rispondi" su messaggio
       → Modal aperto con:
          - Subject prefissato "Re: ..."
          - parent_id impostato
          - recipient_id del mittente originale
       → Compila risposta
       → Submit AJAX
       → Salvataggio come reply (parent_id valorizzato)
       → Notifica email al destinatario
       → Messaggio aggiunto al thread
```

---

### 3. Visualizzazione Conversazione 🆕

```
Utente → Click su messaggio con risposte
       → AJAX get_conversation
       → Recupero thread completo
       → Rendering cronologico:
          - Messaggio originale
          - Tutte le risposte in ordine
       → Pulsante "Rispondi" per continuare conversazione
       → Pulsante "Blocca Utente" disponibile
```

---

### 4. Blocco Utente 🆕

```
Utente → Click "Blocca Utente" su messaggio
       → Conferma modale
       → AJAX block_user
       → Inserimento in blocked_users table
       → Aggiornamento UI:
          - Pulsante diventa "Sblocca Utente"
          - Messaggi utente filtrati dalle liste
       → L'utente bloccato non può più inviare messaggi
```

---

## 🧪 Testing

### Test Funzionalità Base

```bash
# Nel browser console
wp.ajax.post('send_message', {
    nonce: caniincasaData.nonce,
    recipient_id: 25,
    subject: 'Test messaggio',
    message: 'Contenuto di test',
    parent_id: null
});
```

### Test Blocco Utente

```javascript
// Blocca utente ID 25
$.ajax({
    url: caniincasaData.ajaxurl,
    type: 'POST',
    data: {
        action: 'block_user',
        nonce: caniincasaData.nonce,
        blocked_user_id: 25
    },
    success: function(response) {
        console.log(response);
    }
});
```

### Test Conversazione

```javascript
// Ottieni conversazione
$.ajax({
    url: caniincasaData.ajaxurl,
    type: 'POST',
    data: {
        action: 'get_conversation',
        nonce: caniincasaData.nonce,
        message_id: 123
    },
    success: function(response) {
        console.log(response.data.conversation);
    }
});
```

---

## 📊 Performance

### Indici Database

Tutti gli indici sono ottimizzati per query veloci:
- `parent_id` - Per recupero thread
- `sender_id` - Per messaggi inviati
- `recipient_id` - Per messaggi ricevuti
- `is_read` - Per conteggio non letti
- `created_at` - Per ordinamento cronologico
- `user_block_unique` - Impedisce blocchi duplicati

### Caching

- ❌ Nessun caching implementato (messaggi real-time)
- ✅ Query ottimizzate con LIMIT/OFFSET per paginazione
- ✅ Badge aggiornato solo quando necessario

---

## 🚀 Estensioni Future

### Possibili Miglioramenti

1. **Push Notifications** (PWA)
   - Notifiche real-time su nuovo messaggio
   - Service Worker integration

2. **Rich Text Editor**
   - Formattazione testo
   - Emoji picker
   - File attachments

3. **Filtri Avanzati**
   - Ricerca messaggi per keyword
   - Filtro per data
   - Filtro per tipo di post correlato

4. **Archiviazione**
   - Soft delete invece di eliminazione
   - Cartella "Archiviati"
   - Restore messaggi eliminati

5. **Segnalazioni**
   - Report messaggio inappropriato
   - Moderazione admin
   - Ban automatico dopo N segnalazioni

6. **Statistiche**
   - Messaggi inviati/ricevuti
   - Tempo medio risposta
   - Utenti più attivi

---

## 📝 Note di Sicurezza

### Validazione e Sanitizzazione

Tutti gli input vengono validati e sanitizzati:

```php
// Validazione
if ( ! $sender_id || ! $recipient_id || ! $subject || ! $message ) {
    return false;
}

// Sanitizzazione
'subject'  => sanitize_text_field( $subject ),
'message'  => wp_kses_post( $message ),
'related_post_type' => sanitize_text_field( $related_post_type ),
```

### Protezione XSS

- ✅ Tutti gli output escapati con `esc_html()`, `esc_attr()`, etc.
- ✅ Contenuto messaggio filtrato con `wp_kses_post()`
- ✅ Nonce verification su tutti gli AJAX

### Prevenzione SQL Injection

- ✅ Tutti i parametri passati via `$wpdb->prepare()`
- ✅ Type casting esplicito (`absint()`, `sanitize_text_field()`)

### Rate Limiting

⚠️ **TODO:** Implementare rate limiting per prevenire spam

Suggerito:
- Max 10 messaggi per ora per utente
- Max 3 messaggi allo stesso destinatario per ora
- Blocco temporaneo dopo 5 tentativi falliti

---

## 📞 Supporto

Per problemi o domande sul sistema di messaggistica:

1. Verifica i log del browser console per errori JavaScript
2. Verifica i log PHP per errori database
3. Controlla che le tabelle siano create correttamente
4. Verifica che i permessi utente siano corretti

**Log Debug JavaScript:**
```javascript
// Abilita debug
console.log(Messaging);
```

**Log Debug PHP:**
```php
// Nel file messaging-system.php
error_log( print_r( $message, true ) );
```

---

**Fine Documentazione** | Versione 1.0 | 20 Novembre 2025
