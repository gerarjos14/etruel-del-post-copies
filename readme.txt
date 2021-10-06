=== WP Delete Post Copies ===
Contributors: etruel, vanbom, manuelge
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VTNR4BH8XPDR6
Tags: posts, copies, duplicated, duplicate posts, delete copies, delete, erase, cron, squedule, squedule delete
Requires at least: 3.1.0
Tested up to: 5.8.1
Stable tag: 5.3.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin searches duplicated posts by title or content, filtering by category and can permanently delete them with images or send them to the trash.

== Description ==
This plugin searches duplicated posts by title or content, filtering by category and can permanently delete them with images or send them to the trash in manual mode or automatic scheduled with Wordpress cron.

And as a special feature, the erasing images by two different manners, images attached to posts can be trash or delete permanently and also can delete images added in posts content by html tag <img>.  
The images in posts content can be deleted from the folder if they are hosted locally. 

= Some Features =

* Allows limit the query to avoid timeouts or high loads on the server when performing Mysql queries.
* Allows send to trash or delete permanently the posts or any post type, public or private as well images or attachments of every post.
* Also deletes custom meta fields values from postmeta table of each deleted post.
* Allows to delete attachments.
* Allows to search and permanently delete images in posts content if they are hosted locally.
* Allows to filter by post status, revisions or also inherit.
* Allows to filter on one or some categories.  But if ignores categories, the query is very much quicker.
* You can select if it should be kept as original the first or the last duplicated post, deleting the others. 
* Allows exclude posts to delete by post IDs.
* You can preview a table of posts before make the delete in manual mode.
* You can manually delete any single post from the preview table.

Is probable that if there is a large amount of duplicated posts, for the timeouts on each server, the query can be interrupted when is proceeding manually and therefore the log can't be recorded. To avoid this decreases the "Limit per time" value. A value of 100 or 150 is suitable, but also with 10 at a time, works very well.

PLEASE MAKE BACKUPs OF YOUR DATABASE AND FILES BEFORE USE.  This will avoid you many problems if something goes wrong.

= Add-On =
[WP-Delete Oldest Posts](http://etruel.com/downloads/wp-edel-oldest-post/) Allows to select a date to delete all posts published before that date and/or you can establish a period with a cron job to continuously deleting the old posts and just remains that period on database.  Example: I want to keep just the last six months of posts in my blog then the oldest are deleted.

DISCLAIMER:
This plugin deletes posts and/or images and other things. Use it with very much caution.
The use of this plugin and its extensions is at your own risk. I will not be liable of third party for difficulty in use, inaccuracy or incompleteness of information, use of this information or results arising from the use of it, computer viruses, malicious code, loss of data, compatibility issues or otherwise. I will not be liable to you or any third party of any direct, indirect, special incidental, consequential, exemplary or punitive damages ( including lost of profit, lost of data, cost to procure replacement services or business opportunities) arising out of your use of plugin, or any other thing I provide in the site or link to another, or any acts omissions, defect, deficit, security breaches, or delays, regardless of the basis of the claim or if I have been advised of the possibility of such damage or loss.

== Installation ==
You can either install it automatically from the WordPress admin, or do it manually:

1. Unzip plugin archive and put the folder into your plugins folder (/wp-content/plugins/).
2. Activate the plugin from the Plugins menu.

== Screenshots ==
1. Settings Page with Global options.
1. Campaigns of deletes Page.
1. Editing a Campaign.
1. Running a Campaign in manual mode to delete duplicates.
1. You can see a table with duplicated posts to delete and its details or attachments.  
1. You can delete a single post by mouse click and get details of this action by clicking the title.
1. The logs are also in a new tab saving time to load the page. Click on title to refresh.

== Changelog ==
= 5.3.3 Oct 6, 2021 =
* lot of tweaks for PHP notices and warnings.
* Added language catalog file.
* Updated icons and banners. :D
* Bump to WP 5.8.1

= 5.3.2 May 3, 2021 =
* Fixes wrong behaviors of Exclude Posts (types) by ID option.

= 5.3.1 Apr 29, 2021 =
* Bump to WP 5.7.1
* Many tweaks on CSS styles.

= 5.3 Apr 19, 2021 =
* Fixes some queries according with the selected options.
* Allows search duplicates by title AND content or one of both.
* Added many JS conditions to avoid wrong behaviors on running campaigns.
* Initially disabled GO button before save a campaign.
* Added Select2 field to allow select excluded post(types) by name.
* Fixed some behaviors on run all campaigns. 
* We've welcomed a new contributor to the etruel/netmdp team: @vanbom started helping us with development ;-D
* Updated plugin URI and author URI.
* Updated License library to support auto-updates.

= 5.2 Feb 20, 2019 =
* Tested to work with WordPress 5.1
* Updates Plugin updater class.
* Some tweaks in the license handler.

= 5.1 =
* Fixed issue of the "Ignore Categories" option.

= 5.0 =
* All code was improved from scratch.
* Added the functionality of deletion of posts by campaigns.
This allows different filters for campaigns or even use the complement of deletion of old posts in a campaign and in other continue looking for duplicates.
* The results and logs are in tabs inside each campaign editing.
* Fixed the issues reported with the cron malfunctions.

= 4.0.2 =
* Fixed "Warning: Cannot modify header information - headers already sent" that breaks login in some cases.
* Fixed issue deleting a single post in manual mode by table link.
* Updated Screenshots

= 4.0.1 =
* Tested Up to WP 4.2.2
* Fixed the site crash issue reported by asisrodriguez. Thanks!
* Better Readme file. (this :)
* New icons.

= 4.0 =
* Added options to search duplicates by post types.
* Category option only works with posts. (ToDo custom tax for post types)
* Added options to search duplicates by post status.
* Added option to delete images attached to a post.
* Added option to search and delete images in content before delete a post.
* Better style on table showing posts to delete.
* Added option to delete a single post by click.
* Fixed scheduled cron jobs.
* Almost all plugin recoded to make it pluggable to add-ons and Wordpress better practices.

= 3.10 =
* Added options to search duplicates for title or content. 
* Added scrolled log to bottom of page. 

= 3.02 =
* some fixes in main query. 

= 3.01 =
* Added Option for show posts that will be deleted.
* Added Option for Ignore Categories.
* Optimized querys in both cases, with and without categories.

= 3.0Beta =
* Added Categories Option for check duplicated only in selected categories.
* Fixed scheduled feature that work diferent in newers versions of WP.
* Thanks to "Neso" for his production. ;)

= 2.0 =
* Added scheduled feature, icons, and cleaned some codes.
* First public release.

= 1.0 =
* Initial plugin. Private. Based in others plugins like Deleted duplicated post and so on..
Just click for delete.	

== Upgrade Notice ==
1. 5.3.3 version. Fixed and tested with Wordpress 5.8.1
