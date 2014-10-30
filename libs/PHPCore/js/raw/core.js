/**
 * Juggernaut Core Javascript Drivers
 *
 * requires jQuery 1.10.1+ & compatible version of jQuery UI
 *
 * @version  1.1
 */

// instantiate variables
var jui;

// set up core object
jui = new Object();
jui = {
    animate: {
        height: {
            show: {
                height:         'show',
                paddingTop:     'show',
                paddingBottom:  'show',
                marginBottom:   'show',
                marginTop:      'show',
                border:         'show'
            },
            hide: {
                height:         'hide',
                paddingTop:     'hide',
                paddingBottom:  'hide',
                marginTop:      'hide',
                marginBottom:   'hide',
                border:         'hide'
            }
        },
        width: {
            show: {
                width:          'show',
                paddingLeft:    'show',
                paddingRight:   'show',
                marginLeft:     'show',
                marginBottom:   'show',
                border:         'show'
            },
            hide: {
                width:          'hide',
                paddingLeft:    'hide',
                paddingRight:   'hide',
                marginLeft:     'hide',
                marginRight:    'hide',
                border:         'hide'
            }
        }
    },
    msg: {
        ajaxFail: 'Ajax request failed. Please contact system administrator.',
        loading:  'Loading, please wait...'
    }
};

/**
 * DOM ready listeners
 */
$(document).ready( function() {
    // set app base
   var appBase = ( typeof app.base != 'undefined' ? app.base : '../' );

    // preload images
    new Image().src = appBase + 'libs/PHPCore/img/loading.gif';

    /**
     * buttons/listeners
     */

    // alert close button
    $( '.alert > .close' )
        .click(function(e){
            e.preventDefault();
            $(this).parent().animate( jui.animate.height.hide, 'fast', function(){
                $(this).hide();
            });
        })
    ;

    // tooltip
    $( '.tip' ).tooltip();

    // return false
    $( '.rf' ).click(function(e){
        e.preventDefault();
        return false;
    });

    // print button
    $( '.print' )
        .click(function(){
            if ( confirm( 'Are you sure you want to print?' ) )
            {
                window.print();
            }
            return false;
        })
    ;

    // universal confirm button
    $( '.confirm' )
        .click(function(){
            return confirm("Are you sure you want to continue?");
        })
    ;

    // universal reset button
    $( 'input[type="reset"]' )
        .click(function(){
            return confirm("Are you sure you want to reset this form?\nYou will lose all unsaved changes.\nThis cannot be undone.");
        })
    ;

    // universal back button
    $( '.back' )
        .click(function(){
            window.history.go(-1);
        })
    ;

    // universal check toggler
    $( '.check-all' ).change(function(){
        var oThis, sName, sForm, sSelector;

        oThis       = $( this );
        sName       = oThis.attr( 'name' );
        sForm       = oThis.closest( 'form' ).attr( 'id' );
        sSelector   = 'form#' + sForm + ' input[type="checkbox"][name="'+sName+'"]';

        $( sSelector ).each(function(){
            oThis = $( this );
            if ( !oThis.hasClass( 'check-all' ) )
            {
                oThis.prop( 'checked', ( oThis.is( ':checked' ) ? false : true ) );
            }
        });
    });

    // double label click
    $( 'label.dblclick' ).click(function(){
        // check the checkbox
        var bIsChecked = $( this ).prev().prop( 'checked' );
        $( this ).prev().prop( 'checked', ( bIsChecked ? false : true ) );
    });

    // selects text in textbox
    $( '.click-select' )
        .focus(function(){
            $(this).select();
        })
        .click(function(){
            $(this).select();
        })
    ;

    /**
     * dialogs
     */
    $( '.dialog' ).hide();

    // universal dialog window
    $( '[rel="dialog"]' ).click(function(e){
        e.preventDefault();
        var oThis, sDialog, iHeight, iWidth, bModal;

        oThis       = $( this );
        sDialog     = oThis.attr( 'data-dialog' );
        oDialog     = $( '#' + sDialog );
        iHeight     = oDialog.attr( 'data-height' );
        iWidth      = oDialog.attr( 'data-width' );
        bModal      = ( oDialog.attr( 'data-modal' ) == 'true' ? true : false );

        // set defaults
        if ( isEmpty( iHeight ) ) iHeight = 300;
        if ( isEmpty( iWidth ) ) iWidth = 300;

        // initialize the dialog
        oDialog.dialog({
            autoOpen: true,
            height: iHeight,
            width: iWidth,
            modal: bModal,
            close: function(){
                $( this ).dialog( 'destroy' );
            }
        });
    });

    /**
     * drop down menu on click
     */
    // universal menu
    $( '.menu' ).menu();
    closeMenus();

    // close when clicking outside
    $( 'html' ).click(function(e){
        closeMenus();
    });

    // universal menu opener
    $( '[data-menu]' ).click(function(e){
        e.preventDefault();
        e.stopPropagation();
        var oMenu, sMenu, oThis;

        // set vars
        oThis = $( this );
        sMenu = oThis.attr( 'data-menu' );
        oMenu = $( '#' + sMenu );

        // close other menus
        // $( '.menu:not(#'+sMenu+')' ).hide();
        closeMenus();

        // open requested menu
        oMenu.animate({height:'toggle'}, 'fast');

        // set position
        // oMenu.menu( 'option', 'position', { my: "left top", at: "left bottom", of: $(this) } )
    });


    /**
     * char counter
     *
     * <element class="countchars" rel="numberOfChars displayElementId"></element>
     */
    $( '.countchars' ).each(function(){
        countChars( $( this ) );
    });
    $( '.countchars' ).keyup(function(){
        countChars( $( this ) );
    });

    /**
     * word counter
     *
     * <element class="countwords" rel="numberOfWords displayElementId"></element>
     */
    $( '.countwords' ).each(function(){
        countWords( $( this ) );
    });
    $( '.countwords' ).keyup(function(){
        countWords( $( this ) )
    });
});

