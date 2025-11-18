<?php
/**
 * Template Name: Quiz Selezione Razza
 * Interactive quiz to find the perfect dog breed
 *
 * @package Caniincasa
 */

get_header();
?>

<main id="primary" class="site-main quiz-page">

    <!-- Hero Section -->
    <section class="quiz-hero">
        <div class="container">
            <h1 class="quiz-title">Trova la Razza Perfetta per Te</h1>
            <p class="quiz-subtitle">Rispondi a 9 semplici domande e scopri le razze più adatte al tuo stile di vita</p>
        </div>
    </section>

    <!-- Quiz Section -->
    <section class="quiz-section">
        <div class="container">
            <div class="quiz-wrapper">

                <!-- Progress Bar -->
                <div class="quiz-progress-wrapper">
                    <div class="quiz-progress-bar">
                        <div class="quiz-progress-fill" style="width: 11.11%;"></div>
                    </div>
                    <p class="quiz-progress-text">Domanda <span id="current-question">1</span> di 9</p>
                </div>

                <!-- Quiz Form -->
                <form id="quiz-form" class="quiz-form">

                    <!-- Question 1: Esperienza -->
                    <div class="quiz-question active" data-question="1">
                        <h2 class="question-title">Quale è la tua esperienza con i cani?</h2>
                        <p class="question-subtitle">Seleziona il livello che ti rappresenta meglio</p>
                        <div class="quiz-options">
                            <label class="quiz-option-card">
                                <input type="radio" name="esperienza" value="principiante" required>
                                <div class="option-content">
                                    <div class="option-icon">🌱</div>
                                    <h3>Principiante</h3>
                                    <p>È il mio primo cane o ho poca esperienza</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="esperienza" value="intermedia">
                                <div class="option-content">
                                    <div class="option-icon">🐕</div>
                                    <h3>Intermedia</h3>
                                    <p>Ho avuto cani in passato</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="esperienza" value="esperto">
                                <div class="option-content">
                                    <div class="option-icon">🏆</div>
                                    <h3>Esperto</h3>
                                    <p>Ho molta esperienza con razze diverse</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Question 2: Abitazione -->
                    <div class="quiz-question" data-question="2">
                        <h2 class="question-title">Dove vivi?</h2>
                        <p class="question-subtitle">Tipo di abitazione</p>
                        <div class="quiz-options">
                            <label class="quiz-option-card">
                                <input type="radio" name="abitazione" value="appartamento" required>
                                <div class="option-content">
                                    <div class="option-icon">🏢</div>
                                    <h3>Appartamento</h3>
                                    <p>In città, senza giardino</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="abitazione" value="casa_giardino">
                                <div class="option-content">
                                    <div class="option-icon">🏡</div>
                                    <h3>Casa con Giardino</h3>
                                    <p>Spazio esterno disponibile</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="abitazione" value="fattoria">
                                <div class="option-content">
                                    <div class="option-icon">🌾</div>
                                    <h3>Fattoria/Campagna</h3>
                                    <p>Molto spazio all'aperto</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Question 3: Tempo Disponibile -->
                    <div class="quiz-question" data-question="3">
                        <h2 class="question-title">Quanto tempo hai da dedicare al cane?</h2>
                        <p class="question-subtitle">Considera passeggiate, gioco e cura quotidiana</p>
                        <div class="quiz-options">
                            <label class="quiz-option-card">
                                <input type="radio" name="tempo" value="poco" required>
                                <div class="option-content">
                                    <div class="option-icon">⏱️</div>
                                    <h3>Poco Tempo</h3>
                                    <p>1-2 ore al giorno</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="tempo" value="medio">
                                <div class="option-content">
                                    <div class="option-icon">⏰</div>
                                    <h3>Tempo Moderato</h3>
                                    <p>2-4 ore al giorno</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="tempo" value="molto">
                                <div class="option-content">
                                    <div class="option-icon">⌚</div>
                                    <h3>Molto Tempo</h3>
                                    <p>4+ ore al giorno</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Question 4: Livello Attività -->
                    <div class="quiz-question" data-question="4">
                        <h2 class="question-title">Qual è il tuo livello di attività?</h2>
                        <p class="question-subtitle">Quanto sei attivo fisicamente?</p>
                        <div class="quiz-options">
                            <label class="quiz-option-card">
                                <input type="radio" name="attivita" value="sedentario" required>
                                <div class="option-content">
                                    <div class="option-icon">🛋️</div>
                                    <h3>Sedentario</h3>
                                    <p>Preferisco attività tranquille</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="attivita" value="moderato">
                                <div class="option-content">
                                    <div class="option-icon">🚶</div>
                                    <h3>Moderato</h3>
                                    <p>Passeggiate regolari e gioco</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="attivita" value="molto_attivo">
                                <div class="option-content">
                                    <div class="option-icon">🏃</div>
                                    <h3>Molto Attivo</h3>
                                    <p>Sport, corsa, escursioni</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Question 5: Bambini -->
                    <div class="quiz-question" data-question="5">
                        <h2 class="question-title">Ci sono bambini in casa?</h2>
                        <p class="question-subtitle">Il cane conviverà con bambini?</p>
                        <div class="quiz-options">
                            <label class="quiz-option-card">
                                <input type="radio" name="bambini" value="no" required>
                                <div class="option-content">
                                    <div class="option-icon">❌</div>
                                    <h3>No</h3>
                                    <p>Nessun bambino</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="bambini" value="piccoli">
                                <div class="option-content">
                                    <div class="option-icon">👶</div>
                                    <h3>Bambini Piccoli</h3>
                                    <p>Sotto i 6 anni</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="bambini" value="grandi">
                                <div class="option-content">
                                    <div class="option-icon">🧒</div>
                                    <h3>Bambini Grandi</h3>
                                    <p>Oltre i 6 anni</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Question 6: Altri Animali -->
                    <div class="quiz-question" data-question="6">
                        <h2 class="question-title">Hai altri animali in casa?</h2>
                        <p class="question-subtitle">Il cane dovrà convivere con altri pet?</p>
                        <div class="quiz-options">
                            <label class="quiz-option-card">
                                <input type="radio" name="animali" value="no" required>
                                <div class="option-content">
                                    <div class="option-icon">🚫</div>
                                    <h3>No</h3>
                                    <p>Nessun altro animale</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="animali" value="gatti">
                                <div class="option-content">
                                    <div class="option-icon">🐱</div>
                                    <h3>Gatti</h3>
                                    <p>Ho uno o più gatti</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="animali" value="cani">
                                <div class="option-content">
                                    <div class="option-icon">🐕</div>
                                    <h3>Altri Cani</h3>
                                    <p>Ho già uno o più cani</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Question 7: Clima -->
                    <div class="quiz-question" data-question="7">
                        <h2 class="question-title">In che clima vivi?</h2>
                        <p class="question-subtitle">Temperatura media nella tua zona</p>
                        <div class="quiz-options">
                            <label class="quiz-option-card">
                                <input type="radio" name="clima" value="freddo" required>
                                <div class="option-content">
                                    <div class="option-icon">❄️</div>
                                    <h3>Freddo</h3>
                                    <p>Inverni rigidi</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="clima" value="temperato">
                                <div class="option-content">
                                    <div class="option-icon">🌤️</div>
                                    <h3>Temperato</h3>
                                    <p>Clima mite</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="clima" value="caldo">
                                <div class="option-content">
                                    <div class="option-icon">☀️</div>
                                    <h3>Caldo</h3>
                                    <p>Estati molto calde</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Question 8: Manutenzione Pelo -->
                    <div class="quiz-question" data-question="8">
                        <h2 class="question-title">Quanta manutenzione del pelo puoi gestire?</h2>
                        <p class="question-subtitle">Toelettatura e spazzolatura</p>
                        <div class="quiz-options">
                            <label class="quiz-option-card">
                                <input type="radio" name="manutenzione" value="bassa" required>
                                <div class="option-content">
                                    <div class="option-icon">✂️</div>
                                    <h3>Bassa</h3>
                                    <p>Minima manutenzione</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="manutenzione" value="media">
                                <div class="option-content">
                                    <div class="option-icon">🪮</div>
                                    <h3>Media</h3>
                                    <p>Spazzolature regolari</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="manutenzione" value="alta">
                                <div class="option-content">
                                    <div class="option-icon">💇</div>
                                    <h3>Alta</h3>
                                    <p>Toelettatura frequente</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Question 9: Scopo -->
                    <div class="quiz-question" data-question="9">
                        <h2 class="question-title">Qual è lo scopo principale dell'adozione?</h2>
                        <p class="question-subtitle">Cosa ti aspetti dal tuo cane?</p>
                        <div class="quiz-options">
                            <label class="quiz-option-card">
                                <input type="radio" name="scopo" value="compagnia" required>
                                <div class="option-content">
                                    <div class="option-icon">❤️</div>
                                    <h3>Compagnia</h3>
                                    <p>Un amico fedele</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="scopo" value="guardia">
                                <div class="option-content">
                                    <div class="option-icon">🛡️</div>
                                    <h3>Guardia</h3>
                                    <p>Protezione della casa</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="scopo" value="sport">
                                <div class="option-content">
                                    <div class="option-icon">⚽</div>
                                    <h3>Sport</h3>
                                    <p>Attività sportive</p>
                                </div>
                            </label>
                            <label class="quiz-option-card">
                                <input type="radio" name="scopo" value="famiglia">
                                <div class="option-content">
                                    <div class="option-icon">👨‍👩‍👧‍👦</div>
                                    <h3>Famiglia</h3>
                                    <p>Cane per tutta la famiglia</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="quiz-navigation">
                        <button type="button" class="btn btn-outline quiz-prev" style="display: none;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            Indietro
                        </button>
                        <button type="button" class="btn btn-primary btn-lg quiz-next">
                            Avanti
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg quiz-submit" style="display: none;">
                            <span class="btn-text">Vedi Risultati</span>
                            <span class="btn-loading" style="display: none;">
                                <svg class="spinner" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
                                </svg>
                                Calcolo...
                            </span>
                        </button>
                    </div>

                    <?php wp_nonce_field( 'caniincasa_quiz', 'quiz_nonce' ); ?>

                </form>

                <!-- Results Section (initially hidden) -->
                <div id="quiz-results" class="quiz-results" style="display: none;">
                    <div class="results-header">
                        <h2 class="results-title">Le Razze Perfette per Te!</h2>
                        <p class="results-subtitle">Abbiamo trovato <span id="results-count">10</span> razze compatibili con il tuo profilo</p>
                    </div>

                    <div id="results-list" class="results-grid">
                        <!-- Results will be populated by JavaScript -->
                    </div>

                    <div class="results-actions">
                        <?php if ( is_user_logged_in() ) : ?>
                            <button type="button" class="btn btn-outline" id="email-results">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                Invia via Email
                            </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-primary" id="download-pdf">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Scarica PDF
                        </button>
                        <button type="button" class="btn btn-outline quiz-restart">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="1 4 1 10 7 10"></polyline>
                                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                            </svg>
                            Rifai il Quiz
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

</main>

<?php
get_footer();
