=== Backup by Supsystic ===
Contributors: supsystic.com
Donate link: http://supsystic.com/plugins/backup-plugin
Tags: backup, back up, restoration, db backup, dump, migrate, email, FTP, mysql backup, database backup, db backup, full backup, restore, transfer, website backup, wordpress backup, migration, backup plugins, backup posts, backup pages , backup scheduler
Tested up to: 4.2.2
Stable tag: 1.3.1

Online backup, restoration or migrate solution. Custom backup files and database to the FTP, DropBox, Google Drive or Amazon S3. Backup secure option

== Description ==

Backup WordPress website to the FTP, DropBox, Google Drive or Local Computer and restore in two clicks. With [Backup plugin by Supsystic](http://supsystic.com/plugins/backup-plugin?utm_source=wordpress&utm_medium=description&utm_campaign=backup "Backup plugin by Supsytic") make keeping a copy of your site's data on hand extraordinarily simple.

= Backup plugin features =

* Backup to FTP
* Backup to DropBox, Google Drive, Amazon S3 cloud service backup
* Customisation and presets. Database backup, plugins, WordPress core files backup
* Backup in archive with .zip
* WordPress website restoration. Plugins, Posts, Databases, Themes and Files.
* WordPress migration
* Encrypted and Secure backups
* Backup Logs
* Secure Backup
* Backup Scheduler

Why do you need to backup your site?
Any number of undesirable events can happen:

* If you update the core WordPress files or a plugin, sometimes the update may not run properly and you may need to restore your backup & start again,
* If you’re tinkering with your site and playing in areas that perhaps you shouldn’t be (e.g. in the functions.php file), depending on the severity of the problem, you may need to restore your backup,
* If your site gets hacked, having a backup to restore to will mean all your hard work doesn’t go down the drain.

= Video Tutorial how to backup to FTP or DropBox =

[youtube http://www.youtube.com/watch?v=CWHpAjOkKp8]

= Support =

If you have any problem or feature request for the Backup plugin by Supsystic, please [let us know](http://supsystic.com/plugins/backup-plugin/#contact?utm_source=wordpress&utm_medium=contactus&utm_campaign=backup "Contact Us")!

= Translations in your language =

You have an incredible opportunity to get PRO version of the backup plugin for free. Make translation of the plugin and get the PRO version!

* English
* Galician
* Swiss-German
* Dutch 
* French

== Installation ==

= First time Backup by Supsystic user =

Thank you for choosing Backup by Supsystic! Open page of our plug-in admin panel WordPress and you will see two menu items: "Main" and "Backups". 

In order to create your backup, at first you need to choose where to backup - on the Main tab you have a choice between FTP, Google Drive, Dropbox, Amazon S3 and OneDrive backup. Then you will see the Backup presets. Here you can set what exatly you want to backup (full backup or backup specific folders). It is already possible to exclude some folders from backup, activate email notification and set the warehouse where to save backup. After all these points you need to click "Start backup" button. When you see the message "Backup complete", you can check the folder with backup. By default backup is stored in upsupsystic folder (you can find it using this path /wp-content/upsupsystic/), there you should see the archive with backup, log file and .sql file (sql file will be created only if you set the database backup). All this files will be with the same ID in the name.

On the Backups tab you have the ability to restore, download or delete backup, simply click on the appropriate button. 

To create Google Drive, Dropbox, or OneDrive backup, at first you need to click the “Authenticate” button. On your cloud service will be created “Backup by Supsystic” folder with one more folder inside (folder with the name of your site), where the backups will be stored. 

To create Dropbox backup, Google Drive or OneDrive backup, at first you need to click the “Authenticate” button. On your cloud service will be created “Backup by Supsystic” folder with one more folder inside (folder with the name of your site), where the backups will be stored. 

To create Amazon S3 backup, at first you need to get your access key ID and secret access key. Then enter the keys and name of the basket (where backups will be stored) in the appropriate fields, and click “Store Credentials” button.

= To install a plugin via FTP, you must = 

1. Download the backup plugin
2. Unarchive the plugin
3. Copy the backup-by-supsystic folder with plugin
4. Open ftp \wp-content\plugins\
5. Paste the plug-ins folder in the folder
6. Go to admin panel => open item "Plugins" => activate the plugin 

= How to Backup a WordPress site = 

*Step 1: Choosing the cloud storage*

Primarily on the Main tab you need to choose the cloud storage where you prefer backups to be stored With Backup plugin by Supsystic you can backup to:

* FTP server
* Google Drive
* Dropbox
* Amazon S3
* OneDrive

To create backup to Google Drive, Dropbox, Amazon S3 or OneDrive, at first you need to authenticate your account. For all cloud services (except Amazon S3) it is very easy – just click “Authenticate”.


If prompted request for permission to access to your account – press “Allow” button to give permissions to upload the backup files on your account.  Then on your cloud service will be created “Backup by Supsystic” folder with one more folder inside (folder with the name of your site), where the backups will be stored.

If you want to backup to the Amazon S3 – you need to know your access key ID, secret access key and name of the basket (which exists on Amazon S3) where backups will be stored. To get your access key ID and secret access key you must follow the next steps:

1. Open the IAM console.  https://console.aws.amazon.com/iam/home?#home 
2. From the navigation menu, click Users.
3. Select your IAM user name.
4. Click User Actions, and then click Manage Access Keys.
5. Click Create Access Key.
6. Your keys will look something like this:
	- Access key ID example: AKIAIOSFODNN7EXAMPLE
	- Secret access key example: wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
7. Click Download Credentials, and store the keys in a secure location.

After these steps – enter the keys and name of the basket in the appropriate fields, then click “Store Credentials” button.

*Step 2: Customization of Backup by Supsystic*

Choose the backup preset – what exactly you want to backup and how you want to backup:

1. Download the backup plugin
2. Unarchive the plugin
3. Copy the folder with plugin
4. Open ftp \wp-content\plugins\
5. Paste the plug-ins folder in the folder
6. Go to admin panel => open item "Plugins" => activate the Backup by Supsystic plugin 

* Full Backup
* WordPress Core – all folders and files backup, in the root directory, where the WordPress is installed, except the /wp-content folder
* Plugins folder
* Themes folder
* Uploads folder
* Any folder inside wp-content
* Safe update – if the checkbox is set up, the database backup will be performed. This will let the database backup work in the transaction mode, i.e. should there occur any failure during the data base recovery, no data from the data-base backup will be transferred to the data-base. The data-base backup recovery will occur if and only there were no failures during the process. If the checkbox is not set up the data-base backup will be performed without transaction mode
* Force backup – when backup is performed, the labels are usually put at the beginning of the file dump, such as: WordPress version for the backup; WordPress data-base version for the backup; the plugin version for the backup. At recovering, if the force has been off, the backup will not be performed, because it will constantly pop up with the message, that the version is incorrect (the version of WordPress, the version of WordPress data-base or the plugin version). If the force has been on, there will be no such system check and the recovery will be performed


You can select several items at one time. If you choose a full backup – all checkboxes will be automatically activated (including the database backup).

Besides you have the ability to:

* Create only Database backup
* Exclude files and folders from the backup
* Enable email notification – activate checkbox and enter the email address where you want to be notified about how did the backup process passed.
* Specify the warehouse where the data is to be backed up – if ‘Use relative path’ checkbox has been set up, the path will be set against in the root directory, where the WordPress is installed. If ‘Use relative path’ checkbox has been of, the full path to the disk root should be specified in the Warehouse field.
* Use relative path – if the checkbox has been set up, then the backup path must be specified in the Warehouse field against the root directory, where the WordPress is installed. if the checkbox has been off, then the backup path must be specified in the Warehouse field against the disk root.

*Step 3: Creating the backup*

After you have selected the cloud storage and have specified all the settings, click “Start Backup”. Wait while your site is being backed up. It can take a while if your website is large. It depends on your site and hosting how much time will take the process. While plugin is backing up your website, you are not able to create another backup.


When you see the message “Backup complete”, you can check the folder with backup. There you should see the archive with backup, log file and .sql file (sql file will be created only if you set the database backup). All this files will be with the same ID in the name.

On Backups tab will appear a new entry with Database ( if you marked Full Backup or Database Backup) and Filesystem backup files. On top will be shown where has been made backup, the ID of backup files, the date and time of the backup.  You can restore, download (only if it was backup to FTP server) or delete backup, simply click on the appropriate button.

Also here you can view the log file of backup – just clicking “Show Backup Log”.

*Step 4: Backup restoration*

In order to restore the backup you need to go to the Backups tab, select the backup files you want and click “Restore” button - at first for the Filesystem backup, then for Database backup. When you see the message “Done!” – restoration will be completed.

If the backup you want to restore is on your computer, then upload it to the folder on the FTP server where you store the other backups.  By default backup is stored in ‘upsupsystic’ folder (you can find it  in the root directory, where the WordPress is installed, using this path /wp-content/upsupsystic/). Then files of this backup will appear on the Backups tab and you will be able to restore them.

== Screenshots ==

1. [Backup plugin](http://supsystic.com/plugins/backup-plugin?utm_source=wordpress&utm_medium=screenshot&utm_campaign=backup "Backup Plugin") admin interface. Help you effectively backup your website and prepare for any future problems.

2. WordPress backup scheduler. You can backup the sites automatically based on a daily, weekly or monthly schedule.

3. Backup restoration. You can restore a backup in just one simple step, giving you a whole lot of peace of mind.

== Other Notes ==

= Complete Backup by Supsystic =

The WordPress Backup by Supsystic plugin backups in the full scope the WordPress site along with the attached files and database saving the most important part of the data, such as: 

* posts, 
* pages, 
* plugins, 
* images, 
* comments, etc.

= Managed offsite backups =

Additional load on the server backup plugins make when storing everything locally on the server to slow down the site and leave no room for the data you need. The Backup by Supsystic plugin does need no local storage for the WordPress backups, hence, should there be a server crashes, the entire WordPress site and its backups would be lost. So, what to do? Create an offsite backup!  To secure all the content when and if the site goes down the backup dropbox plugin Backup by Supsystic creates offsite backups to archive and place up to thirty WordPress backups at any particular point taken in time. The saved data is stored in the Backup by Supsystics` own servers and in addition all the copied go to Amazon S3 servers. Every WordPress backup has nine full copies maintained in the multiple independent data-centers.

= Easy Restore of Backups =

Should the site get hacked, the DropBox Backup by Supsystic plugin will easily restore all the data in no time automatically restoring the specific WordPress backup right onto the server. To verify the integrity of a WordPress backup version or to test backups before deploying them onto the server the Backup by Supsystic has a test-restore feature. The backup can be validated as the WordPress`s backup is temporarily restored on the Backup by Supsystic's own servers.

= Migration using Backup =

A simple migration feature designed to move simply to a new domain or host using backups is fully realized in the Backup by Supsystic. The work begins at the stored backup on the Backup by Supsystics` servers without making any damage to the original site; executed in several steps; yet a considerable amount of WordPress backups may be very easily migrated. A few minutes, a few clicks, and the selected WordPress backup version is moved to the new place and ready to work. Any version from the backup`s list is suitable for the job.

= Securing your Backup =

Multiple copies of WordPress backups as it is as well as Amazon S3 servers used in the Backup by Supsystic provide the best security system for the data ensuring redundancy of the given WordPress backups. All the backups are encrypted to perform better protection. The backup can be applied to Dropbox feature and to uploading backups to Dropbox account. Time limit for storing the chosen WordPress backup versions is beyond thirty days; exactly for this period the Backup by Supsystic archives the backups.

= Incremental Backups =

Should the site be more than scores of GB, in this case, an ordinary backup plugin does not work properly; a complete backup is done every time and each time, to up loading the data onto the server slowing the site down. To solve these tasks and reduce the load on the server and the size of the backups an incremental backup is used only once and only at the start of the Backup by Supsystic.

= Real-time Backup =

Real-time backup system Backup by Supsystic ensures that any change is saved together with the instant backup at the spot; very useful feature if the work is done in the field of electronic commerce needed to be backed up daily - no transaction is lost even if the site crashed between the scheduled backup. The Real-time backup works as follows, by means of listening to the triggers issued by the standard WordPress updates as follow:

* adding - updating posts, 
* adding - updating pages, 
* adding - updating users, 
* adding - updating media, etc. 

For example, some plugins, let`s take the “wooCommerce” create custom tables in the WordPress database without any protection from the regular real-time backups due to its overwhelming size, hence the Backup by Supsystic has a special feature to manage the ” wooCommerce” backups.

= WordPress Multisite (WPMU) Backup =

WordPress Multisite backup is supported by the Backup by Supsystic and performed in the backup of the entire network, instead of a lonely standing sub –site, without losing in the process any shared resource, plugins or themes to backup, restore, or migrate the WPMU site.

= Backup Monitoring =

The Backup by Supsystic is always monitoring the site to ensure that the scheduled backups are done in time. Should there be any important news, such as failure of the backup or some problems with the site operation, a notification will be sent by E-mail.

= Backup History =

Backup by Supsystic's history page comprises information about each backup recorded within a considerable time period, such as:

* list of plugins
* number of posts
* pages
* files 
* tables. 

Also the changes in the backup are highlighted making finding any particular update and its time really easy, including the use of screenshots for each and every backup.

= Test-Restore Backup =

Temporarily restoring backup on the test servers is a unique feature provided by the Backup by Supsystic to validate the backup and ensure the expected results; or used for the accurate backup restoring from an older backup identification.

*Check other WordPress plugins:*

* [Social Share Buttons by Supsystic](https://wordpress.org/plugins/social-share-buttons-by-supsystic/ "Social Share Buttons by Supsystic")
* [Data Tables Generator by Supsystic](https://wordpress.org/plugins/data-tables-generator-by-supsystic/ "Data Tables Generator by Supsystic")
* [Google Maps Easy](https://wordpress.org/plugins/google-maps-easy/ "Google Maps Easy")
* [Gallery by Supsystic](https://wordpress.org/plugins/gallery-by-supsystic/ "Gallery by Supsystic")
* [Backup by Supsystic](https://wordpress.org/plugins/backup-by-supsystic/ "Backup by Supsystic")
* [Lightbox by Supsystic](https://wordpress.org/plugins/lightbox-by-supsystic/ "Lightbox by Supsystic")
* [Slider by Supsystic](https://wordpress.org/plugins/slider-by-supsystic/ "Slider by Supsystic")
* [PopUp by Supsystic](https://wordpress.org/plugins/popup-by-supsystic/ "PopUp by Supsystic")
* [Security by Supsystic](https://wordpress.org/plugins/security-by-supsystic/ "Security by Supsystic")
* [Secure Login by Supsystic](https://wordpress.org/plugins/secure-login-by-supsystic/ "Secure Login by Supsystic")

== Changelog ==

= 1.3.1 / 01.07.2015 =
 * Important fix in database backup process
 * Fixed bug with excluding folders, when selected 'Wordpress Core' backup option
 * Fixed bug - correct show log of exist backups on 'Restore' tab
 * Minor issues fix
 * Code improvements

= 1.3.0 / 01.07.2015 =
 * Database backup process improved
 * Log system improved
 * Minor issues fix
 * Code improvements

= 1.2.9 / 12.06.2015 =
 * Added check is user logged in cloud service, if backup place is cloud
 * Fixed bug in visual part
 * Minor issues fix
 * Code improvements

 = 1.2.8 / 05.06.2015 =
 * Added Italian language
 * Added German language
 * Fix bug: didn't adding folders from exclude array to backup file, if these folder located in wp-content/plugins or wp-content/themes
 * Add method for creating affiliate link in module 'promo_supsystic' promo_supsysticBup::getProPluginURL()
 * Backup setting by default - 'Full backup'
 * Added Russian translation files
 * Update main language file
 * Minor issues fix
 * Code improvements

= 1.2.7 / 25.05.2015 =
 * Backup restoration bug fix
 * Fix Backup scheduler master
 * Minor bug fixed
 * Added Dutch and French languages

= 1.2.6 / 13.05.2015 =
 * Added Galician Language
 * 'FTP' backup renamed to 'Local Backup'
 * Updated main language file
 * Minor issues fix

= 1.2.5 07/05/2015 =
 * Backup create and schedule edit/create page - added warning, if on server don't exist extension for work with zip archive
 * Minor bug fixed

= 1.2.4 24/04/2015 =
 * Changes for language translate. Added de_CH lang files
 * Fixed bug with backup schedule module
 * Fixed website migration option
 * Backup to DropBox fixed
 * Minor bug fixes

= 1.2.2 24/04/2015 =
 * Clearing TMP directory after backup was created & before restore filesystem process was started
 * Minor bugs fix
 * Added backup translation file and option


= 1.2.1 20/04/2015 =
 * Code improvements
 * Added backup to FTP on the other server
 * Minor issues fix

= 1.2.0 15/04/2015 =
 * Backup encryption for security reason
 * Fixed bugs in WordPress migration option
 * Added video tutorial How to Backup the first time users

= 1.1.0 01/04/2015 =
 * Added check, when restoration process starting and notice, if restoring directory or files don't have permission to write
 * Code improvements
 * Minor issues fix

= 1.0.9 =
 * Fixed bug: long-term storage of 'Google Drive' & 'OneDrive' authorization
 * Fixed bug: show correct page title in tab of browser
 * Added - Overview tab
 * Removed unused code
 * Minor issues fix

= 1.0.8 =
 * Fixed bug: conflict 'GoogleDrive' module with other plugin, which using GoogleClient API
 * Fixed bug: save backup destination, when user clicked on Authenticate button in cloud services.
 * Added - description on main page 'To restore website backup, be sure that all files and folders in the core directory have writing permissions. Backup restoration can rewrite some of them'
 * Removed unused code
 * Minor issues fix

= 1.0.7 =
* Add backup to DropBox
* Database backup fixed
* Restoration via DropBox and Amazon S3 fixed
* Add backup log option

= 1.0.6 =
 * Fixed bug: restore backup, when backup destination set absolute path
 * Added - backup to Amazon S3 cloud service
 * Added - writing backup settings in log
 * Minor issues fix

= 1.0.5 =
 * Added - backup to OneDrive cloud service
 * Fixed bug: backup destination after change destination setting on main page
 * Fixed bugs: empty zip archive and don't created sql fle on 'Full backup'
 * Minor issues fix

= 1.0.4 =
* Fixed bug - Wordpress core backup
* Added - separator line on backups page
* Confirm action on delete and restore backup
* Saving backup destination on the main page
* Fixed bug - restore backup and backup to the new folder

= 1.0.3 =
 * First release on WordPress.org