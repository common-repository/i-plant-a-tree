=== I Plant A Tree ===
Contributors: Hellstrom
Tags: ipat,widget,i plant a tree
Requires at least: 4.2.2
Tested up to: 5.8.2
Stable tag: 1.7.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0

Show how much CO2 you saved with "I Plant A Tree".

== Description ==

This plugin shows the count of planted trees via *I Plant A Tree*, as well as saved CO2. Use it as configurable widget for your sidebar or simply with a shortcode.

== Installation ==

1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to *Settings › I Plant A Tree* and insert your user name
1. Place `[ipat_widget]` in your posts **or** drag & drop the widget into your sidebar

The user name is the same with which you log in into your IPAT account.

There are some possibilities to configure the widget manually. Please see https://lightframefx.de/projects/i-plant-tree-wordpress-plugin/?lang=en for detailed instructions.

== Changelog ==

= 1.7.3 =
* Team widget is useable again

= 1.7.2 =
* Fix for changed API

= 1.7.1 =
* Minor CSS fixes

= 1.7 =
* Implemented modern widget look following website relaunch.

= 1.6 =
* The people behind IPAT relaunched their whole website and with it the old API was discarded. Unfortunately, the new API is not functioning properly and works only with an user name, and not with an user ID anymore. This update provides a new text field in settings for your IPAT user name. Please provide this name to get your widget working again.

= 1.5.4 =
* Security improvement: added rel="noopener" to outgoing links

= 1.5.3 =
* some code cleaning and a minor CSS fix

= 1.5.2 =
* Fix for missing images

= 1.5.1 =
* Fix for some SVN update confusion

= 1.5 =
* The widget now localizes texts automatically, depending on the language your wordpress installation is set to. Currently, only English and German are available.

= 1.4.3 =
* fix for php 5 notice in strict mode / deprecated warning in php 7

= 1.4.2 =
* fixed php notice because of missing index after last update

= 1.4.1 =
* added possibility to show widget for a team
* fixed shortcode widget not showing correctly after last update

= 1.4 =
* updated some function calls that will get deprecated in future php versions
* tested with Wordpress 4.9.1

= 1.3.2 =
* fixed error on plugin activation with Wordpress 4.5

= 1.3.1 =
* added settings link in plugin-list

= 1.3 =
* added more fetch methods, to ensure that data can be retrieved from server

= 1.2.1 =
* added spanish localization

= 1.2 =
* added language options for shortcodes

= 1.1.1 =
* checked compatibility with WP 4.3
* some code cleaning and a minor CSS fix

= 1.1 =
* added german localization

= 1.0.4 =
* show CO2=0 if userID not provided
* more CSS fixes

= 1.0.3 =
* several CSS fixes

= 1.0.2 =
* bugfix parse error on older php-versions

= 1.0.1 =
* sanitize all user input

= 1.0 =
* plugin can show widget in sidebar or via shortcodes in posts

== Upgrade Notice ==

= 1.7 =
Dieses Update bringt einen deutlich moderneren Look des Widgets mit sich.
This update brings a much more modern look to the widget.

= 1.7.1 =
Das 1.7.x Update bringt einen deutlich moderneren Look des Widgets mit sich.
The 1.7.x update brings a much more modern look to the widget.
