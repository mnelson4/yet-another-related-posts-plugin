=== Yet Another Related Posts Plugin ===
Contributors: mitchoyoshitaka
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Plugin URI: http://mitcho.com/code/
Donate link: http://mitcho.com/code/
Tags: related, posts, post, pages, page
Requires at least: 2.1
Tested up to: 2.3.2
Stable tag: 1.1

Returns a list of the related entries based on keyword matches, limited by a certain relatedness threshold. Like the tried and true Related Posts pluginsÑjust better!

== Description ==

Yet Another Related Posts Plugin (YARPP) is the result of some tinkering with [Peter Bowyer's version](http://peter.mapledesign.co.uk/weblog/archives/wordpress-related-posts-plugin) of [Alexander Malov & Mike Lu's Related Entries plugin](http://wasabi.pbwiki.com/Related%20Entries). Modifications made include:

1. *Limiting by a threshold*: Peter Bowyer did the great work of making the algorithm use MySQL's [fulltext search](dev.mysql.com/doc/en/Fulltext_Search.html) score to identify related posts. But it currently just displayed, for example, the top 5 most "relevant" entries, even if some of them weren't at all similar. Now you can set a threshold limit for relevance, and you get more related posts if there are more related posts and less if there are less. Ha!
2. *Related posts and pages*: **New in 1.1!** Puts you in control of pulling up related posts, pages, or both.
3. *Being a better plugin citizen*: now it doesn't require the user to click some sketchy button to `alter` the database and enable a `fulltext key`. Using [`register_activation_hook`](http://codex.wordpress.org/Function_Reference/register_activation_hook), it does it automagically on plugin activation. Just install and go!
4. *Miscellany*: a nicer options screen, displaying the fulltext match score on output for admins, an option to allow related posts from the future, a couple bug fixes, etc.

== Installation ==

Just put it in your `/wp-content/plugins/` directory, activate, and then drop the `related_posts()` function in your [WP loop](http://codex.wordpress.org/The_Loop). Change any options in the Related Posts (YARPP) Options pane in Admin > Plugins. See Examples in Other Notes for sample code you can drop into your theme.

You can override any options in an individual instance of `related_posts` using the following syntax:

> `related_posts(limit, threshold, before title, after title, show excerpt, len, before excerpt, after excerpt, show pass posts, past only, show score);`

Most of these should be self-explanatory. They're also in the same order as the options on the YARPP Options pane.

Example: `related_posts(10, null, 'title: ')` changes the maximum related posts number to 10, keeps the default threshold from the Options pane, and adds `title:` to the beginning of every title.

There's also a `related_posts_exist()` function. It has three optional arguments to override the defaults: a threshold, the show password-protected posts boolean, and the past only boolean.

== Related pages ==

YARPP 1.1 introduces related pages through the `related_pages()` function. `related_pages()` optionally may take the same arguments that `related_posts()` does.

By default, `related_posts()` gives you back posts only, `related_pages()` gives you pages, and there's also a new `related_entries()` which gives you posts and pages. When the "cross-relate posts and pages" option is checked in the YARPP options panel, `related_posts()`, `related_pages()`, and `related_entries()` will give you exactly the same output.

There are also, as you may expect, new `related_pages_exist()` and `related_entries_exist()` functions.

== Examples ==

For a barebones setup, just drop `<?php related_posts(); ?>` right after `<?php the_content() ?>`.

On my own blog I use the following code with `<li>` and `</li>` as the before/after entry options:

>`<?php if (related_posts_exist()): ?>`
>`<p>Related posts:`
>`<ol>`
>`<?php related_posts();?>`
>`</ol>`
>`</p>`
>`<?php else: ?>`
>`<p>No related posts.</p>`
>`<?php endif; ?>`

Of course, if you change all the instances of `posts` above to `pages`, you'll get a related pages listing.

== Coming soon (probably) ==

1. Incorporation of tags and categories in the algorithm. I've gotten the code working, but I still need to think about what the most natural algorithm would be for weighing these factors against the mysql fulltext score currently used (and works pretty well, I must say).
2. 
3. Um, something else! Let me know if you have any suggestions for improvement. ^^

== Version log ==
1.0   Initial upload

1.0.1 Bugfix: 1.0 assumed you had Markdown installed

1.1   Related pages support! Also, uses `apply_filters` to apply whatever content text transformation you use (Wikipedia link, Markdown, etc.) before computing similarity.