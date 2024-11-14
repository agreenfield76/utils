<?php

// This php code does not do any of the list administrator work
// It runs on the central web servers so that it can work with SSO and https.
// IT uses CURL to call www.email.ex.ac.uk to do the real work.

$heading = 'E-Mail List Administration';

$ok = 1;
$start = $_POST['start'];
session_start();
$session = $old_session = session_id();
$session_time = $_SESSION['time'];

if(isset($_SERVER['REMOTE_USER'])) { $user = $_SERVER['REMOTE_USER'];}
$credentials = 'testing';

if(! isset($_SERVER['HTTPS']))
{
  $form = '<p>This facility can only be used with https.</p>';
  $ok = 0;
}
elseif($start)
{
  // make sure we start a new session
  if($_SESSION['time'])
  {
    session_unset();
    session_destroy(); 
    $_SESSION = array();
    session_start();
    $session = session_id();
  }
  $session_time = $_SESSION['time'] = time();
}
elseif($session_time == 0)
{
  // session must have expired
  header('refresh: 5 url=\'/utils/lists\'');
  $form = '<p><b>The session has been idle for too long.';
  $form .= '</br></br>You will be re-directed to the initial list management';
  $form .= 'web pages in a few seconds.';
  $form .= '</br></br>If you are not re-directed automatically <a href="">';
  $form .= 'click here to go there.</a></b><p>';
  $ok = 0;
}
elseif((time() - $session_time) > 600)
{
  // start a new session so that session does not expire
  session_unset();
  session_destroy(); 
  $_SESSION = array();
  session_start();
  $session = session_id();
  $session_time = $_SESSION['time'] = time();
}

// Function to connect to dot and get information
function do_emailex($url, $post)
{

  if($curl = curl_init("https://email.exeter.ac.uk/cgi-bin/$url"))
  {
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
    $buffer = curl_exec($curl);
    curl_close($curl);
    if(! $buffer) { return 'Error connecting to the List Server.';}
  }
  else { return 'Error setting up connection to the List Server.';}

  $strt = stripos($buffer, '<body>');
  $end = stripos($buffer, '</body>');

  $lnth = strlen($buffer);
  if($strt < 0 || $strt >= $end)
  {
    return "Error in data returned from the List Server: $strt/$end ($lnth)";
  }

  $lnth = $end - $strt - 6;
  $txt = substr($buffer, $strt+6, $lnth);
  $txt = trim($txt);

  if  (substr($txt, 0, 8) == 'SUCCESS:') { $txt = substr($txt, 8);}
  elseif(substr($txt, 0, 6) == 'ERROR:') { $txt = substr($txt, 6);}
  else
{
    $form = '
    <p><b>There was a problem obtaining information from the List Server:<b></p><p>
    ';
    $form .= $txt;
    $txt = $form.'</p>';
  }
  return $txt;
}

