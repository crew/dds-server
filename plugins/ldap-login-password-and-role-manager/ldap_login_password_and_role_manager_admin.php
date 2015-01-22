<?php 
# File: ldap_login_password_and_role_manager_admin.php
# vim: nowrapscan ic
#
# Purpose: Admin console for LDAP LPRM Configuration settings.
#
# History:
# 30-Dec-10 fhk; Init
#--------------------------------------------------

# If admin options update ..
if ( $_POST['operation'] == 'saveit' ) {
  update_option('ldap_login_password_and_role_manager_base_dn', trim($_POST['base_dn']));
  update_option('ldap_login_password_and_role_manager_domain_controllers', trim($_POST['domain_controller']));
  update_option('ldap_login_password_and_role_manager_binddn', trim($_POST['binddn']));
  update_option('ldap_login_password_and_role_manager_bindpw', trim($_POST['bindpw']));
  update_option('ldap_login_password_and_role_manager_loginattr', trim($_POST['loginattr']));
  update_option('ldap_login_password_and_role_manager_fnameattr', trim($_POST['fnameattr']));
  update_option('ldap_login_password_and_role_manager_lnameattr', trim($_POST['lnameattr']));
  update_option('ldap_login_password_and_role_manager_emailattr', trim($_POST['emailattr']));
  update_option('ldap_login_password_and_role_manager_roleattr', trim($_POST['roleattr']));
  update_option('ldap_login_password_and_role_manager_passwordexpire', trim($_POST['passwordexpire']));
  update_option('ldap_login_password_and_role_manager_newuserredirect', trim($_POST['newuserredirect']));
  update_option('ldap_login_password_and_role_manager_use_tls', ( ( trim(strtolower($_POST['use_tls'])).'x' == 'yesx' ) ? 'yes' : 'no' ) );
  update_option('ldap_login_password_and_role_manager_memberidmap', trim($_POST['memberidmap']));
  $message = '<span id="ldap_login_password_and_role_manager_admin_message" style="font-weight:bold;display:block;padding:5px;color:green">Update completed.</span>';
}

# Test credentials
if ( $_POST['operation'] == 'test' ) {
  $GLOBALS['ldap_login_password_and_role_manager_testmode'] = 'yes';
  $t = wp_authenticate($_POST['username'],$_POST['password']);
  $testit = ( $GLOBALS['ldap_login_password_and_role_manager_testmode_ok'] ) ? 1 : 2;
}

# Load settings, etc
$ldap_login_password_and_role_manager_base_dn = get_option('ldap_login_password_and_role_manager_base_dn');
$ldap_login_password_and_role_manager_domain_controllers = get_option('ldap_login_password_and_role_manager_domain_controllers');
$ldap_login_password_and_role_manager_binddn = get_option('ldap_login_password_and_role_manager_binddn');
$ldap_login_password_and_role_manager_bindpw = get_option('ldap_login_password_and_role_manager_bindpw');
$ldap_login_password_and_role_manager_loginattr = get_option('ldap_login_password_and_role_manager_loginattr');
$ldap_login_password_and_role_manager_fnameattr = get_option('ldap_login_password_and_role_manager_fnameattr');
$ldap_login_password_and_role_manager_lnameattr = get_option('ldap_login_password_and_role_manager_lnameattr');
$ldap_login_password_and_role_manager_emailattr = get_option('ldap_login_password_and_role_manager_emailattr');
$ldap_login_password_and_role_manager_roleattr = get_option('ldap_login_password_and_role_manager_roleattr');
$ldap_login_password_and_role_manager_passwordexpire = get_option('ldap_login_password_and_role_manager_passwordexpire');
$ldap_login_password_and_role_manager_newuserredirect = get_option('ldap_login_password_and_role_manager_newuserredirect');
$ldap_login_password_and_role_manager_use_tls = get_option('ldap_login_password_and_role_manager_use_tls');
$ldap_login_password_and_role_manager_memberidmap = get_option('ldap_login_password_and_role_manager_memberidmap');

?>
<html>
<head>
</head>
<body>

