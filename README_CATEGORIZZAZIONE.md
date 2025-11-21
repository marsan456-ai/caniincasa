# 📚 Guida Completa: Ricategorizzazione Articoli Caniincasa.it

## 🎯 Obiettivo

Categorizzare automaticamente 127 articoli esistenti nelle nuove 8 categorie principali con relative sottocategorie usando l'AI.

---

## 📋 File Inclusi

1. **categorize_articles_prompt.md** - Prompt AI completo con tassonomia
2. **categorize_batch.py** - Genera batch di 10 articoli
3. **merge_results.py** - Unisce i risultati e crea CSV finale
4. **Articoli-Export-2025-November-21-0711.csv** - CSV originale (127 articoli)

---

## 🚀 METODO RAPIDO (CONSIGLIATO)

### Step 1: Genera i Batch

```bash
python categorize_batch.py
```

Questo creerà file come:
- `batch_01_of_13.txt` (10 articoli)
- `batch_02_of_13.txt` (10 articoli)
- ...
- `batch_13_of_13.txt` (7 articoli)

### Step 2: Categorizza con AI

Per ogni batch:

1. **Apri ChatGPT/Claude** (o qualsiasi LLM)

2. **Copia e incolla il prompt** da `categorize_articles_prompt.md`

3. **Aggiungi**: "Ora categorizza questi articoli:"

4. **Copia e incolla** il contenuto di `batch_01_of_13.txt`

5. **Salva la risposta** dell'AI in un file chiamato `batch_01_results.txt`

6. **Ripeti** per tutti i batch

**💡 Consiglio**: Usa una nuova conversazione per ogni batch per risultati più consistenti.

### Step 3: Unisci i Risultati

```bash
python merge_results.py
```

Questo creerà:
- `articoli_categorizzati_final.csv` - CSV finale con categorie

### Step 4: Importa in WordPress

Vedi sezione "Importazione in WordPress" sotto.

---

## 🔧 METODO ALTERNATIVO (File Unico)

Se preferisci lavorare con un singolo file grande:

### Step 1: Prepara File Unico

```bash
python prepare_for_ai_categorization.py
```

Genera: `articoli_per_ai.txt` (tutti i 127 articoli)

### Step 2: Categorizza

1. Apri il prompt in `categorize_articles_prompt.md`
2. Copia tutto il contenuto di `articoli_per_ai.txt`
3. Incolla in ChatGPT/Claude (usa GPT-4 o Claude Opus per migliori risultati)
4. Salva la risposta in `ai_categorization_output.txt`

### Step 3: Processa

```bash
python prepare_for_ai_categorization.py --process
```

Genera: `articoli_categorizzati_final.csv`

---

## 📊 Struttura delle Categorie

### 1. Educazione & Comportamento
- Training base
- Problemi comportamentali
- Socializzazione
- Psicologia canina
- Comandi avanzati

### 2. Primo Cane
- Guida pre-adozione
- Primi 30 giorni
- Checklist e preparazione
- Errori comuni
- Setup casa

### 3. Vita Quotidiana
- Alimentazione pratica
- Toelettatura e cura
- Casa dog-friendly
- Routine quotidiana
- Gestione budget

### 4. Viaggi & Lifestyle
- Viaggiare con il cane
- Destinazioni pet-friendly
- Normative trasporti
- Hotel e ristoranti
- Vacanze e weekend

### 5. Sport & Attività
- Sport cinofili
- Dog trekking
- Giochi e attività
- Fitness per cani
- Eventi e competizioni

### 6. Storie & Esperienze
- Storie di adozione
- Testimonianze
- Casi studio
- Interviste esperti
- Community stories

### 7. Leggi & Normative
- Normative italiane
- Documenti necessari
- Regolamenti locali
- Diritti e doveri
- Questioni legali

### 8. Guide & Tutorial
- Guide complete
- Video tutorial
- Infografiche
- Checklist scaricabili
- How-to pratici

---

## 📥 Importazione in WordPress

### OPZIONE A: WP All Import (PIÙ SEMPLICE)

#### 1. Crea le Categorie

Prima crea manualmente in WordPress (Articoli → Categorie):

**Categorie Principali:**
1. Educazione & Comportamento
2. Primo Cane
3. Vita Quotidiana
4. Viaggi & Lifestyle
5. Sport & Attività
6. Storie & Esperienze
7. Leggi & Normative
8. Guide & Tutorial

**Sottocategorie:**
Per ogni categoria, crea le rispettive sottocategorie (vedi elenco sopra).

#### 2. Installa WP All Import

- Plugin → Aggiungi nuovo
- Cerca "WP All Import"
- Installa e attiva

#### 3. Importa

1. WP All Import → Nuovo Import
2. Upload: `articoli_categorizzati_final.csv`
3. Tipo: "Update existing posts"
4. Match By: "Post ID" → colonna "ID"
5. Mappa campi:
   - `Categoria` → Categories (seleziona la categoria principale)
   - `Sottocategoria` → Categories (aggiungi come categoria)
6. Opzione: ✅ "Remove existing categories before adding new ones"
7. Esegui Import

### OPZIONE B: Script PHP Personalizzato

Carica questo file nella root di WordPress come `update_categories.php`:

