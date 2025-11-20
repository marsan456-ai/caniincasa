<?php
/**
 * Template Name: Pubblica Annuncio
 * Template for publishing ads from frontend
 *
 * @package Caniincasa
 */

// Redirect to custom login page if not logged in
if ( ! is_user_logged_in() ) {
    $redirect_to = get_permalink();
    $login_url = home_url( '/login' );
    $login_url = add_query_arg( 'redirect_to', urlencode( $redirect_to ), $login_url );
    wp_redirect( $login_url );
    exit;
}

get_header();

$current_user = wp_get_current_user();
$user_id      = $current_user->ID;

// Check if editing existing post
$edit_mode = false;
$edit_post = null;
$edit_post_id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;

if ( $edit_post_id ) {
    $edit_post = get_post( $edit_post_id );

    // Verify post exists and user owns it
    if ( $edit_post && $edit_post->post_author == $user_id ) {
        $edit_mode = true;
    } else {
        // Unauthorized - redirect to dashboard
        wp_redirect( home_url( '/dashboard' ) );
        exit;
    }
}

// Get user data for pre-fill
$user_phone = get_user_meta( $user_id, 'phone', true );
$user_city  = get_user_meta( $user_id, 'city', true );
$user_provincia = get_user_meta( $user_id, 'provincia', true );
?>

