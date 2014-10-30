<?php
    /**
     * Handles business functionality for cAnomaly.
     *
     * Filters and prepares data for logging as well as display on error and exception pages.
     *
     * @author  Team Rah
     * @package Core
     * @package Anomaly
     * @version 0.5.5
     */
    class cBusAnomaly
    {
        /**
         * List of all global variables.
         *
         * @var array
         */
        protected $aSuperGlobals = array(
            '_SERVER', '_GET',     '_POST',    '_FILES',
            '_COOKIE', '_SESSION', '_REQUEST', '_ENV'
        );

        /**
         * List of filters to apply to an array of variables before the array is logged.
         * Useful for removing sensitive information from logs.
         *
         * @var array
         */
        protected $aContextFilters = array();

        /**
         * Initializes the list of context filters.
         */
        public function __construct()
        {
            $this->AddArrayFilter( 'passwords', array( $this, 'FilterPasswords' ) );
        }

        /**
         * Gets the type of a variable.
         *
         * @param   mixed   $vVariable
         *
         * @return  string
         */
        public static function GetType( $vVariable )
        {
            // convert variable into readable format based on type
            $sType = gettype( $vVariable );

            if( $sType == 'object' )
            {
                $sType = is_callable( $vVariable ) ? 'closure' : get_class( $vVariable );
            }

            return $sType;
        }

        /**
         * Converts a variable into readable output.
         *
         * @param   mixed   $vVariable
         *
         * @param   string
         */
        public static function GetStringRepresentation( $vVariable )
        {
            // convert variable into readable format based on type
            $sType = gettype( $vVariable );

            // initialize the return value
            $sStringRepresentation = '';

            // build string representation of the variable's value
            switch( $sType )
            {
                case 'boolean':
                    $sStringRepresentation = $vVariable ? 'true' : 'false' ;
                    break;

                case 'object':
                    // check if this object is a closure or an instance of a class
                    if( is_callable( $vVariable ) )
                    {
                        // get a reflection object to work with
                        $oReflFunc = new ReflectionFunction( $vVariable );

                        // open file and skip to the first line of the closure
                        $oFile = new SplFileObject( $oReflFunc->getFileName() );
                        $oFile->seek( $oReflFunc->getStartLine() - 1 );

                        // Retrieve all of the lines that contain code for the closure
                        $sStringRepresentation = '';
                        while( $oFile->key() < $oReflFunc->getEndLine() )
                        {
                            $sStringRepresentation .= $oFile->current();
                            $oFile->next();
                        }

                        // Only keep the code defining that closure
                        $iStart = strpos( $sStringRepresentation, 'function');
                        $iEnd   = strrpos( $sStringRepresentation, '}');
                        $sStringRepresentation = substr( $sStringRepresentation, $iStart, $iEnd - $iStart + 1 );

                        break;
                    }

                // convert the output into valid php code
                default:
                    ob_start();
                    print_r( $vVariable );
                    $sOutput = ob_get_clean();
                    $sStringRepresentation = $sOutput;
                    $sStringRepresentation = php_sapi_name() === 'cli' ? $sStringRepresentation : htmlentities( $sStringRepresentation );
                    break;
            }

            return $sStringRepresentation;
        }

        /**
         * Formats the context variables from an
         * error into more a readable format.
         *
         * @param   array   $aContext
         *
         * @return  array
         */
        public function FormatContext( array $aContext )
        {
            // build a readable version of all the variables that were in scope when the error or exception occurred
            if( count( $aContext ) > 0 )
            {
                // initialize a new context array
                $aNewContext = array();

                // don't include $GLOBALS because it has a reference to itself
                unset( $aContext[ 'GLOBALS' ] );

                // build the new context from the old
                foreach( $aContext as $sVarName => $vValue )
                {
                    $aNewContext[ $sVarName ] = array(
                        'type'   => self::GetType( $vValue ),
                        'string' => self::GetStringRepresentation( $vValue )
                    );
                }

                // assign the new context to the old
                $aContext = $aNewContext;
            }

            return $aContext;
        }

        /**
         * Gets the code around the problematic line and
         * converts arguments to easily readable versions
         * so we can quickly diagnose the problem.
         *
         * @param   array   $aStackTrace
         *
         * @return  array
         */
        public function GetTraceContext( array $aStackTrace )
        {
            // save the original just in case
            $aOriginal = $aStackTrace;

            // remove anomaly class functions
            $iFrameCount = count( $aStackTrace );
            for( $iFrameCounter = 0; $iFrameCounter < $iFrameCount; ++$iFrameCounter )
            {
                if( isset( $aStackTrace[ $iFrameCounter ][ 'file' ] )
                    && ( strpos( 'cAnomaly', $aStackTrace[ $iFrameCounter ][ 'file' ] ) !== false
                         || strpos( 'cBusAnomaly', $aStackTrace[ $iFrameCounter ][ 'file' ] ) !== false
                         || strpos( 'cPresAnomaly', $aStackTrace[ $iFrameCounter ][ 'file' ] ) !== false ) )
                {
                    unset( $aStackTrace[ $iFrameCounter ] );
                }
                elseif( isset( $aStackTrace[ $iFrameCounter ][ 'class' ] )
                        && ( strpos( 'cAnomaly', $aStackTrace[ $iFrameCounter ][ 'class' ] ) !== false
                             || strpos( 'cBusAnomaly', $aStackTrace[ $iFrameCounter ][ 'class' ] ) !== false
                             || strpos( 'cPresAnomaly', $aStackTrace[ $iFrameCounter ][ 'class' ] ) !== false ) )
                {
                    unset( $aStackTrace[ $iFrameCounter ] );
                }
            }

            // reset array keys
            $aStackTrace = array_values( $aStackTrace );

            // if we cleared out everything, reset it
            $aStackTrace = empty( $aStackTrace ) ? $aOriginal : $aStackTrace;

            // build out the context trace
            $iFrameCount = count( $aStackTrace );
            for( $iFrameCounter = 0; $iFrameCounter < $iFrameCount; ++$iFrameCounter )
            {
                // get the file contents if possible
                if( isset( $aStackTrace[ $iFrameCounter ][ 'file' ] ) )
                {
                    // get code if possible
                    if( isset( $aStackTrace[ $iFrameCounter ][ 'line' ] )
                        && file_exists( $aStackTrace[ $iFrameCounter ][ 'file' ] )
                        && is_readable( $aStackTrace[ $iFrameCounter ][ 'file' ] ) )
                    {
                        // read the file into an array
                        $aFile = file( $aStackTrace[ $iFrameCounter ][ 'file' ] );

                        // strip the base path off the file
                        $aStackTrace[ $iFrameCounter ][ 'file' ] = str_replace( sBASE_INC_PATH, '', $aStackTrace[ $iFrameCounter ][ 'file' ] );

                        // figure out the start and end point for the trace context
                        $iLine  = intval( $aStackTrace[ $iFrameCounter ][ 'line' ] );
                        $iStart = ( $iLine - 8 ) >= 0 ? $iLine - 8 : 0;
                        $iEnd   = ( $iLine + 2 ) <= count( $aFile ) ? ( $iLine + 2 ) : count( $aFile );

                        // initialize the context for this frame
                        $aStackTrace[ $iFrameCounter ][ 'context' ] = array();

                        // find the initial indentation level
                        $iIndentation = strlen( $aFile[ $iStart ] ) - strlen( ltrim( $aFile[ $iStart ] ) );

                        // build each line of the trace context
                        for( $iCurrentLine = $iStart; $iCurrentLine < $iEnd; ++$iCurrentLine )
                        {
                            // get the actual line number, not the array index
                            $iRealLine = $iCurrentLine + 1;

                            // trim the indentation and set the line
                            $aStackTrace[ $iFrameCounter ][ 'context' ][ $iRealLine ] = $aFile[ $iCurrentLine ];
                        }
                    }
                }

                // format the arguments into easily readable versions
                if( isset( $aStackTrace[ $iFrameCounter ][ 'args' ] ) )
                {
                    $iArgCount = count( $aStackTrace[ $iFrameCounter ][ 'args' ] );
                    for( $iArgCounter = 0; $iArgCounter < $iArgCount; ++$iArgCounter )
                    {
                        $aStackTrace[ $iFrameCounter ][ 'args' ][ $iArgCounter ] = array(
                            'type'   => self::GetType( $aStackTrace[ $iFrameCounter ][ 'args' ][ $iArgCounter ] ),
                            'string' => self::GetStringRepresentation( $aStackTrace[ $iFrameCounter ][ 'args' ][ $iArgCounter ] )
                        );
                    }
                }
            }

            return $aStackTrace;
        }

        /**
         * Filters passwords out of the supplied array.
         *
         * @param   array   $aContext
         *
         * @return  array
         */
        public function FilterPasswords( array $aContext )
        {
            // work with a copy of the original array
            $aNewContext = $aContext;

            foreach( $aContext as $sLabel => $vValue )
            {
                // recursively look for passwords
                if( is_array( $vValue ) )
                {
                    $aNewContext[ $sLabel ] = $this->FilterPasswords( $vValue );
                }

                // check if this is a password
                if( is_string( $sLabel )
                    && (    strpos( strtolower( $sLabel ), 'password' ) !== false
                         || strpos( strtolower( $sLabel ), 'pass' )     !== false
                         || strpos( strtolower( $sLabel ), 'pwd' )      !== false
                         || strpos( strtolower( $sLabel ), 'pw' )       !== false ) )
                {
                    unset( $aNewContext[ $sLabel ] );
                }
            }

            return $aNewContext;
        }

        /**
         * Applies loaded filters to an array.
         *
         * @param   array   $aContext
         *
         * @return  array
         */
        public function FilterArray( array $aContext )
        {
            // work with a copy of the original
            $aNewContext = $aContext;

            // remove super globals from context
            $aSavedContext = array();
            foreach( $aContext as $sVarName => $vValue )
            {
                if( !in_array( $sVarName, $this->aSuperGlobals ) )
                {
                    $aSavedContext[ $sVarName ] = $vValue;
                }
            }

            foreach( $this->aContextFilters as $sLabel => $callFilter )
            {
                $aNewContext = call_user_func( $callFilter, $aContext );
            }

            return $aNewContext;
        }

        /**
         * Retrieves currently loaded context filters.
         *
         * @return array
         */
        public function GetFilters()
        {
            return $this->aContextFilters;
        }

        /**
         * Removes a context filter.
         *
         * @param   string   $sLabel
         */
        public function RemoveFilter( $sLabel )
        {
            if( is_string( $sLabel )
                && isset( $this->aContextFilters[ $sLabel ] ) )
            {
                unset( $this->aContextFilters[ $sLabel ] );
            }
        }

        /**
         * Adds an filter to apply to all context variables before they are logged.
         *
         * @param   string     $sLabel
         * @param   callable   $callFilter
         */
        public function AddArrayFilter( $sLabel, $callFilter )
        {
            if( is_string( $sLabel ) && is_callable( $callFilter ) )
            {
                $this->aContextFilters[ $sLabel ] = $callFilter;
            }
        }
    }
?>