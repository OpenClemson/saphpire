<?php
    // get debugging functions
    require_once sCORE_INC_PATH . '/includes/debug.php';

    // include error/exception handling
    require_once sCORE_INC_PATH . '/classes/cAnomaly.php';
    require_once sCORE_INC_PATH . '/classes/cPresAnomaly.php';

    // get an instance of the error handler to work with
    $oAnomaly = cAnomaly::GetInstance();

    // get a presentation object for error handling
    $oPresAnomaly = new cPresAnomaly();

    // set default error/exception presentation functions
    $oAnomaly->SetDevExceptionOutput( array( $oPresAnomaly, 'DevExceptionOutput' ) );
    $oAnomaly->SetUserExceptionOutput( array( $oPresAnomaly, 'UserOutput' ) );
    $oAnomaly->SetDevErrorOutput( array( $oPresAnomaly, 'DevErrorOutput' ) );
    $oAnomaly->SetUserErrorOutput( array( $oPresAnomaly, 'UserOutput' ) );

    // add the log manager as the logger for the error and exception handler
    require_once sCORE_INC_PATH . '/classes/cLogManager.php';
    $oAnomaly->SetLogger( 'cLogManager' );

    try
    {
        // get the service logger
        //require_once sCORE_INC_PATH . '/classes/cLogService.php';

        // set the APIs for the log service to use
        //cLogService::SetLogApi(         cService::GetServiceApiPath( sAPPLICATION_ENV, 'Log', 'log'          ) );
        //cLogService::SetGetContentsApi( cService::GetServiceApiPath( sAPPLICATION_ENV, 'Log', 'get-contents' ) );
        //cLogService::SetClearBeforeApi( cService::GetServiceApiPath( sAPPLICATION_ENV, 'Log', 'clear-before' ) );
        //cLogService::SetClearApi(       cService::GetServiceApiPath( sAPPLICATION_ENV, 'Log', 'clear'        ) );
        //cLogService::SetGetTypesApi(    cService::GetServiceApiPath( sAPPLICATION_ENV, 'Log', 'get-types'    ) );
        //cLogService::SetTypeStatsApi(   cService::GetServiceApiPath( sAPPLICATION_ENV, 'Log', 'type-stats'   ) );

        // set the consumer token for all the log
        //cLogService::SetConsumerToken(  cService::GetServiceToken( sAPPLICATION_ENV, 'Log' ) );

        // add the service logger as high priority
        //cLogManager::AddLogger( 'cLogService', 'Service', true );

        // get the file logger and initialize it
        require_once sCORE_INC_PATH . '/classes/cLogXml.php';
        $sLogDirectory = sBASE_INC_PATH . '/logs/';
        cLogXml::SetLogDirectory( $sLogDirectory );

        // add the file logger to the log manager
        cLogManager::AddLogger( 'cLogXml', 'XML' );
    }
    catch( Exception $oException )
    {
        // ignore this
    }
?>