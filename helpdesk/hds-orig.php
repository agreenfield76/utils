<?php 
$sname = $_SERVER['SERVER_NAME'];
$ip = '/home/webs/'.$sname.'/includes/include0408s';?>
<?php include("$ip/doctype.inc");?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php include("$ip/head.inc");?>
<!-- now put your own stuff in the head -->
<meta name="description" content="Helpdesk Support Page" />
<meta name="keywords" content="helpdesk support" />
<title>Helpdesk Support</title>
<style type="text/css" media="screen">
#navhelp{color: #fff ! important; background-color: #4c70b7;}
.noscreen {visibility: hidden; display: none;}
</style>
<style type="text/css" media="print">
.noprint {visibility: hidden; display: none;}
</style>
<?php 
$maxtab = 5;
include("$ip/txhead.php");?>
</head>
<body>
<?php include("$ip/bodya.shtml");?>
<!-- now put your own stuff in the breadcrumbs -->
<a href="/">IT Services</a> &gt; <a href="">Helpdesk Support</a>
<?php include("$ip/bodyc.inc");?>
<!-- now put your own stuff in the title -->
<span class="noprint">
<h1>Helpdesk Support</h1>
</span>
<!-- now put your own stuff in -->

<?php
function getline($fp, $time)
{
  global $msg;
  global $fmt1;
  global $fmt2;
  $msg = array();    // Start with an empty msg array
  $fmt1 = array();
  $fmt2 = array();

  while(!feof($fp))
  {
    $txt = fgets($fp, 1024);
    $ch1 = substr($txt, 0, 1);
    $ch2 = substr($txt, 1, 1);
    $ch3 = substr($txt, 2, 1);
    $ch4 = substr($txt, 3, 1);
    $msg[] = rtrim(substr($txt, 4));
    $fmt1[] = $ch2;
    $fmt2[] = $ch3;
    if($ch4 == "-")
    {
      continue;
    }
    if($ch1 != "2")
    {
      echo "<p>Error obtaining user data: </p><p>\n";
      foreach($msg as $txt)
      {
        echo htmlspecialchars($txt)."<br />\n";
      }
      echo "</p>\n";
      return 0;
    }
    return 1;
  }
}

function show_value($txt, $flags)
{
  if(($flags & 1) == 1)  { echo "<font color=\"red\">";}
  if($txt == "")         { echo "&nbsp;";}
  elseif(($flags & 2) == 2)
  {
    echo "\n<script language=\"JavaScript1.1\"><!--\n";
    echo "function ShowPwd()\n{\n  alert(\"$txt\");\n}\n";
    echo "// --></script>\n";
    echo "<a title=\"$txt\" onclick=\"ShowPwd();\">Show value</a>";
  }
  else                   { echo htmlspecialchars($txt);}
  if(($flags & 1) == 1)  { echo "</font>";}
}

error_reporting(0);
$ok = 1;

if(! isset($_SERVER['HTTPS']))
{
  echo "<p>This facility can only be used with https.</p>\n";
  $ok = 0;
}
elseif(! isset($_SERVER['PHP_AUTH_USER']))
{
  echo "<p>This facility can only be used by authourised users.</p>\n";
  $ok = 0;
}

// Standard Port values
$port1 = 107;  // Port for getting information
$port2 = 108;  // port for changing things

$url = $_SERVER['PHP_SELF'];
if(substr($url, -9) == '-test.php')
{
  // Use test values
  $port1 = 109;  // Port for getting information
  $port2 = 112;  // port for changing things
}

$action = $_POST['action'];

if    ($action == 'Get student details')       { $student = 1; $action = 'STUDENT';}
elseif($action == 'Get staff details')         { $staff = 1; $action = 'STAFF';}
elseif($action == 'Reset' || $action == 'Set') { $reset = 1;}
elseif($action == 'Collect')                   { $collect = 1;}
elseif($action == 'Unblock')                   { $unblock = 1;}
elseif($action != '')
{
  echo "<p>$action is not recognised!</p>\n";
  $ok = 0;
}

