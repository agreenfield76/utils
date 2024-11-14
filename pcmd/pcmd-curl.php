<?php

error_reporting(E_ALL);

$site = 'as.exeter.ac.uk';
include('/home/webs/as.exeter.ac.uk/.template/template.php');
$uri = '/utils/activation';
$msg;

# Check we're https.
function check_access()
{
   global $content, $user;
   if(! isset($_SERVER['HTTPS']))
   {
     $uri = $_SERVER['SCRIPT_URI'];
     if(strncasecmp($uri, 'http:', 5) == 0)
     {
       header('Location: https:' . substr($uri, 5));
       $content = '';
     } else
     {
       $content = "<h3><br>This page can only be used with HTTPS.</h3>";
       return 0;
     }
   }
   return 1;
}

# Getline function used by _setPass
function getline($fp, $time)
{
  global $msg, $fmt1, $fmt2, $content;
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
      $content = "<h1><p>Error setting user password: </p><p>\n";
      foreach($msg as $txt)
      {
        $content .= htmlspecialchars($txt)."<br />\n";
      }
      $content .= "</p><h1>\n";
      return 0;
    }
    return 1;
  }
}

# Set the users password using the operator account
# // need to connect to cen and set new password
function _setPass($user, $pass)
{
   global $content, $msg;
   
   $ok = 1;
   $port2 = 903;  // port for changing things
   $fp = stream_socket_client("tcp://cen.ex.ac.uk:$port2", $errno, $errstr, 10);
   if(! $fp)
   {
     $content = "<p>Unable to set up a connection to the password server: ".htmlspecialchars($errstr)."</p>\n";
     $content .= "<p>Try again but if the problem persists please report this to the helpdesk.</p>\n";
     return 0;
   }
   if($ok)
   {
      // Get started
      //stream_set_timeout(20);
      if($ok && ($ok = getline($fp, 10)))
      {
        fwrite($fp, "HELO sso-pwdchange\n");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        fwrite($fp, "AUTH $cookie\n");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        fwrite($fp, "USER $user\n");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        fwrite($fp, "NEWPASS $pass\n");
      }
      if($ok && ($ok = getline($fp, 10)))
      {
        $pwd = $msg[0];
        if ($pwd != "Password changed OK")
        {
           $content .= "<p>Failed to change the password: ".htmlspecialchars($msg[0])."</p>\n";
           return 0;
        }
      }
   }
   if (! $ok) { return 0; }
   return 1;
}

# Check the users password meets the required criteria
function check_pass($newpass1, $newpass2)
{
   global $content, $user;
   $rvspwd = '';
   $alpha = $digit = 0;
   $pwdlen = strlen($newpass1);
   if ($pwdlen < 6 || $pwdlen > 127)
   {
      $content .= tryagain("Your password must contain at least six characters");
      return 0;
   }
   if ($newpass1 == '' || $newpass2 == '')
   {
      $content .= tryagain("You must enter your new password in both entry fields");
      return 0;
   }
   if ($newpass1 != $newpass2)
   {
      $content .= tryagain("The password's you entered did not match");
      return 0;
   }
   for($i=0; $i<$pwdlen;)
   {
      $ch = substr($newpass1, $i++, 1);
      $rvspwd = $ch .= $rvspwd;     # construct reverse
      if      (preg_match('/[0-9]/', $ch))    { $digit++;}
      else if (preg_match('/[A-Za-z]/', $ch)) { $alpha++;}
   }
   echo "$alpha : $digit<br>";
   if ($alpha < 2 || $digit < 1)
   {
      $content .= tryagain("Your new password must contain at least 2 letters and 1 number");
      return 0;
   }
   if (strpos(strtolower($newpass1), strtolower($user)) !== FALSE)
   {
      $content .= tryagain("Your new password cannot contain your username");
      return 0;
   }
   if (strpos(strtolower($rvspwd), strtolower($user)) !== FALSE)
   {
      $content .= tryagain("Your new password cannot contain your username in reverse");
      return 0;
   }
   return 1;
}

# Function to login to SSO, returns a valid cookie if authentication suceeds
function _ssologin($user, $pass)
{
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
         $content = '<p>Error connecting to the SSO authentication server.</p>\n';
         return 0;
      }
   }
   else
   {
      $content = '<p>Error setting up connection to SSO server.</p>\n';
      return 0;
   }
   if(preg_match("^token.id=", $buffer) == 0)
   {
      $content = '<p>SSO authentication failed.</p>\n';
      return 0;
   }
   $cookie = substr($buffer, 9);
   return $cookie;
}

# Function to lookup user based on card