/**
 * functions
 */
// close menu
function closeMenus()
{
    $( ".menu" ).menu( "collapseAll", null, true ).hide();
}

//reload page
function reloadPage(){
    self.location.reload();
}

// opens requested tab
function openTab( sTab, iTab )
{
    $( sTab ).tabs( 'option', 'active', iTab );
    return false;
}

// gets variable from <script> tag src string
function getScriptVariable(scriptName, key) {
  var scripts = document.getElementsByTagName('script'),
      n = scripts.length, scriptSource, i, r;

  for (i = 0; i < n; i++) {
    scriptSource = scripts[i].src;
    if(scriptSource.indexOf(scriptName)>=0) {
      r = new RegExp("[\\?&]"+key+"=([^&#]*)");
      var keyValues = r.exec(scriptSource);
      return keyValues[1];
    }
  }
}

// checks if a value is empty
function isEmpty( vVal )
{
    return ( vVal == '' || typeof vVal == 'undefined' );
}

// checks if a text control is empty
function fieldIsEmpty( sSelector )
{
    var vVal = $( sSelector ).val();
    return isEmpty( vVal );
}

// checks if a checkbox is checked
function isChkd( sSelector )
{
    return ( $( sSelector ).prop('checked') );
}

// adds HTML5 style classes to invalid fields
function html5required( sForm )
{
    var bReturn = true, sName;
    $( sForm + ' *[required="required"]' ).each(function(){
        sName = $( this ).attr( 'name' );
        if ( isEmpty( sForm + ' [name="' + sName + '"]' ) )
        {
            $( this ).addClass( 'field-invalid' );
            bReturn = false;
        }
        else
        {
            $( this ).removeClass( 'field-invalid' );
        }
    });
    return bReturn;
}

// serializes the form data for checking against later,
// must be set on page load
function serializeForm( sSelector )
{
    $( sSelector ).data('serialize',$( sSelector ).serialize());
}

