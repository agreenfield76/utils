<?php

$site = 'as.exeter.ac.uk';
include('/home/webs/' . $site . '/utils/helpdesk/template.php');
$uri = '/utils/helpdesk/search.php';
$heading = 'Email Search';

$content .= '
';

modifyTemplate($site, $uri, $heading, $content);

?>
