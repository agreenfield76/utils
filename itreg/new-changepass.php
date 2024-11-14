<?php
/*=============================
password change script
original D.N.Gardner
modified J.A.Aylward@ex.ac.uk for CMS
modified R.A.Rudd@ex.ac.uk for VMCEN
modified A.Spedding@exeter.ac.uk for SSPR go-live 11 Dec 14
==============================*/

$DEBUG = FALSE;
$USERERROR = $PASSERROR = $NPASERROR = "";
$username = $oldpass = $newpass1 = $newpass2 = "";
//$error = "";
//error_reporting(E_ALL);
error_reporting(0);

if(isset($_POST['username']) || isset($_POST['password']))
{
   if(isset($_POST['username'])) { $username = 	trim(filter_input(INPUT_POST,'username', FILTER_SANITIZE_STRING)); }
   if(isset($_POST['password'])) { $oldpass  = 	trim(filter_input(INPUT_POST,'password', FILTER_SANITIZE_STRING)); }
   if(isset($_POST['password1'])){ $newpass1 = 	trim(filter_input(INPUT_POST,'password1', FILTER_SANITIZE_STRING)); }
   if(isset($_POST['password2'])){ $newpass2 =	trim(filter_input(INPUT_POST,'password2', FILTER_SANITIZE_STRING)); }

   // Check args are all OK
   if($username == '') {
      $USERERROR = "You must supply your Username";
   } else if($oldpass == '') {
      $PASSERROR = "You must enter your current password";
   } else {
      $auth = _ssologin($username, $oldpass);
      if(!$auth) {
         sleep(5);
         $auth = _ssologin($username, $oldpass); // try twice, sometimes it fails on the first attempt
      }
      if(!$auth) {
         echo "<p>$error</p>";
      } else {
         if(checkpass($newpass1, $newpass2)) {
            /* Create a TCP/IP socket. */
            if(setPass($auth, $username, $newpass1)) {
               echo "<p>Password change was <b>successful</b></p>";$error='success';
            } else {
               echo "<p>Password change <b style='color:red'>FAILED</b></p>";
               echo "<p>$error</p>";
            }
            _ssologout($auth);
         }
      }
   }
}

// Check the users password meets the required criteria
function checkpass($newpass1, $newpass2)
{
   global $NPASERROR, $username;
   $rvspwd = '';
   $alpha = $digit = 0;
   $pwdlen = strlen($newpass1);
   if ($pwdlen < 6 || $pwdlen > 127)
   {
      $NPASERROR = "Your new password must contain at least six characters";
      return 0;
   }
   if ($newpass1 == '' || $newpass2 == '')
   {
      $NPASERROR = "You must enter your new password in both entry fields";
      return 0;
   }
   if ($newpass1 != $newpass2)
   {
      $NPASERROR = "The new passwords you entered did not match";
      return 0;
   }
   for($i=0; $i<$pwdlen;)
   {
      $ch = substr($newpass1, $i++, 1);
      $rvspwd = $ch .= $rvspwd;     # construct reverse
      if (preg_match("/[a-z]/", $ch)) { $alphaLower = 1;}
      if (preg_match("/[A-Z]/", $ch)) { $alphaUpper = 1;}
      if (preg_match("/[0-9]/", $ch)) { $digit = 1;     }
      if (preg_match('/(\~|!|@|#|\$|%|\^|&|\*|_|-|\+|\=|\'|\(|\)|\{|\}|\[|\]|:|;|"|<|>|,|\.|\?|\/|\\\)/', $ch))  { $special = 1;   }
   }
   //echo "$alphaLower : $alphaUpper : $digit : $special<br>";
   if (($alphaLower + $alphaUpper + $digit + $special) < 3)
   {
      $NPASERROR = "Your new password does not meet the complexity requirements";
      return 0;
   }
   if (strpos(strtolower($newpass1), strtolower($username)) !== FALSE)
   {
      $NPASERROR = "Your new password cannot contain your username";
      return 0;
   }
   if (strpos(strtolower($rvspwd), strtolower($username)) !== FALSE)
   {
      $NPASERROR = "Your new password cannot contain your username in reverse";
      return 0;
   }
   return 1;
}

