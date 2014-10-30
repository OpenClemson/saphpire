<?php
    /**
     * REST api controller
     *
     *  The business and presentation calls are for show. At the moment
     *  there is are cBusService or cPresService classes. They represent the
     *  data model class that the API is meant to interact with.
     */

    /**
     * docblock explaining API controller
     *
     * parameters:
     *     param: description
     *
     * returns:
     *     Statuses:
     *         Token denied     - HTTP/1.1 403 Forbidden
     *         Bad Parameters   - HTTP/1.1 406 Not Acceptable
     *         Success          - HTTP/1.1 200 OK
     *         Error            - HTTP/1.1 500 Internal Server Error
     *     Message (default=blank)
     *
     * @author  author <author@uthor.com>
     * @package <package>
     * @version <version>
     *
     */
    // get configs
    require_once( '../../config.php' );
    require_once( sBASE_INC_PATH . '/path/to/service/includes/bootstrap.php');

    try
    {
        // default the status and message
        $sStatus  = 'HTTP/1.1 200 OK';
        $sMessage = '';

        // business call
        $aData    = $oBusService->ApiCall( $_GET );

        // api presenation
        $aOutput  = $oPresService->ApiJson( $aData );

        // update response
        $sStatus  = $aOutput[ 'status' ];
        $sMessage = $aOutput[ 'message' ];
    }
    catch( Exception $oException )
    {
        // log the exception
        cLogManager::Log( 'exception', $oException->GetMessage(), $oException );

        // set the status and message
        $sStatus  = 'HTTP/1.1 500 Internal Server Error';
        $sMessage = 'An unexpected error has occurred.';
    }

    // send response
    header( $sStatus );
    die( $sMessage );


    /**
     * call a REST api
     */
    // create request object
    $oRequestObj = cRequestAbs::GetObj( 'curl' );
    // GET request (full string)
    $sResult     = $oRequestObj->Get( 'https://service_url/services/path/to/api.php?c_token=' . urlencode( $sCnsmrtoken ) );

    // GET request (parameterized)
    $aParams     = array(
        'c_token' => urlencode( $sCnsmrtoken ),
        'arg1'    => 'test',
        'arg2'    => 'test2',
        'arg3'    => 'test3',
        'arg4'    => 'test4'
    );
    $sResult = $oRequestObj->Get( 'https://service_url/services/path/to/api.php', $aParams );
?>