```php
<?php
require_once('wp-load.php');

$csv_file = 'articoli_categorizzati_final.csv';

if (!file_exists($csv_file)) {
    die("❌ File CSV non trovato!");
}

$handle = fopen($csv_file, 'r');
$header = fgetcsv($handle); // Skip header

$updated = 0;
$errors = 0;
$created_cats = [];

echo "🚀 Inizio importazione categorie...\n\n";

while (($row = fgetcsv($handle)) !== false) {
    $post_id = intval($row[0]);
    $categoria = trim($row[2]);
    $sottocategoria = trim($row[3]);

    if ($post_id <= 0 || $categoria === 'NON_CATEGORIZZATO') {
        continue;
    }

    // Get or create main category
    $cat_term = get_term_by('name', $categoria, 'category');
    if (!$cat_term) {
        $cat_result = wp_insert_term($categoria, 'category');
        if (is_wp_error($cat_result)) {
            echo "❌ Errore creando categoria: $categoria\n";
            $errors++;
            continue;
        }
        $cat_id = $cat_result['term_id'];
        $created_cats[] = $categoria;
        echo "✨ Categoria creata: $categoria\n";
    } else {
        $cat_id = $cat_term->term_id;
    }

    // Get or create subcategory
    $subcat_term = get_term_by('name', $sottocategoria, 'category');
    if (!$subcat_term) {
        $subcat_result = wp_insert_term($sottocategoria, 'category', array('parent' => $cat_id));
        if (is_wp_error($subcat_result)) {
            echo "❌ Errore creando sottocategoria: $sottocategoria\n";
            $errors++;
            continue;
        }
        $subcat_id = $subcat_result['term_id'];
        $created_cats[] = "$categoria → $sottocategoria";
        echo "✨ Sottocategoria creata: $categoria → $sottocategoria\n";
    } else {
        $subcat_id = $subcat_term->term_id;
    }

    // Remove existing categories
    wp_set_post_categories($post_id, array(), false);

    // Set new categories
    $result = wp_set_post_categories($post_id, array($cat_id, $subcat_id), false);

    if ($result) {
        $updated++;
        echo "✅ Post $post_id: $categoria → $sottocategoria\n";
    } else {
        $errors++;
        echo "❌ Errore aggiornando post $post_id\n";
    }
}

fclose($handle);

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 COMPLETATO!\n";
echo str_repeat("=", 50) . "\n\n";
echo "📊 Statistiche:\n";
echo "   • Articoli aggiornati: $updated\n";
echo "   • Errori: $errors\n";
echo "   • Nuove categorie create: " . count(array_unique($created_cats)) . "\n";
?>
```

**Esecuzione:**

```bash
# Carica CSV e script nella root di WordPress
# Poi esegui:
php update_categories.php
```

### OPZIONE C: Manuale (per pochi articoli)

Se hai solo pochi articoli da categorizzare:

1. Apri il CSV in Excel/Google Sheets
2. Per ogni riga, apri l'articolo in WordPress (usando l'ID)
3. Assegna manualmente categoria e sottocategoria
4. Salva

---

## ✅ Verifica Post-Import

1. Vai in **Articoli → Tutti gli articoli**
2. Filtra per categoria
3. Verifica che gli articoli siano correttamente categorizzati
4. Controlla alcune categorie a campione

---

## 🐛 Troubleshooting

### Problema: Articoli NON_CATEGORIZZATO

**Soluzione:**
1. Apri `articoli_categorizzati_final.csv`
2. Cerca righe con "NON_CATEGORIZZATO"
3. Categorizza manualmente questi articoli
4. Re-importa

### Problema: Categorie duplicate

**Soluzione:**
1. In WordPress, vai in Articoli → Categorie
2. Unisci categorie duplicate
3. Elimina duplicati

### Problema: Script PHP non funziona

**Soluzione:**
1. Verifica che `wp-load.php` esista nella root
2. Controlla permessi file
3. Verifica PHP error log
4. Usa WP All Import invece

---

## 📈 Statistiche Attese

Dopo l'importazione, dovresti avere:
- **127 articoli** totali categorizzati
- **8 categorie** principali
- **~30-40 sottocategorie** attive
- Distribuzione equilibrata tra categorie

---

## 💡 Tips & Best Practices

1. **Fai Backup**: Prima di importare, fai backup completo del database
2. **Test su Staging**: Se possibile, testa prima su ambiente di staging
3. **Verifica Batch**: Controlla i primi 2-3 batch prima di fare tutti
4. **Consistenza**: Usa sempre lo stesso LLM per tutti i batch
5. **Review**: Dopo l'import, rivedi manualmente alcune categorie

---

## 📞 Supporto

Se hai problemi:
1. Controlla i log PHP/WordPress
2. Verifica formato CSV
3. Assicurati che i nomi categorie corrispondano esattamente
4. Prova con un batch piccolo (5-10 articoli) prima

---

## 🎊 Completamento

Quando finito:
- [ ] Tutti i batch processati
- [ ] CSV finale generato
- [ ] Categorie create in WordPress
- [ ] Import completato
- [ ] Verifica manuale effettuata
- [ ] Articoli visibili nelle categorie corrette

Congratulazioni! 🎉 Gli articoli sono ora correttamente categorizzati!
