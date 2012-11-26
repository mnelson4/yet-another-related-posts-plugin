<?php
/*
 * YARPP's built-in thumbnails template
 * @since 3.6
 *
 * This template is used when you choose the built-in thumbnails option.
 * If you want to create a new template, look at yarpp-templates/yarpp-template-example.php as an example.
 * More information on the custom templates is available at http://mitcho.com/blog/projects/yarpp-3-templates/
 */

$options = array( 'thumbnails_heading' );
extract( $this->parse_args( $args, $options ) );

global $_wp_additional_image_sizes;

// @todo: add support for other theme-specified sizes?
// if ( isset($_wp_additional_image_sizes['yarpp-thumbnail']) )
// 	$size = 'yarpp-thumbnail';
// elseif ( isset($_wp_additional_image_sizes['post-thumbnail']) )
// 	$size = 'post-thumbnail';

if ( isset($size) ) {
	$width = (int) $_wp_additional_image_sizes[$size]['width'];
	$height = (int) $_wp_additional_image_sizes[$size]['height'];
} else {
	$size = '120x120'; // the ultimate default
	$width = 120;
	$height = 120;
}
$margin = 5;
$width_with_margins = $width + 2 * $margin;
$height_with_text = $height + 50;
$extramargin = 7;

// @todo: specify default image
$default_image = get_header_image();

$output .= '<h3>Related Posts</h3>' . "\n";

if (have_posts()) {
	$output .= '<div class="yarpp-thumbnails-horizontal">' . "\n";
	while (have_posts()) {
		the_post();

		$output .= "<a class='yarpp-thumbnail' href='" . get_permalink() . "' title='" . the_title_attribute('echo=0') . "'>" . "\n";

		if ( has_post_thumbnail() )
			$output .= get_the_post_thumbnail( null, $size );
		else
			$output .= '<span class="yarpp-thumbnail-default"><img class="yarpp-thumbnail-default-wide" src="' . esc_url($default_image) . '"/></span>';
			// assume header images are wider than they are tall

		$output .= '<span class="yarpp-thumbnail-title">' . get_the_title() . '</span>';
		$output .= '</a>' . "\n";

	}
	$output .= "</div>\n";
} else {
	$output .= $no_results;
}

$output .= "
<style>
.yarpp-thumbnails-horizontal {
}
.yarpp-thumbnail, .yarpp-thumbnail-default, .yarpp-thumbnail-title {
	display: inline-block;
	*display: inline;
}
.yarpp-thumbnail {
	border: 1px solid rgba(127,127,127,0.1);
	width: {$width_with_margins}px;
	height: {$height_with_text}px;
	margin: {$margin}px;
	margin-left: 0px;
	vertical-align: top;
}
.yarpp-thumbnail > img, .yarpp-thumbnail-default {
	width: {$width}px;
	height: {$height}px;
	margin: {$margin}px;
	margin-bottom: 0px;
}
.yarpp-thumbnail-default {
	overflow: hidden;
}
.yarpp-thumbnail-default > img.yarpp-thumbnail-default-wide {
	height: {$height}px;
	max-width: none;
}
.yarpp-thumbnail-default > img.yarpp-thumbnail-default-tall {
	width: {$width}px;
	max-height: none;
}
.yarpp-thumbnail-title {
	font-size: 1em;
	max-height: 2.8em;
	line-height: 1.4em;
	margin: {$extramargin}px;
	margin-top: 0px;
	width: {$width}px;
	text-decoration: inherit;
}
</style>
";