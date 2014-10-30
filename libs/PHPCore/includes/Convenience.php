<?php
    // get the custom string code exception class
    require_once sCORE_INC_PATH . '/classes/cStringCodeException.php';

    /**
     * Global functions available to the application.
     *
     * @author  Team Rah
     *
     * @package Core
     * @version 0.3.1
     */

    /**
     * Convenience function to return configuration settings.
     *
     * @param   boolean     $bGetEverything     True to get all config info,
     *                                          false for only environment data.
     *
     * @return  &array      Reference to original array.
     */
    function GetConfig( $bGetEverything = false )
    {
        // get access to the configs
        global $aConfigs;

        // initialize the return value
        $aReturn = isset( $aConfigs ) ? $aConfigs : array();

        // get all databases
        if( isset( $aConfigs[ 'instance' ] )
            && is_array( $aConfigs[ 'instance' ] )
            && !empty( $aConfigs[ 'instance' ] ) )
        {
            // correct the structure if needed
            if( !isset( $aConfigs[ 'instance' ][ 0 ] ) )
            {
                $aConfigs[ 'instance' ] = array( $aConfigs[ 'instance' ] );
            }

            // cycle through the instances
            $iInstances = count( $aConfigs[ 'instance' ] );
            for( $iInstanceCounter = 0; $iInstanceCounter < $iInstances; ++$iInstanceCounter )
            {
                // check if this instance has an environment
                if( isset( $aConfigs[ 'instance' ][ $iInstanceCounter ][ 'env' ] )
                    && is_string( $aConfigs[ 'instance' ][ $iInstanceCounter ][ 'env' ] ) )
                {
                    // get the environment
                    $sEnv = $aConfigs[ 'instance' ][ $iInstanceCounter ][ 'env' ];

                    // initialize the database structures
                    $aReturn[ $sEnv ][ 'mysql' ]  = array();
                    $aReturn[ $sEnv ][ 'oracle' ] = array();

                    // check if there are any MySQL connections
                    if( isset( $aConfigs[ 'instance' ][ $iInstanceCounter ][ 'mysql' ] ) )
                    {
                        // save the connections
                        $aReturn[ $sEnv ][ 'mysql' ]  = $aConfigs[ 'instance' ][ $iInstanceCounter ][ 'mysql' ];

                        // correct structure if needed
                        if( !isset( $aReturn[ $sEnv ][ 'mysql' ][ 0 ] ) )
                        {
                            $aReturn[ $sEnv ][ 'mysql' ] = array( $aReturn[ $sEnv ][ 'mysql' ] );
                        }
                    }

                    // check if there are any Oracle connections
                    if( isset( $aConfigs[ 'instance' ][ $iInstanceCounter ][ 'oracle' ] ) )
                    {
                        // save the connections
                        $aReturn[ $sEnv ][ 'oracle' ] = $aConfigs[ 'instance' ][ $iInstanceCounter ][ 'oracle' ];

                        // correct structure if needed
                        if( !isset( $aReturn[ $sEnv ][ 'oracle' ][ 0 ] ) )
                        {
                            $aReturn[ $sEnv ][ 'oracle' ] = array( $aReturn[ $sEnv ][ 'oracle' ] );
                        }
                    }

                    // check if there are any LDAP connections
                    if( isset( $aConfigs[ 'instance' ][ $iInstanceCounter ][ 'connection' ] ) )
                    {
                        // save the connections
                        $aReturn[ $sEnv ][ 'ldap' ] = $aConfigs[ 'instance' ][ $iInstanceCounter ][ 'connection' ];

                        // correct structure if needed
                        if( !isset( $aReturn[ $sEnv ][ 'ldap' ][ 0 ] ) )
                        {
                            $aReturn[ $sEnv ][ 'ldap' ] = array( $aReturn[ $sEnv ][ 'ldap' ] );
                        }
                    }
                }
            }
        }

        // check if the environment is set
        if( defined( 'sAPPLICATION_ENV' )
            && isset( $aReturn[ sAPPLICATION_ENV ] )
            && !$bGetEverything )
        {
            $aReturn = $aReturn[ sAPPLICATION_ENV ];
        }

        return $aReturn;
    }

    /**
     * Returns the current host.
     *
     * @return string
     */
    function GetHost()
    {
        // check if we're running from a browser
        if( isset( $_SERVER[ 'HTTP_HOST' ] ) )
        {
            $sHost = strtolower( $_SERVER[ 'HTTP_HOST'] );
        }
        // otherwise, it's from the command line
        else
        {
            $sHost = gethostname();
        }

        return $sHost;
    }

    /**
     * Returns either all contacts or all contacts with the given criteria.
     *
     * @param   string  $aCriteria OPTIONAL Array of nodes and values that is
     *                                      used to limit contacts returned.
     *
     * @return  array
     */
    function GetContacts( $aCriteria = array() )
    {
        // initialize  the return array
        $aContacts = array();

        // get the contacts for this app
        $aConfig = GetConfig( true );
        if( isset( $aConfig[ 'contact' ] ) )
        {
            // get the contacts
            $aContacts = $aConfig[ 'contact' ];

            // format into an array if needed
            if( !isset( $aContacts[ 0 ] ) )
            {
                $aTempContacts = array( 0 => $aContacts );
                $aContacts = $aTempContacts;
            }

            // check if options were provided
            if( !empty( $aCriteria ) && is_array( $aCriteria ) )
            {
                // remove users that do not meet the criteria
                $iContactCount = count( $aContacts );
                for( $i = 0; $i < $iContactCount; ++$i )
                {
                    // cycle through the criteria
                    foreach( $aCriteria as $sNode => $sValue )
                    {
                        // remove contacts that do not meet the criteria
                        if( isset( $aContacts[ $i ][ $sNode ] )
                            && ( is_array( $aContacts[ $i ][ $sNode ] ) && !in_array( $sValue, $aContacts[ $i ][ $sNode ] ) )
                               || ( ( !is_array( $aContacts[ $i ][ $sNode ] ) ) && $aContacts[ $i ][ $sNode ] != $sValue ) )
                        {
                            unset( $aContacts[ $i ] );
                            break;
                        }
                    }
                }

                // reset array keys
                sort( $aContacts );
            }
        }

        return $aContacts;
    }

    /**
     * Returns whether or not a developer is currently logged in.
     *
     * @return boolean
     */
    function IsDevLoggedIn()
    {
        // initialize  dev override flag
        $bDevLoggedIn = false;

        // try to get the currently logged in user
        $sUser = cAuthBase::GetUser();

        // only check contacts if a user is logged in
        if( !empty( $sUser ) )
        {
            // check if user is in the contacts for this app
            $aConfig = GetConfig( true );

            if( isset( $aConfig[ 'contact' ] ) )
            {
                // format into an array if needed
                if( !is_array( $aConfig[ 'contact' ] ) )
                {
                    $aConfig[ 'contact' ] = array( $aConfig[ 'contact' ] );
                }

                // check if the user logged in is a developer
                $iContactCount = count( $aConfig[ 'contact' ] );
                for( $i = 0; $i < $iContactCount; ++$i )
                {
                    if( strtolower( $aConfig[ 'contact' ][ $i ][ 'username' ] ) === strtolower( $sUser )
                        && $aConfig[ 'contact' ][ $i ][ 'role' ] === 'dev'
                      )
                    {
                        $bDevLoggedIn = true;
                        break;
                    }
                }
            }
        }

        return $bDevLoggedIn;
    }

    /**
     * Used to keep unwanted visitors out. Default Dev-only environments
     * are localhost and DEV. You can specify more restrictions on other
     * environments by including them in the $aEnvironments array.
     *
     * Dev-only all instances:
     * RequireDev( array( 'local', 'dev', 'demo', 'qa', 'prod' ) );
     *
     * Default Dev-only:
     * RequireDev();
     *
     * @param   array       $aEnvironments ( optional ) List of environments to restrict
     *                                     to Dev-only users.
     *
     * @throws  Exception                   rethrows anything it catches.
     *
     * @return  void
     */
    function RequireDev ( $aEnvironments = array( 'local', 'dev' ) )
    {
        // enforce case on the environments
        $aEnvironments = array_map( 'strtolower', $aEnvironments );

        // check if the application environment has been set
        if ( !defined( 'sAPPLICATION_ENV' ) )
        {
            throw new Exception( 'Application environment has not been set yet.' );
        }
        else if (  in_array( strtolower( sAPPLICATION_ENV ), $aEnvironments )
                && !IsDevLoggedIn()
                )
        {
            throw new cStringCodeException( 'User is not a developer.', 'non-dev' );
        }
    }
?>