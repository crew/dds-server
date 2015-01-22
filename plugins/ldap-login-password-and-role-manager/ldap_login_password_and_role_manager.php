<?php
/*
Plugin Name: LDAP LPRM
Plugin URI: http://ldap_login_password_and_role_manager.frankkoenen.com
Description: Provide WP authentication and role management from LDAP
Version: 1.0.8
Author: Frank Koenen
Author URI: http://www.frankkoenen.com
Donate link: http://www.frankkoenen.com/ldap-and-wordpress
# File: ldap_login_password_and_role_manager.php
# vim: nowrapscan ic
#
# Purpose: Add LDAP authentication and role management to WP.
#
# History:
# 30-Dec-10 fhk; Init
# 10-May-11 fhk; patch to wp_update_user, wp_insert_user call
# 17-May-11 fhk; added ldap_login_password_and_role_manager_updatepassword_using_rootdn, cleared 'ldap_login_password_and_role_manager_password_is_expired'
#           fhk; in ldap_login_password_and_role_manager_updatepassword() method
# 26-May-11 fhk; updated ldap_login_password_and_role_manager_update_wp_user(), had incorrect return value.
# 07-Jul-13 fhk; updated to support userdn/userpw bind as option. Replaced all 'die' calls with return stats and related system logs.
#--------------------------------------------------
    Copyright 2011,2012,2013  Frank Koenen  (email : fkoenen@feweb.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
define('LDAP_LPRM_VERSION','1.0.8');

require_once ABSPATH . WPINC . '/registration.php';

function ldap_login_password_and_role_manager_menu() {
  include 'ldap_login_password_and_role_manager_admin.php';
}

function ldap_login_password_and_role_manager_menu_adduser() {
  include 'ldap_login_password_and_role_manager_adduser.php';
}

function ldap_login_password_and_role_manager_admin_actions() {
  add_options_page('LDAP Settings', 'LDAP Settings', 10, 'ldap_login_password_and_role_manager', 'ldap_login_password_and_role_manager_menu');
  $newuserredir = get_option('ldap_login_password_and_role_manager_newuserredirect');
  if ( $newuserredir.'x' != 'x' ) add_users_page('LDAP Add User', 'LDAP Add User', 10, 'ldap_login_password_and_role_manager_adduser', 'ldap_login_password_and_role_manager_menu_adduser');
}

function ldap_login_password_and_role_manager_add_options() {
  add_option('ldap_login_password_and_role_manager_base_dn', 'DC=mydomain,DC=local');
  add_option('ldap_login_password_and_role_manager_domain_controllers', 'dc01.mydomain.local');
  add_option('ldap_login_password_and_role_manager_loginattr', 'uid');
  add_option('ldap_login_password_and_role_manager_fnameattr','givenname');
  add_option('ldap_login_password_and_role_manager_lnameattr','sn');
  add_option('ldap_login_password_and_role_manager_emailattr','email');
  add_option('ldap_login_password_and_role_manager_passwordexpire', '');
  add_option('ldap_login_password_and_role_manager_newuserredirect', '');
  add_option('ldap_login_password_and_role_manager_use_tls', 'no');
  add_option('ldap_login_password_and_role_manager_memberidmap', '');
}

function ldap_login_password_and_role_manager_activation_hook() {

  // check if it is a network activation - if so, run the activation function for each blog id
  if (function_exists('is_multisite') && is_multisite() && isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
    global $wpdb;
    $ob = $wpdb->blogid;
    // Get all blog ids
    $blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
    foreach ($blogids as $k) {
      switch_to_blog($k);
      ldap_login_password_and_role_manager_add_options();
    }
    switch_to_blog($ob);
  } else
    ldap_login_password_and_role_manager_add_options();       

}
 
register_activation_hook( __FILE__, 'ldap_login_password_and_role_manager_activation_hook' );

define('LDAP_VERSION', 3);
# define LDAP_HOST and LDAP_PORT
function ldap_login_password_and_role_manager_dodefines() {
  global $ldap_login_password_and_role_manager_ldap;

  $controllers = explode(';',get_option('ldap_login_password_and_role_manager_domain_controllers'));
  $ldaphosts = ''; # string to hold each host separated by space
  $ldap_login_password_and_role_manager_ldap = null;
  foreach ( $controllers as $host ) {
    list($host,$port) = explode(':',$host,2);
    if ( (int)$port > 0 ) {
      define('LDAP_HOST', $host);
      define('LDAP_PORT', $port);
      break;
    }
    $ldaphosts .= $host . ' ';
  }
  if ( ! defined('LDAP_HOST') ) {
    define('LDAP_HOST', $ldaphosts);
    define('LDAP_PORT', 389);
  }
}
ldap_login_password_and_role_manager_dodefines();

# Setup filters and actions.
if ( 1 == 1 ) {

  # Add the menu
  add_action('admin_menu', 'ldap_login_password_and_role_manager_admin_actions');

  # Add the authentication filter
  add_filter('authenticate', 'ldap_login_password_and_role_manager_authenticate', 1, 3);

  # Add the login action
  add_action('login_form', 'ldap_login_password_and_role_manager_login_form');

  add_action('personal_options_update', 'ldap_login_password_and_role_manager_userprofile');
  add_action('edit_user_profile_update', 'ldap_login_password_and_role_manager_userprofile');

  add_action('show_user_profile','ldap_login_password_and_role_manager_show_extra_profile_fields', 9);
  add_action('edit_user_profile','ldap_login_password_and_role_manager_show_extra_profile_fields', 9);
 
  remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3); # only login via LDAP.

}

# Authenticate function we registered with WP
function ldap_login_password_and_role_manager_authenticate($user=null, $username='', $password='') {
  if ( is_a($user, 'WP_User') ) { return $user; } # already got a user object?
  
  # check arguments to this function ...
  if ( empty($username) || empty($password) ) {
    $error = new WP_Error();
    if ( empty($username) ) $error->add('empty_username', __('<strong Xldap_lprm="1">ERROR</strong>: Missing username value.'));
    if ( empty($password) ) $error->add('empty_password', __('<strong Xldap_lprm="1">ERROR</strong>: Missing password value.'));
    return $error;
  }
  
  # validate the password, get LDAP details on user.
  {
    list($auth_result,$info) = ldap_login_password_and_role_manager_can_authenticate($username, $password);
    if ( $auth_result != true ) {
      if ( is_a($auth_result, 'WP_Error')) return $auth_result;
      else {
        return new WP_Error('invalid_username', __('<strong Xldap_lprm="1">Login Error</strong>: Could not authenticate your credentials.'));
      }
    }
  }

  # create a WP DB record for this user ...
  {
    if ( ! ldap_login_password_and_role_manager_update_wp_user($username,$info) ) {
      $GLOBALS['ldap_login_password_and_role_manager_testmode_message'] .= "<br>The user is <u>not</u> defined in the realm for this site.";
      if ( $GLOBALS['ldap_login_password_and_role_manager_testmode'].'x' != 'yesx' ) {
        ldap_login_password_and_role_manager_logger(array('message'=>'function '.__FUNCTION__.'(): user ' . $username . ' user create failed.','priority'=>'local0.notice','tag'=>basename(__FILE__)));
        return new WP_Error('invalid_username', __('<strong Xldap_lprm="1">Login Error</strong>: Could not authenticate your credentials. Could be a realm violation.'));
      }
    } else {
      $GLOBALS['ldap_login_password_and_role_manager_testmode_message'] .= "<br>The user is correctly defined in the realm for this site.";
    }
  }

  # determine password expire logic ...
  {
    $passwordexpire = get_option("ldap_login_password_and_role_manager_passwordexpire");
    if ( ! empty($passwordexpire) && is_array($info[$passwordexpire]) ) {
      $gm_expiredate = preg_replace('/Z$/','',$info[$passwordexpire][0]);
      $now = gmdate('YmdHis');
      if ( $gm_expiredate <= $now ) {
        $GLOBALS['ldap_login_password_and_role_manager_testmode_message'] .= '<br>User password is expired. The user would be forced to reset their password upon login.';
        $GLOBALS['ldap_login_password_and_role_manager_password_is_expired'] = true;
      }
    }
  }

  if ( $GLOBALS['ldap_login_password_and_role_manager_testmode'].'x' == 'yesx' ) {
    $GLOBALS['ldap_login_password_and_role_manager_testmode_ok'] = true;
    return;
  } else {

    $user = get_userdatabylogin($username);
    # is the password expired and , and a new password has been provided, update the password ...
    if ( $GLOBALS['ldap_login_password_and_role_manager_password_is_expired'] ) {
      if (    $_POST['ldap_login_password_and_role_manager_password_is_expired'].'x' == 'truex'
           && $_POST['log'].'x' != 'x'
           && $_POST['pwd'].'x' != 'x'
           && $_POST['ldap_login_password_and_role_manager_new_password'].'x' != 'x'
           && $_POST['pwd'].'x' != $_POST['ldap_login_password_and_role_manager_new_password'].'x'
         ) {
        if ( ! ldap_login_password_and_role_manager_updatepassword( array( 'dn' => $info['dn'] , 'userid' => $_POST['log'] , 'ctpassword' => $_POST['pwd'] , 'ctnewpassword' => $_POST['ldap_login_password_and_role_manager_new_password']) ) ) {
          do_action( 'wp_login_failed', $username );        
          return new WP_Error('expired_password', __('<strong Xldap_lprm="1">Failed to reset Password</strong>'));
        }
      } else {
        do_action( 'wp_login_failed', $username );        
        return new WP_Error('expired_password', __('<strong Xldap_lprm="1">Expired Password</strong>'));
      }
    }

  }

  # Wordpress user exists, login ok ...
  return new WP_User($user->ID);
}

function ldap_login_password_and_role_manager_can_authenticate($username, $password) {
  global $ldap_login_password_and_role_manager_ldap;
  
  $username = trim($username);

  $result = false;

  if ( $username.'x' == 'x' || strtolower($username).'x' == 'adminx' ) {
    ldap_login_password_and_role_manager_logger(array('message'=>'aborting on invalid username: ' . substr($username,0,100) . ' in function '.__FUNCTION__.'()','priority'=>'local0.notice','tag'=>basename(__FILE__)));
    return array($result,null);
  }

  if ( ! $ldap_login_password_and_role_manager_ldap = ldap_connect(LDAP_HOST, LDAP_PORT) ) {
    ldap_login_password_and_role_manager_logger(array('message'=>'unable to connect to LDAP server in function '.__FUNCTION__.'()','priority'=>'local0.notice','tag'=>basename(__FILE__)));
    return array($result,null);
  }

  ldap_set_option($ldap_login_password_and_role_manager_ldap, LDAP_OPT_PROTOCOL_VERSION, LDAP_VERSION);
  ldap_set_option($ldap_login_password_and_role_manager_ldap, LDAP_OPT_REFERRALS, 0);

  if ( get_option('ldap_login_password_and_role_manager_use_tls') == 'yes' ) ldap_start_tls($ldap_login_password_and_role_manager_ldap);

  $base_dn = get_option('ldap_login_password_and_role_manager_base_dn');
  $userattr = get_option('ldap_login_password_and_role_manager_loginattr');
  $filter = '(' . $userattr . '=' . $username . ')';

  {
    $binddn = get_option('ldap_login_password_and_role_manager_binddn','');
    $bindpw = get_option('ldap_login_password_and_role_manager_bindpw','');
    if ( trim($binddn).'x' != 'x' && ! ldap_bind($ldap_login_password_and_role_manager_ldap, $binddn, $bindpw)) {
      ldap_login_password_and_role_manager_logger(array('message'=>'error in ldap bind in function '.__FUNCTION__.'(): '.ldap_error($ldap_login_password_and_role_manager_ldap),'priority'=>'local0.notice','tag'=>basename(__FILE__)));
      return array($result, null);
    }
  }

  if ( ! ($search = @ldap_search($ldap_login_password_and_role_manager_ldap, $base_dn, $filter)) ) {
    ldap_login_password_and_role_manager_logger(array('message'=>'error in ldap search in function '.__FUNCTION__.'(): '.ldap_error($ldap_login_password_and_role_manager_ldap),'priority'=>'local0.notice','tag'=>basename(__FILE__)));
    return array($result,null);
  } else {
    $number_returned = ldap_count_entries($ldap_login_password_and_role_manager_ldap,$search);
    if ( (int)$number_returned != 1 ) {
      ldap_login_password_and_role_manager_logger(array('message'=>'found too many (or too few) matches for filter '.$filter.' number found: '.$number_returned.' in function '.__FUNCTION__.'()','priority'=>'local0.notice','tag'=>basename(__FILE__)));
      return array($result,null);
    }
    $info = ldap_get_entries($ldap_login_password_and_role_manager_ldap, $search);
    $dn = $info[0]['dn'];
    if ( $bind = ldap_bind($ldap_login_password_and_role_manager_ldap, $dn, $password) ) $result = true;
    ldap_unbind($ldap_login_password_and_role_manager_ldap);
    return array($result,$info[0]);
  }

  return array($result,null);
}

function ldap_login_password_and_role_manager_get_user_dn($username) {
  global $ldap_login_password_and_role_manager_ldap;
  
  $result = false;

  if ( ! $ldap_login_password_and_role_manager_ldap = ldap_connect(LDAP_HOST, LDAP_PORT) ) {
    ldap_login_password_and_role_manager_logger(array('message'=>'unable to connect to LDAP server in function '.__FUNCTION__.'()','priority'=>'local0.notice','tag'=>basename(__FILE__)));
    return array($result,null);
  }

  ldap_set_option($ldap_login_password_and_role_manager_ldap, LDAP_OPT_PROTOCOL_VERSION, LDAP_VERSION);
  ldap_set_option($ldap_login_password_and_role_manager_ldap, LDAP_OPT_REFERRALS, 0);

  if ( get_option('ldap_login_password_and_role_manager_use_tls') == 'yes' ) ldap_start_tls($ldap_login_password_and_role_manager_ldap);

  $base_dn = get_option('ldap_login_password_and_role_manager_base_dn');
  $userattr = get_option('ldap_login_password_and_role_manager_loginattr');
  $filter = '(' . $userattr . '=' . $username . ')';

  {
    $binddn = get_option('ldap_login_password_and_role_manager_binddn','');
    $bindpw = get_option('ldap_login_password_and_role_manager_bindpw','');
    if ( trim($binddn).'x' != 'x' && ! ldap_bind($ldap_login_password_and_role_manager_ldap, $binddn, $bindpw)) {
      ldap_login_password_and_role_manager_logger(array('message'=>'error in ldap bind in function '.__FUNCTION__.'(): '.ldap_error($ldap_login_password_and_role_manager_ldap),'priority'=>'local0.notice','tag'=>basename(__FILE__)));
      return array($result, null);
    }
  }

  if ( ! ($search = @ldap_search($ldap_login_password_and_role_manager_ldap, $base_dn, $filter)) ) {
    ldap_login_password_and_role_manager_logger(array('message'=>'error in ldap search in function '.__FUNCTION__.'(): '.ldap_error($ldap_login_password_and_role_manager_ldap),'priority'=>'local0.notice','tag'=>basename(__FILE__)));
    return array($result,null);
  } else {
    $number_returned = ldap_count_entries($ldap_login_password_and_role_manager_ldap,$search);
    if ( (int)$number_returned != 1 ) {
      ldap_login_password_and_role_manager_logger(array('message'=>'found too many (or too few) matches for filter '.$filter.' number found: '.$number_returned.' in function '.__FUNCTION__.'()','priority'=>'local0.notice','tag'=>basename(__FILE__)));
      return array($result,null);
    }
    $info = ldap_get_entries($ldap_login_password_and_role_manager_ldap, $search);
    $dn = $info[0]['dn'];
    ldap_unbind($ldap_login_password_and_role_manager_ldap);
    return array($dn,$info[0]);
  }

  return array(null,null);
}

function ldap_login_password_and_role_manager_updatepassword_using_rootdn($args) {
  $dn = $args['dn'];
  $userid = $args['userid'];
  $ctpassword = $args['ctpassword'];
  $ctnewpassword = $args['ctnewpassword'];

  global $ldap_login_password_and_role_manager_ldap, $ldap_login_password_and_role_manager_adldap;

  $returnvalue = false;

  if ( ! $ldap_login_password_and_role_manager_ldap = ldap_connect(LDAP_HOST, LDAP_PORT) ) {
    ldap_login_password_and_role_manager_logger(array('message'=>'unable to connect to LDAP server in function '.__FUNCTION__.'()','priority'=>'local0.notice','tag'=>basename(__FILE__)));
    return $returnvalue;
  }

  ldap_set_option($ldap_login_password_and_role_manager_ldap, LDAP_OPT_PROTOCOL_VERSION, LDAP_VERSION);
  ldap_set_option($ldap_login_password_and_role_manager_ldap, LDAP_OPT_REFERRALS, 0);

  if ( get_option('ldap_login_password_and_role_manager_use_tls') == 'yes' ) ldap_start_tls($ldap_login_password_and_role_manager_ldap);

  if ( $bind = ldap_bind($ldap_login_password_and_role_manager_ldap, $userid, $ctpassword) ) {

    $newpassword = '{SHA}' . base64_encode(sha1($ctnewpassword, true));
    $newtime = gmdate('YmdHis\Z');
    $newexpiretime = gmdate('YmdHis\Z',strtotime(gmdate('YmdHis\Z', strtotime(gmdate('YmdHis\Z'))) . ' +365 days'));

    # update the password and the related password timestamps ...
    $returnvalue = ( ldap_mod_replace ($ldap_login_password_and_role_manager_ldap, $dn, array('userPassword' => $newpassword, 'passwordlastchangedtime' =>$newtime, 'passwordexpireaftertime' => $newexpiretime) ) );

    ldap_unbind($ldap_login_password_and_role_manager_ldap);
  }

  if ( $returnvalue ) unset( $GLOBALS['ldap_login_password_and_role_manager_password_is_expired'] );

  return $returnvalue;
}

function ldap_login_password_and_role_manager_updatepassword($args) {
  $dn = $args['dn'];
  $userid = $args['userid'];
  $ctpassword = $args['ctpassword'];
  $ctnewpassword = $args['ctnewpassword'];

  global $ldap_login_password_and_role_manager_ldap;
  
  $returnvalue = false;

  if ( ! $ldap_login_password_and_role_manager_ldap = ldap_connect(LDAP_HOST, LDAP_PORT) ) {
    ldap_login_password_and_role_manager_logger(array('message'=>'unable to connect to LDAP server in function '.__FUNCTION__.'()','priority'=>'local0.notice','tag'=>basename(__FILE__)));
    return $returnvalue;
  }

  ldap_set_option($ldap_login_password_and_role_manager_ldap, LDAP_OPT_PROTOCOL_VERSION, LDAP_VERSION);
  ldap_set_option($ldap_login_password_and_role_manager_ldap, LDAP_OPT_REFERRALS, 0);

  if ( get_option('ldap_login_password_and_role_manager_use_tls') == 'yes' ) ldap_start_tls($ldap_login_password_and_role_manager_ldap);

  if ( $bind = ldap_bind($ldap_login_password_and_role_manager_ldap, $dn, $ctpassword) ) {

    $newpassword = '{SHA}' . base64_encode(sha1($ctnewpassword, true));
    $newtime = gmdate('YmdHis\Z');
    $newexpiretime = gmdate('YmdHis\Z',strtotime(gmdate('YmdHis\Z', strtotime(gmdate('YmdHis\Z'))) . ' +365 days'));

    # update the password and the related password timestamps ...
    $returnvalue = ( ldap_mod_replace ($ldap_login_password_and_role_manager_ldap, $dn, array('userPassword' => $newpassword, 'passwordlastchangedtime' =>$newtime, 'passwordexpireaftertime' => $newexpiretime) ) );

    ldap_unbind($ldap_login_password_and_role_manager_ldap);
  }

  if ( $returnvalue ) unset( $GLOBALS['ldap_login_password_and_role_manager_password_is_expired'] );

  return $returnvalue;
}

# create or update the user in the local DB ...
function ldap_login_password_and_role_manager_update_wp_user($username,$ldapuserinfo=null) {
  global $ldap_login_password_and_role_manager_ldap;

  if ( $ldap_login_password_and_role_manager_ldap == null || empty($username) ) return false;

  $result = 0;
  
  $userattr = get_option('ldap_login_password_and_role_manager_loginattr');
  $givenname = get_option('ldap_login_password_and_role_manager_fnameattr');
  $sirname = get_option('ldap_login_password_and_role_manager_lnameattr');
  $email = get_option('ldap_login_password_and_role_manager_emailattr');
  $basedn = get_option('ldap_login_password_and_role_manager_base_dn');
  $rolemgt = get_option('ldap_login_password_and_role_manager_roleattr');

  if ( is_null($ldapuserinfo) ) {
    $filter = '(' . $userattr . '=' . $username . ')';
    $lu = ldap_search($ldap_login_password_and_role_manager_ldap, $basedn, $filter);
    $number_returned = ldap_count_entries($ldap_login_password_and_role_manager_ldap,$lu);
    $ldapuserinfo = ldap_get_entries($ldap_login_password_and_role_manager_ldap, $lu);
    $ldapuserinfo = $ldapuserinfo[0];
    if ( $number_returned != 1 ) {
      ldap_login_password_and_role_manager_logger(array('message'=>'found too many (or too few) matches for filter '.$filter.' number found: '.$number_returned.' in function '.__FUNCTION__.'()','priority'=>'local0.notice','tag'=>basename(__FILE__)));
      return false;
    }
  }
  
  $user = get_userdatabylogin($username);
  $userid = ( is_object($user) && (int)$user->ID > 0 ) ? (int)$user->ID : 0;

  # Create user using wp standard include  Ref: wp-includes/registration.php and http://codex.wordpress.org/Roles_and_Capabilities
  $d = array(
    'user_pass'     => uniqid('nopass').microtime(),
    'user_login'    => $ldapuserinfo[$userattr][0],
    'user_nicename' => sanitize_title_with_dashes ( $ldapuserinfo[$givenname][0] . '_' . $ldapuserinfo[$sirname][0] ),
    'user_email'    => $ldapuserinfo[$email][0],
    'display_name'  => $ldapuserinfo[$givenname][0].' '.$ldapuserinfo[$sirname][0],
    'first_name'    => $ldapuserinfo[$givenname][0],
    'last_name'     => $ldapuserinfo[$sirname][0],
    );

  # using the "rolemgt", determine role for this site for this user...
  if ( ! empty($rolemgt) && @count($ldapuserinfo[$rolemgt]) ) {
    $myrealm = preg_replace('/^www./','',strtolower($_SERVER['HTTP_HOST']));
    foreach($ldapuserinfo[$rolemgt] as $v) {
      if ( preg_match('/^WP /',trim($v) ) ) {
        $a = explode(' ', trim(strtolower($v)));
        $aa = explode(',', $a[1]);
        foreach($aa as $vv) {
          if ( $vv.'x' == $myrealm.'x' || $vv.'x' == '__all__x' ) {
            $aaa = array('administrator', 'author', 'editor', 'contributor', 'subscriber');
            if ( in_array($a[2],$aaa) ) {
              $d = array_merge_recursive($d,array('role'=>$a[2]));
            }
            break 2;
          }
        }
      }
    }
  }

  if ( ! empty($rolemgt) && is_null($d['role']) ) {
    ldap_login_password_and_role_manager_logger(array('message'=>'function '.__FUNCTION__.'(): user ' . $username . ' no role defined for this realm.','priority'=>'local0.notice','tag'=>basename(__FILE__)));
    return false; # no role identified for this user in this realm.
  }

  if ( $userid > 0 ) $d = array_merge_recursive($d,array('ID'=>$userid)); # causes an update rather than create.
  
  if ( $GLOBALS['ldap_login_password_and_role_manager_testmode'].'x' != 'yesx' ) {
    $result = ( $userid > 0 ) ? wp_update_user($d) : wp_insert_user($d) ;
    if ( @count($result->errors) ) {
      $le = ''; foreach($result->errors as $k => $v) $le .= $k . ':' . implode(';',$v);
      ldap_login_password_and_role_manager_logger(array('message'=>'function '.__FUNCTION__.'(): user ' . $username . ' wp_update_user() error: ' . $le ,'priority'=>'local0.notice','tag'=>basename(__FILE__)));
    } else $userid = (int)$result;
  } else $result = $userid;

  $memberidmap = get_option('ldap_login_password_and_role_manager_memberidmap');
  if ( ! empty($memberidmap) && (int)$result == (int)$userid && (int)$userid != (int)$ldapuserinfo[$memberidmap][0] ) {
    global $wpdb;
    $wpdb->query("ALTER TABLE " . $wpdb->base_prefix . "users AUTO_INCREMENT = 4000000;");
    $sql = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE 1 = 1 AND COLUMN_NAME = 'user_id' AND TABLE_NAME LIKE '" . $wpdb->base_prefix . "%' AND TABLE_SCHEMA = '" . $wpdb->dbname . "'";
    $rows = $wpdb->get_results($sql);
    foreach($rows as $row) $tables[] = $row->TABLE_NAME  . ':user_id';
    $tables[] = 'wp_users:ID';
    $tables[] = 'wp_posts:post_author';
    foreach($tables as $v) {
      list($t,$c) = explode(':',$v,2);
      $sql = "UPDATE $t SET $c = " . (int)$ldapuserinfo[$memberidmap][0] . " WHERE $c = " . (int)$userid;
      $wpdb->query($sql);
    }
    $result = $userid = (int)$ldapuserinfo[$memberidmap][0];
  }

  return ( (int)$result == (int)$userid );
}

function ldap_login_password_and_role_manager_show_extra_profile_fields( $user ) {
?>
<table class="form-table">
<tbody>
<tr id="ldap_login_password_and_role_manager_current_password_tr">
    <th><label for="ldap_login_password_and_role_manager_current_password">Current Password</label></th>
    <td><input type="password" autocomplete="off" value="" size="16" id="ldap_login_password_and_role_manager_current_password" name="ldap_login_password_and_role_manager_current_password"> 
        <p class="description indicator-hint">Your current password is required to reset your password.</p>
    </td>
</tr>
</tbody></table>
<?php
}

function ldap_login_password_and_role_manager_userprofile ( $user_id ) {

  # wreck the password locally ...
  $d = array('user_pass' => uniqid('nopass').microtime());
  $result = wp_update_user($d);              

  if ( !current_user_can( 'edit_user', $user_id ) ) return false;
  if ( $_POST['pass1'].'x' != $_POST['pass2'].'x' ) return false;
  if ( $_POST['ldap_login_password_and_role_manager_current_password'].'x' == 'x' ) return false;
  global $current_user;
  if ( ! $current_user ) return false;

  $username = $current_user->user_login;
  list($dn,$info) = ldap_login_password_and_role_manager_get_user_dn($username);
  return ldap_login_password_and_role_manager_updatepassword( array('dn'=>$dn,'userid'=>$username,'ctpassword'=>$_POST['ldap_login_password_and_role_manager_current_password'],'ctnewpassword'=>$_POST['pass1']) );
}

function ldap_login_password_and_role_manager_login_form ($args = array()) {

  if ( $GLOBALS['ldap_login_password_and_role_manager_password_is_expired'] )
    $html = '
      <p><label id="ldap_login_password_and_role_manager_new_password_message">Your password has expired. Please enter a new password.</label></p><br>
      <p>
          <label>New Password<br>
          <input type="password" tabindex="10" size="20" value="" class="input" id="ldap_login_password_and_role_manager_new_password" name="ldap_login_password_and_role_manager_new_password"></label>
      </p>
      <p>
          <label>Confirm New Password<br>
          <input type="password" tabindex="10" size="20" value="" class="input" id="ldap_login_password_and_role_manager_cnew_password" name="ldap_login_password_and_role_manager_cnew_password"></label>
      </p>
      <style>
        #user_pass, #user_login, #user_email, #ldap_login_password_and_role_manager_new_password, #ldap_login_password_and_role_manager_cnew_password {
          background: none repeat scroll 0 0 #FBFBFB;
          border: 1px solid #E5E5E5;
          font-size: 24px;
          margin-bottom: 16px;
          margin-right: 6px;
          margin-top: 2px;
          padding: 3px;
          width: 97%;
        }
      </style>
      <script>
        window.ldap_login_password_and_role_manager_login_jso = {
          loginid: \''. preg_replace("/'/",'',$_POST['log']) . '\',
          onload: function() {
            var i = document.getElementById("user_login");
            if ( i ) {
              i.value = window.ldap_login_password_and_role_manager_login_jso.loginid;
              i.style.width = "300px";
            }
            var s = document.getElementById("wp-submit");
            if ( s ) {
              s.setAttribute("type","button");
              s.setAttribute("onclick","window.ldap_login_password_and_role_manager_login_jso.checknewpw(this)");
            }
          },
          checknewpw: function(o) {
            var a = document.getElementById("ldap_login_password_and_role_manager_new_password").value;
            var b = document.getElementById("ldap_login_password_and_role_manager_cnew_password").value;
            var c = document.getElementById("ldap_login_password_and_role_manager_new_password_message");
            var d = document.getElementById("user_pass").value;
            if ( a == "" || ( a != b && a != d ) ) {
              c.innerHTML = "Please enter a new password and a confirmation password.";
              c.style.color = "red";
              c.style.fontWeight = "bold";
              return false;
            }
            o.form.submit();
          },
          end: "end"
        }
        if (window.addEventListener) window.addEventListener ("load",window.ldap_login_password_and_role_manager_login_jso.onload,false);
        else if (window.attachEvent) window.attachEvent ("onload",window.ldap_login_password_and_role_manager_login_jso.onload);
      </script>
      <input type="hidden" name="ldap_login_password_and_role_manager_password_is_expired" value="true" />
    ';

  if ( ! $args || $args['echo'] )
    echo $html;
  else
    return $html;

}

function ldap_login_password_and_role_manager_logger($arr=array()) {
  $message = $arr['message']; # message should be a short/decisive one line message.
  $priority = $arr['priority']; if ( $priority.'x' == 'x' ) $priority = 'local0.notice';
  $tag = $arr['tag']; if ( $tag.'x' == 'x' ) $tag = basename(__FILE__);

  if ( @trim($message).'x'=='x' ) return;

  $message = preg_replace('/\n/',' ',trim($message)); # make sure message is one line.

  if ( $priority.'x' == 'local0.noticex' ) $priority = LOG_LOCAL0;

  @openlog($tag,null,$priority);
  @syslog($priority,$message);
  @closelog();
}
?>
