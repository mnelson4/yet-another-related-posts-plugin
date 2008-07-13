=== Yet Another Related Posts Plugin ===
Contributors: mitchoyoshitaka
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Plugin URI: http://mitcho.com/code/yarpp/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=mitcho%40mitcho%2ecom&item_name=mitcho%2ecom%2fcode%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: related, posts, post, pages, page
Requires at least: 2.1
Tested up to: 2.5.1
Stable tag: 2.0

Returns a list of the related entries based on keyword matches, limited by a certain relatedness threshold. New and improved, version 2.0!

== Description ==

Yet Another Related Posts Plugin (YARPP) gives you a list of posts and/or pages related to the current entry, introducing the reader to other relevant content on your site. Key features include:

1. *Limiting by a threshold*: Peter Bowyer did the great work of making the algorithm use MySQL's [fulltext search](dev.mysql.com/doc/en/Fulltext_Search.html) score to identify related posts. But it just displayed, for example, the top 5 most "relevant" entries, even if some of them weren't at all relevant. Now you can set a threshold limit for relevance, and you get more related posts if there are more related posts and less if there are less. Ha!
2. *Using tags and categories*: **New in 2.0!** The new 2.0 algorithm uses tags and categories. The new options screen puts you in control of how these factors should be used.
3. *Disallowing certain tags or categories*: **New in 2.0!** You can choose certain tags or categories as disallowed, meaning any page or post with such tags or categories will not be served up by the plugin.
4. *Related posts and pages*: **New in 1.1!** Puts you in control of pulling up related posts, pages, or both.
5. *Simple installation*: **New in 1.5!** Automatically displays related posts after content on single entry pages without any theme tinkering.
6. *Miscellany*: a nicer options screen (including a sample display of the code that is produced **New in 2.0**), displaying the fulltext match score on output for admins, an option to allow related posts from the future, a couple bug fixes, etc.

== Installation ==

= Auto display =

Since YARPP 1.5, you can just put the `yarpp` directory in your `/wp-content/plugins/` directory, activate the plugin, and you're set! You'll see related posts in single entry (permalink) pages. If all your pages say "no related posts," see the FAQ.

= Manual installation =

If you would like to put the related posts display in another part of your theme, or display them in pages other than single entry pages, turn off "auto display" in the YARPP Options, then drop `related_posts()`, `related_pages()`, or `related_entries()` (see below) in your [WP loop](http://codex.wordpress.org/The_Loop). Change any options in the Related Posts (YARPP) Options pane in Admin > Plugins. See Examples in Other Notes for sample code you can drop into your theme.

There're also `related_posts_exist()`, `related_pages_exist()`, and `related_entries_exist()` functions, which return a boolean as expected.

= The "related" functions =

By default, `related_posts()` gives you back posts only, `related_pages()` gives you pages, and there's `related_entries()` gives you posts and pages. When the "cross-relate posts and pages" option is checked in the YARPP options panel, `related_posts()`, `related_pages()`, and `related_entries()` will give you exactly the same output.

== Frequently Asked Questions ==

If your question isn't here, ask your own question at [the Wordpress.org forums](http://wordpress.org/tags/yet-another-related-posts-plugin).

= Every page just says "no related posts"! What's up with that? =

Most likely you have "no related posts" right now as the default "match threshold" is too high. Here's what I recommend to find an appropriate match threshold: first, lower your match threshold in the YARPP prefs to something ridiculously low, like 1 or 0.5. Make sure the last option "show admins the match scores" is on. Most likely the really low threshold will pull up many posts that aren't actually related (false positives), so look at some of your posts' related posts and their match scores. This will help you find an appropriate threshold. You want it lower than what you have now, but high enough so it doesn't have many false positives.

= A weird number is displayed after each related post. What is this? =

This is the match score for each of those entries, relative to the current entry. Don't worry, though--this is just being displayed because the "show admins the match scores" option is on (as it is by default) and only blog admins can see those scores. Your readers will not see these values. See above for how to use these values.

= XXX plugin stopped working after I installed YARPP! =

Please submit such bugs by starting a new thread on [the Wordpress.org forums](http://wordpress.org/tags/yet-another-related-posts-plugin). I check the forums regularly and will try to release a quick bugfix.

= Things are weird after I upgraded. Ack! =

I highly recommend you disactivate YARPP, replace it with the new one, and then reactivate it.

== Version log ==

* 1.0
	* Initial upload
* 1.0.1
	* Bugfix: 1.0 assumed you had Markdown installed
* 1.1
	* Related pages support!
	* Also, uses `apply_filters` to apply whatever content text transformation you use (Wikipedia link, Markdown, etc.) before computing similarity.
* 1.5
	* Simple installation: automatic display of a basic related posts install
	* code and variable cleanup
	* FAQ in the documentation
* 1.5.1
	* Bugfix: standardized directory references to `yet-another-related-posts-plugin`
* 2.0
	* New algorithm which considers tags and categories, by frequent request
	* Order by score, date, or title, [by request](http://wordpress.org/support/topic/158459)
	* Excluding certain tags or categories, [by request](http://wordpress.org/support/topic/161263)
	* Sample output displayed in the options screen
	* Bugfix: [an excerpt length bug](http://wordpress.org/support/topic/155034?replies=5)
	* Bugfix: now compatible with the following plugins:
		- diggZEt
		- WP-Syntax
		- Viper's Video Quicktags
		- WP-CodeBox
		- WP shortcodes
	
== Future versions ==

The following feature requests have been made and may be incorporated into a future release. If you have a bug fix, please start a new thread on [the Wordpress.org forums](http://wordpress.org/tags/yet-another-related-posts-plugin).

* User-defineable stopwords, especially to support other languages, [by request](http://wordpress.org/support/topic/159359)
* Widgetization, [by request](http://wordpress.org/support/topic/160459)
* Date and comment count in excerpt, [by request](http://wordpress.org/support/topic/156231)
* RSS feed support: an option to automagically show related posts in RSS feeds, [by request](http://wordpress.org/support/topic/151766).
* Sentece-aware excerpts, [by request](http://wordpress.org/support/topic/162465)