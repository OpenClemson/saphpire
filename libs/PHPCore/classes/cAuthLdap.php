<?php
    // require the abstract class that this extends
    require_once( sCORE_INC_PATH . '/classes/cAuthBase.php' );

    // get the exception handling functionality
    require_once( sCORE_INC_PATH . '/classes/cAnomaly.php' );

    /**
     * Authentication class that uses the cookie based CUTokenAuth system.
     *
     * @author     Team Rah
     * @package    Core
     * @subpackage Authentication
     * @version    0.3.0
     */
    class cAuthLdap extends cAuthBase
    {
        /**
         * Set the URL to redirect to on successful login
         * and redirect to login.clemson.edu for authentication.
         *
         * @throws  Exception
         */
        protected function LoginRedirect()
        {
            try
            {
                // set the success url in session
                $_SESSION[ 'onsuccess' ] = $this->sSuccessUrl;

                // get the base url
                $sSlash = substr( $_SERVER[ 'HTTP_HOST' ], -1 ) == '/' ? '' : '/';

                // redirect to the login site
                header( 'Location: ' . $this->GetProtocol() . '://' . $_SERVER[ 'HTTP_HOST' ] . $sSlash . 'ldap-login.php' );
                die();
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * LDAP based authentication.
         *
         * Check if a user is set in the session. If not, redirect to the login page.
         * After successful login, redirect to the home page of the application.
         */
        public function Authenticate()
        {
            try
            {
                // check if we're already authenticated
                if( !$this->IsAuthenticated() )
                {
                    $this->LoginRedirect();
                }
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>