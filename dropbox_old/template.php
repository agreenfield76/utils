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

function modifyTemplate($site, $uri, $heading, $content) {

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
            $line = str_replace("CONTENT", $content, $line);
        }
        echo "$line";
    }

}

function display($field) {

    // Print HTML update form

    $display = '<p><form method="post" action="' . $_SERVER['PHP_AUTH_USER'] . '" name="DirectoryUpDate">' .
               '<input name="attribute" type="hidden" value="' . $field . '" />' .
               '<input name="' . $field . '" size="14" />&nbsp;&nbsp;' .
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
