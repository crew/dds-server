=== Plugin Name ===
Plugin Name: ldap_login_password_and_role_manager
Plugin URI: http://ldap_login_password_and_role_manager.frankkoenen.com
Description: Provide WP authentication and role management from LDAP
Version: 1.0.8
Author: Frank Koenen
Author URI: http://frankkoenen.com
License: GPL2
Contributors: fkoenen@feweb.net
Donate link: http://www.frankkoenen.com/ldap-and-wordpress
Tags: LDAP, authentication, login
Requires at least: 3.0
Tested up to: 3.3.1
Stable Tag: 1.0.8

Enables LDAP for user authentication, password management and role management.

== Description ==

This plugin will enable WP authentication and role management from LDAP server.
Users who log in to your WP site are authenticated by first matching the
username input against an attribute to local the RDN.  Then a bind is performed
against the RDN.  Upon successful bind, authentication is approved.  Local WP
user management is engaged by initializing and maintaining user DB records.

Simply create your users in your LDAP directly. Define access rights or "roles"
to specific WP sites. When the user logs into the WP site, the local DB user
entry is created.

Use LDAP to define users in one place for access to one or many WP sites.

User passwords in the local DB are replaced with jumbled text and are unusable.
All password information is maintained in LDAP.

User information ( Display Name, Nick Name, Nice Name, First Name, Last Name )
are maintained in master form in LDAP and stored and updated (synced) to the
local DB when the user logs in.

== Features ==

* Supports OpenLDAP
* Supports TLS
* syslog debugging
* Centralized roles management.
* Password expirary management.
* Password reset management in user profile.
* Works with WP Multi-Site setup.

== Architecture ==

Simple LDAP adds an authentication filter to Wordpress that authentication requests must pass.
Consults LDAP for current role configuration.

== Installation ==

Dowload the plugin to your WP site.
Enabled the plugin.
Enter LDAP configuration information from the WP-Admin Settings menu.
Test LDAP configurations from the WP-Admin Settings menu.

== Frequently Asked Questions ==

= Is the password expirary feature optional? =

Yes. Simply leave the LDAP configuration attribute emtpy to disable this feature.

= Does the plugin offer an easy way to create new users in LDAP? =

Yes. The plugin accepts a URL reference to your LDAP administration interface.

= What do I do if my users are already defined in my WP database? =

Not a problem. The user_login value will be mapped in the plugin setup to an
attribute in LDAP. Create your LDAP person records with the same value on the
attribute you map to user_login. Then next time the user logs in, the LDAP will
begin to manage the records in the local DB as normal.

= Does the LDAP plugin supercede the base WP login? =

Yes. Once the plugin is enabled, authentication control is passed on to the LDAP system.

== Changelog ==

= 1.0 =
* Feb-2011, Initial release

= 1.0.2 =
* Apr-2011, Patch for user create for version 3.1.1

= 1.0.3 =
* May-2011, added ldap_login_password_and_role_manager_updatepassword_using_rootdn() method.
* cleared 'ldap_login_password_and_role_manager_password_is_expired' in ldap_login_password_and_role_manager_updatepassword() method.

= 1.0.4 =
* May-2011, updated ldap_login_password_and_role_manager_update_wp_user() method, boolean return value was incorrect.

= 1.0.5 =
* May-2011, added memberidmap logic to manage LDAP member id numbers in sync with Wordpress member id numbers

= 1.0.6 =
* Mar-2012, added admin setting for TLS encryption.

= 1.0.7 =
* Jul-2013, maintenance release.

= 1.0.8 =
* Nov-2013, maintenance release.

== Screenshots ==


