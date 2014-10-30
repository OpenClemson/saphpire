<?php
    /**
     * Database operations
     */

    // get a database object
    $oDbConn = cDbAbs::GetDbObj( $aDbConf, 'database_name' );

    // make a connection
    $oDbConn->GetConnection();

    /**
     * run a query
     */
    // set query
    $sInsertQry = 'INSERT INTO `table` ( id, value ) VALUES( NULL, :value )';
    // create bind array
    $aBinds     = array( 'value' => 'test' );
    /// run query
    $bResult    = $oDbConn->RunQuery( $sInsertQry, $aBinds );
    // check result
    if ( $bResult !== false )
    {
        // success!
    }
    else
    {
        // failure.
    }

    // get last insert id
    $iNewRecord = $oDbConn->GetLastSequenceId();

    /**
     * transactions
     */
    // start a transaction
    $oDbConn->StartTransaction();
    // run query
    $bResult = $oDbConn->RunQuery( $sInsertQry, $aBinds );

    // check result
    if ( $bResult !== false )
    {
        // commit operations
        $oDbConn->Commit();
    }
    else
    {
        // rollback operations
        $oDbConn->Rollback();
    }

    /**
     * get single result from a query
     */
    $sSelectQry = 'SELECT NOW() as time';
    $aResult    = $oDbConn->GetSingleQueryResults( $sSelectQry );
    if ( !empty( $aResult ) )
    {
        $sCurTime = $aResult[ 'time' ];
    }

    /**
     * get results from a query
     */
    // initialize empty result
    $aUsers = array();
    // set query
    $sSelectQry = '
    SELECT  first_name,
            last_name,
            email
    FROM    users
    WHERE   user_id = :user_id1
        OR  user_id = :user_id2
    ';
    // create bind array
    $aBinds   = array( 'user_id1' => 1, 'user_id2' => 2 );
    // run query
    $aResults = $oDbConn->GetQueryResults( $sSelectQry, $aBinds );
    // check results
    if ( !empty( $aResult ) )
    {
        // capture all results
        $aUsers = $aResults;

        // access individual results
        $aUser1 = isset( $aUsers[ 0 ] ) ? $aUsers[ 0 ] : array();
        $aUSer2 = isset( $aUsers[ 1 ] ) ? $aUsers[ 1 ] : array();
    }

    // done with database
    unset( $oDbConn );
?>