// Function to login to SSO, returns a valid cookie if authentication suceeds
function _ssologin($user, $pass)
{
   global $error, $PASSERROR;
   if($curl = curl_init("https://sso.exeter.ac.uk/opensso/identity/authenticate"))
   {
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, "username=$user&password=$pass&uri=realm=/people");
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
      $buffer = curl_exec($curl);
      curl_close($curl);
      if(! $buffer)
      {
         $error = "An error occurred while authenticating with the SSO server";
         return 0;
      }
   }
   else
   {
      $error = "An error occurred setting up a connection to the SSO server";
      return 0;
   }
   if(!preg_match("/token.id=/i", $buffer))
   {
      $PASSERROR = "Invalid username or password";
      return 0;
   }
   $cookie = substr($buffer, 9);
   return $cookie;
}

// Function to login to SSO, returns a valid cookie if authentication suceeds
function _ssologout($auth)
{
   global $error;
   if($curl = curl_init("https://sso.exeter.ac.uk/opensso/identity/logout"))
   {
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, "token.id=$auth");
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
      $buffer = curl_exec($curl);
      curl_close($curl);
      if(! $buffer)
      {
         $error = "An error occurred logging off of the SSO server";
         return 0;
      }
   }
   else
   {
      $error = "An error occurred setting up a connection to the SSO server";
      return 0;
   }
   return 1;
}

// Getline function used by setPass
function getline($fp, $time)
{
   global $DEBUG, $msg;
   $msg = "";    // Start with an empty msg array

   while(!feof($fp))
   {
      $txt = fgets($fp, 1024);
      if($DEBUG) echo "DEBUG : $txt<br>";
      if(preg_match('/200 authorisation please./', $txt)) 
      { 
         return 1; 
      }
      elseif(preg_match('/200 username please./', $txt)) 
      {
         return 1; 
      }
      elseif(preg_match('/200 new password please./', $txt)) 
      {
         return 1; 
      }
      elseif(preg_match('/200-/', $txt)) 
      {
         $msg = substr($txt, 4);
         return 1; 
      }
      elseif(preg_match('/500 /', $txt)) 
      {
         $msg = substr($txt, 4);
         return 0; 
      }
      else 
      { 
         continue;
      }      
   }
   return 1;
}

// Function to send data, allows better debugging
function sendline($fp, $cmd)
{
   global $DEBUG;
   $cmd = rtrim($cmd);
   if($DEBUG) echo "DEBUG : $cmd </br>";
   fwrite($fp, "$cmd\n");
}

// Connect to vmcen and set the new password
function setPass($auth, $user, $pass)
{
   global $error, $msg;

   $ok = 1;
   $port = 106;  // port for changing things
   $fp = stream_socket_client("tcp://vmcen02.ex.ac.uk:$port", $errno, $errstr, 10);
   if(! $fp)
   {
     $error = "Unable to set up a connection to the password server: ".htmlspecialchars($errstr) ."<br>";
     $error .= "Try again but if the problem persists please report this to SID";
     return 0;
   }
   if($ok)
   {
      // Get started
      //stream_set_timeout(20);
      if($ok && ($ok = getline($fp, 10)))
      {
        sendline($fp, "HELO sso-pwdchange");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        sendline($fp, "AUTH $auth");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        sendline($fp, "USER $user");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        sendline($fp, "NEWPASS $pass");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
         _ssologout($auth);
         return 1;
      }
   }
   // if we got this far then there was a problem
   $error = "There was an error setting the password: ".htmlspecialchars($msg);
   _ssologout($auth);
   return 0; 
}

if (!isset($error)){ ?>

<? } ?>



