<?php
/**
 * User Dashboard Template
 *
 * @package    Pawstars
 * @subpackage Pawstars/public/templates
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_user_logged_in() ) {
    echo '<div class="pawstars-login-required">';
    echo '<p>' . esc_html__( 'Devi essere loggato per accedere alla dashboard.', 'pawstars' ) . '</p>';
    echo '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="btn btn-primary">' . esc_html__( 'Accedi', 'pawstars' ) . '</a>';
    echo '</div>';
    return;
}

$plugin = pawstars();
$user_id = get_current_user_id();
$user = wp_get_current_user();
$dogs = $plugin->dog_profile->get_user_dogs( $user_id );
$user_vote_stats = $plugin->database->get_user_vote_stats( $user_id );
$user_achievements = $plugin->database->get_achievements( 'user', $user_id );
$badges = $plugin->achievements->get_badges();

$breeds = Pawstars_Integrations::get_breeds();
$province = Pawstars_Integrations::get_province();

$settings = get_option( 'pawstars_settings', array() );
$max_dogs = isset( $settings['max_dogs_per_user'] ) ? $settings['max_dogs_per_user'] : 5;
$can_add_more = count( $dogs ) < $max_dogs;
?>

<div class="pawstars-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="user-info">
            <?php echo get_avatar( $user_id, 60 ); ?>
            <div class="user-details">
                <h2><?php echo esc_html( $user->display_name ); ?></h2>
                <p><?php printf( esc_html__( '%d cani registrati', 'pawstars' ), count( $dogs ) ); ?></p>
            </div>
        </div>

        <div class="user-stats">
            <div class="stat-item">
                <span class="stat-value"><?php echo esc_html( $user_vote_stats['total'] ); ?></span>
                <span class="stat-label"><?php esc_html_e( 'Voti Dati', 'pawstars' ); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?php echo esc_html( $user_vote_stats['stars'] ); ?></span>
                <span class="stat-label"><?php esc_html_e( 'Star Date', 'pawstars' ); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?php echo count( $user_achievements ); ?></span>
                <span class="stat-label"><?php esc_html_e( 'Badge', 'pawstars' ); ?></span>
            </div>
        </div>
    </div>

    <!-- User Badges -->
    <?php if ( ! empty( $user_achievements ) ) : ?>
        <div class="dashboard-section user-badges">
            <h3><?php esc_html_e( 'I Tuoi Badge', 'pawstars' ); ?></h3>
            <div class="badges-list">
                <?php foreach ( $user_achievements as $achievement ) : ?>
                    <?php $badge = isset( $badges[ $achievement->badge_slug ] ) ? $badges[ $achievement->badge_slug ] : null; ?>
                    <?php if ( $badge ) : ?>
                        <div class="badge-item" title="<?php echo esc_attr( $badge['description'] ); ?>">
                            <span class="badge-icon"><?php echo esc_html( $plugin->achievements->get_badge_icon_html( $achievement->badge_slug ) ); ?></span>
                            <span class="badge-name"><?php echo esc_html( $badge['name'] ); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- My Dogs -->
    <div class="dashboard-section my-dogs">
        <div class="section-header">
            <h3><?php esc_html_e( 'I Miei Cani', 'pawstars' ); ?></h3>
            <?php if ( $can_add_more ) : ?>
                <button class="btn btn-primary btn-add-dog" id="showCreateForm">
                    + <?php esc_html_e( 'Aggiungi Cane', 'pawstars' ); ?>
                </button>
            <?php else : ?>
                <span class="limit-notice"><?php printf( esc_html__( 'Limite raggiunto (%d/%d)', 'pawstars' ), count( $dogs ), $max_dogs ); ?></span>
            <?php endif; ?>
        </div>

        <?php if ( ! empty( $dogs ) ) : ?>
            <div class="my-dogs-list">
                <?php foreach ( $dogs as $dog ) : ?>
                    <div class="my-dog-item" data-dog-id="<?php echo esc_attr( $dog->id ); ?>">
                        <div class="dog-image">
                            <?php if ( $dog->image_url ) : ?>
                                <img src="<?php echo esc_url( $dog->image_url ); ?>" alt="<?php echo esc_attr( $dog->name ); ?>">
                            <?php else : ?>
                                <div class="no-image">🐕</div>
                            <?php endif; ?>
                        </div>

                        <div class="dog-info">
                            <h4><?php echo esc_html( $dog->name ); ?></h4>
                            <?php if ( $dog->breed_name ) : ?>
                                <span class="dog-breed"><?php echo esc_html( $dog->breed_name ); ?></span>
                            <?php endif; ?>
                            <div class="dog-stats-mini">
                                <span>⭐ <?php echo esc_html( $dog->total_points ); ?></span>
                            </div>
                        </div>

                        <div class="dog-status">
                            <?php
                            $status_labels = array(
                                'pending'  => '<span class="status pending">' . __( 'In Attesa', 'pawstars' ) . '</span>',
                                'active'   => '<span class="status active">' . __( 'Attivo', 'pawstars' ) . '</span>',
                                'rejected' => '<span class="status rejected">' . __( 'Rifiutato', 'pawstars' ) . '</span>',
                            );
                            echo wp_kses_post( $status_labels[ $dog->status ] ?? '' );
                            ?>
                        </div>

                        <div class="dog-actions">
                            <?php if ( $dog->status === 'active' ) : ?>
                                <a href="?dog=<?php echo esc_attr( $dog->id ); ?>" class="btn btn-sm btn-view"><?php esc_html_e( 'Vedi', 'pawstars' ); ?></a>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-edit" data-dog-id="<?php echo esc_attr( $dog->id ); ?>"><?php esc_html_e( 'Modifica', 'pawstars' ); ?></button>
                            <button class="btn btn-sm btn-delete" data-dog-id="<?php echo esc_attr( $dog->id ); ?>"><?php esc_html_e( 'Elimina', 'pawstars' ); ?></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="no-dogs">
                <div class="empty-icon">🐕</div>
                <p><?php esc_html_e( 'Non hai ancora aggiunto cani. Inizia ora!', 'pawstars' ); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Create Dog Form -->
    <div class="dashboard-section create-dog-form hidden" id="createDogForm">
        <h3><?php esc_html_e( 'Aggiungi il Tuo Cane', 'pawstars' ); ?></h3>

        <form id="pawstarsCreateDogForm" class="pawstars-form" enctype="multipart/form-data">
            <?php wp_nonce_field( 'pawstars_nonce', 'pawstars_nonce' ); ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="dog_name"><?php esc_html_e( 'Nome del Cane', 'pawstars' ); ?> *</label>
                    <input type="text" id="dog_name" name="name" required maxlength="100">
                </div>

                <div class="form-group">
                    <label for="dog_breed"><?php esc_html_e( 'Razza', 'pawstars' ); ?></label>
                    <select id="dog_breed" name="breed_id">
                        <option value=""><?php esc_html_e( 'Seleziona razza', 'pawstars' ); ?></option>
                        <?php foreach ( $breeds as $breed ) : ?>
                            <option value="<?php echo esc_attr( $breed['id'] ); ?>"><?php echo esc_html( $breed['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dog_birth_date"><?php esc_html_e( 'Data di Nascita', 'pawstars' ); ?></label>
                    <input type="date" id="dog_birth_date" name="birth_date" max="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
                </div>

                <div class="form-group">
                    <label for="dog_provincia"><?php esc_html_e( 'Provincia', 'pawstars' ); ?></label>
                    <select id="dog_provincia" name="provincia">
                        <option value=""><?php esc_html_e( 'Seleziona provincia', 'pawstars' ); ?></option>
                        <?php foreach ( $province as $code => $name ) : ?>
                            <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="dog_bio"><?php esc_html_e( 'Descrizione', 'pawstars' ); ?></label>
                <textarea id="dog_bio" name="bio" rows="4" maxlength="500" placeholder="<?php esc_attr_e( 'Racconta qualcosa sul tuo cane...', 'pawstars' ); ?>"></textarea>
            </div>

            <div class="form-group">
                <label><?php esc_html_e( 'Foto Principale', 'pawstars' ); ?></label>
                <div class="photo-upload-area" id="photoUploadArea">
                    <input type="file" id="dog_photo" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden">
                    <div class="upload-placeholder">
                        <span class="upload-icon">📷</span>
                        <p><?php esc_html_e( 'Clicca o trascina una foto', 'pawstars' ); ?></p>
                        <span class="upload-hint"><?php esc_html_e( 'JPG, PNG o WebP (max 5MB)', 'pawstars' ); ?></span>
                    </div>
                    <div class="upload-preview hidden">
                        <img src="" alt="Preview">
                        <button type="button" class="remove-photo">✕</button>
                    </div>
                </div>
                <input type="hidden" id="featured_image_id" name="featured_image_id" value="">
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="cancelCreate"><?php esc_html_e( 'Annulla', 'pawstars' ); ?></button>
                <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Crea Profilo', 'pawstars' ); ?></button>
            </div>
        </form>
    </div>
</div>
