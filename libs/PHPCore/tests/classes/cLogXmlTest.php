<?php
    // configure the system
    require_once '../../../../config.php';

    // pull in the class we're testing
    require_once sCORE_INC_PATH . '/classes/cLogXml.php';

    /**
     * Tests the functionality of the XML logging class.
     *
     * @author      Team Rah
     * @version     0.21.5
     * @package     Tests
     * @subpackage  Logging
     */
    class cLogXmlTest extends PHPUnit_Framework_TestCase
    {
        /**
         * The log directory that these tests will be using.
         *
         * @var string
         */
        protected $sLogDirectory;

        /**
         * Set the log directory.
         */
        public function setUp()
        {
            $this->sLogDirectory = sBASE_INC_PATH . DIRECTORY_SEPARATOR . 'doesNotExist';
        }

        /**
         * Attempts to set a log directory to one that does not exist
         * and we don't have access to create. Should throw an exception.
         *
         * @expectedException Exception
         */
        public function testSetLogDirectoryFail()
        {
            // make sure the directory does not exist
            $this->assertFalse( file_exists( '/fasdfdsfsdfds3251v' ) );

            // try to make the directory
            cLogXml::SetLogDirectory( '/fasdfdsfsdfds3251v' );

            // fail if the directory was made
            $this->fail( 'Exception was not thrown.' );
        }

        /**
         * Attempts to set a log directory to one that does not exist.
         * Should create the directory and make it writable.
         */
        public function testSetLogDirectory()
        {
            // make sure the directory does not exist
            $this->assertFalse( file_exists( $this->sLogDirectory ) );

            // try to make the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // make sure the directory exists and is writable
            $this->assertTrue( file_exists( $this->sLogDirectory ) );
            $this->assertTrue( is_dir( $this->sLogDirectory ) );
            $this->assertTrue( is_writable( $this->sLogDirectory ) );
        }

        /**
         * Ensures that the log directory was set correctly.
         *
         * @depends testSetLogDirectory
         */
        public function testGetLogDirectory()
        {
            $this->assertTrue( cLogXml::GetLogDirectory() == $this->sLogDirectory . DIRECTORY_SEPARATOR );
        }

        /**
         * Ensures that there are no log types in a new log directory.
         */
        public function testGetLogTypesEmpty()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // get the log types
            $aLogTypes = cLogXml::GetLogTypes();

            // make sure an empty array was returned
            $this->assertTrue( is_array( $aLogTypes ) );
            $this->assertEmpty( $aLogTypes );
        }

        /**
         * Ensures that attempting to get the contents of a log
         * that does not exist will return an empty array.
         */
        public function testGetLogContentsEmpty()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // try to get the contents of a type that does not exist
            $sLogType  = 'doesNotExist';
            $aContents = cLogXml::GetLogContents( $sLogType );

            // make sure an empty array was returned
            $this->assertTrue( is_array( $aContents ) );
            $this->assertEmpty( $aContents );
        }

        /**
         * Ensures that trying to clear a log that
         * does not exist will not change anything.
         */
        public function testClearTypeThatDoesNotExist()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // try to get the contents of a type that does not exist
            $sLogType = 'doesNotExist';
            $bCleared = cLogXml::Clear( $sLogType );

            // make sure false was returned
            $this->assertFalse( $bCleared );
        }

        /**
         * Ensures that trying to clear a log that
         * does not exist will not change anything.
         */
        public function testClearEntriesBeforeTypeThatDoesNotExist()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // try to get the contents of a type that does not exist
            $sLogType = 'doesNotExist';
            $bCleared = cLogXml::ClearEntriesBefore( $sLogType, 0 );

            // make sure a false was returned
            $this->assertFalse( $bCleared );
        }

        /**
         * Ensures attempting to get stats for a type
         * that does not exist will return default data.
         */
        public function testGetStatsForTypeWhereTypeDoesNotExist()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // try to get the contents of a type that does not exist
            $sLogType = 'doesNotExist';
            $aStats   = cLogXml::GetStatsForType( $sLogType );

            // make sure default data was returned
            $this->assertTrue( is_array( $aStats ) );
            $this->assertTrue( $aStats[ 'entries' ]        == 0 );
            $this->assertTrue( $aStats[ 'last_entry' ]     == '' );
            $this->assertTrue( $aStats[ 'estimated_size' ] == 0 );
        }

        /**
         * Ensures that an error is raised if the correct types aren't provided.
         *
         * @expectedException PHPUnit_Framework_Error
         */
        public function testLogWithoutCorrectTypes()
        {
            // initialize data to log
            $sType    = 123;
            $sMessage = 123;
            $aContext = 123;

            // try to log
            $bReturn  = cLogXml::Log( $sType, $sMessage, $aContext );

            // fail if the error was not raised
            $this->fail( 'Error not raised when incorrect variable types were provided.' );
        }

        /**
         * Ensure provided the correct types with empty
         * values will not result in a successful log.
         */
        public function testLogWithCorrectTypesButEmptyValues()
        {
            // initialize data to log
            $sType    = '';
            $sMessage = '';
            $aContext = array();

            // try to log
            $bReturn  = cLogXml::Log( $sType, $sMessage, $aContext );

            // make sure the log failed
            $this->assertFalse( $bReturn );

            // make sure there are no log types
            $this->assertEmpty( cLogXml::GetLogTypes() );
        }

        /**
         * Ensures a valid call to Log() will return a true.
         */
        public function testLogWithCorrectTypesAndValues()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log
            $bReturn  = cLogXml::Log( $sType, $sMessage, $aContext );

            // make sure the log succeeded
            $this->assertTrue( $bReturn );

            // make sure the type we logged exists in the log types
            $this->assertTrue( in_array( $sType, cLogXml::GetLogTypes() ) );
        }

        /**
         * Ensures a the type logged is detected as a type from GetLogContents().
         */
        public function testGetLogTypesAfterSuccessfulLog()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log
            $bReturn  = cLogXml::Log( $sType, $sMessage, $aContext );

            // make sure the type we logged exists in the log types
            $aLogTypes = cLogXml::GetLogTypes();
            $this->assertTrue( is_array( $aLogTypes ) );
            $this->assertTrue( count( $aLogTypes ) == 1 );
            $this->assertTrue( in_array( $sType, $aLogTypes ) );
        }

        /**
         * Ensures the log contents are correct after a log has been created.
         */
        public function testGetLogContentsAfterSuccessfulLog()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log
            $bReturn  = cLogXml::Log( $sType, $sMessage, $aContext ); $iLine = __LINE__;

            // get the log contents
            $aContents = cLogXml::GetLogContents( $sType );

            // make sure the contents are what we're expcecting
            $this->assertTrue( is_array( $aContents ) );
            $this->assertFalse( empty( $aContents ) );
            $this->assertTrue( count( $aContents ) == 1 );
            $this->assertTrue( $aContents[ 0 ][ 'message' ]  == $sMessage );
            $this->assertTrue( $aContents[ 0 ][ 'location' ] == __FILE__ . ':' . $iLine );
            $this->assertTrue( $aContents[ 0 ][ 'extra' ]    == 'info' );
        }

        /**
         * Ensures Clear() says it works after a successful log.
         */
        public function testClearAfterSuccessfulLog()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log and then clear
            cLogXml::Log( $sType, $sMessage, $aContext );
            $bCleared = cLogXml::Clear( $sType );

            // make sure the clear says it was successful
            $this->assertTrue( $bCleared );
        }

        /**
         * Ensures GetLogTypes is empty after clearing a log type.
         */
        public function testGetLogTypesAfterClearAfterSuccessfulLog()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log and then clear
            cLogXml::Log( $sType, $sMessage, $aContext );
            $bCleared = cLogXml::Clear( $sType );

            // get the log types and make sure they're what is expected
            $aLogTypes = cLogXml::GetLogTypes();
            $this->assertTrue( is_array( $aLogTypes ) );
            $this->assertTrue( count( $aLogTypes ) == 0 );
        }

        /**
         * Ensures GetLogContents() is empty after clearing a log type.
         */
        public function testGetLogContentsAfterClearAfterSuccessfulLog()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log and then clear
            cLogXml::Log( $sType, $sMessage, $aContext );
            $bCleared = cLogXml::Clear( $sType );

            // get the log contents
            $aContents = cLogXml::GetLogContents( $sType );

            // make sure the contents are what we're expcecting
            $this->assertTrue( is_array( $aContents ) );
            $this->assertTrue( empty( $aContents ) );
        }

        /**
         * Ensures that clearing log entries before the current time will clear all log entries.
         */
        public function testClearEntriesBeforeWithCurrentTimeAfterSuccessfulLog()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // log the message
            cLogXml::Log( $sType, $sMessage, $aContext );

            // try to clear the entries before the current timestamp
            $bCleared = cLogXml::ClearEntriesBefore( $sType, microtime( true ) );

            // make sure it reported a success
            $this->assertTrue( $bCleared );

            // get the log contents
            $aContents = cLogXml::GetLogContents( $sType );

            // make sure the contents are what we're expcecting
            $this->assertTrue( is_array( $aContents ) );
            $this->assertTrue( empty( $aContents ) );
        }

        /**
         * Ensures that trying to clear log entries before the earliest log entry will not change anything.
         */
        public function testClearEntriesBeforeWithPreviousTimeAfterSuccessfulLog()
        {
            // set the directory
            cLogXml::SetLogDirectory( $this->sLogDirectory );

            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // log the message
            cLogXml::Log( $sType, $sMessage, $aContext );

            // try to clear the entries before the current timestamp
            cLogXml::ClearEntriesBefore( $sType, 0 );

            // get the log contents
            $aContents = cLogXml::GetLogContents( $sType );

            // make sure the contents are what we're expcecting
            $this->assertTrue( is_array( $aContents ) );
            $this->assertFalse( empty( $aContents ) );
        }

        /**
         * Removes any files or folders that were created during testing.
         */
        public function tearDown()
        {
            // check if the test folder was created
            if( file_exists( $this->sLogDirectory )
                && is_dir( $this->sLogDirectory ) )
            {
                // get all the files in the folder
                $aFiles = glob( $this->sLogDirectory . DIRECTORY_SEPARATOR . '*' );

                // remove the files
                foreach( $aFiles as $sFile )
                {
                    if( is_file( $sFile ) )
                    {
                        unlink( $sFile );
                    }
                }

                // remove the test directory
                rmdir( $this->sLogDirectory );
            }
        }

        public function __destruct()
        {
            $this->tearDown();
        }
    }
?>