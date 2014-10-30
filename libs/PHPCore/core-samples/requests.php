<?php
    /**
     * perform a cURL request
     */
    // get a request object
    $oRequest = new cRequest( 'curl' );             // or 'http'    generic request class wrapper
    // or
    $oRequestObj = cRequestAbs::GetObj( 'curl' )    // or 'http'    static object factory

    // modify the request, get the object first
    $oRequestObj             = $oRequest->GetObj();
    $oRequestObj->sReferer   = 'https://clemson.edu';
    $oRequestObj->sHost      = 'google.com';
    $oRequestObj->sUserAgent = ' Mozilla/5.0 (Windows NT 6.1; WOW64; rv:30.0) Gecko/20100101 Firefox/30.0';

    /**
     * make a request
     */
    // GET (multiple approaches)
    $sHtmlResult = $oRequest->Get( 'http://www.google.com' );
    $sHtmlResult = $oRequest->Get( 'http://long.url.com/phpfile.php?param1=test&param2=test2' );
    $aGetParames = array(
        'param1' => 'test',
        'param2' => 'test2'
    );
    $sHtmlResult = $oRequest->Get( 'http://long.url.com/phpfile.php', $aGetParames );

    // POST
    $aPostParams = array( 'q' => 'Searching google' );
    $sHtmlResult = $oRequest->Post( 'http://www.google.com', $aPostParams );
?>