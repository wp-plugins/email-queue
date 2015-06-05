=== Email Queue by BestWebSoft ===
Contributors: bestwebsoft
Donate link: http://bestwebsoft.com/donate/
Tags: email, e-mail, mail, mailout, mail queue, mail query, manage mail, priority, priorities, send mail through smtp server, trash, trush, quiry, mayl, maile, qwiry, email queue plugin, queu, qeueu, emeil, imail, assign priorities, priority level, manage messages, trash messages, delete messages, outgoing messages, organize mail queue, remove plugin from queue, send mail through wp-mail server, send mail through php mail server
Requires at least: 3.1
Tested up to: 4.2.2
Stable tag: 1.0.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin allows you to manage email messages sent by BestWebSoft plugins. 

== Description ==

The plugin works with plugins produced by BestWebSoft team only.
You can assign priorities to plugins that send mail. There are three levels of priority. It gives you an opportunity to organize a simple and effective mail queue.
You can manage outgoing messages: trash them or delete completely. You can use searching, filtering and bulk operations with your mail.
You have an option to remove any of mail-capable plugin from this plugin's queue.

http://www.youtube.com/watch?v=iFX0fF9B65A

<a href="http://wordpress.org/plugins/email-queue/faq/" target="_blank">FAQ</a>

<a href="http://support.bestwebsoft.com" target="_blank">Support</a>

= Features =

* Actions: You can assign three levels of priority to every plugin that sends mail - high, normal and low.
* Actions: You can delete mail-capable plugin from the queue and restore it back.
* Actions: You can choose the method to send mail: wp-mail, php mail or smtp.
* Actions: You can choose to set automatically delete old messages from you Data Base.
* Actions: You can trash you messages, restore them then or delete completely.
* Actions: You can look up the receivers list of your mails.

= Translation =

* Russian (ru_RU)
* Ukrainian (uk)

If you would like to create your own language pack or update the existing one, you can send <a href="http://codex.wordpress.org/Translating_WordPress" target="_blank">the text of PO and MO files</a> for <a href="http://support.bestwebsoft.com" target="_blank">BestWebSoft</a> and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO files <a href="http://www.poedit.net/download.php" target="_blank">Poedit</a>.

= Technical support =

Dear users, our plugins are available for free download. If you have any questions or recommendations regarding the functionality of our plugins (existing options, new options, current issues), please feel free to contact us. Please note that we accept requests in English only. All messages in another languages won't be accepted.

If you notice any bugs in the plugin's work, you can notify us about it and we'll investigate and fix the issue then. Your request should contain URL of the website, issues description and WordPress admin panel credentials. Moreover we can customize the plugin according to your requirements. It's a paid service (as a rule it costs $40, but the price can vary depending on the amount of the necessary changes and their complexity). Please note that we could also include this or that feature (developed for you) in the next release and share with the other users then.
We can fix some things for free for the users who provide translation of our plugin into their native language (this should be a new translation of a certain plugin, you can check available translations on the official plugin page).

== Installation ==

1. Upload `email-queue` folder to the `/wp-content/plugins/` directory
2. Activate the plugin via the 'Plugins' menu in WordPress.
3. Plugin settings are located in 'BWS Plugins', 'Email Queue'.
4. The mail queue is located in 'Email Queue' menu on Dashboard.

== Frequently Asked Questions ==

= How to use the plugin? =

1. Install and activate the plugin.
2. Go to the plugin settings page ( on dashboard "BWS Plugins" -> "Email Queue" ) and edit the necessary options.
3. Go to the mail queue page ( on Dashboard "Email Queue" ) where you can:
	- see if there are any messages in queue
	- filter and sort your messages by plugin, status, priority or date
	- find out whether particular message was sent
	- look up mail receivers list for any message
	- trash, untrash, delete permanently you mails

= How can I make sure that the addressee has read my letter? =

This function will be added in the stable version of the plugin.

= Does this plugin work with and take mail into its queue from every other WP plugin that sends mail? =