<div class="banner"><h1>LDAP LPRM V<?php echo LDAP_LPRM_VERSION; ?></h1></div>

<div style="border:1px solid black;display:block;padding:5px;margin:5px">
<h2>Test Settings</h2>

<span>
Use this form to test your LDAP settings.<br />
Only LDAP authentication is tested, account creation is bypassed with this tester.
</span>

<form method="post">
<p>
  <strong>Username:</strong><br />
  <input name="username" type="text" size="35" />
  <br />

  <strong>Password:</strong><br />
  <input name="password" type="password" size="35" />
  <br />

</p>

<input type="hidden" name="operation" value="test" />
<input type="submit" name="button_submit" value="<?php _e('Test Settings', 'ldap-login-password-and-role-manager') ?>" />
</form>

<?php
if ( (int)$testit == 1) {
  echo '<h3>Test Results:</h3><p style="color:green">Congratulations! The test succeeded. The login credentials worked.';
  if ( ! empty($GLOBALS['ldap_login_password_and_role_manager_testmode_message']) ) echo '<br>Extra notes:<b>' . $GLOBALS['ldap_login_password_and_role_manager_testmode_message'] . '</b>';
  echo '</p>';
} elseif ( (int)$testit == 2) {
  echo '<h3>Test Results:</h3><p style="color:red">Failure. Either your LDAP settings are incorrect or the test credentials are wrong.</p>';
}
?>
</div>

