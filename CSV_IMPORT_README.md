# CSV Import Guide - Caniincasa

Questa guida spiega come importare i dati CSV nel sito Caniincasa.

## Prerequisiti

- Plugin "Caniincasa Core" attivato
- Plugin "Advanced Custom Fields PRO" attivato
- Accesso WP-CLI al server (raccomandato per grandi file)

## File CSV Disponibili

Il repository contiene i seguenti file CSV pronti per l'importazione:

1. `Razze-di-Cani-Export-2025-November-17-1521.csv` (1.4MB) - 400+ razze
2. `Allevamenti-Export-2025-November-17-1454.csv` (2.3MB) - Allevamenti riconosciuti
3. `Strutture-Veterinarie-Export-2025-November-17-1522.csv` (6.8MB) - Cliniche veterinarie
4. `Canili-Export-2025-November-17-1510.csv` (22KB) - Canili e rifugi
5. `Centri-Cinofili-Export-2025-November-17-1516.csv` (8.3KB) - Centri addestramento
6. `Pensioni-per-Cani-Export-2025-November-17-1518.csv` (7.7KB) - Pensioni

## Metodo 1: WP-CLI (Raccomandato)

Il metodo WP-CLI è più veloce e affidabile per grandi quantità di dati.

### Importare Singoli File

```bash
# Importa razze
wp caniincasa import razze /home/user/caniincasa/Razze-di-Cani-Export-2025-November-17-1521.csv

# Importa allevamenti
wp caniincasa import allevamenti /home/user/caniincasa/Allevamenti-Export-2025-November-17-1454.csv

# Importa veterinari
wp caniincasa import veterinari /home/user/caniincasa/Strutture-Veterinarie-Export-2025-November-17-1522.csv

# Importa canili
wp caniincasa import canili /home/user/caniincasa/Canili-Export-2025-November-17-1510.csv

# Importa pensioni
wp caniincasa import pensioni /home/user/caniincasa/Pensioni-per-Cani-Export-2025-November-17-1518.csv

# Importa centri cinofili
wp caniincasa import centri-cinofili /home/user/caniincasa/Centri-Cinofili-Export-2025-November-17-1516.csv
```

### Importare Tutti i File

Per importare tutti i file CSV in un'unica operazione:

```bash
wp caniincasa import all /home/user/caniincasa/
```

## Metodo 2: Codice PHP

Se non hai accesso a WP-CLI, puoi creare uno script temporaneo:

### Creare File Importer

Crea un file `import-csv.php` nella root di WordPress:

```php
<?php
/**
 * Temporary CSV Import Script
 * ATTENZIONE: Elimina questo file dopo l'uso!
 */

// Carica WordPress
require_once __DIR__ . '/wp-load.php';

// Verifica autorizzazione
if ( ! current_user_can( 'administrator' ) ) {
    die( 'Accesso non autorizzato' );
}

// Ottieni importer
$importer = caniincasa_csv_importer();

// Importa razze
echo "Importazione razze...\n";
$result = $importer->import_razze( __DIR__ . '/Razze-di-Cani-Export-2025-November-17-1521.csv' );
print_r( $result );

// Importa allevamenti
echo "\nImportazione allevamenti...\n";
$result = $importer->import_allevamenti( __DIR__ . '/Allevamenti-Export-2025-November-17-1454.csv' );
print_r( $result );

// Continua con altri import...

echo "\nImportazione completata!\n";
```

### Eseguire Script

```bash
php import-csv.php
```

oppure visita: `https://tuosito.it/import-csv.php` (se permesso dal server)

**IMPORTANTE:** Elimina il file `import-csv.php` dopo l'uso per sicurezza!

## Cosa Viene Importato

### Razze di Cani

- **Post Data:** Titolo, contenuto, slug
- **Immagine:** Featured image scaricata da URL
- **Campi Info:** Nazione origine, colorazioni, temperamento
- **Campi Contenuto:** Descrizione, origini, aspetto, carattere, salute, attività, ideale per
- **Caratteristiche:** 18 rating (1-5) per energia, affettuosità, vocalità, ecc.
- **SEO:** Old slug per redirects 301

### Allevamenti

- **Post Data:** Titolo, contenuto, slug
- **Taxonomy:** Provincia
- **Campi ACF:** Indirizzo, telefono, email, sito web, affisso, proprietario

### Strutture (Veterinari, Canili, Pensioni, Centri Cinofili)

- **Post Data:** Titolo, contenuto, excerpt, slug
- **Taxonomy:** Provincia
- **Campi ACF:** Indirizzo completo, contatti, servizi, orari, informazioni specifiche

## Gestione Duplicati

L'importer verifica automaticamente i duplicati tramite lo **slug**:

- Se esiste un post con lo stesso slug → **AGGIORNA** i dati
- Se non esiste → **CREA NUOVO** post

Questo permette di:
- Eseguire l'import più volte senza creare duplicati
- Aggiornare i dati modificando il CSV e reimportando

## Monitoraggio Progresso

L'output dell'import mostra:

```
Total: 432       # Righe totali nel CSV
Imported: 380    # Nuovi post creati
Updated: 50      # Post esistenti aggiornati
Skipped: 2       # Righe saltate per errori
```

Gli errori vengono listati con:
- Titolo del record
- Messaggio di errore specifico

## Performance

### Ottimizzazioni Implementate

- **Batch Processing:** Pause ogni 10 record (0.1s)
- **Memory Management:** Liberazione variabili dopo ogni record
- **Conditional Image Import:** Le immagini vengono scaricate solo se non esistono

### Tempi Stimati (WP-CLI)

- Razze (400 record): ~5-7 minuti
- Allevamenti (2000+ record): ~15-20 minuti
- Veterinari (6000+ record): ~45-60 minuti
- Totale: ~1-2 ore

**Nota:** I tempi dipendono da velocità server e download immagini.

## Troubleshooting

### Timeout PHP

Se ricevi timeout, aumenta i limiti in `php.ini`:

```ini
max_execution_time = 3600
memory_limit = 512M
```

### Errori di Memoria

Riduci il batch size nel file `csv-importer.php`:

```php
private $batch_size = 5; // Invece di 10
```

### Immagini Non Importate

Verifica:
1. Permessi scrittura su `wp-content/uploads/`
2. URL immagini accessibili
3. Funzione `download_url()` non bloccata da firewall

### Errori ACF

Assicurati che:
- ACF Pro sia attivato
- I field groups siano stati creati dal plugin
- I nomi dei campi corrispondano

## Post-Import

Dopo l'importazione:

1. **Flush Rewrite Rules:** Vai in Settings → Permalinks e salva
2. **Rigenera Thumbnails:** Usa plugin come "Regenerate Thumbnails"
3. **Verifica Tassonomie:** Controlla che province, taglie, gruppi siano assegnati
4. **Test Frontend:** Visita alcune pagine per verificare visualizzazione
5. **SEO Check:** Verifica sitemap XML e meta tags

## Sicurezza

- ✅ Validazione e sanitizzazione di tutti i dati
- ✅ Nonce verification per AJAX
- ✅ Capability check per admin-only
- ✅ wp_kses_post per contenuti HTML
- ✅ Gestione sicura file upload

## Support

Per problemi o domande:
1. Controlla i log di WordPress (`wp-content/debug.log`)
2. Verifica la console del browser per errori JavaScript
3. Contatta il team di sviluppo Caniincasa

---

**Ultima revisione:** 2025-11-17
**Versione Plugin:** 1.0.0
