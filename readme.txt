=== Yet Another Related Posts Plugin ===
Contributors: mitchoyoshitaka
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Plugin URI: http://mitcho.com/code/yarpp/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=66G4DATK4999L&item_name=mitcho%2ecom%2fcode%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&charset=UTF%2d8
Tags: related, posts, post, pages, page, RSS, feed, feeds
Requires at least: 2.3
Tested up to: 2.8.4
Stable tag: 3.0.9

Returns a list of related entries based on a unique algorithm for display on your blog and RSS feeds. A templating feature allows customization of the display.

== Description ==

Yet Another Related Posts Plugin (YARPP) gives you a list of posts and/or pages related to the current entry, introducing the reader to other relevant content on your site. Key features include:

1. **An advanced and versatile algorithm**: Using a customizable algorithm considering post titles, content, tags, and categories, YARPP calculates a "match score" for each pair of posts on your blog. You choose the threshold limit for relevance and you get more related posts if there are more related posts and less if there are less.
2. **Templating**: **New in 3.0!** A new [templating system](http://mitcho.com/blog/projects/yarpp-3-templates/) puts you in charge of how your posts are displayed.
3. **Caching**: **New in 3.0!** YARPP now caches the related posts for all the posts and thus has significantly improved performance over versions 2.x.
4. **Related posts in RSS feeds**: Display related posts in your RSS and Atom feeds with custom display options.
5. **Disallowing certain tags or categories**: You can choose certain tags or categories as disallowed, meaning any page or post with such tags or categories will not be served up by the plugin.
6. **Related posts and pages**: Puts you in control of pulling up related posts, pages, or both.

This plugin requires that your database run on MySQL 4.1 or greater.

**Other plugins by mitcho**: [HookPress](http://wordpress.org/extend/plugins/hookpress/), [Markdown for WordPress and bbPress](http://wordpress.org/extend/plugins/markdown-for-wordpress-and-bbpress/), [WP-Smartdate](http://wordpress.org/extend/plugins/wp-smartdate/).

== Installation ==

= Auto display on your website =

1. Copy the folder `yet-another-related-posts-plugin` into the directory `wp-content/plugins/` and (optionally) the sample templates inside `yarpp-templates` folder into your active theme.

2. Activate the plugin.

3. Find the Related Posts (YARPP) settings page in your `wp-admin`. If you see a message telling you to build your cache, please build your cache. Otherwise, you will see no related posts anywhere.

NOTE: If you run a large and complex site and/or worry about your SQL query volume, it may be better to simply make sure the "compute related posts on the fly" option is turned on and *not* try to build the cache all at once.

= Auto display in your feeds =

Make sure the "display related posts in feeds" option is turned on if you would like to show related posts in your RSS and Atom feeds. The "display related posts in feeds" option can be used regardless of whether you auto display them on your website (and vice versa).

= Widget =

Related posts can also be displayed as a widget. Go to the Design > Widgets options page and add the Related Posts widget. The widget will only be displayed on single entry (permalink) pages. The widget can be used even if the "auto display" option is turned off.

= Custom display through templates = 

New in version 3.0, YARPP allows the advanced user with knowledge of PHP to customize the display of related posts using a templating mechanism. More information is available [in this tutorial](http://mitcho.com/blog/projects/yarpp-3-templates/).

= Manual installation =

For advanced users with knowledge of PHP, there is also an [advanced manual installation option](http://mitcho.com/code/yarpp/manual.php).

== Frequently Asked Questions ==

If your question isn't here, ask your own question at [the Wordpress.org forums](http://wordpress.org/tags/yet-another-related-posts-plugin).

= Every page just says "no related posts"! What's up with that? =

1. Most likely you have "no related posts" right now as the default "match threshold" is too high. Here's what I recommend to find an appropriate match threshold: first, lower your match threshold in the YARPP prefs to something ridiculously low, like 1 or 0.5. Most likely the really low threshold will pull up many posts that aren't actually related (false positives), so look at some of your posts' related posts and their match scores. This will help you find an appropriate threshold. You want it lower than what you have now, but high enough so it doesn't have many false positives.

2. It is also possible that your related posts cache has not been built and the "compute related posts on the fly" option is also turned off. Please go to the Related Posts (YARPP) options page and either build the cache or turn on the "compute related posts on the fly" option.

= How do I turn off the match score next to the related posts? =

The match score display is only for administrators... you can log out of `wp-admin` and check out the post again and you will see that the score is gone.

If you would like more flexibility in changing the display of your related posts, please see the [templating tutorial](http://mitcho.com/blog/projects/yarpp-3-templates/).

= Does YARPP work with full-width characters or languages that don't use spaces between words? =

YARPP works fine with full-width (double-byte) characters, assuming your WordPress database is set up with Unicode support. 99% of the time, if you're able to write blog posts with full-width characters and they're displayed correctly, YARPP will work on your blog.

However, YARPP does have difficulty with languages that don't place spaces between words (Chinese, Japanese, etc.). For these languages, the "consider body" and "consider titles" options in the "Relatedness options" may not be very helpful. Using only tags and categories may work better for these languages.

= Does YARPP slow down my blog/server? =

A little bit, yes. YARPP 3.0 introduced a new caching mechanism which greatly reduces the hit of the computationally intensive relatedness computation. In addition, *I highly recommend all YARPP users use a page-caching plugin, such as [WP-SuperCache](http://ocaoimh.ie/wp-super-cache/).*

If you are worried about your database load, do not manually build the cache—simply leave the "on the fly" caching option on, and it will build itself over time.

= I use DISQUS for comments. I can't access the YARPP options page! =

The DISQUS plugin loads some JavaScript voodoo which is interacting in weird ways with the AJAX in YARPP's options page. You can fix this by going to the DISQUS plugin advanced settings and turning on the "Check this if you have a problem with comment counts not showing on permalinks" option.

= I get a PHP error saying "Cannot redeclare `related_posts()`" =

You most likely have another related posts plugin activated at the same time. Please disactivate those other plugins first before using YARPP.

= I turned off one of the relatedness criteria (titles, bodies, tags, or categories) and now every page says "no related posts"! =

This has to do with the way the "match score" is computed. Every entry's match score is the weighted sum of its title-score, body-score, tag-score, and category-score. If you turn off one of the relatedness criteria, you will no doubt have to lower your match threshold to get the same number of related entries to show up. Alternatively, you can consider one of the other criteria "with extra weight".

It is recommended that you tweak your match threshold whenever you make changes to the "makeup" of your match score (i.e., the settings for the titles, bodies, tags, and categories items).

= Are there any plugins that are incompatible with YARPP? =

Aside from the DISQUS plugin (see above), currently the only known incompatibility is [with the SEO_Pager plugin](http://wordpress.org/support/topic/267966). Users of SEO Pager are urged to turn off the automatic display option in SEO Pager and instead add the code manually.

Please submit similar bugs by starting a new thread on [the Wordpress.org forums](http://wordpress.org/tags/yet-another-related-posts-plugin). I check the forums regularly and will try to release a quick bugfix.

= Things are weird after I upgraded. =

I highly recommend you disactivate YARPP, replace it with the new one, and then reactivate it. If you continue to have trouble, please find 

== Localizations ==

YARPP is currently localized in the following languages:

  * Belarussian (`by_BY`) by [Fat Cow](http://www.fatcow.com)
  * Simplified Chinese (`zh_CN`) by Jor Wang (mail at jorwang dot com) of [jorwang.com](http://jorwang.com)
  * Cypriot Greek (`el_CY`) by Aristidis Tonikidis (yarpp-el at mitcho dot com) of [akouseto.gr](http://www.akouseto.gr)
  * Dutch (`nl_NL`) by Sybrand van der Werf (yarpp-nl at mitcho dot com)
  * French (`fr_FR`) by Lionel Chollet (yarpp-fr at mitcho dot com)
  * German (`de_DE`) by Michael Kalina (yarpp-de at mitcho dot com) of [3th.be](http://3th.be)
  * Greek (`el_EL`) by Aristidis Tonikidis (yarpp-el at mitcho dot com) of [akouseto.gr](http://www.akouseto.gr)
  * Italian (`it_IT`) by Gianni Diurno (yarpp-it at mitcho dot com) of [gidibao.net](http://gidibao.net)
  * Japanese (`ja`) by myself (yarpp at mitcho dot com)
  * Korean (`ko_KR`) by [Jong-In Kim](http://incommunity.codex.kr) (yarpp-ko at mitcho dot com)
  * Lithuanian (`lt_LT`) by [Karolis Vyčius](http://vycius.co.cc) (yarpp-lt at mitcho dot com)
  * Polish (`pl_PL`) by [Perfecta](http://perfecta.pro/wp-pl/)
  * Brazilian Portuguese (`pt_BR`) by Rafael Fischmann (yarpp-ptBR at mitcho.com) of [macmagazine.br](http://macmagazine.com.br/)
  * Russian (`ru_RU`) by Marat Latypov (yarpp-ru at mitcho.com) of [blogocms.ru](http://blogocms.ru)
  * Swedish (`sv_SE`) by Max Elander (yarpp-sv at mitcho dot com)
  * Vietnamese (`vi_VN`) by Vu Nguyen (yarpp-vi at mitcho dot com) of [Rubik Integration](http://rubikintegration.com/)
  * Uzbek (`uz_UZ`) by Ali Safarov (yarpp-uz at mitcho dot com) of [comfi.com](http://www.comfi.com/)
	
We already have localizers lined up for the following languages:

  * Danish
  * Spanish
  * Catalan
  * Indonesian
  * Hungarian
  * Romanian
  * Hebrew
  * Latvian

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
		* Simplified Chinese (`zh_CN`) by Jor Wang (mail at jorwang dot com) of [jorwang.com](http://jorwang.com)
		* German (`de_DE`) by Michael Kalina (yarpp-de at mitcho dot com) of [3th.be](http://3th.be)
	* The "show excerpt" option now shows the first `n` words of the excerpt, rather than the content ([by request](http://wordpress.org/support/topic/212577))
	* Added an `echo` parameter to the `related_*()` functions, with default value of `true`. If `false`, the function will simply return the output.
	* Added support for the [AllWebMenus Pro](http://wordpress.org/extend/plugins/allwebmenus-wordpress-menu-plugin/) plugin
	* Further internationalization: 
		* the donate button! ^^
		* overused words lists ([by request](http://wordpress.org/support/topic/159359))), with a German word list.
* 2.1.5
	* Bugfix: In certain scenarios, [related posts would be displayed in RSS feeds even when that option was off](http://wordpress.org/support/topic/216145)
	* Bugfix: The `related_*()` functions were missing the `echo` parameter
	* Some localization bugfixes
	* Localizations:
		* Japanese (`ja`) by myself ([mitcho (Michael Yoshitaka Erlewine)](http://mitcho.com))
* 2.1.6
	* Versioning bugfix - same as 2.1.5
* 3.0
  * Major new release!
  * Caching for better SQL performance
  * A new [templating feature](http://mitcho.com/blog/projects/yarpp-3-templates/) for custom related posts displays
  * Cleaned up options page
  * New and updated localizations
* 3.0.1
  * Bugfix: In some situations before YARPP options were updated, an `include` PHP error was displayed.
* 3.0.2
  * Bugfix: [Templating wasn't working with child templates.](http://wordpress.org/support/topic/265515)
  * Bugfix: In some situations, [SQL errors were printed in the AJAX preview displays](http://wordpress.org/support/topic/265728).
* 3.0.3
  * Reinstated the 3.0.1 bugfix for includes
  * Bugfix: Fixed encoding issue in keyword caching algorithm
  * Bugfix: One SQL query assumed `wp_` prefix on tables
  * Added Polish localization
  * Added note on DISQUS in readme
  * Making some extra strings localizable
  * Bugfix: [a problem with the Italian localization](http://wordpress.org/support/topic/265952)
* 3.0.4
  * A fix to the version checking in the options page - now uses Snoopy
  * Adding Dutch localization
* 3.0.5
  * Added link to manual SQL setup information [by request](http://wordpress.org/support/topic/266752)
  * Added Portuguese localization
  * Updated info on "on the fly" caching - it is *strongly recommended* for larger blogs.
  * Updated "incomplete cache" warning message so it is only displayed when the "on the fly" option is off.
* 3.0.6
  * Bugfix: [updated excerpting to use `wp_html_excerpt`](http://wordpress.org/support/topic/268934) (for WP 2.5+)
  * Bugfix: [fixed broken widget display](http://wordpress.org/support/topic/276031)
  * Added Russian (`ru_RU`) localization by Marat Latypov
  * Confirmed 2.8 compatibility
  * Added note on [incompatibility with SEO Pager plugin](http://wordpress.org/support/topic/267966)
* 3.0.7
  * Bugfix: additional bugfix for widgets.
  * Reinstating excerpt length by number of words (was switched to letters in 3.0.6 without accompanying documentation)
  * Localizations:
    * Updated Italian
    * Added Belarussian by [Fat Cow](http://www.fatcow.com)
  * Confirmed compatibility with 2.8.1
* 3.0.8
  * Bugfix: [a pagination issue](http://wordpress.org/support/topic/267350) (may not be completely fixed yet)
  * Bugfix: a quick bugfix for widgets, thanks to Chris Northwood
  * Added Korean and Lithuanian localizations
  * Bugfix: [when ad-hoc caching was off, the cached status would always say "0% cached" ](http://wordpress.org/support/topic/286395)
  * Bugfix: enabled Polish and Italian stopwords and [fixed encoding of Italian stopwords](http://wordpress.org/support/topic/288808).
  * Bugfix: `is_single` and other such flags are now set properly within the related posts Loop (as a result, now [compatible with WP Greet Box](http://wordpress.org/support/topic/288230))
  * Confirmed compatibility with 2.8.2
  * Bugfix: [the Related Posts metabox now respects the Screen Options](http://wordpress.org/support/topic/289290)
* 3.0.9
  * Added Uzbek, Greek, Cypriot Greek, and Vietnamese localizations
  * Further bugfixes for the [pagination issue](http://wordpress.org/support/topic/267350)
