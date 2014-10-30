<?php
    // configure the system
    require_once '../../../../config.php';

    // pull in the class we're testing
    require_once sCORE_INC_PATH . '/classes/cLogManager.php';

    // pull in the fake logger to test with
    require_once '../classes/cLogFake.php';

    /**
     * Tests the functionality of the log management class
     *
     * @author      Team Rah
     * @version     2.0.0
     * @package     Tests
     * @subpackage  Logging
     */
    class cLogManagerTest extends PHPUnit_Framework_TestCase
    {
        /**
         * Ensures that there are no log types in a new log directory.
         */
        public function testGetLogTypesEmpty()
        {
            // get the log types
            $aLogTypes = cLogManager::GetLogTypes();

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
            // try to get the contents of a type that does not exist
            $sLogType  = 'doesNotExist';
            $aContents = cLogManager::GetLogContents( $sLogType );

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
            // try to get the contents of a type that does not exist
            $sLogType = 'doesNotExist';
            $bCleared = cLogManager::Clear( $sLogType );

            // make sure false was returned
            $this->assertFalse( $bCleared );
        }

        /**
         * Ensures that trying to clear a log that
         * does not exist will not change anything.
         */
        public function testClearEntriesBeforeTypeThatDoesNotExist()
        {
            // try to get the contents of a type that does not exist
            $sLogType = 'doesNotExist';
            $bCleared = cLogManager::ClearEntriesBefore( $sLogType, 0 );

            // make sure a false was returned
            $this->assertFalse( $bCleared );
        }

        /**
         * Ensures attempting to get stats for a type
         * that does not exist will return default data.
         */
        public function testGetStatsForTypeWhereTypeDoesNotExist()
        {
            // try to get the contents of a type that does not exist
            $sLogType = 'doesNotExist';
            $aStats   = cLogManager::GetStatsForType( $sLogType );

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
            $bReturn  = cLogManager::Log( $sType, $sMessage, $aContext );

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
            $bReturn  = cLogManager::Log( $sType, $sMessage, $aContext );

            // make sure the log failed
            $this->assertFalse( $bReturn );

            // make sure there are no log types
            $this->assertEmpty( cLogManager::GetLogTypes() );
        }

        /**
         * Ensures a valid call to Log() will return a true.
         */
        public function testLogWithCorrectTypesAndValuesButNoLoggers()
        {
            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log
            $bReturn  = cLogManager::Log( $sType, $sMessage, $aContext );

            // make sure the log succeeded
            $this->assertFalse( $bReturn );

            // make sure the type we logged exists in the log types
            $this->assertFalse( in_array( $sType, cLogManager::GetLogTypes() ) );
        }

        /**
         * Ensures there are no loggers set initially.
         */
        public function testGetLoggersWithoutAddingLoggers()
        {
            $this->assertEmpty( cLogManager::GetLoggers() );
        }

        /**
         * Ensures that a call to AddLogger will fail when appropriate.
         *
         * @expectedException Exception
         */
        public function testAddLoggerFailure()
        {
            cLogManager::AddLogger();
        }

        /**
         * Ensures a logger can be added.
         */
        public function testAddLoggerSuccess()
        {
            // initialize logger to add
            $sLogger = 'cLogFake';
            $sLabel  = 'Fake';

            // add the logger
            cLogManager::AddLogger( $sLogger, $sLabel );

            // make sure the logger was added
            $aLoggers = cLogManager::GetLoggers();
            $this->assertFalse( empty( $aLoggers ) );
            $this->assertTrue( isset( $aLoggers[ $sLabel ] ) );
            $this->assertTrue( $aLoggers[ $sLabel ] == $sLogger );
        }

        /**
         * Ensures that logging works after adding a logger.
         */
        public function testLogAfterAddLogger()
        {
            // initialize logger to add
            $sLogger = 'cLogFake';
            $sLabel  = 'Fake';

            // add the logger
            cLogManager::AddLogger( $sLogger, $sLabel );

            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log
            $bReturn  = cLogManager::Log( $sType, $sMessage, $aContext );

            // make suer the log was successful
            $this->assertTrue( $bReturn );
        }

        /**
         * Ensures that logging works after adding a logger.
         */
        public function testLogFailAfterAddLogger()
        {
            // initialize logger to add
            $sLogger = 'cLogFake';
            $sLabel  = 'Fake';

            // add the logger
            cLogManager::AddLogger( $sLogger, $sLabel );

            // make sure the logger will return false from the call to Log()
            cLogFake::$bLogged = false;

            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log
            $bReturn  = cLogManager::Log( $sType, $sMessage, $aContext );

            // make suer the log was successful
            $this->assertFalse( $bReturn );
        }

        /**
         * Ensures a the type logged is detected as a type from GetLogContents().
         */
        public function testGetLogTypesAfterSuccessfulLog()
        {
            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log
            $bReturn  = cLogManager::Log( $sType, $sMessage, $aContext );

            // make sure the type we logged exists in the log types
            $aLogTypes = cLogManager::GetLogTypes();
            $this->assertTrue( is_array( $aLogTypes ) );
            $this->assertTrue( count( $aLogTypes ) == 1 );
            $this->assertTrue( in_array( $sType, $aLogTypes ) );
        }

        /**
         * Ensures the log contents are correct after a log has been created.
         */
        public function testGetLogContentsAfterSuccessfulLog()
        {
            // clear out the contents to far
            cLogFake::$aContents = array();

            // make sure the log is successful
            cLogFake::$bLogged = true;

            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log
            $bReturn  = cLogManager::Log( $sType, $sMessage, $aContext + array( 'location' => __FILE__ . ':' . __LINE__ ) ); $iLine = __LINE__;

            // get the log contents
            $aContents = cLogManager::GetLogContents( $sType );

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
            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log and then clear
            cLogManager::Log( $sType, $sMessage, $aContext );
            $bCleared = cLogManager::Clear( $sType );

            // make sure the clear says it was successful
            $this->assertTrue( $bCleared );
        }

        /**
         * Ensures GetLogTypes is empty after clearing a log type.
         */
        public function testGetLogTypesAfterClearAfterSuccessfulLog()
        {
            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log and then clear
            cLogManager::Log( $sType, $sMessage, $aContext );
            $bCleared = cLogManager::Clear( $sType );

            // get the log types and make sure they're what is expected
            $aLogTypes = cLogManager::GetLogTypes();
            $this->assertTrue( is_array( $aLogTypes ) );
            $this->assertTrue( count( $aLogTypes ) == 0 );
        }

        /**
         * Ensures GetLogContents() is empty after clearing a log type.
         */
        public function testGetLogContentsAfterClearAfterSuccessfulLog()
        {
            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // try to log and then clear
            cLogManager::Log( $sType, $sMessage, $aContext );
            $bCleared = cLogManager::Clear( $sType );

            // get the log contents
            $aContents = cLogManager::GetLogContents( $sType );

            // make sure the contents are what we're expcecting
            $this->assertTrue( is_array( $aContents ) );
            $this->assertTrue( empty( $aContents ) );
        }

        /**
         * Ensures that clearing log entries before the current time will clear all log entries.
         */
        public function testClearEntriesBeforeWithCurrentTimeAfterSuccessfulLog()
        {
            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // log the message
            cLogManager::Log( $sType, $sMessage, $aContext );

            // try to clear the entries before the current timestamp
            $bCleared = cLogManager::ClearEntriesBefore( $sType, microtime( true ) );

            // make sure the contents are cleared
            cLogFake::$aContents = array();
            cLogFake::$aTypes    = array();

            // make sure it reported a success
            $this->assertTrue( $bCleared );

            // get the log contents
            $aContents = cLogManager::GetLogContents( $sType );

            // make sure the contents are what we're expcecting
            $this->assertTrue( is_array( $aContents ) );
            $this->assertTrue( empty( $aContents ) );
        }

        /**
         * Ensures that trying to clear log entries before the earliest log entry will not change anything.
         */
        public function testClearEntriesBeforeWithPreviousTimeAfterSuccessfulLog()
        {
            // initialize data to log
            $sType    = 'log-test';
            $sMessage = 'Testing a log.';
            $aContext = array( 'extra' => 'info' );

            // log the message
            cLogManager::Log( $sType, $sMessage, $aContext );

            // try to clear the entries before the current timestamp
            cLogManager::ClearEntriesBefore( $sType, 0 );

            // get the log contents
            $aContents = cLogManager::GetLogContents( $sType );

            // make sure the contents are what we're expcecting
            $this->assertTrue( is_array( $aContents ) );
            $this->assertFalse( empty( $aContents ) );
        }
    }
?>