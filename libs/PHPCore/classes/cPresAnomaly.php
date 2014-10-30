<?php
    /**
     * Presentation functionality for dealing with errors, exceptions, and debugging.
     *
     * @author     Team Rah
     * @package    Core
     * @subpackage Error
     * @version    0.5.3
     */
    class cPresAnomaly
    {
        /**
         * Error levels for mapping
         *
         * @var array
         */
        protected $aLevels = array(
            0     => 'None',
            1     => 'Fatal runtime errors (E_ERROR)',
            2     => 'Runtime warnings (E_WARNING)',
            4     => 'Compile-time parse errors (E_PARSE)',
            8     => 'Runtime notices (E_NOTICE)',
            16    => 'Fatal startup errors (E_CORE_ERROR)',
            32    => 'Startup warnings (E_CORE_WARNING)',
            64    => 'Fatal compile-time errors (E_COMPILE_ERROR)',
            128   => 'Compile-time warnings (E_COMPILE_WARNING)',
            256   => 'User-generated errors (E_USER_ERROR)',
            512   => 'User-generated warnings (E_USER_WARNING)',
            1024  => 'User-generated notices (E_USER_NOTICE)',
            2048  => 'Strict (E_STRICT)',
            4096  => 'Catchable fatal errors (E_RECOVERABLE_ERROR)',
            8192  => 'Runtime notices (E_DEPRECATED)',
            16384 => 'User-generated warnings (E_USER_DEPRECATED)',
            30719 => 'All errors and warnings except strict (E_ALL)'
        );

        /**
         * Used to determine whether or not output
         * should be formatted for HTML or CLI.
         *
         * @var boolean
         */
        protected $bIsCli = true;

        /**
         * Checks if we're running from HTML or CLI.
         * If HTML and needed templates do not exist
         * or are not readable, sets output mode to CLI.
         */
        public function __construct()
        {
            // set a flag for whether or not we should build HTML output
            $this->bIsCli = true;
            if( defined( 'bIS_CLI' ) && is_bool( bIS_CLI ) )
            {
                $this->bIsCli = bIS_CLI;
            }

            // if we're running from HTML, make sure all the needed files exist
            if( !$this->bIsCli )
            {
                // set the files needed for HTML output
                $aFilesNeeded   = array();
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/arg.html';
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/code.html';
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/code-line.html';
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/trace.html';
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/trace-line.html';
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/context.html';
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/context-all.html';
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/variable-format.html';
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/variable-format-null.html';
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/error-dev.html';
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/exception-dev.html';
                $aFilesNeeded[] = sCORE_INC_PATH . '/templates/anomaly/layout-dev.html';

                // check if files exist and are readable
                $iFileCount = count( $aFilesNeeded );
                for( $iFileCounter = 0; $iFileCounter < $iFileCount; ++$iFileCounter )
                {
                    if( !file_exists( $aFilesNeeded[ $iFileCounter ] )
                        || !is_readable( $aFilesNeeded[ $iFileCounter ] ) )
                    {
                        $this->bIsCli = true;
                        break;
                    }
                }
            }
        }

        /**
         * Returns an HTML formatted output of the type, variable name, and value.
         *
         * @param   string  $sType     The type of the variable.
         * @param   string  $sVarName  The variable's name.
         * @param   mixed   $vValue    The value of the variable.
         *
         * @return  string
         */
        public static function FormatVariableForHTML( $sType, $sVarName, $vValue )
        {
            // figure out the class to apply to the output
            $sClassType = in_array( $sType, array( 'boolean', 'integer', 'NULL', 'string', 'closure', 'object', 'array') ) ? $sType : 'object';

            // set the templates that we could use
            $sNullTemplate   = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/variable-format-null.html' );
            $sNormalTemplate = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/variable-format.html' );

            // build the output
            return str_replace(
                array( '_:_CLASS-TYPE_:_', '_:_TYPE_:_', '_:_VAR-NAME_:_', '_:_VALUE_:_' ),
                array( $sClassType, $sType, $sVarName, $vValue ),
                strtoupper( $vValue ) == 'NULL' ? $sNullTemplate : $sNormalTemplate
            );
        }

        /**
         * Returns a plain text formatted output of the type, variable name, and value.
         *
         * @param   string  $sType     The type of the variable.
         * @param   string  $sVarName  The variable's name.
         * @param   mixed   $vValue    The value of the variable.
         *
         * @return  string
         */
        public static function FormatVariableForCLI( $sType, $sVarName, $vValue )
        {
            // set the templates that we could use
            $sNullTemplate   = '_:_VAR-NAME_:_: null';
            $sNormalTemplate = "_:_VAR-NAME_:_:\nType: _:_TYPE_:_\nValue: _:_VALUE_:_\n";

            // build the output
            return str_replace(
                array( '_:_TYPE_:_', '_:_VAR-NAME_:_', '_:_VALUE_:_' ),
                array( $sType, $sVarName, $vValue ),
                $vValue == null ? $sNullTemplate : $sNormalTemplate
            );
        }

        /**
         * Builds a trace of all the calls from the beginning of
         * execution to when the error or exception occurred.
         *
         * @param   array   $aStackTrace
         *
         * @return  string  Generated HTML
         */
        public function FormatTrace( array $aStackTrace )
        {
            // initialize the return value
            $sFormatted    = '';
            $sTraceContext = '';

            // set the template for the stack trace line
            $sFormattedTemplate = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/trace-line.html' );

            // set the template for a line of code
            $sCodeWrapTemplate = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/code-line.html' );

            // set the code wrapper for each chunk of code
            $sContextTemplate = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/code.html' );

            // set the template for arguments to a function
            $sArgTemplate = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/arg.html' );

            // build each stack frame
            $iFrameCount = count( $aStackTrace );
            for( $iFrameCounter = 0; $iFrameCounter < $iFrameCount; ++$iFrameCounter )
            {
                // initialize the list of arguments
                $sArguments = '';

                // build the list of arguments if needed
                if( isset( $aStackTrace[ $iFrameCounter ][ 'args' ] ) )
                {
                    // initialize an array of arguments
                    $aArguments = array();

                    // build out the arguments according to their type
                    foreach( $aStackTrace[ $iFrameCounter ][ 'args' ] as $vArgument )
                    {
                        // build a unique id
                        $iTime = str_replace( '.', '', microtime( true ) );
                        $sId   = $iFrameCounter . $iTime;

                        // cleanup the output string
                        $sString = $vArgument[ 'string' ];
                        $sString = stripslashes( $sString );
                        $sString = str_replace( '\\', '/', $sString );
                        $sString = ltrim( $sString, '\'' );
                        $sString = rtrim( $sString, '\'' );
                        $sString = trim( $sString );

                        // get the class to get the background color
                        $sClassType = in_array( $vArgument[ 'type' ], array( 'boolean', 'integer', 'NULL', 'string', 'closure', 'object', 'array') ) ? $vArgument[ 'type' ] : 'object';

                        // set data to replace in the template
                        $aReplace = array();
                        $aReplace[ '_:_ID_:_'     ] = $sId;
                        $aReplace[ '_:_CLASS_:_'  ] = $sClassType;
                        $aReplace[ '_:_TYPE_:_'   ] = $vArgument[ 'type' ];
                        $aReplace[ '_:_STRING_:_' ] = $sString;


                        // replace the tags in the template
                        $aArguments[] = str_replace( array_keys( $aReplace ), $aReplace, $sArgTemplate );
                    }

                    // concatenate the arguments
                    $sArguments = implode( ', ', $aArguments );
                }

                // get the line if possible
                $sLineNum = '';
                if( isset( $aStackTrace[ $iFrameCounter ][ 'line' ] ) )
                {
                    $sLineNum = '('.$aStackTrace[ $iFrameCounter ][ 'line' ] . ')';
                }

                // get the file if possible
                $sFile = '';
                if( isset( $aStackTrace[ $iFrameCounter ][ 'file' ] ) )
                {
                    $sFile = $aStackTrace[ $iFrameCounter ][ 'file' ];
                }
                // get the class if the file doesn't exist
                else if( isset( $aStackTrace[ $iFrameCounter ][ 'class' ] ) )
                {
                    $sFile = $aStackTrace[ $iFrameCounter ][ 'class' ];
                }

                // build the trace context
                $sContext = '';
                if( isset( $aStackTrace[ $iFrameCounter ][ 'context' ] ) )
                {
                    // initialize the wrapper for the lines of code
                    $sCodeWrap = '';
                    foreach( $aStackTrace[ $iFrameCounter ][ 'context' ] as $iLine => $sLine )
                    {
                        // set whether or not this line should be highlighted
                        $sHighlight = '';
                        if( isset( $aStackTrace[ $iFrameCounter ][ 'line' ] )
                            && $aStackTrace[ $iFrameCounter ][ 'line' ] == $iLine )
                        {
                            $sHighlight = 'highlight';
                        }

                        // set the replacement data
                        $aReplace = array();
                        $aReplace[ '_:_HIGHLIGHT_:_' ] = $sHighlight;
                        $aReplace[ '_:_LINE_:_'      ] = $iLine;
                        $aReplace[ '_:_CODE_:_'      ] = rtrim( htmlentities( $sLine ) );

                        // replace the tags in the template
                        $sCodeWrap .= str_replace( array_keys( $aReplace ), $aReplace, $sCodeWrapTemplate );
                    }

                    // wrap all the lines of code
                    $sContext .= str_replace( '_:_CODE-WRAP_:_', $sCodeWrap, $sContextTemplate );
                }

                // set the function call
                $sFunction = '';
                if( isset( $aStackTrace[ $iFrameCounter ][ 'function' ] ) )
                {
                    if( isset( $aStackTrace[ $iFrameCounter ][ 'class' ] )
                        && isset( $aStackTrace[ $iFrameCounter ][ 'type' ] ) )
                    {
                        $aStackTrace[ $iFrameCounter ][ 'function' ] =
                            $aStackTrace[ $iFrameCounter ][ 'class' ]
                            . $aStackTrace[ $iFrameCounter ][ 'type' ]
                            . $aStackTrace[ $iFrameCounter ][ 'function' ];
                    }
                    $sFunction = ': ' . $aStackTrace[ $iFrameCounter ][ 'function' ] . '( ' . $sArguments . ' )';
                }

                // set the replacement data
                $aReplace = array();
                $aReplace[ '_:_FRAME_:_'    ] = $iFrameCounter;
                $aReplace[ '_:_FILE_:_'     ] = $sFile;
                $aReplace[ '_:_LINE_:_'     ] = $sLineNum;
                $aReplace[ '_:_FUNCTION_:_' ] = $sFunction;
                $aReplace[ '_:_CONTEXT_:_'  ] = $sContext;

                // add the stack trace line
                $sFormatted .= str_replace( array_keys( $aReplace ), $aReplace, $sFormattedTemplate );
            }

            // set the stack trace wrapper
            $sOutput = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/trace.html' );

            // set data to replace tags in the template
            $aReplace = array();
            $aReplace[ '_:_FORMATTED_:_' ] = $sFormatted;
            $aReplace[ '_:_CONTEXT_:_'   ] = $sTraceContext;

            // wrap the trace and return it
            return str_replace( array_keys( $aReplace ), $aReplace, $sOutput );
        }

        /**
         * Builds a trace of all the calls from the beginning of
         * execution to when the error occurred.
         *
         * @param   array   $aStackTrace
         *
         * @return  string  Generated HTML
         */
        public function FormatTraceForCLI( array $aStackTrace )
        {
            // initialize the return value
            $sOutput = '';

            // build each stack frame
            $iFrameCount = count( $aStackTrace );
            for( $iFrameCounter = 0; $iFrameCounter < $iFrameCount; ++$iFrameCounter )
            {
                // initialize the list of arguments
                $sArguments = '';

                // build the list of arguments if needed
                if( isset( $aStackTrace[ $iFrameCounter ][ 'args' ] ) )
                {
                    // initialize an array of arguments
                    $aArguments = array();

                    // build out the arguments according to their type
                    foreach( $aStackTrace[ $iFrameCounter ][ 'args' ] as $vArgument )
                    {
                        $aArguments[] = $vArgument[ 'type' ];
                    }
                    $sArguments = implode( ', ', $aArguments );
                }

                // get the line if possible
                $sLineNum = '';
                if( isset( $aStackTrace[ $iFrameCounter ][ 'line' ] ) )
                {
                    $sLineNum = '('.$aStackTrace[ $iFrameCounter ][ 'line' ] . ')';
                }

                // get the file if possible
                $sFile = '';
                if( isset( $aStackTrace[ $iFrameCounter ][ 'file' ] ) )
                {
                    $sFile = $aStackTrace[ $iFrameCounter ][ 'file' ];
                }
                // get the class if the file doesn't exist
                else if( isset( $aStackTrace[ $iFrameCounter ][ 'class' ] ) )
                {
                    $sFile = $aStackTrace[ $iFrameCounter ][ 'class' ];
                }

                // set the function call
                $sFunction = '';
                if( isset( $aStackTrace[ $iFrameCounter ][ 'function' ] ) )
                {
                    $sFunction = ': ' . $aStackTrace[ $iFrameCounter ][ 'function' ] . '( ' . $sArguments . ' )';
                }

                // add the stack trace line
                $sOutput .= '#' . $iFrameCounter . ' ' . $sFile . $sLineNum . $sFunction . "\n";
            }

            return $sOutput;
        }

        /**
         * Formats the variables found in the context
         * when the error/exception was thrown.
         *
         * @param   array   $aContext
         *
         * @return  string  Generated HTML
         */
        public function FormatContext( array $aContext )
        {
            // initialize the return value
            $sFormatted    = '';
            $sSuperGlobals = '';

            // make sure globals is gone
            unset( $aContext[ 'GLOBALS' ] );

            // set the list of super globals
            $aSuperGlobals = array( '_SERVER', '_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_REQUEST', '_ENV' );

            // build a readable version of all the variables that were in scope when the error or exception occurred
            foreach( $aContext as $sVarName => $aVariable )
            {
                if( in_array( $sVarName, $aSuperGlobals ) )
                {
                    $sSuperGlobals .= self::FormatVariableForHTML( $aVariable[ 'type' ], '$' . $sVarName, $aVariable[ 'string' ] );
                }
                else
                {
                    $sFormatted .= self::FormatVariableForHTML( $aVariable[ 'type' ], '$' . $sVarName, $aVariable[ 'string' ] );
                }
            }

            // build the list of constants
            $aConstants = get_defined_constants( true );
            $aConstants = $aConstants[ 'user' ];
            $sConstants = '';
            foreach( $aConstants as $sConstant => $vValue )
            {
                $sConstants .= self::FormatVariableForHTML( cBusAnomaly::GetType( $vValue ), $sConstant, cBusAnomaly::GetStringRepresentation( $vValue ) );
            }

            // build the context
            $sContext = '';
            if( !empty( $sFormatted ) )
            {
                // get the template
                $sContext = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/context.html' );

                // set replacement data
                $aReplace = array();
                $aReplace[ '_:_UNIQUE-NUMBER_' ] = $this->sUniqueNumber;
                $aReplace[ '_:_FORMATTED_:_'   ] = $sFormatted;

                // replace tags
                $sContext = str_replace( array_keys( $aReplace ), $aReplace, $sContext );
            }

            // set the template for the context, contents, and globals
            $sReturn = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/context-all.html' );

            // build the replacement array
            $aReplace = array();
            $aReplace[ '_:_CONTEXT_:_'       ] = $sContext;
            $aReplace[ '_:_UNIQUE-NUMBER_:_' ] = $this->sUniqueNumber;
            $aReplace[ '_:_CONSTANTS_:_'     ] = $sConstants;
            $aReplace[ '_:_GLOBALS_:_'       ] = $sSuperGlobals;

            // replace and return
            return str_replace( array_keys( $aReplace ), $aReplace, $sReturn );
        }

        /**
         * Default error output for developers.
         *
         * @param   string   $sAppName   The name of the application.
         * @param   int      $iNumber    Error number. Corresponds to the level of error.
         * @param   string   $sMessage   Message telling why the error occurred.
         * @param   string   $sFile      File the error occurred in.
         * @param   int      $iLine      Line the error occurred on.
         * @param   array    $aContext   Variables in context at the time the error occurred.
         * @param   array    $aTrace     Stack trace with all functions or methods leading up to the error.
         */
        public function DevErrorOutput( $sAppName, $iNumber, $sMessage, $sFile, $iLine, array $aContext, array $aTrace )
        {
            // initialize output
            $sOutput = '';

            // check if we need output for HTML or CLI
            if( $this->bIsCli )
            {
                $sOutput  = "A new Error approaches!\n\n";
                $sOutput .= 'Message: ' . $sMessage . "\n";
                $sOutput .= "Trace: \n" . $this->FormatTraceForCLI( $aTrace );
            }
            else
            {
                // set a unique number for links
                $this->sUniqueNumber = $iLine . $iNumber;

                // build the error template
                $aReplace = array();
                $aReplace[ '_:_APP-NAME_:_' ]      = $sAppName;
                $aReplace[ '_:_UNIQUE-NUMBER_:_' ] = $this->sUniqueNumber;
                $aReplace[ '_:_MESSAGE_:_'       ] = $sMessage;
                $aReplace[ '_:_TYPE_:_'          ] = $this->aLevels[ $iNumber ];
                $aReplace[ '_:_FILE_:_'          ] = $sFile;
                $aReplace[ '_:_LINE_:_'          ] = $iLine;
                $aReplace[ '_:_TRACE_:_'         ] = $this->FormatTrace( $aTrace );
                $aReplace[ '_:_CONTEXT_:_'       ] = $this->FormatContext( $aContext );

                $sDevErrorTemplate = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/error-dev.html' );
                $sErrorTemplate    = str_replace( array_keys( $aReplace ), $aReplace, $sDevErrorTemplate );

                // set the base path for resources
                $sBasePath = '/';
                if( isset( $_SERVER )
                    && is_array( $_SERVER )
                    && isset( $_SERVER[ 'DOCUMENT_ROOT' ] )
                    && is_string( $_SERVER[ 'DOCUMENT_ROOT' ] )
                    && defined( 'sBASE_INC_PATH' )
                    && is_string( sBASE_INC_PATH ) )
                {
                    $sBasePath = str_replace( $_SERVER[ 'DOCUMENT_ROOT' ], '', sBASE_INC_PATH ) . '/';
                }

                // output the formatted error
                $sDevLayout = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/layout-dev.html' );
                $sOutput    = str_replace(
                    array( '_:_BODY_:_', '_:_BASE-PATH_:_' ),
                    array( $sErrorTemplate, $sBasePath ),
                    $sDevLayout
                );
            }

            // output the information and make sure execution stops
            echo $sOutput;
            die();
        }

        /**
         * Default exception handling for developers.
         *
         * @param   string      $sAppName     The name of the application.
         * @param   Exception   $oException   The exception that occurred.
         * @param   array       $aTrace       The stack trace.
         * @param   array       $aContext     Variables in context at the time of the exception.
         */
        public function DevExceptionOutput( $sAppName, $oException, array $aTrace, array $aContext )
        {
            // initialize output
            $sOutput = '';

            // check if this is running from HTML or CLI
            if( $this->bIsCli )
            {
                $sOutput  = 'A wild '   . get_class( $oException )  . " appears!\n\n";
                $sOutput .= 'Message: ' . $oException->getMessage() . "\n";
                $sOutput .= "Trace: \n" . $oException->getTraceAsString() . "\n\n";
            }
            else
            {
                // get the lines and code for the exception
                $iLine   = $oException->getLine();
                $iNumber = $oException->getCode();

                // set a unique number for links
                $this->sUniqueNumber = $iLine . $iNumber;

                // build the error template
                $aReplace = array();
                $aReplace[ '_:_APP-NAME_:_'      ] = $sAppName;
                $aReplace[ '_:_CLASS_:_'         ] = get_class( $oException );
                $aReplace[ '_:_UNIQUE-NUMBER_:_' ] = $this->sUniqueNumber;
                $aReplace[ '_:_MESSAGE_:_'       ] = $oException->getMessage();
                $aReplace[ '_:_CODE_:_'          ] = $iNumber;
                $aReplace[ '_:_FILE_:_'          ] = $oException->getFile();
                $aReplace[ '_:_LINE_:_'          ] = $iLine;
                $aReplace[ '_:_TRACE_:_'         ] = $this->FormatTrace( $aTrace );
                $aReplace[ '_:_CONTEXT_:_'       ] = $this->FormatContext( $aContext );

                $sDevExceptionTemplate = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/exception-dev.html' );
                $sExceptionTemplate    = str_replace( array_keys( $aReplace ), $aReplace, $sDevExceptionTemplate );

                // set the base path for resources
                $sBasePath = '/';
                if( isset( $_SERVER )
                    && is_array( $_SERVER )
                    && isset( $_SERVER[ 'DOCUMENT_ROOT' ] )
                    && is_string( $_SERVER[ 'DOCUMENT_ROOT' ] )
                    && defined( 'sBASE_INC_PATH' )
                    && is_string( sBASE_INC_PATH ) )
                {
                    $sBasePath = str_replace( $_SERVER[ 'DOCUMENT_ROOT' ], '', sBASE_INC_PATH ) . '/';
                }

                // output the formatted exception
                $sDevLayout = file_get_contents( sCORE_INC_PATH . '/templates/anomaly/layout-dev.html' );
                $sOutput    = str_replace(
                    array( '_:_BODY_:_', '_:_BASE-PATH_:_' ),
                    array( $sExceptionTemplate, $sBasePath ),
                    $sDevLayout
                );
            }

            // output the information and make sure execution stops
            echo $sOutput;
            die();
        }

        /**
         * Default error and exception handling for users.
         *
         * If a template 'error.html' exists, it's contents are
         * displayed instead of the default error message.
         *
         * @param   string   $sAppName   The name of the application.
         */
        public function UserOutput( $sAppName )
        {
            // set a default message
            $sOutput = 'An unexpected problem has occurred.';

            // check if a template has been set by the application
            $sErrorTmpl = sBASE_INC_PATH . '/templates/error.html';
            if( file_exists( $sErrorTmpl ) && is_readable( $sErrorTmpl ) )
            {
                $sOutput = file_get_contents( $sErrorTmpl );
            }
            echo $sOutput;

            // make sure execution stops
            die();
        }
    }
?>