<main id="main-content" class="site-main pubblica-annuncio-page">

    <!-- Hero Section -->
    <div class="annuncio-hero">
        <div class="container">
            <h1 class="page-title"><?php echo $edit_mode ? 'Modifica Annuncio' : 'Pubblica un Annuncio'; ?></h1>
            <p class="page-subtitle"><?php echo $edit_mode ? 'Aggiorna le informazioni del tuo annuncio' : 'Scegli il tipo di annuncio che vuoi pubblicare'; ?></p>
        </div>
    </div>

    <!-- Content Area -->
    <div class="container">
        <div class="annuncio-content-wrapper">

            <!-- Type Selection -->
            <div class="annuncio-type-selection<?php echo $edit_mode ? ' hidden' : ''; ?>" id="type-selection">
                <div class="type-cards">
                    <div class="type-card" data-type="4zampe">
                        <div class="type-icon">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                        <h3>Annuncio 4 Zampe</h3>
                        <p>Cerco o offro un cane in adozione</p>
                        <button class="btn btn-primary">Seleziona</button>
                    </div>

                    <div class="type-card" data-type="dogsitter">
                        <div class="type-icon">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                        <h3>Annuncio Dogsitter</h3>
                        <p>Cerco o offro servizi di dogsitting</p>
                        <button class="btn btn-primary">Seleziona</button>
                    </div>
                </div>
            </div>

            <!-- Form 4 Zampe -->
            <div class="annuncio-form-container<?php echo ($edit_mode && $edit_post->post_type === '4zampe') ? '' : ' hidden'; ?>" id="form-4zampe">
                <?php if ( ! $edit_mode ) : ?>
                <button class="btn-back" id="back-from-4zampe">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                    Torna Indietro
                </button>
                <?php endif; ?>

                <h2 class="form-title"><?php echo $edit_mode ? 'Modifica Annuncio 4 Zampe' : 'Pubblica Annuncio 4 Zampe'; ?></h2>

                <form id="annuncio-4zampe-form" class="annuncio-form">
                    <?php wp_nonce_field( 'submit_annuncio_4zampe', 'annuncio_4zampe_nonce' ); ?>
                    <?php if ( $edit_mode ) : ?>
                        <input type="hidden" name="edit_post_id" value="<?php echo esc_attr( $edit_post_id ); ?>">
                    <?php endif; ?>

                    <div class="form-section">
                        <h3 class="section-title">Informazioni Generali</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="titolo_4zampe">Titolo Annuncio <span class="required">*</span></label>
                                <input type="text" id="titolo_4zampe" name="titolo" required placeholder="Es: Cerco cucciolo di Golden Retriever">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descrizione_4zampe">Descrizione <span class="required">*</span></label>
                            <textarea id="descrizione_4zampe" name="descrizione" rows="6" required placeholder="Descrivi in dettaglio il tuo annuncio..."></textarea>
                            <span class="help-text">Minimo 50 caratteri</span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="tipo_annuncio">Tipo Annuncio <span class="required">*</span></label>
                                <select id="tipo_annuncio" name="tipo_annuncio" required>
                                    <option value="">Seleziona...</option>
                                    <option value="cerco">Cerco</option>
                                    <option value="offro">Offro</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="eta">Età <span class="required">*</span></label>
                                <select id="eta" name="eta" required>
                                    <option value="">Seleziona...</option>
                                    <option value="cucciolo">Cucciolo</option>
                                    <option value="adulto">Adulto</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="tipo_cane">Tipo Cane <span class="required">*</span></label>
                                <select id="tipo_cane" name="tipo_cane" required>
                                    <option value="">Seleziona...</option>
                                    <option value="meticcio">Meticcio</option>
                                    <option value="razza">Razza</option>
                                </select>
                            </div>

                            <div class="form-group hidden" id="razza-group">
                                <label for="razza">Razza <span class="required">*</span></label>
                                <select id="razza" name="razza">
                                    <option value="">Seleziona razza...</option>
                                    <?php
                                    $razze = get_posts( array(
                                        'post_type'      => 'razze_di_cani',
                                        'posts_per_page' => -1,
                                        'orderby'        => 'title',
                                        'order'          => 'ASC',
                                    ) );
                                    foreach ( $razze as $razza ) :
                                        ?>
                                        <option value="<?php echo esc_attr( $razza->ID ); ?>">
                                            <?php echo esc_html( $razza->post_title ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Contatti</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono_4zampe">Telefono</label>
                                <input type="tel" id="telefono_4zampe" name="telefono" value="<?php echo esc_attr( $user_phone ); ?>" placeholder="+39 333 1234567">
                            </div>

                            <div class="form-group">
                                <label for="contatto_preferito">Contatto Preferito</label>
                                <select id="contatto_preferito" name="contatto_preferito">
                                    <option value="email">Email</option>
                                    <option value="telefono">Telefono</option>
                                    <option value="whatsapp">WhatsApp</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="citta_4zampe">Città</label>
                                <input type="text" id="citta_4zampe" name="citta" value="<?php echo esc_attr( $user_city ); ?>" placeholder="Roma">
                            </div>

                            <div class="form-group">
                                <label for="provincia_4zampe">Provincia</label>
                                <select id="provincia_4zampe" name="provincia">
                                    <option value="">Seleziona provincia</option>
                                    <?php
                                    $province = get_terms( array(
                                        'taxonomy'   => 'provincia',
                                        'hide_empty' => false,
                                        'orderby'    => 'name',
                                        'order'      => 'ASC',
                                    ) );
                                    foreach ( $province as $prov ) :
                                        $selected = ( $user_provincia === $prov->slug ) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo esc_attr( $prov->term_id ); ?>" <?php echo $selected; ?>>
                                            <?php echo esc_html( $prov->name ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Opzioni Annuncio</h3>

                        <div class="form-group">
                            <label for="giorni_scadenza_4zampe">Validità Annuncio</label>
                            <select id="giorni_scadenza_4zampe" name="giorni_scadenza">
                                <option value="30">30 giorni</option>
                                <option value="60">60 giorni</option>
                                <option value="90">90 giorni</option>
                            </select>
                            <span class="help-text">L'annuncio sarà rimosso automaticamente dopo il periodo selezionato</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                            </svg>
                            Pubblica Annuncio
                        </button>
                        <span class="form-note">Il tuo annuncio sarà pubblicato dopo la verifica del nostro team</span>
                    </div>
                </form>
            </div>

            <!-- Form Dogsitter -->
            <div class="annuncio-form-container<?php echo ($edit_mode && $edit_post->post_type === 'dogsitter') ? '' : ' hidden'; ?>" id="form-dogsitter">
                <?php if ( ! $edit_mode ) : ?>
                <button class="btn-back" id="back-from-dogsitter">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                    </svg>
                    Torna Indietro
                </button>
                <?php endif; ?>

                <h2 class="form-title"><?php echo $edit_mode ? 'Modifica Annuncio Dogsitter' : 'Pubblica Annuncio Dogsitter'; ?></h2>

                <form id="annuncio-dogsitter-form" class="annuncio-form">
                    <?php wp_nonce_field( 'submit_annuncio_dogsitter', 'annuncio_dogsitter_nonce' ); ?>
                    <?php if ( $edit_mode ) : ?>
                        <input type="hidden" name="edit_post_id" value="<?php echo esc_attr( $edit_post_id ); ?>">
                    <?php endif; ?>

                    <div class="form-section">
                        <h3 class="section-title">Informazioni Generali</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="titolo_dogsitter">Titolo Annuncio <span class="required">*</span></label>
                                <input type="text" id="titolo_dogsitter" name="titolo" required placeholder="Es: Dogsitter professionista disponibile">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descrizione_dogsitter">Descrizione <span class="required">*</span></label>
                            <textarea id="descrizione_dogsitter" name="descrizione" rows="6" required placeholder="Descrivi il tuo servizio o la tua ricerca..."></textarea>
                            <span class="help-text">Minimo 50 caratteri</span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="tipo_servizio">Tipo <span class="required">*</span></label>
                                <select id="tipo_servizio" name="tipo" required>
                                    <option value="">Seleziona...</option>
                                    <option value="cerco">Cerco Dogsitter</option>
                                    <option value="offro">Offro Servizio Dogsitter</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="esperienza">Esperienza</label>
                                <select id="esperienza" name="esperienza">
                                    <option value="">Seleziona...</option>
                                    <option value="principiante">Principiante</option>
                                    <option value="intermedio">Intermedio</option>
                                    <option value="esperto">Esperto</option>
                                    <option value="professionale">Professionale</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Servizi Offerti</label>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="servizi_offerti[]" value="passeggiate">
                                    <span>Passeggiate</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="servizi_offerti[]" value="pensione">
                                    <span>Pensione</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="servizi_offerti[]" value="visita_domicilio">
                                    <span>Visita a Domicilio</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="servizi_offerti[]" value="toelettatura">
                                    <span>Toelettatura</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="servizi_offerti[]" value="addestramento">
                                    <span>Addestramento Base</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="disponibilita">Disponibilità</label>
                                <textarea id="disponibilita" name="disponibilita" rows="3" placeholder="Es: Lun-Ven 9-18, Weekend su richiesta"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="prezzo_indicativo">Prezzo Indicativo</label>
                                <input type="text" id="prezzo_indicativo" name="prezzo_indicativo" placeholder="Es: 15€/ora - 30€/giorno">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Contatti e Località</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono_dogsitter">Telefono</label>
                                <input type="tel" id="telefono_dogsitter" name="telefono" value="<?php echo esc_attr( $user_phone ); ?>" placeholder="+39 333 1234567">
                            </div>

                            <div class="form-group">
                                <label for="citta_dogsitter">Città</label>
                                <input type="text" id="citta_dogsitter" name="citta" value="<?php echo esc_attr( $user_city ); ?>" placeholder="Roma">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="provincia_dogsitter">Provincia</label>
                            <select id="provincia_dogsitter" name="provincia">
                                <option value="">Seleziona provincia</option>
                                <?php
                                foreach ( $province as $prov ) :
                                    $selected = ( $user_provincia === $prov->slug ) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo esc_attr( $prov->term_id ); ?>" <?php echo $selected; ?>>
                                        <?php echo esc_html( $prov->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                            </svg>
                            Pubblica Annuncio
                        </button>
                        <span class="form-note">Il tuo annuncio sarà pubblicato dopo la verifica del nostro team</span>
                    </div>
                </form>
            </div>

        </div>
    </div>

</main>

<?php if ( $edit_mode && $edit_post ) : ?>
<script>
// Pre-fill form with existing post data in edit mode
document.addEventListener('DOMContentLoaded', function() {
    const editData = {
        title: <?php echo json_encode( $edit_post->post_title ); ?>,
        content: <?php echo json_encode( $edit_post->post_content ); ?>,
        postType: <?php echo json_encode( $edit_post->post_type ); ?>,
        meta: <?php echo json_encode( get_post_meta( $edit_post_id ) ); ?>
    };

    // Determine which form to use
    const formId = editData.postType === '4zampe' ? 'annuncio-4zampe-form' : 'annuncio-dogsitter-form';
    const form = document.getElementById(formId);

    if (form) {
        // Pre-fill title field
        const titleField = form.querySelector('[name="titolo"]');
        if (titleField) titleField.value = editData.title;

        // Pre-fill description/content field
        const descField = form.querySelector('[name="descrizione"]');
        if (descField) descField.value = editData.content;

        // Pre-fill meta fields
        for (const [key, value] of Object.entries(editData.meta)) {
            const field = form.querySelector('[name="' + key + '"]');
            if (field) {
                const metaValue = Array.isArray(value) && value.length > 0 ? value[0] : value;
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = (metaValue === '1' || metaValue === 'yes' || metaValue === field.value);
                } else {
                    field.value = metaValue;
                }
            }
        }

        // Change submit button text
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            const btnText = submitBtn.querySelector('span') || submitBtn;
            btnText.textContent = 'Aggiorna Annuncio';
        }
    }
});
</script>
<?php endif; ?>

<?php
get_footer();
