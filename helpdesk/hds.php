<?php 
// Include web header
include(".includes/functions.php");

$url = $_SERVER['PHP_SELF'];
$content = $error = '';
$valid_cmds = array('RESET', 'COLLECT', 'UNBLOCK');
$suthuser = '';
$heading = 'Helpdesk Support';

function form()
{
    global $url;
    $form = "
    <form method=\"post\" action=\"$url\">
        <table>
        <tr><td>Username or ID number:</td>
            <td><input type=\"text\" name=\"name\" tabindex=\"1\"/></td></tr>
        <tr><td>&nbsp;</td>
            <td><input type=\"submit\" name=\"action\" tabindex=\"2\" value=\"Get Account Details\" /></td></tr>
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
                if(doAction($user, $action));
            }
            else
            {
              $error = "$action is not recognised!";
            }    
        }
    }
    elseif (isset($_POST['name']))
    {
        $name = $_POST['name'];
        lookup($name);
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

$content .= "<p><a href=\"$url\">Enter new details</a></p>\n";

modifyTemplate($site, $uri, $heading, $content);
?>
 
