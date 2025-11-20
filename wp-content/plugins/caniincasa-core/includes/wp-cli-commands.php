<?php
/**
 * WP-CLI Commands for Caniincasa Core Plugin
 *
 * @package Caniincasa_Core
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Only load if WP-CLI is available
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}

/**
 * CSV Import Commands
 */
class Caniincasa_CLI_Import extends WP_CLI_Command {

    /**
     * Import razze from CSV file
     *
     * ## OPTIONS
     *
     * <file>
     * : Path to CSV file
     *
     * ## EXAMPLES
     *
     *     wp caniincasa import razze /path/to/razze.csv
     *
     * @param array $args       Command arguments
     * @param array $assoc_args Associated arguments
     */
    public function razze( $args, $assoc_args ) {
        list( $file ) = $args;

        if ( ! file_exists( $file ) ) {
            WP_CLI::error( "File not found: $file" );
        }

        WP_CLI::log( "Starting razze import from: $file" );

        $importer = caniincasa_csv_importer();
        $results = $importer->import_razze( $file );

        if ( $results['success'] ) {
            WP_CLI::success( $results['message'] );

            WP_CLI::log( sprintf( 'Total: %d', $results['total'] ) );
            WP_CLI::log( sprintf( 'Imported: %d', $results['imported'] ) );
            WP_CLI::log( sprintf( 'Updated: %d', $results['updated'] ) );
            WP_CLI::log( sprintf( 'Skipped: %d', $results['skipped'] ) );

            if ( ! empty( $results['errors'] ) ) {
                WP_CLI::warning( 'Errors encountered:' );
                foreach ( $results['errors'] as $error ) {
                    WP_CLI::log( sprintf( '- %s: %s', $error['title'], $error['message'] ) );
                }
            }
        } else {
            WP_CLI::error( $results['message'] );
        }
    }

    /**
     * Import allevamenti from CSV file
     *
     * ## OPTIONS
     *
     * <file>
     * : Path to CSV file
     *
     * ## EXAMPLES
     *
     *     wp caniincasa import allevamenti /path/to/allevamenti.csv
     *
     * @param array $args       Command arguments
     * @param array $assoc_args Associated arguments
     */
    public function allevamenti( $args, $assoc_args ) {
        list( $file ) = $args;

        if ( ! file_exists( $file ) ) {
            WP_CLI::error( "File not found: $file" );
        }

        WP_CLI::log( "Starting allevamenti import from: $file" );

        $importer = caniincasa_csv_importer();
        $results = $importer->import_allevamenti( $file );

        $this->display_results( $results );
    }

    /**
     * Import veterinari from CSV file
     *
     * ## OPTIONS
     *
     * <file>
     * : Path to CSV file
     *
     * ## EXAMPLES
     *
     *     wp caniincasa import veterinari /path/to/veterinari.csv
     *
     * @param array $args       Command arguments
     * @param array $assoc_args Associated arguments
     */
    public function veterinari( $args, $assoc_args ) {
        list( $file ) = $args;

        if ( ! file_exists( $file ) ) {
            WP_CLI::error( "File not found: $file" );
        }

        WP_CLI::log( "Starting veterinari import from: $file" );

        $importer = caniincasa_csv_importer();
        $results = $importer->import_veterinari( $file );

        $this->display_results( $results );
    }

    /**
     * Import canili from CSV file
     *
     * ## OPTIONS
     *
     * <file>
     * : Path to CSV file
     *
     * ## EXAMPLES
     *
     *     wp caniincasa import canili /path/to/canili.csv
     *
     * @param array $args       Command arguments
     * @param array $assoc_args Associated arguments
     */
    public function canili( $args, $assoc_args ) {
        list( $file ) = $args;

        if ( ! file_exists( $file ) ) {
            WP_CLI::error( "File not found: $file" );
        }

        WP_CLI::log( "Starting canili import from: $file" );

        $importer = caniincasa_csv_importer();
        $results = $importer->import_canili( $file );

        $this->display_results( $results );
    }