// compares stored serialized form with current serialize form
// returns TRUE if data doesnt match, i.e. form has changed
// if TRUE, it adds the 'form-modified' field
function formChanged( sSelector )
{
    var bChanged = false;

    if( $( sSelector ).serialize() != $( sSelector ).data( 'serialize' ) ){
        bChanged = true;
    }

    if ( bChanged )
    {
        if ( $( sSelector ).find( 'input[name="form-modified"]' ).length == 0 )
        {
            $( sSelector ).prepend( $( '<input type="hidden" name="form-modified" value="true" />' ) );
        }
    }
}

// counts characters left, disallows overage
function countChars( oObj )
{
    var relArr = oObj.attr('rel').split(' ');
    var max = relArr[0];
    var countDisplay = relArr[1];
    var len = oObj.val().length;
    if (len > max) {
        oObj.val(oObj.val().substr(0, max));
    } else {
        var charCnt = max - len;
        $('#' + countDisplay).text(charCnt + ' chars');
    }
}

// counts words left, disallows overage
function countWords( oObj )
{
    var relArr, max, words, wordCnt, wordStr;

    // split the rel attribute by spaces
    relArr = oObj.attr('rel').split(' ');

    // get the max val
    max = relArr[0];

    // split the string into words
    words   = $.trim( oObj.val() ).split(' ');

    // count the words
    wordCnt = words.length;

    // if number of words is greater than max, chop off extra words
    if ( wordCnt > max )
    {
        wordCnt = wordCnt - ( wordCnt - max );
        wordStr = '';
        for ( var i = 0; i < wordCnt; i++ )
        {
            wordStr = wordStr + words[ i ] + ' ';
        }

        oObj.val( wordStr );

        // set the display class as 'important'
        $('#' + relArr[1]).addClass( 'label-important' );
    }
    // else if ( ( max - wordCnt ) == 0 )
    // {
    //     $('#' + relArr[1]).addClass( 'label-important' );
    // }

    // otherwise, show how many words are left
    else
    {
        // determine how many words are left
        wordCnt = max - wordCnt;

        // set the element display
        $('#' + relArr[1]).text(wordCnt + ' words');

        // remove the 'important' class if its there
        $('#' + relArr[1]).removeClass( 'label-important' );
    }
}

/*!
 * jQuery Cookie Plugin v1.4.0
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
!function(e){"function"==typeof define&&define.amd?define(["jquery"],e):e(jQuery)}(function(e){function n(e){return u.raw?e:encodeURIComponent(e)}function o(e){return u.raw?e:decodeURIComponent(e)}function i(e){return n(u.json?JSON.stringify(e):String(e))}function r(e){0===e.indexOf('"')&&(e=e.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\/g,"\\"));try{return e=decodeURIComponent(e.replace(c," ")),u.json?JSON.parse(e):e}catch(n){}}function t(n,o){var i=u.raw?n:r(n);return e.isFunction(o)?o(i):i}var c=/\+/g,u=e.cookie=function(r,c,a){if(void 0!==c&&!e.isFunction(c)){if(a=e.extend({},u.defaults,a),"number"==typeof a.expires){var d=a.expires,f=a.expires=new Date;f.setTime(+f+864e5*d)}return document.cookie=[n(r),"=",i(c),a.expires?"; expires="+a.expires.toUTCString():"",a.path?"; path="+a.path:"",a.domain?"; domain="+a.domain:"",a.secure?"; secure":""].join("")}for(var s=r?void 0:{},p=document.cookie?document.cookie.split("; "):[],m=0,v=p.length;v>m;m++){var x=p[m].split("="),k=o(x.shift()),l=x.join("=");if(r&&r===k){s=t(l,c);break}r||void 0===(l=t(l))||(s[k]=l)}return s};u.defaults={},e.removeCookie=function(n,o){return void 0===e.cookie(n)?!1:(e.cookie(n,"",e.extend({},o,{expires:-1})),!e.cookie(n))}});