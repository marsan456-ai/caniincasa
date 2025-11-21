# Paw Stars - Plugin per CaniInCasa

Plugin WordPress per la gestione di profili social dei cani con sistema di voto e classifiche.

## Descrizione

Paw Stars è un sistema social/gamificato dove i proprietari di cani possono:
- Creare profili per i propri cani con foto e informazioni
- Ricevere voti dalla community con diverse reazioni
- Competere nelle classifiche settimanali e all-time
- Guadagnare badge e achievements

## Funzionalità Principali

### Per gli Utenti
- **Profili Cani**: Crea fino a 5 profili per i tuoi cani
- **Galleria Foto**: Carica fino a 10 foto per profilo
- **Reazioni**: Ricevi voti con 5 tipi di reazioni (❤️ Love, 😍 Adorable, ⭐ Star, 😄 Funny, 🥺 Aww)
- **Classifiche**: Competi nelle classifiche Hot Dogs (7 giorni) e All Stars (all-time)
- **Badge**: Guadagna badge speciali (Rising Star, Popular, Legendary, ecc.)

### Per gli Admin
- **Dashboard**: Visualizza statistiche generali
- **Moderazione**: Approva/rifiuta profili in coda
- **Impostazioni**: Configura limiti voti, punti per reazione, giorni Hot Dogs

## Shortcodes

```
[pawstars_feed]           - Feed principale con tutti i cani
[pawstars_leaderboard]    - Classifica completa
[pawstars_profile id="X"] - Profilo singolo cane
[pawstars_create]         - Form creazione profilo
```

### Parametri Feed
- `breed`: ID razza per filtrare
- `provincia`: Codice provincia (es. "MI", "RM")
- `orderby`: Ordinamento (popular, recent, random)
- `limit`: Numero di cani per pagina

### Parametri Leaderboard
- `type`: Tipo classifica (hot, alltime, breed, provincia)
- `limit`: Numero di posizioni
- `filter`: Filtro aggiuntivo (breed_id o codice provincia)

## REST API

### Endpoints Pubblici
- `GET /wp-json/pawstars/v1/dogs` - Lista cani
- `GET /wp-json/pawstars/v1/dogs/{id}` - Singolo cane
- `GET /wp-json/pawstars/v1/leaderboard/{type}` - Classifica
- `GET /wp-json/pawstars/v1/breeds` - Lista razze
- `GET /wp-json/pawstars/v1/province` - Lista province
- `GET /wp-json/pawstars/v1/stats` - Statistiche globali

### Endpoints Autenticati
- `POST /wp-json/pawstars/v1/dogs` - Crea profilo
- `PUT /wp-json/pawstars/v1/dogs/{id}` - Aggiorna profilo
- `DELETE /wp-json/pawstars/v1/dogs/{id}` - Elimina profilo
- `POST /wp-json/pawstars/v1/vote` - Aggiungi voto
- `GET /wp-json/pawstars/v1/my-dogs` - I miei cani

## Sistema Punti

| Reazione | Punti | Limite Giornaliero |
|----------|-------|-------------------|
| ❤️ Love | 5 | Illimitato |
| 😍 Adorable | 3 | Illimitato |
| ⭐ Star | 10 | 1 per utente |
| 😄 Funny | 2 | Illimitato |
| 🥺 Aww | 2 | Illimitato |

## Badge Disponibili

- **Starter**: Primo profilo creato
- **Social Pup**: 10 voti ricevuti
- **Popular**: 50 voti ricevuti
- **Rising Star**: Top 10 Hot Dogs
- **Superstar**: Top 3 Hot Dogs
- **Champion**: #1 Hot Dogs
- **Legend**: 500 voti totali
- **Veteran**: Profilo attivo da 365 giorni
- **Photogenic**: 5+ foto in galleria
- **Community Star**: 100 voti dati ad altri

## Tabelle Database

- `wp_pawstars_dogs` - Profili cani
- `wp_pawstars_votes` - Voti
- `wp_pawstars_achievements` - Badge guadagnati
- `wp_pawstars_challenges` - Sfide (futuro)
- `wp_pawstars_follows` - Seguiti (futuro)
- `wp_pawstars_daily_stats` - Statistiche giornaliere

## Integrazione Theme

Il plugin si integra con:
- CPT `razze_di_cani` per la selezione della razza
- Dashboard utente esistente (tab Paw Stars)
- Sistema di upload media di WordPress

## Requisiti

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+

## Installazione

1. Carica la cartella `caniincasa-pawstars` in `/wp-content/plugins/`
2. Attiva il plugin dal menu Plugin
3. Vai su Paw Stars > Impostazioni per configurare
4. Crea le pagine con gli shortcode necessari

## Disinstallazione

Il plugin può essere configurato per:
- Mantenere i dati alla disinstallazione (default)
- Eliminare tutti i dati (attiva "Elimina dati alla disinstallazione" nelle impostazioni)

## Changelog

### 1.0.0
- Rilascio iniziale
- Sistema profili cani con foto
- Sistema voti con 5 reazioni
- Classifiche Hot Dogs e All Stars
- Filtri per razza e provincia
- Sistema badge
- REST API completa
- Area admin con moderazione
- Mobile-first con swipe cards

## Crediti

Sviluppato per CaniInCasa.it

## Licenza

Proprietario - Tutti i diritti riservati
