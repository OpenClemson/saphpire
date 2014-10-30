<?php
    // configure the system
    require_once '../../../../config.php';

    // pull in the class we're testing
    require_once sCORE_INC_PATH . '/classes/cTemplate.php';

    /**
    * Test class for cTemplate
    *
    * @author      Team Rah
    * @package     Tests
    * @subpackage  Template
    * @version     0.2.0
    */
    class cTemplateTest extends PHPUnit_Framework_TestCase
    {
        /**
         * Template engine object.
         *
         * @var cTemplate
         */
        protected $oTemplate;

        /**
         * Creates an instance of the template engine to work with.
         * Sets relative path to the core templates directory.
         */
        public function setUp()
        {
            $this->oTemplate = new cTemplate( '../templates' );
        }

        /**
         * Frees up the memory used by this test.
         */
        public function tearDown()
        {
            unset( $this->oTemplate );
        }

        /**
         * Ensures that calling the classic use of the template engine
         * without any replacements will result in the original file contents.
         */
        public function testReplaceNothingClassicUse()
        {
            // set the template data
            $aTemplateData = array();
            $aTemplateData[ 'template' ] = 'test.html';

            // make the replacements and get the file contents
            $sReplaced = $this->oTemplate->Replace( $aTemplateData );
            $sContents = file_get_contents( '../templates/test.html' );

            // ensure that replacing nothing will be the same as the original file contents
            $this->assertTrue( $sReplaced == $sContents );
        }

        /**
         * Ensures that calling the new use of the template engine without
         * any replacements will result in the original file contents.
         */
        public function testReplaceNothingFullPath()
        {
            // set the template data
            $aTemplateData = array();
            $aTemplateData[ 'template' ] = '../templates/test.html';

            // make the replacements and get the file contents
            $sReplaced = $this->oTemplate->Replace( $aTemplateData );
            $sContents = file_get_contents( '../templates/test.html' );

            // ensure that replacing nothing will be the same as the original file contents
            $this->assertTrue( $sReplaced == $sContents );
        }

        /**
         * Ensures that providing a string to use as a template
         * without replacements will result in the original text.
         */
        public function testReplaceNothingNoFile()
        {
            // set the template data to not be a file
            $aTemplateData = array();
            $aTemplateData[ 'template' ] = 'not a file!';

            // make the replacements
            $sReplaced = $this->oTemplate->Replace( $aTemplateData );

            // ensure the replaced text is the same as the original
            $this->assertTrue( $sReplaced == $aTemplateData[ 'template' ] );
        }

        /**
         * Ensures that calling the classic use of the template engine
         * with replacements will result in the original file contents
         * with the tag replaced.
         */
        public function testReplaceClassicUse()
        {
            // set the template data
            $aTemplateData = array();
            $aTemplateData[ 'template' ]  = 'test.html';
            $aTemplateData[ '_:_TAG_:_' ] = 'test';

            // make the replacements and get the file contents
            $sReplaced = $this->oTemplate->Replace( $aTemplateData );
            $sContents = file_get_contents( '../templates/test.html' );
            $sContents = str_replace( '_:_TAG_:_', 'test', $sContents );

            // ensure that replacing nothing will be the same as the original file contents
            $this->assertTrue( $sReplaced == $sContents );
        }

        /**
         * Ensures that calling the new use of the template engine with
         * replacements will result in the original file contents with
         * the tag replaced.
         */
        public function testReplaceFullPath()
        {
            // set the template data
            $aTemplateData = array();
            $aTemplateData[ 'template' ]  = '../templates/test.html';
            $aTemplateData[ '_:_TAG_:_' ] = 'test';

            // make the replacements and get the file contents
            $sReplaced = $this->oTemplate->Replace( $aTemplateData );
            $sContents = file_get_contents( '../templates/test.html' );
            $sContents = str_replace( '_:_TAG_:_', 'test', $sContents );

            // ensure that replacing nothing will be the same as the original file contents
            $this->assertTrue( $sReplaced == $sContents );
        }

        /**
         * Ensures that providing a string to use as a template
         * with replacements will result in the original text
         * with the tag replaces.
         */
        public function testReplaceNoFile()
        {
            // set the template data to not be a file
            $aTemplateData = array();
            $aTemplateData[ 'template' ]  = 'not a _:_TAG_:_!';
            $aTemplateData[ '_:_TAG_:_' ] = 'file';

            // make the replacements
            $sReplaced = $this->oTemplate->Replace( $aTemplateData );

            // ensure the replaced text is the same as the original
            $this->assertTrue( $sReplaced == 'not a file!' );
        }

        /**
         * Ensures that recursive replacement can occur.
         */
        public function testReplaceRecursive()
        {
            // set the template data
            $aTemplateData = array();
            $aTemplateData[ 'template' ]  = '../templates/test.html';
            $aTemplateData[ '_:_TAG_:_' ] = array();
            $aTemplateData[ '_:_TAG_:_' ][ 'template' ]  = '../templates/test.html';
            $aTemplateData[ '_:_TAG_:_' ][ '_:_TAG_:_' ] = 'test';

            // make the replacements and set the expected result
            $sReplaced = $this->oTemplate->Replace( $aTemplateData );
            $sCorrect  = '<div><div>test</div></div>';

            // ensure the replaced text is what is expected
            $this->assertTrue( $sReplaced == $sCorrect );
        }

        /**
         * Ensures template replacement and concatenation can occur correctly.
         */
        public function testReplaceConcat()
        {
            // set the template data
            $aTemplateData = array();
            $aTemplateData[ 'template' ]  = '../templates/test.html';

            // create the concatenation array
            $aConcat = array();
            $aConcat[ '_:_TAG_:_' ] = 'test';

            // add the concatenation arrays
            $aTemplateData[] = $aConcat;
            $aTemplateData[] = $aConcat;

            // make the replacements and set the expected result
            $sReplaced = $this->oTemplate->Replace( $aTemplateData );
            $sCorrect  = '<div>test</div><div>test</div>';

            // ensure the replaced text is what is expected
            $this->assertTrue( $sReplaced == $sCorrect );
        }
    }
?>