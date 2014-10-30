<?php
    /**
     * form utilities
     */
    // initialize form state array
    $aData = array(
        'status'  => null,
        'success' => array(),
        'errors'  => array()
        'data'    => array()
    );
    // get form utilities object
    $oForm      = new cFormUtilities();

    // get clean form data, pass in our data
    $aFormData  = $oForm->GetCleanFormData( $_POST );

    // check for submitted form
    $bSubmitted = $oForm->IsFormSubmitted( 'form-name' );

    // validate form
    $bValid     = $oForm->IsValid();

    if ( $bValid )
    {
        // further validation and form handling logic

        // if our status is still null, the form passes.
        if( is_null( $aFormData[ 'status' ] ) )
        {
            // update the form status
            $aFormData[ 'status' ]  = true;
            $aFormData[ 'success' ] = array( 'Success!' );
        }
    }
    else
    {
        // form validation errors
        $aErrors               = $oForm->GetErrors();
        $aFormData[ 'errors' ] = $aErrors[ 'elements' ];
        $aFormData[ 'status' ] = false;
    }
?>

<!-- form validation in HTML (very basic) -->
<!-- validators are added the name attribute like "name:required" -->
<!DOCTYPE html>
<html>
    <body>
        <form id="form-id" action="php.php" method="post">
            <input type="text" name="input-1:required" id="input-1" />
            <input type="text" name="input-1:greater=0" id="input-1" value="1" />
            <button value="true" name="submit" type="submit">Submit</button>
        </form>
    </body>
</html>