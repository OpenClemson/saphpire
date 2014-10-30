<?php
    /**
     * Application management class.
     *
     * @author     Team Rah
     * @package    Application
     * @subpackage Configuration
     * @version    1.0.0
     */
    class cApplication
    {
        /**
         * The name of the application.
         *
         * @var string
         */
        protected static $sAppName = '';

        /**
         * Whether or not this application has been released.
         *
         * @var boolean
         */
        protected static $bReleased = true;

        /**
         * Whether or not this application is in maintenance mode.
         *
         * @var boolean
         */
        protected static $bInMaintenance = false;

        /**
         * The release data for the application.
         *
         * @var string
         */
        protected static $sReleaseDate = '';

        /**
         * The date to start maintenance mode for the application.
         *
         * @var string
         */
        protected static $sMaintenanceStartDate = '';

        /**
         * The date to end maintenance mode for the application.
         *
         * @var string
         */
        protected static $sMaintenanceEndDate = '';

        /**
         * Returns the name of the application.
         *
         * @return string
         */
        public static function GetApplicationName()
        {
            return self::$sAppName;
        }

        /**
         * Sets the name of the application for error and exception output.
         *
         * @param   string   $sAppName
         */
        public static function SetApplicationName( $sAppName )
        {
            if( is_string( $sAppName )
                && !empty( $sAppName ) )
            {
                self::$sAppName = $sAppName;
            }
        }

        /**
         * Checks if this application has been released.
         *
         * @return boolean
         */
        public static function IsReleased()
        {
            return self::$bReleased;
        }

        /**
         * Checks if this application is in maintenance mode.
         *
         * @return boolean
         */
        public static function IsInMaintenance()
        {
            return self::$bInMaintenance;
        }

        /**
         * Sets the released status of this application.
         *
         * @param  boolean  $bReleased
         */
        public static function SetReleasedStatus( $bReleased )
        {
            if( is_bool( $bReleased ) )
            {
                self::$bReleased = $bReleased;
            }
        }

        /**
         * Sets the maintenance status of this application.
         *
         * @param  boolean  $bInMaintenance
         */
        public static function SetMaintenanceStatus( $bInMaintenance )
        {
            if( is_bool( $bInMaintenance ) )
            {
                self::$bInMaintenance = $bInMaintenance;
            }
        }

        /**
         * Set the release date for the application.
         *
         * @param  string  $sTime
         */
        public static function SetReleaseDate( $sTime )
        {
            // make sure the date is a string
            if( is_string( $sTime ) )
            {
                // convert date to a timestamp
                $iTimestamp = strtotime( $sTime );

                // if the timestamp is valid, save it and the date
                if( $iTimestamp !== false )
                {
                    self::$sReleaseDate = $sTime;
                }
            }
        }

        /**
         * Set the start maintenance date for the application.
         *
         * @param  string  $sTime
         */
        public static function SetMaintenanceStartDate( $sTime )
        {
            // make sure the date is a string
            if( is_string( $sTime ) )
            {
                // convert date to a timestamp
                $iTimestamp = strtotime( $sTime );

                // if the timestamp is valid, save it and the date
                if( $iTimestamp !== false )
                {
                    // make sure the end date supplied is greater than the start date
                    if( !empty( self::$sMaintenanceEndDate )
                        && $iTimestamp > strtotime( self::$sMaintenanceEndDate ) )
                    {
                        throw new Exception( 'Attempting to set a start date after the end date.' );
                    }

                    self::$sMaintenanceStartDate = $sTime;
                }
            }
        }

        /**
         * Set the maintenance end date for the application.
         *
         * @param  string  $sTime
         */
        public static function SetMaintenanceEndDate( $sTime )
        {
            // make sure the date is a string
            if( is_string( $sTime ) )
            {
                // convert date to a timestamp
                $iTimestamp = strtotime( $sTime );

                // if the timestamp is valid, save it and the date
                if( $iTimestamp !== false )
                {
                    // make sure the end date supplied is greater than the start date
                    if( !empty( self::$sMaintenanceStartDate )
                        && $iTimestamp < strtotime( self::$sMaintenanceStartDate ) )
                    {
                        throw new Exception( 'Attempting to set a start date after the end date.' );
                    }

                    self::$sMaintenanceEndDate = $sTime;
                }
            }
        }

        /**
         * Gets the ReleaseDate for the application.
         *
         * @return string
         */
        public static function GetReleaseDate()
        {
            return self::$sReleaseDate;
        }

        /**
         * Gets the MaintenanceStartDate for the application.
         *
         * @return string
         */
        public static function GetMaintenanceStartDate()
        {
            return self::$sMaintenanceStartDate;
        }

        /**
         * Gets the MaintenanceEndDate for the application.
         *
         * @return string
         */
        public static function GetMaintenanceEndDate()
        {
            return self::$sMaintenanceEndDate;
        }
    }
?>