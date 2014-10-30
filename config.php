<?php
    /**
     * Configuration File for PHP Applications
     *
     * This file is to be included in all page controller files
     * and it will define all constants used throughout the application.
     *
     * @author  Team Rah
     *
     * @package Core
     * @version 0.5.1
     */

    // enable errors to be shown
    ini_set( 'display_errors', true );
    ini_set( 'display_startup_errors', true );

    // set all possible errors, warnings, and notices to be reported
    ini_set( 'error_reporting', E_ALL | E_STRICT );
    error_reporting( E_ALL | E_STRICT );

    // set the timezone
    ini_set( 'date.timezone', 'America/New_York' );

    // define format that all timestamps should use
    define( 'sTIMESTAMP_FORMAT', 'Y-m-d H:i:s' );

    // define the base path from which to include files
    define( 'sBASE_INC_PATH',  dirname( str_replace( "\\", DIRECTORY_SEPARATOR, __FILE__ ) ) );
    set_include_path( get_include_path() . PATH_SEPARATOR . sBASE_INC_PATH );

    // define the core path from which to include files
    define( 'sCORE_INC_PATH',  sBASE_INC_PATH . '/libs/PHPCore' );
    set_include_path( get_include_path() . PATH_SEPARATOR . sCORE_INC_PATH );

    // define a flag to check is this is being run through the command line
    define( 'bIS_CLI', ( php_sapi_name() === 'cli' ) );

    // begin or resume the session
    session_start();
?>