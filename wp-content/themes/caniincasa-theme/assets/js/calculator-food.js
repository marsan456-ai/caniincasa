/**
 * Dog Food Calculator JavaScript
 *
 * @package Caniincasa
 * @since 1.0.2
 */

(function($) {
    'use strict';

    /**
     * Initialize calculator
     */
    function init() {
        // Tab switching
        $('.calculator-tabs .tab-btn').on('click', function() {
            const tabId = $(this).data('tab');
            switchTab(tabId);
        });

        // Form submissions
        $('#form-crocchette').on('submit', function(e) {
            e.preventDefault();
            calculateCrocchette();
        });

        $('#form-barf').on('submit', function(e) {
            e.preventDefault();
            calculateBarf();
        });

        $('#form-casalinga').on('submit', function(e) {
            e.preventDefault();
            calculateCasalinga();
        });
    }

    /**
     * Switch between tabs
     */
    function switchTab(tabId) {
        // Update tab buttons
        $('.tab-btn').removeClass('active');
        $(`.tab-btn[data-tab="${tabId}"]`).addClass('active');

        // Update tab content
        $('.tab-content').removeClass('active');
        $(`#tab-${tabId}`).addClass('active');

        // Hide all results
        $('.calculator-results').hide();
    }

    /**
     * Calculate RER (Resting Energy Requirement)
     * Formula: RER = 70 * (peso^0.75)
     */
    function calculateRER(peso) {
        return 70 * Math.pow(peso, 0.75);
    }

    /**
     * Get activity multiplier for MER (Maintenance Energy Requirement)
     */
    function getActivityMultiplier(attivita, eta, stato) {
        let multiplier = 1.0;

        // Base multiplier by activity
        switch (attivita) {
            case 'sedentario':
                multiplier = 1.2;
                break;
            case 'moderato':
                multiplier = 1.4;
                break;
            case 'attivo':
                multiplier = 1.6;
                break;
            case 'sportivo':
                multiplier = 2.0;
                break;
            default:
                multiplier = 1.4;
        }

        // Adjust by age
        switch (eta) {
            case 'cucciolo':
                multiplier *= 1.5; // Growing dogs need more
                break;
            case 'senior':
                multiplier *= 0.9; // Senior dogs need less
                break;
        }

        // Adjust by physical condition
        if (stato) {
            switch (stato) {
                case 'sottopeso':
                    multiplier *= 1.2;
                    break;
                case 'sovrappeso':
                    multiplier *= 0.8;
                    break;
            }
        }

        return multiplier;
    }

    /**
     * Calculate Crocchette quantity
     */
    function calculateCrocchette() {
        const peso = parseFloat($('#crocc-peso').val());
        const eta = $('#crocc-eta').val();
        const attivita = $('#crocc-attivita').val();
        const stato = $('#crocc-stato').val();
        const kcalPer100g = parseFloat($('#crocc-kcal').val());
        const pasti = parseInt($('#crocc-pasti').val());

        if (!peso || !kcalPer100g) {
            alert('Inserisci il peso del cane e le calorie delle crocchette');
            return;
        }

        // Calculate daily energy requirement
        const RER = calculateRER(peso);
        const multiplier = getActivityMultiplier(attivita, eta, stato);
        const MER = RER * multiplier; // Maintenance Energy Requirement (kcal/day)

        // Calculate grams of kibble needed
        const gramsPerDay = (MER / kcalPer100g) * 100;
        const gramsPerMeal = gramsPerDay / pasti;
        const kgPerMonth = (gramsPerDay * 30) / 1000;

        // Build results HTML
        const resultsHtml = `
            <div class="results-card">
                <h3>Risultati Calcolo Crocchette</h3>

                <div class="results-summary">
                    <div class="result-main">
                        <span class="result-value">${Math.round(gramsPerDay)}</span>
                        <span class="result-unit">grammi/giorno</span>
                    </div>
                </div>

                <div class="results-details">
                    <div class="result-item">
                        <span class="result-label">Fabbisogno energetico</span>
                        <span class="result-data">${Math.round(MER)} kcal/giorno</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Per pasto (${pasti} pasti)</span>
                        <span class="result-data">${Math.round(gramsPerMeal)} g/pasto</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Consumo mensile</span>
                        <span class="result-data">${kgPerMonth.toFixed(1)} kg/mese</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Consumo annuale</span>
                        <span class="result-data">${(kgPerMonth * 12).toFixed(1)} kg/anno</span>
                    </div>
                </div>

                <div class="results-schedule">
                    <h4>Programma Alimentare Consigliato</h4>
                    ${generateFeedingSchedule(pasti, gramsPerMeal)}
                </div>

                <div class="results-tips">
                    <h4>Consigli</h4>
                    <ul>
                        <li>Pesa sempre le crocchette con una bilancia da cucina</li>
                        <li>Mantieni orari regolari per i pasti</li>
                        <li>Lascia sempre acqua fresca disponibile</li>
                        <li>Adatta le quantita in base alla risposta del tuo cane</li>
                    </ul>
                </div>
            </div>
        `;

        $('#results-crocchette').html(resultsHtml).slideDown();
        scrollToResults('#results-crocchette');
    }

    /**
     * Calculate BARF diet quantity
     */
    function calculateBarf() {
        const peso = parseFloat($('#barf-peso').val());
        const eta = $('#barf-eta').val();
        const attivita = $('#barf-attivita').val();
        const percentuale = parseFloat($('#barf-percentuale').val());

        if (!peso) {
            alert('Inserisci il peso del cane');
            return;
        }

        // Calculate total daily food (percentage of body weight)
        const totalGrams = peso * 1000 * (percentuale / 100);

        // BARF breakdown
        const carneOssa = totalGrams * 0.70;  // 70% meat + bones
        const frattaglie = totalGrams * 0.10; // 10% organs
        const verdure = totalGrams * 0.15;    // 15% vegetables
        const integratori = totalGrams * 0.05; // 5% supplements

        // Monthly and weekly quantities
        const kgPerWeek = (totalGrams * 7) / 1000;
        const kgPerMonth = (totalGrams * 30) / 1000;

        // Build results HTML
        const resultsHtml = `
            <div class="results-card">
                <h3>Risultati Dieta BARF</h3>

                <div class="results-summary">
                    <div class="result-main">
                        <span class="result-value">${Math.round(totalGrams)}</span>
                        <span class="result-unit">grammi/giorno</span>
                    </div>
                    <span class="result-note">(${percentuale}% del peso corporeo)</span>
                </div>

                <div class="barf-breakdown">
                    <h4>Composizione Giornaliera</h4>

                    <div class="breakdown-item">
                        <div class="breakdown-bar" style="--width: 70%; --color: #ef4444;">
                            <span class="breakdown-label">Carne + Ossa polpose</span>
                            <span class="breakdown-value">${Math.round(carneOssa)} g</span>
                        </div>
                    </div>

                    <div class="breakdown-item">
                        <div class="breakdown-bar" style="--width: 10%; --color: #8b5cf6;">
                            <span class="breakdown-label">Frattaglie</span>
                            <span class="breakdown-value">${Math.round(frattaglie)} g</span>
                        </div>
                    </div>

                    <div class="breakdown-item">
                        <div class="breakdown-bar" style="--width: 15%; --color: #22c55e;">
                            <span class="breakdown-label">Verdure e Frutta</span>
                            <span class="breakdown-value">${Math.round(verdure)} g</span>
                        </div>
                    </div>

                    <div class="breakdown-item">
                        <div class="breakdown-bar" style="--width: 5%; --color: #f97316;">
                            <span class="breakdown-label">Integratori</span>
                            <span class="breakdown-value">${Math.round(integratori)} g</span>
                        </div>
                    </div>
                </div>

                <div class="results-details">
                    <div class="result-item">
                        <span class="result-label">Consumo settimanale</span>
                        <span class="result-data">${kgPerWeek.toFixed(1)} kg/settimana</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Consumo mensile</span>
                        <span class="result-data">${kgPerMonth.toFixed(1)} kg/mese</span>
                    </div>
                </div>

                <div class="barf-shopping-list">
                    <h4>Lista della Spesa Settimanale</h4>
                    <ul>
                        <li><strong>Carne muscolare:</strong> ~${((carneOssa * 0.6 * 7) / 1000).toFixed(1)} kg</li>
                        <li><strong>Ossa polpose:</strong> ~${((carneOssa * 0.4 * 7) / 1000).toFixed(1)} kg</li>
                        <li><strong>Frattaglie:</strong> ~${((frattaglie * 7) / 1000).toFixed(1)} kg</li>
                        <li><strong>Verdure miste:</strong> ~${((verdure * 7) / 1000).toFixed(1)} kg</li>
                        <li><strong>Olio/Integratori:</strong> secondo indicazioni</li>
                    </ul>
                </div>

                <div class="results-tips barf-tips">
                    <h4>Alimenti Consigliati</h4>
                    <div class="tips-columns">
                        <div class="tips-column">
                            <h5>Carni</h5>
                            <ul>
                                <li>Pollo, tacchino</li>
                                <li>Manzo, vitello</li>
                                <li>Agnello</li>
                                <li>Coniglio</li>
                            </ul>
                        </div>
                        <div class="tips-column">
                            <h5>Frattaglie</h5>
                            <ul>
                                <li>Fegato (max 5%)</li>
                                <li>Cuore</li>
                                <li>Reni</li>
                                <li>Milza</li>
                            </ul>
                        </div>
                        <div class="tips-column">
                            <h5>Verdure</h5>
                            <ul>
                                <li>Carote</li>
                                <li>Zucchine</li>
                                <li>Spinaci</li>
                                <li>Mele (no semi)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#results-barf').html(resultsHtml).slideDown();
        scrollToResults('#results-barf');
    }

    /**
     * Calculate Casalinga (home-cooked) diet quantity
     */
    function calculateCasalinga() {
        const peso = parseFloat($('#casa-peso').val());
        const eta = $('#casa-eta').val();
        const attivita = $('#casa-attivita').val();
        const stato = $('#casa-stato').val();

        if (!peso) {
            alert('Inserisci il peso del cane');
            return;
        }

        // Calculate daily energy requirement
        const RER = calculateRER(peso);
        const multiplier = getActivityMultiplier(attivita, eta, stato);
        const MER = RER * multiplier;

        // Home-cooked food average ~1.2 kcal/g (varies widely)
        // This is an approximation - real value depends on ingredients
        const kcalPerGram = 1.2;
        const totalGrams = MER / kcalPerGram;

        // Breakdown
        const proteine = totalGrams * 0.40;    // 40% protein
        const carboidrati = totalGrams * 0.30; // 30% carbs
        const verdure = totalGrams * 0.25;     // 25% vegetables
        const grassi = totalGrams * 0.05;      // 5% fats

        // Monthly quantity
        const kgPerMonth = (totalGrams * 30) / 1000;

        // Build results HTML
        const resultsHtml = `
            <div class="results-card">
                <h3>Risultati Alimentazione Casalinga</h3>

                <div class="results-summary">
                    <div class="result-main">
                        <span class="result-value">${Math.round(totalGrams)}</span>
                        <span class="result-unit">grammi/giorno</span>
                    </div>
                    <span class="result-note">(cibo cotto)</span>
                </div>

                <div class="casalinga-breakdown">
                    <h4>Composizione Giornaliera</h4>

                    <div class="breakdown-item">
                        <div class="breakdown-bar" style="--width: 40%; --color: #ef4444;">
                            <span class="breakdown-label">Proteine (carne/pesce/uova)</span>
                            <span class="breakdown-value">${Math.round(proteine)} g</span>
                        </div>
                    </div>

                    <div class="breakdown-item">
                        <div class="breakdown-bar" style="--width: 30%; --color: #f59e0b;">
                            <span class="breakdown-label">Carboidrati (riso/pasta/patate)</span>
                            <span class="breakdown-value">${Math.round(carboidrati)} g</span>
                        </div>
                    </div>

                    <div class="breakdown-item">
                        <div class="breakdown-bar" style="--width: 25%; --color: #22c55e;">
                            <span class="breakdown-label">Verdure</span>
                            <span class="breakdown-value">${Math.round(verdure)} g</span>
                        </div>
                    </div>

                    <div class="breakdown-item">
                        <div class="breakdown-bar" style="--width: 5%; --color: #3b82f6;">
                            <span class="breakdown-label">Grassi (olio)</span>
                            <span class="breakdown-value">${Math.round(grassi)} g</span>
                        </div>
                    </div>
                </div>

                <div class="results-details">
                    <div class="result-item">
                        <span class="result-label">Fabbisogno energetico</span>
                        <span class="result-data">${Math.round(MER)} kcal/giorno</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Consumo mensile totale</span>
                        <span class="result-data">~${kgPerMonth.toFixed(1)} kg/mese</span>
                    </div>
                </div>

                <div class="example-recipe">
                    <h4>Esempio Ricetta Giornaliera</h4>
                    <div class="recipe-card">
                        <ul>
                            <li><strong>${Math.round(proteine)} g</strong> di petto di pollo/manzo/pesce (cotto)</li>
                            <li><strong>${Math.round(carboidrati)} g</strong> di riso o pasta (peso cotto)</li>
                            <li><strong>${Math.round(verdure)} g</strong> di verdure miste (zucchine, carote)</li>
                            <li><strong>${Math.round(grassi)} g</strong> di olio EVO (~1 cucchiaino ogni 5kg peso)</li>
                        </ul>
                        <p class="recipe-note">Cuoci separatamente, mescola e servi tiepido.</p>
                    </div>
                </div>

                <div class="results-tips">
                    <h4>Alimenti da Evitare</h4>
                    <div class="danger-foods">
                        <span class="danger-item">Cipolle/Aglio</span>
                        <span class="danger-item">Cioccolato</span>
                        <span class="danger-item">Uva/Uvetta</span>
                        <span class="danger-item">Xilitolo</span>
                        <span class="danger-item">Ossa cotte</span>
                        <span class="danger-item">Avocado</span>
                        <span class="danger-item">Noci di Macadamia</span>
                        <span class="danger-item">Alcol/Caffe</span>
                    </div>
                </div>

                <div class="vet-reminder">
                    <span class="reminder-icon">👨‍⚕️</span>
                    <p>L'alimentazione casalinga richiede integratori (calcio, vitamine). Consulta il veterinario per un piano completo.</p>
                </div>
            </div>
        `;

        $('#results-casalinga').html(resultsHtml).slideDown();
        scrollToResults('#results-casalinga');
    }

    /**
     * Generate feeding schedule HTML
     */
    function generateFeedingSchedule(pasti, gramsPerMeal) {
        const schedules = {
            1: [{ time: '18:00', label: 'Cena' }],
            2: [
                { time: '08:00', label: 'Colazione' },
                { time: '18:00', label: 'Cena' }
            ],
            3: [
                { time: '07:00', label: 'Colazione' },
                { time: '13:00', label: 'Pranzo' },
                { time: '19:00', label: 'Cena' }
            ]
        };

        const schedule = schedules[pasti] || schedules[2];
        let html = '<div class="schedule-items">';

        schedule.forEach(meal => {
            html += `
                <div class="schedule-item">
                    <span class="schedule-time">${meal.time}</span>
                    <span class="schedule-meal">${meal.label}</span>
                    <span class="schedule-amount">${Math.round(gramsPerMeal)} g</span>
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    /**
     * Scroll to results
     */
    function scrollToResults(selector) {
        setTimeout(function() {
            const $results = $(selector);
            if ($results.length && $results.offset()) {
                $('html, body').animate({
                    scrollTop: $results.offset().top - 100
                }, 500);
            }
        }, 300);
    }

    // Initialize on document ready
    $(document).ready(init);

})(jQuery);
