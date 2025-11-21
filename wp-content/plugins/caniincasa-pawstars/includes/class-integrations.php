<?php
/**
 * Integrations
 *
 * Handles integration with existing CPTs and theme functionality.
 *
 * @package    Pawstars
 * @subpackage Pawstars/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Integrations Class
 *
 * @since 1.0.0
 */
class Pawstars_Integrations {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Add Paw Stars tab to dashboard
        add_filter( 'caniincasa_dashboard_tabs', array( $this, 'add_dashboard_tab' ) );
        add_action( 'caniincasa_dashboard_content_pawstars', array( $this, 'render_dashboard_tab' ) );

        // Add link to razze_di_cani single pages
        add_filter( 'the_content', array( $this, 'add_breed_pawstars_link' ) );
    }

    /**
     * Add Paw Stars tab to user dashboard
     *
     * @since  1.0.0
     * @param  array $tabs Dashboard tabs
     * @return array
     */
    public function add_dashboard_tab( $tabs ) {
        if ( ! pawstars_is_active() ) {
            return $tabs;
        }

        $tabs['pawstars'] = array(
            'label' => __( 'Paw Stars', 'pawstars' ),
            'icon'  => '⭐',
        );

        return $tabs;
    }

    /**
     * Render Paw Stars dashboard tab
     *
     * @since 1.0.0
     */
    public function render_dashboard_tab() {
        if ( ! is_user_logged_in() || ! pawstars_is_active() ) {
            return;
        }

        $template = PAWSTARS_PLUGIN_DIR . 'public/templates/user-dashboard.php';
        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Add Paw Stars link to breed pages
     *
     * @since  1.0.0
     * @param  string $content Post content
     * @return string
     */
    public function add_breed_pawstars_link( $content ) {
        if ( ! is_singular( 'razze_di_cani' ) || ! pawstars_is_active() ) {
            return $content;
        }

        global $wpdb;
        $breed_id = get_the_ID();
        $table = $wpdb->prefix . 'pawstars_dogs';

        // Count dogs with this breed
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE breed_id = %d AND status = 'active'",
                $breed_id
            )
        );

        if ( $count > 0 ) {
            $link = add_query_arg(
                array(
                    'breed' => $breed_id,
                ),
                home_url( '/paw-stars/' ) // Assumes feed shortcode page
            );

            $box = sprintf(
                '<div class="pawstars-breed-cta">
                    <h4>🐾 %s su Paw Stars</h4>
                    <p>%s</p>
                    <a href="%s" class="btn btn-primary">%s</a>
                </div>',
                esc_html( get_the_title() ),
                sprintf(
                    _n(
                        'C\'è %d %s nella nostra community!',
                        'Ci sono %d %s nella nostra community!',
                        $count,
                        'pawstars'
                    ),
                    $count,
                    esc_html( get_the_title() )
                ),
                esc_url( $link ),
                __( 'Scopri i profili', 'pawstars' )
            );

            $content .= $box;
        }

        return $content;
    }

    /**
     * Get all dog breeds from CPT razze_di_cani
     *
     * @since  1.0.0
     * @return array
     */
    public static function get_breeds() {
        $breeds = get_posts( array(
            'post_type'      => 'razze_di_cani',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ) );

        $result = array();
        foreach ( $breeds as $breed ) {
            $result[] = array(
                'id'   => $breed->ID,
                'name' => $breed->post_title,
                'slug' => $breed->post_name,
            );
        }

        return $result;
    }

    /**
     * Get Italian province list
     *
     * @since  1.0.0
     * @return array
     */
    public static function get_province() {
        return array(
            'AG' => 'Agrigento', 'AL' => 'Alessandria', 'AN' => 'Ancona', 'AO' => 'Aosta',
            'AR' => 'Arezzo', 'AP' => 'Ascoli Piceno', 'AT' => 'Asti', 'AV' => 'Avellino',
            'BA' => 'Bari', 'BT' => 'Barletta-Andria-Trani', 'BL' => 'Belluno', 'BN' => 'Benevento',
            'BG' => 'Bergamo', 'BI' => 'Biella', 'BO' => 'Bologna', 'BZ' => 'Bolzano',
            'BS' => 'Brescia', 'BR' => 'Brindisi', 'CA' => 'Cagliari', 'CL' => 'Caltanissetta',
            'CB' => 'Campobasso', 'CE' => 'Caserta', 'CT' => 'Catania', 'CZ' => 'Catanzaro',
            'CH' => 'Chieti', 'CO' => 'Como', 'CS' => 'Cosenza', 'CR' => 'Cremona',
            'KR' => 'Crotone', 'CN' => 'Cuneo', 'EN' => 'Enna', 'FM' => 'Fermo',
            'FE' => 'Ferrara', 'FI' => 'Firenze', 'FG' => 'Foggia', 'FC' => 'Forlì-Cesena',
            'FR' => 'Frosinone', 'GE' => 'Genova', 'GO' => 'Gorizia', 'GR' => 'Grosseto',
            'IM' => 'Imperia', 'IS' => 'Isernia', 'SP' => 'La Spezia', 'AQ' => "L'Aquila",
            'LT' => 'Latina', 'LE' => 'Lecce', 'LC' => 'Lecco', 'LI' => 'Livorno',
            'LO' => 'Lodi', 'LU' => 'Lucca', 'MC' => 'Macerata', 'MN' => 'Mantova',
            'MS' => 'Massa-Carrara', 'MT' => 'Matera', 'ME' => 'Messina', 'MI' => 'Milano',
            'MO' => 'Modena', 'MB' => 'Monza e Brianza', 'NA' => 'Napoli', 'NO' => 'Novara',
            'NU' => 'Nuoro', 'OR' => 'Oristano', 'PD' => 'Padova', 'PA' => 'Palermo',
            'PR' => 'Parma', 'PV' => 'Pavia', 'PG' => 'Perugia', 'PU' => 'Pesaro e Urbino',
            'PE' => 'Pescara', 'PC' => 'Piacenza', 'PI' => 'Pisa', 'PT' => 'Pistoia',
            'PN' => 'Pordenone', 'PZ' => 'Potenza', 'PO' => 'Prato', 'RG' => 'Ragusa',
            'RA' => 'Ravenna', 'RC' => 'Reggio Calabria', 'RE' => 'Reggio Emilia', 'RI' => 'Rieti',
            'RN' => 'Rimini', 'RM' => 'Roma', 'RO' => 'Rovigo', 'SA' => 'Salerno',
            'SS' => 'Sassari', 'SV' => 'Savona', 'SI' => 'Siena', 'SR' => 'Siracusa',
            'SO' => 'Sondrio', 'SU' => 'Sud Sardegna', 'TA' => 'Taranto', 'TE' => 'Teramo',
            'TR' => 'Terni', 'TO' => 'Torino', 'TP' => 'Trapani', 'TN' => 'Trento',
            'TV' => 'Treviso', 'TS' => 'Trieste', 'UD' => 'Udine', 'VA' => 'Varese',
            'VE' => 'Venezia', 'VB' => 'Verbano-Cusio-Ossola', 'VC' => 'Vercelli', 'VR' => 'Verona',
            'VV' => 'Vibo Valentia', 'VI' => 'Vicenza', 'VT' => 'Viterbo',
        );
    }
}

// Initialize
new Pawstars_Integrations();
