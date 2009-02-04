<?php /*
List template
This template returns the related posts as a comma-separated list.
Author: mitcho (Michael Yoshitaka Erlewine)
*/ ?>
<h3>Related Posts</h3>

<?php if ($related_query->have_posts()):
	$postsArray = array();
	while ($related_query->have_posts()) : $related_query->the_post();
		$postsArray[] = '<li><a href="'.get_the_permalink().'" rel="bookmark">'.get_the_title().'</a><!-- ('.get_the_score().')--></li>';
	endwhile;
	
echo implode(', ',$postsArray); // print out a list of the related items, separated by commas

else:
<p>No related posts.</p>
<?php endif; ?>
