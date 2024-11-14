<html>
<head>
<title>PCMP Password Change</title>
</head>
<body>
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
    if($ch4 == "-") { continue;}
    if($ch1 != "2") { return 0;}
    return 1;
  }
}


$sname = $_SERVER['SERVER_NAME'];
error_reporting(0);
$ok = 1;

if(! isset($_SERVER['HTTPS']))
{
  $err = 'The Exeter password facility can only be used with https.';
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

$username = $_POST['username'];
$password = $_POST['password'];
$newpwd = $_POST['newpwd'];

if($username == '' || $password == '' || $newpwd == '')
{
  $err = 'An attribute required by Exeter is missing.';
  $ok = 0;
}

if(! preg_match('/^p3[A-Za-z]{2}[0-9]{4}$/', $username))
{
  // remove next line if only new format is being allowed
  if(! preg_match('/^pm[0-9]{2}[A-Za-z]{1,4}[0-9]{0,1}$/', $username))
  {
    $ok = 0;
    $err = 'The username is not the correct format for PCMD users.';
  }
}

if($ok && ($password != 'pcmdpwd'))
{
  $ok = 0;
  $err = 'Invalid credentials supplied.';
}
$md5 = md5("Username: $username");

if($ok)
{
  $fp = stream_socket_client("tcp://cen.ex.ac.uk:$port2", $errno, $errstr, 10);
  if(! $fp)
  {
    $err = "Unable to connect to central server: ".htmlspecialchars($errstr);
    $ok = 0;
  }
}
  
if($ok)
{
  // Get started
  stream_set_timeout(20);
  if($ok && ($ok = getline($fp, 10)))
  {
    fwrite($fp, "HELO pcmd-web\n");
  }
  if($ok && ($ok = getline($fp, 10)))
  {
    fwrite($fp, "USER $username-pcmdauth\n");
  }
  if($ok && ($ok = getline($fp, 10)))
  {
    fwrite($fp, "PASS $md5\n");
  }
  if($ok && ($ok = getline($fp, 10)))
  {
    fwrite($fp, "NEWPASS $newpwd'\n");
  }
  if($ok) { $ok = getline($fp, 10);}
}

if($ok)
{
  echo "SUCCESS:\n";
  echo "Username: $username\n";
  echo "Password: $password\n";
  echo "New pwd: $newpwd\n";
  $md5 = md5("Username: $username");
  echo "MD5: $md5\n";
}
else
{
  if($msg[0] == 'Invalid Username or Password supplied')
  {
    $err = 'User is not Registered at Exeter University.';
  }
#  else
#  {
#    $err = 'Problem changing the password at Exeter University.';
#  }
  echo "FAILED: $err\n";
}
?>
</body>
</html>
