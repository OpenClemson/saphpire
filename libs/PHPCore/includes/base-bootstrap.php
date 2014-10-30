<?php
    // get access to the file utilities
    require_once( sCORE_INC_PATH . '/classes/cFileUtilities.php' );

    // read all config files
    require_once( sCORE_INC_PATH . '/classes/cBaseConfig.php' );
    $oConfig      = new cBaseConfig();
    $aConfigFiles = $oConfig->GetConfigFiles();

    // read contents of files into an array
    $aConfigs = $oConfig->Read( $aConfigFiles );

    // include convenience functions
    require_once( sCORE_INC_PATH . '/includes/Convenience.php' );

    // get the current host
    $sHost = GetHost();

    // if no hosts are defined, we're done
    if( !isset( $aConfigs[ 'host' ] ) )
    {
        die( 'Your script is being ran on an unknown environment: ' . $sHost );
    }
    // otherwise, format if there's only one
    elseif( !isset( $aConfigs[ 'host' ][ 0 ] ) )
    {
        $aConfigs[ 'host' ] = array( $aConfigs[ 'host' ] );
    }

    // try to find the environment for this host
    $iHostCount = count( $aConfigs[ 'host' ] );
    for( $i = 0; $i < $iHostCount; ++$i )
    {
        // check if the host name matches
        if( $aConfigs[ 'host' ][ $i ][ 'name' ] == $sHost )
        {
            // define application environment for use throughout application
            define( 'sAPPLICATION_ENV', $aConfigs[ 'host' ][ $i ][ 'env' ] );
            break;
        }
    }

    // ensure that the application can be run from this environment
    if( !defined( 'sAPPLICATION_ENV' ) )
    {
        die( 'Your script is being ran on an unknown environment: ' . $sHost );
    }

    // load any extensions that have been provided
    if( isset( $aConfigs[ 'extensions' ] ) )
    {
        $aConfigs = $oConfig->ReadExtensions( $aConfigs );
    }

    // setup error handling
    require_once( sCORE_INC_PATH . '/includes/error-handling.php' );

    // get the application class
    require_once( sCORE_INC_PATH . '/classes/cApplication.php' );

    // try to get the app name
    $sAppName = '';
    if( isset( $aConfigs[ 'application-name' ] )
        && is_string( $aConfigs[ 'application-name' ] ) )
    {
        $sAppName = $aConfigs[ 'application-name' ];
    }

    // set app name in the error/exception handler and in the app class
    $oAnomaly->SetApplicationName( $sAppName );
    cApplication::SetApplicationName( $sAppName );

    // get the current time
    $iNow = strtotime( 'now' );

    // set released status if possible
    if( isset( $aConfigs[ 'release-date' ] )
        && is_string( $aConfigs[ 'release-date' ] ) )
    {
        // if the release timestamp is valid, set the released status
        $iReleaseTimestamp = strtotime( $aConfigs[ 'release-date' ] );
        if( $iReleaseTimestamp !== false )
        {
            cApplication::SetReleaseDate( $aConfigs[ 'release-date' ] );
            cApplication::SetReleasedStatus( $iNow < $iReleaseTimestamp );
        }
    }

    // set maintenance mode if possible
    if( isset( $aConfigs[ 'maintenance-window' ] )
        && is_array( $aConfigs[ 'maintenance-window' ] )
        && isset( $aConfigs[ 'maintenance-window' ][ 'start' ] )
        && is_string( $aConfigs[ 'maintenance-window' ][ 'start' ] )
        && isset( $aConfigs[ 'maintenance-window' ][ 'end' ] )
        && is_string( $aConfigs[ 'maintenance-window' ][ 'end' ] ) )
    {
        // if the start and end times are valid, set the maintenance status
        $iStartMaintenance = strtotime( $aConfigs[ 'maintenance-window' ][ 'start' ] );
        $iEndMaintenance   = strtotime( $aConfigs[ 'maintenance-window' ][ 'end' ] );
        if( $iStartMaintenance !== false && $iEndMaintenance !== false )
        {
            cApplication::SetMaintenanceStartDate( $aConfigs[ 'maintenance-window' ][ 'start' ] );
            cApplication::SetMaintenanceEndDate( $aConfigs[ 'maintenance-window' ][ 'end' ] );
            cApplication::SetMaintenanceStatus( $iStartMaintenance <= $iNow && $iNow <= $iEndMaintenance );
        }
    }

    // get the developer emails and add them to the log manager
    $aDevelopers = GetContacts( array( 'role' => 'dev' ) );
    $iDevCount = count( $aDevelopers );
    $aDevEmails = array();
    for( $i = 0; $i < $iDevCount; ++$i )
    {
        $aDevEmails[] = $aDevelopers[ $i ][ 'email' ];
    }
    cLogManager::AddDevEmails( $aDevEmails );
?>