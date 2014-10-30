<?php
    /**
     * configurations
     *     app
     *     contacts
     *     db
     *     hosts
     *     ldap
     *     services-used
     *     <custom>
     */
    // read a config file
    $oXmlUtilities = new cXmlUtilities();
    $aAppConfig    = $oXmlUtilities->ReadArrayFromFile( sBASE_INC_PATH . '/configs/app.xml' );

    // get configurations
    $sAppName      = $aAppConfig[ 'app' ][ 'application-name' ];
    $utsRlsDate    = strtotime( $aAppConfig[ 'app' ][ 'release-date' ] );
?>