if($ok)
{
  $context = $_POST['context'];
  $action = $_POST['action'];
  $post = "session=$session&oldsession=$old_session&user=$user&secret=$credentials";

  // First pick out the cases where we deliver the initial list
  if(($action == 'list' and $context == 'lists') or
     ($action == 'Continue' and $context == 'list') or
     ($action == 'Cancel' and $context == 'listcreate') or
     ($action == 'Continue' and ($context == 'listcreate' or
                                 $context == 'listdelete' or
                                 $context == 'rlist')) or
     ($action == 'Continue' and $context == 'listedit') or
     ($action == 'Leave alone' and ($context == 'listdelete' or
                                    $context == 'listedit')))
  {
    if(isset($_POST['lname'])) { $post .= '&lname=' . urlencode($_POST['lname']);}
    if(isset($_POST['mtime'])) { $post .= '&mtime=' . urlencode($_POST['mtime']);}
    $form = do_emailex('listadmin', $post);
  }
  // Pick out the end of session case
  elseif($action == 'Done')
  {
    header('Location: /utils/lists/done.php');
    $form = do_emailex('listdone', $post);
  }
  // Follow-on after end of session case
  elseif($action == 'Continue' and $context == 'timeout')
  {
    header('Location: /utils/lists/');
    $form = '';
  }
  // Create a new list
  elseif($action == 'Create')
  {
    $post .= '&lnamenew=' . urlencode($_POST['lnamenew']);
    $post .= '&ownernew=' . urlencode($_POST['ownernew']);
    $post .= '&descriptionnew=' . urlencode($_POST['descriptionnew']);
    $post .= '&open=' . urlencode($_POST['open']);
    $post .= '&lrtype=' . urlencode($_POST['lrtype']);
    $post .= '&mrtype=' . urlencode($_POST['mrtype']);
    $post .= '&ortype=' . urlencode($_POST['ortype']);
    $post .= '&rlistnew=' . urlencode($_POST['rlistnew']);
    $post .= '&ownersnew=' . urlencode($_POST['ownersnew']);
    $post .= '&errmsg=' . urlencode($_POST['errmsg']);
    if($context == 'listcreate') { $form = do_emailex('listadmincreateit', $post);}
    elseif($context == 'lists')  { $form = do_emailex('listadmincreate', $post);}
  }
  // Offer to edit or delete a list
  elseif(($action == 'View' or $action == 'Delete') and $context == 'lists')
  {
    $post .= '&action=' . $action;
    $post .= '&lname=' . urlencode($_POST['lname']);
    $form = do_emailex('listadminedit', $post);
  }
  // Confirm delete
  elseif($action == 'Delete' and $context == 'listdelete')
  {
    $post .= '&action=' . $action;
    $post .= '&lname=' . urlencode($_POST['lname']);
    $post .= '&mtime=' . urlencode($_POST['mtime']);
    $form = do_emailex('listadmindeleteit', $post);
  }
  // Go ahead with editing a list
  elseif($action == 'Apply Changes' and $context == 'listedit')
  {
    $post .= '&action=' . $action;
    $post .= '&lname=' . urlencode($_POST['lname']);
    $post .= '&lnamenew=' . urlencode($_POST['lnamenew']);
    $post .= '&mtime=' . urlencode($_POST['mtime']);
    $post .= '&ownernew=' . urlencode($_POST['ownernew']);
    $post .= '&descriptionnew=' . urlencode($_POST['descriptionnew']);
    $post .= '&open=' . urlencode($_POST['open']);
    $post .= '&lrtype=' . urlencode($_POST['lrtype']);
    $post .= '&mrtype=' . urlencode($_POST['mrtype']);
    $post .= '&ortype=' . urlencode($_POST['ortype']);
    $post .= '&rlistnew=' . urlencode($_POST['rlistnew']);
    $post .= '&errmsg=' . urlencode($_POST['errmsg']);
    $topowner = $_POST['topowner']; 
    $post .= '&topowner=' . urlencode($topowner);
    if(is_numeric($topowner))
    {
      if($topowner > 50) { $topowner = 0;}   # Just be sensible!
    }
    else { $topowner = 0;}                   # Just be sensible!
    for($i=1; $i<=$topowner; $i++)
    {
      $owner = 'owner' . $i;
      $post .= "&$owner=" . urlencode($_POST[$owner]);
    }
    $form = do_emailex('listadmineditit', $post);
  }
  // Offer to change a restrictions file
  elseif(($action == 'View' and $context == 'rlist') or
         ($action == 'Continue' and $context == 'redit'))
  {
    $post .= '&action=' . $action;
    $post .= '&rlist=' . urlencode($_POST['rlist']);
    $form = do_emailex('listadminredit', $post);
  }
  // Go ahead with changes to restrictions file
  elseif((($action == 'Remove') or
          ($action == 'Add') or
          ($action == 'Rename')) and $context == 'rlist')
  {
    $post .= '&action=' . $action;
    $post .= '&rlist=' . urlencode($_POST['rlist']);
    $post .= '&mtime=' . urlencode($_POST['mtime']);
    $post .= '&rlistnew=' . urlencode($_POST['rlistnew']);
    $post .= '&additions=' . urlencode($_POST['additions']);
    $topaddr = $_POST['topaddr']; 
    $post .= '&topaddr=' . urlencode($topaddr);
    if(is_numeric($topaddr))
    {
      if($topaddr > 500) { $topaddr = 0;}   # Just be sensible!
    }
    else { $topaddr = 0;}                   # Just be sensible!
    for($i=1; $i<=$topaddr; $i++)
    {
      $addr = 'addr' . $i;
      $post .= "&$addr=" . urlencode($_POST[$addr]);
    }
    $form = do_emailex('listadminreditit', $post);
    #$form = do_emailex('cgidump.pl', $post);
  }
  else
  {
    $form = '<p>Invalid request from form. Action is '.$action.' Context is '.$context.'</p>';
    $ok = 0;
  }
}

echo '
<html><head>
<style type="text/css">
 body { margin-left:70px; margin-top:30px; margin-right:50px;}
 p  { font-family: Arial; font-size: 90%; line-height: 150%;}
 td { font-family: Arial; font-size: 90%; vertical-align: top;}
 li { font-family: Arial; font-size: 90%;}
 h2 { color:#21aae2;}
 h3 { color:#21aae2;}
</style>
<title>E-Mail List Administration</title></head>
<body>
<img src="/media/universityofexeter/webteam/styleassets/images/gif" width="162" height="60" />
';
echo $form;
echo '
</body>
';
//modifyTemplate($site, $uri, $heading, $form);

?>
