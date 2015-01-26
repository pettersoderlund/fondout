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
    'default' => 'sv_SE',

    /**
     * Supported locales
     *
     * Some good description here. Default is something
     *
     * Accepted is something else
     */
    'supported' => array('sv_SE', 'en_US'),

    /**
     * Aliases for locales
     *
     * Some good description here. Default is something
     *
     * Accepted is something else
     */
    // 'aliases' => array('sv' => 'sv_SE'),

    /**
     * Strategies
     *
     * Some good description here. Default is something
     *
     * Accepted is something else
     */
     'strategies' => array(/*'SlmLocale\Strategy\UriPathStrategy',*/'SlmLocale\Strategy\QueryStrategy', 'SlmLocale\Strategy\CookieStrategy', 'SlmLocale\Strategy\HttpAcceptLanguageStrategy'), 

    /**
     * Throw exception when no locale is found
     *
     * Some good description here. Default is something
     *
     * Accepted is something else
     */
    //'throw_exception' => true,

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
