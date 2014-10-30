<?php
    /**
     * Extension of the Exception class that allows for codes provided to be strings.
     *
     * If the code supplied is a string, the exception handler defined in cAnomaly
     * will use the code as the log type when logging this exception.
     *
     * @author      Team Rah
     * @package     Core
     * @subpackage  Anomaly
     * @version     0.1.0
     */
    class cStringCodeException extends Exception
    {
        /**
         * Override the parent constructor to allow a string to be supplied as the code.
         *
         * @param  string        $sMessage   The message of the exception.
         * @param  int | string  $vCode      The int or string version of the code.
         * @param  Exception     $oPrevious  The previous exception for chaining.
         */
        public function __construct( $sMessage = "", $vCode = 0, Exception $oPrevious = NULL )
        {
            // call the parent constructor
            parent::__construct( $sMessage, 0, $oPrevious );

            // override the code
            $this->code = $vCode;
        }
    }
?>