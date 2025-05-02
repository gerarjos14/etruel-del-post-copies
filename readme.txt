=== WP Delete Post Copies ===
Contributors: etruel, khaztiel, gerarjos14, vanbom  
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VTNR4BH8XPDR6  
Tags: posts, duplicates, delete, duplicated posts, remove copies  
Requires at least: 3.1.0  
Tested up to: 6.8
Stable tag: 6.0.1 
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

Delete duplicate posts by title or content, including attachments, with powerful filters. Supports manual and scheduled cleanups.

== Description ==

**WP Delete Post Copies** is a powerful tool to search and delete duplicate posts by comparing their titles or content. You can filter results by category or post status, choose which post to keep, and remove the rest — including their media files.

You can run deletions manually with previews or set up scheduled cleanups using WordPress cron jobs. This plugin also offers the unique feature of deleting locally hosted images from post content (`<img>` tags), in addition to attached media.

Maker of Campaigns of deletes. With every campaign can search and delete duplicated posts (types) by title or content on different categories and can permanently delete them with images or send them to the trash in manual mode or automatic squeduled with Wordpress cron.

> ⚠️ **Please backup your database and files before running deletions.**

=== Key Features ===

* Detects duplicates by **title**, **content**, or both.
* Supports **manual or scheduled** deletion via WordPress cron.
* Choose to keep the **first** or **last** post among duplicates.
* Filter by **categories**, **post types**, and **post status** (including revisions and inherit).
* Supports all post types (public or private).
* Choose to move **posts to trash** or **delete them permanently**, including attachments and images.
* Allows limiting queries to avoid server timeouts or heavy loads.
* It also deletes:
  - Attachments (media files linked to posts).
  - Locally hosted images found in `<img>` HTML tags in post content.
  - Custom metadata from the `postmeta` table.
* Preview posts before deletion.
* Exclude posts by specific IDs.
* Manually delete single posts from the preview table.
* Logs stored in tabs for better performance and quick reference.

== Why use WP Delete Post Copies? ==

Cleaning up duplicate content is essential for:

✅ **Improving SEO** — Avoid penalties from search engines due to repeated content.  
✅ **Optimizing database performance** — Fewer posts = faster queries.  
✅ **Reducing storage usage** — Delete media files tied to duplicates and save space.  
✅ **Maintaining editorial quality** — Keep your content clear and consistent.  
✅ **Saving time** — Automate cleanups and avoid tedious manual checks.

== Why use WP Delete Post Copies? ==

Is probable that if there is a large amount of duplicated posts, for the timeouts on each server, the query can be interrupted when is proceeding manually and therefore the log can't be recorded. To avoid this decreases the "Limit per time" value. A value of 100 or 150 is suitable, but also with 10 at a time, works very well.

**PLEASE MAKE BACKUPs OF YOUR DATABASE AND FILES BEFORE USE.**
This will avoid you many problems if something goes wrong.

== Add-On ==  
🔌 **[WP Delete Post Copies PRO](https://etruel.com/downloads/etruel-del-post-copies-pro/)**
Take your site cleanup to the next level.  
With **WP Delete Post Copies PRO**, you can not only remove duplicates — you can also schedule campaigns to automatically delete old posts based on a selected date.

Perfect for:  
✅ Implementing content retention policies (e.g., automatically delete posts older than 6 months).  
✅ Keeping your database lean and optimized continuously.  
✅ Improving SEO by removing outdated content.  
✅ Saving server space by deleting old media linked to posts.

**Additional PRO Features:**  
- Scheduled campaigns to delete old posts by selected date.  
- More filters and advanced configuration options.  
- Better performance on large sites with big databases.  
- Priority support and ongoing updates.

> 📢 Many users already trust our tools to keep their sites fast and free of duplicate content!  
**[Click here to learn more about WP Delete Post Copies PRO](https://etruel.com/downloads/etruel-del-post-copies-pro/)**


**DISCLAIMER:**
This plugin is designed to permanently delete posts, images, and other data. Use it with extreme caution.
The use of this plugin and its extensions is entirely at your own risk. We will not be held responsible for any issues arising from its use, including but not limited to: difficulties in operation, inaccuracies or incomplete results, data loss, compatibility problems, computer viruses, malicious code, or any other technical problems.
We are not liable for any direct, indirect, incidental, special, consequential, or punitive damages (including but not limited to lost profits, lost data, costs of replacement services, or missed business opportunities) resulting from the use of this plugin or any related tools, services, or linked resources — regardless of the cause or legal theory, even if we have been advised of the possibility of such damages.

== Installation ==
You can either install it automatically from the WordPress admin, or do it manually:

1. Upload the plugin to `/wp-content/plugins/` and unzip it.
2. Activate it from the **Plugins** menu in WordPress.
3. Go to **Deletes** on menu to start using it.

== Screenshots ==
1. Settings Page with Global options.
1. Campaigns of deletes Page.
1. Editing a Campaign.
1. Running a Campaign in manual mode to delete duplicates.
1. You can see a table with duplicated posts to delete and its details or attachments.  
1. You can delete a single post by mouse click and get details of this action by clicking the title.
1. The logs are also in a new tab saving time to load the page. Click on title to refresh.

== Changelog ==
= 6.0.1 Apr 28, 2025 =
* Rebranded the "WP Delete Oldest Post" addon to **[WP Delete Post Copies PRO](https://etruel.com/downloads/etruel-del-post-copies-pro/)**.
* Introduced internal **version control** for better upgrade handling and future compatibility.
* Improved plugin structure and internal architecture for better performance and maintainability.
* General code cleanup and optimization across all modules.
* Enhanced compatibility with latest WordPress versions and plugins.
* Bump to WP 6.8

= 6.0 Mar 29, 2025 =
* Major version. Important release & must update version.
* **First use should be in a test environment.**
* Fixes Vulnerabilities report.
* Many security improvements.
* Nonce added on erase logs actions.
* Refactored all Ajax avoiding use of WP_Ajax_Response objects.
* Refactored all raw SQL queries to use WordPress standard query functions for improved compatibility and security.
* Many bug fixes.
* Bump to WP 6.7.2

= 5.5 Mar 20, 2023 =
* Bug fix for compatibility in php 8.
* Bump to WP 6.2.0

= 5.4 Oct 7, 2021 =
* Lot of tweaks for PHP notices and warnings.
* Added Select2 js and css files in local directory.
* Added pot catalog and Spanish translation files.
* Updated icons and banners. :D
* Removed calls to external files.
* Improved security.
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
> This allows different filters for campaigns or even use the complement of deletion of old posts in a campaign and in other continue looking for duplicates.
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
6.0 Major version. Must update version. Must first be used in a test environment.