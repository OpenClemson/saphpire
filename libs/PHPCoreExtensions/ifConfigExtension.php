<?php
    /**
     * Interface for extensions that load configuration data.
     *
     * @author  Team Rah
     *
     * @package Core
     * @version 0.1
     */
    interface ifConfigExtension
    {
        /**
         * Loads data either into application or environment level configuration.
         *
         * @param  array      $aOptions  Options to use while loading data.
         *
         * @throws Exception             Rethrows anything that is caught.
         */
        public function Load( $aOptions = array() );
    }
?>