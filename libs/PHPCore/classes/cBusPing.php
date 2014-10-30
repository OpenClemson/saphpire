<?php
    // include the base class
    require_once( sCORE_INC_PATH . '/classes/cBusBase.php' );

    // get the error handling class
    require_once( sCORE_INC_PATH . '/classes/cAnomaly.php' );

    /**
     * Main business class for form processing/database access.
     *
     * @author     Team Rah
     * @package    Core
     * @subpackage Ping
     * @subpackage Business
     * @version    0.1.0
     */
    class cBusPing extends cBusBase
    {
        /**
         * Handles the incoming ping request.
         *
         * Only allows GET method.
         *
         * @throws  Exception rethrows anything it catches.
         *
         * @return  string    Http status code
         */
        public function Ping()
        {
            try
            {
                // initialize return variable.
                $sPingResponse = '403';

                if( $_SERVER[ 'REQUEST_METHOD' ] === 'GET'
                     && !empty( $_GET )
                     && !empty( $_GET[ 'token' ] ) )
                {
                    // I'm alive!
                    $sPingResponse = '200';
                }
                else if( $_SERVER[ 'REQUEST_METHOD' ] !== 'GET' )
                {
                    // unsupported request method
                    $sPingResponse = '405';
                }
                // return response to controller.
                return $sPingResponse;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>