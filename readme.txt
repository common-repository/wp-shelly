=== WP Shelly ===
Contributors: sosidee
Tags: shelly, shelly cloud, IoT device, shelly relay, shelly cloud api
Requires at least: 5.3.0
Tested up to: 6.1
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Connects your WP site to Shelly cloud to turn your IoT devices on/off via Shelly HTTP API.
Compatible with Elementor.

== Description ==

This plugin allows connections to a **Shelly** relay in order to:

* check the status of devices;
* turn the devices **ON**/**OFF**;

### USEFUL SCENARIO ###
You may want to use this plugin to give someone the access of your Shelly devices **in the simplest way**.
The device access is protected by the website login.
Enabled users do not need anything but to be registered in your website.


This plugin is compatible with **Elementor** from the version 2.0.
The Elementor widget is located in the *general* category.

*For security reason you must restrict the access to the web-controls to a specific user or users' role.*

This plugin requires the **Wordpress Rest API**.


== Screenshots ==

1. The Elementor widget


== Usage ==

**1)** From the administration console page of the plugin, enter:

* your Shelly authorization key
* your Shelly server URL
* the device ID
* the device channel
* the user or the users' role that can access to the device control

*It's strongly advised to authorize only trusted user(s) to control your device(s).*


**2)** Save the data and look for the *shortcode* displayed at the bottom of the page.
*Example of the shortcode:*
[shelly id=*123*]


**3)** Insert the shortcode in a post/page of your WP website.



For details about the configuration parameters, please refer to the <a href="https://shelly.cloud/documents/developers/shelly_cloud_api_access.pdf" target="_blank" title="click to open the PDF">Shelly Cloud API Manual</a>.

= Copyright Notice =
*SHELLY* is a trademark copyrighted by
Allterco Robotics LTD
103 CHERNI VRAH BLVD
1407 SOFIA
BULGARIA

== Frequently Asked Questions ==

= How many devices can I control ? =

As many as your website can handle.

= What kind of Shelly device can I control ? =

This plugin has been tested with Shelly2.5 relays.
Nevertheless, a device correctly responding to Shelly cloud API

- POST https://[server location link]/device/status/
- POST https://[server location link]/device/relay/control/

could be controlled by this plugin.

== Upgrade Notice ==

** Tested with latest WP version  **


== Changelog ==

= 2.0.0 =
* Added the feature to manage more devices
* Removed the italian translation

= 1.4.2 =
* Added a workaround to load the core file 'pluggable.php' before using the cache_users() function [WordPress 6.1 bug]

= 1.4.1 =
* Updated the internal library

= 1.4 =
* Minor updates

= 1.3 =
* Minor updates

= 1.2 =
* Client browser's requests are sent to Wordpress Rest API endpoints

= 1.1 =
* Improved the security of sensible data
* Implemented the compatibility with Elementor 2.x

= 1.0 =
First release
