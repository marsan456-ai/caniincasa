# Guida: Importazione CSV Classificazioni Razze

## Panoramica

Il sistema di importazione CSV permette di aggiornare in massa le classificazioni (taglia e gruppo FCI) delle razze di cani presenti nel sito, senza sovrascrivere altri dati come descrizioni, immagini, o altri campi personalizzati.

## Accesso

**Percorso**: Menu Admin WordPress → **Razze** → **Importa CSV**

## Formato CSV Richiesto

Il file CSV deve avere esattamente queste 4 colonne nell'ordine specificato:

```csv
ID,Title,Taglia,Gruppo FCI
14790,Chihuahua,Toy,9
14784,Cavalier king charles spaniel,Toy,9
14738,Volpino italiano,Piccola,5
14740,Weimaraner,Grande,7
```

### Colonne

| Colonna | Descrizione | Obbligatorio | Valori Accettati |
|---------|-------------|--------------|------------------|
| **ID** | ID del post WordPress della razza | ✅ Sì | Numero intero positivo |
| **Title** | Nome della razza (solo riferimento) | ❌ No* | Testo libero |
| **Taglia** | Classificazione taglia | ✅ Sì | `Toy`, `Piccola`, `Media`, `Grande`, `Gigante` |
| **Gruppo FCI** | Numero gruppo FCI | ✅ Sì | Numero da `1` a `10` |

*Il campo Title non viene utilizzato per l'importazione, serve solo come riferimento umano nel CSV.

### Taglie Supportate

| Taglia | Range Peso | Slug Tassonomia |
|--------|-----------|-----------------|
| Toy | < 4 kg | `toy` |
| Piccola | 4-10 kg | `piccola` |
| Media | 10-25 kg | `media` |
| Grande | 25-45 kg | `grande` |
| Gigante | > 45 kg | `gigante` |

**Nota**: È possibile assegnare **taglie multiple** a una singola razza separandole con virgola:
```csv
14790,Beagle,"Piccola,Media",6
```

### Gruppi FCI Supportati

| Numero | Descrizione |
|--------|-------------|
| 1 | Cani da pastore e bovari |
| 2 | Pinscher, Schnauzer, Molossoidi |
| 3 | Terrier |
| 4 | Bassotti |
| 5 | Spitz e primitivi |
| 6 | Segugi e per pista di sangue |
| 7 | Cani da ferma |
| 8 | Cani da riporto, da cerca, da acqua |
| 9 | Cani da compagnia |
| 10 | Levrieri |

## Procedura di Importazione

### Passo 1: Preparare il File CSV

1. Aprire Excel, Google Sheets o editor testo
2. Creare le 4 colonne: `ID`, `Title`, `Taglia`, `Gruppo FCI`
3. Inserire i dati delle razze da aggiornare
4. Salvare come **CSV (UTF-8)**

**Esempio Excel/Google Sheets**:

| ID | Title | Taglia | Gruppo FCI |
|----|-------|--------|------------|
| 14790 | Chihuahua | Toy | 9 |
| 14784 | Cavalier king charles spaniel | Toy | 9 |
| 14738 | Volpino italiano | Piccola | 5 |

### Passo 2: Test con Dry Run (Consigliato)

Prima di importare definitivamente, è **altamente consigliato** eseguire un test:

1. Accedere a **Razze → Importa CSV**
2. Click su **Seleziona File** e caricare il CSV
3. ✅ **Spuntare "Modalità Test (Dry Run)"**
4. Click su **Importa CSV**
5. Verificare il log:
   - ✅ Razze aggiornate correttamente
   - ❌ Razze non trovate (ID errati)
   - ⚠️ Errori di validazione

### Passo 3: Importazione Definitiva

Dopo aver verificato il test:

1. Ricaricare la pagina
2. Selezionare nuovamente il file CSV
3. ❌ **NON spuntare** "Modalità Test"
4. Click su **Importa CSV**
5. Attendere l'elaborazione
6. Verificare il riepilogo finale:
   - **Razze aggiornate**: numero di razze modificate con successo
   - **Razze non trovate**: ID non esistenti nel database
   - **Errori**: problemi di validazione o formato

### Passo 4: Verifica

1. Navigare all'**archivio Razze**
2. Verificare che le tassonomie siano state applicate correttamente
3. Controllare alcune razze specifiche:
   - Click su "Modifica" razza
   - Verificare le caselle **Taglie** e **Gruppi FCI** nella sidebar

