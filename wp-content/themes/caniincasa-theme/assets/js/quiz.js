/**
 * Quiz Selezione Razza JavaScript
 *
 * @package Caniincasa
 */

(function($) {
    'use strict';

    let currentQuestion = 1;
    const totalQuestions = 9;
    let quizResults = null;

    /**
     * Initialize quiz navigation
     */
    function initQuizNavigation() {
        // Next button
        $('.btn-next').on('click', function(e) {
            e.preventDefault();

            if (validateCurrentQuestion()) {
                if (currentQuestion < totalQuestions) {
                    currentQuestion++;
                    updateQuizDisplay();
                }
            }
        });

        // Previous button
        $('.btn-prev').on('click', function(e) {
            e.preventDefault();

            if (currentQuestion > 1) {
                currentQuestion--;
                updateQuizDisplay();
            }
        });

        // Submit button
        $('.btn-submit').on('click', function(e) {
            e.preventDefault();

            if (validateCurrentQuestion()) {
                submitQuiz();
            }
        });
    }

    /**
     * Update quiz display (questions, progress, buttons)
     */
    function updateQuizDisplay() {
        // Update questions visibility
        $('.quiz-question').removeClass('active');
        $('.quiz-question[data-question="' + currentQuestion + '"]').addClass('active');

        // Update progress bar
        const progressPercentage = (currentQuestion / totalQuestions) * 100;
        $('.quiz-progress-fill').css('width', progressPercentage + '%');
        $('.quiz-progress-current').text(currentQuestion);

        // Update navigation buttons
        if (currentQuestion === 1) {
            $('.btn-prev').hide();
        } else {
            $('.btn-prev').show();
        }

        if (currentQuestion === totalQuestions) {
            $('.btn-next').hide();
            $('.btn-submit').show();
        } else {
            $('.btn-next').show();
            $('.btn-submit').hide();
        }

        // Scroll to top
        const $container = $('.quiz-container');
        if ($container.length && $container.offset()) {
            $('html, body').animate({
                scrollTop: $container.offset().top - 100
            }, 300);
        }
    }

    /**
     * Validate current question
     */
    function validateCurrentQuestion() {
        const $currentQuestion = $('.quiz-question[data-question="' + currentQuestion + '"]');
        const $radioInputs = $currentQuestion.find('input[type="radio"]');
        const isChecked = $radioInputs.is(':checked');

        if (!isChecked) {
            showMessage('Seleziona una risposta per continuare.', 'error');
            return false;
        }

        return true;
    }

    /**
     * Submit quiz via AJAX
     */
    function submitQuiz() {
        const $submitBtn = $('.btn-submit');

        // Disable submit button
        $submitBtn.prop('disabled', true).addClass('loading');

        // Collect all answers
        const formData = {
            action: 'submit_quiz',
            nonce: $('#quiz_nonce').val(),
            esperienza: $('input[name="esperienza"]:checked').val(),
            abitazione: $('input[name="abitazione"]:checked').val(),
            tempo: $('input[name="tempo"]:checked').val(),
            attivita: $('input[name="attivita"]:checked').val(),
            bambini: $('input[name="bambini"]:checked').val(),
            animali: $('input[name="animali"]:checked').val(),
            clima: $('input[name="clima"]:checked').val(),
            manutenzione: $('input[name="manutenzione"]:checked').val(),
            scopo: $('input[name="scopo"]:checked').val()
        };

        // AJAX request
        $.ajax({
            url: caniincasaQuiz.ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    quizResults = response.data;
                    displayResults(response.data);
                } else {
                    showMessage(response.data.message || 'Errore durante l\'elaborazione del quiz.', 'error');
                    $submitBtn.prop('disabled', false).removeClass('loading');
                }
            },
            error: function() {
                showMessage('Errore di connessione. Riprova.', 'error');
                $submitBtn.prop('disabled', false).removeClass('loading');
            }
        });
    }

    /**
     * Display quiz results
     */
    function displayResults(data) {
        // Hide quiz questions
        $('.quiz-form-wrapper').fadeOut(400, function() {
            // Populate results
            const $resultsGrid = $('.results-grid');
            $resultsGrid.empty();

            // Add top 10 breeds
            if (data.breeds && data.breeds.length > 0) {
                data.breeds.forEach(function(breed, index) {
                    const isTopMatch = index === 0;
                    const rankNumber = index + 1;

                    const breedHtml = `
                        <div class="breed-card ${isTopMatch ? 'top-match' : ''}" data-breed-id="${breed.id}">
                            <div class="breed-rank">${rankNumber}</div>
                            <div class="breed-image-container">
                                ${breed.image ?
                                    `<img src="${breed.image}" alt="${breed.name}" class="breed-image">` :
                                    `<div class="breed-image" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);"></div>`
                                }
                            </div>
                            <div class="breed-info">
                                <h3 class="breed-name">${breed.name}</h3>
                                ${breed.description ?
                                    `<p class="breed-description">${breed.description}</p>` :
                                    ''
                                }
                                <div class="breed-match">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                    ${breed.match_percentage}% Compatibilità
                                </div>
                                ${breed.url ?
                                    `<a href="${breed.url}" class="breed-link">
                                        Scopri di più
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 12h14M12 5l7 7-7 7"/>
                                        </svg>
                                    </a>` :
                                    ''
                                }
                            </div>
                        </div>
                    `;

                    $resultsGrid.append(breedHtml);
                });
            }

            // Add meticcio card
            const meticcioHtml = `
                <div class="breed-card meticcio-card">
                    <div class="breed-rank">❤️</div>
                    <div class="breed-image-container">
                        <div class="breed-image" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);"></div>
                    </div>
                    <div class="breed-info">
                        <h3 class="breed-name">Considera anche un Meticcio!</h3>
                        <p class="breed-description">I meticci sono cani unici, spesso più sani e con caratteristiche imprevedibili che li rendono compagni speciali. Dai un'occhiata ai meticci disponibili per l'adozione!</p>
                        <a href="/annunci?tipo=cani&razza=meticcio" class="breed-link">
                            Vedi meticci in adozione
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            `;
            $resultsGrid.append(meticcioHtml);

            // Show results section
            $('.quiz-results').fadeIn(400);

            // Scroll to results
            setTimeout(function() {
                const $results = $('.quiz-results');
                if ($results.length && $results.offset()) {
                    $('html, body').animate({
                        scrollTop: $results.offset().top - 100
                    }, 500);
                }
            }, 500);
        });
    }

    /**
     * Email results
     */
    function emailResults() {
        if (!quizResults) {
            showMessage('Nessun risultato disponibile.', 'error');
            return;
        }

        const $emailBtn = $('.btn-email');
        $emailBtn.prop('disabled', true).addClass('loading');

        $.ajax({
            url: caniincasaQuiz.ajaxUrl,
            type: 'POST',
            data: {
                action: 'email_quiz_results',
                nonce: $('#quiz_nonce').val(),
                results: JSON.stringify(quizResults)
            },
            success: function(response) {
                if (response.success) {
                    showMessage('Risultati inviati via email con successo!', 'success');
                } else {
                    showMessage(response.data.message || 'Errore durante l\'invio email.', 'error');
                }
                $emailBtn.prop('disabled', false).removeClass('loading');
            },
            error: function() {
                showMessage('Errore durante l\'invio email. Riprova.', 'error');
                $emailBtn.prop('disabled', false).removeClass('loading');
            }
        });
    }

    /**
     * Download PDF
     */
    function downloadPDF() {
        if (!quizResults) {
            showMessage('Nessun risultato disponibile.', 'error');
            return;
        }

        const $pdfBtn = $('.btn-pdf');
        $pdfBtn.prop('disabled', true).addClass('loading');

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = caniincasaQuiz.ajaxUrl;
        form.target = '_blank';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'download_quiz_pdf';
        form.appendChild(actionInput);

        const nonceInput = document.createElement('input');
        nonceInput.type = 'hidden';
        nonceInput.name = 'nonce';
        nonceInput.value = $('#quiz_nonce').val();
        form.appendChild(nonceInput);

        const resultsInput = document.createElement('input');
        resultsInput.type = 'hidden';
        resultsInput.name = 'results';
        resultsInput.value = JSON.stringify(quizResults);
        form.appendChild(resultsInput);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        setTimeout(function() {
            $pdfBtn.prop('disabled', false).removeClass('loading');
        }, 2000);
    }

    /**
     * Restart quiz
     */
    function restartQuiz() {
        // Reset form
        $('#quiz-form')[0].reset();

        // Reset state
        currentQuestion = 1;
        quizResults = null;

        // Hide results
        $('.quiz-results').fadeOut(400, function() {
            // Show quiz form
            $('.quiz-form-wrapper').fadeIn(400);

            // Reset display
            updateQuizDisplay();

            // Scroll to top
            const $container = $('.quiz-container');
            if ($container.length && $container.offset()) {
                $('html, body').animate({
                    scrollTop: $container.offset().top - 100
                }, 300);
            }
        });
    }

    /**
     * Social sharing
     */
    function initSocialSharing() {
        // WhatsApp share
        $('.share-btn-whatsapp').on('click', function(e) {
            e.preventDefault();
            const text = 'Ho appena scoperto le razze di cani più adatte a me su Caniincasa! Prova anche tu il quiz:';
            const url = window.location.href;
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`;
            window.open(whatsappUrl, '_blank');
        });

        // Facebook share
        $('.share-btn-facebook').on('click', function(e) {
            e.preventDefault();
            const url = window.location.href;
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            window.open(facebookUrl, '_blank', 'width=600,height=400');
        });
    }

    /**
     * Show message
     */
    function showMessage(message, type) {
        const $messagesContainer = $('.quiz-messages');

        // If container doesn't exist, create it
        if ($messagesContainer.length === 0) {
            $('.quiz-container').prepend('<div class="quiz-messages"></div>');
        }

        const $messages = $('.quiz-messages');

        $messages
            .removeClass('success error info')
            .addClass(type)
            .html(getMessageIcon(type) + '<span>' + message + '</span>')
            .slideDown(300);

        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(function() {
                $messages.slideUp(300);
            }, 5000);
        }

        // Scroll to message
        if ($messages.length && $messages.offset()) {
            $('html, body').animate({
                scrollTop: $messages.offset().top - 100
            }, 300);
        }
    }

    function getMessageIcon(type) {
        if (type === 'success') {
            return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>';
        } else if (type === 'error') {
            return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/></svg>';
        } else {
            return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>';
        }
    }

    /**
     * Animate option selection
     */
    function initOptionAnimations() {
        $('.quiz-option-card input[type="radio"]').on('change', function() {
            const $question = $(this).closest('.quiz-question');

            // Remove animation from all cards in this question
            $question.find('.quiz-option-card').removeClass('selected-animation');

            // Add animation to selected card
            $(this).closest('.quiz-option-card').addClass('selected-animation');
        });
    }

    /**
     * Initialize all functions
     */
    $(document).ready(function() {
        // Check if we're on quiz page
        if ($('.quiz-page').length && $('#quiz-form').length) {
            initQuizNavigation();
            initOptionAnimations();
            initSocialSharing();

            // Initialize display
            updateQuizDisplay();

            // Button event handlers
            $('.btn-email').on('click', function(e) {
                e.preventDefault();
                emailResults();
            });

            $('.btn-pdf').on('click', function(e) {
                e.preventDefault();
                downloadPDF();
            });

            $('.btn-restart').on('click', function(e) {
                e.preventDefault();
                restartQuiz();
            });
        }
    });

})(jQuery);
