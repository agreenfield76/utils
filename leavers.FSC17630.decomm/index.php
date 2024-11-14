<?php 
// Include web header
include(".includes/functions.php");

$heading = $content = $error = $user = $dd = $mm = $box = '';
$forwarding = 'Not Set';

# This actually does the work!
if (check_access())
{
    $done = 0;
    if (!isset($_POST['password']) || strlen($_POST['password']) == 0) {
        $error = "You must enter your password";
    } else {
        $pass = trim(filter_input(INPUT_POST,'password', FILTER_SANITIZE_STRING));
    }
    if (!isset($_POST['username']) || strlen($_POST['username']) == 0) {
        $error = "You must enter your username";
    } else {
        $user = trim(filter_input(INPUT_POST,'username', FILTER_SANITIZE_STRING));
    }
    if (isset($_POST['forwarding'])) {
        $box = trim(filter_input(INPUT_POST,'forwarding', FILTER_SANITIZE_STRING));
        $forwarding = "Set";
    }    
    if (isset($_POST['dd'])) {
        $dd = trim(filter_input(INPUT_POST,'dd', FILTER_SANITIZE_STRING));
        $mm = trim(filter_input(INPUT_POST,'mm', FILTER_SANITIZE_STRING));
        $id = (integer) $dd;
        $my = explode(" ",$mm);
        $im = $months[$my[0]];
        $iy = (integer) $my[1];
        if (!checkdate($im,$id,$iy)) { $error = "Invalid date selected, please check"; }
    }
    else {
        $dd = (integer) gmdate("d"); // set day to todays date
        $error = '';
    }
    if (isset($user) && isset($pass)) {
        $status = explode(":", checkuser($user,$pass));
        if ($status[0] == "error") { $error = $status[1]; }
        else { $email = $status[0]; }
    }
    
    if (isset($email)) {
        $data = $user .":". $dd ." ". $mm .":". $forwarding;
        $content = sendmail($email, $data);
        $done = 1;
    }
    
    if(!$done) {
        if ($error == '') { $content = shownotes(); }
        $content .= form($error, $user, $dd, $mm, $box);
    }
}

modifyTemplate($site, $uri, $heading, $content);