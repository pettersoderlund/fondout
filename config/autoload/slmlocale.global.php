<?php
/**
 * SlmLocale Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
$settings = array(
    /**
     * Default locale
     *
     * Some good description here. Default is something
     *
     * Accepted is something else
     */
    'default' => 'sv-SE',

    /**
     * Supported locales
     *
     * Some good description here. Default is something
     *
     * Accepted is something else
     */
    'supported' => array('sv-SE', 'en-GB', 'en'),

    /**
     * Aliases for locales
     *
     * Some good description here. Default is something
     *
     * Accepted is something else
     */
    'aliases' => array('en' => 'en-GB'),

    /**
     * Strategies
     *
     * Some good description here. Default is something
     *
     * Accepted is something else
     */
    'strategies' => array('query', 'acceptlanguage'),


    /**
     * Throw exception when no locale is found
     *
     * Some good description here. Default is something
     *
     * Accepted is something else
     */
    'throw_exception' => true,

    /**
     * End of SlmLocale configuration
     */
);

/**
 * You do not need to edit below this line
 */
return array(
    'slm_locale' => $settings
);
