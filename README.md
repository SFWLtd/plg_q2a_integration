Single-sign-on integration between Joomla! and Question2Answer
==============================================================

* Joomla! (http://www.joomla.org) is one of the world's leading CMS platforms.
* Question2Answer (http://www.question2answer.org) is a platform for providing a Question and Answer web forum.

This package contains code to integrate these two pieces of software and facilitate running a Question2Answer Q&A board within a Joomla! powered site.

The package consists of a Joomla! plugin and instructions for using it to integrate with Question2Answer.


Functionality
-------------

The key features of this plugin:

* Single-sign-on integration between Joomla! and Question2Answer, using Joomla's user accounts to grant access to the Q2A board.
* Mapping of Joomla's Access Levels to Question2Answer's permissions structures.
* Display Joomla! user group name along with user name in Question2Answer.


Installation and Usage
----------------------

1. Install Joomla! and run run the installer to the point where you can access the admin panel.

2. Install the Joomla! plugin:
   * Make sure you have suitable Joomla! Access Levels for each user type you want to support (ie end users, moderators, super-users, etc), and have configured the users so that they will have the correct access level.
   * Create a zip file of the plugin.
   * Open Joomla! admin, and use the plugin installer to upload and install plg_q2a_integration.zip.
   * Go to Extensions / Plugins admin menu, then search for Q+A to find the plugin. Open the config, and set the access levels to how you want them.

3. Install Question2Answer in a sub-directory of the Joomla! installation (this section is similar to points 1-5 in the official Question2Answer single-sign-on instructions (see http://www.question2answer.org/single-sign-on.php).
   * Download and unzip the Question2Answer files to your server.
   * Set up the database for it if necessary. You can share the same database as Joomla! if you wish or create a separate database.
   * Find qa-config-example.php in the Question2Answer root and rename it qa-config.php.
   * Open this file and find the line that sets ```QA_EXTERNAL_USERS```, and change it so that it is set to ```true```.
   * **Do not run the Question2Answer installer yet.**

4. Install the integration code into Question2Answer (this section replaces the remaining points in http://www.question2answer.org/single-sign-on.php).
   * Find the folder named qa-external-example and rename it qa-external.
   * Find the file q-external-users.php within this folder, and open it in a text editor.
   * Replace the entire contents of this file with the following code (you may want to keep a backup copy of the original if you're worried about doing this):

```
<?php
//Find the Joomla! path (if Q2A is installed correctly in subdirectory of Joomla, Joomla's root directory should be a level above the parent of the current folder).
$joomlaPath = dirname(dirname(__DIR__));
if (!file_exists($joomlaPath.'/configuration.php')) {
    exit("Could not find Joomla! root directory.");
}
//Find the Joomla! plugin
$pluginPath = $joomlaPath.'/plugins/q2a/qaintegration';
if (!file_exists($pluginPath.'/qaintegration.php')) {
    exit("Could not find Joomla! qaintegration plugin.");
}
//Include the 
require ($pluginPath.'/qa-external/qa-external-users.php');
```

5. You should now be able to run Question2Answer's installation script by going to its URL in your browser.


Limitations
-----------

* For this integration to work, the Question2Answer software must be installed in a sub-directory of the root folder of the Joomla! application it is being integrated into. This integration does not support hosting your Q2A board on a separate server or even on the same server but under a separate domain name.
* Logging out from Q2A requires the Joomla! site to have a /login.html URL. By default, Joomla! does not provide this. The easiest way to set this up is to install the QuickLogout extension (http://extensions.joomla.org/extensions/extension/quick-logout)
* Question2Answer requires you to manually edit its config files in order to activate its single-sign-on functionality. I have endeavoured to minimise the amount of code editing that is required to get up and running, but a small amount will be required. This will only be resolved if I can build Joomla! integration into the Q2A core, as it currently is with Wordpress.
* Display of group name as part of the user name in Q2A works best if users are only members of one group that qualifies them for access. This is because it will only display one group name; if he is a member of multiple groups, it may not show the one you expect.
* This code has been tested against the current versions of Joomla! and Question2Answer (3.4.8 and 1.7.3 respectively at the time of writing). It may or may not work with other versions. Please follow best practice and test it thoroughly for yourself before deploying to a production system.


Who wrote this?
---------------

This code was written by Simon Champion, at SFW Ltd (http://sfwltd.co.uk) as part of a client project.

All code in this repository is released under the GPLv2 licence. The GPLv2 licence document should be included with the code.


Support
-------

Please note that we can only offer formal support for this software for paying clients. Open source users may feel free to report issues and submit pull requests via the Github repository, but note that we cannot guarantee a response. If you require formal support, please contact us via our website (http://sfwltd.co.uk) for more information.


Todo
----

* Improve the group name display (and make it optional in config)
* Any other config options to add?
* Allow Q2A to inherit Joomla's database credentials rather than needing to set them in Q2A config manually (however, this will need core patches to Q2A rather than work in this plugin)
* Allow integration to work with Q2A on a different subdomain (will probably need to use Joomla's XMLRPC API instead of direct calls)
* Write a search plugin for Joomla! to allow us to include Q2A search results in the main Joomla! search.


Trademarks and Licenses
-----------------------

* Joomla!® is a registered trademark of Open Source Matters, Inc.
* Joomla! is distributed under the GPLv2 licence.
* Question2Answer is distributed under the GPLv2 licence.
* This package is distributed under the GPLv2 licence. The GPLv2 licence document should be included with the code.
