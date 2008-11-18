=== Yet Another Related Posts Plugin ===
Contributors: mitchoyoshitaka
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Plugin URI: http://mitcho.com/code/yarpp/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=66G4DATK4999L&item_name=mitcho%2ecom%2fcode%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&charset=UTF%2d8
Tags: related, posts, post, pages, page, RSS, feed, feeds
Requires at least: 2.3
Tested up to: 2.7
Stable tag: 2.1.3

Returns a list of the related entries based on a unique algorithm using titles, post bodies, tags, and categories. Now with RSS feed support!

== Description ==

Yet Another Related Posts Plugin (YARPP) gives you a list of posts and/or pages related to the current entry, introducing the reader to other relevant content on your site. Key features include:

1. *Limiting by a threshold*: Peter Bowyer did the great work of making the algorithm use MySQL's [fulltext search](http://dev.mysql.com/doc/en/Fulltext_Search.html) score to identify related posts. But it just displayed, for example, the top 5 most "relevant" entries, even if some of them weren't at all relevant. Now you can set a threshold limit for relevance, and you get more related posts if there are more related posts and less if there are less. Ha!
2. *Using tags and categories*: **New in 2.0!** The new 2.0 algorithm uses tags and categories. The new options screen puts you in control of how these factors should be used.
3. *Related posts in RSS feeds*: **New in 2.1!** Display related posts in your RSS and Atom feeds with custom display options.
4. *Disallowing certain tags or categories*: **New in 2.0!** You can choose certain tags or categories as disallowed, meaning any page or post with such tags or categories will not be served up by the plugin.
5. *Related posts and pages*: **New in 1.1!** Puts you in control of pulling up related posts, pages, or both.
6. *Simple installation*: **New in 1.5!** Automatically displays related posts after content on single entry pages without any theme tinkering.
7. *Miscellany*: a nicer options screen (including a sample display of the code that is produced **New in 2.0**), displaying the fulltext match score on output for admins, an option to allow related posts from the future, a couple bug fixes, etc.

== Installation ==

= Auto display on your website =

Since YARPP 1.5, you can just put the `yet-another-related-posts-plugin` directory in your `/wp-content/plugins/` directory, activate the plugin, and you're set! You'll see related posts in single entry (permalink) pages. If all your pages say "no related posts," see the FAQ.

= Auto display in your feeds =

Since YARPP 2.1, you can turn on the "display related posts in feeds" option to show related posts in your RSS and Atom feeds.

The "display related posts in feeds" option can be used regardless of whether you auto display them on your website (and vice versa).

= Widget =

Related posts can also be displayed as a widget. Go to the Design > Widgets options page and add the Related Posts widget. The widget will only be displayed on single entry (permalink) pages. The widget can be used even if the "auto display" option is turned off.

= Manual installation =

**This is an advanced feature for those comfortable with PHP.** 97% of users will be better served by the auto display options above.

