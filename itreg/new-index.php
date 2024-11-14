<?php
// Include web header
include(".includes/new-functions.php");

// disable browser caching
ae_nocache();

$heading = '';
$error = $content = $sits = '';
$hide_header = $set_hide = '';
$firsttime = $tries = 0;

$set_hide = "";
$hide_header="false";
if(isset($_SERVER['QUERY_STRING'])){
//echo ("Is set".print_r($_SERVER));
   $url_string_array=array();
   $url_string=$_SERVER['QUERY_STRING'];
   parse_str($url_string, $url_string_array);

   //print_r($url_string_array);

   if(isset($url_string_array['hide'])&& $url_string_array['hide'] != ""){
      $hide_header = $url_string_array['hide'];
      $set_hide = "?hide=$hide_header";
   }
}
//echo("hide is ".$hide_header."<br>");

function form($sits,$dob,$tries)
{
   global $hide_header, $set_hide;
   $msg = "<center>
            <form id=\"collectform\" name=\"collectform\" method=\"post\" autocomplete=\"off\" action=\"new-index.php". $set_hide ."\" onsubmit=\"document.collectform.submit.disabled='true';document.collectform.submit.value='  Please wait  '\">
            <table align=\"center\">
               <tr>
                  <td><div align=\"right\"><b>Your Student ID:</b></div></td>
                  <td><input id=\"sits\" type=\"text\" name=\"sits\" size=\"10\" maxlength=\"9\" value=\"$sits\"/> e.g. 630000000</td>
               </tr>
               <tr>
                  <td><div align=\"right\"><b>Your Date of Birth:</b></div></td>
                  <td><input id=\"datepicker\" type=\"text\" name=\"dateofbirth\" size=\"10\" maxlength=\"10\" value=\"$dob\"/> e.g. DD/MM/YYYY</td>
               </tr>
               <tr>
                  <td colspan=\"2\"><div align=\"center\">";
   $msg .= tsandcs($hide_header);
   if ($tries < 5) {
      $msg .=    "<input style=\"font-size:18px;\" type=\"submit\" id=\"submit\" value=\"  Activate  \"/>";
   }
   $msg .=       "<input type=\"hidden\" name=\"firstpage\" value=\"no\"/>
                  <input type=\"hidden\" name=\"tries\" value=\"$tries\"/>
                  </div></td>
               </tr>
            </table>
            </form>
            </center>";
   return $msg;
}


