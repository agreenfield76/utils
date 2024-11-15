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
//$options = array("", "", "");
$options = array("", ""); 
$style = "visibility:hidden";
$htmlvalue= "";
//Make x amount of blank fields, where x is the amount of options
//if($value == "billable"){$options[0] = "selected='selected'";}
if($value == "billable"){$options[0] = "selected='selected'";}
//elseif($value == "nonbillable"){$options[1] = "selected='selected'";}//etc etc
//else{$options[2] = "selected='selected'";$style = "visibility:visible";$htmlvalue=$value;}
else{$options[1] = "selected='selected'";$style = "visibility:visible";$htmlvalue=$value;}

    $display = '<p><form name="print" method="post" action="' . $_SERVER['PHP_SELF'] . '" >' .
               '<input name="attribute" type="hidden" value="' . $field . '" />' .
	       '<select name="' .$field. '" id="' .$field. '" onChange="disable_input()">'.
	       '<option value="billable" '.$options[0].'>Use Multiple Billing Codes</option>'.
               //'<option value="other" '.$options[1].'>Use single billing code for copy/print</option>'.
		//'<option value="nonbillable" '.$options[1].'>Use Personal Account</option>'.
               //'<option value="other" '.$options[2].'>Use single billing code for copy/print</option>'.
               '</select>'.
               '<input name="update" type="submit" value="update" /><p>' .
               //'<input id="other" name="other" size="4" type="text" style="'.$style.'" value="'.$htmlvalue.'"/>&nbsp;&nbsp;' .
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
