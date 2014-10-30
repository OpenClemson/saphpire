<?php
    /**
     * file operations
     */

    // read a file
    $sFilePath = '/path/to/file/test.txt';
    $bExists   = file_exists( $sFilePath );
    if ( $bExists )
    {
        $sFileContents = file_get_contents( $sFilePath );
    }
    else
    {
        // file does not exist.
    }

    // write to a file
    $sFileContents = 'file123456';
    $iBytesWritten = file_put_contents( $sFileName, $sFileContents );
    if ( $iBytesWritten !== false )
    {
        // success! at least 0 bytes written
    }
    else
    {
        // writing to file failed.
    }
?>