#!/usr/bin/env python3
"""
Script per unire i risultati dei batch e creare il CSV finale
"""

import csv
import glob
import re

def parse_batch_result(filename):
    """Parsifica un singolo file di risultati batch"""
    categorizations = {}

    with open(filename, 'r', encoding='utf-8') as f:
        content = f.read()

    # Pattern: [ID] | Categoria: [Nome] | Sottocategoria: [Nome]
    # O anche: ID XXX | Categoria: ... | Sottocategoria: ...
    patterns = [
        r'\[?(\d+)\]?\s*\|\s*Categoria:\s*([^\|]+?)\s*\|\s*Sottocategoria:\s*(.+?)(?:\n|$)',
        r'ID\s+(\d+)\s*\|\s*Categoria:\s*([^\|]+?)\s*\|\s*Sottocategoria:\s*(.+?)(?:\n|$)',
    ]

    for pattern in patterns:
        matches = re.finditer(pattern, content, re.MULTILINE | re.IGNORECASE)
        for match in matches:
            article_id = match.group(1).strip()
            categoria = match.group(2).strip()
            sottocategoria = match.group(3).strip()

            categorizations[article_id] = {
                'categoria': categoria,
                'sottocategoria': sottocategoria
            }

    return categorizations

def read_articles(filename):
    """Legge articoli originali"""
    articles = []
    with open(filename, 'r', encoding='utf-8-sig') as f:
        reader = csv.DictReader(f)
        for row in reader:
            articles.append({
                'id': row['ID'],
                'title': row['Title']
            })
    return articles

def merge_all_results():
    """Unisce tutti i file batch_XX_results.txt"""
    result_files = sorted(glob.glob('batch_*_results.txt'))

    if not result_files:
        print("❌ Nessun file batch_XX_results.txt trovato!")
        print("Assicurati di aver salvato le risposte dell'AI con questo formato:")
        print("  batch_01_results.txt, batch_02_results.txt, etc.")
        return None

    print(f"📂 Trovati {len(result_files)} file di risultati\n")

    all_categorizations = {}

    for result_file in result_files:
        print(f"📄 Elaborazione {result_file}...")
        cats = parse_batch_result(result_file)
        all_categorizations.update(cats)
        print(f"   ✓ {len(cats)} articoli categorizzati")

    print(f"\n📊 Totale articoli categorizzati: {len(all_categorizations)}")
    return all_categorizations

def generate_final_csv(articles, categorizations, output_file):
    """Genera CSV finale"""
    with open(output_file, 'w', encoding='utf-8', newline='') as f:
        fieldnames = ['ID', 'Title', 'Categoria', 'Sottocategoria']
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()

        missing_ids = []

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
                # Articolo non categorizzato
                writer.writerow({
                    'ID': article_id,
                    'Title': article['title'],
                    'Categoria': 'NON_CATEGORIZZATO',
                    'Sottocategoria': 'NON_CATEGORIZZATO'
                })
                missing_ids.append((article_id, article['title']))

    print(f"\n✅ CSV finale creato: {output_file}")
    print(f"   • Articoli totali: {len(articles)}")
    print(f"   • Articoli categorizzati: {len(articles) - len(missing_ids)}")

    if missing_ids:
        print(f"   ⚠ Articoli NON categorizzati: {len(missing_ids)}")
        print(f"\nArticoli mancanti:")
        for article_id, title in missing_ids[:10]:  # Show first 10
            print(f"   • ID {article_id}: {title[:60]}...")
        if len(missing_ids) > 10:
            print(f"   ... e altri {len(missing_ids) - 10}")

def generate_category_stats(categorizations):
    """Genera statistiche categorie"""
    cat_counts = {}
    subcat_counts = {}

    for cat_info in categorizations.values():
        cat = cat_info['categoria']
        subcat = cat_info['sottocategoria']

        cat_counts[cat] = cat_counts.get(cat, 0) + 1
        key = f"{cat} → {subcat}"
        subcat_counts[key] = subcat_counts.get(key, 0) + 1

    print("\n📈 STATISTICHE CATEGORIE\n")

    print("Categorie principali:")
    for cat, count in sorted(cat_counts.items(), key=lambda x: x[1], reverse=True):
        print(f"   • {cat}: {count} articoli")

    print("\n Sottocategorie più usate:")
    sorted_subcats = sorted(subcat_counts.items(), key=lambda x: x[1], reverse=True)[:15]
    for subcat, count in sorted_subcats:
        print(f"   • {subcat}: {count}")

def main():
    import_csv = 'Articoli-Export-2025-November-21-0711.csv'
    final_csv = 'articoli_categorizzati_final.csv'

    print("🔄 Unione risultati batch...\n")

    categorizations = merge_all_results()

    if not categorizations:
        return

    articles = read_articles(import_csv)
    generate_final_csv(articles, categorizations, final_csv)
    generate_category_stats(categorizations)

    print("\n" + "="*50)
    print("🎉 COMPLETATO!")
    print("="*50)
    print(f"\nFile generato: {final_csv}")
    print("\nPROSSIMI PASSI:")
    print("1. Controlla il CSV generato")
    print("2. Se ci sono articoli NON_CATEGORIZZATO, categorizzali manualmente")
    print("3. Importa in WordPress usando WP All Import o lo script PHP")
    print("4. Leggi 'ISTRUZIONI_IMPORT_WORDPRESS.md' per la guida completa")

if __name__ == '__main__':
    main()
