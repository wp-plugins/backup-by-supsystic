=== Backup by Supsystic ===
Contributors: supsystic.com
Donate link: http://supsystic.com/plugins/backup-plugin/
Tags: backup, back up, restoration, db backup, dump, file, migrate, schedule, email, FTP, mysql backup, website backup, database backup, db backup, wordpress backup, full backup, restoration, restore, rollback, transfer, website backup, wordpress backup 
Tested up to: 4.1
Stable tag: 1.0.3

Online backup, restoration or migrate solution. Fully customized backup files and database to the FTP or Google Drive

== Description ==

Backup WordPress website to the FTP, Google Drive or Local Computer and restore in two clicks. With Backup plugin by Supsystic make keeping a copy of your site's data on hand extraordinarily simple.

= Backup plugin features =

* Backup to FTP, Google Drive
* Google Drive cloude service backup
* Customisation and presets. Database backup, plugins, WordPress core files backup
* Backup in archive with .zip
* WordPress website restoration and migration via backup

Why do you need to backup your site?
Any number of undesirable events can happen:

* If you update the core WordPress files or a plugin, sometimes the update may not run properly and you may need to restore your backup & start again,
* If you’re tinkering with your site and playing in areas that perhaps you shouldn’t be (e.g. in the functions.php file), depending on the severity of the problem, you may need to restore your backup,
* If your site gets hacked, having a backup to restore to will mean all your hard work doesn’t go down the drain.

== Installation ==

= First time Backup by Supsystic user =

Thank you for choosing Backup by Supsystic! Open page of our plug-in admin panel WordPress and you will see two menu items: "Main" and "Backups". 

In order to create your backup, at first you need to choose where to backup - on the Main tab you have a choice between FTP and Google Drive backup. Then you wiil see the Backup presets. Here you can set what exatly you want to backup (full backup or backup specific folders). It is already possible to exclude some folders from backup, activate email notification and set the warehouse where to save backup. After all these points you need to click "Start backup" button. When you see the message "Backup complete", you can check the folder with backup. By default backup is stored in upsupsystic folder (you can find it using this path /wp-content/upsupsystic/), there you should see the archive with backup, log file and .sql file (sql file will be created only if you set the database backup). All this files will be with the same ID in the name.
On the Backups tab you have the ability to restore, download or delete backup, simply click on the appropriate button. 

To create Google Drive backup, at first you need to click the “Authenticate” button. On your Google Disk will be created “Backup by Supsystic” folder with one more folder inside (folder with the name of your site), where the backups will be stored.

= To install a plugin via FTP, you must = 

1. Download the plugin
2. Unarchive the plugin
3. Copy the folder with plugin
4. Open ftp \wp-content\plugins\
5. Paste the plug-ins folder in the folder
6. Go to admin panel => open item "Plugins" => activate the plugin 

== Screenshots ==

1. Backup plugin admin interface

== Changelog ==

= 1.0.3 =
 * First release on WordPress.org