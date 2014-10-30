<?php
    // get the base class
    require_once( sCORE_INC_PATH . '/classes/cPresBase.php' );

    /**
     * Presentation functionality for contact information.
     *
     * @author       Team Rah
     * @package      LDAPLogin
     * @subpackage   Presentation
     * @version      0.2.0
     */
    class cPresLogin extends cPresBase
    {
        /**
         * calls the parent constructor
         *
         * @throws Exception rethrows anything caught at a lower level
         */
        public function __construct()
        {
            try
            {
                parent::__construct( sBASE_INC_PATH . '/libs/LDAPLogin/templates' );
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Generates the errors for forms.
         *
         * @param   array   $aElementErrors
         *
         * @return  string  Generated HTML
         */
        public function GenerateElementErrors( array $aElementErrors = array() )
        {
            try
            {
                // initialize the return value
                $sErrors = '';

                // only build errors if they exist
                if( !empty( $aElementErrors ) )
                {
                    // build the list of errors for each element
                    foreach( $aElementErrors as $sElement => $aErrors )
                    {
                        $iElementCount = count( $aErrors );
                        for( $i = 0; $i < $iElementCount; ++$i )
                        {
                            $sErrors .= '&bull; ' . $aErrors[ $i ] . "\n";
                        }
                    }

                    // convert newlines if needed
                    if( !bIS_CLI )
                    {
                        $sErrors = nl2br( $sErrors );
                    }

                    // if there's only one error, we don't need a bullet list
                    if( count( $aElementErrors ) == 1 && count( $aElementErrors[ key( $aElementErrors ) ] ) == 1 )
                    {
                        $sErrors = ltrim( $sErrors, '&bull; ' );
                    }

                    // build the template data
                    $aErrorTemplate = array();
                    $aErrorTemplate[ 'template' ]      = 'form-error.html';
                    $aErrorTemplate[ '_:_MESSAGE_:_' ] = $sErrors;

                    $sErrors = $this->PopulateTemplate( $aErrorTemplate );
                }

                return $sErrors;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Generates the status message for the form.
         *
         * @param   null | boolean  $vFormStatus       If null, the form was not submitted.
         *                                             Otherwise, if the submission was successful or not.
         * @param   string          $sSuccessMessage   Message to display when the submission was successful.
         * @param   array           $aErrors           The list of errors that were encountered in the submission process.
         *
         * @return  string
         */
        public function GenerateFormStatus( $vFormStatus, $sSuccessMessage, array $aErrors = array() )
        {
            try
            {
                // initialize the return value
                $sStatus = '';

                // check if submission was successful or not
                if( false === $vFormStatus )
                {
                    // build errors
                    $sStatus = $this->GenerateElementErrors( $aErrors );
                }
                elseif( true === $vFormStatus && !empty( $sSuccessMessage ) )
                {
                    // add the success message
                    $sStatus = $this->PopulateTemplate(
                        array(
                            'template'      => 'form-success.html',
                            '_:_MESSAGE_:_' => $sSuccessMessage
                        )
                    );
                }

                return $sStatus;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
        /**
         * Generates the profile page.
         *
         * @param   array   $aPageData
         *
         * @return  string
         */
        public function GenerateLoginPage( array $aPageData )
        {
            try
            {
                // initialize output for this page
                $sTitle  = '';
                $sHeader = '';
                $sBody   = '';
                $sFooter = '';
                $sLayout = 'layout-login.html';
                $aBody   = array( 'template' => 'form-login.html' );

                // initialize page data
                // set the status for the form submission
                $aBody[ '_:_FORM-STATUS_:_' ] = $this->GenerateFormStatus(
                    $aPageData[ 'status' ],
                    'Logged in.',
                    $aPageData[ 'errors' ]
                );
                $aBody[ '_:_USERNAME_:_' ] = !empty( $aPageData[ 'data' ][ 'username' ] ) ? $aPageData[ 'data' ][ 'username' ] : '';
                $aBody[ '_:_PASSWORD_:_' ] = !empty( $aPageData[ 'data' ][ 'password' ] ) ? $aPageData[ 'data' ][ 'password' ] : '';;

                // build the page
                $sBody   = $this->PopulateTemplate( $aBody );
                $sOutput = $this->PopulateLayout( $sTitle, $sHeader, $sBody, $sFooter, $sLayout );

                return $sOutput;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>