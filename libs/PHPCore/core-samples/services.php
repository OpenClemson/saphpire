<?php
    /**
     * REST services
     */
    // validating the consumer token
    $sCnsmrtoken = $aData[ 'c_token' ];
    $sAppToken   = 'serviceapp_consumertoken';
    $sBrokerUrl  = 'https://path/to/service/broker/services/api/access.php';
    $oRequestObj = cRequestAbs::GetObj( 'curl' );
    $aParams     = array(
        'c_token'   => urlencode( $sCnsmrtoken ),
        'app_token' => urlencode( $sAppToken ),
        'env'       => array(),
        'args'      => array( 'apikey' => 'api' ),
    );
    $oRequestObj->Post( $sBrokerUrl, $aParams );
    $iStatusCode = $oRequestObj->GetStatus();
    if ( $iStatusCode == '200' )
    {
        // access granted
    }
    else if ( $iStatusCode === '403' )
    {
        // access denied
    }
    // else if ( $iStatusCode === '' )
    else
    {
        // unknown error, likely cannot connect for various reasons
    }

?>