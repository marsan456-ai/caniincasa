<?php
/**
 * Leaderboard System
 *
 * Handles ranking calculations and leaderboard display.
 *
 * @package    Pawstars
 * @subpackage Pawstars/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Leaderboard Class
 *
 * @since 1.0.0
 */
class Pawstars_Leaderboard {

    /**
     * Database instance
     *
     * @var Pawstars_Database
     */
    private $db;

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param Pawstars_Database $database Database instance
     */
    public function __construct( $database ) {
        $this->db = $database;
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        add_action( 'pawstars_daily_leaderboard_update', array( $this, 'update_ranks' ) );
    }

    /**
     * Get hot dogs leaderboard
     *
     * @since  1.0.0
     * @param  int $limit Number of dogs
     * @return array
     */
    public function get_hot( $limit = 10 ) {
        $settings = get_option( 'pawstars_settings', array() );
        $days = isset( $settings['leaderboard_hot_days'] ) ? $settings['leaderboard_hot_days'] : 7;

        return $this->db->get_hot_leaderboard( $limit, $days );
    }

    /**
     * Get all-time leaderboard
     *
     * @since  1.0.0
     * @param  int $limit Number of dogs
     * @return array
     */
    public function get_alltime( $limit = 10 ) {
        return $this->db->get_alltime_leaderboard( $limit );
    }

    /**
     * Get breed leaderboard
     *
     * @since  1.0.0
     * @param  int $breed_id Breed ID
     * @param  int $limit    Number of dogs
     * @return array
     */
    public function get_by_breed( $breed_id, $limit = 10 ) {
        $dogs = $this->db->get_breed_leaderboard( $breed_id, $limit );

        // Add rank
        $position = 1;
        foreach ( $dogs as &$dog ) {
            $dog->rank = $position++;
            if ( $dog->gallery_ids ) {
                $dog->gallery_ids = json_decode( $dog->gallery_ids, true );
            }
            $dog->image_url = $dog->featured_image_id ? wp_get_attachment_url( $dog->featured_image_id ) : '';
        }

        return $dogs;
    }

    /**
     * Get provincia leaderboard
     *
     * @since  1.0.0
     * @param  string $provincia Provincia code
     * @param  int    $limit     Number of dogs
     * @return array
     */
    public function get_by_provincia( $provincia, $limit = 10 ) {
        $dogs = $this->db->get_provincia_leaderboard( $provincia, $limit );

        // Add rank
        $position = 1;
        foreach ( $dogs as &$dog ) {
            $dog->rank = $position++;
            if ( $dog->gallery_ids ) {
                $dog->gallery_ids = json_decode( $dog->gallery_ids, true );
            }
            $dog->image_url = $dog->featured_image_id ? wp_get_attachment_url( $dog->featured_image_id ) : '';
        }

        return $dogs;
    }

    /**
     * Get leaderboard by type
     *
     * @since  1.0.0
     * @param  string $type   Leaderboard type
     * @param  int    $limit  Number of dogs
     * @param  mixed  $filter Optional filter (breed_id or provincia)
     * @return array
     */
    public function get( $type, $limit = 10, $filter = null ) {
        switch ( $type ) {
            case 'hot':
                return $this->get_hot( $limit );
            case 'alltime':
                return $this->get_alltime( $limit );
            case 'breed':
                return $filter ? $this->get_by_breed( $filter, $limit ) : array();
            case 'provincia':
                return $filter ? $this->get_by_provincia( $filter, $limit ) : array();
            default:
                return $this->get_hot( $limit );
        }
    }

    /**
     * Get dog position in leaderboard
     *
     * @since  1.0.0
     * @param  int    $dog_id Dog ID
     * @param  string $type   Leaderboard type
     * @return int|null
     */
    public function get_dog_position( $dog_id, $type = 'alltime' ) {
        return $this->db->get_dog_rank( $dog_id, $type );
    }

    /**
     * Update rank positions
     *
     * @since 1.0.0
     */
    public function update_ranks() {
        global $wpdb;

        $table = $wpdb->prefix . 'pawstars_dogs';

        // Update all-time ranks
        $dogs = $wpdb->get_results(
            "SELECT id, total_points FROM $table WHERE status = 'active' ORDER BY total_points DESC"
        );

        $position = 1;
        foreach ( $dogs as $dog ) {
            $wpdb->update(
                $table,
                array(
                    'rank_position' => $position,
                    'rank_category' => 'alltime',
                ),
                array( 'id' => $dog->id ),
                array( '%d', '%s' ),
                array( '%d' )
            );
            $position++;
        }

        // Clear leaderboard caches
        delete_transient( 'pawstars_leaderboard_hot' );
        delete_transient( 'pawstars_leaderboard_alltime' );

        // Also clear any numbered variants
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '%_transient_pawstars_leaderboard_%'
                OR option_name LIKE '%_transient_timeout_pawstars_leaderboard_%'"
        );
    }

    /**
     * Get podium (top 3)
     *
     * @since  1.0.0
     * @param  string $type Leaderboard type
     * @return array
     */
    public function get_podium( $type = 'hot' ) {
        $dogs = $this->get( $type, 3 );

        // Reorder for display: 2nd, 1st, 3rd
        if ( count( $dogs ) >= 3 ) {
            return array( $dogs[1], $dogs[0], $dogs[2] );
        }

        return $dogs;
    }

    /**
     * Get available breeds with dogs
     *
     * @since  1.0.0
     * @return array
     */
    public function get_breeds_with_dogs() {
        global $wpdb;

        $table = $wpdb->prefix . 'pawstars_dogs';

        $breed_ids = $wpdb->get_col(
            "SELECT DISTINCT breed_id FROM $table WHERE status = 'active' AND breed_id IS NOT NULL"
        );

        $breeds = array();
        foreach ( $breed_ids as $id ) {
            $breeds[] = array(
                'id'   => $id,
                'name' => get_the_title( $id ),
            );
        }

        // Sort alphabetically
        usort( $breeds, function( $a, $b ) {
            return strcmp( $a['name'], $b['name'] );
        } );

        return $breeds;
    }

    /**
     * Get available province with dogs
     *
     * @since  1.0.0
     * @return array
     */
    public function get_province_with_dogs() {
        global $wpdb;

        $table = $wpdb->prefix . 'pawstars_dogs';

        $province = $wpdb->get_col(
            "SELECT DISTINCT provincia FROM $table WHERE status = 'active' AND provincia IS NOT NULL AND provincia != ''"
        );

        $province_names = $this->get_province_names();

        $result = array();
        foreach ( $province as $code ) {
            $result[] = array(
                'code' => $code,
                'name' => isset( $province_names[ $code ] ) ? $province_names[ $code ] : $code,
            );
        }

        // Sort alphabetically by name
        usort( $result, function( $a, $b ) {
            return strcmp( $a['name'], $b['name'] );
        } );

        return $result;
    }

    /**
     * Get Italian province names
     *
     * @since  1.0.0
     * @return array
     */
    private function get_province_names() {
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
