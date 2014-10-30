<?php
    try
    {
        // pull in the config file
        require_once 'config.php';

        // pull in the base bootstrap
        require_once sCORE_INC_PATH . '/includes/base-bootstrap.php';

        // pull in the bootstrap file
        require_once sBASE_INC_PATH . '/libs/LDAPLogin/includes/login-bootstrap.php';

        $aPageData = $oBusiness->HandleLoginForm( $_POST );
        echo $oPresentation->GenerateLoginPage( $aPageData );
    }
    catch( Exception $oException )
    {
        cAnomaly::ExceptionHandler( $oException );
    }
?>