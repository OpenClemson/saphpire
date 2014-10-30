<?php
    // get access to the form utilities
    require_once( sCORE_INC_PATH . '/classes/cFormUtilities.php' );

    // get the error handling class
    require_once( sCORE_INC_PATH . '/classes/cAnomaly.php' );

    /**
     * Base business layer. Defines basic form handling functionality.
     *
     * @author     Team Rah
     * @package    Core
     * @subpackage Business
     * @version    0.2.0
     */
    class cBusBase
    {
        /**
         * Default error message for form save errors.
         *
         * @var string
         */
        protected $sFormSaveError = 'There was an unexpected problem. Please try again.';

        /**
         * Form handling function. Supply a business class method
         * to use on successful validation of form elements.
         *
         * @param   string      $sMethodName    Method name of this class that will be used
         *                                      to save valid submitted data.
         * @param   string      $sSubmitButton  The name of the submit button to check for.
         *
         * @throws  Exception   Thrown if caught at a lower level.
         *
         * @return  array       Contains submitted data and errors for the form or the form's elements.
         */
        public function HandleForm( $sMethodName, $sSubmitButton = 'submit' )
        {
            try
            {
                // check if the method exists in this class
                if( !in_array( $sMethodName, get_class_methods( $this ) ) )
                {
                    throw new InvalidArgumentException( "Method '$sMethodName' does not exist in '" . get_class( $this ) . "'" );
                }

                // get access to form utilities
                $oUtilities = new cFormUtilities();

                // get submitted data
                $aData = $oUtilities->GetCleanFormData();

                // initialize  the error messages
                $aErrors = array();

                // check if the form has been submitted
                if( $oUtilities->IsFormSubmitted( $sSubmitButton ) )
                {
                    // check if form is valid
                    if( $oUtilities->IsValid() )
                    {
                        // save new contact info
                        $bStatus = $this->$sMethodName( $aData );

                        // set errors for form
                        if( $bStatus === false )
                        {
                            $aErrors = array(
                                'form' => $this->sFormSaveError
                            );
                        }
                    }
                    else
                    {
                        // get the errors
                        $aErrors = $oUtilities->GetErrors();
                    }
                }

                return array( $aData, $aErrors );
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>