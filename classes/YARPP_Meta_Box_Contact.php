<?php

class YARPP_Meta_Box_Contact extends YARPP_Meta_Box {
    public function display() {
		global $yarpp;

        $happy = ($yarpp->diagnostic_happy()) ? 'spin' : null;

		$out =
		'<ul class="yarpp_contacts">'.
            '<li>'.
                '<a href="http://wordpress.org/support/plugin/yet-another-related-posts-plugin" target="_blank">'.
                    '<span class="icon icon-wordpress"></span> '.__('YARPP Forum', 'yarpp').
                '</a>'.
            '</li>'.
            '<li>'.
                '<a href="http://twitter.com/yarpp" target="_blank">'.
        '<span class="icon icon-twitter"></span> '.__('YARPP on Twitter', 'yarpp').
                '</a>'.
            '</li>'.
            '<li>'.
                '<a href="https://www.facebook.com/YARPPRecommendationEngine" target="_blank">'.
                    '<span class="icon icon-facebook"></span> YARPP on Facebook'.
                '</a>'.
            '</li>'.
            '<li>'.
                '<a href="http://www.yarpp.com" target="_blank">'.
                    '<span class="icon icon-pro"></span> Learn more about YARPP'.
                '</a>'.
            '</li>'.
            '<li>'.
                '<a href="http://wordpress.org/support/view/plugin-reviews/yet-another-related-posts-plugin" target="_blank">'.
                    '<span class="icon icon-star '.$happy.'"></span> '.__('Review YARPP on WordPress.org', 'yarpp').
                '</a>'.
            '</li>'.
         '</ul>';

        echo $out;
	}
}