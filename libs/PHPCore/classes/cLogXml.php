<?php
    // get the base class
    require_once sCORE_INC_PATH . '/classes/cLogBase.php';

    /**
     * XML logging class.
     *
     * Logs the following information for all log types:
     *  - type ( error, exception, info, etc. )
     *  - message
     *  - current user
     *  - user's IP address ( or server IP if run from CLI )
     *  - time that this was called in sTIMESTAMP_FORMAT format as well as microseconds
     *  - location of call in one of the following formats:
     *    - file:line
     *    - class->method
     *    - class::method
     *
     * The output to XML log files is escaped with CDATA as necessary
     * so that the characters will not interfere with XML structure.
     *
     * @author    Team Rah
     * @package   Log
     * @version   1.5.5
     */
    class cLogXml extends cLogBase
    {
        /**
         * Directory to store log files. Relative to sBASE_INC_PATH.
         *
         * @var string
         */
        protected static $sLogDirectory = '';

        /**
         * XML handling object.
         *
         * @var DOMDocument
         */
        protected static $oDom;

        /**
         * Returns the directory that log files are saved to.
         *
         * @return string
         */
        public static function GetLogDirectory()
        {
            return self::$sLogDirectory;
        }

        /**
         * Sets the logs directory.
         *
         * @param  string  $sLogDirectory
         */
        public static function SetLogDirectory( $sLogDirectory )
        {
            // make sure the log directory is a string
            if( !is_string( $sLogDirectory ) )
            {
                throw new Exception( 'Log directory provided is not a string.' );
            }

            // make sure the log directory exists and is writable
            if( is_dir( $sLogDirectory ) )
            {
                if( is_writable( $sLogDirectory ) !== true )
                {
                    throw new Exception( 'Log directory provided is not writable. ' . $sLogDirectory );
                }
            }
            else
            {
                // check if the directory above is writable
                $bCouldNotCreate = false;
                if( strpos( $sLogDirectory, DIRECTORY_SEPARATOR ) !== false )
                {
                    $sAbove = substr( $sLogDirectory, 0, 1 + strrpos( $sLogDirectory, DIRECTORY_SEPARATOR ) );
                    if( is_writable( $sAbove ) !== true )
                    {
                        $bCouldNotCreate = true;
                    }
                }

                // try to make the directory if it's possible
                if( $bCouldNotCreate || !mkdir( $sLogDirectory ) )
                {
                    throw new Exception( 'Could not create log directory.' );
                }
            }

            // add the directory separator if needed
            if( substr( $sLogDirectory, -1 ) !== DIRECTORY_SEPARATOR )
            {
                $sLogDirectory .= DIRECTORY_SEPARATOR;
            }

            self::$sLogDirectory = $sLogDirectory;
        }

        /**
         * Writes log information into a log file.
         *
         * @param   string   $sFile
         * @param   object   $oNode
         * @param   string   $sMessage
         * @param   array    $aContext
         *
         * @return  boolean
         */
        protected static function WriteLog( $sFile, $oNode, $sMessage, $aContext )
        {
            // initialize return value
            $bReturn = false;

            try
            {
                // add the message node
                $oNode->appendChild( self::$oDom->createElement( 'message', $sMessage ) );

                // loop through our data elements
                foreach( $aContext as $sNode => $vNodeData )
                {
                    // Check if we need to add a cdata wrapper.
                    if( is_string( $vNodeData ) )
                    {
                        if( strpos( $vNodeData, '&' ) !== false
                            || strpos( $vNodeData, '<' ) !== false
                            || strpos( $vNodeData, '>' ) !== false )
                        {
                            // insert a CDATA node
                            $oCDATA = $oNode->appendChild( self::$oDom->createElement( $sNode ) );
                            $oCDATA->appendChild( self::$oDom->createCDATASection( $vNodeData ) );
                        }
                        else
                        {
                            // add a regular data node
                            $oNode->appendChild( self::$oDom->createElement( $sNode, $vNodeData  ) );
                        }
                    }
                    else
                    {
                        // format for xml
                        $vNodeData = print_r( $vNodeData, true );

                        // insert a CDATA node
                        $oCDATA = $oNode->appendChild( self::$oDom->createElement( $sNode ) );
                        $oCDATA->appendChild( self::$oDom->createCDATASection( $vNodeData ) );
                    }
                }

                // try to save the file
                $bReturn = file_put_contents( $sFile, self::$oDom->saveXML(), LOCK_EX ) !== false;
            }
            catch( Exception $oException )
            {
                // we can't log this, so we should send an email here
            }

            return $bReturn;
        }

        /**
         * Logs the given message and extra data with the supplied type.
         *
         * @param  string  $sType     Type of log.
         * @param  string  $sMessage  Message to log.
         * @param  array   $aContext  Any additional data to save with the log entry.
         *
         * @return boolean
         */
        public static function Log( $sType, $sMessage, array $aContext = array() )
        {
            // initialize the return value
            $bReturn = false;

            // make sure the type and message are provided correctly
            if( is_string( $sType )
                && is_string( $sMessage )
                && !empty( $sType )
                && !empty( $sMessage ) )
            {
                // normalize the type
                $sType = self::NormalizeType( $sType );

                // check if the log directory is writable
                if( is_dir( self::$sLogDirectory )
                    && is_writable( self::$sLogDirectory ) )
                {
                    // get the missing data
                    $aContext = self::GetMissingContext( $aContext );

                    // set the file to write to
                    $sFile = self::$sLogDirectory . $sType . '.xml';

                    // setup DOMDocument for handling xml
                    self::$oDom = new DOMDocument( '1.0', 'UTF-8' );
                    self::$oDom->preserveWhiteSpace = false;
                    self::$oDom->formatOutput       = true;
                    libxml_use_internal_errors( true );

                    // create root level 'log' node and append a 'lognode' entry
                    $oLog = self::$oDom->createElement( 'log' );
                    self::$oDom->appendChild( $oLog );
                    $oNode = self::$oDom->createElement( 'lognode' );
                    $oLog->appendChild( $oNode );

                    // check if the file exists
                    if( file_exists( $sFile ) )
                    {
                        // check if the file is writable
                        if( is_writable( $sFile ) )
                        {
                            // attempt to load log file into DOM
                            self::$oDom->load( $sFile );

                            // if no errors occurred we have to append to the existing log entries
                            if( libxml_get_last_error() === false )
                            {
                                $oLog  = self::$oDom->documentElement;
                                $oNode = self::$oDom->createElement( 'lognode' );
                                $oLog->appendChild( $oNode );

                                // try to write the log
                                $bReturn = self::WriteLog( $sFile, $oNode, $sMessage, $aContext );
                            }
                            else
                            {
                                // get the contents from the malformed file and strip tags
                                $sMalformatData = file_get_contents( $sFile );
                                $sMalformatData = trim( strip_tags( $sMalformatData ) );

                                // append to the malformed file and remove the malformed log file contents
                                $sMalformatFile = self::GetLogDirectory() . 'malformat.log';
                                file_put_contents( $sMalformatFile, "\n" . $sMalformatData, FILE_APPEND|LOCK_EX );
                                unlink( $sFile );

                                // setup DOMDocument for handling xml
                                self::$oDom = new DOMDocument( '1.0', 'UTF-8' );
                                self::$oDom->preserveWhiteSpace = false;
                                self::$oDom->formatOutput       = true;
                                libxml_use_internal_errors( true );

                                // create root level 'log' node and append a 'lognode' entry
                                $oLog = self::$oDom->createElement( 'log' );
                                self::$oDom->appendChild( $oLog );
                                $oNode = self::$oDom->createElement( 'lognode' );
                                $oLog->appendChild( $oNode );

                                // try to write the log
                                $bReturn = self::WriteLog( $sFile, $oNode, $sMessage, $aContext );
                            }
                        }
                    }
                    else
                    {
                        // try to write the new log
                        $bReturn = self::WriteLog( $sFile, $oNode, $sMessage, $aContext );
                    }
                }
            }

            return $bReturn;
        }

        /**
         * Normalizes a log type into a string without an extension.
         *
         * @param   string  $sType
         *
         * @return  string
         */
        protected static function NormalizeType( $sType )
        {
            // convert to lowercase
            $sType = strtolower( $sType );

            // drop the extension if it was provided
            if( substr( $sType, -4 ) == '.xml' )
            {
                $sType = substr( $sType, 0, -4 );
            }

            return $sType;
        }

        /**
         * Returns an array of all log files in the log directory.
         *
         * @return  array  $aFiles  Array of log files within /logs directory.
         */
        public static function GetLogTypes()
        {
            // initialize return value
            $aLogTypes = array();

            // only search for log types if possible
            $sPath = self::GetLogDirectory();
            if( file_exists( $sPath )
                && is_dir( $sPath )
                && is_readable( $sPath ) )
            {
                // get the path and remove linux defaults
                $aLogTypes = array_values( array_diff( scandir( $sPath ), array( '..', '.' ) ) );

                // loop through and ensure we don't return directories
                // or files that were not created by this class
                $iCountLogFiles = count( $aLogTypes );
                for( $iLogCounter = 0; $iLogCounter < $iCountLogFiles; ++$iLogCounter )
                {
                    // remove directory if needed
                    if( is_dir( $sPath . $aLogTypes[ $iLogCounter ] ) )
                    {
                        unset( $aLogTypes[ $iLogCounter ] );
                    }
                    else
                    {
                        // normalize the type
                        $sLogType = self::NormalizeType( $aLogTypes[ $iLogCounter ] );

                        // get at most one entry
                        $aContents = self::GetLogContents( $sLogType, 1 );

                        // only add the type if the log is not empty
                        if( !empty( $aContents ) )
                        {
                            $aLogTypes[ $iLogCounter ] = $sLogType;
                        }
                        else
                        {
                            unset( $aLogTypes[ $iLogCounter ] );
                        }
                    }
                }

                // reset the array indexes
                $aLogTypes = array_values( $aLogTypes );
                sort( $aLogTypes );
            }

            return $aLogTypes;
        }

        /**
         * Retrieves the contents from the log of the given type.
         *
         * @param  string      $sType    Type of log.
         * @param  null | int  $vAmount  Amount of entries to retrieve.
         *                               If null, all entries are retrieved.
         *
         * @return array       Example:
         *                         array(
         *                             array(
         *                                 'message'      => 'Message that was logged.',
         *                                 'location'     => '/path/to/file:12' || 'cClassName->method',
         *                                 'date'         => '2020-12-20 12:12:12',
         *                                 'user'         => 'username',
         *                                 'user_ip'      => '127.0.0.1',
         *                                 'trace'        => '',
         *                                 'microseconds' => '123456789'
         *                             )
         *                         )
         */
        public static function GetLogContents( $sType, $vAmount = null )
        {
            // initialize the return value
            $aContents = array();

            // check the type provided exists
            if( is_string( $sType ) && !empty( $sType ) )
            {
                // normalize the type
                $sType = self::NormalizeType( $sType );

                // get the path to the file
                $sPath = self::GetLogDirectory() . $sType . '.xml';

                // check if the file can be read
                if( file_exists( $sPath ) && is_readable( $sPath ) )
                {
                    // setup a dom document
                    self::$oDom                     = new DOMDocument( '1.0', 'UTF-8' );
                    self::$oDom->preserveWhiteSpace = false;
                    self::$oDom->formatOutput       = true;
                    libxml_use_internal_errors( true );

                    // load the contents
                    self::$oDom->load( $sPath );

                    // check if there was an error
                    $oError = libxml_get_last_error();
                    if( $oError === false )
                    {
                        // build the return
                        $iEntries = 0;
                        $oNodes = self::$oDom->getElementsByTagName( 'lognode' );
                        $iLogCount = is_int( $vAmount ) ? $vAmount : $oNodes->length;
                        foreach( $oNodes as $oNode )
                        {
                            // make sure we can continue
                            if( is_int( $vAmount ) && $iEntries == $iLogCount )
                            {
                                break;
                            }
                            ++$iEntries;

                            // default the essential information
                            $aTempContents = array(
                                'message'      => '',
                                'location'     => '',
                                'user'         => '',
                                'user_ip'      => '',
                                'date'         => '',
                                'microseconds' => ''
                            );

                            // set flag for whether or not to add the log
                            $bAdd = false;

                            // add all nodes with no children
                            foreach( $oNode->childNodes as $oKid )
                            {
                                if( $oKid->childNodes->length <= 1 )
                                {
                                    $aTempContents[ $oKid->nodeName ] = $oKid->nodeValue;
                                    $bAdd = true;
                                }
                            }

                            // add to the contents
                            if( $bAdd )
                            {
                                $aContents[] = $aTempContents;
                            }
                        }
                    }
                }
            }

            return $aContents;
        }

        /**
         * Removes all log entries for the given log type.
         *
         * @param    string    $sType
         *
         * @return   boolean
         */
        public static function Clear( $sType )
        {
            // initialize the return value
            $bReturn = false;

            // normalize the type
            $sType = self::NormalizeType( $sType );

            // get the path to the file to clear
            $sPath = self::GetLogDirectory() . $sType . '.xml';

            // check if the file exists and is writable
            if( file_exists( $sPath ) && is_writable( $sPath ) )
            {
                $bReturn = file_put_contents( $sPath, '<?xml version="1.0" encoding="UTF-8"?><log></log>', LOCK_EX ) !== false;
            }

            return $bReturn;
        }

        /**
         * Cleans the log of the given type by removing
         * all entries older than the given timestamp.
         *
         * @param   int     $iTimestamp   Newest date that entries can have.
         *
         * @return  boolean
         */
        public static function ClearEntriesBefore( $sType, $iTimestamp )
        {
            // initialize return value
            $bSuccess = false;

            // normalize the type
            $sType = self::NormalizeType( $sType );

            // initialize the list of entries to save
            $aNewLogEntries = array();

            // get the log contents
            $aLogContents = self::GetLogContents( $sType );

            // only try to clear if there's something in the logs
            if( !empty( $aLogContents ) )
            {
                // cycle through and save the ones that are newer than the timestamp
                $iEntryCount = count( $aLogContents );
                for( $i = 0; $i < $iEntryCount; ++$i )
                {
                    if( $iTimestamp < intval( $aLogContents[ $i ][ 'microseconds' ] ) )
                    {
                        $aNewLogEntries[] = $aLogContents[ $i ];
                    }
                }

                // delete the file if it exists
                $bSuccess = self::Clear( $sType );

                // add new entries to log
                if( $bSuccess && !empty( $aNewLogEntries ) )
                {
                    // write all the logs
                    $iNewCount = count( $aNewLogEntries );
                    for( $i = 0; $i < $iNewCount; ++$i )
                    {
                        $sMessage = $aNewLogEntries[ $i ][ 'message' ];
                        unset( $aNewLogEntries[ $i ][ 'message' ] );
                        $bSuccess = self::Log( $sType, $sMessage, $aNewLogEntries[ $i ] );

                        if( !$bSuccess )
                        {
                            break;
                        }
                    }
                }
            }

            return $bSuccess;
        }

        /**
         * Retrieves stats for the type provided.
         *
         * @param   string   $sType
         *
         * @return  array
         */
        public static function GetStatsForType( $sType )
        {
            // initialize the return value
            $aStats = array();
            $aStats[ 'entries' ]        = 0;
            $aStats[ 'last_entry' ]     = '';
            $aStats[ 'estimated_size' ] = 0;
            $sLogFile = self::GetLogDirectory() . self::NormalizeType( $sType ) . '.xml';

            // get the contents of the log
            $aContents = self::GetLogContents( $sType );

            // only continue if there are entries
            if( !empty( $aContents ) )
            {
                // count the entries
                $aStats[ 'entries' ] = count( $aContents );

                // get the last modified time
                $iLastEntryTime = 0;
                $iIndex = 0;
                $iEntryCount = count( $aContents );
                for( $i = 0; $i < $iEntryCount; ++$i )
                {
                    if( $aContents[ $i ][ 'microseconds' ] > $iLastEntryTime )
                    {
                        $iLastEntryTime = $aContents[ $i ][ 'microseconds' ];
                        $iIndex = $i;
                    }
                }
                $aStats[ 'last_entry' ] = $aContents[ $iIndex ][ 'date' ];
            }

            // try to get local file size if we can.
            if( file_exists( $sLogFile ) )
            {
                $aStats[ 'estimated_size' ] = filesize( $sLogFile );
            }

            return $aStats;
        }
    }
?>