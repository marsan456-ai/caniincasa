<?php
/**
 * Template Name: Claim Struttura
 * Template per inserire/aggiornare dati di una struttura
 *
 * @package Caniincasa
 */

// Redirect if not logged in
if ( ! is_user_logged_in() ) {
    $redirect_url = add_query_arg( 'redirect_to', urlencode( $_SERVER['REQUEST_URI'] ), home_url( '/login' ) );
    wp_redirect( $redirect_url );
    exit;
}

// Get struttura ID and type from URL
$struttura_id = isset( $_GET['struttura_id'] ) ? intval( $_GET['struttura_id'] ) : 0;
$struttura_type = isset( $_GET['struttura_type'] ) ? sanitize_text_field( $_GET['struttura_type'] ) : '';

if ( ! $struttura_id || ! $struttura_type ) {
    wp_redirect( home_url() );
    exit;
}

$struttura = get_post( $struttura_id );

if ( ! $struttura || ! in_array( $struttura->post_type, array( 'allevamenti', 'veterinari', 'pensioni_per_cani', 'canili', 'centri_cinofili' ) ) ) {
    wp_redirect( home_url() );
    exit;
}

$current_user = wp_get_current_user();
$success_message = '';
$error_message = '';

// Handle form submission
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['submit_claim'] ) && wp_verify_nonce( $_POST['claim_nonce'], 'submit_struttura_claim' ) ) {

    // Collect form data
    $claim_data = array();

    // Common fields for all structures
    $claim_data['persona'] = sanitize_text_field( $_POST['persona'] ?? '' );
    $claim_data['indirizzo'] = sanitize_text_field( $_POST['indirizzo'] ?? '' );
    $claim_data['localita'] = sanitize_text_field( $_POST['localita'] ?? '' );
    $claim_data['provincia'] = sanitize_text_field( $_POST['provincia'] ?? '' );
    $claim_data['cap'] = sanitize_text_field( $_POST['cap'] ?? '' );
    $claim_data['telefono'] = sanitize_text_field( $_POST['telefono'] ?? '' );
    $claim_data['email'] = sanitize_email( $_POST['email'] ?? '' );
    $claim_data['sito_web'] = esc_url_raw( $_POST['sito_web'] ?? '' );

    // Type-specific fields
    if ( $struttura_type === 'allevamenti' ) {
        $claim_data['affisso'] = sanitize_text_field( $_POST['affisso'] ?? '' );
        $claim_data['proprietario'] = sanitize_text_field( $_POST['proprietario'] ?? '' );
        $claim_data['id_affisso'] = sanitize_text_field( $_POST['id_affisso'] ?? '' );
    }

    // Submit the claim
    $claim_id = caniincasa_submit_structure_claim( $struttura_id, $struttura_type, $claim_data );

    if ( $claim_id ) {
        $success_message = 'Richiesta inviata con successo! Sarà revisionata dagli amministratori.';
    } else {
        $error_message = 'Errore durante l\'invio della richiesta. Riprova più tardi.';
    }
}

get_header();
?>

