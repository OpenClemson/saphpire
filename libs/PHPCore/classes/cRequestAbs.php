<?php
    // get the error handling class
    require_once( sCORE_INC_PATH . '/classes/cAnomaly.php' );

    /**
     * Request class abstraction layer
     *
     * Usage:
     *     $oCurl = cRequestAbs::GetObj( 'curl' );
     *     $oHttp = cRequestAbs::GetObj( 'http' ) | cRequestAbs::GetObj( 'httprequest' );
     *
     * @author     Team Rah
     * @package    Core
     * @subpackage Request
     * @version    0.1.0
     */
    class cRequestAbs
    {
        /**
         * Static default requestor.
         *
         * @var    string
         */
        public static $sDefault = 'curl';

        /**
         * Flag to turn of debug output of headers
         * in the response body.
         *
         * @var    object
         */
        protected static $bDebugOutput = false;

        /**
         * Creates a request class object based on the requestor
         * you have chosen.
         *
         * @param   string  $sRequestor Requestor class (curl, http|httprequest)
         *
         * @throws  Exception rethrows anything it catches.
         *
         * @return  object  Request class object
         */
        public static function GetObj( $sRequestor = '' )
        {
            try
            {
                // initialize request object
                $oRequestObj = null;
                $sRequestorToUse = strtolower( !empty( $sRequestor ) ? $sRequestor : self::$sDefault );

                if( empty( $sRequestorToUse ) )
                {
                    throw new Exception( 'No default requestor specified. Must choose one.' );
                }
                else
                {
                    if( $sRequestorToUse == 'curl' && extension_loaded( 'curl' ) )
                    {
                        require_once( sCORE_INC_PATH . '/classes/cRequestCurl.php' );
                        $oRequestObj = new cRequestCurl( self::$bDebugOutput );
                    }
                    else if(  ( $sRequestorToUse == 'http'      || $sRequestorToUse == 'httprequest' )
                            && ( extension_loaded( 'http' ) || extension_loaded( 'pecl_http' ) ) )
                    {
                        require_once( sCORE_INC_PATH . '/classes/cRequestHttp.php' );
                        $oRequestObj = new cRequestHttp( self::$bDebugOutput );
                    }
                    else
                    {
                        // do nothing
                        throw new Exception( 'Invalid requestor selected.' );
                    }
                }
                return $oRequestObj;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>