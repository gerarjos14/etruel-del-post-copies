# README #
Development version of [WP Delete Post Copies](https://wordpress.org/plugins/etruel-del-post-copies/)

This plugin searches duplicated posts by title or content, filtering by category and can permanently delete them with images or send them to the trash in manual mode or automatic scheduled with WordPress cron.

And as a special feature, the erasing images by two different manners, images attached to posts can be trash or delete permanently and also can delete images added in posts content by html tag .
The images in posts content can be deleted from the folder if they are hosted locally.

### Some Features ###
    Allows limit the query to avoid timeouts or high loads on the server when performing Mysql queries.
    Allows send to trash or delete permanently the posts or any post type, public or private as well images or attachments of every post.
    Also deletes custom meta fields values from postmeta table of each deleted post.
    Allows to delete attachments.
    Allows to search and permanently delete images in posts content if they are hosted locally.
    Allows to filter by post status, revisions or also inherit.
    Allows to filter on one or some categories. But if ignores categories, the query is very much quicker.
    You can select if it should be kept as original the first or the last duplicated post, deleting the others.
    Allows exclude posts to delete by post IDs.
    You can preview a table of posts before make the delete in manual mode.
    You can manually delete any single post from the preview table.

Is probable that if there is a large amount of duplicated posts, for the timeouts on each server, the query can be interrupted when is proceeding manually and therefore the log can’t be recorded. To avoid this decreases the “Limit per time” value. A value of 100 or 150 is suitable, but also with 10 at a time, works very well.

PLEASE MAKE BACKUPs OF YOUR DATABASE AND FILES BEFORE USE. This will avoid you many problems if something goes wrong.

### How do I get set up? ###
Find help and instructions at the link above.