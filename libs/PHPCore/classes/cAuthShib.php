<?php
    // require the abstract class that this extends
    require_once( sCORE_INC_PATH . '/classes/cAuthBase.php' );

    // require the request class so we can check if the user is logged in
    require_once( sCORE_INC_PATH . '/classes/cRequest.php' );

    // get the exception handling functionality
    require_once( sCORE_INC_PATH . '/classes/cAnomaly.php' );

    /**
     * Authentication class that utilizes Shibboleth.
     *
     * @author     Team Rah
     * @package    Core
     * @subpackage Authentication
     * @version    0.1.0
     */
    class cAuthShib extends cAuthBase
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
                // set cookies to know where to redirect on success
                setcookie( 'AUTHURL', $this->sSuccessUrl, 0, "/", '.clemson.edu', false, true);
                setcookie( 'AUTHREASON', '', 0, "/", '.clemson.edu', false, true);

                // redirect to the login site
                header( 'Location: ' . $this->GetProtocol() . '://' . GetHost() . '/Shibboleth.sso/Login?target=' . $this->sSuccessUrl );
                die();
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Checks if a user is currently saved in the session.
         *
         * @return boolean
         */
        public function IsAuthenticated()
        {
            try
            {
                // initialize return value
                return isset( $_SERVER[ 'cn' ] );
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Extending class must implement this.
         *
         * On successful authentication, call SetUser.
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

                if( isset( $_SERVER[ 'cn' ] ) )
                {
                    $this->SetUser( $_SERVER[ 'cn' ] );
                }
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>