<?php /*
List template
Author: mitcho (Michael Yoshitaka Erlewine)

This is an example template 
*/ ?>

<h3>Related Posts</h3>

<?php if ($related_query->have_posts()): //<===== IF there are any related posts...
	$postsArray = array();
	while ($related_query->have_posts()) : $related_query->the_post(); //<===== FOR EACH related post...
		$postsArray[] = '<li><a href="'.get_the_permalink().'" rel="bookmark">'.get_the_title().'</a></li>'; // put each related entry in the array
	endwhile; //<===== END FOR EACH related post
	
echo implode(', ',$postsArray); // print out a list of the related items, separated by commas

else: //<===== IF there are NO related posts...?>
<p>No related posts.</p>
<?php endif; //<===== FINISH!?>
