<?php
    // get the error handling class
    require_once( sCORE_INC_PATH . '/classes/cAnomaly.php' );

    /**
     * Base Request class
     *
     * contains properties and methods that are shared by
     * all requests that extend this class.
     *
     * @author     Michael Alphonso
     * @package    Core
     * @subpackage Request
     * @version    0.5.0
     */
    class cRequestBase
    {
        /**
         * @var array   allows us to store the headers of the response
         */
        protected $aResponseHeaders = array();

        /**
         * @var array  used to see that last sent headers. finalized before the request
         */
        public $aRequestHeaders = array();

        /**
         * @var array   allows us to store the body of the response
         */
        protected $sResponseBody = null;

        /**
         * @var string  contains the last effective url of the latest response
         */
        protected $sResponseLocation = null;

        /**
         * @var integer Connection timeout, in milliseconds.
         */
        public $iConnectTimeoutMs;

        /**
         * @var integer Response timeout, in milliseconds.
         */
        public $iResponseTimeoutMs;

        /**
         * @var integer Connection timeout, in seconds.
         */
        public $iConnectTimeout;

        /**
         * @var integer Response timeout, in seconds.
         */
        public $iResponseTimeout;

        /**
         * @var boolean Enables/Disables SSL certificate verification. Not recommended for
         *              production systems, but avoids the hassle of dealing with cert files,
         *              authorities and keys.
         */
        public $bVerifySslPeer = false;

        /**
         * Content type used during POST method
         *
         * application/x-www-form-urlencoded, multipart/form-data
         *
         * @var    string
         */
        public $sContentType     = '';

        /**
         * User-agent string.
         *
         * @var    string
         */
        public $sUserAgent       = 'X-Juggernaut';

        /**
         * Maximum number of redirects for a single request
         * default=10
         *
         * @var    integer
         */
        public $iMaxRedirects    = 10;

        /**
         * Flag to allow auto redirect
         * default=true
         *
         * @var    boolean
         */
        public $bAutoRedirect    = true;

        /**
         * Maximum number of times to re-attempt a failed request
         * default=1
         *
         * @var    integer
         */
        public $iMaxRetries      = 1;

        /**
         * Flag to set Connection: keep-alive header in request
         *
         * @var    boolean
         */
        public $bKeepAlive       = true;

        /**
         * Http Referer header
         *
         * @var    string
         */
        public $sReferer         = '';

        /**
         * Http header for default encoding
         *
         * @var    string
         */
        public $sAcceptEncoding  = 'gzip,deflate';

        /**
         * Http Accept header
         *
         * @var    string
         */
        public $sAccept          = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';

        /**
         * Accept-Language header
         *
         * @var    string
         */
        public $sAcceptLanguage  = 'en-US,en';

        /**
         * Host header.
         *
         * @var    string
         */
        public $sHost;

        /**
         * Array of request headers
         *
         * @var    array
         */
        protected $aHeaders;

        /**
         * @var string HTTP status code response from previous request.
         */
        public $sHttpStatusCode = null;

        /**
         * Instantiated request class object to reference
         *
         * @var    object
         */
        protected $oRequestObj = null;

        /**
         * sets the headers that should be included with every request. some
         * are optional and are set during the request if they are
         * left empty.
         *
         * these are the headers that can be set via properties by the public
         * user of this class
         *
         * @throws  Exception rethrows anything it catches.
         *
         * @return void
         */
        protected function SetDefaultHeaders()
        {
            // set Accept
            if ( !empty( $this->sAccept ) && !isset( $this->aHeaders[ 'Accept' ] ) )
            {
                $this->SetRequestHeader( 'Accept', $this->sAccept );
            }
            // set Accept-Language
            if ( !empty( $this->sAcceptLanguage ) && !isset( $this->aHeaders[ 'Accept-Language' ] ) )
            {
                $this->SetRequestHeader( 'Accept-Language', $this->sAcceptLanguage );
            }
            // set content-type
            if ( !empty( $this->sContentType ) && !isset( $this->aHeaders[ 'Content-Type' ] ) )
            {
                $this->SetRequestHeader( 'Content-Type', $this->sContentType );
            }

            // set keep-alive
            if ( $this->bKeepAlive && !isset( $this->aHeaders[ 'Connection' ] ) )
            {
                $this->SetRequestHeader( 'Connection', 'keep-alive' );
            }

            // set host header
            if ( !empty( $this->sHost ) && !isset( $this->aHeaders[ 'Host' ] ) )
            {
                $this->SetRequestHeader( 'Host', $this->sHost );
            }

            // set referer
            if ( !empty( $this->sReferer ) && !isset( $this->aHeaders[ 'Referer' ] ) )
            {
                $this->SetRequestHeader( 'Referer', $this->sReferer );
            }

            // set User-Agent
            if ( !empty( $this->sUserAgent ) && !isset( $this->aHeaders[ 'User-Agent' ] ) )
            {
                $this->SetRequestHeader( 'User-Agent', $this->sUserAgent );
            }

            // set encoding
            if ( !empty( $this->sAcceptEncoding ) && !isset( $this->aHeaders[ 'Accept-Encoding' ] ) )
            {
                $this->SetRequestHeader( 'Accept-Encoding', $this->sAcceptEncoding );
            }
        }

        /**
         * sets the header in the request headers array
         *
         * @param   string      $sHeaderKey     header option
         * @param   string      $sHeaderValue   value of header message
         *
         * @throws  Exception                   rethrows anything it catches.
         *
         * @return  boolean                     whether or not setting the header succeeded.
         */
        public function SetRequestHeader( $sHeaderKey, $sHeaderValue )
        {
            try
            {
                // initialize
                $bReturn = false;
                if ( !empty( $sHeaderKey ) && is_string( $sHeaderKey )
                    && !empty( $sHeaderValue ) && is_string( $sHeaderValue ) )
                {
                    $this->aHeaders[ $sHeaderKey ] = $sHeaderValue;
                    $bReturn                       = true;
                }

                return $bReturn;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * gets the property: sResponseBody
         *
         * @return  string
         */
        public function GetResponseBody()
        {
            return $this->sResponseBody;
        }

        /**
         * gets the property: aResponseHeaders
         *
         * @return  array
         */
        public function GetResponseHeaders()
        {
            return $this->aResponseHeaders;
        }

        /**
         * gets the property: sResponseLocation
         *
         * @return  string
         */
        public function GetResponseLocation()
        {
            return $this->sResponseLocation;
        }

        /**
         * gets the property: sHttpStatusCode
         *
         * @return  string
         */
        public function GetResponseCode()
        {
            return $this->sHttpStatusCode;
        }

        /**
         * attempt to return the full HTTP status message header
         *
         * @return  string | null
         */
        public function GetResponseStatus()
        {
            $iCount  = count( $this->aResponseHeaders );
            $sStatus = null;
            if ( $iCount > 1 )
            {
                $sStatus = isset( $this->aResponseHeaders[ $iCount - 1 ][ 0 ] ) ? $this->aResponseHeaders[ $iCount - 1 ][ 0 ] : null;
            }
            else if ( $iCount > 0 )
            {
                $sStatus = isset( $this->aResponseHeaders[ 0 ] ) ? $this->aResponseHeaders[ 0 ] : null;
            }
            return $sStatus;
        }

        /**
         * override request headers array
         *
         * @param    array    $aHeaders array(
         *                                  [ 'key' ] => 'value',
         *                                  ...
         *                              )
         * Example: array(
         *     'Content-type'     => 'application/json',
         *     'Connection'       => 'keep-alive',
         *     'Referer'          => 'http://clemson.edu',
         *     'User-Agent'       => 'X-PHP',
         *     'X-Requested-With' => 'XMLHttpRequest'
         * )
         *
         * @throws  Exception rethrows anything it catches.
         *
         * @return  void
         */
        public function SetRequestHeaders( array $aHeaders )
        {
            try
            {
                $this->aHeaders = $aHeaders;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>