    /**
     * Import pensioni from CSV file
     *
     * ## OPTIONS
     *
     * <file>
     * : Path to CSV file
     *
     * ## EXAMPLES
     *
     *     wp caniincasa import pensioni /path/to/pensioni.csv
     *
     * @param array $args       Command arguments
     * @param array $assoc_args Associated arguments
     */
    public function pensioni( $args, $assoc_args ) {
        list( $file ) = $args;

        if ( ! file_exists( $file ) ) {
            WP_CLI::error( "File not found: $file" );
        }

        WP_CLI::log( "Starting pensioni import from: $file" );

        $importer = caniincasa_csv_importer();
        $results = $importer->import_pensioni( $file );

        $this->display_results( $results );
    }

    /**
     * Import centri cinofili from CSV file
     *
     * ## OPTIONS
     *
     * <file>
     * : Path to CSV file
     *
     * ## EXAMPLES
     *
     *     wp caniincasa import centri-cinofili /path/to/centri.csv
     *
     * @param array $args       Command arguments
     * @param array $assoc_args Associated arguments
     */
    public function centri_cinofili( $args, $assoc_args ) {
        list( $file ) = $args;

        if ( ! file_exists( $file ) ) {
            WP_CLI::error( "File not found: $file" );
        }

        WP_CLI::log( "Starting centri cinofili import from: $file" );

        $importer = caniincasa_csv_importer();
        $results = $importer->import_centri_cinofili( $file );

        $this->display_results( $results );
    }

    /**
     * Import all CSV files from a directory
     *
     * ## OPTIONS
     *
     * <directory>
     * : Path to directory containing CSV files
     *
     * ## EXAMPLES
     *
     *     wp caniincasa import all /path/to/csv-files/
     *
     * @param array $args       Command arguments
     * @param array $assoc_args Associated arguments
     */
    public function all( $args, $assoc_args ) {
        list( $directory ) = $args;

        if ( ! is_dir( $directory ) ) {
            WP_CLI::error( "Directory not found: $directory" );
        }

        // Ensure trailing slash
        $directory = rtrim( $directory, '/' ) . '/';

        $importer = caniincasa_csv_importer();

        // Map of CSV files to import methods
        $imports = array(
            'Razze-di-Cani-Export-*.csv'            => 'import_razze',
            'Allevamenti-Export-*.csv'              => 'import_allevamenti',
            'Strutture-Veterinarie-Export-*.csv'    => 'import_veterinari',
            'Canili-Export-*.csv'                   => 'import_canili',
            'Pensioni-per-Cani-Export-*.csv'        => 'import_pensioni',
            'Centri-Cinofili-Export-*.csv'          => 'import_centri_cinofili',
        );

        foreach ( $imports as $pattern => $method ) {
            $files = glob( $directory . $pattern );

            if ( empty( $files ) ) {
                WP_CLI::warning( "No files found matching: $pattern" );
                continue;
            }

            foreach ( $files as $file ) {
                WP_CLI::log( "\n" . str_repeat( '=', 60 ) );
                WP_CLI::log( "Importing: " . basename( $file ) );
                WP_CLI::log( str_repeat( '=', 60 ) . "\n" );

                $results = $importer->$method( $file );
                $this->display_results( $results );
            }
        }

        WP_CLI::success( 'All imports completed!' );
    }

    /**
     * Display import results
     *
     * @param array $results Import results
     */
    private function display_results( $results ) {
        if ( $results['success'] ) {
            WP_CLI::success( $results['message'] );

            WP_CLI::log( sprintf( 'Total: %d', $results['total'] ) );
            WP_CLI::log( sprintf( 'Imported: %d', $results['imported'] ) );
            WP_CLI::log( sprintf( 'Updated: %d', $results['updated'] ) );
            WP_CLI::log( sprintf( 'Skipped: %d', $results['skipped'] ) );

            if ( ! empty( $results['errors'] ) ) {
                WP_CLI::warning( sprintf( 'Errors encountered: %d', count( $results['errors'] ) ) );

                // Show first 5 errors
                $error_count = min( 5, count( $results['errors'] ) );
                for ( $i = 0; $i < $error_count; $i++ ) {
                    $error = $results['errors'][ $i ];
                    WP_CLI::log( sprintf( '- %s: %s', $error['title'], $error['message'] ) );
                }

                if ( count( $results['errors'] ) > 5 ) {
                    WP_CLI::log( sprintf( '... and %d more errors', count( $results['errors'] ) - 5 ) );
                }
            }
        } else {
            WP_CLI::error( $results['message'] );
        }
    }
}

// Register WP-CLI commands
WP_CLI::add_command( 'caniincasa import', 'Caniincasa_CLI_Import' );
