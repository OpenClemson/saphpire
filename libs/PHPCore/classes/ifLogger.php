<?php
    /**
     * Interfaces for handling logging operations.
     *
     * @author     Team Rah
     *
     * @package    Core
     * @subpackage Logging
     * @version    0.0.1
     */
    interface ifLogger
    {
        /**
         * Logs the given message and extra data with the supplied type.
         *
         * @param  string  $sType     Type of log.
         * @param  string  $sMessage  Message to log.
         * @param  array   $aContext  Any additional data to save with the log entry.
         *
         * @return boolean
         */
        public static function Log( $sType, $sMessage, array $aContext = array() );

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
        public static function GetLogContents( $sType, $vAmount = null );

        /**
         * Cleans the log of the given type by removing
         * all entries older than the given timestamp.
         *
         * @param   int     $iTimestamp   Newest date that entries can have.
         *
         * @return  boolean
         */
        public static function ClearEntriesBefore( $sType, $iTimestamp );

        /**
         * Removes all log entries for the given log type.
         *
         * @param    string    $sType
         *
         * @return   boolean
         */
        public static function Clear( $sType );

        /**
         * Retrieves the types of logs that have been created.
         *
         * @return array
         */
        public static function GetLogTypes();

        /**
         * Retrieves stats for the type provided.
         *
         * @param   string   $sType
         *
         * @return  array
         */
        public static function GetStatsForType( $sType );
    }
?>