function firstpage()
{
   global $hide_header, $set_hide;
   $msg = "
   <p style=\"font-size:14px;text-align:left;\">To activate your IT account and receive your IT username and password, please:</p>
   <ol>
      <li>Read our regulations at ";
if($hide_header != "false") {
   $msg .= "www.exeter.ac.uk/it/regulations/regs";
} else {
   $msg .= "<a href=\"http://www.exeter.ac.uk/it/regulations/regs\" target=\"_blank\">www.exeter.ac.uk/it/regulations/regs</a>";
}
$msg .= "<br><br>Note in particular that:<br>
         <ul style=\"font-size:13px;\">
            <li>You must keep your password secret. University staff will never ask for your password, in person or by email</li>
            <li>You must not use, copy, download or distribute copyright material (such as full-length feature films) without permission</li>
            <li>You must not send email spam</li>
         </ul>
      </li>
      <li>Learn how to protect your cyberspace<br>
         <ul style=\"font-size:13px;\">
            <li>Promptly install all security patches for your computer and smartphone</li>
            <li>Run anti-virus software on your computer and make sure the definitions are up to date</li>
            <li>Never click links or attachments in emails unless you know the sender and you were expecting the message</li>
            <li>Never log in over the Internet except on a secure connection (look for the browser's locked padlock symbol)</li>
         </ul>
      </li>
   </ol>
   <p>To find out more about safe computing, please visit ";
if($hide_header != "false") {
   $msg .= "www.exeter.ac.uk/infosec/tips";
} else {
   $msg .= "<a href=\"http://www.exeter.ac.uk/infosec/tips\" target=\"_blank\">www.exeter.ac.uk/infosec/tips</a>";
}
$msg .= "</p>
   <form id=\"firstform\" name=\"firstform\" method=\"post\" action=\"new-index.php". $set_hide ."\" onsubmit=\"document.firstform.submit.disabled='true';document.firstform.submit.value='  Please wait  '\">
   <div align=\"center\"><input style=\"font-size:32px;font-weight:bold;\" type=\"submit\" id=\"submit\" value=\"  Next  \"/></div>
   <div align=\"center\"><input type=\"hidden\" name=\"firstpage\" value=\"yes\"/></div>
   <div align=\"center\"><input type=\"hidden\" name=\"dateofbirth\" value=\"\"/></div>
   </form>
   ";
   return $msg;
}

function tsandcs()
{
   global $hide_header;
   //echo("hide is ".$hide_header."<br>");
   $msg = "<p style=\"font-size:14px;font-weight:bold;\">By activating your University of Exeter IT Account you agree to be bound by all our<br> regulations for using computing facilities ";
   if($hide_header != "false") {
      $msg .= "www.exeter.ac.uk/it/regulations/regs";
   } else {
      $msg .= "<a href=\"http://www.exeter.ac.uk/it/regulations/regs\" target=\"_blank\">www.exeter.ac.uk/it/regulations/regs</a>";
   }
   $msg .= "</p>";
   return $msg;
}

# This actually does the work!
if (check_access())
{
   $sucess = 0;
   if (isset($_POST['firstpage']))
   {
      if (isset($_POST['dateofbirth']))
      {
         $dob = $_POST['dateofbirth'];
         if (isset($_POST['sits']))
         {
            $sits = $_POST['sits'];
            if (check_user($_POST['sits'],$_POST['dateofbirth']))
            {
               if(_collectPass($uid)) { $sucess = 1; }
            }
         }
      }
   } else
   {
      $content .= firstpage();
      $firsttime = 1;
   }

   if(!$sucess && !$firsttime)
   {
      //$content .= regdate();
      if (isset($_POST['tries']))
      {
         if ($_POST['tries'] > 3) {
            if (isset($_POST['sits'])) {
               attempts_exceeded($_POST['sits']);
            }
            $error = "You have exceeded the maximum number of attempts, please contact sid@exeter.ac.uk";
         }
         $tries = $_POST['tries'] + 1;
      }
      $content .= "<p style=\"font-size:14px;\">Now please enter the following:</p>";
      $content .= "<p style=\"text-align:center;font-size:16px;;font-weight:bold;color:red;\">$error</p>";
      $content .= form($sits,$dob,$tries);
   }
}

$content .= '
   <link rel="stylesheet" href="css/jquery-ui.css" type="text/css" />
   <script src="js/jquery.js"></script>
   <script src="js/jquery-ui.js"></script>
   <script>
      $(function() {
         $( "#datepicker" ).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd/mm/yy",
            yearRange: "-80:-14",
            defaultDate: "12/07/1989"
            });
      });
   </script>
   ';

if($hide_header!="false") {
   echo "<head>
         <link rel=\"shortcut icon\" href=\"/media/universityofexeter/webteam/styleassets/images/favicon.ico\" />
         <!-- Academic Services -->
         <style type=\"text/css\" media=\"all\">@import \"/media/universityofexeter/webteam/styleassets/css/global.css\";</style>
         <!--[if IE 6]><style type=\"text/css\" media=\"all\">@import \"/media/universityofexeter/webteam/styleassets/css/global_IE6.css\";</style><![endif]-->
         <!--[if IE 7]><style type=\"text/css\" media=\"all\">@import \"/media/universityofexeter/webteam/styleassets/css/global_IE7.css\";</style><![endif]-->
         <style type=\"text/css\" media=\"print\">@import \"/media/universityofexeter/webteam/styleassets/css/print.css\";</style>
         <link rel=\"stylesheet\" href=\"/media/universityofexeter/webteam/styleassets/css/schools.css\" type=\"text/css\" media=\"screen\" />
         <link rel=\"stylesheet\" href=\"/media/universityofexeter/webteam/styleassets/css/jd.gallery_2col.css\" type=\"text/css\" media=\"screen\" />
         <style type=\"text/css\" media=\"all\">@import \"/media/universityofexeter/webteam/styleassets/css/academic.css\";</style>
         </head>";
   echo "<body style=\"text-align:center;\"><div id=\"wrapper\">
            <div id=\"wrapperinner\">
               <div id=\"header\">
                  <img src=\"/media/universityofexeter/webteam/styleassets/images/logo.gif\" alt=\"University of Exeter\" class=\"logo\" width=\"162\" height=\"60\"/>
                  <!--<img src=\"/media/universityofexeter/alumniandsupporters/campaign/campaignlogo.gif\" alt=\"Visit the Campaign website\" class=\"campaignlogo\" width=\"98\" height=\"98\"/>-->
               </div>
            </div>
         </div>
         <div style=\"margin-left:auto;margin-right:auto;width:70%;text-align:left;>";
   echo $content;
   echo "</div></body>";
} else {
   modifyTemplate($site, $uri, $heading, $content);
}
?>
