<?php
    $htmlTemplate = file_get_contents( 'template.html' );

    $sPage = isset( $_GET[ 'p' ] ) ? $_GET[ 'p' ] : '';
    $sPage = !empty( $sPage ) ? htmlentities( $sPage ) : 'general';
    $sFile = $sPage . '.php';
    if ( file_exists( $sFile ) )
    {
        // build nav
        $aNav = array(
            'apis',
            'auth',
            'configs',
            'database',
            'errors-exceptions',
            'files',
            'forms',
            'general',
            'logs',
            'requests',
            'services',
            'templates'
        );
        $sNavElem = '<li style="float:left; margin: 3px"><a href="./_:_URL_:_">_:_TEXT_:_</a></li>';
        $sNav     = '<ul style="list-style-type: none;">';

        for( $i = 0; $i < count( $aNav ); $i++ )
        {
            $sLink = str_replace( '_:_URL_:_', './index.php?p=' . $aNav[ $i ], $sNavElem );
            $sLink = str_replace( '_:_TEXT_:_', ucfirst( $aNav[ $i ] ), $sLink );
            $sNav .= $sLink;
        }
        $sNav    .= '</ul><div style="clear:both">&nbsp;</div>';

        $htmlTemplate = str_replace( '_:_NAVIGATION_:_', $sNav, $htmlTemplate );
        $htmlTemplate = str_replace( '_:_TITLE_:_', ucfirst( strtolower( $sPage ) ), $htmlTemplate );
        $htmlTemplate = str_replace( '_:_PAGE_:_', $sFile, $htmlTemplate );
        $htmlTemplate = str_replace( '_:_BODY_:_', htmlentities( file_get_contents( $sFile ) ), $htmlTemplate );
    }

    echo $htmlTemplate;
?>