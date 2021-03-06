<?php
    // get the exception handling functionality
    require_once( sCORE_INC_PATH . '/classes/cAnomaly.php' );

    /**
     * Abstract authentication factory class.
     *
     * @author     Team Rah
     * @package    Core
     * @subpackage Authentication
     * @version    0.2.0
     */
    class cAuthAbs
    {
        /**
         * Creates, authenticates, and returns an authentication object of the specified type.
         *
         * Currently supported types: CUTokenAuth, Shibboleth | Shib, LDAP.
         *
         * @param  string     $sType          Type of authentication object to create.
         * @param  string     $sSuccessUrl    Optional URL to redirect to upon successful authentication.
         *
         * @return cCUTokenAuth | cShibAuth
         */
        public static function GetAuthObj( $sType, $sSuccessUrl = null )
        {
            try
            {
                // if we dont have a config throw an exception
                if( !is_string( $sType )  )
                {
                    throw new Exception( "The requested auth configuration (" . $sType . ") does not exist.", 2 );
                }

                // check if success url was provided correctly
                if( !empty( $sSuccessUrl ) && !is_string( $sSuccessUrl ) )
                {
                    throw new Exception( 'URL provided is not a string.', 2 );
                }

                // initialize  the authentication object
                $oAuth = null;

                // make the connection
                switch( strtoupper( $sType ) )
                {
                    // login through login.clemson.edu
                    case "CUTOKENAUTH" :
                        require_once( sCORE_INC_PATH . '/classes/cAuthCUToken.php' );

                        // make a CUTokenAuth object
                        $oAuth = new cAuthCUToken( $sSuccessUrl );
                        break;

                    // login through shibboleth
                    case "SHIB" :
                    case "SHIBBOLETH" :
                        require_once( sCORE_INC_PATH . '/classes/cAuthShib.php' );

                        // make a CUTokenAuth object
                        $oAuth = new cAuthShib( $sSuccessUrl );
                        break;

                    case 'LDAP' :
                        require_once( sCORE_INC_PATH . '/classes/cAuthLdap.php' );

                        // make an LdapAuth Object
                        $oAuth = new cAuthLdap( $sSuccessUrl );
                        break;

                    // Otherwise throw an exception because there is no valid authentication method
                    default :
                        throw new Exception( "No VALID authentication method was defined.", 2 );
                }

                // authenticate the user
                $oAuth->Authenticate();

                return $oAuth;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>