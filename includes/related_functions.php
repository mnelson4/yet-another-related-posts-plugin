<?php
/*---------------------------------------------------------------------------------------------------------------------
Here are the related_WHATEVER functions, as introduced in 1.1.
Since YARPP 2.1, these functions receive (optionally) one array argument.
----------------------------------------------------------------------------------------------------------------------*/

function yarpp_related($args = array(), $reference_ID = false, $echo = true) {
	global $yarpp;

	if (is_array($reference_ID)){
		_doing_it_wrong( __FUNCTION__, "This NARPP function now takes \$args first and \$reference_ID second.", '3.5');
		return;
	}
	
	return $yarpp->display_related($reference_ID, $args, $echo);
}

function yarpp_related_exist($args = array(), $reference_ID = false) {
	global $yarpp;

	if (is_array($reference_ID)) {
		_doing_it_wrong( __FUNCTION__, "This NARPP function now takes \$args first and \$reference_ID second.", '3.5');
		return;
	}
	
	return $yarpp->related_exist($reference_ID, $args);
}

function yarpp_get_related($args = array(), $reference_ID = false) {
	global $yarpp;
	return $yarpp->get_related($reference_ID, $args);
}


/**
 * Only define `related_posts` (and the other non-namespaced functions) for backward-compatibility with code that's
 * already using them, and if they won't conflict with other plugins. Remember this gets loaded on 'init' priority 10
 * so if another plugin defines them, they probably have already done so.
 */
if (! function_exists('related_posts')) {
    function related_posts($args = array(), $reference_ID = false, $echo = true)
    {
        global $yarpp;

        if (false !== $reference_ID && is_bool($reference_ID)) {
            _doing_it_wrong(
                __FUNCTION__,
                "This NARPP function now takes \$args first and \$reference_ID second.",
                '3.5'
            );
            return;
        }

        if ($yarpp->get_option('cross_relate')) {
            $args['post_type'] = $yarpp->get_post_types();
        } else {
            $args['post_type'] = array('post');
        }

        return yarpp_related($args, $reference_ID, $echo);
    }
}

if (! function_exists('related_pages')) {
    function related_pages($args = array(), $reference_ID = false, $echo = true)
    {
        global $yarpp;

        if (false !== $reference_ID && is_bool($reference_ID)) {
            _doing_it_wrong(
                __FUNCTION__,
                "This NARPP function now takes \$args first and \$reference_ID second.",
                '3.5'
            );
            return;
        }

        if ($yarpp->get_option('cross_relate')) {
            $args['post_type'] = $yarpp->get_post_types();
        } else {
            $args['post_type'] = array('page');
        }

        return yarpp_related($args, $reference_ID, $echo);
    }
}

if (! function_exists('related_entris')) {
    function related_entries($args = array(), $reference_ID = false, $echo = true)
    {
        global $yarpp;

        if (false !== $reference_ID && is_bool($reference_ID)) {
            _doing_it_wrong(
                __FUNCTION__,
                "This NARPP function now takes \$args first and \$reference_ID second.",
                '3.5'
            );
            return;
        }

        $args['post_type'] = $yarpp->get_post_types();

        return yarpp_related($args, $reference_ID, $echo);
    }
}

if (! function_exists('related_posts_exist')) {
    function related_posts_exist($args = array(), $reference_ID = false)
    {
        global $yarpp;

        if ($yarpp->get_option('cross_relate')) {
            $args['post_type'] = $yarpp->get_post_types();
        } else {
            $args['post_type'] = array('post');
        }

        return yarpp_related_exist($args, $reference_ID);
    }
}

if (! function_exists('related_pages_exist')) {
    function related_pages_exist($args = array(), $reference_ID = false)
    {
        global $yarpp;

        if ($yarpp->get_option('cross_relate')) {
            $args['post_type'] = $yarpp->get_post_types();
        } else {
            $args['post_type'] = array('page');
        }

        return yarpp_related_exist($args, $reference_ID);
    }
}

if (! function_exists('related_entries_exist')) {
    function related_entries_exist($args = array(), $reference_ID = false)
    {
        global $yarpp;

        $args['post_type'] = $yarpp->get_post_types();

        return yarpp_related_exist($args, $reference_ID);
    }
}