===  BuddyPress Hashtags LS ===
Contributors: lenasterg
Tags: buddypress, activity stream, activity, hashtag, hashtags
Requires at least: PHP 5.2, WordPress 3.5.2, BuddyPress 1.7
Tested up to: PHP 5.2.x, WordPress 3.8, BuddyPress 2
Stable tag: 1.1
License: GNU General Public License 3.0 or newer (GPL) http://www.gnu.org/licenses/gpl.html
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Q4VCLDW4BFW6L



Enables #hashtags linking within activity stream content and provides a "Popular Hashtags" widget and shortcode [ls_bp_hashtags].
Based on the idea of BuddyPress Activity Stream Hashtags (http://wordpress.org/extend/plugins/buddypress-activity-stream-hashtags/) but it also stores the hashtag in an extra table in order to allow other actions (Popular hashtags etc) and make lighter database queries.

== Description ==
This plugin will convert #hashtags references to a link (activity search page) posted to the activity stream.
It supports Unicode characters (tested with Greek languages).
In multisite installations also uses the tags and category of a blog post as hashtags.
It add on the top of the activity loop (activity page, group activity page and user activity page), the popular hashtags as links.
The visitor can see all related activity by clicking the hashtag.
There are 2 widgets available:
- Sitewide hashtags
- Current group hashtags


Works on the same filters as the @atusername mention filter (see Extra Configuration if you want to enable this on blog/comments activity) - this will convert anything with a leading #.


Warnings:
1.The plugin creates an extra table in the database which is not deleted if the plugin is unistalled or deleted.

Please note: accepted pattern is: `/(#\w+)/u` - all linked hashtags will have a css a.hashtag - does support unicode.


== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Activate the plugin at the plugin administration page
3. If you want change the default settings

== Frequently Asked Questions ==

= Does it support unicode characters? =

Yes it does.


= What url is used? =

you may define a slug for hashtags via the admin settings page




== Changelog ==

= 1.1 =
New widget: Current group hashtags
Fix the issue with activity of private groups.
Add hashtags cloud into group and user activity.
Change table schema. Added hide_sitewide, base_activity_component, activity_item_id to database table bp_hashtags
When an activity is marked as spam it gets deleted from the bp_hashtags table

= 1.0 =
Created based on BuddyPress Activity Stream Hashtags plugin behavior.
Added a database table bp_hashtags to hold the hashtags.
Added the "Popular Hashtags" widget
Greek translation added


== Extra Configuration ==

