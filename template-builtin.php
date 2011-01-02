<?php /*
YARPP's built-in "template"

This "template" is used when you choose not to use a template.

If you want to create a new template, look at templates/template-example.php as an example.
*/

$options = array(
	'before_title'=>"${domainprefix}before_title",
	'after_title'=>"${domainprefix}after_title",
	'show_excerpt'=>"${domainprefix}show_excerpt",
	'excerpt_length'=>"${domainprefix}excerpt_length",
	'before_post'=>"${domainprefix}before_post",
	'after_post'=>"${domainprefix}after_post",
	'before_related'=>"${domainprefix}before_related",
	'after_related'=>"${domainprefix}after_related",
	'no_results'=>"${domainprefix}no_results");
$optvals = array();
foreach (array_keys($options) as $option) {
	if (isset($args[$option])) {
		$optvals[$option] = stripslashes($args[$option]);
	} else {
		$optvals[$option] = stripslashes(stripslashes(yarpp_get_option($options[$option])));
	}
}
extract($optvals);

if ($related_query->have_posts()) {
	while ($related_query->have_posts()) {
		$related_query->the_post();

		$output .= "$before_title<a href='".get_permalink()."' rel='bookmark' title='Permanent Link: ".preg_replace('/\s*<br[ \/]*>\s*/i', ' ', get_the_title())."'>".get_the_title()."";
		if (current_user_can('manage_options') && $domain != 'rss')
			$output .= ' <abbr title="'.sprintf(__('%f is the YARPP match score between the current entry and this related entry. You are seeing this value because you are logged in to WordPress as an administrator. It is not shown to regular visitors.','yarpp'),round(get_the_score(),3)).'">('.round(get_the_score(),3).')</abbr>';
		$output .= '</a>';
		if ($show_excerpt) {
			$output .= $before_post .
			  yarpp_excerpt(get_the_excerpt(),$excerpt_length)
			  . $after_post;
		}
		$output .=  $after_title."\n";

	}
	$output = stripslashes(stripslashes($before_related)).$output.stripslashes(stripslashes($after_related));
} else {
	$output = $no_results;
}
