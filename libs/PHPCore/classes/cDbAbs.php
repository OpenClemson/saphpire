<?php
    // get the error handler
	require_once( sCORE_INC_PATH . '/classes/cAnomaly.php' );

	/**
	 * Class to instantiate the particular class for the specific database platform.
	 *
	 * See example below for implementation of the methods that instantiate objects of particular database platform
	 *     Example of usage of the static method GetDbObj
	 *     $oDb = cDbAbs::GetDbObj( $aDb, 'Oracle' );
	 *
	 * @author	   Team Rah
	 * @package    Core
	 * @subpackage Database
	 * @version    0.3.0
	 */
	class cDbAbs
	{
	    /**
	     * Database configuration array.
	     *
	     * @var array
	     */
	    protected $aDbConf = array();

		/**
	     * Returns a database object based upon the configuration information
	     * supplied in the constructor.
	     *
	     * @param   array       $aDbConf    Database configuration array
	     * @param   string      $sDbKey     Database configuration identifier
	     *
	     * @throws  Exception   Thrown if a connection could not be made.
	     *
	     * @return  object  	Returns the requested database object.
	     */
	    public static function GetDbObj( array $aDbConf, $sDbKey )
	    {
	        try
	        {
	        	// initialize the return value
	        	$oReturn = null;

		    	// if we don't have a config throw an exception
		        if( empty( $aDbConf ) )
		        {
		            throw new Exception( "The requested Database configuration (" . $sDbKey . ") does not exist" );
		        }

		        // set the list of adapters we support
		        $aAdapters = array( 'mysql', 'oracle' );

		        // cycle through the adapters to find the database requested
		        $iAdapters = count( $aAdapters );
		        for( $iAdapterCount = 0; $iAdapterCount < $iAdapters; ++$iAdapterCount )
		        {
		        	// check if this type of adapter exists
		        	if( isset( $aDbConf[ $aAdapters[ $iAdapterCount ] ] ) )
		        	{
		        		// try to find the database with the given label
		        		$iDatabases = count( $aDbConf[ $aAdapters[ $iAdapterCount ] ] );
		        		for( $iDatabaseCount = 0; $iDatabaseCount < $iDatabases; ++$iDatabaseCount )
		        		{
		        			// get a shorter version of the adapter to work with
		        			$aDb = $aDbConf[ $aAdapters[ $iAdapterCount ] ][ $iDatabaseCount ];

		        			// check if the label is the same
		        			if( isset( $aDb[ 'label' ] )
		        			    && is_string( $aDb[ 'label' ] )
		        			    && strtolower( $aDb[ 'label' ] ) == strtolower( $sDbKey ) )
		        			{
		        				// build the adapter with the settings provided
		        				switch( $aAdapters[ $iAdapterCount ] )
		        				{
					                case 'oracle':
					                    require_once( sCORE_INC_PATH . '/classes/cDbOracle.php' );

					                    // make an Oracle Database Object
					                    $oReturn = new cDbOracle( $aDb );
					                    break 3;

					                // connection to MySQL
					                case 'mysql':
					                    require_once( sCORE_INC_PATH . '/classes/cDbMySql.php' );

					                    // make a MySQL Database Object
					                    $oReturn = new cDbMySql( $aDb );
					                    break 3;
		        				}
		        			}
		        		}
		        	}
		        }

		        // if we don't have a config throw an exception
		        if( empty( $oReturn ) )
		        {
		            throw new Exception( "The requested Database configuration (" . $sDbKey . ") does not exist." );
		        }

				return $oReturn;
	        }
			catch( Exception $oException )
    		{
    			throw cAnomaly::BubbleException( $oException );
    		}
	    }
	}
?>