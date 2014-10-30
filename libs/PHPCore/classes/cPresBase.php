<?php
    // get the template engine
    require_once( sCORE_INC_PATH . '/classes/cTemplate.php' );

    // get the error handling functionality
    require_once( sCORE_INC_PATH . '/classes/cAnomaly.php' );

    /**
     * Base presentation class for all applications.
     *
     * Contains functionality that most if not all
     * presentation layer subclasses will use.
     *
     * @uses       cTemplate
     * @uses       cAnomaly
     * @author     Team Rah
     * @package    Core
     * @subpackage Presentation
     * @version    0.5.1
     */
    class cPresBase
    {
        /**
         * Template engine object.
         *
         * @var cTemplate
         */
        private $oTemplateEngine;

        /**
         * Creates an instance of the template engine and populates
         * the paths the the engine will use with parameters supplied.
         *
         * If no paths are sent in, the base and core paths are provided.
         */
        public function __construct()
        {
            try
            {
                // create instance of template engine
                $this->oTemplateEngine = new cTemplate();

                // get the arguments for this function
                $aArgs = func_get_args();

                // set the default paths
                $sBasePath = sBASE_INC_PATH . DIRECTORY_SEPARATOR . 'templates';
                $sCorePath = sCORE_INC_PATH . DIRECTORY_SEPARATOR . 'templates';

                // ensure that there's always access to the base and core templates
                if( !in_array( $sBasePath, $aArgs ) )
                {
                    $aArgs[] = $sBasePath;
                }
                if( !in_array( $sCorePath, $aArgs ) )
                {
                    $aArgs[] = $sCorePath;
                }

                // add the directories to the template engine
                $this->oTemplateEngine->SetTemplateDirectories( $aArgs );
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Wrapper for the template engine's SetTemplateDirectories.
         * Allows overriding template directories at any time.
         *
         * @param  array  $aPaths  Array of directory paths.
         *
         * @return $this
         */
        public function SetTemplateDirectories( $aPaths )
        {
            try
            {
                // add the directories to the template engine
                $this->oTemplateEngine->SetTemplateDirectories( $aPaths );

                return $this;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Wrapper for cTemplate::replace().
         *
         * @uses    cTemplate::replace
         *
         * @param   array   $aArgs
         *
         * @return  string  Template populated with values.
         */
        public function PopulateTemplate( array $aArgs )
        {
            try
            {
                // make replacements
                return $this->oTemplateEngine->Replace( $aArgs );
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Builds all the options for a select box and
         * sets the selected value(s) appropriately.
         *
         * @param   array                   $aOptions   All possible options.
         * @param   array | string | int    $vSelected  Currently selected option(s).
         *
         * @return  string                  HTML generated list of options.
         */
        public function BuildOptions( array $aOptions = array(), $vSelected = null )
        {
            // initialize return string
            $sOptions = '';

            // check if options were provided
            if( !empty( $aOptions ) )
            {
                // set option template information
                $aOptionsData = array();
                $aOptionsData[ 'template' ] = 'option.html';

                // build options
                $bSelectedSet = false;
                foreach( $aOptions as $sValue => $sLabel )
                {
                    // check if this should be selected
                    $sSelected = '';

                    // if nothing was sent in, select the first option
                    // if a value was sent in and the current value matches, select it
                    // if multiple values were sent in and the current value
                    //    is in the list of values, select it
                    if( ( $vSelected === null && !$bSelectedSet )
                        || $vSelected == $sValue
                        || ( is_array( $vSelected )
                             && in_array( $sValue, $vSelected ) ) )
                    {
                        $sSelected    = ' selected="selected"';
                        $bSelectedSet = true;
                    }

                    // add option to template data
                    $aOptionsData[] = array(
                        '_:_VALUE_:_' => $sValue,
                        '_:_ATTRS_:_' => $sSelected,
                        '_:_LABEL_:_' => $sLabel
                    );
                }

                // populate the templates
                $sOptions = $this->PopulateTemplate( $aOptionsData );
            }

            return $sOptions;
        }

        /**
         * Converts array of errors for elements into HTML.
         *
         * Array will be in the form:
         *     array(
         *         'element-name' => array(
         *             'humanized' => <readable version of name>,
         *             'errors'    => array(
         *                 0 => 'Error message',
         *                 1 => 'Error message',
         *                 etc.
         *             )
         *         )
         *     )
         *
         * @param  array $aErrors
         *
         * @return string
         */
        public function BuildElementErrors( array $aErrors )
        {
            // initialize error string
            $sErrors = '';

            // build error string for each element
            foreach( $aErrors as $sElementName => $aElementInfo )
            {
                // get the humanized name
                $sElementName = $aElementInfo[ 'humanized' ];

                // get the errors
                $aElementErrors = $aElementInfo[ 'errors' ];

                // build error messages for each error on this element
                $iErrorCount = count( $aElementErrors );
                $sErrors .= "$sElementName:<br />";
                for( $i = 0; $i < $iErrorCount; ++$i )
                {
                    $sErrors .= "&nbsp;&nbsp;&nbsp;{$aElementErrors[ $i ]}";
                    $sErrors .= '<br />';
                }
                $sErrors .= '<br />';
            }

            return $sErrors;
        }

        /**
         * Clean out the form hashes.
         *
         * @return $this
         */
        protected function ClearFormHashes()
        {
            unset( $_SESSION[ 'form-hashes' ] );

            return $this;
        }

        /**
         * Parses out all the possible form inputs and builds
         * a hash out of all the possible combinations of inputs
         * and submit values. The hash is later used in form
         * validation to verify whether or not the input has
         * been tampered with.
         *
         * @param   string      $sHTML  The html containing a set of forms.
         *
         * @throws  Exception   Rethrows anything that is caught.
         *
         * @return  $this
         */
        protected function ParseFormHashes( $sHTML )
        {
            try
            {
                // check if the html provided is valid
                if( !is_string( $sHTML ) || empty( $sHTML ) )
                {
                    throw new Exception( 'HTML was not provided. Cannot parse for forms.' );
                }

                // create a dom document item
                $oDOMDoc = new DOMDocument( '1.0', 'utf-8' );

                // load the html
                $oDOMDoc->loadHTML( $sHTML );

                // get all the forms on the page
                $oForms = $oDOMDoc->getElementsByTagName( 'form' );

                // initialize the form hashes in the session if they're not already there
                if( !isset( $_SESSION[ 'form-hashes' ] ) )
                {
                    $_SESSION[ 'form-hashes' ] = array();
                }

                // cycle through the forms to find the inputs
                for( $i = 0; $i < $oForms->length; ++$i )
                {
                    // get this form
                    $oForm = $oForms->item( $i );

                    // initialize the list of element names
                    $aInputs = array();

                    // get all the input elements for this form
                    $oInputs    = $oForm->getElementsByTagName( 'input' );
                    $oTextAreas = $oForm->getElementsByTagName( 'textarea' );
                    $oSelects   = $oForm->getElementsByTagName( 'select' );

                    $aSubmits = array();

                    // add all the inputs to the concatenated string
                    for( $j = 0; $j < $oInputs->length; ++$j )
                    {
                        // check if this is a submit button
                        if( strtolower( $oInputs->item( $j )->getAttribute( 'type' ) ) == 'submit' )
                        {
                            // save to the list of submits for this form
                            $aSubmits[] = $oInputs->item( $j )->getAttribute( 'name' );
                        }
                        else
                        {
                            // get the name
                            $aInputs[] = $oInputs->item( $j )->getAttribute( 'name' );
                        }
                    }

                    // add all textareas to the concatenated string
                    for( $j = 0; $j < $oTextAreas->length; ++$j )
                    {
                        $aInputs[] = $oTextAreas->item( $j )->getAttribute( 'name' );
                    }

                    // add all selects to the concatenated string
                    for( $j = 0; $j < $oSelects->length; ++$j )
                    {
                        $aInputs[] = $oSelects->item( $j )->getAttribute( 'name' );
                    }

                    // build concatenated string of all inputs
                    $sConcat = preg_replace('/\s+/', '_', implode( '', $aInputs ) );

                    // add form hash for each submit to the list saved in the session
                    $iSubmitCount = count( $aSubmits );
                    for( $j = 0; $j < $iSubmitCount; ++$j )
                    {
                        $_SESSION[ 'form-hashes' ][ md5( $sConcat . $aSubmits[ $j ]) ] = $aInputs;
                    }
                }

                return $this;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Get a the form specified in the template, populate with data, and display errors.
         *
         * @param   string      $sTemplate          The template file to use for the form.
         * @param   array       $aData              The data to populate the form with.
         * @param   array       $aErrors            Form and element errors.
         * @param   boolean     $bParseFormHashes   Flag: Parse form inputs for security.
         *
         * @throws  Exception   Thrown if an exception was caught at a lower level.
         *
         * @return  string      Form populated with values.
         */
        public function GetForm( $sTemplate, array $aData, array $aErrors, $bParseFormHashes = true )
        {
            try
            {
                // initialize the form data with the template to use
                $aFormData = array();
                $aFormData[ 'template' ] = $sTemplate;

                // initialize the error data
                $vErrorData = '';

                // display errors if possible
                if( !empty( $aErrors ) ) // unsuccessful submission
                {
                    // show invalid data errors
                    if( !empty( $aErrors[ 'elements' ] ) )
                    {
                        // set error string for elements
                        $sErrors = $this->BuildElementErrors( $aErrors[ 'elements' ] );

                        // build form error information
                        $vErrorData = array();
                        $vErrorData[ 'template' ]     = 'form-errors.html';
                        $vErrorData[ '_:_ERRORS_:_' ] = $sErrors;
                    }
                    // show unexpected problem errors
                    elseif( !empty( $aErrors[ 'form' ] ) )
                    {
                        // build form error information
                        $vErrorData = array();
                        $vErrorData[ 'template' ]     = 'form-errors.html';
                        $vErrorData[ '_:_ERRORS_:_' ] = $aErrors[ 'form' ];
                    }
                }

                // populate the form if possible
                if( !empty( $aData ) )
                {
                    foreach( $aData as $sInputName => $sInputValue )
                    {
                        $aFormData[ "_:_{$sInputName}_:_" ] = $sInputValue;
                    }
                }

                // build form data
                $aForm = array(
                    'template'     => 'form.html',
                    '_:_ERRORS_:_' => $vErrorData,
                    '_:_FORM_:_'   => $aFormData
                );

                // build the form
                $sForm = $this->PopulateTemplate( $aForm );

                // clear out form hashes
                $this->ClearFormHashes();

                // build the form hashes if possible
                if( $bParseFormHashes )
                {
                    // parse out the form hashes for security
                    $this->ParseFormHashes( $sForm );
                }

                return $sForm;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Add the header, body, and footer to the specified layout.
         *
         * @param   string      $sHeader
         * @param   string      $sBody
         * @param   string      $sFooter
         * @param   string      $sLayoutTemplate
         *
         * @throws  Exception   If an exception is caught, bubble it up.
         *
         * @return  string      Layout template populated with header, body, and footer.
         */
        public function PopulateLayout( $sTitle = '', $sHeader = '', $sBody = '', $sFooter = '', $sLayoutTemplate = 'layout.html' )
        {
            try
            {
                // initialize the layout array
                $aLayout = array();

                // set the template
                $aLayout[ 'template' ] = $sLayoutTemplate;

                // set the page header
                $aLayout[ '_:_HEADER_:_' ] = $sHeader;

                // set the title of the page
                $aLayout[ '_:_PAGE-TITLE_:_' ] = $sTitle;

                // set the page body
                $aLayout[ '_:_BODY_:_' ] = $sBody;

                // set the page footer
                $aLayout[ '_:_FOOTER_:_' ] = $sFooter;

                return $this->PopulateTemplate( $aLayout );
            }
            catch ( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Determines the right compression mode to use.
         *
         * @return string
         */
        private function CompressionMode()
        {
            // initialize the return value
            $sMode = '';

            // determine whether http accepts gzip
            $aHttpAccept = array();
            if( isset( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ] ) )
            {
                if( strpos( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ], ',' ) !== false )
                {
                    $aHttpAccept = explode( ',' , $_SERVER[ 'HTTP_ACCEPT_ENCODING' ] );
                    $aHttpAccept = array_map( 'trim', $aHttpAccept );
                }
                else
                {
                    $aHttpAccept[] = $_SERVER[ 'HTTP_ACCEPT_ENCODING' ];
                }
            }

            // determine whether to use the mode
            if( // must not be CLI mode
                !bIS_CLI
                // gzip must be accepted by the browser
                && in_array( 'gzip', $aHttpAccept ) )
            {
                $sMode = 'ob_gzhandler';
            }

            return $sMode;
        }

        /**
         * Concatenates multiple files of a given content type into one.
         *
         * @param    string       $sContentType   The Content-Type for the header.
         * @param    array        $aFiles         The list of files to suture together.
         * @param    boolean      $bCache         Whether or not to cache the file.
         * @param    boolean      $bCompress      Whether or not to compress the file using GZ.
         * @param    string       $sCharset       The character set to encode the response with.
         *
         * @throws   Exception    Rethrows anything caught at a lower level.
         *
         * @return   string
         */
        public function SutureFiles( $sContentType, array $aFiles, $bCache = true, $bCompress = true, $sCharSet = 'utf-8' )
        {
            try
            {
                // initialize return
                $sSutured = '';

                // check if files were provided
                if ( $aFiles )
                {
                    // loop through each file
                    $iFileCount = count( $aFiles );
                    for( $iFile = 0; $iFile < $iFileCount; ++$iFile )
                    {
                        // check if the file exists and is readable
                        if( file_exists( $aFiles[ $iFile ] )
                            && is_readable( $aFiles[ $iFile ] ) )
                        {
                            $sSutured .= file_get_contents( $aFiles[ $iFile ] );
                        }
                        else
                        {
                            throw new Exception( 'File provided does not exist or is not readable: ' . $aFiles[ $iFile ] );
                        }
                    }
                }

                // set headers
                header( "Content-Type: " . $sContentType . "; charset=" . $sCharSet  );

                if ( $bCache )
                {
                    // set expiration date
                    header( "Expires: " . strftime( "%a, %d %b %Y %H:%M:%S %Z", strtotime( "next month" ) ) );

                    //get the last-modified-date of this very file
                    $iLastModified = filemtime( __FILE__ );

                    //get a unique hash of this file (etag)
                    $sETag = md5( __FILE__ );

                    //get the HTTP_IF_MODIFIED_SINCE header if set
                    $ifModifiedSince = ( isset( $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ] ) ? $_SERVER[ 'HTTP_IF_MODIFIED_SINCE' ] : false);

                    //get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
                    $sETagHeader = ( isset( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) ? trim( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) : false);

                    //set last-modified header
                    header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $iLastModified ) . ' GMT' );

                    //set etag-header
                    header( 'Etag: ' . $sETag );

                    //make sure caching is turned on
                    header( 'Cache-Control: public' );

                    //check if page has changed. If not, send 304 and exit
                    if ( @strtotime( $ifModifiedSince ) == $iLastModified || $sETagHeader == $sETag )
                    {
                       header( 'HTTP/1.1 304 Not Modified' );
                       // exit;
                    }
                }

                if ( $bCompress )
                {
                    // compress the output if possible
                    $sMode = $this->CompressionMode();
                    if ( $sMode !== '' )
                    {
                       ob_start( $sMode );
                    }
                }

                return $sSutured;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }

        /**
         * Takes a filesize arguments and makes in humanized.
         *
         * @param   integer $iSize      Raw filesize passed in - should be in bytes.
         *
         * @throws  Exception           Thrown if an exception was caught at a lower level.
         *
         * @return  string  $vReturn    Formatted filesize string.
         */
        public function GenerateFilesize( $iBytes )
        {
            try
            {
                // Set definitions and figure out which will be appended.
                $sSizes   = array( 'B','kB','MB','GB','TB','PB','EB','ZB','YB' );
                $iFactor  = floor( ( strlen( $iBytes ) - 1) / 3 );
                $sPostFix = isset( $sSizes[ $iFactor ] ) ? $sSizes[ $iFactor ] : '';
                $vReturn  = sprintf( '%.2f', $iBytes / pow( 1024, $iFactor) ) . ' ' . $sPostFix;
                return $vReturn;
            }
            catch( Exception $oException )
            {
                throw cAnomaly::BubbleException( $oException );
            }
        }
    }
?>