# Report Analisi Strutture Caniincasa.it

**Data**: 18 Novembre 2025
**Branch**: `claude/fix-acf-field-mapping-01TMbbHUKBmnKuPi2FJRa5tX`
**Obiettivo**: Verifica minuziosa CSV vs ACF importati e revisione template

---

## 1. ANALISI CSV DISPONIBILI

### 1.1 Conteggio Record

| Tipologia | File CSV | Numero Record |
|-----------|----------|---------------|
| Allevamenti | Allevamenti-Export-2025-November-17-1454.csv | 8.169 |
| Canili | Canili-Export-2025-November-17-1510.csv | 80 |
| Centri Cinofili | Centri-Cinofili-Export-2025-November-17-1516.csv | 21 |
| Pensioni | Pensioni-per-Cani-Export-2025-November-17-1518.csv | 46 |
| Veterinari | Strutture-Veterinarie-Export-2025-November-17-1522.csv | 26.558 |
| Razze | Razze-di-Cani-Export-2025-November-17-1521.csv | 617 |
| **TOTALE** | | **35.491** |

### 1.2 Struttura Colonne CSV

#### CANILI
```
ID, Title, Content, Permalink, indirizzo:, indirizzo, comune, provincia,
telefono, sito_web, email, riferimento, provincia_estesa, Slug
```

**⚠️ PROBLEMA RILEVATO**: Presenza di due colonne simili:
- `indirizzo:` (con due punti)
- `indirizzo` (senza due punti)

#### PENSIONI PER CANI
```
ID, Title, Content, Permalink, Province, nome_struttura, indirizzo, regione,
provincia, comune, telefono, email, sito, referente, altre_informazioni,
cap, sito_web, Slug
```

**⚠️ NOTA**: Campo `sito` e `sito_web` (potrebbero essere duplicati)

#### CENTRI CINOFILI
```
ID, Title, Content, Permalink, Province, indirizzo, regione, provincia,
comune, telefono, email, sito, referente, altre_informazioni, Slug
```

#### VETERINARI (Strutture Veterinarie)
```
ID, Title, Content, Excerpt, Date, Post Type, Permalink, Servizi Veterinari,
Province, id_struttura, nome_struttura, tipologia, direttore_sanitario,
indirizzo, localita, provincia, telefono, email, sito_web,
pronto_soccorso_h24, reperibilita_h24, specie_animali_trattate,
servizi_offerti, orari_di_apertura, provincia_estesa, cap, comune,
regione, url_2, Slug
```

**✅ STRUTTURA PIÙ RICCA**: Contiene campi aggiuntivi specifici per veterinari:
- `direttore_sanitario`
- `pronto_soccorso_h24`
- `reperibilita_h24`
- `specie_animali_trattate`
- `servizi_offerti`
- `orari_di_apertura` (⚠️ ATTENZIONE: può contenere HTML)
- `tipologia`

#### ALLEVAMENTI
```
ID, Title, Content, Permalink, Razze Allevamenti, Province, persona,
localita, provincia_, email, sito_web, telefono, sregcode, idaffisso,
desaffisso, proprietario, codregione, desregione, codprovincia,
desprovincia, desindirizzo, deslocalita, codcap, razzecount, Slug
```

**✅ GIÀ IMPORTATO E FUNZIONANTE**

---

## 2. CAMPI ACF DEFINITI

### 2.1 Campi Comuni a TUTTE le Strutture
Definiti in `group_strutture_contatti`:
- `indirizzo` ✅
- `citta` ✅
- `cap` ✅
- `telefono` ✅
- `cellulare` (WhatsApp) ✅
- `email` ✅
- `sito_web` ✅
- `facebook` ✅
- `instagram` ✅

Definiti in `group_strutture_geo`:
- `latitudine` ✅
- `longitudine` ✅

### 2.2 Campi Specifici per ALLEVAMENTI
Definiti in `group_allevamenti`:
- `razze_allevate` (relationship con razze_di_cani) ✅
- `persona` ✅
- `localita` ✅
- `provincia` ✅
- `affisso` ✅
- `proprietario` ✅
- `id_affisso` ✅

### 2.3 Campi MANCANTI per VETERINARI

