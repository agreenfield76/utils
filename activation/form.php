<?php 

// Include web header
include(".includes/header.php");

$heading = 'Online Staff IT Account Activation';

if (! isset($_POST["User"]))
{
   $content = '<h3><br>Invalid user details, please try again.</h3>';
} else
{

   # Details passed via post method
   $user = $_POST["User"];
   $name = $_POST["Name"];
   $email = $_POST["Email"];
   $number = $_POST["Number"];
   $card = $_POST["Card"];

   $content = "
   <h3>Collect IT Account details for the following user</h3>
   <h4>Please note that this can only be done once</h4>
   <br>
      <table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
         <tr>
            <td>Name:</td>
            <td>$name</td>
         </tr>
         <tr>
            <td>E-mail:</td>
            <td>$email</td>
         </tr>
         <tr>
            <td>Employee No:</td>
            <td>$number</td>
         </tr>
         <tr>
            <td>UniCard No:</td>
            <td>$card</td>
         </tr>
         <tr>
            <td>&nbsp;</td>
            <td>
                 <form id=\"form1\" name=\"form1\" method=\"post\" action=\"itpdf.php\"
                      onsubmit=\"document.form1.submit.disabled='true';
                      document.form1.submit.value=' Please wait '\">
                 <input type=\"submit\" name=\"submit\" id=\"submit\" value=\" Activate Account \">
                 <input type=\"hidden\" name=\"User\" value=\"$user\">
                 </form>
            </td>
         </tr>
         <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
         </tr>
         <tr>
            <td>&nbsp;</td>
            <td>
                <form id=\"form2\" name=\"form2\" method=\"post\" action=\"index.php\">
                <input type=\"submit\" name=\"submit\" id=\"submit\" value=\" Return to Account List \">
                </form>
            </td>
         </tr>
      </table>
   ";
}

modifyTemplate($site, $uri, $heading, $content);
?>
