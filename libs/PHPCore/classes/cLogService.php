<?php
    // get the base class
    require_once sCORE_INC_PATH . '/classes/cLogBase.php';

    // pull in the request class
    require_once sCORE_INC_PATH . '/classes/cRequest.php';

    /**
     * Implementation of ifLogger that saves and retrieves log entries through calls to a service.
     *
     * @author  Team Rah
     * @package Core
     * @package Logging
     * @version 0.6.0
     */
    class cLogService extends cLogBase
    {
        /**
         * Token identifying the application.
         *
         * @var string
         */
        protected static $sConsumerToken = '';

        /**
         * The API used to log information.
         *
         * @var string
         */
        protected static $sLogApi = '';

        /**
         * The API used to get log entries for a type.
         *
         * @var string
         */
        protected static $sGetContentsApi = '';

        /**
         * The API used to log information.
         *
         * @var string
         */
        protected static $sClearBeforeApi = '';

        /**
         * The API used to clear all entries for a type.
         *
         * @var string
         */
        protected static $sClearApi = '';

        /**
         * The API used to get all log types.
         *
         * @var string
         */
        protected static $sGetTypesApi = '';

        /**
         * The API used to get stats about a log type.
         *
         * @var string
         */
        protected static $sTypeStatsApi = '';

        /**
         * Sets the API to use for sending log entries.
         *
         * @param   string   $sLogApi
         */
        public static function SetLogApi( $sLogApi )
        {
            if(! is_string( $sLogApi ) )
            {
                throw new Exception( 'API provided is not a string.' );
            }

            self::$sLogApi = $sLogApi;
        }

        /**
         * Sets the API to use for getting the contents for a log.
         *
         * @param   string   $sGetContentsApi
         */
        public static function SetGetContentsApi( $sGetContentsApi )
        {
            if( !is_string( $sGetContentsApi ) )
            {
                throw new Exception( 'API provided is not a string.' );
            }

            self::$sGetContentsApi = $sGetContentsApi;
        }

        /**
         * Sets the API to use to clear a log before a given timestamp.
         *
         * @param   string   $sClearBeforeApi
         */
        public static function SetClearBeforeApi( $sClearBeforeApi )
        {
            if( !is_string( $sClearBeforeApi ) )
            {
                throw new Exception( 'API provided is not a string.' );
            }

            self::$sClearBeforeApi = $sClearBeforeApi;
        }

        /**
         * Sets the API to use to clear a given log.
         *
         * @param   string   $sClearApi
         */
        public static function SetClearApi( $sClearApi )
        {
            if( !is_string( $sClearApi ) )
            {
                throw new Exception( 'API provided is not a string.' );
            }

            self::$sClearApi = $sClearApi;
        }

        /**
         * Sets the API to use to get the types of logs.
         *
         * @param   string   $sGetTypesApi
         */
        public static function SetGetTypesApi( $sGetTypesApi )
        {
            if( !is_string( $sGetTypesApi ) )
            {
                throw new Exception( 'API provided is not a string.' );
            }

            self::$sGetTypesApi = $sGetTypesApi;
        }

        /**
         * Sets the API to use to get stats about a log type.
         *
         * @param   string   $sTypeStatsApi
         */
        public static function SetTypeStatsApi( $sTypeStatsApi )
        {
            if( !is_string( $sTypeStatsApi ) )
            {
                throw new Exception( 'API provided is not a string.' );
            }

            self::$sTypeStatsApi = $sTypeStatsApi;
        }

        /**
         * Sets the consumer token for this class.
         *
         * @param   string   $sConsumerToken
         */
        public static function SetConsumerToken( $sConsumerToken )
        {
            // make sure the token provided is a string
            if( !is_string( $sConsumerToken ) )
            {
                throw new Exception( 'Application token provided is not a string.' );
            }

            // set the token
            self::$sConsumerToken = $sConsumerToken;
        }

        /**
         * Sends the log contents to the logger service to sync local with db.
         *
         * @param array   $sLogFile   File path to the file to be sent.
         * @param string  $sLogType   Logtype to be logged.
         */
        // public static function SendSync( $sLogFile, $sLogType )
        // {
        //     // Make sure file has contents.
        //     $iCheckSize = filesize( $sLogFile );
        //     if( $iCheckSize > 0 )
        //     {
        //         // Move the contents over to a temporary file.
        //         $sTmpFileContents = file_get_contents( $sLogFile );
        //         $sTmpFileName     = str_replace( '.xml', '', $sLogFile );
        //         $aTime            = explode( ' ', microtime() );
        //         $sMicro           = substr( $aTime[ 0 ], 2, 8) ;
        //         $sTmpFileName     = $sTmpFileName . '-temp-' . $sMicro . '.xml';
        //         file_put_contents( $sTmpFileName, $sTmpFileContents );
        //         file_put_contents( $sLogFile, '' );

        //         // Set dummy token, auth and url.
        //         $sAuth      = md5( 'squirrel' );

        //         $sPath = sBASE_INC_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'app.xml';               // Set xml to supress errors so we can prevent application crash.
        //         libxml_use_internal_errors( true );
        //         try
        //         {
        //             // Attempt to load xml file.
        //             $oXml = simplexml_load_file( $sPath, 'SimpleXMLElement', LIBXML_NOCDATA );
        //         }
        //         catch( Exception $oException )
        //         {
        //             $sMsg = $oException->GetMessage();
        //         }

        //         // Check for xml and if it could be loaded before proceeding.
        //         if( empty( $sMsg ) )
        //         {
        //             $aAppContents = json_decode( json_encode( (array) $oXml ), 1 );
        //         }
        //         $sAppName   = empty( $aAppContents[ 'application-name' ] ) ? '' : $aAppContents[ 'application-name' ];
        //         self::$sServiceUrl = 'localhost/logger/services/logger/sync.php';
        //         // self::$sServiceUrl      = 'https://alsutilities.dev.clemson.edu/rramik/logger/services/logger/log.php';

        //         // Setup POST cUrl string.
        //         $sData  = 'auth='      . $sAuth;
        //         $sData .= '&app_name=' . $sAppName;
        //         $sData .= '&log_type=' . $sLogType;
        //         $sData .= '&contents=' . urlencode( $sTmpFileContents );

        //         // Setup curl options and execute.
        //         $rhCurl = curl_init();
        //         curl_setopt( $rhCurl, CURLOPT_RETURNTRANSFER, 1);
        //         curl_setopt( $rhCurl, CURLOPT_VERBOSE, 1 );
        //         curl_setopt( $rhCurl, CURLOPT_HEADER, 1 );
        //         curl_setopt( $rhCurl, CURLOPT_URL, self::$sServiceUrl );
        //         curl_setopt( $rhCurl, CURLOPT_POST, 1 );
        //         curl_setopt( $rhCurl, CURLOPT_POSTFIELDS, $sData );
        //         $vResponse = curl_exec( $rhCurl );
        //         $sError    = curl_error( $rhCurl );

        //         // Get the HTTP code received and do some logic.
        //         $aResult[ 'code' ] = curl_getinfo( $rhCurl, CURLINFO_HTTP_CODE );
        //         $aResult[ 'url' ]  = curl_getinfo( $rhCurl, CURLINFO_EFFECTIVE_URL );

        //         // For debugging logger.
        //         $sHeader = curl_getinfo( $rhCurl, CURLINFO_HEADER_SIZE) ;
        //         $aResult[ 'header' ] = substr( $vResponse, 0, $sHeader );
        //         $aResult[ 'body' ]   = substr( $vResponse, $sHeader );
        //         curl_close( $rhCurl );

        //         // Remove the temporary file on success.
        //         if( $aResult[ 'code' ] == 201 )
        //         {
        //             unlink( $sTmpFileName );
        //         }
        //         // If not successful put the contents back and unlink temporary file.
        //         else
        //         {
        //             file_put_contents( $sLogFile, $sTmpFileContents );
        //             unlink( $sTmpFileName );
        //         }
        //     }
        // }

        /**
         * Checks current logfile size and calls sync if its larger than a preset number.
         *
         * @param array    $sLogFile   File path to the file to be sent.
         * @param string   $sLogType   Logtype to be logged.
         */
        public static function SyncCheckFileSize( $sLogFile, $sLogType )
        {
            if( file_exists( $sLogFile ) )
            {
                // If over X ( Currently 5 ) MB perform a sync.
                $iFileSize = filesize( $sLogFile );

                if( $iFileSize > self::$iMaxFileSize )
                {
                    self::SendSync( $sLogFile, $sLogType );
                }
            }
        }

        /**
         * Logs the given message and extra data with the supplied type.
         *
         * @param  string  $sType     Type of log.
         * @param  string  $sMessage  Message to log.
         * @param  array   $aContext  Any additional data to save with the log entry.
         *
         * @return boolean
         */
        public static function Log( $sType, $sMessage, array $aContext = array() )
        {
            // set params for the request
            $aParams = array();
            $aParams[ 'c_token' ]      = self::$sConsumerToken;
            $aParams[ 'app'  ]         = self::$sConsumerToken;
            $aParams[ 'type' ]         = $sType;
            $aParams[ 'message' ]      = $sMessage;
            $aParams[ 'user' ]         = $aContext[ 'user' ];
            $aParams[ 'user_ip' ]      = $aContext[ 'user_ip' ];
            $aParams[ 'location' ]     = $aContext[ 'location' ];
            $aParams[ 'date' ]         = $aContext[ 'date' ];
            $aParams[ 'microseconds' ] = intval( $aContext[ 'microseconds' ] );

            // send the request
            $oRequest  = new cRequest( 'curl' );
            $sResponse = $oRequest->Post( self::$sLogApi, $aParams );

            return $oRequest->GetStatus() == 200;
        }

        /**
         * Retrieves the contents from the log of the given type.
         *
         * @param  string      $sType    Type of log.
         * @param  null | int  $vAmount  Amount of entries to retrieve.
         *                               If null, all entries are retrieved.
         *
         * @return array       Example:
         *                         array(
         *                             array(
         *                                 'timestamp' => 123456789,
         *                                 'message'   => 'Message.',
         *                                 'extra'     => array()
         *                             )
         *                         )
         */
        public static function GetLogContents( $sType, $vAmount = null )
        {
            // initialize return value
            $aContents = array();

            // setup parameters for the request
            $aParams = array();
            $aParams[ 'c_token' ] = self::$sConsumerToken;
            $aParams[ 'app' ]     = self::$sConsumerToken;
            $aParams[ 'type' ]    = $sType;

            // add amount if possible
            if( is_numeric( $vAmount ) )
            {
                $aParams[ 'amount' ] = intval( $vAmount );
            }

            // send the request
            $oRequest  = new cRequest( 'curl' );
            $sResponse = $oRequest->Get( self::$sGetContentsApi . '?' . http_build_query( $aParams ) );

            // if the body was valid json, save the array
            $aTempLogContents = json_decode( $sResponse, true );
            if( is_array( $aTempLogContents ) )
            {
                $aContents = $aTempLogContents;
            }

            return $aContents;
        }

        /**
         * Cleans the log of the given type by removing
         * all entries older than the given timestamp.
         *
         * @param   int     $iTimestamp   Newest date that entries can have.
         *
         * @return  boolean
         */
        public static function ClearEntriesBefore( $sType, $iTimestamp )
        {
            // set params for the request
            $aParams = array();
            $aParams[ 'c_token' ]      = self::$sConsumerToken;
            $aParams[ 'app' ]          = self::$sConsumerToken;
            $aParams[ 'type' ]         = $sType;
            $aParams[ 'microseconds' ] = $iTimestamp;

            // send the request
            $oRequest  = new cRequest( 'curl' );
            $sResponse = $oRequest->Post( self::$sClearBeforeApi, $aParams );

            return $oRequest->GetStatus() == 200;
        }

        /**
         * Removes all log entries for the given log type.
         *
         * @param    string    $sType
         *
         * @return   boolean
         */
        public static function Clear( $sType )
        {
            // set params for the request
            $aParams = array();
            $aParams[ 'c_token' ] = self::$sConsumerToken;
            $aParams[ 'app' ]     = self::$sConsumerToken;
            $aParams[ 'type' ]    = $sType;

            // send the request
            $oRequest  = new cRequest( 'curl' );
            $sResponse = $oRequest->Get( self::$sClearApi . '?' . http_build_query( $aParams ) );

            return $oRequest->GetStatus() == 200;
        }

        /**
         * Retrieves the types of logs that have been created.
         *
         * @return array
         */
        public static function GetLogTypes()
        {
            // initialize return value
            $aLogTypes = array();

            // set params for the request
            $aParams = array();
            $aParams[ 'c_token' ] = self::$sConsumerToken;
            $aParams[ 'app' ]     = self::$sConsumerToken;

            // send the request
            $oRequest  = new cRequest( 'curl' );
            $sResponse = $oRequest->Get( self::$sGetTypesApi . '?' . http_build_query( $aParams ) );

            // if the body was valid json, save the array
            $aTempTypes = json_decode( $sResponse, true );
            if( is_array( $aTempTypes ) )
            {
                $aLogTypes = $aTempTypes;
            }

            return $aLogTypes;
        }

        /**
         * Retrieves stats for the type provided.
         *
         * @param   string   $sType
         *
         * @return  array
         */
        public static function GetStatsForType( $sType )
        {
            // initialize return value
            $aTypeStats = array();

            // set params for the request
            $aParams = array();
            $aParams[ 'c_token' ] = self::$sConsumerToken;
            $aParams[ 'app' ]     = self::$sConsumerToken;
            $aParams[ 'type' ]    = $sType;

            // send the request
            $oRequest  = new cRequest( 'curl' );
            $sResponse = $oRequest->Get( self::$sTypeStatsApi . '?' . http_build_query( $aParams ) );

            // if the body was valid json, save the array
            $aTempStats = json_decode( $sResponse, true );
            if( is_array( $aTempStats ) )
            {
                $aTypeStats = $aTempStats;
            }

            return $aTypeStats;
        }
    }
?>