/**
 * Dog Age Calculator - Frontend Logic
 *
 * @package Caniincasa
 * @since 1.0.1
 */

(function($) {
    'use strict';

    // Store breed data from PHP
    let breedData = {};

    $(document).ready(function() {
        // Initialize breed data
        if (typeof dogAgeData !== 'undefined' && dogAgeData.breeds) {
            dogAgeData.breeds.forEach(breed => {
                breedData[breed.id] = breed;
            });
        }

        // Calculate button click
        $('#calculate-age-btn').on('click', calculateDogAge);

        // Reset button click
        $('#reset-age-calculator').on('click', resetCalculator);

        // Enable calculate on Enter key
        $('#dog-age-years, #dog-age-months').on('keypress', function(e) {
            if (e.which === 13) {
                calculateDogAge();
            }
        });
    });

    /**
     * Calculate dog's human age equivalent
     */
    function calculateDogAge() {
        // Get form values
        const breedId = $('#dog-breed').val();
        const ageYears = parseInt($('#dog-age-years').val()) || 0;
        const ageMonths = parseInt($('#dog-age-months').val()) || 0;

        // Validate inputs
        if (!breedId) {
            alert('Seleziona una razza del cane');
            return;
        }

        if (ageYears === 0 && ageMonths === 0) {
            alert('Inserisci l\'età del cane');
            return;
        }

        // Get breed data
        const breed = breedData[breedId];
        if (!breed) {
            alert('Dati della razza non trovati');
            return;
        }

        // Calculate total age in years (decimal)
        const totalAgeYears = ageYears + (ageMonths / 12);

        // Calculate using different methods
        const ageTraditional = calculateTraditional(totalAgeYears);
        const ageScientific = calculateScientific(totalAgeYears);
        const ageBreedSpecific = calculateBreedSpecific(totalAgeYears, breed);

        // Determine life stage
        const lifeStage = determineLifeStage(totalAgeYears, breed);

        // Display results
        displayResults({
            dogAge: totalAgeYears,
            ageTraditional: ageTraditional,
            ageScientific: ageScientific,
            ageBreedSpecific: ageBreedSpecific,
            lifeStage: lifeStage,
            breed: breed,
        });

        // Show results section
        $('#age-calculator-results').slideDown(300);

        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#age-calculator-results').offset().top - 100
        }, 500);
    }

    /**
     * Traditional method: age × 7
     */
    function calculateTraditional(ageYears) {
        return Math.round(ageYears * 7);
    }

    /**
     * Scientific method: UCSD 2020 formula
     * 16 × ln(dog_age) + 31
     */
    function calculateScientific(ageYears) {
        if (ageYears < 0.5) {
            // For very young puppies, use a linear approximation
            return Math.round(15 * ageYears);
        }
        return Math.round(16 * Math.log(ageYears) + 31);
    }

    /**
     * Breed-specific method: custom coefficients
     */
    function calculateBreedSpecific(ageYears, breed) {
        let humanAge = 0;

        // Puppy stage (0-2 years)
        if (ageYears <= 2) {
            humanAge = ageYears * breed.coef_cucciolo;
        }
        // Adult stage (2-7 years approximately)
        else if (ageYears <= 7) {
            // First 2 years
            humanAge = 2 * breed.coef_cucciolo;
            // Remaining years
            humanAge += (ageYears - 2) * breed.coef_adulto;
        }
        // Senior stage (7+ years)
        else {
            // First 2 years (puppy)
            humanAge = 2 * breed.coef_cucciolo;
            // Years 2-7 (adult)
            humanAge += 5 * breed.coef_adulto;
            // Remaining years (senior)
            humanAge += (ageYears - 7) * breed.coef_senior;
        }

        return Math.round(humanAge);
    }

    /**
     * Determine life stage
     */
    function determineLifeStage(ageYears, breed) {
        const avgLifeExpectancy = (breed.vita_min + breed.vita_max) / 2;

        if (ageYears < 2) {
            return {
                name: 'Cucciolo',
                emoji: '🐶',
                color: '#4CAF50',
                description: 'Fase di crescita e apprendimento'
            };
        } else if (ageYears < 7) {
            return {
                name: 'Adulto',
                emoji: '🐕',
                color: '#2196F3',
                description: 'Fase di maturità e piena energia'
            };
        } else if (ageYears < avgLifeExpectancy * 0.75) {
            return {
                name: 'Adulto Maturo',
                emoji: '🦮',
                color: '#FF9800',
                description: 'Fase di stabilità e saggezza'
            };
        } else {
            return {
                name: 'Senior',
                emoji: '🐕‍🦺',
                color: '#9C27B0',
                description: 'Fase che richiede cure extra'
            };
        }
    }

    /**
     * Display calculation results
     */
    function displayResults(data) {
        // Main result (breed-specific)
        $('#result-age-breed').html(`<strong>${data.ageBreedSpecific}</strong> anni umani`);

        // Life stage
        $('#result-life-stage').html(`
            <span style="color: ${data.lifeStage.color}">
                ${data.lifeStage.emoji} ${data.lifeStage.name}
            </span>
        `);

        // Life expectancy
        const avgLifeExpectancy = (data.breed.vita_min + data.breed.vita_max) / 2;
        const remainingYears = Math.max(0, avgLifeExpectancy - data.dogAge);
        $('#result-life-expectancy').html(`
            ${data.breed.vita_min}-${data.breed.vita_max} anni<br>
            <small style="opacity: 0.8;">~${remainingYears.toFixed(1)} anni rimanenti</small>
        `);

        // Comparison values
        $('#result-age-traditional').text(`${data.ageTraditional} anni`);
        $('#result-age-scientific').text(`${data.ageScientific} anni`);
        $('#result-age-breed-comparison').text(`${data.ageBreedSpecific} anni`);

        // Generate insights
        generateInsights(data);

        // Generate timeline
        generateTimeline(data);
    }

    /**
     * Generate insights and recommendations
     */
    function generateInsights(data) {
        const insights = [];

        // Age interpretation
        if (data.dogAge < 1) {
            insights.push({
                icon: '🍼',
                title: 'Cucciolo in Crescita',
                text: 'Il tuo cane è ancora un cucciolo! Questo è il momento cruciale per socializzazione e addestramento di base.'
            });
        } else if (data.lifeStage.name === 'Senior') {
            insights.push({
                icon: '💙',
                title: 'Cure Senior',
                text: 'Il tuo cane è nella fase senior. Considera controlli veterinari più frequenti e attenzione alle articolazioni.'
            });
        } else if (data.lifeStage.name === 'Adulto') {
            insights.push({
                icon: '⚡',
                title: 'Nel Pieno delle Forze',
                text: 'Il tuo cane è nel suo periodo di massima energia. Perfetto per attività fisiche e sport cinofili!'
            });
        }

        // Health priorities
        const healthPriorities = getHealthPriorities(data.dogAge, data.lifeStage.name);
        insights.push({
            icon: '🏥',
            title: 'Priorità Salute',
            text: healthPriorities
        });

        // Size-specific advice
        const sizeAdvice = getSizeSpecificAdvice(data.breed.taglia);
        if (sizeAdvice) {
            insights.push({
                icon: '📏',
                title: 'Info Taglia',
                text: sizeAdvice
            });
        }

        // Render insights
        const insightsHtml = insights.map(insight => `
            <div class="insight-item">
                <div class="insight-icon">${insight.icon}</div>
                <div class="insight-content">
                    <h5>${insight.title}</h5>
                    <p>${insight.text}</p>
                </div>
            </div>
        `).join('');

        $('#age-insights-content').html(insightsHtml);
    }

    /**
     * Get health priorities by life stage
     */
    function getHealthPriorities(age, stage) {
        if (age < 1) {
            return 'Vaccinazioni, antiparassitari, socializzazione, addestramento di base.';
        } else if (stage === 'Cucciolo') {
            return 'Completamento vaccinazioni, sterilizzazione/castrazione, educazione comportamentale.';
        } else if (stage === 'Adulto') {
            return 'Controlli annuali, igiene dentale, mantenimento peso forma, esercizio regolare.';
        } else {
            return 'Controlli semestrali, gestione dolore articolare, dieta senior, monitoraggio peso.';
        }
    }

    /**
     * Get size-specific advice
     */
    function getSizeSpecificAdvice(taglia) {
        const advice = {
            'toy': 'Le razze toy vivono più a lungo ma sono fragili. Attenzione a cadute e traumi.',
            'piccola': 'Le razze piccole tendono a vivere 14-16 anni. Attenzione a problemi dentali.',
            'media': 'Le razze medie hanno una buona longevità (10-14 anni) ed equilibrio.',
            'grande': 'Le razze grandi invecchiano più velocemente. Monitora displasia e articolazioni.',
            'gigante': 'Le razze giganti hanno vita più breve (7-10 anni). Cruciale la gestione del peso.'
        };
        return advice[taglia] || '';
    }

    /**
     * Generate age progression timeline
     */
    function generateTimeline(data) {
        const maxAge = data.breed.vita_max;
        const currentAge = data.dogAge;
        const percentage = Math.min((currentAge / maxAge) * 100, 100);

        const timelineHtml = `
            <div class="timeline-bar">
                <div class="timeline-progress" style="width: ${percentage}%; background-color: ${data.lifeStage.color};">
                    <span class="timeline-marker">${data.lifeStage.emoji}</span>
                </div>
            </div>
            <div class="timeline-labels">
                <span>0 anni</span>
                <span>Età attuale: ${currentAge.toFixed(1)} anni (${data.ageBreedSpecific} anni umani)</span>
                <span>${maxAge} anni</span>
            </div>
            <div class="timeline-stages">
                <div class="stage-marker" style="left: 0%;">Nascita</div>
                <div class="stage-marker" style="left: 16.7%;">2 anni<br><small>(Adulto)</small></div>
                <div class="stage-marker" style="left: 58.3%;">7 anni<br><small>(Senior)</small></div>
                <div class="stage-marker" style="left: 100%;">Fine vita</div>
            </div>
        `;

        $('#age-timeline').html(timelineHtml);
    }

    /**
     * Reset calculator
     */
    function resetCalculator() {
        $('#dog-breed').val('');
        $('#dog-age-years').val('1');
        $('#dog-age-months').val('0');
        $('#age-calculator-results').slideUp(300);
    }

})(jQuery);
