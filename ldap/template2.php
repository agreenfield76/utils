<?php

$template = "/home/webs/$site/.template/index.html";

function printHeader($site, $uri, $heading) {

    global $template;
    $lines = file($template);

    foreach ($lines as $line) {
        if (preg_match("/www.exeter.ac.uk\/\.template/", $line)) {
            $line = str_replace("www.exeter.ac.uk/.template", $site . $uri, $line);
        }
        if (preg_match("/Template/", $line)) {
            $line = str_replace("Template", $heading, $line);
        }
        if (preg_match("/HEADING/", $line)) {
            $line = str_replace("HEADING", $heading, $line);
        }
        if (preg_match("/CONTENT/", $line)) {
            break;
        }
        echo "$line";
    }

}

function printFooter($site, $uri) {

    global $template;
    $lines = file($template);
    $show = 0;


    // Get rid of the facebook/twitter crap in the footer (which causes insecure warnings on https)
//    $pattern = "/\<div id=\"footerright\"\>(.+?)\<\/div\>/s";
//    $template = preg_replace($pattern, "", $template);

    // And remove the "World Class University" campaign image, which sits behind the top right
//    $pattern = "/\<a href=\"http\:\/\/www\.exeter\.ac\.uk\/campaign\" title=\"Visit the Campaign website\"\>(.+?)\<\/a\>/s";
//    $template = preg_replace($pattern, "", $template);


    foreach ($lines as $line) {
        if (preg_match("/www.exeter.ac.uk\/\.template/", $line)) {
            $line = str_replace("www.exeter.ac.uk/.template", $site . $uri, $line);
        }
        if ($show) { echo "$line"; }
        if (preg_match("/CONTENT/", $line)) {
            $show = 1;
        }
    }

}



function display($field) {

    // Print HTML update form

    $display = '<p><form method="post" action="' . $_SERVER['PHP_SELF'] . '" name="DirectoryUpDate">' .
               '<input name="attribute" type="hidden" value="' . $field . '" />' .
               '<input name="' . $field . '" size="14" />&nbsp;&nbsp;' .
               '<input name="update" type="submit" value="update" />' .
               '</form>' .
               '<br /><a href="javascript:void(window.open(\'update-' .  $field .
               '.html\',\'_190x240\',\'height=600,width=500\'));">Read this first</a></p>';

    return($display);

}


function displayprint($field,$value) {

    // Print HTML update form

    $display = '<p><form name="print" method="post" action="' . $_SERVER['PHP_SELF'] . '" >' .
               '<input name="attribute" type="hidden" value="' . $field . '" />' .
	       '<select name ="' .$field. '" id="' .$field. '" onChange="enable_input(this)">'.
	       '<option value="billable">Use Multiple Billing Codes</option>'.
               '<option value="nonbillable">Use Personal Account</option>'.
               '<option value="other">Use single billing code for all copy/print</option>'.
               '</select>&nbsp'.
               '<input id="other" name="other" size="14" type="text" value=""/>&nbsp;&nbsp;' .
               '<input name="update" type="submit" value="update" />' .
               '</form>' .
               '<br /><a href="javascript:void(window.open(\'update-' .  $field .
               '.html\',\'_190x240\',\'height=600,width=500\'));">Read this first</a></p>';

    return($display);

}

function wordcheck($in) {

    // Parse input text

    return preg_match("/^[a-zA-Z\d- .]*$/", $in);

}

?>
