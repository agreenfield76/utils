<?php

include('include.php');

$heading = 'Managing University Distribution Lists';

// Function to connect to dot and get information
function do_exeter($username, $credentials)
{

  if($curl = curl_init("https://email.exeter.ac.uk/cgi-bin/listcheck"))
  {
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "user=$username&secret=$credentials");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
    $buffer = curl_exec($curl);
    curl_close($curl);
    if(! $buffer)
    {
      return 'ERROR:Error connecting to the List Server.';
    }
  }
  else
  {
    return 'ERROR:Error setting up connection to the List Server.';
  }

  $strt = stripos($buffer, '<body>');
  $end = stripos($buffer, '</body>');

  $lnth = strlen($buffer);
  if($strt < 0 || $strt >= $end)
  {
    return "ERROR:Error in data returned from the List Server: $strt/$end ($lnth)";
  }

  $lnth = $end - $strt - 6;
  $txt = substr($buffer, $strt+6, $lnth);
  $txt = trim($txt);

  return $txt;
}

$form = $content = '';
$ok = 1;
if(isset($_SERVER['REMOTE_USER'])) { $user = $_SERVER['REMOTE_USER'];}
if(! isset($_SERVER['HTTPS']))
{
  $uri = $_SERVER['SCRIPT_URI'];
  if(strncasecmp($uri, 'http:', 5) == 0)
  {
    header('Location: https:' . substr($uri, 5));
    $content = '';
  }
  else
  {
    $content = '<p>This page can only be used with HTTPS.</p>';
  }
  $ok = 0;
}
if($ok and $user == '')
{
  $content = '<p>You must be authenticated to use this facility.</p>';
  $ok = 0;
}

if($ok)
{
  // get user's status
  $result = do_exeter($user, 'testing');

  $content = '
<p>There are two aspects to list management:</p>
<p>The first is that of managing the membership of lists and people who can do this 
are called list \'owners\'.</p>
<p>The second aspect is the creation of new lists, setting up restrictions to control who may
send messages to them, assigning list \'owners\' and so on. A person
who can do this is called a list \'administrator\'.</p>
<p>A list \'administrator\' can always act as a list \'owner\' for list over which he or she
has rights.</p>
';

  if(substr($result, 0, 8) == 'SUCCESS:')
  {
    if(substr($result, 8, 13) == 'administrator')
    {
      $form = '
<p>You are a list administrator:</p>
<form name="lform" method="post" action="ladmin.php">
<input type="hidden" name="start" value=1>
<input type="hidden" name="context" value="lists">
<input type="hidden" name="action" value="list">
<input type="submit" value="Continue as Administrator">
</form> or 
<form name="lform" method="post" action="lowner.php">
<input type="hidden" name="start" value=1>
<input type="hidden" name="context" value="list">
<input type="hidden" name="action" value="list">
<input type="submit" value="Continue as Owner">
</form>
';
    }
    elseif(substr($result, 8, 5) == 'owner')
    {
      $form = '
<p>You are a list owner:</p>
<form name="lform" method="post" action="lowner.php">
<input type="hidden" name="start" value=1>
<input type="hidden" name="context" value="list">
<input type="hidden" name="action" value="list">
<input type="submit" value="Continue as Owner">
</form>
';
    }
    else
    {
      $form = '<p>You are neither a list administrator nor a list owner.</p>';
    }
  }
  else
  {
    $form = '
<p><b>There was a problem establishing if you are either a list administrator
or a list owner:<b></p><p>
';
    // if(substr($result, 0, 7) == 'ERROR:') { $form .= substr($result, 7);}
    // else                                  { $form .= $result;}
    $form .= $result;
    $form .= '</p>';
  }
  $form .= '
<br /><p>Note: If you wish to contact the main person who maintains the
membership of a list you can do so by e-mailing <b>owner-</b> followed by
the address of the list.  For example, for a list with an address of
<b>example-list@exeter.ac.uk</b>, send e-mail to
<b>owner-example-list@exeter.ac.uk</b>.
';
}

modifyTemplate($site, $uri, $heading, $content.$form);

?>
