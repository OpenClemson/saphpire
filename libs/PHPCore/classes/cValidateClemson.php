<?php
    // get the string validation class
    require_once( sCORE_INC_PATH . '/classes/cValidateString.php' );

    // get the error handling class
    require_once( sCORE_INC_PATH . '/classes/cAnomaly.php' );

    /**
     * Validation rules that apply to Clemson inputs.
     *
     * @author     Team Rah
     * @package    Core
     * @subpackage Validation
     * @version    0.1.0
     */
    class cValidateClemson extends cValidateString
    {
        /**
         * Default error messages for validation functions.
         *
         * Structure:
         *  array(
         *      FunctionName => Error Message
         *  )
         *
         * Messages that have _:_TAGS_:_ will replace the tags with relevant data on error.
         *
         * @var array
         */
        protected $aErrorMessages = array(
            'ValidateCuid'     => 'The value provided is not a valid CUID.',
            'ValidateXid'      => 'The value provided is not a valid XID.',
            'ValidateDeptCode' => 'The value provided is not a valid department code.',
            'ValidateTerm'     => 'The value provided is not a valid term.',
            'ValidateEmplid'   => 'The value provided is not a valid Employee ID.',
            'ValidateMajor'    => 'The value provided is not a valid major code.',
        );

        /**
         * Validates whether or not the value provided
         * is a syntactically valid CUID.
         *
         * @todo: needs to be updated to see if the CUID exists.
         *
         * @param   string          $sValue         The value to test.
         * @param   string          $sErrorMessage  Error message to return on failure.
         *
         * @throws  Exception       Thrown if an exception was caught from a lower level.
         *
         * @return  true | string   Returns an error message if the validation fails.
         */
        public function ValidateCuid( $sValue, $sErrorMessage = null )
        {
            try
            {
                // verify that a string was provided to test
                cStringUtilities::VerifyString( $sValue );

                // if an error message was not supplied, default it
                $vValid = $this->GetErrorMessage( $sErrorMessage );

                // check the the value is a valid host
                if( $this->ValidateEqualsLength( $sValue, 9, 'CUID numbers must be exactly 9 characters' ) === true // check if the value is 9 characters long
                     && is_numeric( $sValue )                // check if the value contains only numeric characters
                   )
                {
                    $vValid = true;
                }

                return $vValid;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Validates whether or not the value provided
         * is a syntactically vaild XID.
         *
         * @todo: needs to be updated to check if XID exists.
         *
         * @param   string          $sValue         The value to test.
         * @param   string          $sErrorMessage  Error message to return on failure.
         *
         * @throws  Exception       Thrown if an exception was caught at a lower level.
         *
         * @return  true | string   Returns an error message if the validation fails.
         */
        public function ValidateXid( $sValue, $sErrorMessage = null )
        {
            try
            {
                // verify that a string was provided to test
                cStringUtilities::VerifyString( $sValue );

                // if an error message was not supplied, default it
                $vValid = $this->GetErrorMessage( $sErrorMessage );

                // check the the value is a valid host
                if( $this->ValidateEqualsLength( $sValue, 9, 'XIDs must be exactly nine characters long.' ) === true // check if the value is 9 characters long
                     && strtolower( substr( $sValue, 0, 1 ) ) === 'c'      // check if the first character is a c
                     && is_numeric( substr( $sValue, 1 ) )   // check if all characters after the first are numeric
                   )
                {
                    $vValid = true;
                }

                return $vValid ;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Validates whether or not the value provided
         * is a syntactically valid department code.
         *
         * @todo: needs to be updated to check if dept code exists
         *
         * @param   string          $sValue         The value to test.
         * @param   string          $sErrorMessage  Error message to return on failure.
         *
         * @throws  Exception       Thrown if an exception was caught at a lower level.
         *
         * @return  true | string   Returns an error message if the validation fails.
         */
        public function ValidateDeptCode( $sValue, $sErrorMessage = null )
        {
            try
            {
                // verify that a string was provided to test
                cStringUtilities::VerifyString( $sValue );

                // if an error message was not supplied, default it
                $vValid = $this->GetErrorMessage( $sErrorMessage );

                // check the the value is a valid host
                if( $this->ValidateEqualsLength( $sValue, 4, 'Department codes must be exactly 4 digits long.' ) === true // check if the value is 4 characters long
                     && is_numeric( $sValue )                // check if the value contains only numeric characters
                   )
                {
                    $vValid = true;
                }

                return $vValid ;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Validates whether or not the value provided
         * is a syntactically valid term.
         *
         * @todo: needs to be updated to check if term actually exists
         *
         * @param   string          $sValue         The value to test.
         * @param   string          $sErrorMessage  Error message to return on failure.
         *
         * @throws  Exception       Thrown if an exception was caught at a lower level.
         *
         * @return  true | string   Returns an error message if the validation fails.
         */
        public function ValidateTerm( $sValue, $sErrorMessage = null )
        {
            try
            {
                // verify that a string was provided to test
                cStringUtilities::VerifyString( $sValue );

                // if an error message was not supplied, default it
                $vValid = $this->GetErrorMessage( $sErrorMessage );

                // check the the value is a valid host
                if( $this->ValidateEqualsLength( $sValue, 6, 'Terms must be exactly 6 digits long.' ) === true // check if the value is 6 characters long
                     && is_numeric( $sValue )                // check if the value contains only numeric characters
                   )
                {
                    $vValid = true;
                }

                return $vValid ;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Validates whether or not the value provided
         * is a syntactically valid employee id.
         *
         * @todo: needs to be updated to check if the employee id exists
         *
         * @param   string          $sValue         The value to test.
         * @param   string          $sErrorMessage  Error message to return on failure.
         *
         * @throws  Exception       Thrown if an exception was caught at a lower level.
         *
         * @return  true | string   Returns an error message if the validation fails.
         */
        public function ValidateEmplid( $sValue, $sErrorMessage = null )
        {
            try
            {
                // verify that a string was provided to test
                cStringUtilities::VerifyString( $sValue );

                // if an error message was not supplied, default it
                $vValid = $this->GetErrorMessage( $sErrorMessage );

                // check the the value is a valid host
                if( $this->ValidateEqualsLength( $sValue, 6, 'Employee IDs must be exactly 6 characters long.' ) === true // check if the value is 6 characters long
                     && is_numeric( $sValue )                // check if the value contains only numeric characters
                   )
                {
                    $vValid = true;
                }

                return $vValid ;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Validates whether or not the value provided
         * is a syntactically valid major code.
         *
         * @todo: needs to be updated to check if the employee id exists
         *
         * @param   string          $sValue         The value to test.
         * @param   string          $sErrorMessage  Error message to return on failure.
         *
         * @throws  Exception       Thrown if an exception was caught at a lower level.
         *
         * @return  true | string   Returns an error message if the validation fails.
         */
        public function ValidateMajor( $sValue, $sErrorMessage = null )
        {
            try
            {
                // verify that a string was provided to test
                cStringUtilities::VerifyString( $sValue );

                // if an error message was not supplied, default it
                $vValid = $this->GetErrorMessage( $sErrorMessage );

                // check the the value is a valid host
                if( $this->ValidateEqualsLength( $sValue, 3, 'Major codes must be exactly 3 digits long.' ) === true // check if the value is 3 characters long
                     && is_numeric( $sValue )                // check if the value contains only numeric characters
                   )
                {
                    $vValid = true;
                }

                return $vValid ;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>