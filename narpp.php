<?php
/*----------------------------------------------------------------------------------------------------------------------
Plugin Name: Not Another Related Posts Plugin
Description: Adds related posts to your site and in RSS feeds, based on a powerful, customizable algorithm.
Version: 5.0.0
Author: Michael Yoshitaka Erlewine, Alexander Malov, Mike Lu, Peter Bowyer, Jeff Parker, Michael Nelson
Author URI: https://cmljnelson.wordpress.com
Plugin URI: https://github.com/mnelson4/yet-another-related-posts-plugin
Text Domain: narpp
----------------------------------------------------------------------------------------------------------------------*/
// @codingStandardsIgnoreStart
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// @codingStandardsIgnoreStart

add_action('plugins_loaded', 'narpp_load_after_other_plugins');
function narpp_load_after_other_plugins()
{
    // Double-check YARPP isn't already active. If it is, don't load NARPP.
    if(defined('YARPP_VERSION')) {
        // Oups! The old YARPP plugin is already active!
        add_action(
            'admin_notices',
            function(){
                echo '<div class="notice notice-error">' . esc_html__('Not Another Related Posts Plugin cannot run while its predecessor, Yet Another Related Posts Plugin, is active. Please deactivate Yet Another Related Posts Plugin.', 'narpp') . '</div>';
            }
        );
    } else {
        define('YARPP_VERSION', '5.0.0');
        define('YARPP_DIR', dirname(__FILE__));
        define('YARPP_URL', plugins_url('',__FILE__));
        define('YARPP_NO_RELATED', ':(');
        define('YARPP_RELATED', ':)');
        define('YARPP_NOT_CACHED', ':/');
        define('YARPP_DONT_RUN', 'X(');

        /*----------------------------------------------------------------------------------------------------------------------
        Sice v3.2: YARPP uses it own cache engine, which uses custom db tables by default.
        Use postmeta instead to avoid custom tables by un-commenting postmeta line and comment out the tables one.
        ----------------------------------------------------------------------------------------------------------------------*/
        /* Enable postmeta cache: */
        //if(!defined('YARPP_CACHE_TYPE')) define('YARPP_CACHE_TYPE', 'postmeta');

        /* Enable Yarpp cache engine - Default: */
        if(!defined('YARPP_CACHE_TYPE')) define('YARPP_CACHE_TYPE', 'tables');

        /* Load proper cache constants */
        switch(YARPP_CACHE_TYPE){
            case 'tables':
                define('YARPP_TABLES_RELATED_TABLE', 'yarpp_related_cache');
                break;
            case 'postmeta':
                define('YARPP_POSTMETA_KEYWORDS_KEY', '_yarpp_keywords');
                define('YARPP_POSTMETA_RELATED_KEY',  '_yarpp_related');
                break;
        }

        /* New in 3.5: Set YARPP extra weight multiplier */
        if(!defined('YARPP_EXTRA_WEIGHT')) define('YARPP_EXTRA_WEIGHT', 3);

        /* Includes ----------------------------------------------------------------------------------------------------------*/
        include_once(YARPP_DIR.'/includes/init_functions.php');
        include_once(YARPP_DIR.'/includes/related_functions.php');
        include_once(YARPP_DIR.'/includes/template_functions.php');

        include_once(YARPP_DIR.'/classes/NARPP_Core.php');
        include_once(YARPP_DIR.'/classes/NARPP_Widget.php');
        include_once(YARPP_DIR.'/classes/NARPP_Cache.php');
        include_once(YARPP_DIR.'/classes/NARPP_Cache_Bypass.php');
        include_once(YARPP_DIR.'/classes/NARPP_Cache_'.ucfirst(YARPP_CACHE_TYPE).'.php');

        /* WP hooks ----------------------------------------------------------------------------------------------------------*/
        add_action('init', 'yarpp_init');
        add_action('activate_'.plugin_basename(__FILE__), 'yarpp_plugin_activate', 10, 1);
    }
}



