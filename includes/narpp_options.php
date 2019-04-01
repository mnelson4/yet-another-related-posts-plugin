<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpdb, $wp_version, $yarpp;

/* Enforce YARPP setup: */
$yarpp->enforce();

if(!$yarpp->enabled() && !$yarpp->activate()) {
    echo '<div class="updated">'.__('The NARPP database has an error which could not be fixed.','narpp').'</div>';
}

/* Check to see that templates are in the right place */
if (!$yarpp->diagnostic_custom_templates()) {

    $template_option = yarpp_get_option('template');
    if ($template_option !== false &&  $template_option !== 'thumbnails') yarpp_set_option('template', false);

    $template_option = yarpp_get_option('rss_template');
    if ($template_option !== false && $template_option !== 'thumbnails') yarpp_set_option('rss_template', false);
}

/* MyISAM Check */
include 'narpp_myisam_notice.php';

/* This is not a yarpp pluging update, it is an yarpp option update */
if (isset($_POST['update_yarpp'])
    && check_admin_referer('update_yarpp', 'update_yarpp-nonce')
    && current_user_can('manage_options')
) {
    $new_options = array();
    foreach ($yarpp->default_options as $option => $default) {
        // Skip options we'll handle separately.
        if (in_array(
            $option,
            array(
                'weight',
                'auto_display_post_types',
                'recent',
                'exclude',
                'template',
                'rss_template',
                'recent_number',
                'recent_units',
            )
        )) {
            continue;
        }
        if ( is_bool($default) ) {
            $new_options[ $option ] = isset($_POST[ $option ]);
        }
        if ( isset($_POST[$option]) && is_string($_POST[$option]) ){
            if(is_bool($default)){
                $new_options[$option] = (bool)$_POST[$option];
            }else if(is_int($default)){
                $new_options[$option] = intval($_POST[$option]);
            } elseif($default === 'score DESC') {
                $order_parts = explode(' ', $order, 2);
                if(in_array(strtolower($order_parts[1]),['asc','desc'])) {
                    // FYI the first part will be passed into WP_Query's "orderby" field, so it will be sanitized there.
                    $new_options[$option] = implode(' ', $order_parts);
                }
            } else {
                if(current_user_can('unfiltered_html')){
                    // Ok, let them put HTML in there. This will be passed to update_option which guards against
                    // SQL injection
                    $new_options[$option] = stripslashes($_POST[$option]);
                } else {
                    $new_options[$option] = wp_kses((string) $_POST[$option], wp_kses_allowed_html('post'));
                }
            }
        }
    }

    if ( isset($_POST['weight']) ) {
        $new_options['weight'] = array();
        $new_options['require_tax'] = array();
        foreach ( (array) ['title','body'] as $key) {
            $value = isset(
                $_POST['weight'],
                $_POST['weight'][$key]
            )
            ? $_POST['weight'][$key]
            : '';
            if ( $value === 'consider' )
                $new_options['weight'][$key] = 1;
            if ( $value === 'consider_extra' )
                $new_options['weight'][$key] = YARPP_EXTRA_WEIGHT;
        }
        $real_taxonomies = get_taxonomies(array(),'names');
        foreach ( (array) $_POST['weight']['tax'] as $tax => $value) {
            if(! in_array($tax, $real_taxonomies)){
                continue;
            }
            if ( $value === 'consider' )
                $new_options['weight']['tax'][$tax] = 1;
            if ( $value === 'consider_extra' )
                $new_options['weight']['tax'][$tax] = YARPP_EXTRA_WEIGHT;
            if ( $value === 'require_one' ) {
                $new_options['weight']['tax'][$tax] = 1;
                $new_options['require_tax'][$tax] = 1;
            }
            if ( $value == 'require_more' ) {
                $new_options['weight']['tax'][$tax] = 1;
                $new_options['require_tax'][$tax] = 2;
            }
        }
    }

    if (isset($_POST['auto_display_post_types'])) {
        $new_options['auto_display_post_types'] = array_intersect(
            get_post_types(array(), 'names'),
            array_keys($_POST['auto_display_post_types'])
        );
    } else {
        $new_options['auto_display_post_types'] = array();
    }
    if(isset($_POST['recent_only'])){
        $recent_number = intval($_POST['recent_number']);
        $recent_units = in_array(
            $_POST['recent_units'],
            [
                'day',
                'week',
                'month',
            ])
            ? $_POST['recent_units']
            : 'month';
        $new_options['recent'] =  $recent_number . ' ' .  $recent_units;
    }


    if ( isset($_POST['exclude']) )
        $new_options['exclude'] = implode(
            ',',
            array_map(
                'intval',
                array_keys($_POST['exclude'])
            )
        );
    else
        $new_options['exclude'] = '';

    $new_options['template'] = $_POST['use_template'] == 'custom' ? sanitize_file_name($_POST['template_file']) :
        ( $_POST['use_template'] == 'thumbnails' ? 'thumbnails' : false );
    $new_options['rss_template'] = $_POST['rss_use_template'] == 'custom' ? sanitize_file_name($_POST['rss_template_file']) :
        ( $_POST['rss_use_template'] == 'thumbnails' ? 'thumbnails' : false );

    $new_options = apply_filters( 'yarpp_settings_save', $new_options );
    yarpp_set_option($new_options);

    echo '<div class="updated fade"><p>'.__('Options saved!','narpp').'</p></div>';
}

wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
wp_nonce_field('yarpp_display_demo', 'yarpp_display_demo-nonce', false);
wp_nonce_field('yarpp_display_exclude_terms', 'yarpp_display_exclude_terms-nonce', false);
wp_nonce_field('yarpp_set_display_code', 'yarpp_set_display_code-nonce', false);

if (!count($yarpp->admin->get_templates()) && $yarpp->admin->can_copy_templates()) {
    wp_nonce_field('yarpp_copy_templates', 'yarpp_copy_templates-nonce', false);
}

include(YARPP_DIR . '/includes/phtmls/narpp_options.phtml');