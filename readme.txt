=== Backup by Supsystic ===
Contributors: supsystic.com
Donate link: http://supsystic.com/plugins/backup-plugin/
Tags: backup, back up, restoration, db backup, dump, file, migrate, schedule, email, FTP, mysql backup, website backup, database backup, db backup, wordpress backup, full backup, restoration, restore, rollback, transfer, website backup, wordpress backup, migration, backup plugins, backup posts, backup pages 
Tested up to: 4.1
Stable tag: 1.0.6

Online backup, restoration or migrate solution. Fully customized backup files and database to the FTP or Google Drive

== Description ==

Backup WordPress website to the FTP, Google Drive or Local Computer and restore in two clicks. With [Backup plugin by Supsystic](http://supsystic.com/plugins/backup-plugin/ "Backup plugin by Supsytic") make keeping a copy of your site's data on hand extraordinarily simple.

= Backup plugin features =

* Backup to FTP
* Google Drive cloude service backup
* Customisation and presets. Database backup, plugins, WordPress core files backup
* Backup in archive with .zip
* WordPress website restoration and migration via backup

Why do you need to backup your site?
Any number of undesirable events can happen:

* If you update the core WordPress files or a plugin, sometimes the update may not run properly and you may need to restore your backup & start again,
* If you’re tinkering with your site and playing in areas that perhaps you shouldn’t be (e.g. in the functions.php file), depending on the severity of the problem, you may need to restore your backup,
* If your site gets hacked, having a backup to restore to will mean all your hard work doesn’t go down the drain.

= Support =

If you have any problem or feature request for the Backup plugin by Supsystic, please [let us know](http://supsystic.com/contact-us/ "Contact Us")!

== Installation ==

= First time Backup by Supsystic user =

Thank you for choosing Backup by Supsystic! Open page of our plug-in admin panel WordPress and you will see two menu items: "Main" and "Backups". 
In order to create your backup, at first you need to choose where to backup - on the Main tab you have a choice between FTP, Google Drive, Dropbox, Amazon S3 and OneDrive backup. Then you will see the Backup presets. Here you can set what exatly you want to backup (full backup or backup specific folders). It is already possible to exclude some folders from backup, activate email notification and set the warehouse where to save backup. After all these points you need to click "Start backup" button. When you see the message "Backup complete", you can check the folder with backup. By default backup is stored in upsupsystic folder (you can find it using this path /wp-content/upsupsystic/), there you should see the archive with backup, log file and .sql file (sql file will be created only if you set the database backup). All this files will be with the same ID in the name.
On the Backups tab you have the ability to restore, download or delete backup, simply click on the appropriate button. 
To create Google Drive, Dropbox, or OneDrive backup, at first you need to click the “Authenticate” button. On your cloud service will be created “Backup by Supsystic” folder with one more folder inside (folder with the name of your site), where the backups will be stored. 

To create Amazon S3 backup, at first you need to get your access key ID and secret access key. To do this - follow next steps:

1. Open the IAM console.  https://console.aws.amazon.com/iam/home?#home 
2. From the navigation menu, click Users.
3. Select your IAM user name.
4. Click User Actions, and then click Manage Access Keys.
5. Click Create Access Key.
6. Your keys will look something like this:
	- Access key ID example: AKIAIOSFODNN7EXAMPLE
	- Secret access key example: wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
7. Click Download Credentials, and store the keys in a secure location.

After you have got the keys you need to enter them in the appropriate fields, also enter the name of the basket (which exists on Amazon S3), where the backups will be stored. And click “Store Credentials” button.

= To install a plugin via FTP, you must = 

1. Download the plugin
2. Unarchive the plugin
3. Copy the folder with plugin
4. Open ftp \wp-content\plugins\
5. Paste the plug-ins folder in the folder
6. Go to admin panel => open item "Plugins" => activate the plugin 

== Screenshots ==

1. Backup plugin admin interface

== Other Notes ==

= Complete Backup by Supsystic =

The WordPress Backup by Supsystic plugin backups in the full scope the WordPress site along with the attached files and database saving the most important part of the data, such as: 

* posts, 
* pages, 
* plugins, 
* images, 
* comments, etc.

= Managed offsite backups =

Additional load on the server backup plugins make when storing everything locally on the server to slow down the site and leave no room for the data you need. The Backup by Supsystic plugin does need no local storage for the WordPress backups, hence, should there be a server crashes, the entire WordPress site and its backups would be lost. So, what to do? Create an offsite backup!  To secure all the content when and if the site goes down the backup plugin Backup by Supsystic creates offsite backups to archive and place up to thirty WordPress backups at any particular point taken in time. The saved data is stored in the Backup by Supsystics` own servers and in addition all the copied go to Amazon S3 servers. Every WordPress backup has nine full copies maintained in the multiple independent data-centers.

= Easy Restore of Backups =

Should the site get hacked, the Backup by Supsystic plugin will easily restore all the data in no time automatically restoring the specific WordPress backup right onto the server. To verify the integrity of a WordPress backup version or to test backups before deploying them onto the server the Backup by Supsystic has a test-restore feature. The backup can be validated as the WordPress`s backup is temporarily restored on the Backup by Supsystic's own servers.

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

* [Slider by Supsystic](https://wordpress.org/plugins/slider-by-supsystic/ "Slider plugin")
* [Grid Gallery](https://wordpress.org/plugins/gallery-by-supsystic/ "Grid Gallery plugin")
* [Google Maps](https://wordpress.org/plugins/google-maps-easy/ "Google Maps plugin")
* [Popup plugin](https://wordpress.org/plugins/popup-by-supsystic/ "Popup plugin")
* [Security and Firewall](https://wordpress.org/plugins/security-by-supsystic/ "Security solution")

== Changelog ==

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