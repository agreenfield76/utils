<?php

$template = "/home/webs/$site/library/.template/index.php";

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

?>
