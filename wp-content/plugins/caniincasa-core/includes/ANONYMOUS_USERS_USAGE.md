# Guida Gestione Contatti Annunci

## Panoramica
Il sistema offre due modalità di gestione dei contatti per gli annunci:

1. **Annunci Utenti Registrati**: Email e telefono vengono salvati per ogni singolo annuncio (inizialmente popolati dal profilo ma modificabili)
2. **Annunci Utenti Anonimi**: Gli amministratori possono creare annunci per utenti non registrati, inserendo manualmente tutti i dati (nome, email, telefono)

## Utilizzo nell'Admin

### 1. Dati di Contatto Annuncio (Utenti Registrati)

Quando crei o modifichi un annuncio come **utente registrato**, trovi la meta box **"Dati di Contatto Annuncio"**:

**Funzionamento:**
- I campi Email e Telefono vengono **automaticamente popolati** dai dati del tuo profilo
- Puoi **modificarli liberamente** per questo specifico annuncio
- Le modifiche **non influenzano** il tuo profilo utente
- I dati originali del profilo sono sempre visibili in fondo alla meta box per riferimento

**Vantaggi:**
- Ogni annuncio può avere contatti diversi (es: numero aziendale vs personale)
- Mantieni i tuoi dati di profilo invariati
- Flessibilità completa per gestire più annunci con contatti differenti

### 2. Creare un Annuncio Anonimo (Solo Amministratori)
1. Vai su **Annunci 4 Zampe** o **Annunci Dogsitter**
2. Clicca su **Aggiungi Nuovo**
3. Nella sidebar, trovi la meta box **"Utente Anonimo"**
4. Spunta la checkbox: _"Questo è un annuncio per utente anonimo"_
5. Compila i campi obbligatori:
   - Nome e Cognome
   - Email
   - Telefono
6. Salva l'annuncio

### 2. Filtrare Annunci Anonimi
Nella lista annunci, usa il dropdown **"Tutti i tipi"** per filtrare:
- **Solo Utenti Registrati**: mostra annunci con autore registrato
- **Solo Anonimi**: mostra solo annunci anonimi

### 3. Identificare Annunci Anonimi
Nella colonna "Autore" della lista admin, gli annunci anonimi sono contrassegnati con:
```
🔒 ANONIMO
Nome Utente
email@esempio.it
```

## Utilizzo nel Frontend (Template)

### Funzione Helper: `caniincasa_get_annuncio_contact_info()`

Questa funzione restituisce automaticamente i dati corretti (utente registrato o anonimo):

```php
<?php
// Ottieni le informazioni di contatto
$contact = caniincasa_get_annuncio_contact_info( get_the_ID() );

// Esempio di utilizzo
if ( $contact['is_anonymous'] ) {
    echo '<span class="badge">Annuncio Anonimo</span>';
}

echo '<div class="contact-info">';
echo '  <h3>' . esc_html( $contact['name'] ) . '</h3>';
echo '  <p>Email: <a href="mailto:' . esc_attr( $contact['email'] ) . '">' . esc_html( $contact['email'] ) . '</a></p>';

if ( ! empty( $contact['phone'] ) ) {
    echo '  <p>Tel: <a href="tel:' . esc_attr( $contact['phone'] ) . '">' . esc_html( $contact['phone'] ) . '</a></p>';
}
echo '</div>';
?>
```

### Struttura Dati Restituita

**Per utente ANONIMO:**
```php
array(
    'is_anonymous' => true,
    'name'         => 'Mario Rossi',
    'email'        => 'mario@esempio.it',
    'phone'        => '+39 123 456 7890',
)
```

**Per utente REGISTRATO:**
```php
array(
    'is_anonymous' => false,
    'name'         => 'Giovanni Verdi',
    'email'        => 'giovanni@email.com',
    'phone'        => '+39 333 1234567',
    'user_id'      => 42,  // ID utente WordPress
)
```

## Esempio Template Single Annuncio

```php
<?php
// In single-annunci_4zampe.php o single-annunci_dogsitter.php

$contact = caniincasa_get_annuncio_contact_info( get_the_ID() );
?>

<div class="annuncio-author-box">
    <?php if ( $contact['is_anonymous'] ) : ?>
        <span class="anonymous-badge">👤 Utente Non Registrato</span>
    <?php endif; ?>

    <h3>Contatta l'inserzionista</h3>

    <div class="author-info">
        <p><strong>Nome:</strong> <?php echo esc_html( $contact['name'] ); ?></p>

        <?php if ( ! empty( $contact['email'] ) ) : ?>
            <p>
                <strong>Email:</strong>
                <a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>">
                    <?php echo esc_html( $contact['email'] ); ?>
                </a>
            </p>
        <?php endif; ?>

        <?php if ( ! empty( $contact['phone'] ) ) : ?>
            <p>
                <strong>Telefono:</strong>
                <a href="tel:<?php echo esc_attr( str_replace( ' ', '', $contact['phone'] ) ); ?>">
                    <?php echo esc_html( $contact['phone'] ); ?>
                </a>
            </p>
        <?php endif; ?>

        <?php if ( ! $contact['is_anonymous'] && ! empty( $contact['user_id'] ) ) : ?>
            <p>
                <a href="<?php echo esc_url( home_url( '/profilo-utente/' . $contact['user_id'] ) ); ?>" class="btn">
                    Vedi Profilo
                </a>
            </p>
        <?php endif; ?>
    </div>
</div>
```

## Meta Fields Salvati

### Per Annunci Utenti Registrati:
- `_annuncio_email` → Email di contatto specifica per questo annuncio
- `_annuncio_phone` → Telefono di contatto specifico per questo annuncio

### Per Annunci Anonimi:
- `_is_anonymous_user` → `'1'` se anonimo, `'0'` se registrato
- `_anonymous_name` → Nome completo
- `_anonymous_email` → Indirizzo email
- `_anonymous_phone` → Numero telefono

## Note Importanti

1. **Validazione**: I campi nome, email e telefono sono OBBLIGATORI per annunci anonimi
2. **Email valida**: Il sistema verifica che l'email sia in formato corretto
3. **Privacy**: I dati anonimi sono visibili pubblicamente come quelli degli utenti registrati
4. **Sicurezza**: Tutti i dati vengono sanitizzati prima del salvataggio
5. **Messaggi Errore**: Se mancano dati obbligatori, appare un avviso admin

## Compatibilità

- **Annunci 4 Zampe** (`annunci_4zampe`)
- **Annunci Dogsitter** (`annunci_dogsitter`)

## Aggiornamenti Futuri

Per estendere questa funzionalità ad altri CPT:

```php
// In cpt-annunci.php, aggiungi il CPT agli array:
array( 'annunci_4zampe', 'annunci_dogsitter', 'nuovo_cpt' )
```
