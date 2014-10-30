<?php
    /**
     * including / loading / requiring files
     */
    require_once( '/path/to/file.php' );

    /**
     * load config.php
     */
    require_once( './path/to/config.php' );

    /**
     * load bootstrap
     */
    require_once( './path/to/includes/bootstrap.php' );

    /**
     * definitions
     */
    // get available definitions
    $aDefined     = get_defined_constants( true );
    $aDefinitions = $aDefined[ 'user' ];
    var_dump( $aDefinitions );

    /**
     * available with config.php:
     *     sTIMESTAMP_FORMAT
     *     sBASE_INC_PATH
     *     sCORE_INC_PATH
     *     bIS_CLI
     *
     * available after bootstrap (in addition)
     *     sAPPLICATION_ENV
     */

    /**
     * create a single-dimensional array
     */
    $aFruits = array(
        'apple',
        'orange',
        'peach',
        'tomato',
        'pineapple',
        'guava melon',
        'starfruit'
    );

    /**
     * environmental logic
     */
    if ( sAPPLICATION_ENV === 'prod' )
    {
        // we are in production! do things special.
    }
    else if ( sAPPLICATION_ENV === 'dev' )
    {
        // dev only stuff
    }
    else if ( sAPPLICATION_ENV === 'local' )
    {
        // local only stuff
    }
    else if ( sAPPLICATION_ENV === 'qa' )
    {
        // let qa folks in
    }
    else if ( sAPPLICATION_ENV === 'demo' )
    {
        // demonstration only.
    }

    /**
     * create a multi-dimensional associative array
     */
    $aAnimals = array(
        'dogs' => array(
            array(
                'name'  => 'spot',
                'age'   => 15,
                'color' => 'brown'
            ),
            array(
                'name'  => 'fido',
                'age'   => 2,
                'color' => 'black'
            ),
        ),
        'cats' => array(
            array(
                'name'  => 'fluffy',
                'age'   => 6,
                'color' => 'white'
            )
        ),
        'chickens' => array(),
        'cows'     => array()
    );

    // assign values to variables, align equal signs
    $sVariable = 'some string';
    $aOthers   = array();
    $iValue    = 0;

    /**
     * classes
     *
     * @author
     * @package
     * @version
     */
    class cClassName
    {
        private $sPrivate     = 'sPrivate';
        protected $sProtected = 'sProtected';
        public $sPublic       = 'sPublic';
        static $sStatic       = 'sStatic';

        public function __construct()
        {
            try
            {

            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        public function MethodName()
        {
            try
            {

            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        public function __destruct()
        {
            try
            {

            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>