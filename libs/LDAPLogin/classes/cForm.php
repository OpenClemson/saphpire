<?php
    /**
     * Basic form handling functionality.
     *
     * @author  Team Rah
     * @package Form
     * @version 0.8.4
     */
    class cForm
    {
        /**
         * All the errors that were found during validation.
         *
         * Ex. array(
         *         'username' => array(
         *             'Username is not at least 8 characters.',
         *             'Username must contain at least one special character.'
         *         )
         *     )
         *
         * @var array
         */
        protected $aErrors = array();

        /**
         * All the elements and their associated validators.
         *
         * Ex. array(
         *         'username' => array(
         *             'required'   => true,
         *             'missing'    => 'Please provide a username.'
         *             'validators' => array(
         *                 array(
         *                     'validator' => <callable>,
         *                     'options'   => array( <options> ),
         *                     'error'     => 'Custom error message.'
         *                 )
         *             )
         *         )
         *     )
         *
         * @var array
         */
        protected $aElements = array();

        /**
         * Input for the form.
         *
         * @var array
         */
        protected $aDataSource = array();

        /**
         * Constructs an instance of this class and attempts
         * to set the fields and data source for this form.
         *
         * @param   array   $aElements     The fields and validators to use on each.
         * @param   array   $aDataSource   The data source for fields.
         */
        public function __construct( array $aElements = array(), array $aDataSource = array() )
        {
            try
            {
                // try to set the fields and validators
                if( !empty( $aElements ) )
                {
                    $this->SetElements( $aElements );
                }

                // try to set the data source
                if( !empty( $aDataSource ) )
                {
                    $this->SetDataSource( $aDataSource );
                }
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Sets the fields to validate against.
         *
         * @param   array   $aElements
         *
         * @return  $this
         */
        public function SetElements( array $aElements )
        {
            try
            {
                // clear out all errors
                $this->aErrors = array();

                // ensure that everything was provided correctly
                foreach( $aElements as $sElement => $aElementInfo )
                {
                    // default the required flag if possible, otherwise check if it is set correctly
                    if( !isset( $aElementInfo[ 'required' ] ) )
                    {
                        $aElements[ $sElement ][ 'required' ] = false;
                    }

                    // make sure required elements have a message to display when they are empty
                    if( $aElementInfo[ 'required' ] )
                    {
                        if( !isset( $aElementInfo[ 'missing' ] ) )
                        {
                            throw new Exception( 'Message for missing required element is not set.' );
                        }
                    }

                    // make sure the missing message is set
                    if( !isset( $aElementInfo[ 'missing' ] ) )
                    {
                        $aElementInfo[ 'missing' ] = '';
                    }

                    // check if validators were supplied
                    if( !isset( $aElementInfo[ 'validators' ] ) )
                    {
                        $aElementInfo[ 'validators' ] = array();
                    }

                    // add the element
                    $this->AddElement(
                        $sElement,
                        $aElementInfo[ 'required' ],
                        $aElementInfo[ 'missing' ],
                        $aElementInfo[ 'validators' ]
                    );
                }
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Adds an element to this form.
         *
         * @param   string    $sElement      The name of the element.
         * @param   boolean   $bRequired     Whether or not the element is required.
         * @param   string    $sMissing      The error message for when the element is required and not provided.
         * @param   array     $aValidators   The validators to apply to the element's value.
         */
        public function AddElement( $sElement, $bRequired = false, $sMissing = '', array $aValidators = array() )
        {
            try
            {
                // make sure a non-empty string is provided for the element name
                if( !is_string( $sElement ) )
                {
                    throw new Exception( 'Element name provided is not a string.' );
                }
                elseif( empty( $sElement ) )
                {
                    throw new Exception( 'Element name cannot be empty.' );
                }

                // make sure a boolean is provided for required status
                if( !is_bool( $bRequired ) )
                {
                    throw new Exception( 'Required status provided is not a boolean.' );
                }

                // make sure a string is provided for the missing message
                if( !is_string( $sMissing ) )
                {
                    throw new Exception( 'Missing message provided is not a string.' );
                }

                // add the element
                $this->aElements[ $sElement ] = array(
                    'required'   => $bRequired,
                    'missing'    => $sMissing,
                    'validators' => array()
                );

                // try to add the validators
                $this->AddValidators( $sElement, $aValidators );
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Removes an element and any errors that may exists for it.
         *
         * @param   string   $sElement
         */
        public function RemoveElement( $sElement )
        {
            try
            {
                if( !is_string( $sElement ) )
                {
                    throw new Exception( 'Element name provided is not a string.' );
                }

                if( isset( $this->aElements[ $sElement ] ) )
                {
                    unset( $this->aElements[ $sElement ] );
                }
                if( isset( $this->aErrors[ $sElement ] ) )
                {
                    unset( $this->aErrors[ $sElement ] );
                }
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Adds a validator to an alement.
         *
         * @param   string     $sElement        The element to add the validator to/
         * @param   callable   $callValidator   The validation function.
         * @param   string     $sError          The error message to set if validation fails.
         * @param   array      $aOptions        Additional parameters to provide for the validator.
         */
        public function AddValidator( $sElement, $callValidator, $sError, array $aOptions = array() )
        {
            try
            {
                // check if the validator is callable
                if( !is_callable( $callValidator ) )
                {
                    throw new Exception( 'Validator supplied is not callable.' );
                }

                // check if an error message provided and if so, make sure it is a string
                if( !is_string( $sError ) )
                {
                    throw new Exception( 'Error message provided is not a string.' );
                }
                elseif( empty( $sError ) )
                {
                    throw new Exception( 'Error message is empty.' );
                }

                // add the validator to the element
                $this->aElements[ $sElement ][ 'validators' ][] = array(
                    'validator' => $callValidator,
                    'options'   => $aOptions,
                    'error'     => $sError
                );
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Adds validators for the given element.
         *
         * @param   string   $sElement      The element to add validators to.
         * @param   array    $aValidators   The validators to apply to the element.
         */
        public function AddValidators( $sElement, array $aValidators )
        {
            try
            {
                // make sure all the validators were supplied correctly
                $iValidatorCount = count( $aValidators );
                for( $i = 0; $i < $iValidatorCount; ++$i )
                {
                    // check if the validator has been supplied
                    if( !isset( $aValidators[ $i ][ 'validator' ] ) )
                    {
                        throw new Exception( 'Validator was not supplied.' );
                    }

                    // check if the error message has been supplied
                    if( !isset( $aValidators[ $i ][ 'error' ] ) )
                    {
                        throw new Exception( 'Validator was not supplied.' );
                    }

                    // check if the options have been supplied
                    if( !isset( $aValidators[ $i ][ 'options' ] ) )
                    {
                        $aValidators[ $i ][ 'options' ] = array();
                    }

                    // try to add the validator
                    $this->AddValidator(
                        $sElement,
                        $aValidators[ $i ][ 'validator' ],
                        $aValidators[ $i ][ 'error' ],
                        $aValidators[ $i ][ 'options' ]
                    );
                }
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Sets the required status for an element.
         *
         * @param   string    $sElement    Name of the element to set required status for.
         * @param   boolean   $bRequired   Whether or not this element is required.
         * @param   string    $sMissing    Error message for empty required element.
         */
        public function SetRequired( $sElement, $bRequired, $sMissing = '' )
        {
            $this->aElements[ $sElement ][ 'required' ] = $bRequired;
            $this->aElements[ $sElement ][ 'missing' ]  = $sMissing;
        }

        /**
         * Sets the data source for this form.
         *
         * @param   array   $aElements
         *
         * @return  $this
         */
        public function SetDataSource( array $aDataSource )
        {
            $this->aDataSource = $aDataSource;
        }

        /**
         * Extracts the form data from the data source.
         *
         * @return array
         */
        public function GetFormData()
        {
            // initialize the return array
            $aExtracted = array();

            // check if the form has been submitted or not
            $bIsSubmitted = $this->IsSubmitted();

            // extract the form elements from the data source
            foreach( $this->aElements as $sElement => $aElementInfo )
            {
                // pull the element if it exists
                if( isset( $this->aDataSource[ $sElement ] ) )
                {
                    $aExtracted[ $sElement ] = $this->aDataSource[ $sElement ];
                }
                else
                {
                    // initialize the element
                    $aExtracted[ $sElement ] = '';

                    // add the missing error for this element if it is required
                    if( $bIsSubmitted && $this->aElements[ $sElement ][ 'required' ] )
                    {
                        $this->AddElementError( $sElement, $this->aElements[ $sElement ][ 'missing' ] );
                    }
                }
            }

            return $aExtracted;
        }

        /**
         * Returns the errors found during validation.
         *
         * @return array
         */
        public function GetErrors()
        {
            return $this->aErrors;
        }

        /**
         * Returns all errors for an element.
         *
         * @param   string   $sElement
         *
         * @return  array
         */
        public function GetElementErrors( $sElement )
        {
            $aErrors = array();
            if( isset( $this->aErrors[ $sElement ] ) )
            {
                $aErrors =  $this->aErrors[ $sElement ];
            }
            return $aErrors;
        }

        /**
         * Adds and error to an element.
         *
         * @param   string   $sElement   The element to add the error to.
         * @param   string   $sError     The error to add.
         */
        public function AddElementError( $sElement, $sError )
        {
            // check if the element errors have been set
            if( !isset( $this->aErrors[ $sElement ] ) )
            {
                $this->aErrors[ $sElement ] = array();
            }

            $this->aErrors[ $sElement ][] = $sError;
        }

        /**
         * Removes all errors from all elements.
         */
        public function ClearAllErrors()
        {
            foreach( $this->aElements as $sElement => $aElementInfo )
            {
                $this->ClearElementErrors( $sElement );
            }
        }

        /**
         * Removes errors for the given element.
         *
         * @param   string   $sElement   The element to clear errors for.
         */
        public function ClearElementErrors( $sElement )
        {
            unset( $this->aErrors[ $sElement ] );
        }

        /**
         * Checks whether or not the fields that have been set exist in the data sourse.
         *
         * @return boolean
         */
        public function IsSubmitted()
        {
            // initialize the return value
            $bIsSubmitted = true;

            // check through all the elements to see if they exist in the data source
            foreach( $this->aElements as $sElement => $aElementInfo )
            {
                if( $aElementInfo[ 'required' ] && !isset( $this->aDataSource[ $sElement ] ) )
                {
                    $bIsSubmitted = false;
                    break;
                }
            }

            return $bIsSubmitted;
        }

        /**
         * Checks if the data in the data source is valid.
         *
         * Calls each validator provided for each element with the options supplied.
         * If the element is not valid, add the custom error to the list of all errors
         * for the given element.
         *
         * @return boolean
         */
        public function IsValid()
        {
            // initialize the return value
            $bIsValid = true;

            // cycle through the elements and try to validate them
            foreach( $this->aElements as $sElement => $aElementInfo )
            {
                // if the element is required and empty, add the missing message
                if( $aElementInfo[ 'required' ] && empty( $this->aDataSource[ $sElement ] ) )
                {
                    // set the return value
                    $bIsValid = false;

                    $this->AddElementError( $sElement, $aElementInfo[ 'missing' ] );
                }
                // only validate if this element is required or a value was supplied
                elseif( $aElementInfo[ 'required' ] || !empty( $this->aDataSource[ $sElement ] ) )
                {
                    // validate the element against each validator
                    foreach( $aElementInfo[ 'validators' ] as $aValidator )
                    {
                        // build the array of parameters to the validator
                        array_unshift( $aValidator[ 'options' ], $this->aDataSource[ $sElement ] );

                        // validate against the validator
                        $vReturn = call_user_func_array( $aValidator[ 'validator' ], $aValidator[ 'options' ] );
                        if( $vReturn !== true )
                        {
                            // set the return value
                            $bIsValid = false;

                            // add the error to this element
                            $sError = isset( $aValidator[ 'error' ] ) ? $aValidator[ 'error' ] : $vReturn;
                            $this->AddElementError( $sElement, $sError );
                        }
                    }
                }
            }

            return $bIsValid;
        }
    }
?>