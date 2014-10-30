<?php
    /**
     * populate a template.
     *
     * these examples are super simple, purposefully. at some point
     * you need to abstract your components into modular code, instead of
     * procedurally building the template definition.
     *
     * as a recommendation, you should have only one call to PopulateTemplate. Unless,
     * of course you are saving resources and rendering components individually. It depends on
     * the circumstances.
     */
    // get base presentation. set the template folder path.
    $oPresBase = new cPresBase( sBASE_INC_PATH . '/templates' );

    // initialize page template
    $aPage     = array( 'template' => 'page.html' );

    // initialize table template
    $aTable    = array( 'template' => 'table.html' );

    // define table headers
    $aTableHeaders = array(
        'template' => 'table-th.html',
        array(
            '_:_HEADER_:_' => 'header1'
        ),
        array(
            '_:_HEADER_:_' => 'header2'
        )
    );

    // build table rows
    $aTableRows = array(
        'template' => 'table-tr.html',
        array(
            '_:_CELL1_:_' => 'test',
            '_:_CELL2_:_' => 'test2'
        ),
        array(
            '_:_CELL1_:_' => 'test',
            '_:_CELL2_:_' => 'test2'
        ),
        array(
            '_:_CELL1_:_' => 'test',
            '_:_CELL2_:_' => 'test2'
        ),
        array(
            '_:_CELL1_:_' => 'test',
            '_:_CELL2_:_' => 'test2'
        ),
        array(
            '_:_CELL1_:_' => 'test',
            '_:_CELL2_:_' => 'test2'
        ),
        array(
            '_:_CELL1_:_' => 'test',
            '_:_CELL2_:_' => 'test2'
        ),

    );

    // put table together
    $aTable[ '_:_HEADERS_:_' ] = $aTableHeaders;
    $aTable[ '_:_ROWS_:_' ]    = $aTableRows;

    // put the page together
    $aPage[ '_:_TITLE_:_' ]    = 'Page title';
    $aPage[ '_:_BODY_:_' ]     = 'Hello world.';
    $aPage[ '_:_USER_:_' ]     = 'test';
    $aPage[ '_:_TABLE_:_' ]    = $aTable;
    $aPage[ '_:_FOOTER_:_' ]   = '&copy; 2014 Clemson University, South Carolina';

    // render the template(s)
    $sPage = $oPresBase->PopulateTemplate( $aPage );
?>