## Log Dettagliato

Il sistema fornisce un log completo dell'importazione. Click su **"Dettagli importazione"** per espandere:

```
Inizio importazione: 2025-11-21 10:30:45
File: razze_classificate_final.csv

✓ Riga 2: Chihuahua (ID 14790) - Taglia: toy, Gruppo FCI: 9
✓ Riga 3: Thai bangkaew dog (ID 14722) - Taglia: media, Gruppo FCI: 5
Riga 4: Razza ID 99999 non trovata o non è una razza
✓ Riga 5: Volpino italiano (ID 14738) - Taglia: piccola, Gruppo FCI: 5

=== RIEPILOGO ===
Razze aggiornate: 3
Razze non trovate: 1
Errori: 0
```

## Dashboard Stato Tassonomie

Nella pagina di importazione è visibile una dashboard con:

### Taglie Disponibili
- Toy (< 4 kg) - `toy` - (X razze)
- Piccola (4-10 kg) - `piccola` - (X razze)
- Media (10-25 kg) - `media` - (X razze)
- Grande (25-45 kg) - `grande` - (X razze)
- Gigante (> 45 kg) - `gigante` - (X razze)

### Gruppi FCI Disponibili
- Gruppo 1 - Cani da pastore e bovari - (X razze)
- Gruppo 2 - Pinscher, Schnauzer, Molossoidi - (X razze)
- ... ecc.

## Caratteristiche Importanti

### ✅ Aggiornamento NON Distruttivo

Il sistema aggiorna **SOLO** le tassonomie `razza_taglia` e `razza_gruppo`. Tutti gli altri dati rimangono intatti:

- ✅ Titolo razza
- ✅ Descrizione
- ✅ Immagini
- ✅ Campi ACF (carattere, temperamento, salute, ecc.)
- ✅ Meta dati esistenti
- ✅ URL/permalink

### 🔒 Sicurezza

- Solo amministratori possono accedere
- Validazione formato CSV obbligatoria
- Validazione ID post esistente
- Nonce security per form submission
- Sanificazione di tutti i dati input

### 🚀 Performance

- Elaborazione veloce (centinaia di razze in secondi)
- Nessun timeout su file grandi
- Log in tempo reale

## Risoluzione Problemi

### "Razza ID XXXXX non trovata"

**Causa**: L'ID nel CSV non corrisponde a nessun post di tipo `razze_di_cani` nel database.

**Soluzione**:
1. Verificare l'ID corretto accedendo a Razze → Tutte le Razze
2. Hover sul titolo della razza e controllare l'ID nell'URL: `post=14790`
3. Aggiornare il CSV con l'ID corretto

### "Taglia 'XXXXX' non riconosciuta"

**Causa**: Il valore nel campo Taglia non è valido.

**Soluzione**: Usare SOLO questi valori (case-insensitive):
- `Toy`
- `Piccola`
- `Media`
- `Grande`
- `Gigante`

### "Gruppo FCI 'X' non valido"

**Causa**: Il numero gruppo non è compreso tra 1 e 10.

**Soluzione**: Usare solo numeri da `1` a `10` (senza zeri iniziali).

### Il file non viene caricato

**Causa**: Formato file non corretto o troppo grande.

**Soluzione**:
1. Salvare il file come **CSV UTF-8**
2. Verificare dimensione < 10 MB
3. Controllare che non ci siano caratteri speciali nel nome file

## Aggiornamento Termini Tassonomie

Se necessario forzare la ricreazione dei termini delle tassonomie (es. dopo aggiunta nuova taglia):

1. Navigare a qualsiasi pagina admin
2. Aggiungere alla URL: `?caniincasa_update_razza_terms=1`
3. Esempio: `wp-admin/edit.php?post_type=razze_di_cani&caniincasa_update_razza_terms=1`
4. Premere Invio
5. Vedere messaggio di conferma: "Tassonomie razze aggiornate con successo!"

## File di Esempio

Un file CSV di esempio (`razze_classificate_final.csv`) è disponibile nella root del progetto con 324 razze già classificate.

## Supporto Tecnico

Per problemi tecnici o domande:
1. Verificare log dettagliato dell'importazione
2. Controllare questa guida per soluzioni comuni
3. Contattare il team di sviluppo con:
   - Screenshot del log errori
   - File CSV utilizzato (primi 10 righe)
   - Numero razze totali nel database

---

**Ultima modifica**: 2025-11-21
**Versione**: 1.0.0