<div onclick="try{ document.getElementById('ldap_login_password_and_role_manager_admin_message').style.display='none';}catch(e){}">
<form style="display::inline;" method="post">
<div style="border:1px solid black;display:block;padding:5px;margin:5px">
<h2>Settings</h2>
<p>
  <strong>LDAP Login Attribute:</strong><br />
  <input name="loginattr" type="text" value="<?php echo $ldap_login_password_and_role_manager_loginattr; ?>" size="55" /><br />
  <span>* Name the attribute to use when matching the username to check login against, example:  <i>mail</i></span><br />
  <br />

  <strong>LDAP Firstname Attribute:</strong><br />
  <input name="fnameattr" type="text" value="<?php echo $ldap_login_password_and_role_manager_fnameattr; ?>" size="55" /><br />
  <span>* Name the attribute to map to the user firstname, example:  <i>givenname</i></span><br />
  <br />

  <strong>LDAP Lastname Attribute:</strong><br />
  <input name="lnameattr" type="text" value="<?php echo $ldap_login_password_and_role_manager_lnameattr; ?>" size="55" /><br />
  <span>* Name the attribute to map to the user lastname, example:  <i>sn</i></span><br />
  <br />

  <strong>LDAP Email Attribute:</strong><br />
  <input name="emailattr" type="text" value="<?php echo $ldap_login_password_and_role_manager_emailattr; ?>" size="55" /><br />
  <span>* Name the attribute to map to the user email, example:  <i>mail</i></span><br />
  <br />

  <strong>Base DN:</strong><br />
  <input name="base_dn" type="text" value="<?php echo $ldap_login_password_and_role_manager_base_dn; ?>" size="55" /><br />
  <span>* Example: For subdomain.domain.sufix use: <i>DC=subdomain,DC=domain,DC=suffix</i></span><br />
  <br />

  <strong>Domain Controller(s):</strong><br />
  <input name="domain_controller" type="text" value="<?php echo $ldap_login_password_and_role_manager_domain_controllers; ?>" size="55" /><br />
  <span>* Separate with semi-colons. Format: controllername[:portnumber] or controllername[;controllername[...]]</span><br />
  <br />

  <strong>Bind DN:</strong> (optional)<br />
  <input name="binddn" type="text" value="<?php echo $ldap_login_password_and_role_manager_binddn; ?>" size="55" /><br />
  <span>* Enter DN used to bind in LDAP. If this is left blank, an anonymous bind is used to connect to the LDAP server.</span><br />
  <br />

  <strong>Bind Password:</strong> (optional)<br />
  <input name="bindpw" type="password" value="<?php echo $ldap_login_password_and_role_manager_bindpw; ?>" size="55" /><br />
  <span>* Enter password used to bind in LDAP. This can be blank or contains a clear-text password used with <strong>Bind DN</strong> setting.</span><br />
  <br />

  <strong>Use TLS encryption:</strong><br />
  <input name="use_tls" type="text" value="<?php echo $ldap_login_password_and_role_manager_use_tls; ?>" size="55" /><br />
  <span>* Enter 'yes' or 'no', to use TLS encryption.</span><br />
  <br />

  <strong>LDAP Password Expire Attribute:</strong> (optional)<br />
  <input name="passwordexpire" type="text" value="<?php echo $ldap_login_password_and_role_manager_passwordexpire; ?>" size="55" /><br />
  <span>* Name the attribute to use to test if password change/force/expire, example:  <i>passwordExpirationTime</i>. If this is
    left blank, no expire password logic is enforced.
  </span><br />
  <br />

  <strong>LDAP Role Manager Attribute:</strong> (optional)<br />
  <input name="roleattr" type="text" value="<?php echo $ldap_login_password_and_role_manager_roleattr; ?>" size="55" /><br />
  <span>* Name the attribute to use to determine user roles, example:  <i>winacess</i>.  Leave blank to disable this feature.</span><br /><br>
  <span>
    The format expects 3 parts separated by a space. The 1st
    part is identified as the <i>realm</i> of the access, giving the ability to
    allow this LDAP attribute use beyond this plugin in terms of LDAP. The 1st part
    is expected to be literally 'WP'. The second part is the <i>scope</i> of the
    access and is either the literal <i>__ALL__</i> or a specific website-domain.
    The 3rd and final component is the role itself that is one and the same as the
    roles defined in the Wordpress framework. Some example LDAP settings:<br>
    <code>
     &nbsp;&nbsp; WP __ALL__ subscriber<br>
     &nbsp;&nbsp; WP www.domaina.com administrator<br>
     &nbsp;&nbsp; WP www.domainb.com author<br>
     &nbsp;&nbsp; WP www.domainc.com editor<br>
    </code><br>
    In the example above, the first entry defines this user record is allowed to access and login to any of the WP sites and would have the role as subscriber.
    The second, third and fourth examples define specific website-domains and specific roles for them.
  </span>
  <br />

  <strong>URL for New User Redirection:</strong> (optional)<br />
  <input name="newuserredirect" type="text" value="<?php echo $ldap_login_password_and_role_manager_newuserredirect; ?>" size="155" /><br />
  <span>* Enter a URL for creation of New Users creation. If this is left blank, new user creation is performed as normal at your WP site. example: http://ldap.myserver.com
  </span><br />

  <strong>LDAP Member ID Map:</strong> (optional)<br />
  <input name="memberidmap" id="memberidmap" type="text" value="<?php echo $ldap_login_password_and_role_manager_memberidmap; ?>" size="55" /><br />
  <span>* Name the attribute to use when syncronizing local user ID to LDAP user ID, example: <i>membernum</i>. Leave blank to disable this feature.<br>When this feature is enabled, the following known user id locations in the DB will be syncronized to match the integer value found in this LDAP attribute:
<ul style="list-style:disc;margin-left:100px">
  <li><?php global $wpdb; echo $wpdb->comments; ?>:user_id</li>
  <li><?php echo $wpdb->posts; ?>:post_author</li>
  <li><?php echo $wpdb->postmeta; ?>:user_id</li>
  <li><?php echo $wpdb->usermeta; ?>:ID</li>
</ul>
In addition, any table in the DB named <b><?php echo $wpdb->dbname; ?></b> with a prefix name of <b><?php echo $wpdb->base_prefix; ?></b> found otherwise to contain the column named <b>user_id</b> will be managed by this feature as well.
</span><br />

  <br />


</p>
<input type="hidden" name="operation" value="saveit" />
<input type="submit" name="button_submit" value="<?php _e('Update Options', 'ldap-login-password-and-role-manager') ?>" />
</form>
<?php echo $message; ?>

</div>
</div>

</body>
</html>