**❌ NON DEFINITI IN ACF:**
- `nome_struttura`
- `tipologia`
- `direttore_sanitario`
- `localita`
- `comune`
- `regione`
- `pronto_soccorso` (da CSV: `pronto_soccorso_h24`)
- `reperibilita` (da CSV: `reperibilita_h24`)
- `specie_trattate` (da CSV: `specie_animali_trattate`)
- `servizi` (da CSV: `servizi_offerti`)
- `orari` (da CSV: `orari_di_apertura`)

### 2.4 Campi MANCANTI per CANILI

**❌ NON DEFINITI IN ACF:**
- `comune`
- `provincia_estesa`
- `riferimento` (referente)

### 2.5 Campi MANCANTI per PENSIONI

**❌ NON DEFINITI IN ACF:**
- `nome_struttura`
- `regione`
- `comune`
- `referente`
- `altre_informazioni`

### 2.6 Campi MANCANTI per CENTRI CINOFILI

**❌ NON DEFINITI IN ACF:**
- `regione`
- `comune`
- `referente`
- `altre_informazioni`

---

## 3. TEMPLATE ESISTENTI

### 3.1 Template Archive (ESISTENTI ✅)
- `archive-allevamenti.php` ✅
- `archive-canili.php` ✅
- `archive-pensioni_per_cani.php` ✅
- `archive-centri_cinofili.php` ✅
- `archive-veterinari.php` ✅

### 3.2 Template Single

**ESISTENTI:**
- `single-allevamenti.php` ✅ (COMPLETO E FUNZIONANTE)
- `single-razze_di_cani.php` ✅
- `single-annunci_4zampe.php` ✅
- `single-annunci_dogsitter.php` ✅

**MANCANTI (❌):**
- `single-canili.php` ❌
- `single-pensioni_per_cani.php` ❌
- `single-centri_cinofili.php` ❌
- `single-veterinari.php` ❌

**Conseguenza**: Le strutture canili, pensioni, centri cinofili e veterinari usano il template generico `single.php` o non vengono visualizzate correttamente.

---

## 4. MAPPING CSV → ACF NELL'IMPORTER

### 4.1 Allevamenti (✅ FUNZIONANTE)
Mapping definito in `csv-importer.php` linea 399-411:

```php
$acf_fields = array(
    'persona'       => 'persona',
    'desindirizzo'  => 'indirizzo',
    'deslocalita'   => 'localita',
    'provincia_'    => 'provincia',
    'codcap'        => 'cap',
    'telefono'      => 'telefono',
    'email'         => 'email',
    'sito_web'      => 'sito_web',
    'desaffisso'    => 'affisso',
    'proprietario'  => 'proprietario',
    'idaffisso'     => 'id_affisso',
);
```

### 4.2 Altre Strutture (❌ MAPPING GENERICO INCOMPLETO)
Mapping generico in `csv-importer.php` linea 614-631:

```php
$acf_fields = array(
    'nome_struttura'        => 'nome_struttura',
    'indirizzo'             => 'indirizzo',
    'localita'              => 'localita',
    'cap'                   => 'cap',
    'comune'                => 'comune',
    'provincia'             => 'provincia',
    'regione'               => 'regione',
    'telefono'              => 'telefono',
    'email'                 => 'email',
    'sito_web'              => 'sito_web',
    'direttore_sanitario'   => 'direttore_sanitario',
    'pronto_soccorso_h24'   => 'pronto_soccorso',
    'reperibilita_h24'      => 'reperibilita',
    'specie_animali_trattate' => 'specie_trattate',
    'servizi_offerti'       => 'servizi',
    'orari_di_apertura'     => 'orari',
);
```

**⚠️ PROBLEMA**: Questi campi ACF NON ESISTONO! Devono essere creati in `acf-fields.php`.

---

## 5. PROBLEMI IDENTIFICATI

### 5.1 Priorità ALTA 🔴

1. **Campi ACF mancanti per Veterinari**
   - I campi specifici non sono definiti in ACF
   - L'import salva i dati ma non vengono visualizzati

2. **Template Single mancanti**
   - Canili, Pensioni, Centri Cinofili, Veterinari non hanno template single dedicati
   - Impossibile visualizzare correttamente i dati importati

