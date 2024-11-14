<?php

if($_SESSION['time'])
{
  session_unset();
  session_destroy();
  $_SESSION = array();
}

header('Location: /it/email/');

echo '<html><head><title>Managing University Distribution Lists</title></head><body></body></html>';

?>