If you would like to put the related posts display in another part of your theme, or display them in pages other than single entry pages, turn off "auto display" in the YARPP Options, then drop `related_posts()`, `related_pages()`, or `related_entries()` (see below) in your [WP loop](http://codex.wordpress.org/The_Loop). Change any options in the Related Posts (YARPP) Options pane in Admin > Plugins. See Examples in Other Notes for sample code you can drop into your theme.

There're also `related_posts_exist()`, `related_pages_exist()`, and `related_entries_exist()` functions, which return a boolean as expected.

**The `related_` functions**

By default, `related_posts()` gives you back posts only, `related_pages()` gives you pages, and there's `related_entries()` which gives you posts and pages. When the "cross-relate posts and pages" option is checked in the YARPP options panel, `related_posts()`, `related_pages()`, and `related_entries()` will give you exactly the same output.

The `related` functions can be used in conjunction to the regular "auto display" option.

**Customizing the "related" functions**

Since YARPP 2.1, you can specify some custom options for each instance of `related_*()`. The functions take two arguments: 1. an array with key-value pairs of options, and 2. a boolean called `echo`, with default value of `true`. If `echo` is set to `false`, the result will simply be returned back instead of echoed. 

For example: `related_*(array(key=>value, key=>value, ...),`(`true` or `false`)`)`.

The available keys in version 2.1 are (roughly in the same order as in the options page):

* The Pool:
	* `distags` => comma-delimited list of tag numbers which should be disallowed
	* `discats` => comma-delimited list of category numbers which should be disallowed
* Relatedness options:
	* `threshold` => the match threshold
	* `show_pass_post` => (`bool`) show password-protected posts
	* `past_only` => (`bool`) only past posts
	* `title` => 1 for "do not consider", 2 for "consider", 3 for "consider with extra weight"
	* `body` => 1 for "do not consider", 2 for "consider", 3 for "consider with extra weight"
	* `tags` => 1 for "do not consider", 2 for "consider", 3 for "require one common tag", 4 for "require multiple common tags"
	* `categories` => 1 for "do not consider", 2 for "consider", 3 for "require one common category", 4 for "require multiple common categories"
	* `cross_relate` => (`bool`) cross-relate posts and pages
* Display options:
	* `limit` => (`int`) maximum number of results
	* `before_related` => before related entries text
	* `after_related` => after related entries text
	* `before_title` => before related entry title text
	* `after_title` => after related entry title text
	* `show_excerpt` => (`bool`) show excerpt
	* `excerpt_length` => (`int`) the excerpt length
	* `before_post` => before each related entry text
	* `after_post` => after each related entry text
	* `order` => MySQL `ORDER BY ` field and direction
	* `no_results` => "no results" text
	* `promote_yarpp` => (`bool`) promote YARPP?
	* `show_score` => (`bool`) show the match score to admins

**Examples**

Customized `related_*()` functions can be used to build specialized related-post functionality into your WordPress-enabled site. Here are some examples to get you started:

* `related_posts(array('title'=>1,'body'=>1,'tags'=>1,'categories'=>3))`
	* This example will return posts with at least one common category (with no other considerations).
* `related_posts(array('show_pass_post'=>1))`
	* This example will return password-protected posts.
	* This is useful for a site with some members-only content. This command can be run within a `if ($membership == true)` type of conditional.
* `related_posts(array('order'=>'rand() asc','limit'=>1))`
	* This example will link to one random related post.
* `related_posts(array('discats'=>'`(all categories except one)`'))`
	* This example will give you related posts from only a certain category. (Although there are certainly much better ways to do this with other plugins or custom code.)

== Frequently Asked Questions ==

If your question isn't here, ask your own question at [the Wordpress.org forums](http://wordpress.org/tags/yet-another-related-posts-plugin).

= Every page just says "no related posts"! What's up with that? =

Most likely you have "no related posts" right now as the default "match threshold" is too high. Here's what I recommend to find an appropriate match threshold: first, lower your match threshold in the YARPP prefs to something ridiculously low, like 1 or 0.5. Make sure the last option "show admins the match scores" is on. Most likely the really low threshold will pull up many posts that aren't actually related (false positives), so look at some of your posts' related posts and their match scores. This will help you find an appropriate threshold. You want it lower than what you have now, but high enough so it doesn't have many false positives.

= Does YARPP work with full-width characters or languages that don't use spaces between words? =

YARPP works fine with full-width (double-byte) characters, assuming your WordPress database is set up with Unicode support. 99% of the time, if you're able to write blog posts with full-width characters and they're displayed correctly, YARPP will work on your blog.

However, YARPP does have difficulty with languages that don't place spaces between words (Chinese, Japanese, etc.). For these languages, the "consider body" and "consider titles" options in the "Relatedness options" may not be very helpful. Using only tags and categories may work better for these languages.

= Does YARPP slow down my blog/server? =

A little bit, yes. Every time you display a post with automatic display of related posts (or one of the `related_*()` functions) it will calculate the related posts, which can be a database-intensive operation. *I highly recommend all YARPP users use a page-caching plugin, such as [WP-SuperCache](http://ocaoimh.ie/wp-super-cache/).* For the majority of users, this type of caching will be enough to stem the performance issues.

If you have a large blog with many (>1000) posts or have many tags or categories, YARPP may noticibly affect your blog's performance, even with a caching plugin. For these large blogs, for the time being I recommend you disable the "consider tags" and "consider categories" options. Turning off any "disallow" tags or categories will also speed things up.

In the future I will be building a YARPP-internal cache system so that YARPP can calculate all the post-relations at one time and then re-use those results every time, rather than calculating them on the fly.

= I get a PHP error saying "Cannot redeclare `related_posts()`" =

You most likely have another related posts plugin activated at the same time. Please disactivate those other plugins first before using YARPP.

= I turned off one of the relatedness criteria (titles, bodies, tags, or categories) and now every page says "no related posts"! =

This has to do with the way the "match score" is computed. Every entry's match score is the weighted sum of its title-score, body-score, tag-score, and category-score. If you turn off one of the relatedness criteria, you will no doubt have to lower your match threshold to get the same number of related entries to show up. Alternatively, you can consider one of the other criteria "with extra weight".

It is recommended that you tweak your match threshold whenever you make changes to the "makeup" of your match score (i.e., the settings for the titles, bodies, tags, and categories items).

= XXX plugin stopped working after I installed YARPP! =

Please submit such bugs by starting a new thread on [the Wordpress.org forums](http://wordpress.org/tags/yet-another-related-posts-plugin). I check the forums regularly and will try to release a quick bugfix.

= Things are weird after I upgraded. Ack! =

I highly recommend you disactivate YARPP, replace it with the new one, and then reactivate it.

= Does YARPP come in different languages? =

YARPP has been [internationalized](http://codex.wordpress.org/Writing_a_Plugin#Internationalizing_Your_Plugin) as of version 2.1.1. No localizations have been made yet, but they are coming.

If you are a bilingual speaker of English and another language and an avid user of YARPP, I would love to talk to you about localizing YARPP! Localizing YARPP can be pretty easy using [the Codestyling Localization plugin](http://www.code-styling.de/english/development/wordpress-plugin-codestyling-localization-en). Please [contact me](mailto:yarpp@mitcho.com) *first* before translating to make sure noone else is working on your language. Thanks!

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
* 2.0.1
	* Bugfix: [`admin_menu` instead of `admin_head`](http://konstruktors.com/blog/wordpress/277-fixing-postpost-and-ozh-absolute-comments-plugins/)
	* Bugfix: [a variable scope issue](http://wordpress.org/support/topic/188550) crucial for 2.0 upgrading
* 2.0.2
	* Versioning bugfix (rerelease of 2.0.1)
* 2.0.3
	* Bugfix: [2.0.2 accidentally required some tags or categories to be disabled](http://wordpress.org/support/topic/188745)
* 2.0.4 - what 2.0 should have been
	* Bugfix: new fulltext query for MySQL 5 compatibility
	* Bugfix: updated `apply_filters` to work with WP 2.6
* 2.0.5
	* Further optimized algorithm - should be faster on most systems. Good bye [subqueries](http://dev.mysql.com/doc/refman/5.0/en/unnamed-views.html)!
	* Bugfix: restored MySQL 4.0 support
	* Bugfix: [widgets required the "auto display" option](http://wordpress.org/support/topic/190454)
	* Bugfix: sometimes default values were not set properly on (re)activation
	* Bugfix: [quotes in HTML tag options would get escaped](http://wordpress.org/support/topic/199139)
	* Bugfix: `user_level` was being checked in a deprecated manner
	* A helpful little tooltip for the admin-only threshold display
* 2.0.6
	* A quick emergency bugfix (In one instance, assumed existence of `wp_posts`)
* 2.1 - The RSS edition!
	* RSS feed support!: the option to automagically show related posts in RSS feeds and to customize their display, [by popular request](http://wordpress.org/support/topic/151766).
	* A link to [the Yet Another Related Posts Plugin RSS feed](http://wordpress.org/support/topic/208469).
	* Further optimization of the main SQL query in cases where not all of the match criteria (title, body, tags, categories) are chosen.
	* A new format for pushing arguments to the `related_posts()` functions.
	* Bugfix: [compatibility](http://wordpress.org/support/topic/207286) with the [dzoneZ-Et](http://wordpress.org/extend/plugins/dzonez-et/) and [reddZ-Et](http://wordpress.org/extend/plugins/reddz-et/) plugins.
	* Bugfix: `related_*_exist()` functions produced invalid queries
	* A warning for `wp_posts` with non-MyISAM engines and semi-compatibility with non-MyISAM setups.
	* Bugfix: [a better notice for users of Wordpress < 2.5](http://www.mattcutts.com/blog/wordpress-plugin-related-posts/#comment-131194) regarding the "compare tags" and "compare categories" features.
* 2.1.1
	* Bugfix: keywords with forward slashes (\) could make the main SQL query ill-formed.
	* Bugfix: Added an override option for the [false MyISAM warnings](http://wordpress.org/support/topic/211043).
	* Preparing for localization! (See note at the bottom of the FAQ.)
	* Adding a debug mode--just try adding `&yarpp_debug=1` to your URL's and look at the HTML source.
* 2.1.2
	* Bugfix: MyISAM override handling bug
* 2.1.3
	* Bugfix: Turned off [the experimental caching](http://wordpress.org/support/topic/216194#post-894440) which shouldn't have been on in this release...
	* Bugfix: an issue with the [keywords algorithm for non-ASCII characters](http://wordpress.org/support/topic/216078)
* 2.1.4
	* Bugfix: [Settings' sumbmit button took you to PayPal](http://wordpress.org/support/topic/214090)
	* Bugfix: Fixed [keyword algorithm for users without `mbstring`](http://wordpress.org/support/topic/216420)
	* Bugfix: `title` attributes were not properly escaped
	* Bugfix: [keywords did not filter tags](http://wordpress.org/support/topic/218211). (This bugfix may vastly improve "relatedness" on some blogs.)
	* Localizations:
		* Simplified Chinese (`zh_CN`)
		* Danish (`da_DK`)
	* The "show excerpt" option now shows the first `n` words of the excerpt, rather than the content ([by request](http://wordpress.org/support/topic/212577))
	* Added an `echo` parameter to the `related_*()` functions, with default value of `true`. If `false`, the function will simply return the output.
	* Added support for the [AllWebMenus Pro](http://wordpress.org/extend/plugins/allwebmenus-wordpress-menu-plugin/) plugin
	* Further internationalization (including the donate button and overused words lists ([by request](http://wordpress.org/support/topic/159359)))

== Future versions ==

The following feature requests have been made and may be incorporated into a future release. If you have a bug fix, please start a new thread on [the Wordpress.org forums](http://wordpress.org/tags/yet-another-related-posts-plugin).

* More customizeable displays so that you can, for example, add the date and comment count in the excerpt ([by request](http://wordpress.org/support/topic/156231))
* More localizations
* Sentece-aware excerpts, [by request](http://wordpress.org/support/topic/162465)