if($ok)
{
  if($student || $staff)
  {
    if(($username = $_POST['name']) == '')
    {
      echo "<p>No username or ID provided!</p>\n";
      $ok = 0;
    }
    
    if($ok)
    {
      $fp = stream_socket_client("tcp://cen.ex.ac.uk:$port1", $errno, $errstr, 10);
      if(! $fp)
      {
        echo "<p>Unable to connect to central server: ".htmlspecialchars($errstr)."</p>\n";
        $ok = 0;
      }
    }
  
    if($ok)
    {
      // Get started
      stream_set_timeout(20);
      if($ok && ($ok = getline($fp, 10)))
      {
        fwrite($fp, "USER ".$_SERVER['PHP_AUTH_USER']."\n");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        fwrite($fp, "PASS ".$_SERVER['PHP_AUTH_PW']."\n");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        fwrite($fp, "$action $username\n");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        echo "<table border=\"1\">\n";
        $txt = array_pop($msg);           // Final line of result
        list($username, $collected, $inugtf, $setreset) = explode("\t", $txt, 4);
        while($txt = array_shift($msg))
        {
          $xfmt1 = array_shift($fmt1);
          $xfmt2 = array_shift($fmt2);
          $pair = explode("\t", $txt, 2);
  
          echo "<tr><td>";
          show_value($pair[0], $xfmt1);
          echo "</td><td>";
          show_value($pair[1], $xfmt2);
          echo "</td></tr>\n";
        }
        echo "</table>\n";
        # Provide password reset option
        echo "<form method=\"post\" action=\"$url\">\n";
        if($setreset != 1)
        {
          if($inugtf == -1)
          {
            echo "<p>Unblock $username: ";
            echo "<input type=\"hidden\" name=\"username\" value=\"$username\" />\n";
            echo "<input type=\"submit\" name=\"action\" value=\"Unblock\" />\n";
          }
          elseif(!$collected && $student)
          {
            echo "<p>Collect password for $username: ";
            echo "<input type=\"hidden\" name=\"username\" value=\"$username\" />\n";
            echo "<input type=\"submit\" name=\"action\" value=\"Collect\" />\n";
          }
          elseif($inugtf)
          {
            echo "<p>Reset password to original for $username: ";
            echo "<input type=\"hidden\" name=\"username\" value=\"$username\" />\n";
            echo "<input type=\"submit\" name=\"action\" value=\"Reset\" />\n";
          }
          else
          {
            echo "<p>Set a new password for $username: ";
            echo "<input type=\"hidden\" name=\"username\" value=\"$username\" />\n";
            echo "<input type=\"submit\" name=\"action\" value=\"Set\" />\n";
          }
          echo "</p>\n";
        }
        echo "<p><a href=\"$url\">Enter new staff or student details</a>.\n";
        echo "</p></form>\n";
?>
<p>NOTES.</p>
<ol>
<li>An account must not be unblocked unless appropriate approval has been obtained.</li>
<li>This is particularly important where an account is in the process of being deleted.
    Unblocking alone will not stop the account from being deleted.</li>
<li>An account which has been blocked by Registry or Finance will not be automatically
    unblocked when the issue is resolved. You may unblock an account in this situation.
    Check with the student that they did have a Registry or Finance issue.</li>
</ol>
<?php
      }
      if($ok)
      {
        fwrite($fp, "QUIT\n");
      }
      fclose($fp);
    }
  }
  elseif($reset || $collect || $unblock)
  {
    if(($username = $_POST['username']) == "")
    {
      echo "<p>No student username or ID provided!</p>\n";
      $ok = 0;
    }
    
    if($ok)
    {
      $fp = stream_socket_client("tcp://cen.ex.ac.uk:$port2", $errno, $errstr, 10);
      if(! $fp)
      {
        echo "<p>Unable to connect to central server: ".htmlspecialchars($errstr)."</p>\n";
        $ok = 0;
      }
    }
  
    if($ok)
    {
      // Get started
      stream_set_timeout(20);
      if($ok && ($ok = getline($fp, 10)))
      {
        fwrite($fp, "HELO hd-web\n");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        fwrite($fp, "USER ".$username.'-'.$_SERVER['PHP_AUTH_USER']."\n");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        fwrite($fp, "PASS ".$_SERVER['PHP_AUTH_PW']."\n");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        if($reset)             { fwrite($fp, "RESET\n");}
        elseif($collect)       { fwrite($fp, "COLLECT\n");}
        else                   { fwrite($fp, "UNBLOCK\n");}
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        if($reset)
        {
          $pwd = "New password: ".$msg[0];
          echo "<p>Password reset. \n";
          echo "\n<script language=\"JavaScript1.1\"><!--\n";
          echo "function ShowPwd()\n{\n  alert(\"$pwd\");\n}\n";
          echo "// --></script>\n";
          echo "<a title=\"$pwd\" onclick=\"ShowPwd();\">Show value</a>";
        }
        elseif($collect)
        {
          echo "<span class=\"noprint\">\n";
          echo "<p>Print this page and hand to the user.</p>\n";
          echo "</span>\n";
          list($pwd, $username, $email) = explode("\t", $msg[0], 3);
          echo "<h3>Please keep this information safe and confidential.</h3>\n";
          echo "<p>Your login username is: $username<br />\n";
          echo "Your initial password is: $pwd<br />\n";
          echo "Your e-mail address is: $email</p>\n";
  ?>
  <span class="noscreen">
  <h3>Please note the following important information:</h3>
  <ul>
  <li>You must use upper and lower case letters in your username and password
      exacly as indicated above.</li>
  <li>All users should be aware of the importance of their password.  You are
      advised to change the password to one that follows the security guidelines
      on the online form at http://www.exeter.ac.uk/its/ldap/pwdchange.shtml</li>
  <li>Do not divulge your password to anyone else.  members of Academic Services staff should never ask for your password.</li>
  </ul>
  </span>
  <?php
        }
        else
        {
          echo "<p>The account has been unblocked and the password re-instated.</p>\n";
        }
        echo "<span class=\"noprint\">\n";
        echo "<p><a href=\"$url\">Enter new staff or student details</a>.</p>\n";
        echo "</span>\n";
      }
    }
  }
  else
  {
   echo "<form method=\"post\" action=\"$url\">\n";
  ?>
    <table>
    <tr><td>Username or ID number:</td>
        <td><input type="text" name="name" /></td></tr>
    <tr><td>&nbsp;</td>
        <td><input type="submit" name="action" value="Get student details" /></td></tr>
    <tr><td>&nbsp;</td>
        <td><input type="submit" name="action" value="Get staff details" /></td></tr>
    </table>
   </form>
<?php
  }
} ?>
<?php include("$ip/bodyd.inc");?>
Last modified:
<!-- #BeginDate format:Sw1 -->7 March, 2008<!-- #EndDate -->
by the Information and Liaison Team
<?php include("$ip/bodye.inc");?>
</body>
</html>
