<?php
    // get the interface that this class implements
    require_once( sCORE_INC_PATH . '/classes/ifDb.php' );

    // get the exception handling functionality
    require_once( sCORE_INC_PATH . '/classes/cAnomaly.php' );

    /**
     * Oracle database adapter.
     *
     * @author     Team Rah
     * @package    Core
     * @subpackage Database
     * @version    0.6.0
     */
    class cDbOracle implements ifDb
    {
        /**
         * Username to use when connecting to database.
         * Set in db configuration file.
         *
         * @var string
         */
        private $sUsername = '';

        /**
         * Password to use when connecting to database.
         * Set in db configuration file.
         *
         * @var string
         */
        private $sPassword = '';

        /**
         * Host address to use when connecting to database.
         * Set in db configuration file.
         *
         * @var string
         */
        private $sHost = '';

        /**
         * Service name to use when connecting to database.
         * Set in db configuration file.
         *
         * @var string
         */
        private $sServiceName = '';

        /**
         * Connect Name from the tnsnames.ora file used for failover
         * Set in db.ini config file.
         *
         * @var string
         */
        private $sTnsName = '';

        /**
         * Port to use when connecting to database.
         * Defaults to standard Oracle port, 1521.
         * Set in db configuration file.
         *
         * @var integer
         */
        private $iPort = 1521;

        /**
         * Number of attempts to make when connecting.
         * At least one attempt is made.
         * Set in db configuration file.
         *
         * @var integer
         */
        private $iRetries = 1;

        /**
         * Number of attempts to make when connecting.
         * At least one attempt is made.
         * Set in db configuration file.
         *
         * @var integer
         */
        private $iError = 0;

        /**
         *
         * Sets the character set to be used in the database connection.
         * @var string
         */
        private $sCharset = 'utf8';

        /**
         * Database object.
         *
         * @var object
         */
        private $oConnection = null;

        /**
         * When set to OCI_COMMIT_ON_SUCCESS, queries and statements are automatically committed.
         * When set to OCI_NO_AUTO_COMMIT, queries and statements must be committed manually.
         *
         * @var boolean
         */
        private $iCommit = OCI_COMMIT_ON_SUCCESS;

        /**
         * Constructor for the database class.
         *
         * @param array $aSpecConf      Array of database settings, taken from the db config
         *                              file and parsed by the cBaseConfig class
         *
         * @throws Exception
         */
        public function __construct( array $aSpecConf = array() )
        {
            // Check first for a non-empty TNS Names. If one is set, then we don't
            // need to look for host, port, or service name. Otherwise, we require
            // those values.
            if( isset( $aSpecConf[ 'sTnsName' ] ) && !empty( $aSpecConf[ 'sTnsName' ] ) )
            {
                $this->sTnsName = $aSpecConf[ 'sTnsName' ];
            }
            else
            {
                // Verify there is a valid host and set it if there is
                if( !isset( $aSpecConf[ 'host' ] ) || empty( $aSpecConf[ 'host' ] ) )
                {
                    throw new Exception( 'Missing host setting.' );
                }
                $this->sHost = $aSpecConf[ 'host' ];

                // parse the host for service name if needed
                if( !isset( $aSpecConf[ 'service' ] ) )
                {
                    // remove leading slashes
                    $this->sHost = substr( $this->sHost, 2 );

                    // check for the service name
                    if( strpos( $this->sHost, '/' ) === false )
                    {
                        throw new Exception( 'Missing service setting.' );
                    }

                    // split on the slash
                    $aHost = explode( '/', $this->sHost );
                    $aSpecConf[ 'service' ] = $aHost[ 1 ];
                    $this->sHost = $aHost[ 0 ];
                }

                // parse port from host if needed
                if( !isset( $aSpecConf[ 'port' ] ) && strpos( $this->sHost, ':' ) )
                {
                    $aHost = explode( ':', $this->sHost );
                    $this->sHost = $aHost[ 0 ];
                    $aSpecConf[ 'port' ] = $aHost[ 1 ];
                }

                // We parsed from host, but if values are set individually, they take precedence
                // Verify there is a valid database name and set it if there is
                if( !isset( $aSpecConf[ 'service' ] ) || empty( $aSpecConf[ 'service' ] ) )
                {
                    throw new Exception( 'Missing service setting.' );
                }
                $this->sServiceName   = $aSpecConf[ 'service' ];

                // If a port is set, use it, otherwise use the default
                if( isset( $aSpecConf[ 'port' ] ) && !empty( $aSpecConf[ 'port' ] ) )
                {
                    $this->iPort = $aSpecConf[ 'port' ];
                }
            }

            // Verify there is a valid username and set it if there is
            if( !isset( $aSpecConf[ 'username' ] ) || empty( $aSpecConf[ 'username' ] ) )
            {
                throw new Exception( 'Missing username setting.' );
            }
            $this->sUsername = $aSpecConf[ 'username' ];

            // If a password is set, use it, otherwise leave it as empty string
            if( isset( $aSpecConf[ 'password' ] ) && !empty( $aSpecConf[ 'password' ] ) )
            {
                $this->sPassword = $aSpecConf[ 'password' ];
            }

            // If a character set is set, use it, otherwise use the default
            if( isset( $aSpecConf[ 'charset' ] ) && !empty( $aSpecConf[ 'charset' ] ) )
            {
                $this->sCharset = $aSpecConf[ 'charset' ];
            }

            // If there is a number of retries set, use it (and increment by 1 because the
            //  first try doesnt' count). Else, leave it at the default 1.
            if( isset( $aSpecConf[ 'retries' ] ) && !empty( $aSpecConf[ 'retries' ] ) && $aSpecConf[ 'retries' ] > 0 )
            {
                $this->iRetries = $aSpecConf[ 'retries' ] + 1;
            }

            // Set the PDO error reporting level. Expected options are: 'EXCEPTION' 'WARNING' 'SILENT'
            if( isset( $aSpecConf[ 'sErrorMode' ] ) && $aSpecConf[ 'sErrorMode' ] != '' )
            {
                // @todo: find oci errors
            }
        }

        /**
         * Ensures that the query provided is a string
         * and returns the query trimmed of preceding
         * or trailing whitespace.
         *
         * @param string $sQuery
         */
        public function CleanQuery( $sQuery )
        {
            // check if the query was supplied correctly
            if( !is_string( $sQuery ) )
            {
                throw new Exception( 'Query provided is not a string' );
            }

            // trim the query
            return trim( $sQuery );
        }

        /**
         *
         * Sets a connection to the database. If the first attempt does not succeed, it
         * will try a number of times equal to iRetries.
         * Sets the error mode after connection is made.
         *
         * @return null
         */
        public function GetConnection()
        {
            try
            {
                // check if a connection exists
                if( $this->oConnection === null )
                {
                    // try to create the connection
                    $iRetryCount = 0;
                    while ( $iRetryCount < $this->iRetries )
                    {
                        try
                        {
                            // check for tnsnames entry
                            if( !empty( $this->sTnsName ) )
                            {
                                // try to connect with tnsname
                                $this->oConnection = oci_connect( $this->sUsername, $this->sPassword, $this->sTnsName, $this->sCharset );
                            }
                            else
                            {
                                // try to connect with hardcoded credentials
                                $sConnection  = '(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)';
                                $sConnection .= '(HOST = ' . $this->sHost . ')(PORT = ' . $this->iPort . '))) (CONNECT_DATA = (SERVICE_NAME = ' . $this->sServiceName . ')))';
                                $this->oConnection = oci_connect( $this->sUsername, $this->sPassword, $sConnection, $this->sCharset );
                            }
                        }
                        catch ( Exception $oException )
                        {
                            // ignore exception while trying to connect
                        }
                        ++$iRetryCount;
                    }

                    if( !( $this->oConnection ) )
                    {
                        $aError = oci_error();
                        throw new Exception( htmlentities( 'Could not connect to database host ' . $this->sHost
                                                           . ' using username ' . $this->sUsername
                                                           . '. Oracle Error Message: ' . $aError[ 'message' ], ENT_QUOTES ) );
                    }
                }

                return $this->oConnection;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Runs a SQL statement, and returns true if the query was successful.
         *
         * @param   string      $sQuery           query to run
         * @param   array       $aBindVariables   array of bind variables, key => value
         *
         * @throws  Exception
         *
         * @return  boolean
         *
         * Example:
         *   RunQuery( 'insert into `names` VALUES (null, :firstname, :lastname) ',
         *              array( 'firstname' => 'John', 'lastname' => 'Smith' ) );
         */
        public function RunQuery( $sQuery, array $aBindVariables = array() )
        {
            try
            {
                // cleanup the query
                $sQuery = $this->CleanQuery( $sQuery );

                // ensure we have a connection
                $this->GetConnection();

                $oOciStatement = oci_parse( $this->oConnection, $sQuery );

                if( $oOciStatement )
                {
                    // prepare the bind variables, if any
                    if( !empty( $aBindVariables ) )
                    {
                        foreach ( $aBindVariables as $sVariable => $vValue )
                        {
                            // For oci, we have to pass in the colon, so if it's not there, add it.
                            $sNewVariable = $sVariable;
                            if( substr( $sNewVariable, 0, 1 ) != ':' )
                            {
                                $sNewVariable = ':' . $sNewVariable;
                            }

                            // Bind the variable
                            if( !oci_bind_by_name( $oOciStatement, $sNewVariable, $aBindVariables[ $sVariable ] ) )
                            {
                                throw new Exception( 'Could not bind variable ' . $sNewVariable . ' to : ' . print_r( $vValue, true ) );
                            }
                        }
                    }
                    if( oci_execute( $oOciStatement, $this->iCommit ) === false )
                    {
                        $aOracleError = oci_error( $this->oConnection );
                        $sErrorMessage = $aOracleError[ 'message' ] . ' with query ' . $sQuery;
                        if( !empty ( $aBindVariables ) )
                        {
                            $sErrorMessage .= ' ' . json_encode( $aBindVariables );
                        }
                        throw new Exception( $sErrorMessage );
                    }
                }
                else
                {
                    $aOracleError = oci_error( $this->oConnection );
                    $sErrorMessage = $aOracleError[ 'message' ] . ' with query ' . $sQuery;
                    if( !empty ( $aBindVariables ) )
                    {
                        $sErrorMessage .= ' ' . json_encode( $aBindVariables );
                    }
                    throw new Exception( $sErrorMessage );
                }
                return true;
            }
            catch( Exception $oException )
            {
                $aError = oci_error();
                throw new Exception( htmlentities( $aError[ 'message' ], ENT_QUOTES ) );
            }
        }


        /**
         *
         * Runs a SQL query, and the results in an array. Only works with 'SELECT'
         *   statements.
         * @param string $sQuery           select statement to run
         * @param array  $aBindVariables   array of bind variables, key => value
         * @throws Exception
         * @return array
         *
         * Example:
         *   GetQueryResults( 'select * from `names` WHERE Name_First = :firstname',
         *                     array( 'firstname' => 'John' ) );
         *
         */
        public function GetQueryResults( $sQuery, array $aBindVariables = array() )
        {
            try
            {
                // cleanup the query
                $sQuery = $this->CleanQuery( $sQuery );

                // ensure we have a connection
                $this->GetConnection();

                // Make sure this is a select statement
                if( strtoupper( substr( $sQuery, 0, 6) ) != 'SELECT' )
                {
                    throw new Exception( 'GetQueryResults called without a SELECT statement.' );
                }

                $oOciStatement = oci_parse( $this->oConnection, $sQuery );

                if( $oOciStatement )
                {
                    $aResult = array();
                    // prepare the bind variables, if any
                    if( !empty( $aBindVariables ) )
                    {
                        foreach ( $aBindVariables as $sVariable => $vValue )
                        {
                            // For oci, we have to pass in the colon, so if it's not there, add it.
                            $sNewVariable = $sVariable;
                            if( substr( $sNewVariable, 0, 1 ) != ':' )
                            {
                                $sNewVariable = ':' . $sNewVariable;
                            }
                            // Bind the variable
                            if( !oci_bind_by_name( $oOciStatement, $sNewVariable, $aBindVariables[ $sVariable ] ) )
                            {
                                throw new Exception( 'Could not bind variable ' . $sNewVariable . ' to : ' . print_r( $vValue, true ) );
                            }
                        }
                    }
                    if( oci_execute( $oOciStatement, $this->iCommit ) === false )
                    {
                        $aOracleError = oci_error( $this->oConnection );
                        $sErrorMessage = $aOracleError[ 'message' ] . ' with query ' . $sQuery;
                        if( !empty ( $aBindVariables ) )
                        {
                            $sErrorMessage .= ' ' . json_encode( $aBindVariables );
                        }
                        throw new Exception( $sErrorMessage );
                    }
                    else /** Success! **/
                    {
                        $iRows = oci_fetch_all( $oOciStatement, $aResult, null, null, OCI_FETCHSTATEMENT_BY_ROW+OCI_RETURN_NULLS );
                        oci_free_statement( $oOciStatement );
                    }
                }
                else
                {
                    $aOracleError = oci_error( $this->oConnection );
                    throw new Exception( $aOracleError[ 'message' ] );
                }
                return $aResult;
            }
            catch( Exception $oException )
            {
                $aError = oci_error();
                throw cAnomaly::BubbleException( new Exception( htmlentities( $aError[ 'message' ], ENT_QUOTES ) ) );
            }
        }

        /**
         * Runs a SQL query, and the results in an array. Only works with 'SELECT'
         *   statements expecting one row.
         *
         * @param   string      $sQuery           select statement to run
         * @param   array       $aBindVariables   array of bind variables, key => value
         *
         * @throws  Exception
         *
         * @return  array
         *
         * Example:
         *   GetSingleQueryResults( 'select * from `names` WHERE Name_First = :firstname',
         *                           array( 'firstname' => 'John' ) );
         *
         */
        public function GetSingleQueryResults( $sQuery, array $aBindVariables = array() )
        {
            try
            {
                // get full results
                $aResults = $this->GetQueryResults( $sQuery, $aBindVariables );

                // check if something was returned
                if( !empty( $aResults ) )
                {
                    // there were results, return the first one
                    $aResults = $aResults[ 0 ];
                }

                return $aResults;
            }
            catch( Exception $oException )
            {
                $aError = oci_error();
                throw cAnomaly::BubbleException( new Exception( $oException->getMessage() . 'OCI Error: ' . htmlentities( $aError[ 'message' ], ENT_QUOTES ) ) );
            }
        }

        /**
         * Runs a SQL query, and returns the number of columns.
         *
         * @param   string      $sQuery           select statement to run
         * @param   array       $aBindVariables   array of bind variables, key => value
         *
         * @throws  Exception
         *
         * @return  integer
         *
         * Example:
         *   ReturnColCount( 'select * from `names` WHERE Name_First = :firstname',
         *                    array( 'firstname' => 'John' ) );
         *
         */
        public function ReturnColCount( $sQuery, array $aBindVariables = array() )
        {
            try
            {
                // cleanup the query
                $sQuery = $this->CleanQuery( $sQuery );

                // Make sure this is a select statement
                if( strtoupper( substr( $sQuery, 0, 6) ) != 'SELECT' )
                {
                    throw new Exception( 'GetQueryResults called without a SELECT statement.' );
                }

                // ensure we have a connection
                $this->GetConnection();

                $oOciStatement = oci_parse( $this->oConnection, $sQuery );

                if( $oOciStatement )
                {
                    $aResult = array();
                    // prepare the bind variables, if any
                    if( !empty( $aBindVariables ) )
                    {
                        foreach ( $aBindVariables as $sVariable => $vValue )
                        {
                            // For oci, we have to pass in the colon, so if it's not there, add it.
                            $sNewVariable = $sVariable;
                            if( substr( $sNewVariable, 0, 1 ) != ':' )
                            {
                                $sNewVariable = ':' . $sNewVariable;
                            }

                            // Bind the variable
                            if( !oci_bind_by_name( $oOciStatement, $sNewVariable, $aBindVariables[ $sVariable ] ) )
                            {
                                throw new Exception( 'Could not bind variable ' . $sNewVariable . ' to : ' . print_r( $vValue, true ) );
                            }
                        }
                    }
                    if( oci_execute( $oOciStatement, $this->iCommit ) === false )
                    {
                        $aOracleError = oci_error( $this->oConnection );
                        $sErrorMessage = $aOracleError[ 'message' ] . ' with query ' . $sQuery;
                        if( !empty ( $aBindVariables ) )
                        {
                            $sErrorMessage .= ' ' . json_encode( $aBindVariables );
                        }
                        throw new Exception( $sErrorMessage );
                    }
                    else /** Success! **/
                    {
                        $iRows = oci_fetch_all( $oOciStatement, $aResult, null, null, OCI_FETCHSTATEMENT_BY_ROW+OCI_RETURN_NULLS );
                        $iCols = count( $aResult [ 0 ] );
                        if( !oci_free_statement( $oOciStatement ) )
                        {
                            throw new Exception( 'Could not free statement.' );
                        }
                    }
                }
                else
                {
                    $aOracleError = oci_error( $this->oConnection );
                    throw new Exception( $aOracleError[ 'message' ] );
                }
                return $iCols;
            }
            catch( Exception $oException )
            {
                $aError = oci_error();
                throw cAnomaly::BubbleException( new Exception( htmlentities( $aError[ 'message' ], ENT_QUOTES ) ) );
            }
        }

        /**
         * Returns the number of rows affected by the statement given
         *
         * @param   string      $sQuery           SQL statement
         * @param   array       $aBindVariables   array of bind variables, key => value
         *
         * @throws  Exception
         *
         * @return  integer
         */
        public function ReturnRowCount( $sQuery = '', array $aBindVariables = array() )
        {
            try
            {
                // cleanup the query
                $sQuery = $this->CleanQuery( $sQuery );

                // ensure we have a connection
                $this->GetConnection();

                $oOciStatement = oci_parse( $this->oConnection, $sQuery );

                if( $oOciStatement )
                {
                    // prepare the bind variables, if any
                    if( !empty( $aBindVariables ) )
                    {
                        foreach ( $aBindVariables as $sVariable => $vValue )
                        {
                            // For oci, we have to pass in the colon, so if it's not there, add it.
                            $sNewVariable = $sVariable;
                            if( substr( $sNewVariable, 0, 1 ) != ':' )
                            {
                                $sNewVariable = ':' . $sNewVariable;
                            }

                            // Bind the variable
                            if( !oci_bind_by_name( $oOciStatement, $sNewVariable, $aBindVariables[ $sVariable ] ) )
                            {
                                throw new Exception( 'Could not bind variable ' . $sNewVariable . ' to : ' . print_r( $vValue, true ) );
                            }
                        }
                    }
                }
                else
                {
                    $aOracleError = oci_error( $this->oConnection );
                    throw new Exception( $aOracleError[ 'message' ] );
                }

                // try to execute the statement
                if( oci_execute( $oOciStatement, $this->iCommit ) === false )
                {
                    $aOracleError = oci_error( $this->oConnection );
                    $sErrorMessage = $aOracleError[ 'message' ] . ' with query ' . $sQuery;
                    if( !empty ( $aBindVariables ) )
                    {
                        $sErrorMessage .= ' ' . json_encode( $aBindVariables );
                    }
                    throw new Exception( $sErrorMessage );
                }
                $iRows = oci_num_rows( $oOciStatement );
                if( !oci_free_statement( $oOciStatement ) )
                {
                    throw new Exception( 'Could not free statement.' );
                }
                return $iRows;
            }
            catch( Exception $oException )
            {
                $aError = oci_error();
                throw cAnomaly::BubbleException( new Exception( htmlentities( $aError[ 'message' ], ENT_QUOTES ) ) );
            }
        }

        /**
         * Returns the auto incremented id from the last insert made
         *
         * @param string $sQuery           not used
         *
         * @throws Exception
         *
         * @return integer
         */
        public function GetLastSequenceId( $sQuery = '' )
        {
            throw cAnomaly::BubbleException( new Exception( 'GetLastSequenceId not enabled.' ) );
        }

        /**
         * Returns the next id in the sequence.
         *
         * @param   string      $sSequenceName      the name of the sequence
         *
         * @throws  Exception
         *
         * @return  integer
         */
        public function GetNextSequenceId( $sSequenceName = '' )
        {
            try
            {
                return $this->GetSingleQueryResults( 'SELECT ' . $sSequenceName . '.NEXTVAL FROM DUAL' );
            }
            catch( Exception $oException )
            {
                $aError = oci_error();
                throw cAnomaly::BubbleException( new Exception( htmlentities( $aError[ 'message' ], ENT_QUOTES ) ) );
            }
        }

        /**
         * RunProcedure would run a procedure than a query to get the output of the procedure
         *  ... if we had rights to actually write procedures, that is.
         *
         * @param string $sProcedure
         */
        public function RunProcedure( $sProcedure, array $aBindVariables = array() )
        {
            throw cAnomaly::BubbleException( new Exception( 'RunProcedure not enabled.' ) );
        }

        /**
         * Statements are automatically freed after every call.
         *
         * @param string $sQuery     not used
         *
         * @return boolean
         */
        public function FreeResults( $sQuery = '' )
        {
            try
            {

            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Starts a new transaction.
         *
         * @return boolean
         */
        public function StartTransaction()
        {
            // @todo: use flag to track whether or not to use OCI_NO_AUTO_COMMIT on execute statements
            if( $this->iCommit == OCI_COMMIT_ON_SUCCESS )
            {
                $this->iCommit = OCI_DEFAULT; // Should be OCI_NO_AUTO_COMMIT for PHP > 5.3.2
            }
            else
            {
                throw new Exception( 'Transaction already started.' );
            }
        }

        /**
         * Commits any changes made since a new transaction was started.
         *
         * @return boolean
         */
        public function Commit()
        {
            try
            {
                // ensure we have a connection
                $this->GetConnection();

                $this->iCommit = OCI_COMMIT_ON_SUCCESS;
                return oci_commit( $this->oConnection );
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Rolls back any changes made since a new transaction was started.
         *
         * @return boolean
         */
        public function Rollback()
        {
            try
            {
                // ensure we have a connection
                $this->GetConnection();

                $this->iCommit = OCI_COMMIT_ON_SUCCESS;
                return oci_rollback( $this->oConnection );
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Close the connection if it has been set.
         */
        public function __destruct()
        {
            if( !empty( $this->oConnection ) )
            {
                oci_close( $this->oConnection );
            }
        }
    }
?>