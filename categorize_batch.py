#!/usr/bin/env python3
"""
Script semplificato per categorizzazione batch

Genera file batch di 10 articoli alla volta per facilitare il lavoro con l'AI
"""

import csv

def read_articles(filename):
    articles = []
    with open(filename, 'r', encoding='utf-8-sig') as f:
        reader = csv.DictReader(f)
        for row in reader:
            articles.append({
                'id': row['ID'],
                'title': row['Title']
            })
    return articles

def generate_batches(articles, batch_size=10):
    """Genera file batch separati"""
    total_batches = (len(articles) + batch_size - 1) // batch_size

    for batch_num in range(total_batches):
        start_idx = batch_num * batch_size
        end_idx = min((batch_num + 1) * batch_size, len(articles))
        batch_articles = articles[start_idx:end_idx]

        filename = f'batch_{batch_num + 1:02d}_of_{total_batches:02d}.txt'

        with open(filename, 'w', encoding='utf-8') as f:
            f.write(f"# BATCH {batch_num + 1} di {total_batches}\n")
            f.write(f"# Articoli {start_idx + 1}-{end_idx} di {len(articles)}\n\n")

            f.write("Categorizza questi articoli usando il formato:\n")
            f.write("[ID] | Categoria: [Nome] | Sottocategoria: [Nome]\n\n")
            f.write("---\n\n")

            for article in batch_articles:
                f.write(f"ID {article['id']}: {article['title']}\n\n")

        print(f"✓ Creato {filename} ({len(batch_articles)} articoli)")

    print(f"\n📊 Totale batch creati: {total_batches}")
    print(f"📄 Articoli per batch: {batch_size}")
    print(f"\n💡 COME PROCEDERE:")
    print(f"1. Apri 'categorize_articles_prompt.md' - questo è il prompt per l'AI")
    print(f"2. Per ogni batch file (batch_01_of_XX.txt, batch_02_of_XX.txt, etc.):")
    print(f"   a. Copia il contenuto del prompt")
    print(f"   b. Aggiungi il contenuto del batch")
    print(f"   c. Incolla in ChatGPT/Claude")
    print(f"   d. Copia la risposta in 'batch_XX_results.txt'")
    print(f"3. Quando finito, esegui: python merge_results.py")

def main():
    import_csv = 'Articoli-Export-2025-November-21-0711.csv'
    articles = read_articles(import_csv)

    print(f"📚 Articoli trovati: {len(articles)}\n")
    generate_batches(articles, batch_size=10)

if __name__ == '__main__':
    main()
