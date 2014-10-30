<?php
	require_once( sCORE_INC_PATH . '/classes/cPresBase.php' );

	/**
	 * Presentation functionality for developers.
	 *
	 * @package Core
	 * @version 0.1
	 */
    class cDevPresentation extends cPresBase
    {
    	/**
    	 * XML Utilities object.
    	 *
    	 * @var cXmlUtilities
    	 */
    	protected $oXmlUtilities = null;

    	/**
		 * Create an instance of this object and set the XML utilities.
		 */
		public function __construct()
		{
			try
			{
				parent::__construct();

            	// create an instance of the xml utilities
				$this->oXmlUtilities = new cXmlUtilities();
			}
			catch( Exception $oException )
    		{
    			throw BubbleException( $oException );
    		}
		}

    	// @TODO: refactor, clean up, comment
    	public function GetLogViewPage( $sFile = null, $aContents = null, $aLogFiles = array() )
    	{
    		try {
		    	// generate form if log files have been created
	        	if( !empty( $aLogFiles ) )
	        	{
		        	// initialize  log file data
		        	$aLogFileOptions = array(
		        		'template' => 'option.html'
		        	);

		        	// create options for each of the log files
		        	$bFirstSet = false;
		        	$iLogCount = count( $aLogFiles );
		        	foreach( $aLogFiles as $sKey => $sLogSource )
		        	{
		        		$sAttrs = '';
		        		if( isset( $sFile ) && $sFile === $sKey || ( !isset( $sFile ) && !$bFirstSet ) )
		        		{
		        			$sAttrs = 'selected="selected"';
		        			$bFirstSet = true;
		        		}
		        		$aLogFileOptions[] = array(
		        			'_:_VALUE_:_' => $sKey,
		        			'_:_LABEL_:_' => $sLogSource,
		        			'_:_ATTRS_:_' => $sAttrs
		        		);
		        	}

		        	// create the form for the log files
		        	$aFormData = array(
		        		'OPTIONS' => $aLogFileOptions
		        	);

		        	$sLogFileForm = $this->GetForm( 'form-logs.html', $aFormData, array() );

	        		$sTable = $sLogFileForm . '
	        			<table>
	        				<thead>
	        					<th>Message</th>
	        					<th>Location</th>
	        					<th>User</th>
	        					<th>User IP</th>
	        					<th>Date</th>
	        				</thead>
	        				<tbody>
	        					_:_ROWS_:_
	        				</tbody>
	        			</table>
	        		';
	        		$sRow = '
	        			<tr>
	        				<td>_:_MESSAGE_:_</td>
	        				<td>_:_LOCATION_:_</td>
	        				<td>_:_USER_:_</td>
	        				<td>_:_IP_:_</td>
	        				<td>_:_DATE_:_</td>
	        			</tr>
	        		';
	        		$aRow = array( 'template' => $sRow );
	        		foreach( $aContents as $aEntry )
	        		{
	        			$aRow[] = array(
							'_:_MESSAGE_:_'  => $aEntry[ 'message' ],
							'_:_LOCATION_:_' => $aEntry[ 'location' ],
							'_:_USER_:_'     => $aEntry[ 'user' ],
							'_:_IP_:_'       => $aEntry[ 'user_ip' ],
							'_:_DATE_:_'     => $aEntry[ 'date' ],
	        			);
	        		}
	        		$aTemplate = array( 'template' => $sTable, '_:_ROWS_:_' => $aRow );
	        		$sBody = $this->PopulateTemplate( $aTemplate );
	        	}
	        	else
	        	{
	        		$sBody = 'No log files to read.';
	        	}

				return $sBody;
    		}
    		catch( Exception $oException )
    		{
    			throw BubbleException( $oException );
    		}
        }

    	public function GetConfigViewPage( $sFile = null, $sContents = null, $aConfigFiles = array(), $sError = '' )
    	{
    		try {
		    	// generate form if config files have been created
	        	if( !empty( $aConfigFiles ) )
	        	{
		        	// initialize  log file data
		        	$aConfigFileOptions = array(
		        		'template' => 'option.html'
		        	);

		        	// create options for each of the log files
		        	$bFirstSet = false;
		        	$iConfigCount = count( $aConfigFiles );
		        	for( $i = 0; $i < $iConfigCount; ++$i )
		        	{
						$sAttrs = '';
		        		if( isset( $sFile ) && $sFile === $aConfigFiles[ $i ] || ( !isset( $sFile ) && !$bFirstSet ) )
		        		{
		        			$sAttrs = 'selected="selected"';
		        			$bFirstSet = true;
		        			$sFile = $aConfigFiles[ $i ];
		        		}
		        		$aConfigFileOptions[] = array(
		        			'_:_VALUE_:_' => $aConfigFiles[ $i ],
		        			'_:_LABEL_:_' => $aConfigFiles[ $i ],
		        			'_:_ATTRS_:_' => $sAttrs
		        		);
		        	}

		        	// create the form for the log files
		        	$aFormData = array(
		        		'OPTIONS' => $aConfigFileOptions,
		        	);

		        	if( $sFile === '' )
		        	{
		        		$aFormData[ 'CONFIG-CONTENTS' ] = '<div style="padding-left: 10px;">Config file is empty.</div>';
		        	}
		        	else
		        	{
		        		$aFormData[ 'CONFIG-CONTENTS' ]  = empty( $sError ) ? '' : '
		        			<div style="color: red">' . $sError . '<br/></div>
		        		';
		        		$aFormData[ 'CONFIG-CONTENTS' ] .= '<div style="padding-top: 10px;"><textarea id="configContents" name="configContents" style="width:99%; height:500px">' . htmlentities( $sContents ) . '</textarea></div>';
		        	}

		        	$sBody = $this->GetForm( 'form-configs.html', $aFormData, array() );
	        	}
	        	else
	        	{
	        		$sBody = 'No config files to read.';
	        	}

				return $sBody;
    		}
    		catch( Exception $oException )
    		{
    			throw BubbleException( $oException );
    		}
        }

        public function FormatXmlTraceNode( $aNode )
        {
      	  try {
				// initialize  the table
				$sReturn = '<table style="width: 100%; border-top: 1px solid black"><tbody>';

				// create header row
				$aHeaders = array();
				$aHeaderData = array();

				// add file information
				$aHeaders[] = 'File';
				$aHeaderData[] = $aNode[ 'file' ];

				// check for class information
				if( isset( $aNode[ 'class' ] ) )
				{
					// add class information
					$aHeaders[] = 'Class';
					$aHeaderData[] = $aNode[ 'class' ];
				}

				// check for function information
				if( isset( $aNode[ 'function' ] ) )
				{
					// add function information
					$aHeaders[] = 'Function';
					$aHeaderData[] = $aNode[ 'function' ];
				}

				// add line information
				$aHeaders[] = 'Line';
				$aHeaderData[] = $aNode[ 'line' ];

				// count how many headers there are
				$iHeaderCount = count( $aHeaders );

				// add the header row
				$sReturn .= '<tr>';
				for( $i = 0; $i < $iHeaderCount; ++$i )
				{
					if( $i != 0 )
					{
						$sReturn .= '<td style="border-left: 1px solid lightgrey; font-weight: bold;">' . $aHeaders[ $i ] . '</td>';
					}
					else
					{
						$sReturn .= '<td style="font-weight: bold;">' . $aHeaders[ $i ] . '</td>';
					}
				}
				$sReturn .= '</tr>';

				// add the values for the header row
				$sReturn .= '<tr>';
				for( $i = 0; $i < $iHeaderCount; ++$i )
				{
					if( $i != 0 )
					{
						$sReturn .= '<td style="border-top: 1px solid lightgrey; border-left: 1px solid lightgrey;">' . $aHeaderData[ $i ] . '</td>';
					}
					else
					{
						$sReturn .= '<td style="border-top: 1px solid lightgrey;">' . $aHeaderData[ $i ] . '</td>';
					}
				}
				$sReturn .= '</tr>';

				// create row for args
				if( isset( $aNode[ 'args' ] ) )
				{
					// add the header
					$sReturn .= '<tr><td style="border-top: 1px solid lightgrey; font-weight: bold;" colspan="' . $iHeaderCount . '">Function Arguments:</td></tr>';

					// output the arguments
					if( is_array( $aNode[ 'args' ][ 'arg' ] ) )
					{
						// if there's more than one, output all of them
						$iArgCount = count( $aNode[ 'args' ][ 'arg' ] );
						for( $i = 0; $i < $iArgCount; ++$i )
						{
							if( $aNode[ 'args' ][ 'arg' ][ $i ] == array() )
							{
								$aNode[ 'args' ][ 'arg' ][ $i ] = '';
							}
							$sReturn .= '<tr><td style="border-top: 1px solid lightgrey; border-left: 1px solid lightgrey;" colspan="' . $iHeaderCount . '"><pre>' . htmlspecialchars( $aNode[ 'args' ][ 'arg' ][ $i ] ) . '</pre></td></tr>';
						}
					}
					else
					{
						$sReturn .= '<tr><td style="border-top: 1px solid lightgrey; border-left: 1px solid lightgrey;" colspan="' . $iHeaderCount . '"><pre>' . htmlspecialchars( $aNode[ 'args' ][ 'arg' ] ) . '</pre></td></tr>';
					}
				}

				// close the body and table tags
				$sReturn .= '</tbody></table>';

				return $sReturn;
			}
			catch( Exception $oException )
    		{
    			throw BubbleException( $oException );
    		}
        }

        // @TODO: refactor, template, comment
        public function FormatExceptionLogContents( $sContents, $bShowFirst = false )
        {
        	try
        	{
	    		// initialize  the string to return
	    		$sReturn = '';

	    		// build the contents
	    		if( !empty( $sContents ) )
	    		{
	    			// convert the xml to the an array
	    			$aContents = $this->oXmlUtilities->ToArray( $sContents );

	    			if( isset( $aContents[ 'exception' ] ) )
	    			{
		    			// check if there are multiple nodes
		    			if( isset( $aContents[ 'exception' ][ 0 ] ) )
		    			{
		    				// reverse log nodes so we see the newest ones first
		    				$aContents[ 'exception' ] = array_reverse( $aContents[ 'exception' ] );

		    				//$aContents = $aContents[ 'exception' ][ key( $aContents[ 'exception' ] ) ];
		    				$iExceptionCount = count( $aContents[ 'exception' ] );

			    			if( $bShowFirst && $iExceptionCount > 1 )
		    				{
		    					$aContents[ 'exception' ] = array( $aContents[ 'exception' ][ 0 ] );
		    					$iExceptionCount = count( $aContents[ 'exception' ] );
		    				}

		    				for( $i = 0; $i < $iExceptionCount; ++$i)
		    				{
			    				// save the user and date
			    				$sUser    = ( isset( $aContents[ 'exception' ][ $i ][ 'user' ] ) && is_string( $aContents[ 'exception' ][ $i ][ 'user' ] ) ) ? $aContents[ 'exception' ][ $i ][ 'user' ] : '';
			    				$sDate    = $aContents[ 'exception' ][ $i ][ 'date' ];

			    				$sReturn .= '<div><table>';

			    				// add headings for user and date
			    				$sReturn .= '<tr>';
			    				$sReturn .= "<td style=\"font-weight:bold; border-bottom: 1px solid lightgrey; \">User</td>";
			    				$sReturn .= "<td style=\"font-weight:bold; border-bottom: 1px solid lightgrey; \">Date</td>";
			    				$sReturn .= '</tr>';

			    				// add user and date info
			    				$sReturn .= '<tr>';
			    				$sReturn .= "<td>$sUser</td>";
			    				$sReturn .= "<td style=\"border-left: 1px solid lightgrey;\">$sDate</td>";
			    				$sReturn .= '</tr>';

			    				// add an empty row for spacing
			    				$sReturn .= '<tr><td></td></tr>';

			    				$sReturn .= '<tr>';
			    				$sReturn .= "<td style=\"font-weight:bold; \">Error Code</td>";
			    				$sReturn .= "<td style=\"font-weight:bold; border-left: 1px solid lightgrey;\">Message</td>";
			    				$sReturn .= '</tr>';

			    				// check if there are any codes and messages to be displayed
			    				if( !empty( $aContents[ 'exception' ][ $i ][ 'codes-and-messages' ] ) )
			    				{
			    					if( isset( $aContents[ 'exception' ][ $i ][ 'codes-and-messages' ][ 'code-and-message' ][ 0 ] ) )
			    					{
			    						$iMessageCount = count( $aContents[ 'exception' ][ $i ][ 'codes-and-messages' ][ 'code-and-message' ] );
					    				for( $j = 0; $j < $iMessageCount; ++$j )
					    				{
					    					$vCode = $aContents[ 'exception' ][ $i ][ 'codes-and-messages' ][ 'code-and-message' ][ $j ][ 'code' ];
					    					$sMessage = $aContents[ 'exception' ][ $i ][ 'codes-and-messages' ][ 'code-and-message' ][ $j ][ 'message' ];
					    					$sReturn .= '<tr>';
						    				$sReturn .= '<td style="border-top: 1px solid lightgrey;">' . $vCode . '</td>';
						    				$sReturn .= '<td style="border-top: 1px solid lightgrey; border-left: 1px solid lightgrey;">' . $sMessage . '</td>';
						    				$sReturn .= '</tr>';
			    						}
			    					}
				    				else
				    				{
				    					// @TODO: this isn't being saved correctly
				    					$vCode = $aContents[ 'exception' ][ $i ][ 'codes-and-messages' ][ 'code-and-message' ][ 'code' ];
				    					$sMessage = $aContents[ 'exception' ][ $i ][ 'codes-and-messages' ][ 'code-and-message' ][ 'message' ];
				    					$sReturn .= '<tr>';
					    				$sReturn .= '<td style="border-top: 1px solid lightgrey;">' . $vCode . '</td>';
					    				$sReturn .= '<td style="border-top: 1px solid lightgrey; border-left: 1px solid lightgrey;">' . $sMessage . '</td>';
					    				$sReturn .= '</tr>';
				    				}
			    				}

			    				// add an empty row for spacing
			    				$sReturn .= '<tr><td></td></tr>';

			    				$sReturn .= '</table>';

			    				// format each tracenode
				    			if( isset( $aContents[ 'exception' ][ $i ][ 'tracenode' ][0] ) )
				    			{
				    				$iNodeCount = count( $aContents[ 'exception' ][ $i ][ 'tracenode' ] );
					    			$aContents[ 'exception' ][ $i ][ 'tracenode' ] = array_reverse( $aContents[ 'exception' ][ $i ][ 'tracenode' ] );

				    				for( $j = 0; $j < $iNodeCount; ++$j )
				    				{
				    					$sReturn .= $this->FormatXmlTraceNode( $aContents[ 'exception' ][ $i ][ 'tracenode' ][ $j ] );
			    					}
				    			}
				    			else
				    			{
				    				$sReturn .= $this->FormatXmlTraceNode( $aContents[ 'exception' ][ $i ][ 'tracenode' ] );
				    			}
				    			$sReturn .= '</div>';
		    				}
		    			}
		    			else
		    			{
		    				// save the user and date
		    				$aContents = $aContents[ 'exception' ];
		    				$sUser    = ( isset( $aContents[ 'user' ] ) && is_string( $aContents[ 'user' ] ) ) ? $aContents[ 'user' ] : '';
		    				$sDate    = $aContents[ 'date' ];

		    				$sReturn .= '<div><table>';

		    				// add headings for user and date
		    				$sReturn .= '<tr>';
		    				$sReturn .= "<td style=\"font-weight:bold; border-bottom: 1px solid lightgrey; \">User</td>";
		    				$sReturn .= "<td style=\"font-weight:bold; border-bottom: 1px solid lightgrey; \">Date</td>";
		    				$sReturn .= '</tr>';

		    				// add user and date info
		    				$sReturn .= '<tr>';
		    				$sReturn .= "<td>$sUser</td>";
		    				$sReturn .= "<td style=\"border-left: 1px solid lightgrey;\">$sDate</td>";
		    				$sReturn .= '</tr>';

		    				// add an empty row for spacing
		    				$sReturn .= '<tr><td></td></tr>';

		    				$sReturn .= '<tr>';
		    				$sReturn .= "<td style=\"font-weight:bold; \">Error Code</td>";
		    				$sReturn .= "<td style=\"font-weight:bold; border-left: 1px solid lightgrey;\">Message</td>";
		    				$sReturn .= '</tr>';

		    				// check if there are any codes and messages to be displayed
		    				if( !empty( $aContents[ 'codes-and-messages' ] ) )
		    				{
		    					if( isset( $aContents[ 'codes-and-messages' ][ 'code-and-message' ][ 0 ] ) )
		    					{
		    						$iMessageCount = count( $aContents[ 'codes-and-messages' ][ 'code-and-message' ] );
				    				for( $j = 0; $j < $iMessageCount; ++$j )
				    				{
				    					$vCode = $aContents[ 'codes-and-messages' ][ 'code-and-message' ][ $j ][ 'code' ];
				    					$sMessage = $aContents[ 'codes-and-messages' ][ 'code-and-message' ][ $j ][ 'message' ];
				    					$sReturn .= '<tr>';
					    				$sReturn .= '<td style="border-top: 1px solid lightgrey;">' . $vCode . '</td>';
					    				$sReturn .= '<td style="border-top: 1px solid lightgrey; border-left: 1px solid lightgrey;">' . $sMessage . '</td>';
					    				$sReturn .= '</tr>';
		    						}
		    					}
			    				else
			    				{
			    					$vCode = $aContents[ 'codes-and-messages' ][ 'code-and-message' ][ 'code' ];
			    					$sMessage = $aContents[ 'codes-and-messages' ][ 'code-and-message' ][ 'message' ];
			    					$sReturn .= '<tr>';
				    				$sReturn .= '<td style="border-top: 1px solid lightgrey;">' . $vCode . '</td>';
				    				$sReturn .= '<td style="border-top: 1px solid lightgrey; border-left: 1px solid lightgrey;">' . $sMessage . '</td>';
				    				$sReturn .= '</tr>';
			    				}
		    				}

		    				// add an empty row for spacing
		    				$sReturn .= '<tr><td></td></tr>';

		    				$sReturn .= '</table>';

		    				// format each tracenode
			    			if( isset( $aContents[ 'tracenode' ][0] ) )
			    			{
			    				$iNodeCount = count( $aContents[ 'tracenode' ] );
				    			$aContents[ 'tracenode' ] = array_reverse( $aContents[ 'tracenode' ] );

			    				for( $j = 0; $j < $iNodeCount; ++$j )
			    				{
			    					$sReturn .= $this->FormatXmlTraceNode( $aContents[ 'tracenode' ][ $j ] );
		    					}
			    			}
			    			else
			    			{
			    				$sReturn .= $this->FormatXmlTraceNode( $aContents[ 'tracenode' ] );
			    			}
			    			$sReturn .= '</div>';
		    			}
	    			}
	    		}

	    		return $sReturn;
    		}
    		catch( Exception $oException )
    		{
    			throw BubbleException( $oException );
    		}
        }

    	// @TODO: refactor, clean up, comment
    	public function GetExceptionLogPage( $bShowFirst = false )
    	{
    		try {
				// get exception log contents
				$sContents = cLogger::GetLogContents( 'exception.xml' );

		        // get the exception contents
		        //'<h1>A wild Exception appears!</h1>' .
		        return $this->FormatExceptionLogContents( $sContents, $bShowFirst );
    		}
    		catch( Exception $oException )
    		{
    			throw BubbleException( $oException );
    		}
        }

        public function GetPulsePage( $sBeaconMessage, array $aDbConnections )
        {
        	// initialize  database connection information
        	$vDbData = '';
        	if( !empty( $aDbConnections ) )
        	{
        		// set database connection template data
	        	$vDbData = array();
	        	$vDbData[ 'template' ]       = 'db-connections.html';
	        	$vDbData[ '_:_DB-CONNS_:_' ] = array();
	        	$vDbData[ '_:_DB-CONNS_:_' ][ 'template' ] = 'db-connection.html';

	        	// add each database to the list of connections
	        	foreach( $aDbConnections as $sDb => $aDbData )
	        	{
	        		$vDbData[ '_:_DB-CONNS_:_' ][] = array(
	        			'_:_DB_:_'        => $sDb,
	        			'_:_ADAPTER_:_'   => $aDbData[ 'adapter' ],
	        			'_:_HOST_:_'      => $aDbData[ 'host' ],
	        			'_:_PORT_:_'      => $aDbData[ 'port' ],
	        			'_:_CONN_:_'      => ( $aDbData[ 'connection' ] === true ) ? 'Can Connect' : $aDbData[ 'connection' ],
	        			'_:_PORT-CONN_:_' => $aDbData[ 'port_connection' ] ? 'Open' : 'Closed'
	        		);
	        	}
	        }

	        // build pulse template data
        	$aPulse = array();
        	$aPulse[ 'template' ]       = 'pulse.html';
        	$aPulse[ '_:_BEACON_:_' ]   = $sBeaconMessage;
        	$aPulse[ '_:_DB-CONNS_:_' ] = $vDbData;

        	return $aPulse;
        }

        /**
         * Builds the dev console page.
         *
         * @param  array   $aLogInfo
         * @param  array   $aConfigInfo
         * @param  string  $sPHPInfo
         * @param  string  $sBeacon
         * @param  string  $sCoreBeacon
         * @param  array   $aDbConnections
         *
         * @return string HTML
         */
        public function GetDevConsolePage( $aLogInfo, $aConfigInfo, $sPHPInfo, $sBeacon, $sCoreBeacon, $aDbConnections )
        {
        	try
        	{
        		// split out log data
				$sLogFile     = $aLogInfo[ 'source' ];
				$aLogContents = $aLogInfo[ 'contents' ];
				$aLogFiles    = $aLogInfo[ 'types' ];

				// split out config data
				$sConfigFile     = $aConfigInfo[ 'file' ];
				$sConfigContents = $aConfigInfo[ 'contents' ];
				$aConfigFiles    = $aConfigInfo[ 'files' ];
				$sConfigError    = $aConfigInfo[ 'error' ];

				// get log view page contents
        		$sLogContent = $this->GetLogViewPage( $sLogFile, $aLogContents, $aLogFiles );

        		// get config veiw page contents
				$sConfigContent = $this->GetConfigViewPage( $sConfigFile, $sConfigContents, $aConfigFiles, $sConfigError );

				// cleanup the phpinfo output
			    $sPHPInfo = preg_replace ( '/<\/div><\/body><\/html>/', '', $sPHPInfo );
			    $sPHPInfo = preg_replace('~<(?:!DOCTYPE|/?(?:html|body|head|meta))[^>]*>\s*~i', '', $sPHPInfo );

			    // set the beacon message
			    $sBeaconMessage = 'Core up to date? <span style="color:red">No <a href="brokenlink">Get newest core here.</a></span>';
			    if( !$sCoreBeacon )
			    {
			    	$sBeaconMessage = 'Core up to date? <span style="color:red">Error: Could not connect to Core Validation Service.</span>';
			    }
			    elseif( $sBeacon == $sCoreBeacon )
			    {
			    	$sBeaconMessage = 'Core up to date?: <span style="color:green">Yes</span>';
			    }

			    // put it all together
		        $aConsole = array(
		        	'template'      => 'dev-console.html',
			        '_:_CONFIGS_:_' => $sConfigContent,
		        	'_:_LOGS_:_'    => $sLogContent,
		        	'_:_PHPINFO_:_' => $sPHPInfo,
		        	'_:_PULSE_:_'   => $this->GetPulsePage( $sBeaconMessage, $aDbConnections )
		        );
		        return $this->PopulateTemplate( $aConsole );
    		}
    		catch( Exception $oException )
    		{
    			throw BubbleException( $oException );
    		}
        }
    }
?>