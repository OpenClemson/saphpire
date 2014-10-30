<?php
    try
    {
        // get the ldap class
        require_once sCORE_INC_PATH . '/classes/cLdap.php';

        // create a ldap connection
        $oLdap = new cLdap( GetConfig(), 'ldap' );

        // pull in the business and presentation class
        require_once sBASE_INC_PATH . '/libs/LDAPLogin/classes/cBusLogin.php';
        require_once sBASE_INC_PATH . '/libs/LDAPLogin/classes/cPresLogin.php';

        // set instances for business and presentation
        $oBusiness     = new cBusLogin( $oLdap );
        $oPresentation = new cPresLogin();
    }
    catch( Exception $oException )
    {
        cAnomaly::ExceptionHandler( $oException );
    }
?>