3. **Mapping CSV incoerente**
   - CSV Canili ha `indirizzo:` invece di `indirizzo`
   - CSV Pensioni ha sia `sito` che `sito_web`

### 5.2 Priorità MEDIA 🟡

4. **Campi comuni mancanti**
   - `comune`, `regione`, `referente`, `altre_informazioni` mancano per alcune strutture

5. **Campo `orari_di_apertura` per Veterinari**
   - Contiene HTML che deve essere gestito correttamente (wp_kses_post)

### 5.3 Priorità BASSA 🟢

6. **Ottimizzazione import**
   - Verificare se tutti i campi CSV vengono effettivamente importati
   - Test su record campione

---

## 6. VERIFICHE DA FARE

### 6.1 Verifica Import Canili
```bash
wp post list --post_type=canili --format=count
```
- [ ] Verificare se i dati sono stati importati
- [ ] Controllare campo `indirizzo` vs `indirizzo:`
- [ ] Verificare presenza provincia

### 6.2 Verifica Import Pensioni
```bash
wp post list --post_type=pensioni_per_cani --format=count
```
- [ ] Verificare campo `sito` vs `sito_web`
- [ ] Controllare `nome_struttura`

### 6.3 Verifica Import Centri Cinofili
```bash
wp post list --post_type=centri_cinofili --format=count
```
- [ ] Verificare tutti i campi base

### 6.4 Verifica Import Veterinari
```bash
wp post list --post_type=veterinari --format=count
```
- [ ] Verificare campi specifici (pronto_soccorso_h24, orari, ecc.)
- [ ] Test campo `orari_di_apertura` con HTML

---

## 7. PIANO DI LAVORO

### Fase 1: Verifica Import Esistente ✅ COMPLETATA
1. ✅ Analizzare CSV e campi disponibili
2. ✅ Identificare campi mancanti o non mappati
3. ✅ Creare script di verifica `test-verify-strutture-import.php`

### Fase 2: Aggiornamento Campi ACF ✅ COMPLETATA
1. ✅ Aggiungere campi mancanti per Veterinari
2. ✅ Aggiungere campi comuni mancanti (nome_struttura, comune, localita, regione, provincia_estesa, referente)
3. ✅ Aggiungere campo `altre_informazioni` per Pensioni e Centri Cinofili
4. ✅ Campi ACF Veterinari: tipologia, direttore_sanitario, pronto_soccorso, reperibilita, specie_trattate, servizi, orari

### Fase 3: Aggiornamento Importer ✅ COMPLETATA
1. ✅ Correggere mapping per Canili (`indirizzo:` vs `indirizzo`)
2. ✅ Correggere mapping per Pensioni (`sito` vs `sito_web`)
3. ✅ Aggiungere gestione HTML per `orari_di_apertura` (wp_kses_post)
4. ✅ Mapping specifico per tipo di struttura
5. ✅ Gestione alias campi (riferimento → referente)

### Fase 4: Creazione Template Single ✅ COMPLETATA
1. ✅ Creare `single-canili.php`
2. ✅ Creare `single-pensioni_per_cani.php`
3. ✅ Creare `single-centri_cinofili.php`
4. ✅ Creare `single-veterinari.php` (con campi specifici e box pronto soccorso H24)

### Fase 5: Test e Verifica ⏳ IN CORSO
1. ⏳ Testare import per ogni tipologia
2. ⏳ Verificare visualizzazione template single
3. ⏳ Verificare filtri AJAX negli archivi

---

## 8. NOTE TECNICHE

### 8.1 File da Modificare
- `/wp-content/plugins/caniincasa-core/includes/acf-fields.php` (aggiungere campi)
- `/wp-content/plugins/caniincasa-core/includes/csv-importer.php` (correggere mapping)
- `/wp-content/themes/caniincasa-theme/single-*.php` (creare template mancanti)

### 8.2 Riferimenti
- Brief: `brief_sviluppo_tema_plugin_caniincasa.md`
- Modello funzionante: `single-allevamenti.php`
- CSV disponibili nella root del progetto

---

## 9. CONCLUSIONI

