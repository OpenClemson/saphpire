<?php
    // configure the system
    require_once '../../../../config.php';

    // pull in the class we're testing
    require_once sCORE_INC_PATH . '/classes/cLogManager.php';

    // pull in the fake logger to test with
    require_once '../classes/cLogFake.php';

    /**
     * Tests the functionality of the XML logging class.
     *
     * @author      Team Rah
     * @version     0.1.0
     * @package     Tests
     * @subpackage  Logging
     */
    class cLogServiceTest extends PHPUnit_Framework_TestCase
    {

    }
?>