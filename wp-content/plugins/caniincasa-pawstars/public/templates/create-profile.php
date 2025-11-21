<?php
/**
 * Create Profile Template
 *
 * @package    Pawstars
 * @subpackage Pawstars/public/templates
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_user_logged_in() ) {
    return;
}

$breeds = Pawstars_Integrations::get_breeds();
$province = Pawstars_Integrations::get_province();
?>

<div class="pawstars-create-profile" id="pawstars-create">
    <div class="create-profile-header">
        <h2><?php esc_html_e( 'Aggiungi il Tuo Cane a Paw Stars', 'pawstars' ); ?></h2>
        <p><?php esc_html_e( 'Compila il form per creare il profilo del tuo amico a quattro zampe!', 'pawstars' ); ?></p>
    </div>

    <form id="pawstarsCreateProfile" class="pawstars-form" enctype="multipart/form-data">
        <?php wp_nonce_field( 'pawstars_nonce', 'pawstars_nonce' ); ?>

        <div class="form-section">
            <h3><?php esc_html_e( 'Informazioni Base', 'pawstars' ); ?></h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="create_name"><?php esc_html_e( 'Nome del Cane', 'pawstars' ); ?> <span class="required">*</span></label>
                    <input type="text" id="create_name" name="name" required maxlength="100" placeholder="<?php esc_attr_e( 'Es. Luna, Max, Bella...', 'pawstars' ); ?>">
                </div>

                <div class="form-group">
                    <label for="create_breed"><?php esc_html_e( 'Razza', 'pawstars' ); ?></label>
                    <select id="create_breed" name="breed_id">
                        <option value=""><?php esc_html_e( 'Seleziona razza...', 'pawstars' ); ?></option>
                        <?php foreach ( $breeds as $breed ) : ?>
                            <option value="<?php echo esc_attr( $breed['id'] ); ?>"><?php echo esc_html( $breed['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="create_birth_date"><?php esc_html_e( 'Data di Nascita', 'pawstars' ); ?></label>
                    <input type="date" id="create_birth_date" name="birth_date" max="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
                </div>

                <div class="form-group">
                    <label for="create_provincia"><?php esc_html_e( 'Provincia', 'pawstars' ); ?></label>
                    <select id="create_provincia" name="provincia">
                        <option value=""><?php esc_html_e( 'Seleziona provincia...', 'pawstars' ); ?></option>
                        <?php foreach ( $province as $code => $name ) : ?>
                            <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3><?php esc_html_e( 'Descrizione', 'pawstars' ); ?></h3>

            <div class="form-group">
                <label for="create_bio"><?php esc_html_e( 'Racconta qualcosa del tuo cane', 'pawstars' ); ?></label>
                <textarea id="create_bio" name="bio" rows="4" maxlength="500" placeholder="<?php esc_attr_e( 'Carattere, abitudini, curiosità...', 'pawstars' ); ?>"></textarea>
                <span class="char-counter"><span id="bioCharCount">0</span>/500</span>
            </div>
        </div>

        <div class="form-section">
            <h3><?php esc_html_e( 'Foto', 'pawstars' ); ?></h3>

            <div class="form-group">
                <label><?php esc_html_e( 'Foto Principale', 'pawstars' ); ?> <span class="required">*</span></label>
                <div class="photo-upload-zone" id="createPhotoZone">
                    <input type="file" id="create_photo" name="photo" accept="image/jpeg,image/png,image/webp" class="upload-input">
                    <div class="upload-content">
                        <div class="upload-icon">📷</div>
                        <p class="upload-text"><?php esc_html_e( 'Clicca o trascina una foto qui', 'pawstars' ); ?></p>
                        <span class="upload-hint"><?php esc_html_e( 'JPG, PNG o WebP - Max 5MB', 'pawstars' ); ?></span>
                    </div>
                    <div class="upload-preview">
                        <img src="" alt="Preview">
                        <button type="button" class="remove-preview">✕ <?php esc_html_e( 'Rimuovi', 'pawstars' ); ?></button>
                    </div>
                </div>
                <input type="hidden" id="create_featured_image_id" name="featured_image_id" value="">
            </div>
        </div>

        <div class="form-notice">
            <p>📋 <?php esc_html_e( 'Il profilo sarà revisionato prima della pubblicazione. Riceverai una email di conferma.', 'pawstars' ); ?></p>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">
                <?php esc_html_e( 'Crea Profilo', 'pawstars' ); ?>
            </button>
        </div>
    </form>
</div>
