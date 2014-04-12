<?php 
# File: ldap_login_password_and_role_manager_adduser.php
# vim: nowrapscan ic
#
# Purpose: Admin console for LDAP-Login-Password-and-Role-Manager New User creation.
#
# History:
# 03-Feb-11 fhk; Init
#--------------------------------------------------

$ldap_login_password_and_role_manager_newuserredirect = get_option('ldap_login_password_and_role_manager_newuserredirect');

?>
<html>
<head>
</head>
<body>

<div class="banner"><h1>LDAP LPRM 1.0, Create New User</h1></div>

<div style="border:1px solid black;display:block;padding:5px;margin:5px">
<h2>Create New User</h2>

<span>
<?php
  if ( 1 == 2 || $ldap_login_password_and_role_manager_newuserredirect.'x' == 'x' ) {
    $u = preg_replace('/users.php?.*$/','options-general.php',$_SERVER['PHP_SELF']) . '?page=ldap-login-password-and-role-manager';
    echo 'Please be sure to setup the <b>New User Redirection</b> option setting before accessing this page. <a href="'.$u.'">Click here</a>.';
  } else {
    ?>
     <iframe
       width="100%"
       height="900px"
       scrolling="vertical"
       id="ldap_login_password_and_role_manager_admin_adduser_iframe"
       name="ldap_login_password_and_role_manager_admin_adduser_iframe"
       frameborder="1"
       style="border:#000000 solid 1px;background-color:#eeeeee;overflow-x:hidden"
       src="<?php echo $ldap_login_password_and_role_manager_newuserredirect; ?>"
     ></iframe>
    <?php
  }
?>
</span>

</div>

</body>
</html>
