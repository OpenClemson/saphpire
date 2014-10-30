<?php
    /**
     * Business layer class for snapshot service.
     *
     * @author   Team Rah
     * @package  CORE
     * @category Services
     * @version  0.0.2.1
     */
    class cBusiness
    {
        /**
         * @var array   Data array
         */
        protected $aData = array();

        /**
         * @var string  HTTP Response code.
         */
        protected $sResponseCode = '';

        /**
         * @var string  Body of response
         */
        protected $sResponseBody = '';

        /**
         * @var array   Extra headers to pass thru the service response.
         */
        protected $aExtraHeaders = array();

        /**
         * @var array   Ignored files.
         */
        protected $aIgnoredFiles = array(
            'desktop.ini',
            'sftp-config.json',
            '.ds_store',
            '._.ds_store'
        );

        /**
         * @var array   Ignored folders.
         */
        protected $aIgnoredFolders = array(
            'logs',
            '_macosx',
            '.svn'
        );

        /**
         * @var string predetermined core hash
         */
        protected $sBaseHash = null;

        /**
         * constructor for snapshot business.
         */
        public function __construct ()
        {
            $sHashFile = sSERVICE_INC_PATH . DIRECTORY_SEPARATOR . 'snapshot' . DIRECTORY_SEPARATOR . 'core.hash';
            if ( file_exists( $sHashFile ) )
            {
                $this->sBaseHash = trim( file_get_contents( $sHashFile ) );
            }
        }

        /**
         * Checks request method. Only allow GET.
         *
         * @return  boolean
         */
        public function ValidateRequest ()
        {
            // check the request method.
            $bValidRequest = false;

            if ( $_SERVER[ 'REQUEST_METHOD' ] === 'GET' )
            {
                $bValidRequest = true;
            }

            return $bValidRequest;
        }

        /**
         * check for any immediate problems with the CORE Environment.
         *  - /libs folder is missing
         *  - /libs/PHPCore folder is missing
         *  - /logs folder is not writable.
         *  - /libs/PHPCore is not accessible ( can not execute list command )
         *  - Expected core folder is missing or not accessible:
         *      - classes
         *      - cli
         *      - docs
         *      - includes
         *      - js
         *      - templates
         *      - tests
         *
         * @return  boolean
         */
        public function CheckEnvironment ()
        {
            // initialize
            $bReturn = true;

            // check core exists
            $bReturn = $bReturn && @file_exists( sBASE_INC_PATH . DIRECTORY_SEPARATOR . 'libs' ) === TRUE;
            $bReturn = $bReturn && @file_exists( sBASE_INC_PATH . DIRECTORY_SEPARATOR . 'configs' ) === TRUE;
            $bReturn = $bReturn && @file_exists( sCORE_INC_PATH ) === TRUE;

            // check logs are writable.
            $bReturn = $bReturn && @is_writable( sBASE_INC_PATH . DIRECTORY_SEPARATOR . 'logs' ) === TRUE;

            // check core directory is executable (can list files/subfolders)
            $bReturn = $bReturn && @file_exists( sCORE_INC_PATH . DIRECTORY_SEPARATOR . '.' ) === TRUE;

            // check core directory has expected folder structure.
            $bReturn = $bReturn && @file_exists( sCORE_INC_PATH . DIRECTORY_SEPARATOR . 'classes' )   === TRUE;
            $bReturn = $bReturn && @file_exists( sCORE_INC_PATH . DIRECTORY_SEPARATOR . 'cli' )       === TRUE;
            $bReturn = $bReturn && @file_exists( sCORE_INC_PATH . DIRECTORY_SEPARATOR . 'docs' )      === TRUE;
            $bReturn = $bReturn && @file_exists( sCORE_INC_PATH . DIRECTORY_SEPARATOR . 'includes' )  === TRUE;
            $bReturn = $bReturn && @file_exists( sCORE_INC_PATH . DIRECTORY_SEPARATOR . 'js' )        === TRUE;
            $bReturn = $bReturn && @file_exists( sCORE_INC_PATH . DIRECTORY_SEPARATOR . 'templates' ) === TRUE;
            $bReturn = $bReturn && @file_exists( sCORE_INC_PATH . DIRECTORY_SEPARATOR . 'tests' )     === TRUE;

            return $bReturn;
        }

        /**
         * Gets all files and folders recursively.
         *
         * @param   string      $sDirPath   Path to directory.
         *
         * @return  array                   contains files/folders information.
         */
        public function GetDirectoryFiles ( $sDirPath )
        {
            // initialize
            $aFiles = array();

            if ( $rhFolder = opendir( $sDirPath ) )
            {
                // loop all files and subfolders recursively.
                while ( false !== ( $sPath = readdir( $rhFolder ) ) )
                {
                    $sFullPath = $sDirPath . DIRECTORY_SEPARATOR . $sPath;
                    if ( $sPath == "." || $sPath == ".." )
                    {
                        continue;
                    }

                    // get metadata.
                    $sPerms = substr( sprintf( '%o', fileperms( $sFullPath ) ), -4 );

                    $bIsDir = is_dir( $sFullPath );

                    // setup information array
                    $aFile = array();
                    $aFile[ 'path' ]    = $sFullPath;
                    $aFile[ 'perms' ]   = $sPerms;
                    $aFile[ 'name' ]    = basename( $sFullPath );
                    $aFile[ 'is_dir' ]  = $bIsDir;

                    // save for return.
                    $aFiles[] = $aFile;

                    if ( $bIsDir )
                    {
                        // get files in subfolder.
                        $aSubfiles = $this->GetDirectoryFiles( $sFullPath ) ;
                        $aFiles    = array_merge( $aFiles, $aSubfiles );
                    }
                }
                closedir($rhFolder);
            }

            return $aFiles;
        }

        /**
         * Gets all files from the core library.
         *
         * @return  array
         */
        public function GetCoreFiles ()
        {
            // get core files.
            $aCoreFiles = array();
            $aCoreFiles = $this->GetDirectoryFiles( sCORE_INC_PATH );

            return $aCoreFiles;
        }

        /**
         * Creates an MD5 hash of pertinent file information. For files,
         * it uses name and file content. For directories, it uses the name.
         *
         * @param   array       $aFile  file array
         *
         * @return  string
         */
        public function CreateFileHash ( array $aFile )
        {
            // initialize hash cipher as file / folder name.
            $sFileContent = $aFile[ 'name' ];

            if ( !$aFile[ 'is_dir' ] )
            {
                // get file contents
                $sContent = file_get_contents( $aFile[ 'path' ] );
                if ( $sContent === false )
                {
                    throw new Exception( 'Failed to read file contents to create hash: ' . print_r( $aFile, 1 ) );
                }

                // combine file name and contents.
                $sFileContent = $sFileContent . $sContent;
            }

            // create hash.
            return md5( $sFileContent );
        }

        /**
         * Creates a hash of filename and permission.
         *
         * @param   array       $aFile  File info.
         *
         * @return  string
         */
        public function CreatePermHash ( array $aFile )
        {
            // create hash.
            return md5( $aFile[ 'name' ] . $aFile[ 'perms' ] );
        }

        /**
         * Concatenate all file hashes into a single string and perform
         * an md5 on that string to give us our master hash for core.
         *
         * @param   array       $aFileHashes    array of md5 hashes
         *
         * @return  string
         */
        public function CreateMasterHash ( array $aFileHashes )
        {
            return md5( implode( '', $aFileHashes ) );
        }

        /**
         * Performs the snapshot logic. Validates Request, Checks the environment,
         * Creates hashes of all core files and combines them into a master hashsum.
         *
         * This hashsum is then returned to the consumer that called the service.
         *
         * @return  array
         */
        public function TakeSnapshot ()
        {
            // initialize
            $aReturn = array();

            // validate the request and check the environment first.
            $bValidRequest = $this->ValidateRequest( );
            $bCheckEnv     = $this->CheckEnvironment( );

            if ( $bValidRequest && $bCheckEnv )
            {
                // loop through /libs/PHPCore files
                $aFileHashes = array();
                $aFiles      = $this->GetCoreFiles();

                $iFileCount  = count( $aFiles );
                for ( $iIndex = 0; $iIndex < $iFileCount; $iIndex++ )
                {
                    // get file information and create hash.
                    $aFile     = $aFiles[ $iIndex ];
                    $sFileHash = $this->CreateFileHash( $aFile );
                    $sFilename = basename( $aFile[ 'path' ] );

                    // skip ignored files and folders.
                    if (   in_array( strtolower( $sFilename ), $this->aIgnoredFiles )
                        || in_array( strtolower( $sFilename ), $this->aIgnoredFolders )
                        )
                    {
                        unset( $aFiles[ $iIndex ] );
                        continue;
                    }

                    // save file hash.
                    $aFileHashes[ $sFilename ] = $sFileHash;
                }

                // reset keys
                $aFiles = array_values( $aFiles );

                // create master hash.
                $sMasterHash = $this->CreateMasterHash( $aFileHashes );

                // check for known hash.
                if (   !empty( $this->sBaseHash )
                    && $this->sBaseHash !== $sMasterHash
                    )
                {
                    // send alert
                    $this->sResponseCode = 'HTTP/1.1 207 Multi-Status';
                    $this->sResponseBody = array( $this->sBaseHash, $sMasterHash, 'Mismatch between expected core hash and latest snapshot.' );
                }
                else
                {
                    // set response.
                    $this->sResponseCode = 'HTTP/1.1 200 OK';
                    $this->sResponseBody = $sMasterHash;
                }
            }
            else if ( !$bValidRequest )
            {
                // send notification.
                $this->sResponseCode   = 'HTTP/1.1 405 Method Not Allowed';
                $this->aExtraHeaders[] = 'Allow: GET';
            }
            else if ( !$bCheckEnv )
            {
                // send notification.
                $this->sResponseCode = 'HTTP/1.1 417 Expectation Failed';
                $this->sResponseBody = 'Core does not exist, logs are not writable, core directory is not executable, or core directory is missing folders.';
            }


            // send the data back to the controller.
            $aReturn[ 'data' ]         = $this->aData;
            $aReturn[ 'code' ]         = $this->sResponseCode;
            $aReturn[ 'response' ]     = $this->sResponseBody;
            $aReturn[ 'extraheaders' ] = $this->aExtraHeaders;

            return $aReturn;
        }

        /**
         * Performs a check of all permissions on files and folder in the core directory.
         * Validates Request, Checks the environment.
         *
         * @return  array
         */
        public function CheckPermissions ()
        {

            // initialize
            $aReturn = array();

            // validate the request and check the environment first.
            $bValidRequest = $this->ValidateRequest( );
            $bCheckEnv     = $this->CheckEnvironment( );
            if ( $bValidRequest && $bCheckEnv )
            {
                // loop through /libs/PHPCore files
                $aFileHashes = array();
                $aFiles      = $this->GetCoreFiles();
                $iFileCount  = count( $aFiles );
                for ( $iIndex = 0; $iIndex < $iFileCount; $iIndex++ )
                {
                    // get file information and create hash.
                    $aFile     = $aFiles[ $iIndex ];
                    $sFileHash = $this->CreatePermHash( $aFile );
                    $sFilename = basename( $aFile[ 'path' ] );

                    // skip ignored files and folders.
                    if (   in_array( strtolower( $sFilename ), $this->aIgnoredFiles )
                        || in_array( strtolower( $sFilename ), $this->aIgnoredFolders )
                        )
                    {
                        unset( $aFiles[ $iIndex ] );
                        continue;
                    }

                    // save file hash.
                    $aFileHashes[ $sFilename ] = $sFileHash;
                }

                // reset keys
                $aFiles = array_values( $aFiles );

                // create master hash.
                $sMasterHash = $this->CreateMasterHash( $aFileHashes );

                // set response.
                $this->sResponseCode = 'HTTP/1.1 200 OK';
                $this->sResponseBody = $sMasterHash;
            }
            else if ( !$bValidRequest )
            {
                // send notification.
                $this->sResponseCode   = 'HTTP/1.1 405 Method Not Allowed';
                $this->aExtraHeaders[] = 'Allow: GET';
            }
            else if ( $bCheckEnv )
            {
                // send notification.
                $this->sResponseCode = 'HTTP/1.1 417 Expectation Failed';
                $this->sResponseBody = 'Core does not exist, logs are not writable, core directory is not executable, or core directory is missing folders.';
            }


            // send the data back to the controller.
            $aReturn[ 'data' ]         = $this->aData;
            $aReturn[ 'code' ]         = $this->sResponseCode;
            $aReturn[ 'response' ]     = $this->sResponseBody;
            $aReturn[ 'extraheaders' ] = $this->aExtraHeaders;

            return $aReturn;
        }
    }
?>