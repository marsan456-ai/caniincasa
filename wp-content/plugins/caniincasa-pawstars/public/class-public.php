<?php
/**
 * Public Functionality
 *
 * Handles public-facing functionality.
 *
 * @package    Pawstars
 * @subpackage Pawstars/public
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Public Class
 *
 * @since 1.0.0
 */
class Pawstars_Public {

    /**
     * Plugin instance
     *
     * @var Caniincasa_Pawstars
     */
    private $plugin;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param Caniincasa_Pawstars $plugin Plugin instance
     */
    public function __construct( $plugin ) {
        $this->plugin = $plugin;
    }

    /**
     * Get template part
     *
     * @since 1.0.0
     * @param string $template Template name
     * @param array  $args     Arguments to pass
     */
    public function get_template( $template, $args = array() ) {
        $file = PAWSTARS_PLUGIN_DIR . 'public/templates/' . $template . '.php';

        if ( file_exists( $file ) ) {
            extract( $args );
            include $file;
        }
    }

    /**
     * Render dog card
     *
     * @since 1.0.0
     * @param object $dog  Dog object
     * @param string $mode Display mode (card, swipe)
     */
    public function render_dog_card( $dog, $mode = 'card' ) {
        $user_id = get_current_user_id();
        $user_votes = $user_id ? $this->plugin->voting->get_user_votes( $dog->id, $user_id ) : array();
        $vote_stats = $this->plugin->database->get_dog_vote_stats( $dog->id );

        include PAWSTARS_PLUGIN_DIR . 'public/templates/partials/dog-card.php';
    }

    /**
     * Render reactions buttons
     *
     * @since 1.0.0
     * @param int   $dog_id     Dog ID
     * @param array $user_votes User's existing votes
     */
    public function render_reactions( $dog_id, $user_votes = array() ) {
        $reactions = $this->plugin->voting->get_reactions();
        ?>
        <div class="pawstars-reactions" data-dog-id="<?php echo esc_attr( $dog_id ); ?>">
            <?php foreach ( $reactions as $type => $reaction ) : ?>
                <button
                    class="reaction-btn <?php echo in_array( $type, $user_votes ) ? 'voted' : ''; ?>"
                    data-reaction="<?php echo esc_attr( $type ); ?>"
                    title="<?php echo esc_attr( $reaction['label'] ); ?>"
                    <?php echo in_array( $type, $user_votes ) ? 'disabled' : ''; ?>
                >
                    <span class="reaction-emoji"><?php echo esc_html( $reaction['emoji'] ); ?></span>
                </button>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render leaderboard
     *
     * @since 1.0.0
     * @param string $type   Leaderboard type
     * @param int    $limit  Number of dogs
     * @param mixed  $filter Optional filter
     */
    public function render_leaderboard( $type = 'hot', $limit = 10, $filter = null ) {
        $dogs = $this->plugin->leaderboard->get( $type, $limit, $filter );

        include PAWSTARS_PLUGIN_DIR . 'public/templates/partials/leaderboard.php';
    }

    /**
     * Render create form
     *
     * @since 1.0.0
     */
    public function render_create_form() {
        if ( ! is_user_logged_in() ) {
            echo '<p class="pawstars-login-notice">';
            printf(
                wp_kses(
                    __( '<a href="%s">Accedi</a> per creare un profilo per il tuo cane.', 'pawstars' ),
                    array( 'a' => array( 'href' => array() ) )
                ),
                esc_url( wp_login_url( get_permalink() ) )
            );
            echo '</p>';
            return;
        }

        $breeds = Pawstars_Integrations::get_breeds();
        $province = Pawstars_Integrations::get_province();

        include PAWSTARS_PLUGIN_DIR . 'public/templates/create-profile.php';
    }

    /**
     * Get placeholder image URL
     *
     * @since  1.0.0
     * @return string
     */
    public function get_placeholder_image() {
        return PAWSTARS_PLUGIN_URL . 'assets/images/placeholders/dog-placeholder.png';
    }
}
