#!/usr/bin/env python3
"""
Script per preparare gli articoli per la categorizzazione AI

Questo script:
1. Legge il CSV degli articoli
2. Genera un file di testo formattato per l'AI
3. Dopo la categorizzazione AI, ricostruisce il CSV finale

Uso:
  python prepare_for_ai_categorization.py
"""

import csv
import sys
import json

def read_articles_csv(filename):
    """Legge il CSV degli articoli"""
    articles = []
    with open(filename, 'r', encoding='utf-8-sig') as f:
        reader = csv.DictReader(f)
        for row in reader:
            articles.append({
                'id': row['ID'],
                'title': row['Title']
            })
    return articles

def generate_ai_input(articles, output_file):
    """Genera file di input per AI con tutti i titoli"""
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write("# ARTICOLI DA CATEGORIZZARE\n\n")
        f.write("Per ogni articolo, rispondi nel formato:\n")
        f.write("```\n")
        f.write("[ID] | Categoria: [Nome] | Sottocategoria: [Nome]\n")
        f.write("```\n\n")
        f.write("---\n\n")

        for article in articles:
            f.write(f"**ID {article['id']}**\n")
            f.write(f"Titolo: {article['title']}\n\n")

    print(f"✓ Generato file per AI: {output_file}")
    print(f"✓ Articoli da categorizzare: {len(articles)}")
    print(f"\nPROSSIMI PASSI:")
    print(f"1. Apri il file '{output_file}'")
    print(f"2. Copia il contenuto")
    print(f"3. Incollalo in ChatGPT/Claude con il prompt da 'categorize_articles_prompt.md'")
    print(f"4. Copia la risposta dell'AI in un file chiamato 'ai_categorization_output.txt'")
    print(f"5. Esegui: python prepare_for_ai_categorization.py --process")

def parse_ai_output(ai_output_file):
    """Parsifica l'output dell'AI"""
    categorizations = {}

    with open(ai_output_file, 'r', encoding='utf-8') as f:
        content = f.read()

    # Pattern: [ID] | Categoria: [Nome] | Sottocategoria: [Nome]
    lines = content.split('\n')

    for line in lines:
        line = line.strip()
        if '|' in line and 'Categoria:' in line:
            try:
                parts = line.split('|')
                id_part = parts[0].strip()
                cat_part = parts[1].strip()
                subcat_part = parts[2].strip()

                # Estrai ID
                article_id = id_part.replace('[', '').replace(']', '').strip()

                # Estrai categoria
                categoria = cat_part.replace('Categoria:', '').strip()

                # Estrai sottocategoria
                sottocategoria = subcat_part.replace('Sottocategoria:', '').strip()

                categorizations[article_id] = {
                    'categoria': categoria,
                    'sottocategoria': sottocategoria
                }
            except:
                continue

    return categorizations

def generate_final_csv(articles, categorizations, output_file):
    """Genera CSV finale con categorie"""
    with open(output_file, 'w', encoding='utf-8', newline='') as f:
        fieldnames = ['ID', 'Title', 'Categoria', 'Sottocategoria']
        writer = csv.DictWriter(f, fieldnames=fieldnames)

        writer.writeheader()

        missing_count = 0
        for article in articles:
            article_id = article['id']

            if article_id in categorizations:
                cat_info = categorizations[article_id]
                writer.writerow({
                    'ID': article_id,
                    'Title': article['title'],
                    'Categoria': cat_info['categoria'],
                    'Sottocategoria': cat_info['sottocategoria']
                })
            else:
                # Articolo senza categorizzazione
                writer.writerow({
                    'ID': article_id,
                    'Title': article['title'],
                    'Categoria': 'NON_CATEGORIZZATO',
                    'Sottocategoria': 'NON_CATEGORIZZATO'
                })
                missing_count += 1
                print(f"⚠ ID {article_id} non categorizzato: {article['title'][:50]}...")

    print(f"\n✓ CSV finale generato: {output_file}")
    print(f"✓ Articoli categorizzati: {len(articles) - missing_count}/{len(articles)}")

    if missing_count > 0:
        print(f"⚠ {missing_count} articoli non hanno categorizzazione")

