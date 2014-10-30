<?php
    // get the logger interface
    require_once( sCORE_INC_PATH . '/classes/ifLogger.php' );

    // get the custom exception
    require_once( sCORE_INC_PATH . '/classes/cStringCodeException.php' );

    // require the business functionality
    require_once( sCORE_INC_PATH . '/classes/cBusAnomaly.php' );

    /**
     * Error and exception handling.
     *
     * @author      Team Rah
     * @package     Core
     * @subpackage  Anomaly
     * @version     1.2.0
     */
    class cAnomaly
    {
        /**
         * Flag for whether or not an error or exception occured.
         *
         * @var boolean
         */
        protected static $bProblem = false;

        /**
         * Logger for error and exception handling.
         *
         * @var string
         */
        protected static $sLogger;

        /**
         * Function that will handle developer output when an exception occurs.
         *
         * @var callable
         */
        protected static $callDevExceptionOutput;

        /**
         * Function that will handle user output when an exception occurs.
         *
         * @var callable
         */
        protected static $callUserExceptionOutput;

        /**
         * Function that will handle developer output when an error occurs.
         *
         * @var callable
         */
        protected static $callDevErrorOutput;

        /**
         * Function that will handle user output when an error occurs.
         *
         * @var callable
         */
        protected static $callUserErrorOutput;

        /**
         * Instance of this class.
         *
         * @var cAnomaly
         */
        protected static $oInstance;

        /**
         * The name of the application.
         *
         * @var string
         */
        protected static $sAppName = 'App Name was not set.';

        /**
         * Flag for whether or not a developer is logged in.
         *
         * @var boolean
         */
        protected static $bDevLoggedIn = false;

        /**
         * Class that handles business functionality.
         *
         * @var cBusAnomaly
         */
        protected $oBusAnomaly;

        /**
         * The last exception to occur.
         *
         * @var Exception
         */
        protected $oLastException = null;

        /**
         * The last error that occurred.
         *
         * @var array
         */
        protected $aLastError = array();

        /**
         * Creates an instance of this class and loads
         * a logger to use when things go wrong.
         */
        private function __construct()
        {
            // set error and exceptions handlers
            register_shutdown_function( array( $this, 'FatalErrorHandler' ) );
            set_exception_handler( array( 'cAnomaly', 'ExceptionHandler' ) );
            set_error_handler( array( $this, 'ErrorHandler' ) );

            // get a business object to work with
            $this->oBusAnomaly = new cBusAnomaly();
        }

        /**
         * Sets the name of the application for error and exception output.
         *
         * @param   string   $sAppName
         */
        public function SetApplicationName( $sAppName )
        {
            if( is_string( $sAppName )
                && !empty( $sAppName ) )
            {
                self::$sAppName = $sAppName;
            }
        }

        /**
         * Checks if a developer is logged in or we are in a development
         * environment and sets the internal flag for whether or not to
         * output a developer- or user-friendly view.
         */
        public function SetDevLoggedIn()
        {
            // set whether or not a developer is logged in
            if( !defined( 'sAPPLICATION_ENV' )
                || ( defined( 'sAPPLICATION_ENV' ) && sAPPLICATION_ENV === 'dev' )
                || IsDevLoggedIn() )
            {
                self::$bDevLoggedIn = true;
            }
            else
            {
                self::$bDevLoggedIn = false;
            }

            // turn off default error handling
            ini_set( 'display_errors', self::$bDevLoggedIn );
            ini_set( 'display_startup_errors', self::$bDevLoggedIn );
        }

        /**
         * Gets an instance of this object.
         *
         * @param   string   $sLogger
         */
        public static function GetInstance( $sLogger = null )
        {
            // create an instance if needed
            if( empty( self::$oInstance ) )
            {
                self::$oInstance = new cAnomaly( $sLogger );
            }

            return self::$oInstance;
        }

        /**
         * Returns whether or not an error or exception has occurred.
         *
         * @return  boolean
         */
        public static function Absorbed()
        {
            return self::$bProblem;
        }

        /**
         * Returns the last error that occurred.
         *
         * @return array
         */
        public static function GetLastError()
        {
            return self::GetInstance()->aLastError;
        }

        /**
         * Returns the last exception that occurred.
         *
         * @return Exception
         */
        public static function GetLastException()
        {
            return self::GetInstance()->oLastException;
        }

        /**
         * Sets the developer presentation handler for exceptions.
         *
         * @param   callable   $oCall
         */
        public function SetDevExceptionOutput( $oCall )
        {
            if( !is_callable( $oCall ) )
            {
                throw new Exception( 'Exception handler provided is not callable.' );
            }

            self::$callDevExceptionOutput = $oCall;
        }

        /**
         * Sets the user presentation handler for exceptions.
         *
         * @param   callable   $oCall
         */
        public function SetUserExceptionOutput( $oCall )
        {
            if( !is_callable( $oCall ) )
            {
                throw new Exception( 'Exception handler provided is not callable.' );
            }

            self::$callUserExceptionOutput = $oCall;
        }

        /**
         * Sets the developer presentation handler for errors.
         *
         * @param   callable   $oCall
         */
        public function SetDevErrorOutput( $oCall )
        {
            if( !is_callable( $oCall ) )
            {
                throw new Exception( 'Error handler provided is not callable.' );
            }

            self::$callDevErrorOutput = $oCall;
        }

        /**
         * Sets the user presentation handler for errors.
         *
         * @param   callable   $oCall
         */
        public function SetUserErrorOutput( $oCall )
        {
            if( !is_callable( $oCall ) )
            {
                throw new Exception( 'Error handler provided is not callable.' );
            }

            self::$callUserErrorOutput = $oCall;
        }

        /**
         * Sets the logging class to use to log when errors or exceptions occur.
         *
         * @param   string   $sLogger
         */
        public function SetLogger( $sLogger )
        {
            // make sure the class exists and it implements the logging interface
            if( !is_string( $sLogger ) || !class_exists( $sLogger ) || !in_array( 'ifLogger', class_implements( $sLogger ) ) )
            {
                throw new Exception( 'Logger provided is not an instance of ifLogger.' );
            }

            self::$sLogger = $sLogger;
        }

        /**
         * Builds a new exception with the provided exception set as previous
         * so we can maintain the full exception path.
         *
         * @param   Exception   $oException   Exception that has been caught.
         *
         * @return  Exception
         */
        public static function BubbleException( $oException )
        {
            // set the flag so it can be checked for later
            self::$bProblem = true;

            // save the exception
            self::GetInstance()->oLastException = $oException;

            // initialize the class of the new exception
            $sClass = 'Exception';

            // check if this is a string code exception
            if( is_string( $oException->getCode() ) )
            {
                $sClass = 'cStringCodeException';
            }

            return new $sClass( $oException->getMessage(), $oException->getCode(), $oException );
        }

        /**
         * Handler for non-fatal PHP errors.
         *
         * Sets the class flag for when an problem occurs, attempts to log the error,
         * and then sends the error information to the appropriate output handler based on user type.
         *
         * @param  integer  $iNumber   Error number.
         * @param  string   $sMessage  Error message.
         * @param  string   $sFile     File error occurred in.
         * @param  string   $sLine     Line that caused the error.
         * @param  array    $aContext  Variables in scope at the time the error occurred.
         */
        public function ErrorHandler( $iNumber, $sMessage, $sFile, $sLine, array $aContext )
        {
            // set if a developer is logged in
            $this->SetDevLoggedIn();

            // restore the default handler so that things shouldn't blow up
            restore_error_handler();

            // set the flag so it can be checked for later
            self::$bProblem = true;

            // get the error
            $aLastError = error_get_last();

            // if we have a fatal error, let the fatal handler take care of it
            if( $aLastError === null )
            {
                // save this error
                $this->aLastError = array();
                $this->aLastError[ 'number' ]  = $iNumber;
                $this->aLastError[ 'message' ] = $sMessage;
                $this->aLastError[ 'file' ]    = $sFile;
                $this->aLastError[ 'line' ]    = $sLine ;

                // get the backtrace
                $aTrace = debug_backtrace();
                $aTrace = $this->oBusAnomaly->GetTraceContext( $aTrace );

                // filter out any information that shouldn't be logged
                if( isset( $aContext[ 'GLOBALS' ] ) )
                {
                    unset( $aContext[ 'GLOBALS' ] );
                }
                $aCopyContext  = $aContext;
                $aSavedContext = $this->oBusAnomaly->FilterArray( $aCopyContext );

                // setup the error array to send back and log it
                $aNewContext = array();
                $aNewContext[ 'code'    ] = $iNumber;
                $aNewContext[ 'file'    ] = $sFile;
                $aNewContext[ 'line'    ] = $sLine;
                $aNewContext[ 'context' ] = print_r( $aSavedContext, true );
                unset( $aSavedContext );

                // log the error if possible
                if( isset( self::$sLogger ) )
                {
                    try
                    {
                        @call_user_func_array(
                            array( self::$sLogger, 'Log' ),
                            array( 'error', $sMessage, $aNewContext )
                        );
                    }
                    catch( Exception $oException )
                    {
                        // if we can't log, we should send an email
                    }
                }

                // check if this has been suppressed or not
                if( error_reporting() != 0 )
                {
                    // update the context into a more user friendly version
                    $aContext = $this->oBusAnomaly->FormatContext( $aContext );

                    // figure out which presentation output to call
                    $oPresCallable = self::$bDevLoggedIn ? self::$callDevErrorOutput : self::$callUserErrorOutput;

                    // call the error output function if possible
                    if( is_callable( $oPresCallable ) )
                    {
                        echo call_user_func_array(
                            $oPresCallable,
                            array( self::$sAppName, $iNumber, $sMessage, $sFile, $sLine, $aContext, $aTrace )
                        );
                    }
                }
            }
        }

        /**
         * Handler for fatal PHP errors.
         *
         * Sets the class flag for when an problem occurs, attempts to log the error,
         * and then sends the error information to the appropriate output handler based on user type.
         */
        public function FatalErrorHandler()
        {
            // set if a developer is logged in
            $this->SetDevLoggedIn();

            // get the error info
            $aLastError = error_get_last();

            // ensure this is a fatal error
            if( !empty( $aLastError ) && ( $aLastError[ 'type' ] !== null ) )
            {
                // restore the default handler so that things shouldn't blow up
                restore_error_handler();

                // save this error
                $this->aLastError = $aLastError;

                // get the stack trace
                $aTrace = debug_backtrace();

                // Setup the error aray to send back and log it.
                $aContext = array();
                $aContext[ 'code'  ] = $aLastError[ 'type' ];
                $aContext[ 'file'  ] = $aLastError[ 'file' ];
                $aContext[ 'line'  ] = $aLastError[ 'line' ];

                // log the error is possible
                if( isset( self::$sLogger ) )
                {
                    try
                    {
                        @call_user_func_array(
                            array( self::$sLogger, 'Log' ),
                            array( 'fatal', $aLastError[ 'message' ], $aContext )
                       );
                    }
                    catch( Exception $oException )
                    {
                        // if we can't log, then we should send an email
                    }
                }

                // figure out which presentation output to call
                $oPresCallable = self::$bDevLoggedIn ? self::$callDevErrorOutput : self::$callUserErrorOutput;

                // make sure the current file and line are in the trace
                $iFrameCount = count( $aTrace );
                for( $iFrameCounter = 0; $iFrameCounter < $iFrameCount; ++$iFrameCounter )
                {
                    if( isset( $aTrace[ $iFrameCounter ][ 'file' ] )
                        && $aTrace[ $iFrameCounter ][ 'file' ] == $aLastError[ 'file' ]
                        && isset( $aTrace[ $iFrameCounter ][ 'line' ] )
                        && $aTrace[ $iFrameCounter ][ 'line' ] == $aLastError[ 'line' ] )
                    {
                        array_unshift( $aTrace, $aLastError );
                        break;
                    }
                }

                // format the stack trace
                $aTrace = $this->oBusAnomaly->GetTraceContext( $aTrace );

                // add the context
                $aSuperGlobals = array(
                    '_SERVER' => $_SERVER,  '_GET'    => $_GET,     '_POST'    => $_POST,    '_FILES' => $_FILES,
                    '_COOKIE' => $_COOKIE, '_SESSION' => $_SESSION, '_REQUEST' => $_REQUEST, '_ENV'   => $_ENV,
                );
                $aContext = $this->oBusAnomaly->FormatContext( $aSuperGlobals );

                // call the exception output function is possible
                if( is_callable( $oPresCallable ) )
                {
                    echo call_user_func_array(
                        $oPresCallable,
                        array(
                            self::$sAppName,
                            $aLastError[ 'type' ],
                            $aLastError[ 'message' ],
                            $aLastError[ 'file' ],
                            $aLastError[ 'line' ],
                            $aContext,
                            $aTrace
                        )
                    );
                }
            }
        }

        /**
         * Centralized exception handling.
         *
         * All uncaught exceptions will be handled by this function.
         * All caught exceptions will bubble up into this function.
         *
         * @param  Exception $oException
         */
        public static function ExceptionHandler( $oException )
        {
            // set if a developer is logged in
            self::GetInstance()->SetDevLoggedIn();

            // set the flag so it can be checked for later
            self::$bProblem = true;

            // save the exception
            self::GetInstance()->oLastException = $oException;

            // restore the default handler so that things shouldn't blow up
            restore_exception_handler();

            // get the first exception
            $oFirstException = $oException;
            while( $oFirstException->getPrevious() !== null )
            {
                $oFirstException = $oFirstException->getPrevious();
            }

            // initialize the context for the exception
            $aContext = array();
            $aContext[ 'code' ] = $oFirstException->getCode();
            $aContext[ 'file' ] = $oFirstException->getFile();
            $aContext[ 'line' ] = $oFirstException->getLine();

            // log the exception if possible
            if( isset( self::$sLogger ) )
            {
                try
                {
                    // set the type of log
                    $sType = 'exception';
                    if( is_string( $aContext[ 'code' ] ) )
                    {
                        $sType = $aContext[ 'code' ];
                        unset( $aContext[ 'code' ] );
                    }

                    // log the exception
                    @call_user_func_array(
                        array( self::$sLogger, 'Log' ),
                        array( $sType, $oFirstException->getMessage(), $aContext )
                    );
                }
                catch( Exception $oIgnoredException )
                {
                    // if we can't log, then we should send an email
                }
            }

            // get the trace of the exception
            $aTrace = $oFirstException->getTrace();

            // add the original exception trace information
            $aFirstData = array(
                'file'     => $oFirstException->getFile(),
                'line'     => $oFirstException->getLine(),
                'function' => '__construct',
                'class'    => get_class( $oFirstException ),
                'type'     => '->',
                'args'     => array( $oFirstException->getMessage() )
            );
            array_unshift( $aTrace, $aFirstData );

            // format the trace arguments
            $aTrace = self::GetInstance()->oBusAnomaly->GetTraceContext( $aTrace );

            // set the context to display
            $aSuperGlobals = array(
                '_SERVER' => $_SERVER,  '_GET'    => $_GET,     '_POST'    => $_POST,    '_FILES' => $_FILES,
                '_COOKIE' => $_COOKIE, '_SESSION' => $_SESSION, '_REQUEST' => $_REQUEST, '_ENV'   => $_ENV,
            );
            $aContext = self::GetInstance()->oBusAnomaly->FormatContext( $aSuperGlobals );

            // figure out which presentation output to call
            $oPresCallable = self::$bDevLoggedIn ? self::$callDevExceptionOutput : self::$callUserExceptionOutput;

            // call the exception output function if possible
            if( is_callable( $oPresCallable ) )
            {
                echo call_user_func_array(
                    $oPresCallable,
                    array( self::$sAppName, $oFirstException, $aTrace, $aContext )
                );
            }
        }
    }
?>