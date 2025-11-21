/**
 * Dog Cost Calculator - Frontend Logic
 *
 * @package Caniincasa
 * @since 1.0.1
 */

(function($) {
    'use strict';

    // Store breed data from PHP
    let breedData = {};

    // Cost database (2024 Italy prices in EUR)
    const costDatabase = {
        initial: {
            adoption: { min: 0, max: 300, avg: 150 },
            purchase_toy: { min: 800, max: 3000, avg: 1500 },
            purchase_small: { min: 500, max: 2000, avg: 1000 },
            purchase_medium: { min: 400, max: 1500, avg: 800 },
            purchase_large: { min: 500, max: 2000, avg: 1000 },
            purchase_giant: { min: 800, max: 2500, avg: 1500 },
            microchip: { min: 20, max: 50, avg: 30 },
            initial_vet: { min: 100, max: 250, avg: 150 },
            equipment_toy: { min: 150, max: 300, avg: 200 },
            equipment_small: { min: 200, max: 400, avg: 300 },
            equipment_medium: { min: 250, max: 500, avg: 350 },
            equipment_large: { min: 300, max: 600, avg: 400 },
            equipment_giant: { min: 350, max: 700, avg: 500 },
        },
        monthly: {
            food_toy_economica: { min: 20, max: 40, avg: 30 },
            food_toy_media: { min: 40, max: 80, avg: 60 },
            food_toy_alta: { min: 80, max: 150, avg: 100 },
            food_small_economica: { min: 30, max: 50, avg: 40 },
            food_small_media: { min: 50, max: 100, avg: 70 },
            food_small_alta: { min: 100, max: 180, avg: 130 },
            food_medium_economica: { min: 50, max: 80, avg: 60 },
            food_medium_media: { min: 80, max: 150, avg: 100 },
            food_medium_alta: { min: 150, max: 250, avg: 180 },
            food_large_economica: { min: 80, max: 120, avg: 100 },
            food_large_media: { min: 120, max: 200, avg: 150 },
            food_large_alta: { min: 200, max: 350, avg: 250 },
            food_giant_economica: { min: 100, max: 150, avg: 120 },
            food_giant_media: { min: 150, max: 250, avg: 180 },
            food_giant_alta: { min: 250, max: 400, avg: 300 },
            treats_snacks: { min: 10, max: 30, avg: 20 },
        },
        annual: {
            vet_base: { min: 100, max: 200, avg: 150 },
            vet_completo: { min: 250, max: 500, avg: 350 },
            vet_insurance: { min: 300, max: 600, avg: 400 },
            flea_tick_small: { min: 80, max: 150, avg: 100 },
            flea_tick_medium: { min: 100, max: 180, avg: 130 },
            flea_tick_large: { min: 120, max: 200, avg: 150 },
            grooming_short: { min: 60, max: 150, avg: 100 },
            grooming_medium: { min: 150, max: 300, avg: 200 },
            grooming_long: { min: 250, max: 500, avg: 350 },
            training_basic: { min: 200, max: 500, avg: 300 },
            training_advanced: { min: 500, max: 1500, avg: 800 },
            boarding_week: { min: 150, max: 350, avg: 250 },
            walker_monthly: { min: 200, max: 500, avg: 300 },
        },
        emergency: {
            low: { min: 100, max: 300, avg: 200 },
            medium: { min: 300, max: 800, avg: 500 },
            high: { min: 800, max: 2000, avg: 1200 },
        },
    };

    // Regional multipliers
    const regionalMultipliers = {
        nord: 1.15,
        centro: 1.0,
        sud: 0.85,
    };

    $(document).ready(function() {
        // Initialize breed data
        if (typeof dogCostData !== 'undefined' && dogCostData.breeds) {
            dogCostData.breeds.forEach(breed => {
                breedData[breed.id] = breed;
            });
        }

        // Calculate button click
        $('#calculate-cost-btn').on('click', calculateCosts);

        // Reset button click
        $('#reset-cost-calculator').on('click', resetCalculator);

        // Tab switching
        $('.tab-btn').on('click', function() {
            const tabName = $(this).data('tab');
            $('.tab-btn').removeClass('active');
            $(this).addClass('active');
            $('.tab-content').removeClass('active');
            $('#tab-' + tabName).addClass('active');
        });
    });

    /**
     * Calculate all costs
     */
    function calculateCosts() {
        // Get form values
        const breedId = $('#cost-breed').val();
        const dogAge = parseFloat($('#dog-age-cost').val()) || 0;
        const region = $('#region').val();
        const foodQuality = $('#food-quality').val();
        const vetPlan = $('#vet-plan').val();

        // Services
        const includeGrooming = $('#include-grooming').is(':checked');
        const includeTraining = $('#include-training').is(':checked');
        const includeBoarding = $('#include-boarding').is(':checked');
        const includeWalker = $('#include-walker').is(':checked');

        // Validate
        if (!breedId) {
            alert('Seleziona una razza del cane');
            return;
        }

        // Get breed data
        const breed = breedData[breedId];
        if (!breed) {
            alert('Dati della razza non trovati');
            return;
        }

        // Calculate costs
        const regionalMultiplier = regionalMultipliers[region];
        const avgLifeExpectancy = (breed.vita_min + breed.vita_max) / 2;
        const remainingYears = Math.max(0, avgLifeExpectancy - dogAge);

        // Initial costs (only if dog is new/young)
        const initialCosts = calculateInitialCosts(breed, dogAge, regionalMultiplier);

        // Monthly costs
        const monthlyCosts = calculateMonthlyCosts(breed, foodQuality, regionalMultiplier);

        // Annual costs
        const annualCosts = calculateAnnualCosts(
            breed,
            vetPlan,
            includeGrooming,
            includeTraining,
            includeBoarding,
            includeWalker,
            regionalMultiplier
        );

        // Lifetime costs
        const lifetimeCosts = calculateLifetimeCosts(
            initialCosts,
            monthlyCosts,
            annualCosts,
            remainingYears
        );

        // Display results
        displayResults({
            breed: breed,
            dogAge: dogAge,
            remainingYears: remainingYears,
            initialCosts: initialCosts,
            monthlyCosts: monthlyCosts,
            annualCosts: annualCosts,
            lifetimeCosts: lifetimeCosts,
            region: region,
            services: {
                grooming: includeGrooming,
                training: includeTraining,
                boarding: includeBoarding,
                walker: includeWalker,
            },
        });

        // Show results
        $('#cost-calculator-results').slideDown(300);

        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#cost-calculator-results').offset().top - 100
        }, 500);
    }

    /**
     * Calculate initial costs
     */
    function calculateInitialCosts(breed, dogAge, multiplier) {
        const costs = [];
        let total = 0;

        // Only show purchase/adoption for new dogs
        if (dogAge < 1) {
            const adoptionCost = costDatabase.initial.adoption.avg;
            costs.push({
                item: 'Adozione/Acquisto',
                amount: adoptionCost * multiplier,
                description: 'Costo medio di adozione da canile o acquisto',
            });
            total += adoptionCost * multiplier;
        }

        // Microchip (if new)
        if (dogAge < 2) {
            const microchipCost = costDatabase.initial.microchip.avg;
            costs.push({
                item: 'Microchip e Registrazione',
                amount: microchipCost,
                description: 'Obbligatorio per legge',
            });
            total += microchipCost;
        }

        // Initial vet visit
        if (dogAge < 1) {
            const initialVet = costDatabase.initial.initial_vet.avg;
            costs.push({
                item: 'Prima Visita Veterinaria',
                amount: initialVet * multiplier,
                description: 'Controllo iniziale e vaccinazioni base',
            });
            total += initialVet * multiplier;
        }

        // Equipment
        if (dogAge < 2) {
            const equipmentKey = `equipment_${breed.taglia}`;
            const equipmentCost = costDatabase.initial[equipmentKey]?.avg || 300;
            costs.push({
                item: 'Attrezzatura Base',
                amount: equipmentCost * multiplier,
                description: 'Cuccia, guinzaglio, ciotole, giochi, trasportino',
            });
            total += equipmentCost * multiplier;
        }

        return { costs, total };
    }

    /**
     * Calculate monthly costs
     */
    function calculateMonthlyCosts(breed, foodQuality, multiplier) {
        const costs = [];
        let total = 0;

        // Food
        const foodKey = `food_${breed.taglia}_${foodQuality}`;
        const foodCost = costDatabase.monthly[foodKey]?.avg || 80;
        costs.push({
            item: 'Alimentazione',
            amount: foodCost * multiplier,
            description: `Cibo di qualità ${foodQuality} per taglia ${breed.taglia}`,
        });
        total += foodCost * multiplier;

        // Treats and snacks
        const treatsCost = costDatabase.monthly.treats_snacks.avg;
        costs.push({
            item: 'Snack e Premi',
            amount: treatsCost,
            description: 'Snack per addestramento e premio',
        });
        total += treatsCost;

        return { costs, total };
    }

    /**
     * Calculate annual costs
     */
    function calculateAnnualCosts(breed, vetPlan, grooming, training, boarding, walker, multiplier) {
        const costs = [];
        let total = 0;

        // Veterinary care
        const vetKey = `vet_${vetPlan}`;
        const vetCost = costDatabase.annual[vetKey]?.avg || 250;
        costs.push({
            item: 'Cure Veterinarie',
            amount: vetCost * multiplier,
            description: `Piano ${vetPlan}: visite, vaccini, analisi`,
        });
        total += vetCost * multiplier;

        // Flea and tick prevention
        const fleaKey = breed.taglia === 'toy' || breed.taglia === 'piccola' ? 'flea_tick_small' :
                        breed.taglia === 'media' ? 'flea_tick_medium' : 'flea_tick_large';
        const fleaCost = costDatabase.annual[fleaKey]?.avg || 120;
        costs.push({
            item: 'Antiparassitari',
            amount: fleaCost * multiplier,
            description: 'Protezione pulci, zecche, filaria',
        });
        total += fleaCost * multiplier;

        // Grooming
        if (grooming) {
            // Determine grooming frequency based on breed coat
            const groomingKey = breed.taglia === 'toy' || breed.taglia === 'piccola' ? 'grooming_short' : 'grooming_medium';
            const groomingCost = costDatabase.annual[groomingKey]?.avg || 200;
            costs.push({
                item: 'Toelettatura',
                amount: groomingCost * multiplier,
                description: 'Bagno, taglio pelo, pulizia 4-6 volte/anno',
            });
            total += groomingCost * multiplier;
        }

        // Training
        if (training) {
            const trainingCost = costDatabase.annual.training_basic.avg;
            costs.push({
                item: 'Addestramento',
                amount: trainingCost * multiplier,
                description: 'Corso base o continuazione educazione',
            });
            total += trainingCost * multiplier;
        }

        // Boarding
        if (boarding) {
            const boardingCost = costDatabase.annual.boarding_week.avg * 2; // 2 weeks per year
            costs.push({
                item: 'Pensione',
                amount: boardingCost * multiplier,
                description: '2 settimane di pensione per vacanze',
            });
            total += boardingCost * multiplier;
        }

        // Dog walker
        if (walker) {
            const walkerCost = costDatabase.annual.walker_monthly.avg * 12;
            costs.push({
                item: 'Dog Walker',
                amount: walkerCost * multiplier,
                description: 'Servizio regolare 3-5 volte/settimana',
            });
            total += walkerCost * multiplier;
        }

        // Emergency fund (based on health predisposition)
        const emergencyKey = breed.predisposizioni_salute || 'medium';
        const emergencyCost = costDatabase.emergency[emergencyKey === 'bassa' ? 'low' :
                                                     emergencyKey === 'alta' ? 'high' : 'medium'].avg;
        costs.push({
            item: 'Fondo Emergenze',
            amount: emergencyCost,
            description: 'Riserva per imprevisti medici (consigliato)',
        });
        total += emergencyCost;

        return { costs, total };
    }

    /**
     * Calculate lifetime costs
     */
    function calculateLifetimeCosts(initialCosts, monthlyCosts, annualCosts, years) {
        const monthlyTotal = monthlyCosts.total * 12 * years;
        const annualTotal = annualCosts.total * years;
        const total = initialCosts.total + monthlyTotal + annualTotal;

        return {
            total: total,
            initial: initialCosts.total,
            monthly: monthlyTotal,
            annual: annualTotal,
        };
    }

    /**
     * Display results
     */
    function displayResults(data) {
        // Main results
        $('#result-lifetime-cost').html(`<strong>€${formatNumber(data.lifetimeCosts.total)}</strong>`);
        $('#result-lifetime-years').html(`Per ${data.remainingYears.toFixed(1)} anni di vita`);
        $('#result-annual-cost').html(`€${formatNumber(data.annualCosts.total + (data.monthlyCosts.total * 12))}`);
        $('#result-monthly-cost').html(`€${formatNumber(data.monthlyCosts.total)}`);

        // Initial costs breakdown
        let initialHtml = '';
        data.initialCosts.costs.forEach(cost => {
            initialHtml += createCostItem(cost);
        });
        initialHtml += `<div class="breakdown-total">Totale: <strong>€${formatNumber(data.initialCosts.total)}</strong></div>`;
        $('#initial-costs-list').html(initialHtml);

        // Recurring costs breakdown
        let recurringHtml = '<h5>Mensili</h5>';
        data.monthlyCosts.costs.forEach(cost => {
            recurringHtml += createCostItem(cost);
        });
        recurringHtml += `<div class="breakdown-subtotal">Subtotale Mensile: <strong>€${formatNumber(data.monthlyCosts.total)}</strong></div>`;

        recurringHtml += '<h5>Annuali</h5>';
        data.annualCosts.costs.forEach(cost => {
            recurringHtml += createCostItem(cost);
        });
        recurringHtml += `<div class="breakdown-total">Totale Annuale: <strong>€${formatNumber(data.annualCosts.total + (data.monthlyCosts.total * 12))}</strong></div>`;
        $('#recurring-costs-list').html(recurringHtml);

        // Optional costs
        let optionalHtml = '<p class="info-text">Costi inclusi nella tua configurazione:</p>';
        const optionalServices = [];
        if (data.services.grooming) optionalServices.push('Toelettatura');
        if (data.services.training) optionalServices.push('Addestramento');
        if (data.services.boarding) optionalServices.push('Pensione');
        if (data.services.walker) optionalServices.push('Dog Walker');

        if (optionalServices.length > 0) {
            optionalHtml += '<ul>';
            optionalServices.forEach(service => {
                optionalHtml += `<li>✓ ${service}</li>`;
            });
            optionalHtml += '</ul>';
        } else {
            optionalHtml += '<p>Nessun servizio extra selezionato</p>';
        }
        $('#optional-costs-list').html(optionalHtml);

        // Cost distribution chart
        generateCostChart(data);

        // Generate insights
        generateCostInsights(data);
    }

    /**
     * Create cost item HTML
     */
    function createCostItem(cost) {
        return `
            <div class="cost-item">
                <div class="cost-item-header">
                    <span class="cost-item-name">${cost.item}</span>
                    <span class="cost-item-amount">€${formatNumber(cost.amount)}</span>
                </div>
                <div class="cost-item-description">${cost.description}</div>
            </div>
        `;
    }

    /**
     * Generate cost distribution chart
     */
    function generateCostChart(data) {
        const yearlyFood = data.monthlyCosts.total * 12;
        const yearlyVet = data.annualCosts.total;
        const total = yearlyFood + yearlyVet;

        const foodPercent = (yearlyFood / total) * 100;
        const vetPercent = (yearlyVet / total) * 100;

        const chartHtml = `
            <div class="chart-bars">
                <div class="chart-bar">
                    <div class="chart-bar-label">Alimentazione</div>
                    <div class="chart-bar-visual">
                        <div class="chart-bar-fill" style="width: ${foodPercent}%; background: #4CAF50;"></div>
                    </div>
                    <div class="chart-bar-value">€${formatNumber(yearlyFood)} (${foodPercent.toFixed(1)}%)</div>
                </div>
                <div class="chart-bar">
                    <div class="chart-bar-label">Cure e Servizi</div>
                    <div class="chart-bar-visual">
                        <div class="chart-bar-fill" style="width: ${vetPercent}%; background: #2196F3;"></div>
                    </div>
                    <div class="chart-bar-value">€${formatNumber(yearlyVet)} (${vetPercent.toFixed(1)}%)</div>
                </div>
            </div>
        `;

        $('#cost-distribution-chart').html(chartHtml);
    }

    /**
     * Generate cost insights
     */
    function generateCostInsights(data) {
        const insights = [];

        // Budget insight
        const monthlyBudget = data.monthlyCosts.total + (data.annualCosts.total / 12);
        insights.push({
            icon: '💰',
            title: 'Budget Mensile Consigliato',
            text: `Metti da parte €${formatNumber(monthlyBudget)} al mese per coprire tutte le spese. Include un fondo emergenze per imprevisti medici.`,
        });

        // Saving tips
        insights.push({
            icon: '💡',
            title: 'Come Risparmiare',
            text: 'Acquista cibo in bulk, assicurati che le vaccinazioni siano in regola per prevenire malattie, considera un\'assicurazione sanitaria se la razza ha predisposizioni.',
        });

        // Size-specific
        if (data.breed.taglia === 'gigante' || data.breed.taglia === 'grande') {
            insights.push({
                icon: '📏',
                title: 'Costi Razza Grande',
                text: 'Le razze grandi hanno costi superiori: più cibo, farmaci dosi maggiori, attrezzatura robusta. Pianifica di conseguenza.',
            });
        }

        // Health costs
        if (data.breed.predisposizioni_salute === 'alta') {
            insights.push({
                icon: '🏥',
                title: 'Attenzione Salute',
                text: 'Questa razza ha predisposizioni a problemi di salute. Un fondo emergenze adeguato e controlli preventivi possono ridurre costi futuri.',
            });
        }

        // Render
        const insightsHtml = insights.map(insight => `
            <div class="insight-item">
                <div class="insight-icon">${insight.icon}</div>
                <div class="insight-content">
                    <h5>${insight.title}</h5>
                    <p>${insight.text}</p>
                </div>
            </div>
        `).join('');

        $('#cost-insights-content').html(insightsHtml);
    }

    /**
     * Format number with thousands separator
     */
    function formatNumber(num) {
        return Math.round(num).toLocaleString('it-IT');
    }

    /**
     * Reset calculator
     */
    function resetCalculator() {
        $('#cost-breed').val('');
        $('#dog-age-cost').val('0');
        $('#region').val('centro');
        $('#food-quality').val('media');
        $('#vet-plan').val('completo');
        $('#include-grooming').prop('checked', true);
        $('#include-training').prop('checked', false);
        $('#include-boarding').prop('checked', false);
        $('#include-walker').prop('checked', false);
        $('#cost-calculator-results').slideUp(300);
    }

})(jQuery);
