<?php
    try
    {
        // bootstrap.
        require_once( 'includes/snapshot-bootstrap.php' );

        // take snapshot and send response.
        $aData     = $oBusiness->CheckPermissions();
        $sResponse = $oPresentation->Render( $aData );

        echo $sResponse;
    }
    catch( Exception $oException )
    {
        // display error.
        $aMessage = print_r( "Unknown error occurred. \r\n" . $oException->getMessage() . "\r\n" . print_r( $oException, 1 ), 1 );
        if ( !empty( $oPresentation ) )
        {
            $aData = array(
                'code'         => 'HTTP/1.1 500 Internal Server Error',
                'data'         => $aMessage,
                'response'     => '',
                'extraheaders' => ''
            );
            echo $oPresentation->Render( $aData );
        }
        else
        {
            header( 'HTTP/1.1 503 Service Unavailable' );
            header( 'Content-type: application/json' );
            echo json_encode( $oException->getMessage() );
        }
    }
?>