<? if (!isset($error)) { ?>
<h2>Change your password</h2>
<p> </p>
<h3>Before you start</h3>
<p>If you have your University of Exeter email, WiFi, or any other university apps set up on your mobile devices (smart phone or tablet) please disconnect WiFi and 3G connections on <b>all</b> your devices <b>before</b> you change your password. If you don't then your university IT account may be locked out.</p>
<p>The easiest way to do this is to turn on "Airplane" or "Flight" mode on each of your devices.</p>
<h3>Change your password here</h3>
<p>Enter your username, current password, and chosen new password twice, then click the <b>Change</b> button.</p>
<p>Your new password must be at least 6 characters long and must include a mix of characters from 3 of the following types:<br>- upper case letters<br>- lower case letters<br>- numbers from 0 to 9<br>- any of the special characters  ~ ! @ # $ % ^ & * _ - + = ’ | \ ( ) { } [ ] : ; ” < > , . ? /</p>
<?
if (strlen($USERERROR)!=0) { $USERCOLOR = "red"; } else { $USERCOLOR = "black"; }
if (strlen($PASSERROR)!=0) { $PASSCOLOR = "red"; } else { $PASSCOLOR = "black"; }
if (strlen($NPASERROR)!=0) { $NPASCOLOR = "red"; } else { $NPASCOLOR = "black"; }

// Fix to prevent code injection in GET request to form action
if ($pos_get = strpos($_SERVER['PHP_SELF'], '?')) $page_uri = substr($_SERVER['PHP_SELF'], 0, $pos_get);
?>

<form id="changepass" name="changepass" method="post" autocomplete="off" action="<?php echo $page_uri; ?>"
onsubmit="document.changepass.submit.disabled='true';document.changepass.submit.value='  Please wait  '" \>
  <table>
    <tr>
      <td><label style="color:<?=$USERCOLOR ?>;font-size: 13px">Username:</label></td>
      <td><input type="text" id="username" name="username" size="16" tabindex="1" value="<?=$username?>"/></td>
      <td><font style="color:red"><b><?=$USERERROR ?></b></font></td>
    </tr>
    <tr>

      <td><label style="color:<?=$PASSCOLOR ?>;font-size: 13px">Current password:</label></td>
      <td><input type="password" id="password" name="password" size="16" maxlength="127" tabindex="2" value="<?=$oldpass?>"/></td>
      <td><font style="color:red"><b><?=$PASSERROR ?></b></font></td>
    </tr>
    <tr>
      <td><label style="color:<?=$NPASCOLOR ?>;font-size: 13px">New password:</label></td>
      <td><input type="password" id="password1" name="password1" size="16" maxlength="127" tabindex="3" /></td>
      <td rowspan="2"><font style="color:red"><b><?=$NPASERROR ?></b></font></td>
    </tr>
    <tr>

      <td><label style="color:<?=$NPASCOLOR ?>;font-size: 13px">Verify new password:</label></td>
      <td><input type="password" id="password2" name="password2" size="16" maxlength="127" tabindex="4" /></td>
    </tr>
    <tr>
      <td colspan="2"><div align="center">
      <input style="font-size:13px; background-color: lightblue; font-weight:bold" type="submit" id="submit" value=" Change "/></td>
      <td></td>
    </tr>
  </table>
<? } ?>
<p> </p>
<h3>After you have successfully changed your password</h3>
<p>Make sure you update your new password in Account Settings for email, WiFi or any other University apps on <b>all</b> your mobile devices before you turn WiFi on again on them, or turn off "Airplane" mode.</p>
<p>If you are logged in to your University account, log out and back in again using your new password.</p>
<p>You will be able to use your new password straight away to access nearly all University systems, but you do need to update Lync with your new password, and also WiFi for Eduroam and UoE_Secure when you are on campus.</p>
<p>Please see <a href="https://as.exeter.ac.uk/it/account/changepassword/stoplock/">Stop your account being locked</a> for more details.</p>
