# 🚀 Guida Rapida: Importazione Categorie Articoli

## 📦 File Necessari

- `Articoli-Export-2025-November-21-0711-categorizzati.csv` - CSV con categorie (128 articoli)
- `import_categories.php` - Script di importazione
- `verify_categories.php` - Script di verifica

---

## ⚡ Installazione Rapida

### Step 1: Carica i File

Carica questi 3 file nella **root di WordPress** (stessa cartella di wp-config.php):

```
/public_html/
├── wp-config.php
├── wp-load.php
├── Articoli-Export-2025-November-21-0711-categorizzati.csv  ← CARICA
├── import_categories.php                                      ← CARICA
└── verify_categories.php                                      ← CARICA
```

### Step 2: Verifica Stato Iniziale

**Opzione A - Browser (Consigliato):**
```
http://tuosito.it/verify_categories.php
```

**Opzione B - CLI:**
```bash
php verify_categories.php
```

Questo ti mostrerà:
- Quanti articoli hai
- Quanti hanno già categorie
- Stato attuale delle categorie

### Step 3: TEST (Dry Run) - IMPORTANTE!

Prima di modificare il database, fai un test:

**Browser:**
```
http://tuosito.it/import_categories.php?dry_run=1
```

Questo **simula** l'importazione senza modificare nulla. Controlla che tutto sia ok!

### Step 4: Importazione Reale

Se il test è ok, esegui l'importazione reale:

**Browser:**
```
http://tuosito.it/import_categories.php
```

**CLI:**
```bash
php import_categories.php
```

### Step 5: Verifica Risultato

Controlla che tutto sia andato bene:

```
http://tuosito.it/verify_categories.php
```

### Step 6: Pulizia

**IMPORTANTE:** Dopo l'importazione, **elimina gli script** per sicurezza:
- `import_categories.php`
- `verify_categories.php`

Puoi tenere il CSV per backup.

---

## 📊 Cosa Fa lo Script

### import_categories.php

1. ✅ Legge il CSV
2. ✅ Crea le 8 categorie principali se non esistono
3. ✅ Crea le sottocategorie come child
4. ✅ Assegna categorie agli articoli
5. ✅ Rimuove vecchie categorie
6. ✅ Mostra report dettagliato

### Categorie Create

1. **Educazione & Comportamento**
   - Training base
   - Problemi comportamentali
   - Socializzazione
   - Psicologia canina
   - Comandi avanzati

2. **Primo Cane**
   - Guida pre-adozione
   - Primi 30 giorni
   - Checklist e preparazione
   - Errori comuni
   - Setup casa

3. **Vita Quotidiana**
   - Alimentazione pratica
   - Toelettatura e cura
   - Casa dog-friendly
   - Routine quotidiana
   - Gestione budget

4. **Viaggi & Lifestyle**
   - Viaggiare con il cane
   - Destinazioni pet-friendly
   - Normative trasporti
   - Hotel e ristoranti
   - Vacanze e weekend

5. **Sport & Attività**
   - Sport cinofili
   - Dog trekking
   - Giochi e attività
   - Fitness per cani
   - Eventi e competizioni

6. **Storie & Esperienze**
   - Storie di adozione
   - Testimonianze
   - Casi studio
   - Interviste esperti
   - Community stories

7. **Leggi & Normative**
   - Normative italiane
   - Documenti necessari
   - Regolamenti locali
   - Diritti e doveri
   - Questioni legali

8. **Guide & Tutorial**
   - Guide complete
   - Video tutorial
   - Infografiche
   - Checklist scaricabili
   - How-to pratici

---

## 🎨 Interfaccia

### Browser (Interfaccia Grafica)

Lo script mostra un'interfaccia pulita con:
- 📊 Statistiche in tempo reale
- ✅ Log colorato delle operazioni
- 📈 Progress bar
- ⚠️ Alert per problemi
- 🔗 Link diretti a WordPress Admin

### CLI (Linea di Comando)

Output testuale con:
- Emoji per stato operazioni
- Statistiche finali
- Lista errori (se presenti)

---

## 🛡️ Sicurezza

### Lo script include:

