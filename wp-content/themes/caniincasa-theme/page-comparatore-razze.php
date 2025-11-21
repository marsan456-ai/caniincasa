<?php
/**
 * Template Name: Comparatore Razze
 *
 * Template per confrontare fino a 3 razze di cani
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="main-content" class="site-main comparatore-razze">

    <!-- Archive Header -->
    <div class="archive-header">
        <div class="container">
            <h1 class="archive-title">Comparatore Razze</h1>
            <p class="archive-description">Confronta fino a 3 razze di cani per trovare quella più adatta a te</p>
        </div>
    </div>

    <!-- Breadcrumbs -->
    <div class="container">
        <div class="breadcrumbs-wrapper">
            <?php caniincasa_breadcrumbs(); ?>
        </div>
    </div>

    <div class="container">

        <!-- Selezione Razze -->
        <div class="razze-selector">
            <h2>Seleziona le Razze da Confrontare</h2>
            <p class="selector-hint">Scegli fino a 3 razze per visualizzare il confronto dettagliato</p>

            <div class="selector-inputs">
                <div class="selector-slot" data-slot="1">
                    <label for="razza-1">Razza 1</label>
                    <input type="text"
                           id="razza-1"
                           class="razza-search"
                           placeholder="Cerca una razza..."
                           autocomplete="off">
                    <input type="hidden" id="razza-id-1" class="razza-id">
                    <button type="button" class="clear-selection" data-slot="1" style="display: none;">×</button>
                </div>

                <div class="selector-slot" data-slot="2">
                    <label for="razza-2">Razza 2</label>
                    <input type="text"
                           id="razza-2"
                           class="razza-search"
                           placeholder="Cerca una razza..."
                           autocomplete="off">
                    <input type="hidden" id="razza-id-2" class="razza-id">
                    <button type="button" class="clear-selection" data-slot="2" style="display: none;">×</button>
                </div>

                <div class="selector-slot" data-slot="3">
                    <label for="razza-3">Razza 3 (opzionale)</label>
                    <input type="text"
                           id="razza-3"
                           class="razza-search"
                           placeholder="Cerca una razza..."
                           autocomplete="off">
                    <input type="hidden" id="razza-id-3" class="razza-id">
                    <button type="button" class="clear-selection" data-slot="3" style="display: none;">×</button>
                </div>
            </div>

            <div class="selector-actions">
                <button type="button" id="compare-btn" class="btn btn-primary btn-large" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                    </svg>
                    Confronta Razze
                </button>
                <button type="button" id="reset-btn" class="btn btn-secondary">
                    Ricomincia
                </button>
            </div>
        </div>

        <!-- Tabella Comparativa -->
        <div id="comparison-table" class="comparison-wrapper" style="display: none;">

            <!-- Mobile Navigation -->
            <div class="mobile-nav">
                <button type="button" class="mobile-nav-btn" id="prev-breed">‹</button>
                <span class="mobile-nav-indicator">
                    <span id="current-breed">1</span> / <span id="total-breeds">2</span>
                </span>
                <button type="button" class="mobile-nav-btn" id="next-breed">›</button>
            </div>

            <!-- Comparison Header (Images & Basic Info) -->
            <div class="comparison-header">
                <div class="comparison-labels">
                    <div class="label-cell"></div>
                </div>
                <div class="comparison-breeds" id="comparison-breeds-header">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Comparison Body (Data) -->
            <div class="comparison-body">

                <!-- Fisici -->
                <div class="comparison-section">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/>
                        </svg>
                        Caratteristiche Fisiche
                    </h3>
                    <div class="comparison-row" data-field="taglia">
                        <div class="row-label">Taglia</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="peso">
                        <div class="row-label">Peso</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="aspettativa_vita">
                        <div class="row-label">Aspettativa di vita</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="tipo_pelo">
                        <div class="row-label">Colorazioni</div>
                        <div class="row-values"></div>
                    </div>
                </div>

                <!-- Caratteriali -->
                <div class="comparison-section">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                            <line x1="9" y1="9" x2="9.01" y2="9"/>
                            <line x1="15" y1="9" x2="15.01" y2="9"/>
                        </svg>
                        Carattere e Temperamento
                    </h3>
                    <div class="comparison-row" data-field="affettuosita">
                        <div class="row-label">Affettuosità</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="energia">
                        <div class="row-label">Livello di energia</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="socialita">
                        <div class="row-label">Socialità</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="addestrabilita">
                        <div class="row-label">Addestrabilità</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="territorialita">
                        <div class="row-label">Intelligenza</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="tendenza_abbaiare">
                        <div class="row-label">Tendenza ad abbaiare</div>
                        <div class="row-values"></div>
                    </div>
                </div>

                <!-- Cure e Mantenimento -->
                <div class="comparison-section">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                        </svg>
                        Cure e Mantenimento
                    </h3>
                    <div class="comparison-row" data-field="toelettatura">
                        <div class="row-label">Facilità toelettatura</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="perdita_pelo">
                        <div class="row-label">Cura e perdita pelo</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="esercizio_fisico">
                        <div class="row-label">Esigenze di esercizio</div>
                        <div class="row-values"></div>
                    </div>
                </div>

                <!-- Ambiente Ideale -->
                <div class="comparison-section">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        Ambiente Ideale
                    </h3>
                    <div class="comparison-row" data-field="adattabilita_appartamento">
                        <div class="row-label">Adatto all'appartamento</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="tolleranza_solitudine">
                        <div class="row-label">Tolleranza alla solitudine</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="tolleranza_caldo">
                        <div class="row-label">Tolleranza al caldo</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="tolleranza_freddo">
                        <div class="row-label">Tolleranza al freddo</div>
                        <div class="row-values"></div>
                    </div>
                </div>

                <!-- Famiglia -->
                <div class="comparison-section">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                            <path d="M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                        Famiglia e Convivenza
                    </h3>
                    <div class="comparison-row" data-field="compatibilita_bambini">
                        <div class="row-label">Con bambini</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="compatibilita_cani">
                        <div class="row-label">Con altri cani</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="compatibilita_gatti">
                        <div class="row-label">Con altri animali</div>
                        <div class="row-values"></div>
                    </div>
                    <div class="comparison-row" data-field="adatto_principianti">
                        <div class="row-label">Livello esperienza richiesto</div>
                        <div class="row-values"></div>
                    </div>
                </div>

            </div>

            <!-- Comparison Footer -->
            <div class="comparison-footer">
                <button type="button" id="share-comparison" class="btn btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="18" cy="5" r="3"/>
                        <circle cx="6" cy="12" r="3"/>
                        <circle cx="18" cy="19" r="3"/>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                    </svg>
                    Condividi Confronto
                </button>
                <button type="button" id="new-comparison" class="btn btn-primary">
                    Nuovo Confronto
                </button>
            </div>

        </div>

    </div>

</main>

<?php
get_footer();
