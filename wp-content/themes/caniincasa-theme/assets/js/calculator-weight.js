/**
 * Dog Weight Calculator - Frontend Logic
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
        if (typeof dogWeightData !== 'undefined' && dogWeightData.breeds) {
            dogWeightData.breeds.forEach(breed => {
                breedData[breed.id] = breed;
            });
        }

        // Calculate button click
        $('#calculate-weight-btn').on('click', calculateIdealWeight);

        // Reset button click
        $('#reset-weight-calculator').on('click', resetCalculator);
    });

    /**
     * Calculate ideal weight
     */
    function calculateIdealWeight() {
        // Get form values
        const breedId = $('#weight-breed').val();
        const gender = $('#dog-gender').val();
        const currentWeight = parseFloat($('#current-weight').val());
        const dogAge = parseFloat($('#dog-age-weight').val()) || 1;
        const activityLevel = $('#activity-level').val();

        // Validate inputs
        if (!breedId) {
            alert('Seleziona una razza del cane');
            return;
        }

        if (!gender) {
            alert('Seleziona il sesso del cane');
            return;
        }

        if (!currentWeight || currentWeight <= 0) {
            alert('Inserisci il peso attuale del cane');
            return;
        }

        // Get breed data
        const breed = breedData[breedId];
        if (!breed) {
            alert('Dati della razza non trovati');
            return;
        }

        // Calculate BCS score
        const bcsScore = calculateBCS();

        // Get ideal weight range
        const idealWeightRange = getIdealWeightRange(breed, gender);

        if (!idealWeightRange.min || !idealWeightRange.max) {
            alert('Dati di peso ideale non disponibili per questa razza e sesso');
            return;
        }

        // Calculate target weight based on BCS
        const targetWeight = calculateTargetWeight(currentWeight, bcsScore, idealWeightRange);

        // Calculate weight delta
        const weightDelta = currentWeight - targetWeight;

        // Determine weight status
        const weightStatus = determineWeightStatus(bcsScore, weightDelta);

        // Display results
        displayResults({
            currentWeight: currentWeight,
            targetWeight: targetWeight,
            weightDelta: weightDelta,
            bcsScore: bcsScore,
            weightStatus: weightStatus,
            idealWeightRange: idealWeightRange,
            activityLevel: activityLevel,
            breed: breed,
            gender: gender,
            dogAge: dogAge,
        });

        // Show results section
        $('#weight-calculator-results').slideDown(300);

        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#weight-calculator-results').offset().top - 100
        }, 500);
    }

    /**
     * Calculate BCS from questionnaire
     */
    function calculateBCS() {
        const q1 = parseInt($('input[name="bcs_q1"]:checked').val()) || 5;
        const q2 = parseInt($('input[name="bcs_q2"]:checked').val()) || 5;
        const q3 = parseInt($('input[name="bcs_q3"]:checked').val()) || 5;
        const q4 = parseInt($('input[name="bcs_q4"]:checked').val()) || 5;

        // Average of all questions
        const avgScore = (q1 + q2 + q3 + q4) / 4;

        // Round to nearest integer
        return Math.round(avgScore);
    }

    /**
     * Get ideal weight range for breed and gender
     */
    function getIdealWeightRange(breed, gender) {
        if (gender === 'maschio') {
            return {
                min: breed.peso_min_maschio,
                max: breed.peso_max_maschio,
            };
        } else {
            return {
                min: breed.peso_min_femmina,
                max: breed.peso_max_femmina,
            };
        }
    }

    /**
     * Calculate target weight based on BCS
     */
    function calculateTargetWeight(currentWeight, bcsScore, idealWeightRange) {
        const idealMid = (idealWeightRange.min + idealWeightRange.max) / 2;

        // BCS 4-5 is ideal
        if (bcsScore >= 4 && bcsScore <= 5) {
            // Current weight might be close to ideal, but still use breed standard
            return idealMid;
        }
        // BCS 1-3: underweight
        else if (bcsScore < 4) {
            // Target is ideal mid-range
            return idealMid;
        }
        // BCS 6-9: overweight
        else {
            // Calculate how much to lose based on BCS
            // BCS 6: 10-15% overweight
            // BCS 7: 15-25% overweight
            // BCS 8: 25-35% overweight
            // BCS 9: 35%+ overweight

            let targetWeight;
            if (bcsScore === 6) {
                targetWeight = currentWeight / 1.125; // 12.5% reduction
            } else if (bcsScore === 7) {
                targetWeight = currentWeight / 1.20; // 20% reduction
            } else if (bcsScore === 8) {
                targetWeight = currentWeight / 1.30; // 30% reduction
            } else { // BCS 9
                targetWeight = currentWeight / 1.40; // 40% reduction
            }

            // Ensure target is within breed range
            return Math.max(idealWeightRange.min, Math.min(targetWeight, idealWeightRange.max));
        }
    }

    /**
     * Determine weight status
     */
    function determineWeightStatus(bcsScore, weightDelta) {
        let status, color, emoji;

        if (bcsScore >= 4 && bcsScore <= 5) {
            status = 'Peso Ideale';
            color = '#4CAF50';
            emoji = '✅';
        } else if (bcsScore < 4) {
            status = 'Sottopeso';
            color = '#FF9800';
            emoji = '⚠️';
        } else if (bcsScore === 6) {
            status = 'Lieve Sovrappeso';
            color = '#FFC107';
            emoji = '⚠️';
        } else if (bcsScore === 7 || bcsScore === 8) {
            status = 'Sovrappeso';
            color = '#FF5722';
            emoji = '⚠️';
        } else {
            status = 'Obesità';
            color = '#D32F2F';
            emoji = '🚨';
        }

        return { status, color, emoji };
    }

    /**
     * Display calculation results
     */
    function displayResults(data) {
        // Main result
        $('#result-ideal-weight').html(`<strong>${data.targetWeight.toFixed(1)}</strong> kg`);

        // Weight delta
        if (Math.abs(data.weightDelta) < 0.5) {
            $('#result-weight-delta').html('Il peso attuale è già ottimale!');
        } else if (data.weightDelta > 0) {
            $('#result-weight-delta').html(`Da perdere: <strong>${Math.abs(data.weightDelta).toFixed(1)} kg</strong>`);
        } else {
            $('#result-weight-delta').html(`Da guadagnare: <strong>${Math.abs(data.weightDelta).toFixed(1)} kg</strong>`);
        }

        // BCS Score
        const bcsLabel = getBCSLabel(data.bcsScore);
        $('#result-bcs-score').html(`<strong>${data.bcsScore}/9</strong>`);
        $('#result-bcs-label').html(bcsLabel.text);

        // Weight Status
        $('#result-weight-status').html(`
            <span style="color: ${data.weightStatus.color}">
                ${data.weightStatus.emoji} ${data.weightStatus.status}
            </span>
        `);

        // Generate diet plan
        generateDietPlan(data);

        // Generate exercise plan
        generateExercisePlan(data);

        // Generate insights
        generateInsights(data);
    }

    /**
     * Get BCS label
     */
    function getBCSLabel(bcsScore) {
        const labels = {
            1: { text: 'Estremamente Magro', color: '#D32F2F' },
            2: { text: 'Molto Magro', color: '#FF5722' },
            3: { text: 'Magro', color: '#FF9800' },
            4: { text: 'Peso Ideale Basso', color: '#4CAF50' },
            5: { text: 'Peso Ideale', color: '#4CAF50' },
            6: { text: 'Lieve Sovrappeso', color: '#FFC107' },
            7: { text: 'Sovrappeso', color: '#FF9800' },
            8: { text: 'Obeso', color: '#FF5722' },
            9: { text: 'Molto Obeso', color: '#D32F2F' },
        };
        return labels[bcsScore] || labels[5];
    }

    /**
     * Generate diet plan
     */
    function generateDietPlan(data) {
        let planHtml = '';

        // Calculate daily calories
        const dailyCalories = calculateDailyCalories(data.targetWeight, data.activityLevel);
        const currentCalories = calculateDailyCalories(data.currentWeight, data.activityLevel);

        planHtml += `<div class="plan-item">`;
        planHtml += `<div class="plan-icon">🍽️</div>`;
        planHtml += `<div class="plan-details">`;
        planHtml += `<h5>Calorie Giornaliere Target</h5>`;
        planHtml += `<p><strong>${Math.round(dailyCalories)} kcal/giorno</strong></p>`;

        if (data.weightDelta > 0.5) {
            const deficit = Math.round(currentCalories - dailyCalories);
            planHtml += `<p class="plan-note">Riduzione di ${deficit} kcal rispetto al fabbisogno attuale</p>`;
        }

        planHtml += `</div></div>`;

        // Meal frequency
        planHtml += `<div class="plan-item">`;
        planHtml += `<div class="plan-icon">⏰</div>`;
        planHtml += `<div class="plan-details">`;
        planHtml += `<h5>Frequenza Pasti</h5>`;

        if (data.dogAge < 1) {
            planHtml += `<p>3-4 pasti al giorno (cucciolo in crescita)</p>`;
        } else {
            planHtml += `<p>2 pasti al giorno a orari regolari</p>`;
        }

        planHtml += `</div></div>`;

        // Food recommendations
        planHtml += `<div class="plan-item">`;
        planHtml += `<div class="plan-icon">🥩</div>`;
        planHtml += `<div class="plan-details">`;
        planHtml += `<h5>Raccomandazioni Alimentari</h5>`;

        if (data.bcsScore < 4) {
            planHtml += `<p>• Aumentare gradualmente le porzioni del 10-15%</p>`;
            planHtml += `<p>• Scegliere alimenti ad alto valore energetico</p>`;
            planHtml += `<p>• Considerare integratori calorici se necessario</p>`;
        } else if (data.bcsScore > 5) {
            planHtml += `<p>• Ridurre porzioni del 10-20% per perdita graduale</p>`;
            planHtml += `<p>• Alimenti "light" o "weight management"</p>`;
            planHtml += `<p>• Aumentare fibre per maggiore sazietà</p>`;
            planHtml += `<p>• Eliminare snack extra e avanzi dal tavolo</p>`;
        } else {
            planHtml += `<p>• Mantenere alimentazione attuale</p>`;
            planHtml += `<p>• Cibo di qualità bilanciato per l'età</p>`;
            planHtml += `<p>• Snack salutari con moderazione (max 10% calorie)</p>`;
        }

        planHtml += `</div></div>`;

        $('#diet-plan-content').html(planHtml);
    }

    /**
     * Calculate daily calories
     */
    function calculateDailyCalories(weight, activityLevel) {
        // RER (Resting Energy Requirement) = 70 × (weight in kg)^0.75
        const rer = 70 * Math.pow(weight, 0.75);

        // Activity multipliers
        const multipliers = {
            'sedentario': 1.2,
            'leggero': 1.4,
            'moderato': 1.6,
            'attivo': 1.8,
            'molto_attivo': 2.0,
        };

        const multiplier = multipliers[activityLevel] || 1.6;

        return rer * multiplier;
    }

    /**
     * Generate exercise plan
     */
    function generateExercisePlan(data) {
        let planHtml = '';

        // Determine exercise recommendations
        const exerciseRecommendations = getExerciseRecommendations(data.breed.livello_attivita, data.bcsScore, data.dogAge);

        exerciseRecommendations.forEach(rec => {
            planHtml += `<div class="plan-item">`;
            planHtml += `<div class="plan-icon">${rec.icon}</div>`;
            planHtml += `<div class="plan-details">`;
            planHtml += `<h5>${rec.title}</h5>`;
            planHtml += `<p>${rec.description}</p>`;
            planHtml += `</div></div>`;
        });

        $('#exercise-plan-content').html(planHtml);
    }

    /**
     * Get exercise recommendations
     */
    function getExerciseRecommendations(breedActivity, bcsScore, dogAge) {
        const recommendations = [];

        // Base activity
        if (bcsScore > 5) {
            recommendations.push({
                icon: '🚶',
                title: 'Passeggiate Quotidiane',
                description: 'Inizia con 20-30 minuti 2 volte al giorno. Aumenta gradualmente durata e intensità.'
            });
        } else {
            recommendations.push({
                icon: '🚶',
                title: 'Passeggiate Regolari',
                description: 'Mantieni 30-60 minuti al giorno, divisi in più sessioni.'
            });
        }

        // Additional activities
        if (dogAge < 8 && bcsScore <= 6) {
            recommendations.push({
                icon: '⚽',
                title: 'Gioco Attivo',
                description: 'Giochi di riporto, tira e molla, inseguimento. 15-30 minuti al giorno.'
            });
        }

        // Swimming for overweight dogs
        if (bcsScore > 6) {
            recommendations.push({
                icon: '🏊',
                title: 'Nuoto (Opzionale)',
                description: 'Esercizio a basso impatto ideale per perdita peso. Riduce stress su articolazioni.'
            });
        }

        // Mental stimulation
        recommendations.push({
            icon: '🧩',
            title: 'Stimolazione Mentale',
            description: 'Giochi di problem-solving, addestramento, giochi olfattivi. Brucia energia mentale.'
        });

        return recommendations;
    }

    /**
     * Generate insights
     */
    function generateInsights(data) {
        const insights = [];

        // Primary insight based on BCS
        if (data.bcsScore < 4) {
            insights.push({
                icon: '⚠️',
                title: 'Sottopeso Rilevato',
                text: 'Il tuo cane potrebbe essere sottopeso. Consulta il veterinario per escludere problemi di salute e pianificare un aumento ponderale sicuro.',
                type: 'warning'
            });
        } else if (data.bcsScore === 4 || data.bcsScore === 5) {
            insights.push({
                icon: '✅',
                title: 'Peso Ottimale!',
                text: 'Complimenti! Il tuo cane ha un peso ideale. Mantieni la routine attuale di alimentazione ed esercizio.',
                type: 'success'
            });
        } else if (data.bcsScore === 6) {
            insights.push({
                icon: '⚠️',
                title: 'Lieve Sovrappeso',
                text: 'Il tuo cane è leggermente sovrappeso. Piccole modifiche a dieta ed esercizio possono riportarlo al peso forma.',
                type: 'warning'
            });
        } else {
            insights.push({
                icon: '🚨',
                title: 'Sovrappeso Significativo',
                text: 'Il tuo cane è in sovrappeso. Questo aumenta il rischio di diabete, problemi articolari e cardiaci. Consulta il veterinario per un piano personalizzato.',
                type: 'alert'
            });
        }

        // Health risks
        if (data.bcsScore > 6) {
            insights.push({
                icon: '💊',
                title: 'Rischi per la Salute',
                text: 'L\'obesità aumenta il rischio di: diabete, problemi cardiaci, respiratori, articolari, e riduce l\'aspettativa di vita.',
                type: 'info'
            });
        }

        // Timeline
        if (Math.abs(data.weightDelta) > 0.5) {
            const weeksToGoal = calculateWeeksToGoal(data.weightDelta, data.bcsScore);
            insights.push({
                icon: '📅',
                title: 'Timeline Stimata',
                text: `Obiettivo raggiungibile in circa ${weeksToGoal} settimane con perdita/guadagno graduale di 1-2% peso corporeo a settimana.`,
                type: 'info'
            });
        }

        // Render insights
        const insightsHtml = insights.map(insight => `
            <div class="insight-item insight-${insight.type}">
                <div class="insight-icon">${insight.icon}</div>
                <div class="insight-content">
                    <h5>${insight.title}</h5>
                    <p>${insight.text}</p>
                </div>
            </div>
        `).join('');

        $('#weight-insights-content').html(insightsHtml);
    }

    /**
     * Calculate weeks to reach goal
     */
    function calculateWeeksToGoal(weightDelta, bcsScore) {
        // Safe weight loss/gain: 1-2% per week
        const weeklyRate = 0.015; // 1.5% average

        const weeks = Math.abs(weightDelta) / (Math.abs(weightDelta) * weeklyRate);

        return Math.ceil(weeks);
    }

    /**
     * Reset calculator
     */
    function resetCalculator() {
        $('#weight-breed').val('');
        $('#dog-gender').val('');
        $('#current-weight').val('');
        $('#dog-age-weight').val('1');
        $('#activity-level').val('moderato');

        // Reset BCS questions to default (5)
        $('input[name="bcs_q1"][value="5"]').prop('checked', true);
        $('input[name="bcs_q2"][value="5"]').prop('checked', true);
        $('input[name="bcs_q3"][value="5"]').prop('checked', true);
        $('input[name="bcs_q4"][value="5"]').prop('checked', true);

        $('#weight-calculator-results').slideUp(300);
    }

})(jQuery);
