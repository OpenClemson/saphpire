$(function(){
    // set up the screen width & height globals
    var iScreenWidth, iScreenHeight;
    iScreenHeight = $( window ).height();
    iScreenWidth  = $( window ).width();

    /**
     * handle those variable things ryan loves
     */
    $( '#sections .debug-value' ).parent().hide();

    $( '.debug-header' ).parent().click(function(){
        var oThis;
        oThis = $( this );
        oThis.parent().parent().find( '.debug-value > pre' ).dialog({
            autoOpen: true,
            minWidth: 400,
            maxHeight: iScreenHeight,
            maxWidth: iScreenWidth,
            position: { my: 'top', at: 'bottom', of: oThis },
            title: oThis.find('.debug-header').html() + ' (' + oThis.find('.debug-type').html() + ')',
            close: function(){
                $( this ).dialog( 'destroy' );
            }
        });
    });

    /**
     * handle dialogs for strings in stack trace
     */
    $( '.anomaly-dialog' ).dialog({
        autoOpen: false,
        width: 400,
        height: 400
    });

    $( '.anomaly-dialog-open' ).click(function(e){
        e.preventDefault();

        var oThis, aClass, iClass, aSubClass, sId, oDialog;

        oThis   = $( this );
        aClass  = oThis.attr('class').split(' ');
        iClass  = aClass.length;

        for ( var i = 0; i < iClass; ++i )
        {
            aSubClass = aClass[ i ].split( '-' );
            if ( aSubClass[ 0 ] == 'debug' )
            {
                sId = aSubClass[ 2 ];
                break;
            }
        }

        oDialog = $( '#' + sId );

        if ( $( '#' + sId ).dialog( 'isOpen' ) === true )
        {
            oDialog.dialog( 'close' );
        }
        else
        {
            oDialog.dialog( 'open' ).dialog( 'option', 'position', { my: 'left top', at: 'right', of: oThis } );
        }

        return false;
    });

    /**
     * handle the accordion for sections
     */
    $( '#sections' ).accordion({
        heightStyle: 'content',
        collapsible: true
    });

    $( '#stacktrace' ).accordion({
        heightStyle: 'content',
        collapsible: true
    });

	// enable tooltips
    $( '.tooltip' ).tooltip();
});