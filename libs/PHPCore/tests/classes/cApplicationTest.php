<?php
    // configure the system
    require_once '../../../../config.php';

    // pull in the class we're testing
    require_once sCORE_INC_PATH . '/classes/cApplication.php';

    /**
     * Tests the functionality of the application class.
     *
     * @author      Team Rah
     * @version     0.1.0
     * @package     Tests
     * @subpackage  Application
     */
    class cApplicationTest extends PHPUnit_Framework_TestCase
    {
        /**
         * Ensures that an initial application instance has valid default data,
         * is runnable, and is not in maintenance mode.
         */
        public function testInitialSettings()
        {
            // make sure this application is released and not in maintenance
            $this->assertTrue( cApplication::IsReleased() );
            $this->assertFalse( cApplication::IsInMaintenance() );

            // make sure release date and maintenance start and end dates are not set yet
            $this->assertTrue( cApplication::GetReleaseDate() == '' );
            $this->assertTrue( cApplication::GetMaintenanceStartDate() == '' );
            $this->assertTrue( cApplication::GetMaintenanceEndDate() == '' );
        }

        /**
         * Ensures setting the released status works correctly.
         */
        public function testSetReleasedStatus()
        {
            // set the status
            cApplication::SetReleasedStatus( false );

            // make sure it was set correctly
            $this->assertFalse( cApplication::IsReleased() );
        }

        /**
         * Ensures setting the maintenance mode works correctly.
         */
        public function testSetMaintenanceStatus()
        {
            // set the status
            cApplication::SetMaintenanceStatus( true );

            // make sure it was set correctly
            $this->assertTrue( cApplication::IsInMaintenance() );
        }

        /**
         * Ensures setting the release date will return the same thing sent in.
         */
        public function testSetReleaseDate()
        {
            // set the release date
            $sReleaseDate = date( sTIMESTAMP_FORMAT, time() );
            cApplication::SetReleaseDate( $sReleaseDate );

            // make sure the release date is the same as what was provided
            $this->assertTrue( cApplication::GetReleaseDate() == $sReleaseDate );
        }

        /**
         * Ensures setting the maintenance start date will return the same thing sent in.
         */
        public function testSetMaintenanceStartDate()
        {
            // set the maintenance start date
            $sMaintenanceDate = date( sTIMESTAMP_FORMAT, time() );
            cApplication::SetMaintenanceStartDate( $sMaintenanceDate );

            // make sure the maintenance date is the same as what was provided
            $this->assertTrue( cApplication::GetMaintenanceStartDate() == $sMaintenanceDate );
        }

        /**
         * Ensures setting the maintenance end date will return the same thing sent in.
         */
        public function testSetMaintenanceEndDate()
        {
            // set the maintenance end date
            $sMaintenanceDate = date( sTIMESTAMP_FORMAT, time() );
            cApplication::SetMaintenanceEndDate( $sMaintenanceDate );

            // make sure the maintenance date is the same as what was provided
            $this->assertTrue( cApplication::GetMaintenanceEndDate() == $sMaintenanceDate );
        }

        /**
         * Ensures the user can't set a start date after an end date.
         *
         * @expectedException  Exception
         */
        public function testStupidStartDate()
        {
            // set the maintenance end date
            $sMaintenanceDate = date( sTIMESTAMP_FORMAT, time() );
            cApplication::SetMaintenanceEndDate( $sMaintenanceDate );

            // set the maintenance start date
            $sMaintenanceDate = date( sTIMESTAMP_FORMAT, time() + ( 7 * 24 * 60 * 60 ) );
            cApplication::SetMaintenanceStartDate( $sMaintenanceDate );
        }

        /**
         * Ensures the user can't set an end date before a start date.
         *
         * @expectedException  Exception
         */
        public function testStupidEndDate()
        {
            // set the maintenance start date
            $sMaintenanceDate = date( sTIMESTAMP_FORMAT, time() );
            cApplication::SetMaintenanceStartDate( $sMaintenanceDate );

            // set the maintenance end date
            $sMaintenanceDate = date( sTIMESTAMP_FORMAT, time() - ( 7 * 24 * 60 * 60 ) );
            cApplication::SetMaintenanceEndDate( $sMaintenanceDate );
        }
    }
?>