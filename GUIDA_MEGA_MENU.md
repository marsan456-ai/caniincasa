# 📖 Guida Completa Mega Menu System

Sistema flessibile per creare mega menu in WordPress con due modalità: **Colonne Automatiche** e **HTML Personalizzato**.

---

## 📋 Indice

1. [Configurazione Base](#configurazione-base)
2. [Modalità 1: Colonne Automatiche](#modalità-1-colonne-automatiche)
3. [Modalità 2: HTML Personalizzato](#modalità-2-html-personalizzato)
4. [Esempi Pratici](#esempi-pratici)
5. [Personalizzazione Avanzata](#personalizzazione-avanzata)
6. [Mobile Responsive](#mobile-responsive)
7. [Troubleshooting](#troubleshooting)

---

## 🚀 Configurazione Base

### Passo 1: Attivazione
Il sistema è già attivo! I file sono stati inclusi automaticamente.

### Passo 2: Accesso al Menu
1. Vai in **Aspetto → Menu**
2. Seleziona il menu che vuoi modificare (es. "Menu Principale")
3. Clicca su una voce di menu per espanderla

### Passo 3: Opzioni Schermo
Se non vedi i nuovi campi:
1. Clicca su **Opzioni Schermo** (in alto a destra)
2. Assicurati che **"Descrizione"** sia selezionata
3. Salva e ricarica la pagina

---

## 📊 Modalità 1: Colonne Automatiche

**Quando usarla**: Quando hai un menu semplice con sottovoci che vuoi disporre in colonne.

### Configurazione

1. **Seleziona la voce di menu principale** (es. "RAZZE")
2. Nel campo **"Tipo Mega Menu"**, seleziona: **"Colonne automatiche"**
3. Nel campo **"Numero Colonne"**, scegli: **2**, **3** o **4** colonne
4. Aggiungi le **sottovoci** come normali voci di menu
5. Clicca **"Salva menu"**

### Esempio Visivo

```
RAZZE (voce principale con mega menu a 3 colonne)
├─ Per Taglia (Colonna 1)
│  ├─ Razze Toy
│  ├─ Razze Piccole
│  ├─ Razze Medie
│  ├─ Razze Grandi
│  └─ Razze Giganti
├─ Per Carattere (Colonna 2)
│  ├─ Cani da Famiglia
│  ├─ Cani Attivi
│  └─ Cani da Guardia
└─ Strumenti (Colonna 3)
   ├─ Comparatore Razze
   └─ Quiz Compatibilità
```

### Risultato
Le sottovoci saranno automaticamente disposte in 3 colonne quando il menu si apre.

---

## 🎨 Modalità 2: HTML Personalizzato

**Quando usarla**: Quando vuoi massimo controllo sul design, aggiungere immagini, icone, contatori, box speciali, etc.

### Configurazione

1. **Seleziona la voce di menu principale** (es. "RAZZE")
2. Nel campo **"Tipo Mega Menu"**, seleziona: **"HTML personalizzato"**
3. Nel campo **"Descrizione"** (ora visibile), inserisci l'HTML del mega menu
4. Clicca **"Salva menu"**

### Template Base HTML

```html
<div class="mega-menu-content">
    <!-- Sezione 1 -->
    <div class="mega-menu-section">
        <h3><i class="icon">📏</i> Per Taglia</h3>
        <ul>
            <li><a href="/razze/taglia/toy/">Razze Toy <span class="count">12</span></a></li>
            <li><a href="/razze/taglia/piccola/">Razze Piccole <span class="count">45</span></a></li>
            <li><a href="/razze/taglia/media/">Razze Medie <span class="count">78</span></a></li>
            <li><a href="/razze/taglia/grande/">Razze Grandi <span class="count">56</span></a></li>
            <li><a href="/razze/taglia/gigante/">Razze Giganti <span class="count">23</span></a></li>
        </ul>
    </div>

    <!-- Sezione 2 -->
    <div class="mega-menu-section">
        <h3><i class="icon">❤️</i> Per Carattere</h3>
        <ul>
            <li><a href="/razze/carattere/famiglia/">Cani da Famiglia</a></li>
            <li><a href="/razze/carattere/sportivi/">Cani Attivi/Sportivi</a></li>
            <li><a href="/razze/carattere/guardia/">Cani da Guardia</a></li>
            <li><a href="/razze/carattere/compagnia/">Cani da Compagnia</a></li>
        </ul>
    </div>

    <!-- Sezione 3 -->
    <div class="mega-menu-section">
        <h3><i class="icon">🔧</i> Strumenti</h3>
        <ul>
            <li><a href="/comparatore-razze/">Comparatore Razze <span class="mega-menu-badge new">Nuovo</span></a></li>
            <li><a href="/quiz-razza/">Quiz Compatibilità</a></li>
            <li><a href="/razze-di-cani/">Tutte le Razze A-Z</a></li>
        </ul>
    </div>

    <!-- Box in Evidenza -->
    <div class="mega-menu-featured">
        <h4>Razza in Evidenza</h4>
        <div class="featured-breed">
            <img src="URL_IMMAGINE_RAZZA" alt="Golden Retriever">
            <h5>Golden Retriever</h5>
            <p>Il Golden Retriever è un cane affettuoso, intelligente e perfetto per le famiglie...</p>
            <a href="/razze/golden-retriever/" class="btn">Scopri di più</a>
        </div>
    </div>
</div>
```

---

## 💡 Esempi Pratici

### Esempio 1: Menu RAZZE (Raccomandato per il brief)

Usa l'helper PHP per generare automaticamente il mega menu con dati dinamici:

1. Nella voce menu "RAZZE", seleziona **HTML personalizzato**
2. Nel campo Descrizione, inserisci semplicemente:

```html
[razze_mega_menu]
```

Questo shortcode genera automaticamente:
- Sezione "Per Taglia" con tutte le taglie e contatori
- Sezione "Gruppi FCI" con i primi 5 gruppi
- Sezione "Strumenti" con link a Comparatore e Quiz
- Box "Razza in Evidenza" con una razza casuale

### Esempio 2: Menu GUIDA CANI

```html
<div class="mega-menu-content">
    <div class="mega-menu-section">
        <h3><i class="icon">🐕</i> Primo Cane</h3>
        <ul>
            <li><a href="/guida/come-scegliere/">Come Scegliere</a></li>
            <li><a href="/guida/preparare-casa/">Preparare la Casa</a></li>
            <li><a href="/guida/primi-giorni/">Primi Giorni</a></li>
            <li><a href="/guida/attrezzatura/">Attrezzatura Necessaria</a></li>
        </ul>
    </div>

    <div class="mega-menu-section">
        <h3><i class="icon">💊</i> Salute & Benessere</h3>
        <ul>
            <li><a href="/guida/vaccinazioni/">Vaccinazioni</a></li>
            <li><a href="/guida/parassiti/">Parassiti</a></li>
            <li><a href="/guida/malattie/">Malattie Comuni</a></li>
            <li><a href="/guida/veterinario/">Quando Andare dal Vet</a></li>
        </ul>
    </div>

    <div class="mega-menu-section">
        <h3><i class="icon">🎓</i> Educazione</h3>
        <ul>
            <li><a href="/guida/comandi-base/">Comandi Base</a></li>
            <li><a href="/guida/socializzazione/">Socializzazione</a></li>
            <li><a href="/guida/problemi/">Problemi Comportamentali</a></li>
        </ul>
    </div>

    <div class="mega-menu-section">
        <h3><i class="icon">🏠</i> Vita Quotidiana</h3>
        <ul>
            <li><a href="/guida/alimentazione/">Alimentazione</a></li>
            <li><a href="/guida/toelettatura/">Toelettatura</a></li>
            <li><a href="/guida/esercizio/">Esercizio Fisico</a></li>
            <li><a href="/guida/viaggi/">Viaggiare col Cane</a></li>
        </ul>
    </div>
</div>
```

### Esempio 3: Menu STRUMENTI

```html
<div class="mega-menu-content">
    <div class="mega-menu-section">
        <h3><i class="icon">🧮</i> Calcolatori</h3>
        <ul>
            <li><a href="/calcolatore-eta/">Età Umana</a></li>
            <li><a href="/calcolatore-peso/">Peso Ideale</a></li>
            <li><a href="/calcolatore-costo/">Costo Mantenimento</a></li>
            <li><a href="/calcolatore-cibo/">Quantità Cibo</a></li>
        </ul>
    </div>

    <div class="mega-menu-section">
        <h3><i class="icon">⚖️</i> Comparatori</h3>
        <ul>
            <li><a href="/comparatore-razze/">Comparatore Razze <span class="mega-menu-badge new">Nuovo</span></a></li>
            <li><a href="/quiz-razza/">Quiz Compatibilità <span class="mega-menu-badge popular">Popolare</span></a></li>
        </ul>
    </div>

    <div class="mega-menu-section">
        <h3><i class="icon">📚</i> Directory</h3>
        <ul>
            <li><a href="/allevamenti/">Allevamenti</a></li>
            <li><a href="/veterinari/">Veterinari</a></li>
            <li><a href="/toelettatori/">Toelettatori</a></li>
            <li><a href="/addestratori/">Addestratori</a></li>
        </ul>
    </div>
</div>
```

---

## 🎨 Personalizzazione Avanzata

### Elementi Disponibili

#### 1. Contatori
Aggiungi badge con numeri:
```html
<a href="/link/">Testo <span class="count">42</span></a>
```

#### 2. Badge "Nuovo" / "Popolare"
```html
<a href="/link/">Testo <span class="mega-menu-badge new">Nuovo</span></a>
<a href="/link/">Testo <span class="mega-menu-badge popular">Popolare</span></a>
<a href="/link/">Testo <span class="mega-menu-badge">Custom</span></a>
```

#### 3. Voci in Evidenza
```html
<li class="highlight">
    <a href="/link/">Voce Speciale</a>
</li>
```

#### 4. Divisori
```html
<div class="mega-menu-divider"></div>
```

#### 5. Box Featured (con immagine)
```html
<div class="mega-menu-featured">
    <h4>Titolo Box</h4>
    <div class="featured-breed">
        <img src="URL_IMMAGINE" alt="Alt Text">
        <h5>Nome</h5>
        <p>Descrizione breve...</p>
        <a href="/link/" class="btn">Call to Action</a>
    </div>
</div>
```

### Icone Disponibili (Emoji)

Alcune icone consigliate per le sezioni:

- **Razze**: 🐕 🐶 🦮 🐩
- **Taglia**: 📏 📐 ⚖️
- **Carattere**: ❤️ 💕 😊 🎯
- **Salute**: 💊 🏥 💉 🩺
- **Educazione**: 🎓 📚 🎯 ✨
- **Strumenti**: 🔧 🧮 ⚙️ 🛠️
- **Italia**: 🇮🇹
- **Casa**: 🏠 🏡
- **Cibo**: 🍖 🦴 🥩

---

## 📱 Mobile Responsive

Il mega menu è automaticamente responsive:

### Desktop (> 1024px)
- Dropdown al hover
- Layout a colonne/grid
- Animazioni smooth

### Tablet/Mobile (≤ 1024px)
- Accordion (click per aprire/chiudere)
- Una colonna verticale
- Touch-friendly
- Smooth scroll alla sezione

### Comportamento Mobile
- **Primo click**: Apre il mega menu (non naviga)
- **Secondo click**: Naviga al link
- **Click fuori**: Chiude il mega menu

---

## 🐛 Troubleshooting

### Il mega menu non si vede

**Soluzione 1**: Verifica che la classe CSS sia applicata
1. Ispeziona l'elemento nel browser
2. Verifica che la voce di menu abbia la classe `mega-menu-X-cols` o `mega-menu-custom`

**Soluzione 2**: Cache
1. Svuota la cache del browser (Ctrl+F5)
2. Svuota la cache di WordPress (se usi un plugin di cache)

### L'HTML personalizzato non funziona

**Soluzione**: Verifica il campo Descrizione
1. Assicurati che **Opzioni Schermo → Descrizione** sia selezionata
2. L'HTML deve essere inserito nel campo **"Descrizione"** della voce di menu
3. La voce deve avere **Tipo Mega Menu = "HTML personalizzato"**

### Le colonne sono sbagliate

**Soluzione**: Struttura menu
- Le **sottovoci di primo livello** diventano le colonne
- Le **sottovoci di secondo livello** vanno sotto la colonna parent
- Esempio corretto:
  ```
  RAZZE (mega-menu-3-cols)
  ├─ Colonna 1 (sottvoce livello 1)
  │  ├─ Link A (sottvoce livello 2)
  │  └─ Link B (sottvoce livello 2)
  ├─ Colonna 2 (sottvoce livello 1)
  └─ Colonna 3 (sottvoce livello 1)
  ```

### Mobile: il menu non si apre

**Soluzione**: JavaScript
1. Verifica che jQuery sia caricato
2. Controlla la console del browser per errori JavaScript
3. Assicurati che il file `mega-menu.js` sia caricato

### Shortcode non funziona

**Soluzione**: Shortcode WordPress
- Gli shortcode funzionano **solo nel campo Descrizione**
- Sintassi corretta: `[razze_mega_menu]`
- Se non funziona, usa l'HTML diretto invece dello shortcode

---

## 🎯 Best Practices

### 1. Non Abusare
- Usa mega menu solo per le voci principali (max 2-3 voci)
- Troppi mega menu confondono l'utente

### 2. Mantieni Semplice
- Max 4-5 link per sezione
- Max 3-4 sezioni per mega menu
- Usa etichette chiare e brevi

### 3. Icone Coerenti
- Usa lo stesso stile di icone (emoji o SVG, non mischiarli)
- Una icona per sezione, non per ogni link

### 4. Mobile First
- Testa sempre su mobile
- Verifica che il menu sia usabile su touch screen
- Evita troppi livelli di profondità

### 5. Performance
- Ottimizza le immagini nel box featured
- Non inserire troppi elementi pesanti
- Usa lazy loading per immagini

---

## 📞 Supporto

Per problemi o domande:
1. Controlla questa guida
2. Verifica la console del browser per errori
3. Controlla che i file CSS e JS siano caricati correttamente

---

## 🔄 Aggiornamenti Futuri

Funzionalità pianificate:
- [ ] Generatore visuale mega menu (drag & drop)
- [ ] Template predefiniti salvabili
- [ ] Statistiche click mega menu
- [ ] A/B testing layout
- [ ] Integrazione con page builder

---

**Versione**: 1.0.0
**Ultimo aggiornamento**: Novembre 2024
**Compatibilità**: WordPress 5.0+, PHP 7.4+
