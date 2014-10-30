<?php
    // get the base class
    require_once sCORE_INC_PATH . '/classes/cLogBase.php';

    /**
     * Manage logging with multiple loggers.
     *
     * @author     Team Rah
     * @package    Core
     * @subpackage Logging
     * @version    1.0.0
     */
    class cLogManager extends cLogBase
    {
        /**
         * The email addresses for the developers of this application.
         *
         * @var array
         */
        protected static $aDevEmails = array();

        /**
         * List of high priority loggers that will
         * be used before low priority loggers.
         *
         * When an error or exception occurs, the loggers are
         * cycled through until a log is successful.
         *
         * @var array
         */
        protected static $aHighPriorityLoggers = array();

        /**
         * List of low priority loggers that will
         * be used after high priority loggers.
         *
         * When an error or exception occurs, the loggers are
         * cycled through until a log is successful.
         *
         * @var array
         */
        protected static $aLowPriorityLoggers = array();

        /**
         * Flag for whether or not to save the same log entry
         * more than once if it is called multiple times.
         *
         * For instance, if an exception is thrown on one page
         * every time it is loaded, it may be beneficial to see
         * that once user has visited the page 15 times or it may
         * be beneficial to only log the message once.
         *
         * Set this value through SetNoDuplicates()
         *
         * @var boolean
         */
        protected static $bNoDuplicates = false;

        /**
         * Retrieves the list of loggers.
         *
         * @return array
         */
        public static function GetLoggers()
        {
            // combine the high and low priority loggers
            $aAllLoggers = array_merge( self::$aHighPriorityLoggers, self::$aLowPriorityLoggers );

            // sort all the loggers
            asort( $aAllLoggers );

            return $aAllLoggers;
        }

        /**
         * Add a logger to the list of loggers.
         *
         * @param   string   $sLogger        The name of a class that implements ifLogger.
         * @param   string   $sLabel         A label to refer to the logger.
         * @param   boolean  $bHighPriority  If possible, use this logger before low priority loggers.
         */
        public static function AddLogger( $sLogger, $sLabel, $bHighPriority = false )
        {
            // make sure the class exists and it implements the logging interface
            if( !is_string( $sLogger )
                || !class_exists( $sLogger )
                || !in_array( 'ifLogger', class_implements( $sLogger ) ) )
            {
                throw new Exception( 'Logger provided is not an instance of ifLogger.' );
            }

            // make sure the label provided is a string
            if( !is_string( $sLabel ) )
            {
                throw new Exception( 'Label provided is not a string.' );
            }

            // add if needed
            if( !isset( self::$aHighPriorityLoggers[ $sLabel ] )
                && !isset( self::$aLowPriorityLoggers[ $sLabel ] ) )
            {
                if( $bHighPriority )
                {
                    self::$aHighPriorityLoggers[ $sLabel ] = $sLogger;
                }
                else
                {
                    self::$aLowPriorityLoggers[ $sLabel ] = $sLogger;
                }
            }
        }

        /**
         * Removes a logger from the list of loggers.
         *
         * @param   string   $sLabel
         */
        public static function RemoveLogger( $sLabel )
        {
            // make sure the label provided is a string
            if( !is_string( $sLabel ) )
            {
                throw new Exception( 'Label provided is not a string.' );
            }

            // remove if possible
            if( isset( self::$aHighPriorityLoggers[ $sLabel ] ) )
            {
                unset( self::$aHighPriorityLoggers[ $sLabel ] );
            }
            elseif( isset( self::$aLowPriorityLoggers[ $sLabel ] ) )
            {
                unset( self::$aLowPriorityLoggers[ $sLabel ] );
            }
        }

        /**
         * Retrieves the list of developer emails.
         *
         * @return array
         */
        public static function GetDevEmails()
        {
            return self::$aDevEmails;
        }

        /**
         * Adds an email to the list of developers.
         *
         * @param   string   $sDevEmail
         */
        public static function AddDevEmail( $sDevEmail )
        {
            // make sure the class exists and it implements the logging interface
            if( !is_string( $sDevEmail ) || !filter_var( $sDevEmail, FILTER_VALIDATE_EMAIL ) )
            {
                throw new Exception( 'Developer email provided is not a valid email address.' );
            }

            // only add if it's not already in the array
            if( !in_array( $sDevEmail, self::$aDevEmails ) )
            {
                // add the logger
                self::$aDevEmails[] = $sDevEmail;
            }
        }

        /**
         * Adds multiple emails to the list of developers.
         *
         * @param   string   $sDevEmail
         */
        public static function AddDevEmails( array $aDevEmails = array() )
        {
            // try to add all the loggers
            $iDevCount = count( $aDevEmails );
            for( $i = 0; $i < $iDevCount; ++$i )
            {
                // add the logger
                self::AddDevEmail( $aDevEmails[ $i ] );
            }
        }

        /**
         * Removes an email address from the list of developer emails.
         *
         * @param   string   $sDevEmail
         */
        public static function RemoveDevEmail( $sDevEmail )
        {
            if( in_array( $sDevEmail, self::$aDevEmails ) )
            {
                unset( self::$aDevEmails[ array_search( $sDevEmail, self::$aDevEmails ) ] );
            }
        }

        /**
         * Returns whether or not the message can be logged.
         *
         * @param   string   $sType      The type of the log.
         * @param   string   $sMessage   The message to log.
         * @param   array    $aContext   The context of the log entry.
         */
        protected static function CanLog( $sType, $sMessage, array $aContext )
        {
            // check the types
            $bCanLog = is_string( $sType ) && is_string( $sMessage );
            $bCanLog = $bCanLog && !empty( $sType ) && !empty( $sMessage );

            // check if types are good and we want to restrict duplicates
            if( $bCanLog && self::$bNoDuplicates )
            {
                // get the previous log entry
                $aPrevious = self::GetLogContents( $sType );

                // check if a previous entry exists
                if( !empty( $aPrevious ) )
                {
                    // check if this problem has been logged before
                    $iLogCount = count( $aPrevious );
                    for( $iPreviousCounter = 0; $iPreviousCounter < $iLogCount; ++$iPreviousCounter )
                    {
                        // setup all the checks
                        $bSameMessage  = $aPrevious[ $iPreviousCounter ][ 'message' ] == $sMessage;
                        $bSameLocation = false;

                        // check if the location of the problem is set
                        if( isset( $aContext[ 'location' ] ) && isset( $aPrevious[ $iPreviousCounter ][ 'location' ] ) )
                        {
                            $bSameLocation = $aContext[ 'location' ] == $aPrevious[ $iPreviousCounter ][ 'location' ];
                        }

                        // check if the entry is the same as what we're trying to log
                        if( $bSameMessage && $bSameLocation )
                        {
                            $bCanLog = false;
                            break;
                        }
                    }
                }
            }

            return $bCanLog;
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
            // initialize the return value
            $bSuccess = false;

            // fix the context
            $aContext = self::GetMissingContext( $aContext );

            // set a check for whether or not to log
            $bCanLog = self::CanLog( $sType, $sMessage, $aContext );

            // only log if we can
            if( $bCanLog )
            {
                // append all the loggers with high priority loggers first
                $aLoggers = self::$aHighPriorityLoggers + self::$aLowPriorityLoggers;

                // cycle through the loggers
                foreach( $aLoggers as $sLogger )
                {
                    // try to log with this logger
                    $bSuccess = call_user_func_array(
                        array( $sLogger, 'Log' ),
                        array( $sType, $sMessage, $aContext )
                    );

                    // we we logged correctly, we're done
                    if( $bSuccess )
                    {
                        break;
                    }
                }

                // if the problem could not be logged, email the developers
                if( !$bSuccess && !empty( self::$aDevEmails ) )
                {
                    $sSubject = 'Logging';
                    $sTo = implode( ', ', self::$aDevEmails );
                    $sLogMessage  = "Could not log the following:\n\n";
                    $sLogMessage .= "Type: $sType\nMessage: $sMessage\nContext:\n" . print_r( $aContext, true );
                    mail( $sTo, $sSubject, $sLogMessage );
                }
            }

            return $bSuccess;
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

            // append all the loggers with high priority loggers first
            $aLoggers = self::$aHighPriorityLoggers + self::$aLowPriorityLoggers;

            // cycle through the loggers
            foreach( $aLoggers as $sLogger )
            {
                // get the log contents for this source
                $aSourceContents = call_user_func_array(
                    array( $sLogger, 'GetLogContents' ),
                    func_get_args()
                );

                // add the source to each entry
                $iSourceCount = count( $aSourceContents );
                for( $i = 0; $i < $iSourceCount; ++$i )
                {
                    $aSourceContents[ $i ][ 'source' ] = $sLogger;
                }

                // try to get log contents for this logger
                $aContents = array_merge(
                    $aSourceContents,
                    $aContents
                );
            }

            // sort by timestamp ascending
            usort(
                $aContents,
                function( $aArray1, $aArray2 )
                {
                    return $aArray1[ 'microseconds' ] > $aArray2[ 'microseconds' ];
                }
            );

            // add in handling for $vAmount
            if( is_int( $vAmount ) )
            {
                $aContents = array_slice( $aContents, 0, $vAmount );
            }

            return $aContents;
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
        public static function GetLogContentsForLogger( $sLabel, $sType, $vAmount = null )
        {
            // initialize return value
            $aContents = array();

            // check if we have the logger
            if( isset( self::$aHighPriorityLoggers[ $sLabel ] ) )
            {
                // get the log contents for this source
                $aContents = call_user_func_array(
                    array( self::$aHighPriorityLoggers[ $sLabel ], 'GetLogContents' ),
                    array( $sType, $vAmount )
                );
            }
            elseif( isset( self::$aLowPriorityLoggers[ $sLabel ] ) )
            {
                // get the log contents for this source
                $aContents = call_user_func_array(
                    array( self::$aLowPriorityLoggers[ $sLabel ], 'GetLogContents' ),
                    array( $sType, $vAmount )
                );
            }

            // sort by timestamp ascending
            usort(
                $aContents,
                function( $aArray1, $aArray2 )
                {
                    // make sure microseconds are set in both arrays
                    if( !isset( $aArray1[ 'microseconds' ] )
                        || !isset( $aArray2[ 'microseconds' ] )
                        || !is_numeric( $aArray2[ 'microseconds' ] )
                        || !is_numeric( $aArray2[ 'microseconds' ] ) )
                    {
                        throw new Exception( 'Microseconds have not been setup correctly.' );
                    }

                    return $aArray1[ 'microseconds' ] > $aArray2[ 'microseconds' ];
                }
            );

            return $aContents;
        }

        /**
         * Cleans the log of all loggers for the given type by removing
         * all entries older than the given timestamp.
         *
         * @param   int     $iTimestamp   Newest date that entries can have.
         *
         * @return  boolean
         */
        public static function ClearEntriesBefore( $sType, $iTimestamp )
        {
            // initialize return value
            $bSuccess = false;

            // append all the loggers with high priority loggers first
            $aLoggers = self::$aHighPriorityLoggers + self::$aLowPriorityLoggers;

            // cycle through the loggers
            foreach( $aLoggers as $sLogger )
            {
                // try to clear the entries for this logger
                $bSuccess = call_user_func_array(
                    array( $sLogger, 'ClearEntriesBefore' ),
                    func_get_args()
                );

                // get out as soon as a failure occurs
                if( !$bSuccess )
                {
                    break;
                }
            }

            return $bSuccess;
        }

        /**
         * Cleans the log of the given logger and type by removing
         * all entries older than the given timestamp.
         *
         * @param   string  $sLogger      The logger to clear entries from.
         * @param   string  $sType        The type of log to clear.
         * @param   int     $iTimestamp   Newest date that entries can have.
         *
         * @return  boolean
         */
        public static function ClearEntriesBeforeForLogger( $sLogger, $sType, $iTimestamp )
        {
            // initialize return value
            $bSuccess = false;

            // check if this logger exists
            if( isset( self::$aHighPriorityLoggers[ $sLogger ] ) )
            {
                // try to clear the entries for this logger
                $bSuccess = call_user_func_array(
                    array( self::$aHighPriorityLoggers[ $sLogger ], 'ClearEntriesBefore' ),
                    array( $sType, $iTimestamp )
                );
            }
            elseif( isset( self::$aLowPriorityLoggers[ $sLogger ] ) )
            {
                // try to clear the entries for this logger
                $bSuccess = call_user_func_array(
                    array( self::$aLowPriorityLoggers[ $sLogger ], 'ClearEntriesBefore' ),
                    array( $sType, $iTimestamp )
                );
            }

            return $bSuccess;
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
            // initialize return value
            $bSuccess = false;

            // append all the loggers with high priority loggers first
            $aLoggers = self::$aHighPriorityLoggers + self::$aLowPriorityLoggers;

            // cycle through the loggers
            foreach( $aLoggers as $sLogger )
            {
                // try to clear all entries for this logger
                $bSuccess = call_user_func_array(
                    array( $sLogger, 'Clear' ),
                    func_get_args()
                );

                // get out as soon as a failure occurs
                if( !$bSuccess )
                {
                    break;
                }
            }

            return $bSuccess;
        }

        /**
         * Removes all log entries for the given log type.
         *
         * @param    string    $sLogger
         * @param    string    $sType
         *
         * @return   boolean
         */
        public static function ClearForLogger( $sLogger, $sType )
        {
            // initialize return value
            $bSuccess = false;

            // check if this logger exists the in high priority loggers
            if( isset( self::$aHighPriorityLoggers[ $sLogger ] ) )
            {
                // try to clear all entries for this logger
                $bSuccess = call_user_func_array(
                    array( self::$aHighPriorityLoggers[ $sLogger ], 'Clear' ),
                    array( $sType )
                );
            }
            elseif( isset( self::$aLowPriorityLoggers[ $sLogger ] ) )
            {
                // try to clear all entries for this logger
                $bSuccess = call_user_func_array(
                    array( self::$aLowPriorityLoggers[ $sLogger ], 'Clear' ),
                    array( $sType )
                );
            }

            return $bSuccess;
        }

        /**
         * Retrieves the types of logs that have been created.
         *
         * @return array
         */
        public static function GetLogTypes()
        {
            // initialize return value
            $aTypes = array();

            // append all the loggers with high priority loggers first
            $aLoggers = self::$aHighPriorityLoggers + self::$aLowPriorityLoggers;

            // cycle through loggers
            foreach( $aLoggers as $sLogger )
            {
                // try to get log types with this logger
                $aTypes = array_merge(
                    call_user_func_array(
                        array( $sLogger, 'GetLogTypes' ),
                        func_get_args()
                    ),
                    $aTypes
                );
            }

            // get the unique types and sort them
            $aTypes = array_unique( $aTypes );
            sort( $aTypes );

            return $aTypes;
        }

        /**
         * Retrieves the types of logs that have been created
         * by the provided logger.
         *
         * @return array
         */
        public static function GetLogTypesForLogger( $sLabel )
        {
            // initialize return value
            $aTypes = array();

            // check if this type has been provided yet
            if( isset( self::$aHighPriorityLoggers[ $sLabel ] ) )
            {
                // try to get log types with this logger
                $aTypes = call_user_func( array( self::$aHighPriorityLoggers[ $sLabel ], 'GetLogTypes' ) );
            }
            elseif( isset( self::$aLowPriorityLoggers[ $sLabel ] ) )
            {
                // try to get log types with this logger
                $aTypes = call_user_func( array( self::$aLowPriorityLoggers[ $sLabel ], 'GetLogTypes' ) );
            }

            // sort the types
            sort( $aTypes );

            return $aTypes;
        }

        /**
         * Retrieves the stats of the log type provided.
         *
         * @return array
         */
        public static function GetStatsForType( $sType )
        {
            // initialize return value
            $aStats = array();
            $aStats[ 'entries' ]        = 0;
            $aStats[ 'last_entry' ]     = '';
            $aStats[ 'estimated_size' ] = 0;

            // combine the loggers for easier cycling through
            $aLoggers = self::$aLowPriorityLoggers + self::$aHighPriorityLoggers;

            // cycle through loggers
            foreach( $aLoggers as $sLogger )
            {
                // try to get log stats with this logger
                $aSourceStats = call_user_func_array(
                    array( $sLogger, 'GetStatsForType' ),
                    func_get_args()
                );

                // merge stats as needed
                foreach( $aSourceStats as $sKey => $sValue )
                {
                    if( isset( $aStats[ $sKey ] ) )
                    {
                        switch( $sKey )
                        {
                            case 'entries':
                                $aStats[ 'entries' ] += intval( $sValue );
                                break;

                            case 'last_entry':
                                if( $aStats[ 'last_entry' ] < $sValue )
                                {
                                    $aStats[ 'last_entry' ] = $sValue;
                                }
                                break;

                            case 'estimated_size':
                                $aStats[ 'estimated_size' ] += intval( $sValue );
                                break;
                        }
                    }
                    else
                    {
                        switch( $sKey )
                        {
                            case 'entries':
                                $aStats[ 'entries' ] = intval( $sValue );
                                break;

                            case 'estimated_size':
                                $aStats[ 'estimated_size' ] = intval( $sValue );
                                break;

                            default:
                                $aStats[ $sKey ] = $sValue;
                                break;
                        }
                    }
                }
            }

            return $aStats;
        }

        /**
         * Retrieves the stats of the log type for the provided logger.
         *
         * @return array
         */
        public static function GetStatsForTypeForLogger( $sLabel, $sType )
        {
            // initialize return value
            $aStats = array();

            // check if this type has been provided yet
            if( isset( self::$aHighPriorityLoggers[ $sLabel ] ) )
            {
                // try to get log stats with this logger
                $aStats = call_user_func_array(
                    array( self::$aHighPriorityLoggers[ $sLabel ], 'GetStatsForType' ),
                    array( $sType )
                );
            }
            elseif( isset( self::$aLowPriorityLoggers[ $sLabel ] ) )
            {
                // try to get log stats with this logger
                $aStats = call_user_func_array(
                    array( self::$aLowPriorityLoggers[ $sLabel ], 'GetStatsForType' ),
                    array( $sType )
                );
            }

            return $aStats;
        }
    }
?>