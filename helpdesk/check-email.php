<?php

$site = 'as.exeter.ac.uk';
include('/home/webs/' . $site . '/utils/helpdesk/template.php');
$uri = '/utils/helpdesk/check-email.php';
$heading = 'Helpdesk check of user\'s e-mail account';

if (! isset($_COOKIE['iPlanetDirectoryPro']))
{
   $content = "<br><h3>Error obtaining your SSO cookie details</h3>";
   exit;
}
$cookie = $_COOKIE['iPlanetDirectoryPro'];
$url = $_SERVER['PHP_SELF'];

if (isset($_POST['mail'])) {
    $localpart = $_POST['mail'];
    $content = get_include_contents("https://sysadd.exeter.ac.uk/cgi-bin/check-email?format=0&mail=$localpart");
}
else {
$content = "
<p>This form is provided to enable Helpdesk staff to obtain basic information about an e-mail.</p>
<p>Enter the username or email of the person whose details are needed then click the <strong>Get Details</strong> button. </p>
<form name=\"upf\" method=\"post\" action=\"$url\">
<input type=\"hidden\" name=\"cookie\" value=\"' . $cookie . '\"/>
  <table>
    <tr>
    <tr>
      <td><p>Username or Email:</p></td>
      <td><input type=\"text\" name=\"mail\" size=\"30\"/></td>
    </tr>
    <tr>
      <td colspan=\"2\"><center><input type=\"submit\" value=\" Get Details \"/></center></td>
    </tr>
  </table>
</form>
<script type=\"text/javascript\" language=\"JavaScript1.1\">
<!--
  document.upf.privuser.focus();
// -->
</script>
";
}

modifyTemplate($site, $uri, $heading, $content);

function get_include_contents($filename) {
   ob_start();
   include $filename;
   return ob_get_clean();
}

?>