**STATO ATTUALE:**
- ✅ Allevamenti: COMPLETO e FUNZIONANTE
- ❌ Canili: Import possibile ma template single MANCANTE
- ❌ Pensioni: Import possibile ma template single MANCANTE
- ❌ Centri Cinofili: Import possibile ma template single MANCANTE
- ❌ Veterinari: Campi ACF MANCANTI + template single MANCANTE

**PROSSIMI PASSI:**
1. ⏳ Testare import/re-import per ogni tipologia di struttura
2. ⏳ Verificare visualizzazione template single su dati reali
3. ⏳ Testare filtri AJAX negli archivi
4. ⏳ Ottimizzare performance query

---

## 10. MODIFICHE EFFETTUATE (18 Novembre 2025)

### 10.1 File Modificati

#### `/wp-content/plugins/caniincasa-core/includes/acf-fields.php`
**Modifiche:**
- ✅ Aggiunti campi comuni a tutte le strutture:
  - `nome_struttura`, `localita`, `comune`, `provincia`, `provincia_estesa`, `regione`, `referente`
- ✅ Creato gruppo `group_veterinari_specifici` con 7 nuovi campi:
  - `tipologia`, `direttore_sanitario`, `pronto_soccorso`, `reperibilita`, `specie_trattate`, `servizi`, `orari` (WYSIWYG)
- ✅ Creato gruppo `group_strutture_altre_info` per Pensioni e Centri Cinofili:
  - `altre_informazioni`

#### `/wp-content/plugins/caniincasa-core/includes/csv-importer.php`
**Modifiche:**
- ✅ Riscritto mapping campi comuni per tutte le strutture
- ✅ Aggiunta gestione campo `indirizzo:` (con due punti) per Canili
- ✅ Aggiunta gestione alias `sito` → `sito_web`
- ✅ Aggiunta gestione alias `riferimento` → `referente`
- ✅ Mapping specifico per Veterinari con tutti i campi dedicati
- ✅ Campo `orari_di_apertura` gestito con `wp_kses_post()` per permettere HTML
- ✅ Mapping specifico per Pensioni e Centri Cinofili (`altre_informazioni`)

### 10.2 File Creati

#### Template Single
1. ✅ `/wp-content/themes/caniincasa-theme/single-canili.php`
   - Template basato su allevamenti
   - Campi: nome_struttura, referente, indirizzo, comune, provincia_estesa, telefono, email, sito_web

2. ✅ `/wp-content/themes/caniincasa-theme/single-pensioni_per_cani.php`
   - Include sezione "Altre Informazioni"
   - Campi completi con regione

3. ✅ `/wp-content/themes/caniincasa-theme/single-centri_cinofili.php`
   - Layout simile a pensioni
   - Include sezione "Altre Informazioni"

4. ✅ `/wp-content/themes/caniincasa-theme/single-veterinari.php`
   - Template più complesso con sezioni dedicate:
     - Box "Servizi e Disponibilità" (pronto_soccorso, reperibilita, specie_trattate)
     - Box "Orari di Apertura" con supporto HTML
     - Sezione "Servizi Offerti"
   - Sidebar con box speciale "Pronto Soccorso H24" (se disponibile)

#### Tool di Verifica
5. ✅ `/wp-content/plugins/caniincasa-core/test-verify-strutture-import.php`
   - Script di test per verificare import CSV vs ACF
   - Verifica 3 post per ogni tipologia
   - Mostra statistiche completamento
   - Verifica esistenza template single

#### Documentazione
6. ✅ `/REPORT_ANALISI_STRUTTURE.md`
   - Report completo analisi CSV
   - Mappatura campi
   - Piano di lavoro
   - Questo documento

### 10.3 Statistiche Finali

| Componente | Prima | Dopo | Stato |
|-----------|-------|------|-------|
| Campi ACF Strutture | 9 | 18 | ✅ +100% |
| Campi ACF Veterinari | 0 | 7 | ✅ Nuovo |
| Template Single | 1/5 | 5/5 | ✅ 100% |
| Mapping CSV | Parziale | Completo | ✅ 100% |
| Gestione HTML | ❌ | ✅ | ✅ Implementato |

---

**Fine Report** - Ultimo aggiornamento: 18 Novembre 2025