For now, this plugin has been tested and works with free and Pro versions of four BestWebSoft plugins ('Contact Form', 'Sender', 'Subscriber' and 'Updater') by putting the mails of these plugins in its own queue. We hope that in future our plugin will be working with more mail-capable plugins. Also, every other WP plugin (that isn't listed above) can send its mail when our plugin is activated. It will be sent, just not via our queue.

= Why doesn't this plugin display mail of 'Contact Form', 'Sender', 'Subscriber' and 'Updater' plugins in its queue? =

Please make sure you have set mail-capable plugin's status to 'in queue' on this plugin settings page.

Also, to make this plugin work with other mail-capable plugins you should use the most recent versions of the plugins listed in this question. Please, make sure you use at least the following versions:

- version 3.80 of 'Contact Form' plugin, 
- version 1.31 of 'Contact Form Pro' plugin,
- version 0.7 of 'Sender' plugin, 
- version 1.0.2 of 'Sender Pro' plugin, 
- version 1.1.2 of 'Subscriber' plugin,
- version 1.20 of 'Updater' plugin and 
- version 1.11 of 'Updater Pro' plugin.

= Why are my letters sent so long? =

For sending letters in plugin we use wp_cron - Wordpress function for periodic execution of any planned actions. This function depends on the traffic of your site: the more visitors, the faster the letters will be sent.
Also some of your mails may have been assigned a low priority. In that case mail with low priority would have to wait until all 'High' and 'Normal' prioritized mail would be sent.

= Why am I unable to send letters to all users at the same time? =

1. Simultaneous sending of a large number of messages can slow down your site. 
2. Your site can be identified as a source of spamming, which can lead to blocking of your website or hosting-account.

= How does the plugin work on Multisite? =

To use the plugin on Multisite you should activate it for network. If you use Multisite installation, the plugin settings page and mail queue page will be displayed only on the Network Admin Dashboard. The list of plugins on the settings page would mark any plugin as 'active' if it's activated on a network or any site of the network.

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (<a href="http://support.bestwebsoft.com" target="_blank">http://support.bestwebsoft.com</a>). If no, please provide the following data along with your problem's description:

1. the link to the page where the problem occurs
2. the name of the plugin and its version. If you are using a pro version - your order number.
3. the version of your WordPress installation
4. copy and paste into the message your system status report. Please read more here: <a href="https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/edit"target="_blank">Instuction on System Status</a>

<a href="https://docs.google.com/document/d/1fzt1YMA0l7j4gmURCuDhp0AGISMYn5iWKOCTXTSsmC8/edit" target="_blank">View a Step-by-step Instruction on Email Queue Installation</a>.

== Screenshots ==

1. Plugin`s settings page with mail plugins list.
2. Plugin`s settings page with additional settings.
3. Mail queue page with the list of addressees. 

== Changelog ==

= V1.0.6 - 05.06.2015 =
* Update : BWS plugins section is updated.
* Update : We updated all functionality for wordpress 4.2.2.

= V1.0.5 - 22.04.2015 =
* Bugfix : We fixed bug with displaying the list of the letters.

= V1.0.4 - 05.02.2015 =
* Update : Compatibility with Subscriber Pro was added.
* Bugfix : Bug with PRO plugins priorities was fixed.
* Bugfix : Bug with sending Sender PRO plugin mail was fixed.

= V1.0.3 - 30.12.2014 =
* Update : BWS plugins section is updated.
* Update : We updated all functionality for wordpress 4.1.

= V1.0.2 - 21.08.2014 =
* Bugfix : Bug with errors on mailout list and plugin list pages on WP versions prior to 3.6 was fixed.

= V1.0.1 - 08.08.2014 =
* Bugfix : Security Exploit was fixed.

= V1.0.0 - 20.06.2014 =
* NEW : Russian language files were added to the plugin.
* Update : Database tables were renamed. 
* Update : The tructure of settings page was changed.

== Upgrade Notice ==

= V1.0.6 =
BWS plugins section is updated. We updated all functionality for wordpress 4.2.2.

= V1.0.5 =
We fixed bug with displaying the list of the letters.

= V1.0.4 =
Compatibility with Subscriber Pro was added. Bug with PRO plugins priorities was fixed. Bug with sending Sender PRO plugin mail was fixed.

= V1.0.3 =
BWS plugins section is updated. We updated all functionality for wordpress 4.1.

= V1.0.2 =
Bug with errors on mailout list and plugin list pages on WP versions prior to 3.6 was fixed.

= V1.0.1 =
Security Exploit was fixed.

= V1.0.0 =
Russian language files were added to the plugin. Database tables were renamed. The tructure of settings page was changed.