<div class="claim-struttura-page">
    <div class="container">
        <div class="claim-content">

            <div class="claim-header">
                <h1><?php esc_html_e( 'Richiedi Aggiornamento Dati', 'caniincasa' ); ?></h1>
                <p class="claim-subtitle">
                    Stai richiedendo di aggiornare i dati per: <strong><?php echo esc_html( $struttura->post_title ); ?></strong>
                </p>
            </div>

            <?php if ( $success_message ) : ?>
                <div class="claim-message success">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <p><?php echo esc_html( $success_message ); ?></p>
                    <a href="<?php echo get_permalink( $struttura_id ); ?>" class="btn btn-primary">Torna alla Struttura</a>
                </div>
            <?php elseif ( $error_message ) : ?>
                <div class="claim-message error">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/>
                    </svg>
                    <p><?php echo esc_html( $error_message ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( ! $success_message ) : ?>

            <div class="claim-info-box">
                <h3><?php esc_html_e( 'Come funziona?', 'caniincasa' ); ?></h3>
                <ol>
                    <li>Compila il form con i dati aggiornati della struttura</li>
                    <li>La tua richiesta verrà inviata agli amministratori per la revisione</li>
                    <li>Una volta approvata, i dati saranno aggiornati sul sito</li>
                    <li>Riceverai una email di conferma</li>
                </ol>
            </div>

            <form method="post" class="claim-form">
                <?php wp_nonce_field( 'submit_struttura_claim', 'claim_nonce' ); ?>

                <h2><?php esc_html_e( 'Dati di Contatto', 'caniincasa' ); ?></h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="persona"><?php esc_html_e( 'Persona di Riferimento', 'caniincasa' ); ?> *</label>
                        <input type="text" id="persona" name="persona" required
                               value="<?php echo esc_attr( get_field( 'persona', $struttura_id ) ?? '' ); ?>"
                               placeholder="Nome e Cognome">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="indirizzo"><?php esc_html_e( 'Indirizzo', 'caniincasa' ); ?> *</label>
                        <input type="text" id="indirizzo" name="indirizzo" required
                               value="<?php echo esc_attr( get_field( 'indirizzo', $struttura_id ) ?? '' ); ?>"
                               placeholder="Via, Numero Civico">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="localita"><?php esc_html_e( 'Località', 'caniincasa' ); ?> *</label>
                        <input type="text" id="localita" name="localita" required
                               value="<?php echo esc_attr( get_field( 'localita', $struttura_id ) ?? '' ); ?>"
                               placeholder="Città">
                    </div>

                    <div class="form-group">
                        <label for="provincia"><?php esc_html_e( 'Provincia', 'caniincasa' ); ?> *</label>
                        <input type="text" id="provincia" name="provincia" required maxlength="2"
                               value="<?php echo esc_attr( get_field( 'provincia', $struttura_id ) ?? '' ); ?>"
                               placeholder="ES: MI">
                    </div>

                    <div class="form-group">
                        <label for="cap"><?php esc_html_e( 'CAP', 'caniincasa' ); ?> *</label>
                        <input type="text" id="cap" name="cap" required maxlength="5"
                               value="<?php echo esc_attr( get_field( 'cap', $struttura_id ) ?? '' ); ?>"
                               placeholder="20100">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="telefono"><?php esc_html_e( 'Telefono', 'caniincasa' ); ?> *</label>
                        <input type="tel" id="telefono" name="telefono" required
                               value="<?php echo esc_attr( get_field( 'telefono', $struttura_id ) ?? '' ); ?>"
                               placeholder="+39 333 1234567">
                    </div>

                    <div class="form-group">
                        <label for="email"><?php esc_html_e( 'Email', 'caniincasa' ); ?> *</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo esc_attr( get_field( 'email', $struttura_id ) ?? '' ); ?>"
                               placeholder="info@esempio.it">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="sito_web"><?php esc_html_e( 'Sito Web', 'caniincasa' ); ?></label>
                        <input type="url" id="sito_web" name="sito_web"
                               value="<?php echo esc_attr( get_field( 'sito_web', $struttura_id ) ?? '' ); ?>"
                               placeholder="https://www.esempio.it">
                    </div>
                </div>

                <?php if ( $struttura_type === 'allevamenti' ) : ?>
                    <h2><?php esc_html_e( 'Informazioni Allevamento', 'caniincasa' ); ?></h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="affisso"><?php esc_html_e( 'Affisso', 'caniincasa' ); ?></label>
                            <input type="text" id="affisso" name="affisso"
                                   value="<?php echo esc_attr( get_field( 'affisso', $struttura_id ) ?? '' ); ?>"
                                   placeholder="Nome Affisso">
                        </div>

                        <div class="form-group">
                            <label for="proprietario"><?php esc_html_e( 'Proprietario', 'caniincasa' ); ?></label>
                            <input type="text" id="proprietario" name="proprietario"
                                   value="<?php echo esc_attr( get_field( 'proprietario', $struttura_id ) ?? '' ); ?>"
                                   placeholder="Nome Proprietario">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="id_affisso"><?php esc_html_e( 'ID Affisso ENCI', 'caniincasa' ); ?></label>
                            <input type="text" id="id_affisso" name="id_affisso"
                                   value="<?php echo esc_attr( get_field( 'id_affisso', $struttura_id ) ?? '' ); ?>"
                                   placeholder="ID ENCI">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" name="submit_claim" class="btn btn-primary">
                        <?php esc_html_e( 'Invia Richiesta', 'caniincasa' ); ?>
                    </button>
                    <a href="<?php echo get_permalink( $struttura_id ); ?>" class="btn btn-outline">
                        <?php esc_html_e( 'Annulla', 'caniincasa' ); ?>
                    </a>
                </div>
            </form>

            <?php endif; ?>

        </div>
    </div>
</div>

<style>
.claim-struttura-page {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 60px 0;
}

.claim-content {
    max-width: 800px;
    margin: 0 auto;
}

.claim-header {
    text-align: center;
    margin-bottom: 40px;
}

.claim-header h1 {
    font-size: 2.5rem;
    color: #2d3748;
    margin-bottom: 10px;
}

.claim-subtitle {
    font-size: 1.1rem;
    color: #64748b;
}

.claim-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    text-align: center;
}

.claim-message.success {
    background: #d4edda;
    border: 2px solid #28a745;
    color: #155724;
}

.claim-message.error {
    background: #f8d7da;
    border: 2px solid #dc3545;
    color: #721c24;
}

.claim-message svg {
    width: 48px;
    height: 48px;
}

.claim-info-box {
    background: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 30px;
    border-left: 4px solid #FF6B35;
}

.claim-info-box h3 {
    margin-top: 0;
    color: #2d3748;
}

.claim-info-box ol {
    margin: 16px 0 0 20px;
    color: #64748b;
}

.claim-info-box li {
    margin-bottom: 8px;
}

.claim-form {
    background: white;
    padding: 32px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.claim-form h2 {
    font-size: 1.5rem;
    color: #2d3748;
    margin-top: 0;
    margin-bottom: 24px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-row:has(.form-group:nth-child(2)) {
    grid-template-columns: repeat(2, 1fr);
}

.form-row:has(.form-group:nth-child(3)) {
    grid-template-columns: repeat(3, 1fr);
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 12px;
    border: 1px solid #cbd5e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #FF6B35;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
}

.form-actions {
    display: flex;
    gap: 16px;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e2e8f0;
}

.btn {
    padding: 12px 32px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
    border: 2px solid transparent;
}

.btn-primary {
    background: #FF6B35;
    color: white;
    border-color: #FF6B35;
}

.btn-primary:hover {
    background: #e55a2a;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
}

.btn-outline {
    background: white;
    color: #64748b;
    border-color: #cbd5e0;
}

.btn-outline:hover {
    background: #f8f9fa;
    border-color: #94a3b8;
}

@media (max-width: 768px) {
    .claim-struttura-page {
        padding: 40px 0;
    }

    .claim-header h1 {
        font-size: 2rem;
    }

    .claim-form {
        padding: 24px;
    }

    .form-row:has(.form-group:nth-child(2)),
    .form-row:has(.form-group:nth-child(3)) {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<?php get_footer(); ?>
