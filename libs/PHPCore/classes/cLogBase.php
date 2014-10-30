<?php
    // get the interface this class implements
    require_once( sCORE_INC_PATH . '/classes/ifLogger.php' );

    // get the base auth class
    require_once( sCORE_INC_PATH . '/classes/cAuthBase.php' );

    /**
     * Defines base logging functionality.
     *
     * @author     Team Rah
     * @package    Core
     * @subpackage Logging
     * @version    0.2.5
     */
    abstract class cLogBase implements ifLogger
    {
        /**
         * Returns the user's IP address.
         *
         * Tries to pull the REMOTE_ADDR or SERVER_ADDR
         * from $_SERVER and if neither are found, pulls
         * the hostname of the server.
         *
         * @return  string  User's IP address.
         */
        protected static function GetUserIP()
        {
            // check if this was an HTTP request.
            if( isset( $_SERVER[ 'REMOTE_ADDR' ] ) )
            {
                $sUserIP = $_SERVER[ 'REMOTE_ADDR' ];
            }
            // check if this was run from CLI
            else if( isset( $_SERVER[ 'SERVER_ADDR' ] ) )
            {
                $sUserIP = $_SERVER[ 'SERVER_ADDR' ];
            }
            // server is misconfigured, so just get the hostname
            else
            {
                $sUserIP = gethostbyname( gethostname() );
            }

            return $sUserIP;
        }

        /**
         * Gets the essential log data that may not have
         * been passed in with the log context.
         *
         * Ensures date, microseconds, user, user_ip, and call location are set.
         *
         * @param   array   $aContext   The original log context.
         *
         * @return  array   Merged context with missing information added.
         */
        protected static function GetMissingContext( array $aContext )
        {
            // get the current time
            $aTime = explode( ' ', microtime() );
            $aNewContext[ 'date' ] = date( sTIMESTAMP_FORMAT, $aTime[ 1 ] );
            $aNewContext[ 'microseconds' ] = microtime( true );

            // get the user if possible
            $aNewContext[ 'user' ]   = cAuthBase::GetUser() ?: '';

            // get user's IP address
            $aNewContext[ 'user_ip' ] = self::GetUserIP();

            // default the call location and arguments
            $aNewContext[ 'location' ] = '';
            $aNewContext[ 'args' ]     = array();

            // get caller info if not provided
            $aBackTrace  = debug_backtrace();
            $iTraceCount = count( $aBackTrace );
            for( $i = 0; $i < $iTraceCount; ++$i )
            {
                // check if the file or class is defined
                if( isset( $aBackTrace[ $i ][ 'file' ] ) )
                {
                    // if this file is the current class, skip it
                    if( strpos( $aBackTrace[ $i ][ 'file' ], get_called_class() . '.php' ) !== false )
                    {
                        continue;
                    }

                    // set the exact location
                    $aNewContext[ 'location' ] = $aBackTrace[ $i ][ 'file' ] . ':' . $aBackTrace[ $i ][ 'line' ];
                }
                elseif( isset( $aBackTrace[ $i ][ 'class' ] ) )
                {
                    // if this class is the current class, skip it
                    if( strpos( $aBackTrace[ $i ][ 'class' ], get_called_class() ) !== false )
                    {
                        continue;
                    }

                    // set the generalized location
                    $aNewContext[ 'location' ] = $aBackTrace[ $i ][ 'class' ] . $aBackTrace[ $i ][ 'type' ] . $aBackTrace[ $i ][ 'function' ];
                }

                break;
            }

            // merge into old context
            foreach( $aNewContext as $sKey => $vValue )
            {
                if( !isset( $aContext[ $sKey ] ) )
                {
                    $aContext[ $sKey ] = $vValue;
                }
            }

            return $aContext;
        }
    }
?>