- ✅ Verifica permessi amministratore
- ✅ Validazione dati CSV
- ✅ Gestione errori
- ✅ Modalità dry-run per test
- ✅ Cache categorie per performance
- ✅ Sanitizzazione slug

### Raccomandazioni:

1. **Fai BACKUP del database** prima di importare
2. **Usa dry-run** prima dell'importazione reale
3. **Prova su staging** se disponibile
4. **Elimina gli script** dopo l'uso

---

## 📈 Risultati Attesi

Dopo l'importazione:

- ✅ 128 articoli categorizzati
- ✅ 8 categorie principali create
- ✅ ~25 sottocategorie create
- ✅ Ogni articolo ha 2 categorie (principale + sotto)
- ✅ Report completo delle operazioni

---

## 🐛 Troubleshooting

### Problema: "File CSV non trovato"

**Soluzione:**
- Verifica che il CSV sia nella root di WordPress
- Controlla il nome file (copia-incolla per evitare errori)
- Verifica permessi file (644)

### Problema: "Accesso negato"

**Soluzione:**
- Devi essere loggato come amministratore
- Usa un browser dove sei già loggato in WP Admin
- Oppure usa CLI: `php import_categories.php`

### Problema: Script timeout

**Soluzione:**
- Aumenta `max_execution_time` in php.ini
- Usa CLI invece del browser
- Contatta il supporto hosting

### Problema: Categorie duplicate

**Soluzione:**
- Lo script controlla automaticamente se esistono
- Se ci sono duplicati, uniscili manualmente in WP Admin
- Poi riesegui lo script

### Problema: Alcuni articoli non categorizzati

**Soluzione:**
- Controlla il CSV per righe con "NON_CATEGORIZZATO"
- Categorizza manualmente o correggi il CSV
- Riesegui importazione

---

## 📞 Verifica Post-Importazione

### In WordPress Admin:

1. **Vai in Articoli → Tutti gli articoli**
   - Verifica che gli articoli abbiano le nuove categorie

2. **Vai in Articoli → Categorie**
   - Controlla che ci siano 8 categorie principali
   - Verifica struttura gerarchica (parent/child)
   - Guarda il conteggio articoli per categoria

3. **Controlla alcuni articoli a campione**
   - Apri articolo
   - Verifica categorie nella sidebar destra
   - Controlla che abbiano sia categoria che sottocategoria

### Con verify_categories.php:

Esegui di nuovo:
```
http://tuosito.it/verify_categories.php
```

Dovresti vedere:
- 100% articoli categorizzati
- 0 articoli senza categoria
- Match 100% con CSV

---

## ✅ Checklist Finale

Dopo l'importazione, verifica:

- [ ] Tutti i 128 articoli hanno categorie
- [ ] Le 8 categorie principali esistono
- [ ] Le sottocategorie sono child delle principali
- [ ] Nessun articolo in "Senza categoria"
- [ ] Report di importazione mostra 0 errori
- [ ] verify_categories.php mostra 100% match
- [ ] Script PHP eliminati dal server

---

## 🎉 Completato!

Le categorie sono ora correttamente importate!

**Prossimi passi:**
- Aggiorna cache (se usi plugin di caching)
- Verifica sitemap XML
- Controlla navigazione categorie nel frontend
- Testa filtri e archivi categorie

---

## 💡 Tips & Best Practices

### Performance

- Lo script usa cache interna per velocizzare
- Processa ~50 articoli/secondo
- Tempo totale: 2-3 secondi per 128 articoli

### Manutenzione

- Mantieni backup del CSV per futuri aggiornamenti
- Se aggiungi nuovi articoli, puoi aggiornare CSV e re-importare
- Lo script è idempotente (puoi eseguirlo più volte senza problemi)

### SEO

- Gli slug categorie vengono generati automaticamente
- WordPress aggiorna automaticamente sitemap
- I link interni verranno mantenuti

---

## 📧 Supporto

Se hai problemi:

1. Controlla i log PHP (wp-content/debug.log)
2. Attiva WP_DEBUG in wp-config.php
3. Verifica permessi file e cartelle
4. Controlla disponibilità memoria PHP
5. Usa dry-run per testare senza rischi

---

**Versione:** 1.0.0
**Testato con:** WordPress 6.4+, PHP 7.4+
