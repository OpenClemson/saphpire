<?php
    /**
     * Main presentation class for snapshot service.
     *
     * @author     Team Rah
     * @package    Snapshot
     * @subpackage Presentation
     * @version    0.0.2.0
     */
    class cPresentation
    {
        /**
         * Constructor for cPresentation class.
         *
         * @return  void
         */
        public function __construct()
        {

        }

        /**
         * Sets headers.
         *
         * @param   string  $sResponse
         * @param   array   $aExtraHeaders
         *
         * @return  void
         */
        public function SetHeaders( $sResponse = '', $aExtraHeaders )
        {
            header( 'Content-type: application/json' );
            if ( $sResponse )
            {
                header( $sResponse );
            }
            if ( !empty( $aExtraHeaders ) )
            {
                foreach ( $aExtraHeaders as $sHeader )
                {
                    header ( $sHeader );
                }
            }
        }

        /**
         * Renders the supplied data.
         *
         * @param   array    $aData
         *
         * @return  array
         */
        public function Render( array $aData = array() )
        {
            $this->SetHeaders( $aData[ 'code' ], $aData[ 'extraheaders' ] );

            $sReturn = '';
            if ( !empty( $aData[ 'data' ] ) )
            {
                $sReturn = json_encode( $aData[ 'data' ] );
            }
            else if ( !empty( $aData[ 'response' ] ) )
            {
                $sReturn = json_encode( $aData[ 'response' ] );
            }

            return $sReturn;
        }
    }
?>