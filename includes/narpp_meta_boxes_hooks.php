<?php
if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

include_once(YARPP_DIR . '/classes/NARPP_Meta_Box.php');
include_once(YARPP_DIR . '/classes/NARPP_Meta_Box_Display_Feed.php');
include_once(YARPP_DIR . '/classes/NARPP_Meta_Box_Display_Web.php');
include_once(YARPP_DIR . '/classes/NARPP_Meta_Box_Pool.php');
include_once(YARPP_DIR . '/classes/NARPP_Meta_Box_Relatedness.php');

global $yarpp;

add_meta_box(
    'yarpp_pool',
    __('"The Pool"', 'narpp'),
    array(new NARPP_Meta_Box_Pool, 'display'),
    'settings_page_yarpp',
    'normal',
    'core'
);

add_meta_box(
    'yarpp_relatedness',
    __('"Relatedness" options', 'narpp'),
    array(
        new NARPP_Meta_Box_Relatedness,
        'display',
    ),
    'settings_page_yarpp',
    'normal',
    'core'
);

add_meta_box(
    'yarpp_display_web',
    __('Display options <small>for your website</small>', 'narpp'),
    array(
        new NARPP_Meta_Box_Display_Web,
        'display',
    ),
    'settings_page_yarpp',
    'normal',
    'core'
);

add_meta_box(
    'yarpp_display_rss',
    __('Display options <small>for RSS</small>', 'narpp'),
    array(
        new NARPP_Meta_Box_Display_Feed,
        'display',
    ),
    'settings_page_yarpp',
    'normal',
    'core'
);

/** @since 3.3: hook for registering new YARPP meta boxes */
//do_action('add_meta_boxes_settings_page_yarpp');