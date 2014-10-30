<?php
    /**
     * Debugging functions. Output any amount of variables with v(),
     * output variables and stop execution with dv(), and profile
     * with saveTime() and getTotalTime().
     *
     * @author  Team Rah
     *
     * @package Debugging
     * @version 0.6.7
     */

    // flag to ensure debugging style tags aren't output more than once
    $bStyleOutput = true;

    /**
     * Print a formatted version of all parameters.
     *
     * @param   OPTIONAL *  Anything can be passed in.
     *
     * @return  null
     */
    function v()
    {
        // never output anything if unless we're in dev
        if( !defined( 'sAPPLICATION_ENV' ) || ( defined( 'sAPPLICATION_ENV' ) && sAPPLICATION_ENV === 'dev' ) )
        {
            // get a flag for whether or not we've already output styles
            global $bStyleOutput;

            // get whether or not we're on the command line
            $bCli = php_sapi_name() == 'cli';

            // set style if output from HTML
            if( !$bCli && $bStyleOutput )
            {
                echo "<style>
                          .debug-header { font-family: monospace; font-size: 14px; font-weight: normal; color: white; text-align: center; }
                          .debug-type   { font-family: monospace; font-size: 14px; font-weight: normal; text-align: center; }
                          .debug-boolean { border: 1px solid #3498DB; }
                          .debug-boolean .debug-header { background: #3498DB; }
                          .debug-boolean .debug-value { border-top: 1px solid #3498DB; }
                          .debug-integer { border: 1px solid #da4f49; }
                          .debug-integer .debug-header { background: #da4f49; }
                          .debug-integer .debug-value { border-top: 1px solid #da4f49; }
                          .debug-null { border: 1px solid #C5C5C5; }
                          .debug-null .debug-header { background: #C5C5C5; }
                          .debug-null .debug-value { border-top: 1px solid #C5C5C5; }
                          .debug-string { border: 1px solid #F4C430; }
                          .debug-string .debug-value { border-top: 1px solid #F4C430; }
                          .debug-string .debug-header { background: #F4C430; }
                          .debug-closure { border: 1px solid #5bb75b; }
                          .debug-closure .debug-header { background: #5bb75b; }
                          .debug-closure .debug-value { border-top: 1px solid #5bb75b; }
                          .debug-object { border: 1px solid #CC99CC; }
                          .debug-object .debug-header { background: #CC99CC; }
                          .debug-object .debug-value { border-top: 1px solid #CC99CC; }
                          .debug-array { border: 1px solid #49afcd; }
                          .debug-array .debug-header { background: #49afcd; }
                          .debug-array .debug-value { border-top: 1px solid #49afcd; }
                          .debug-table { border-spacing: 0; margin-left: 15px; float: left; text-shadow: none; }
                          .debug-table td { padding: 5px; background: white; }
                          span.debug-table { clear: both; margin-top: 15px; }
                      </style>";

                $bStyleOutput = false;
            }

            // get all the arguments passed to this function
            $aArgs = func_get_args();

            // initialize the output
            $sOutput = '';

            // get info about the function that called this
            $aCallers = debug_backtrace();

            // set the caller correctly for dv
            if( isset( $aCallers[ 2 ][ 'function' ] ) && $aCallers[ 2 ][ 'function' ] === 'dv' )
            {
                // get the file and line this call came from
                $sFile = $aCallers[ 2 ][ 'file' ];
                $iLine = $aCallers[ 2 ][ 'line' ];
            }
            // search back until we find this function
            else
            {
                // get the file and line this call came from
                $sFile = $aCallers[ 0 ][ 'file' ];
                $iLine = $aCallers[ 0 ][ 'line' ];
            }

            // read in the file and get the line that this was called on
            $aFile = file( $sFile );

            // build the full function call in case it's broken across multiple lines
            $sCallLocation = '';
            for( $i = $iLine; $i > 0; --$i  )
            {
                // get the line
                $sCallLocation = trim( $aFile[ $i - 1 ] . $sCallLocation );

                // find where the dv or v is in the line
                $iDvLoc = strpos( $sCallLocation, 'dv(' );
                $iVLoc  = strpos( $sCallLocation, 'v(' );

                // if v or dv is found, get out
                if( $iDvLoc !== false )
                {
                    $sCallLocation = substr( $sCallLocation, $iDvLoc );
                    break;
                }
                elseif( $iVLoc !== false )
                {
                    $sCallLocation = substr( $sCallLocation, $iVLoc );
                    break;
                }
            }

            // trim off the unneeded characters
            $sLine = trim( $sCallLocation, " \t\n\r\0\x0B" );

            // initialize variable names, current variable name, and get the tokens
            $sVarName  = '';
            $aVarNames = array();
            $aTokens   = token_get_all( '<?php ' . $sLine . ' ?>' );

            // build the variable names from the tokens
            $iTokenCount = count( $aTokens );
            for( $iTokenCounter = 0; $iTokenCounter < $iTokenCount; ++$iTokenCounter )
            {
                // skip open and close tags as well as the function name and semicolons
                if( is_array( $aTokens[ $iTokenCounter ] ) )
                {
                    if( $aTokens[ $iTokenCounter ][ 0 ] == T_OPEN_TAG
                        || $aTokens[ $iTokenCounter ][ 0 ] == T_CLOSE_TAG )
                    {
                        continue;
                    }
                }
                elseif( $aTokens[ $iTokenCounter ] == ';' )
                {
                    continue;
                }

                // if the token is a comma, end the variable name and start a new one
                // otherwise, concatenate the token to the variable name
                if( $aTokens[ $iTokenCounter ] == ',' )
                {
                    $aVarNames[] = trim( ltrim( $sVarName, '(' ) );
                    $sVarName    = '';
                }
                elseif( is_array( $aTokens[ $iTokenCounter ] ) )
                {
                    $sVarName .= $aTokens[ $iTokenCounter ][ 1 ];
                }
                else
                {
                    $sVarName .= trim( $aTokens[ $iTokenCounter ] );
                }
            }

            // add the last variable if needed
            if( !empty( $sVarName ) )
            {
                $aVarNames[] = trim( rtrim( trim( rtrim( trim( $sVarName, 'dv();' ), ')' ) ), ')' ) );
            }

            // output all the arguments in a clean format
            $iArgCount = count( $aArgs );
            for( $i = 0; $i < $iArgCount; ++$i )
            {
                // get the variable's name if possible. if not, say it's anonymous
                $sVarName = ( substr( $aVarNames[ $i ], 0, 1 ) == '$' ) ? $aVarNames[ $i ] : '( anonymous variable )';
                $sVarName = rtrim( $sVarName, ',' );

                // get the type and string representation
                $sType = cBusAnomaly::GetType( $aArgs[ $i ] );
                $sStringRepresentation = cBusAnomaly::GetStringRepresentation( $aArgs[ $i ] );

                // output
                if( $bCli )
                {
                    $sOutput .= cPresAnomaly::FormatVariableForCLI( $sType, $sVarName, $sStringRepresentation ) . "\n\n";
                }
                else
                {
                    $sOutput .= cPresAnomaly::FormatVariableForHTML( $sType, $sVarName, $sStringRepresentation );
                }
            }

            // output the values of the variables and which file and line this was called from
            if( $bCli )
            {
                $sOutput = $sOutput . 'Called from: ' . $sFile . ' on line ' . $iLine . "\n\n";
            }
            else
            {
                $sOutput = $sOutput . '<span class="debug-table">Called from: ' . $sFile . ' on line ' . $iLine . '</span><br/><br style="clear:both;"/>';
            }

            echo $sOutput;
        }
    }

    /**
     * Print a formatted version of all parameters and stop execution.
     *
     * @param   OPTIONAL *  Anything can be passed in.
     * @return  null
     */
    function dv()
    {
        // never output anything if unless we're in dev
        if( !defined( 'sAPPLICATION_ENV' ) || ( defined( 'sAPPLICATION_ENV' ) && sAPPLICATION_ENV === 'dev' ) )
        {
            call_user_func_array( 'v', func_get_args() );
            die();
        }
    }

    /**
     * A list of times that have been saved.
     *
     * @var array $aTimes
     */
    $aTimes = array();

    /**
     * Resets the $aTimes variable.
     */
    function clearTimes()
    {
        global $aTimes;
        $aTimes = array();
    }

    /**
     * Returns the list of times saved.
     */
    function getTimes()
    {
        global $aTimes;
        return $aTimes;
    }

    /**
     * Save the current time in the $aTimes variable.
     *
     * @param string $sTag  Optional tag with which to associate the saved time.
     */
    function saveTime( $sTag = null )
    {
        global $aTimes;
        if( !empty($sTag) )
        {
            $aTimes[ $sTag ] = microtime( true );
        }
        else
        {
            $aTimes[] = microtime( true );
        }
    }

    /**
     * Returns the total time between two periods.
     *
     * If no specific periods are sent in, returns total time
     * between the first and last entries in $aTimes.
     *
     * If both periods are provided, returns total time
     * between the periods.
     *
     * @param   null | string $vStart   Beginning period of time to measure.
     * @param   null | string $vEnd     Ending period of time to meausre.
     *
     * @return  string  The elapsed time between the start and end periods.
     */
    function getTotalTime( $vStart = null, $vEnd = null )
    {
        global $aTimes;
        $iCount = count( $aTimes );
        if( $iCount == 1 ) {
            if( is_string( key( $aTimes ) ) ) {
                saveTime( 'end' );
            }
            else
            {
                saveTime();
            }
        }
        if( !empty( $vStart ) && !empty( $vEnd ) ) {
            $fStart = $aTimes[ $vStart ];
            $fEnd   = $aTimes[ $vEnd   ];
        }
        else
        {
            $fStart = current( $aTimes );
            $fEnd   = end( $aTimes );
        }

        return sprintf( '%f', $fEnd - $fStart );
    }
?>