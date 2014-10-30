<?php
    /**
     * authenticate a user
     */
    // create auth object
    $oAuthObj = cAuthAbs::GetAuthObj( 'LDAP' );
    // $oAuthObj = cAuthAbs::GetAuthObj( 'CUTokenAuth' );
    // $oAuthObj = cAuthAbs::GetAuthObj( 'Shib' );

    // authenticate
    if ( !$oAuthObj->IsAuthenticated() )
    {
        $oAuthObj->Authenticate();
    }

    // get authenticated user
    $sUser = $oAuthObj->GetUser();
?>