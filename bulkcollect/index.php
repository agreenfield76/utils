<?php 
// Include web header
include(".includes/functions.php");

$url = $_SERVER['PHP_SELF'];
$content = $error = '';
$suthuser = '';
$heading = 'Bulk Student IT Account Collection';

function form()
{
    global $url;
    $form = "
    <form id=\"collectform\" name=\"collectform\" method=\"post\" action=\"$url\" onsubmit=\"document.collectform.submit.disabled='true';document.collectform.submit.value='  Please wait  '\">
        <p>Enter the list of student numbers below, one per line, then click Get Account Details.</p> 
        <table>
        <tr><td><textarea name=\"numbers\" rows=\"20\" cols=\"20\"></textarea></td></tr>
        <tr><td><input type=\"submit\" id=\"submit\" value=\"Get Account Details\" /></td></tr>
        </table>
    </form>";
    return $form;
}

# This actually does the work!
if (check_access())
{
    if (isset($_POST['username']))
    {
        $user = $_POST['username'];
        if (isset($_POST['action']))
        {   
            $action = $_POST['action'];
            if(in_array("$action", $valid_cmds))
            {
                #doAction($user, $action);
            }
            else
            {
              $error = "$action is not recognised!";
            }    
        }
    }
    elseif (isset($_POST['numbers']))
    {
        $numbers = $_POST['numbers'];
        lookup($numbers);
    }
    else
    {
        $content .= form();
    }
}

if (!empty($error))
{
    $content .= "<p><font size=\"3\" color=\"red\">Error: $error</font></p>";
}

modifyTemplate($site, $uri, $heading, $content);
?>
 