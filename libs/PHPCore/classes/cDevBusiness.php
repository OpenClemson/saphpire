<?php
    require_once( sCORE_INC_PATH . '/classes/cBusBase.php' );
    require_once( sCORE_INC_PATH . '/classes/cLogManager.php' );
    require_once( sCORE_INC_PATH . '/classes/cBaseConfig.php' );
    require_once( sCORE_INC_PATH . '/classes/cFileUtilities.php' );
    require_once( sCORE_INC_PATH . '/classes/cFormUtilities.php' );
    require_once( sCORE_INC_PATH . '/classes/cDbAbs.php' );

    /**
     * Business functionality for developers.
     *
     * @author      Team Rah
     * @package     Core
     * @subpackage  Dev
     * @version     0.2.0
     */
    class cDevBusiness extends cBusBase
    {
        /**
         * property for cFormUtilities
         *
         * @var object
         */
        private $oForm  = null;

        /**
         * class constructor
         *
         * @throws Exception if errors
         *
         * @return void
         */
        public function __construct()
        {
            try
            {
                // instance of form utils
                $this->oForm = new cFormUtilities();
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Gets the types of logs for all sources and individual sources.
         *
         * @return array
         */
        public function GetLogTypes()
        {
            // initialize the log types
            $aLogTypes = array();

            // get all the loggers that have been registered
            $aLoggers = cLogManager::GetLoggers();
            foreach( $aLoggers as $sLabel => $sLogger )
            {
                // get types for this logger
                $aLogTypesForLogger = cLogManager::GetLogTypesForLogger( $sLabel );

                // add the log types for this logger
                $iTypeCount = count( $aLogTypesForLogger );
                for( $i = 0; $i < $iTypeCount;  ++$i )
                {
                    $aLogTypes[ $sLabel . ' - ' . $aLogTypesForLogger[ $i ] ] = $sLabel . ' - ' . ucfirst( $aLogTypesForLogger[ $i ] );
                }
            }

            // if there's more than one type, add options for all logs
            if( count( $aLoggers ) > 1 )
            {
                $aAllLogTypes = cLogManager::GetLogTypes();
                $iTypeCount   = count( $aAllLogTypes );
                for( $i = 0; $i < $iTypeCount;  ++$i )
                {
                    $aLogTypes[ 'All - ' . $aAllLogTypes[ $i ] ] = 'All - ' . ucfirst( $aAllLogTypes[ $i ] );
                }
                asort( $aLogTypes );
            }

            return $aLogTypes;
        }

        public function HandleLogForm()
        {
            try
            {
                // initialize the page data to return
                $aLog = array();
                $aLog[ 'types' ]   = $this->GetLogTypes();
                $aLog[ 'source' ]  = '';
                $aLog[ 'entries' ] = 50;
                $aLog[ 'stats' ]   = array();
                $aLog[ 'contents' ] = array();

                // only continue if log files exist
                if ( !empty( $aLog[ 'types' ] ) )
                {
                    $aLog[ 'source' ] = !empty( $aLog[ 'types' ] ) ? key( $aLog[ 'types' ] ) : '';

                    // Check if the form has been submitted.
                    if ( $this->oForm->IsFormSubmitted( 'clearLogBefore' ) )
                    {
                        // get the source
                        $aData = $this->oForm->GetCleanFormData();
                        if( isset( $aData[ 'source' ] )
                            && isset( $aLog[ 'types' ][ $aData[ 'source' ] ] ) )
                        {
                            $aLog[ 'source' ] = $aData[ 'source' ];
                        }

                        // set number and length appropriately
                        $iNumber = 0;
                        if( isset( $aData[ 'number' ] )
                            && is_numeric( $aData[ 'number' ] ) )
                        {
                            $iNumber = $aData[ 'number' ];
                        }

                        $iLength = 0;
                        if( isset( $aData[ 'length' ] )
                            && is_numeric( $aData[ 'length' ] ) )
                        {
                            $iLength = $aData[ 'length' ];
                        }

                        // split the source and get the timestamp
                        list( $sSource, $sType ) = explode( ' - ', $aLog[ 'source' ] );
                        $iDays      = $iNumber * $iLength;
                        $sTime      = 'now - ' . $iDays . ' day' . ( $iDays == 1 ? '' : 's' );
                        $iTimestamp = strtotime( $sTime);

                        // don't want to delete everything.
                        if( $iTimestamp !== false )
                        {
                            // clear from an individual logger or all the loggers
                            if( $sSource == 'All' )
                            {
                                cLogManager::ClearEntriesBefore( $sType, $iTimestamp );
                            }
                            else
                            {
                                cLogManager::ClearEntriesBeforeForLogger( $sSource, $sType, $iTimestamp );
                            }
                        }

                        // reset the log types
                        $aLog[ 'types' ] = $this->GetLogTypes();
                    }
                    else if ( $this->oForm->IsFormSubmitted( 'clearLog' ) )
                    {
                        // get the source
                        $aData = $this->oForm->GetCleanFormData();
                        $aLog[ 'source' ] = $aData[ 'source' ];

                        // split the source and type
                        list( $sSource, $sType ) = explode( ' - ', $aLog[ 'source' ] );

                        // clear from an individual logger or all the loggers
                        if( $sSource == 'All' )
                        {
                            cLogManager::Clear( $sType );
                        }
                        else
                        {
                            cLogManager::ClearForLogger( $sSource, $sType );
                        }

                        // reset the log types
                        $aLog[ 'types' ] = $this->GetLogTypes();
                    }
                    else
                    {
                        // get the source
                        $aData = $this->oForm->GetCleanFormData();
                        if( isset( $aData[ 'source' ] ) )
                        {
                            $aLog[ 'source' ] = $aData[ 'source' ];
                        }
                    }

                    // save the number of entries
                    if( isset( $aData ) && isset( $aData[ 'entries' ] ) )
                    {
                        if( is_numeric( $aLog[ 'entries' ] ) )
                        {
                            $aLog[ 'entries' ] = intval( $aData[ 'entries' ] );
                        }
                    }

                    // set the source if it hasn't been selected
                    if( !isset( $aLog[ 'types' ][ $aLog[ 'source' ] ] )
                        || ( empty( $aLog[ 'source' ] )
                            && !empty( $aLog[ 'types' ] ) ) )
                    {
                        $aLog[ 'source' ] = key( $aLog[ 'types' ] );
                    }

                    // if a source exists, get its contents
                    if( !empty( $aLog[ 'source' ] ) )
                    {
                        list( $sSource, $sType ) = explode( ' - ', $aLog[ 'source' ] );
                        if( $sSource == 'All' )
                        {
                            $aLog[ 'contents' ] = cLogManager::GetLogContents( $sType, $aLog[ 'entries' ] );
                            $aLog[ 'stats' ]    = cLogManager::GetStatsForType( $sType );
                        }
                        else
                        {
                            $aLog[ 'contents' ] = cLogManager::GetLogContentsForLogger( $sSource, $sType, $aLog[ 'entries' ] );
                            $aLog[ 'stats' ]    = cLogManager::GetStatsForTypeForLogger( $sSource, $sType );
                        }
                    }

                    // push the download if needed
                    if( $this->oForm->IsFormSubmitted( 'downloadLog' ) && !empty( $aLog[ 'contents' ] ) )
                    {
                        $aLog[ 'contents' ] = $this->DownloadLog( $aLog[ 'source' ], $aLog[ 'contents' ] );
                    }
                }

                return $aLog;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        public function HandleConfigForm()
        {
            try
            {
                $aConfig = array();

                // Initialize the contents to return.
                $oConfig       = new cBaseConfig();
                $aConfigFiles  = $oConfig->GetConfigFiles();
                $sFile         = !empty( $aConfigFiles ) ? $aConfigFiles[ 0 ] : '';
                $sContents     = '';
                $sLastModified = '';
                $sFileSize     = '';
                $bSuccess      = true;
                $bWritable     = false;
                $sError        = '';

                // Check if the form has been submitted.
                if ( $this->oForm->IsFormSubmitted( 'submitConfig' ) )
                {
                    // get the file
                    $aData = $this->oForm->GetFormData();
                    if( isset( $aData[ 'configFile' ] )
                        && is_string( $aData[ 'configFile' ] )
                        && in_array( $aData[ 'configFile' ], $aConfigFiles ) )
                    {
                        $sFile = $aData[ 'configFile' ];
                    }
                }
                else if ( $this->oForm->IsFormSubmitted( 'saveConfig' ) )
                {
                    // Get the form data.
                    $aData = $this->oForm->GetFormData();

                    // get the selected file
                    if( isset( $aData[ 'file' ] )
                        && is_string( $aData[ 'file' ] )
                        && in_array( $aData[ 'file' ], $aConfigFiles ) )
                    {
                        $sFile = $aData[ 'file' ];
                    }

                    // get the new file contents
                    $sNewData = '';
                    if( isset( $aData[ 'configContents' ] )
                        && is_string( $aData[ 'configContents' ] ) )
                    {
                        $sNewData = trim( $aData[ 'configContents' ] );
                    }

                    // create a dom object
                    $oDom = new DOMDocument( '1.0', 'UTF-8' );
                    $oDom->preserveWhiteSpace = true;
                    $oDom->formatOutput       = true;
                    libxml_use_internal_errors( true );

                    // load the xml
                    $bLoaded = $oDom->loadXML( $sNewData );

                    // validate against XSD if it exists
                    $bValid    = true;
                    $aPathInfo = pathinfo( $sFile );
                    $sFileName = sCORE_INC_PATH . '/configs/' . strtolower( $aPathInfo[ 'filename' ] ) . '.xsd';
                    if( file_exists( $sFileName )
                        && is_readable( $sFileName ) )
                    {
                        $bValid = $oDom->schemaValidate( $sFileName );
                    }

                    // check if it was loaded correctly
                    if( $bValid
                        && libxml_get_last_error() === false )
                    {
                        // set absolute file path
                        $sPath = sBASE_INC_PATH . '/configs/' . $sFile;
                        if ( @file_put_contents( $sPath, trim( $oDom->saveXML() ), LOCK_EX ) === false )
                        {
                            $sError = 'Config file is not writable: ' . $sFile;
                            $bSuccess = false;
                        }
                    }
                    else
                    {
                        $sError = 'Config file does not pass XSD validation: ' . sCORE_INC_PATH . '/configs/' . strtolower( $aPathInfo[ 'filename' ] ) . '.xsd';
                        $bSuccess = false;
                    }
                }

                if ( !empty( $sFile ) )
                {
                    // set absolute file path.
                    $sPath = sBASE_INC_PATH . '/configs/' . $sFile;

                    // Get updated file contents and set the return values.
                    $sEnv      = php_uname();
                    $sContents = $oConfig->GetConfigContents( $sFile );
                    $aConfig[ 'owner' ] = '';
                    $aConfig[ 'plain' ] = empty( $aData[ 'plain' ] ) ? '' : 'yes';
                    $aConfig[ 'write' ] = substr( sprintf( '%o', fileperms( $sPath ) ), -4 );

                    // check if we're on Unix or have posix installed
                    if ( function_exists( 'posix_getpwuid' ) )
                    {
                        $sTmpOwner          = posix_getpwuid( fileowner( $sPath) );
                        $aConfig[ 'owner' ] = $sTmpOwner[ 'name' ];

                        // determine the group for Linux systems
                        $iGroup             = filegroup( $sPath );
                        $aGroup             = posix_getgrgid( $iGroup );
                        $aConfig[ 'group' ] = $aGroup[ 'name' ];
                    }
                    // check if we're on Windows
                    else if ( strpos( $sEnv, 'Windows' ) !== false )
                    {
                        $aConfig[ 'owner' ] = getenv( 'USERNAME' );
                        $aConfig[ 'group' ] = 'n/a';
                    }

                    $sLastModified = date( sTIMESTAMP_FORMAT, filemtime( $sPath ) );
                    $sFileSize     = filesize( $sPath );
                    $bWritable     = ( is_writable( $sPath ) ? true : false );
                }

                $aConfig[ 'file' ]     = $sFile;
                $aConfig[ 'writable' ] = $bWritable;
                $aConfig[ 'contents' ] = $sContents;
                $aConfig[ 'files' ]    = $aConfigFiles;
                $aConfig[ 'mod' ]      = $sLastModified;
                $aConfig[ 'size' ]     = $sFileSize;
                $aConfig[ 'success' ]  = $bSuccess;
                $aConfig[ 'error' ]    = $sError;

                return $aConfig;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Reads package and version information for the the core files.
         *
         * Reads anything in the classes or includes as well as config, Error, and DevConsole.
         * Update if any new files are created outside of the classes or includes directories.
         *
         * @return  string  Hashed version of core files.
         */
        public function GetBeacon()
        {
            try
            {
                // get all the core files
                $aBeaconFiles = array_merge(
                    cFileUtilities::GetDirectoryContents( sCORE_INC_PATH . DIRECTORY_SEPARATOR . 'classes', array( 'include_path', 'files_only' ) ),
                    cFileUtilities::GetDirectoryContents( sCORE_INC_PATH . DIRECTORY_SEPARATOR . 'includes', array( 'include_path', 'files_only' ) ),
                    array(
                        sBASE_INC_PATH . DIRECTORY_SEPARATOR . 'config.php'
                    )
                );

                // initialize  beacon hash
                $sBeaconHash = '';

                // pull out package and version information in the files
                $iBeaconCount = count( $aBeaconFiles );
                for( $i = 0; $i < $iBeaconCount; ++$i )
                {
                    // get the file contents
                    $sContents = file_get_contents( $aBeaconFiles[ $i ] );

                    // add the file name to the hash
                    $sBeaconHash .= basename( $aBeaconFiles[ $i ] );

                    // look for package information
                    preg_match( "/@package[[:blank:]]+[a-zA-Z_0-9]+/", $sContents, $aMatches );
                    if( isset( $aMatches[ 0 ] ) )
                    {
                        // add package information to the hash
                        $sBeaconHash .= preg_replace( "/@package[[:blank:]]+/", '', $aMatches[ 0 ] );
                    }

                    // look for version information
                    preg_match( "/@version[[:blank:]]+[0-9]+\.[0-9]+/", $sContents, $aMatches );
                    if( isset( $aMatches[ 0 ] ) )
                    {
                        // add version information to the hash
                        $sBeaconHash .= preg_replace( "/@version[[:blank:]]+/", '', $aMatches[ 0 ] );
                    }
                }

                return md5( $sBeaconHash );
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Talks to the core beacon service to get the newest beacon hash.
         *
         * Update when service is created.
         *
         * @return string
         */
        public function GetCoreBeacon()
        {
            // @todo: update this to talk to core beacon service
            return false;
        }

        function CheckPort( $sServer, $iPort, $iTimeout = 5 )
        {
            // open socket to port...
            $bPass = false;
            if ( $sServer && $iPort && $iTimeout )
            {
                if ( @fsockopen("$sServer", $iPort, $iErrno, $sErrstr, $iTimeout) )
                {
                    $bPass = true;
                }
            }

            return $bPass;
        }

        public function GetDatabaseConnections()
        {
            try
            {
                // Initialize list of databases, ports and get config
                $aConnStatus = array();
                $aPorts      = array();

                // Check if we can connect to database before proceeding.
                if ( class_exists( 'cDbAbs' ) )
                {
                    // Check if db file exists.
                    $oConfig = new cBaseConfig();
                    if ( file_exists( $oConfig->GetConfigDirectory() . DIRECTORY_SEPARATOR . 'db.xml' ) )
                    {
                        // Read contents of files into an array and check for environment.
                        $aDbConfigs = $oConfig->Read( 'db.xml' );

                        // initialize the return value
                        $aDatabases = array();

                        // If comments are found remove them to avoid crashing.
                        if ( isset( $aDbConfigs[ 'comment' ] ) )
                        {
                            unset( $aDbConfigs[ 'comment' ] );
                        }

                        // get all databases
                        if( isset( $aDbConfigs[ 'instance' ] )
                            && is_array( $aDbConfigs[ 'instance' ] )
                            && !empty( $aDbConfigs[ 'instance' ] ) )
                        {

                            // correct the structure if needed
                            if( !isset( $aDbConfigs[ 'instance' ][ 0 ] ) )
                            {
                                $aDbConfigs[ 'instance' ] = array( $aDbConfigs[ 'instance' ] );
                            }

                            // cycle through the instances
                            $iInstances = count( $aDbConfigs[ 'instance' ] );
                            for( $iInstanceCounter = 0; $iInstanceCounter < $iInstances; ++$iInstanceCounter )
                            {
                                // check if this instance has an environment
                                if( isset( $aDbConfigs[ 'instance' ][ $iInstanceCounter ][ 'env' ] )
                                    && is_string( $aDbConfigs[ 'instance' ][ $iInstanceCounter ][ 'env' ] ) )
                                {
                                    // get the environment
                                    $sEnv = $aDbConfigs[ 'instance' ][ $iInstanceCounter ][ 'env' ];
                                    if ( !empty( $sEnv ) )
                                    {
                                        // initialize the database structures
                                        $aDatabases[ $sEnv ][ 'mysql' ]  = array();
                                        $aDatabases[ $sEnv ][ 'oracle' ] = array();

                                        // check if there are any MySQL connections
                                        if( isset( $aDbConfigs[ 'instance' ][ $iInstanceCounter ][ 'mysql' ] ) )
                                        {
                                            // save the connections
                                            $aDatabases[ $sEnv ][ 'mysql' ]  = $aDbConfigs[ 'instance' ][ $iInstanceCounter ][ 'mysql' ];

                                            // correct structure if needed
                                            if( !isset( $aDatabases[ $sEnv ][ 'mysql' ][ 0 ] ) )
                                            {
                                                $aDatabases[ $sEnv ][ 'mysql' ] = array( $aDatabases[ $sEnv ][ 'mysql' ] );
                                            }
                                        }

                                        // check if there are any Oracle connections
                                        if( isset( $aDbConfigs[ 'instance' ][ $iInstanceCounter ][ 'oracle' ] ) )
                                        {
                                            // save the connections
                                            $aDatabases[ $sEnv ][ 'oracle' ] = $aDbConfigs[ 'instance' ][ $iInstanceCounter ][ 'oracle' ];

                                            // correct structure if needed
                                            if( !isset( $aDatabases[ $sEnv ][ 'oracle' ][ 0 ] ) )
                                            {
                                                $aDatabases[ $sEnv ][ 'oracle' ] = array( $aDatabases[ $sEnv ][ 'oracle' ] );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            $aDatabases = array();
                        }

                        // check if the environment is set
                        if( defined( 'sAPPLICATION_ENV' )
                            && isset( $aDatabases[ sAPPLICATION_ENV ] ) )
                        {
                            $aDatabases = $aDatabases[ sAPPLICATION_ENV ];
                        }

                        foreach( $aDatabases as $sAdapter => $aConnections )
                        {
                            if( !empty( $aConnections ) )
                            {
                                foreach( $aConnections as $aOptions )
                                {
                                    if ( isset( $aOptions[ 'comment' ] ) )
                                    {
                                        continue;
                                    }
                                    if ( empty( $aOptions[ 'label' ] )
                                        || empty( $aOptions[ 'username' ] )
                                        || empty( $aOptions[ 'host' ] )
                                        || empty( $aOptions[ 'database' ] ) )
                                    {
                                        continue;
                                    }
                                    $sDataBase = $aOptions[ 'label' ];
                                    $aConnStatus[ $sDataBase ]             = array();
                                    $aConnStatus[ $sDataBase ][ 'errors' ] = '';

                                    // Make sure everything is formatted correctly.
                                    foreach ( $aOptions as $sKey => $vValue )
                                    {
                                        if ( is_array( $vValue ) )
                                        {
                                            if ( empty( $vValue ) )
                                            {
                                                $aOptions[ $sKey ] = '';
                                            }
                                            else if ( count( $vValue ) == 1 )
                                            {
                                                $aOptions[ $sKey ] = $vValue[ key( $vValue ) ];
                                            }
                                            else
                                            {
                                                // If multiple values found set the key to the first one.
                                                $aOptions[ $sKey ] = $vValue[ 0 ];
                                                $aConnStatus[ $sDataBase ][ 'errors' ] .=
                                                    'Too may values for option "' . $sKey . '" in db.xml' . "\n";
                                            }
                                        }
                                    }
                                    // Initialize db connection data and add port if set.
                                    $aConnStatus[ $sDataBase ][ 'adapter' ] = $sAdapter;
                                    $aConnStatus[ $sDataBase ][ 'host'    ] = $aOptions[ 'host' ];
                                    $aConnStatus[ $sDataBase ][ 'port'    ] = 'unknown adapters';
                                    if ( isset( $aOptions[ 'port' ] ) )
                                    {
                                        $aConnStatus[ $sDataBase ][ 'port' ] = $aOptions[ 'port' ];
                                    }
                                    else if ( strtolower( $sAdapter ) == 'oracle' )
                                    {
                                        $aConnStatus[ $sDataBase ][ 'port' ] = 1521;
                                    }
                                    else if ( strtolower( $sAdapter ) == 'mysql' )
                                    {
                                        $aConnStatus[ $sDataBase ][ 'port' ] = 3306;
                                    }
                                    // Check if this is an oracle connection and the oci8 extension is loaded.
                                    if ( strtolower( $sAdapter ) == 'oracle' && !extension_loaded( 'oci8' ) )
                                    {
                                        $aConnStatus[ $sDataBase ][ 'connection' ]      = 'oci8 extension is not loaded.';
                                        $aConnStatus[ $sDataBase ][ 'port_connection' ] = false;
                                        $aConnStatus[ $sDataBase ][ 'port' ]            = false;
                                        continue;
                                    }
                                    try
                                    {
                                        // Try to make a connection and if made set flags.
                                        $oObj = @cDbAbs::GetDbObj( $aDatabases, $sDataBase );

                                        // check connection
                                        $bConnection = $oObj->GetConnection();

                                        // check port
                                        $rhSocket  = @fsockopen( $sHost, $iPort, $iErrno, $sErrstr, $iTimeout );
                                        $bPortOpen = $rhSocket !== null;

                                        $aConnStatus[ $sDataBase ][ 'port_connection' ] = $bPortOpen;
                                        $aConnStatus[ $sDataBase ][ 'connection' ]      = ( $bConnection != false );
                                    }
                                    catch( Exception $oException )
                                    {
                                        // Degrade gracefully and show error messages on db fail.
                                        $aConnStatus[ $sDataBase ][ 'connection' ]      = $oException->GetMessage();
                                        $aConnStatus[ $sDataBase ][ 'port_connection' ] = false;
                                        $aConnStatus[ $sDataBase ][ 'port' ]            = false;
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    // Gracefully handle user feedback.
                    $aConnStatus[ 'error' ] = 'cDbAbs class does not exist.';
                }

                return $aConnStatus;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>