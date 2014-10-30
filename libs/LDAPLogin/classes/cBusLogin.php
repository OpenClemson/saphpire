<?php
    require_once( sBASE_INC_PATH . '/libs/LDAPLogin/classes/cForm.php' );
    require_once( sCORE_INC_PATH . '/classes/cValidateBase.php' );
    require_once( sCORE_INC_PATH . '/classes/cAuthBase.php' );

    /**
     * Business functionality for interacting with consumer data.
     *
     * @author   Team Rah
     * @version  0.2.0
     */
    class cBusLogin
    {
        /**
         * @var object ldap class object
         */
        protected $oLdap = null;

        /**
         * sets up the ldap object
         *
         */
        public function __construct( $oLdap = null )
        {
            try
            {
                // check if a object was passed in
                if( $oLdap !== null )
                {
                    $this->oLdap = $oLdap;
                }
                // no object passed in
                else
                {
                    require_once( sCORE_INC_PATH . '/classes/cLdap.php' );
                    $this->oLdap = new cLdap( GetConfig(), 'ldap' );
                }
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         *
         * @throws  Exception   Rethrows anything thrown from a lower level.
         *
         */
        public function HandleLoginForm( array $aDataSource )
        {
            try
            {
                // initialize form data
                $aFormData = array(
                    'status' => null,
                    'errors' => array(),
                    'data' => array()
                );

                $oBaseValidator = new cValidateBase();

                // get the form for this page
                $oForm = new cForm();
                $oForm->SetDataSource( $aDataSource );

                // add the application name element
                $oForm->AddElement( 'username', true, 'Please provide a username.' );
                $oForm->AddValidator(
                    'username',
                    array( $oBaseValidator, 'ValidateRequired' ),
                    'Username is a required field.'
                );

                // add the application description element
                $oForm->AddElement( 'password', true, 'Please provide an application description.' );
                $oForm->AddValidator(
                    'password',
                    array( $oBaseValidator, 'ValidateRequired' ),
                    'Password is a required field.'
                );

                if( !empty( $aDataSource ) )
                {
                    // get the submitted data
                    $aFormData[ 'data' ] = $oForm->GetFormData();

                    // check if the form was submitted
                    if( $oForm->IsSubmitted() )
                    {
                        // check if the submitted form was valid
                        $aFormData[ 'status' ] = $oForm->IsValid();

                        // if everything is valid, try to login
                        $bUserAccepted = $this->oLdap->AuthUser( $aFormData[ 'data' ][ 'username' ], $aFormData[ 'data' ][ 'password' ] );

                        // valid form but bad username/password combo
                        if( $aFormData[ 'status' ] && !$bUserAccepted )
                        {
                            $aFormData[ 'status' ] = false;
                            $oForm->AddElementError( 'username', 'Incorrect username/password combination.' );
                        }
                        // valid form and good username/password combo
                        elseif( $aFormData[ 'status' ] && $bUserAccepted )
                        {
                            // logged in succesfully
                            $oAuth = new cAuthBase();
                            $oAuth->SetUser( $aFormData[ 'data' ][ 'username' ] );

                            if( isset( $_SESSION[ 'onsuccess' ] ) && !empty( $_SESSION[ 'onsuccess' ] ) )
                            {
                                header( 'Location: http://' . $_SESSION[ 'onsuccess' ] );
                                die();
                            }
                        }

                        // get any errors
                        $aFormData[ 'errors' ] = $oForm->GetErrors();
                    }
                }

                return $aFormData;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>