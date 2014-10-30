<?php
    // get the interface this class implements
    require_once sCORE_INC_PATH . '/classes/ifLogger.php';

    /**
     * Implementation of the logging interface that allows
     * return values to be controlled for testing.
     *
     * @author      Team Rah
     * @package     Tests
     * @subpackage  Logging
     * @version     0.6.0
     */
    class cLogFake implements ifLogger
    {
        /**
         * Flag for what the Log method should return.
         *
         * @var boolean
         */
        public static $bLogged = true;

        /**
         * Flag for what the ClearBefore method should return.
         *
         * @var boolean
         */
        public static $bClearedBefore = true;

        /**
         * Flag for what the Clear method should return.
         *
         * @var boolean
         */
        public static $bCleared = true;

        /**
         * Return value for GetLogContents.
         *
         * @var array
         */
        public static $aContents = array();

        /**
         * Return value for GetLogTypes.
         *
         * @var array
         */
        public static $aTypes = array();

        /**
         * Return value for GetStatsForType.
         *
         * @var array
         */
        public static $aStats = array();

        public static function Log( $sType, $sMessage, array $aContext = array() )
        {
            if( self::$bLogged )
            {
                if( !isset( self::$aContents[ $sType ] ) )
                {
                    self::$aContents[ $sType ] = array();
                }
                self::$aContents[ $sType ][] = $aContext + array( 'message' => $sMessage );

                // add to the types
                if( !in_array( $sType, self::$aTypes ) )
                {
                    self::$aTypes[] = $sType;
                }
            }

            return self::$bLogged;
        }

        public static function GetLogContents( $sType, $vAmount = null )
        {
            $aReturn = array();
            if( isset( self::$aContents[ $sType ] ) )
            {
                $aReturn = self::$aContents[ $sType ];
            }

            if( is_numeric( $vAmount ) )
            {
                $aReturn = array_slice( $aReturn, 0, intval( $vAmount ) );
            }

            return $aReturn;
        }

        public static function ClearEntriesBefore( $sType, $iTimestamp )
        {
            if( isset( self::$aContents[ $sType ] )
                && is_numeric( $iTimestamp ) )
            {
                $iTimestamp = intval( $iTimestamp );

                $iEntries = count( self::$aContents[ $sType ] );
                for( $iEntry = 0; $iEntry < $iEntries; ++$iEntry )
                {
                    if( isset( self::$aContents[ $sType ][ $iEntry ][ 'microseconds' ] )
                        && is_numeric( self::$aContents[ $sType ][ $iEntry ][ 'microseconds' ] )
                        && self::$aContents[ $sType ][ $iEntry ][ 'microseconds' ] <= $iTimestamp )
                    {
                        unset( self::$aContents[ $sType ][ $iEntry ] );
                    }
                }
                sort( self::$aContents[ $sType ] );
            }

            return self::$bClearedBefore;
        }

        public static function Clear( $sType )
        {
            if( self::$bCleared )
            {
                if( isset( self::$aContents[ $sType ] ) )
                {
                    self::$aContents[ $sType ] = array();
                }
                if( in_array( $sType, self::$aTypes ) )
                {
                    $iKey = array_search( $sType, self::$aTypes );
                    unset( self::$aTypes[ $iKey ] );
                }
            }

            return self::$bCleared;
        }

        public static function GetLogTypes()
        {
            return self::$aTypes;
        }

        public static function GetStatsForType( $sType )
        {
            return self::$aStats;
        }
    }
?>