def generate_wordpress_import_instructions(output_file):
    """Genera istruzioni per importare in WordPress"""
    instructions = """
# ISTRUZIONI PER IMPORTARE LE CATEGORIE IN WORDPRESS

## Metodo 1: Plugin WP All Import (CONSIGLIATO)

1. **Crea le Categorie in WordPress**
   - Vai in Articoli → Categorie
   - Crea tutte le categorie principali (usa nomi esatti dal CSV)
   - Per ogni categoria principale, crea le sottocategorie

2. **Installa WP All Import**
   - Plugin → Aggiungi nuovo
   - Cerca "WP All Import"
   - Installa e attiva

3. **Importa il CSV**
   - WP All Import → Nuovo Import
   - Upload CSV: `articoli_categorizzati_final.csv`
   - Seleziona: "Update existing posts"
   - Match per: ID

4. **Mappa i Campi**
   - ID → Post ID
   - Categoria → Categories (Main Category)
   - Sottocategoria → Categories (Sub Category)

5. **Esegui Import**
   - Conferma e importa
   - Verifica che gli articoli siano stati aggiornati

---

## Metodo 2: Script PHP Personalizzato

Crea un file `update_categories.php` nella root di WordPress:

```php
<?php
require_once('wp-load.php');

$csv_file = 'articoli_categorizzati_final.csv';

if (!file_exists($csv_file)) {
    die("File CSV non trovato!");
}

$handle = fopen($csv_file, 'r');
$header = fgetcsv($handle); // Skip header

$updated = 0;
$errors = 0;

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
        $cat_id = wp_insert_term($categoria, 'category');
        if (is_wp_error($cat_id)) {
            echo "Errore creando categoria: $categoria\\n";
            $errors++;
            continue;
        }
        $cat_id = $cat_id['term_id'];
    } else {
        $cat_id = $cat_term->term_id;
    }

    // Get or create subcategory
    $subcat_term = get_term_by('name', $sottocategoria, 'category');
    if (!$subcat_term) {
        $subcat_id = wp_insert_term($sottocategoria, 'category', array('parent' => $cat_id));
        if (is_wp_error($subcat_id)) {
            echo "Errore creando sottocategoria: $sottocategoria\\n";
            $errors++;
            continue;
        }
        $subcat_id = $subcat_id['term_id'];
    } else {
        $subcat_id = $subcat_term->term_id;
    }

    // Update post categories
    $result = wp_set_post_categories($post_id, array($cat_id, $subcat_id), false);

    if ($result) {
        $updated++;
        echo "✓ Post $post_id aggiornato: $categoria → $sottocategoria\\n";
    } else {
        $errors++;
        echo "✗ Errore aggiornando post $post_id\\n";
    }
}

fclose($handle);

echo "\\n=== COMPLETATO ===\\n";
echo "Articoli aggiornati: $updated\\n";
echo "Errori: $errors\\n";
?>
```

**Esecuzione:**
```bash
php update_categories.php
```

---

## Metodo 3: SQL Diretto (PER ESPERTI)

**ATTENZIONE: Fai backup del database prima!**

Questo metodo richiede conoscenza di SQL e della struttura database WordPress.

---

## Verifica Post-Import

1. Vai in WordPress Admin
2. Articoli → Tutti gli articoli
3. Verifica che le categorie siano assegnate correttamente
4. Controlla alcune categorie per verificare gli articoli

---

## Note Importanti

- **Backup**: Fai sempre backup del database prima di importare
- **Test**: Prova prima su un ambiente di staging
- **Categorie**: Assicurati che i nomi delle categorie nel CSV corrispondano esattamente a quelli in WordPress
- **Slug**: WordPress genera gli slug automaticamente, non preoccupartene

"""

    with open(output_file, 'w', encoding='utf-8') as f:
        f.write(instructions)

    print(f"✓ Istruzioni generate: {output_file}")

def main():
    import_csv = 'Articoli-Export-2025-November-21-0711.csv'
    ai_input_file = 'articoli_per_ai.txt'
    ai_output_file = 'ai_categorization_output.txt'
    final_csv = 'articoli_categorizzati_final.csv'
    instructions_file = 'ISTRUZIONI_IMPORT_WORDPRESS.md'

    if len(sys.argv) > 1 and sys.argv[1] == '--process':
        # Processa l'output dell'AI e genera CSV finale
        print("📊 Elaborazione output AI...")

        articles = read_articles_csv(import_csv)
        categorizations = parse_ai_output(ai_output_file)
        generate_final_csv(articles, categorizations, final_csv)
        generate_wordpress_import_instructions(instructions_file)

        print("\n🎉 COMPLETATO!")
        print(f"\nFile generati:")
        print(f"  • {final_csv} - CSV con categorie per WordPress")
        print(f"  • {instructions_file} - Guida all'importazione")

    else:
        # Genera file per AI
        print("🔧 Preparazione articoli per categorizzazione AI...")

        articles = read_articles_csv(import_csv)
        generate_ai_input(articles, ai_input_file)

if __name__ == '__main__':
    main()
