<?php
    if ( !defined( 'sBASE_INC_PATH' ) )
    {
        // define the base path from which to include files
        define( 'sBASE_INC_PATH', dirname( dirname( dirname( dirname( str_replace( "\\", DIRECTORY_SEPARATOR, __FILE__ ) ) ) ) ) );
        set_include_path( get_include_path() . PATH_SEPARATOR . sBASE_INC_PATH );
    }

    if ( !defined( 'sCORE_INC_PATH' ) )
    {
        // define the core path from which to include files
        define( 'sCORE_INC_PATH', sBASE_INC_PATH . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'PHPCore' );
        set_include_path( get_include_path() . PATH_SEPARATOR . sCORE_INC_PATH );
    }

    if ( !defined( 'sSERVICE_INC_PATH' ) )
    {
        // define the path from which to include service files
        define( 'sSERVICE_INC_PATH', sBASE_INC_PATH . DIRECTORY_SEPARATOR . 'services' );
        set_include_path( get_include_path() . PATH_SEPARATOR . sSERVICE_INC_PATH );
    }

    // get required classes.
    require_once( sSERVICE_INC_PATH . DIRECTORY_SEPARATOR . 'snapshot' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'cBusiness.php' );
    require_once( sSERVICE_INC_PATH . DIRECTORY_SEPARATOR . 'snapshot' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'cPresentation.php' );

    $oBusiness     = new cBusiness();
    $oPresentation = new cPresentation();
?>