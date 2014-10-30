<?php
    /**
     * error handling.
     *
     * Thanks the PHP, all errors and any uncaught exceptions are handled by the Anomaly class.
     *
     * It is mandatory that all controllers have a try/catch block that handles the exception, rather
     * than bubbling any further.
     *
     */
    // controller try/catch
    try
    {

    }
    catch( Exception $oException )
    {
        cAnomaly::ExceptionHandler( $oException );
    }


    /**
     * handle an exception
     */
    cAnomaly::ExceptionHandler( new Exception( 'An exception has occurred!' ) );

    /**
     * bubble exception try/catch (usually in a method)
     */
    try
    {
        // any code here
    }
    catch( Exception $oException )
    {
        // bubble the exception to the top of the stack
        throw cAnomaly::BubbleException( $oException );
    }
?>