<?php

// dropbox-php v2
// Copyright (C) 2004, 2005 Doke Scott, doke at udel dot edu
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.


/* PHP rewrite of perl dropbox.cgi
 *
 * Pretty simple really:
 * (a) pick up
 * (b) drop off
 * (c) list files
 * (d) logout
 *
 * (c) and (d) only when authenticated
 * (a) via authentication or with claim ID and passcode
 * (b) via authentication or for internal user only


From original perl dropbox file:

# page flow 3: drop and pickup are context sensitive
# top main menu
#     drop off inbound form
#         dropped inbound
#     pickup outbound form
#         pickup outbound info
#             pickup outbound download
#     logged in menu
#         drop off in/out form
#             dropped in/out
#         pickup list  
#         pickup in/out form
#             pickup in/out info
#                 pickup in/out download
#         logout
 */

$site = 'as.exeter.ac.uk';
include('/home/webs/' . $site . '/utils/dropbox/template.php');
include ('/home/webs/' . $site . '/utils/dropbox/.includes/config.php');
$uri = '/utils/dropbox/';
$heading = 'File Drop Box';

$auth_authed = 0;

session_name("ASdropbox");
session_start();

// error_reporting(0);
//error_reporting(E_ALL);

// skip header for state being pickup_download:
$state = $_GET["state"];

if($state != "pickup_download")
{
    printHeader($site, $uri, $heading);
} // not pickup_download state (allow header)

function top_menu()
{
        global $login_link;
        global $drop_off_link;
        global $pick_up_link;
        global $auth_authed;
        global $insiders_formal;

        print "<p>If you are a $insiders_formal user, you may:" .
              "<ul><li>$login_link to access restricted features</li></ul></p>\n" .

              "<p>Non-Exeter users may either:\n" .
              "<ul><li>$drop_off_link (upload) a file for a $insiders_formal user, or</li>" .
              "<li>$pick_up_link (download) a file left for them by a $insiders_formal user.</li></ul></p>\n" .
              "<h2>Important Information</h2>\n" .
              "<p> <font color=\"red\"> As part of an ongoing process to consolidate our cloud storage and improve both security " .
              "and data integrity, we will be discontinuing the University's File Drop Box site on June 30th 2019. " .
              "By that date all data must be moved to the University's OneDrive allocation, " .
              "details of which can be found </font> <a href=\"https://as.exeter.ac.uk/it/files/onedrive/\">here</a></p>\n" .
              "<p>Please note that files are transferred over a secure connection (HTTPS), but it is advised that " .
              "that you do not use this facility for the transfer of any personal or sensitive data. Data encryption " .
              "of files prior to using this facility would improve the security of your data. Please also consider ". 
              "compressing files prior to transfer to conserve available upload space.  An unsupported utility for " .
              "compressing and encrypting files is <a href=\"http://www.exeter.ac.uk/infosec/encryptfiles/\">7-Zip</a></p>\n" .
              "<p><b>Non-person email accounts</b> (i.e. shared generic email addresses) may not be validated for this service.</p>\n";
}

function member_menu()
{
        global $drop_off_link;
        global $pick_up_link;
        global $list_link;
        global $logout_link;
        global $auth_authed;
        global $insiders_short;

        //assert($auth_authed);
        if($auth_authed)
        {
                print "<p>Logged in as <strong>" . $_SESSION['username'] . "</strong>.</p>";

                print "<p>You may:</p>
<ul>
<li>$drop_off_link (upload) a file for anyone ($insiders_short or other),</li>
<li>$list_link left for you, or</li>
<li>$pick_up_link (download) a file left for you.</li>
<li>$logout_link of dropbox.</li>
</ul>
";
        }
        else
        {
                top_menu();
        }
}

function auth_login_form()
{

        global $dropbox_baseref;

        // print login form
print "<form method=\"post\" action=\"".$dropbox_baseref."?state=member_menu\">
<input type=\"hidden\" name=\"state\" value=\"member_menu\" />
<input type=\"hidden\" name=\"id\" value=\"$id\" />
<table>
<tr><td>Username:</td><td><input type=\"text\" name=\"username\" size=\"8\" value=\"$username\" /></td></tr>
<tr><td>Password:</td><td><input type=\"password\" name=\"password\" size=\"8\" value=\"\" /></td></tr>
<tr><td></td><td><input type=\"submit\" name=\"login\" value=\"login\" /></td></tr>
</table>
</form>";
}

function check_email_address($email)
{
        // This checks that there is exactly one `@' sign as well:
        return preg_match("/^([A-Za-z][A-Za-z0-9\._-]*)@(\w[\w\d\.-]*)$/", $email);
}

function top_menu_footer()
{
        global $auth_authed;
        global $insiders_short;
        global $dropbox_baseref;

        print "<br /><hr />";
        
        if($auth_authed)
        {
                print "<p><a href=\"".$dropbox_baseref."?state=member_menu\">dropbox main menu</a></p>";
        }
        else
        {
                print "<p><a href=\"".$dropbox_baseref."?state=top_menu\">dropbox main menu</a></p>";
        }
}

function drop_off_inbound_form()
{
        global $auth_authed;
        global $insiders_formal;
        global $insiders_short;
        global $insiders_domain;
        global $max_upload_file_size;
        global $dropbox_baseref;
        
        $username = $_SESSION['username'];
        
        if($auth_authed)
        {
print
"<p>This web page will allow you to drop off (upload) a file for anyone (either
$insiders_short or others).  They will receive an automated email with the
information you enter below and instructions for downloading the file.</p>";
        }
        else
        {
print
"<p>This web page will allow you to upload a file for a $insiders_formal user.
They will receive an automated email with the information you enter below and
instructions for downloading the file.  Your IP address will be logged and
sent to the recipient.</p>";
        }


        // print multipart form
        // TODO : ensure max uploaded file size is OK (ie. LARGE :)
        print "<form enctype=\"multipart/form-data\" method=\"post\" action=\"".$dropbox_baseref."?state=drop2\">
               <input type=hidden name=state value=drop2>
               <input type=hidden name=\"MAX_FILE_SIZE\" value=\"$max_upload_file_size\" />
               <table>";
        
        if($auth_authed)
        {
                $from_name = $username;
                $from_org = $insiders_formal;
                $from_mail = get_email_address($username);
        }
        else
        {
                $from_name = $from_org = $from_mail = '';
        }

        print "<tr><td><strong>From:</strong></td><td></td><td></td></tr>
<tr><td></td><td>Your Name:</td>
<td><input type=text name=from_name size=30 value=\"$from_name\"> <i>(required)</i></td></tr>
<tr><td></td><td>Your Organization:</td><td><input type=text name=from_org size=30 value=\"$from_org\"></td></tr>";

        print "<tr><td></td><td>Your eMail: </td><td><input type=\"text\" name=\"from_email\" size=\"30\" value=\"$from_mail\" ";
        if($auth_authed)
        {
                print "readonly=\"readonly\" /> <i>(read-only)</i></td></tr>";
        }
        else
        {
                print"> <i>(required)</i></td></tr>";
        }

        print "<tr><td><strong>To:</strong></td><td></td><td></td></tr>
<tr><td></td><td>Their Name: </td><td><input type=\"text\" name=\"to_name\" size=\"30\" /></td></tr>";

        if($auth_authed)
        {
                print "<tr><td></td><td>Their eMail: </td><td><input type=text name=to_email size=30 value=\"\"> <i>(required)</i></td></tr>";
        }
        else
        {
                print "<tr><td></td><td>Their $insiders_short eMail: </td><td><input type=text name=to_email size=30 value=\"@$insiders_domain\"> <i>(required, and must be in the form user@$insiders_domain)</i></td></tr>";
        }
        
        print "<tr><td><strong>File:</strong></td><td></td><td></td></tr>
<tr><td></td><td>File pathname<br>on your system:
    </td><td><input id=\"file_name\" name=\"file_name\" size=\"50\" type=\"file\"> <i>(required, <strong>max size " . ($max_upload_file_size / (1024*1024))  . "Mb</strong>)</i></td></tr>
<tr><td></td><td>Brief description: </td><td><input type=text name=file_desc size=30></td></tr>
<tr><td></td><td><input type=submit name=upload value=upload></td><td></td></tr>
</table> </form>";
}

global $old_error_handler;

// user defined error handling function
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
        global $limit_results;
        global $old_error_handler;
        
        // Only check for warning (should be that there are too many results)
        if(($errno == E_WARNING) && (strstr($errmsg, "Sizelimit exceeded")))
        {
                if($limit_results > 1)
                {
                        echo "<p><strong>Too many results found, showing first " . $limit_results . " results.</strong><br /><br /></p>";
                }
                else
                {
                        echo "<p><strong>Too many results found, showing first result.</strong><br /><br /></p>";
                }
        }
        else
        {
                old_error_handler($errno, $errmsg, $filename, $linenum, $vars);
        }
} 

function verify_ldap_user($userpart, $full_email)
{
        global $ldap_host;
        global $ldap_list_base;
        global $old_error_handler;
  
        // ensure ldap search filter is in config.php and looks ok
        $ds = ldap_connect($ldap_host);

        if($ds)
        {
                // bind anonymously
                $r = ldap_bind($ds);

                // CURRENT : bit more complex now, since we want to find the
                // `mail' ldap entry and match the first part
                
                $filter = "(|(mail=$userpart*)(uid=$userpart))";
        
                // ldap_list does a single-level search
                //print "ldap_list($ds, $ldap_list_base, $filter);";
                $sr = ldap_list($ds, $ldap_list_base, $filter);

                $num_results = ldap_count_entries($ds, $sr);
                $info = ldap_get_entries($ds, $sr);
                ldap_close($ds);

                if($num_results == 0)
                {
                        return false;
                }

                // Peer at results and match email address
                // TODO : coping with more than one mail address per user?
                $ldap_email = $info[0]["mail"][0];
                $uid = $info[0]["uid"][0];

                //list($ldap_alias, $to_domain) = explode("@", $ldap_email, 2);

                return email_address_match($uid, $full_email);
        }
        else
        {
                write_syslog("ERROR : Unable to connect to LDAP server ($ldap_host)");
                print "<p><strong>Error: Unable to connect to LDAP server.</strong></p>";
                return false;
        }
}

function dropped_inbound()
{
        global $auth_authed;
        global $verbose;
        global $dropbox_dir;
        global $pickup_url_base;
        global $retention;
        global $insiders_domain;
        global $insiders_domain_short;
        global $max_upload_file_size;
        
        // get parameters for drop (from POST)
        $from_name = $_POST['from_name'];
        $from_org = $_POST['from_org'];
        $from_email = $_POST['from_email'];
        $to_name = $_POST['to_name'];
        $to_email = $_POST['to_email'];
        $file_name = $_FILES['file_name']['name'];//$_POST['file_name'];
        $file_desc = $_POST['file_desc'];

        write_syslog("drop from name:$from_name org:$from_org email:$from_email, to name:$to_name email:$to_email, file:" . $_FILES['file_name']['name'] . ", session:" . session_id());

        // Check for some common errors:
        $error = $_FILES['file_name']['error'];

        switch($error)
        {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                        print "<p><strong>Error: Uploaded file is too large (maximum of " . ($max_upload_file_size / (1024*1024)) . "Mb).</strong></p>";
                        return;
                case UPLOAD_ERR_NO_FILE:
                        print "<p><strong>Error: No file specified for upload.</strong></p>";
                        return;
                case UPLOAD_ERR_PARTIAL:
                        print "<p><strong>Error: File only partially uploaded.</strong></p>";
                        return;
                
                case UPLOAD_ERR_OK:
                        break;

                default:
                        print "<p><strong>Error: Unknown error during file upload!</strong></p>";
                        return;
        }
        
        // Check parameters are set
        if(!$from_name || !$from_email || !$to_email || !$file_name)
        {
                print "<p><strong>Error: Required parameter missing.  Please click back and fill in the missing data.</strong></p>";
                #print "<p><strong>FN: $from_name, FE: $from_email, TE: $to_email, FILE: $file_name</strong></p>";
                return;
        }
        
        // Check for zero-size file (usually indicates that the browser is
        // unhappy in some way:
        if(($_FILES['file_name']['size'] == 0) ||
           ($_FILES['file_name']['tmp_name'] == ''))
        {
                // Something is wrong..
                print "<p><strong>Error: Uploaded file is invalid!</strong></p>";
                print "<p>Note that files can only be a maximum size of " .
                        ($max_upload_file_size / (1024*1024)) . " Mb.</p>";
                return;
        }

        if(!check_email_address($to_email))
        {
                print "<p><strong>Error: 'To' email address must be of the form 'username@domainname'.</strong></p><p>The username must start with a letter, and contain only letters, numbers, periods, underscores and hyphens.</p>";
                sleep(5); // stop them from guessing too fast
                return;
        }

        // Chop up email address to get username and domain
        list($to_userpart, $to_domain) = explode("@", $to_email);

        if($verbose)
        {
                print "<p><strong>to_domain '$to_domain', insiders_domain '$insiders_domain'.</strong></p>";
        }
                
        // Check against long and short forms of internal domain name
        if((strcasecmp($to_domain, $insiders_domain) == 0) ||
           (strcasecmp($to_domain, $insiders_domain_short) == 0))
        {
                // Dropping for an insider
                if(!verify_ldap_user($to_userpart, $to_email))
                {
                        //sleep(3);
                        print "<p><strong>Error: unknown user '$to_userpart'</strong></p>";
                        return;
                }
        }
        elseif(!$auth_authed)
        {
                print "<p><strong>You must be an authenticated $insiders_formal user to drop a file for an outsider.</strong></p>";
                return;
        }
                
        $passcode = rand(1, 0x7ffffffe); // don't want it to be zero
        
        if(!$file_name)
        {
                print "<p><strong>Error: No file name</strong></p>";
                return;
        }

        if($verbose)
        {
                // display uploaded file info (debug output really)
                print "<p>Upload info:<br />";
                print "name: " . $_FILES['file_name']['name'] . "<br />";
                print "type: " . $_FILES['file_name']['type'] . "<br />";
                print "size: " . $_FILES['file_name']['size'] . "<br />";
                print "tmp_name: " . $_FILES['file_name']['tmp_name'] . "<br />";
                print "error: " . $_FILES['file_name']['error'] . "<br />";
                print "</p>";
        }

        // find an unused ID for file numbering
        $n = 0;
        while(1)
        {
                $id = rand(1, 0x7ffffffe);
                $lock_link = "$dropbox_dir/$id.lock";
                $control_file = "$dropbox_dir/$id.control";
                $lock_link_pid = "$lock_link." . getmypid();
                
                if(!file_exists($lock_link))
                {
                        if(!file_exists($control_file))
                        {
                                // Argh, symlink is disabled(*&%
                                //if(symlink(getmypid(), $lock_link))
                                //{
                                //        if(strcmp(readlink($lock_link), getmypid()))
                                //        {
                                //                break 1; // got lock
                                //        }
                                //}

                                // Instead, touch lock and lock.PID
                                if(touch($lock_link) && touch($lock_link_pid))
                                {
                                        if(file_exists($lock_link) &&
                                           file_exists($lock_link_pid))
                                        {
                                                break 1; // got lock
                                        }
                                }
                        }
                }

                if($n++ >= 1)//000)
                {
                        print "<p><strong>Error: Unable to obtain dropbox lock.</strong></p>";
                        return;
                }
        }

        // open control file
        $fp = fopen($control_file, "w");

        if(!$fp)
        {
                print "<p><strong>Error: Unable to create control file: $control_file</strong></p>";
                unlink($control_file);
                unlink($lock_link_pid);
                unlink($lock_link);
                return;
        }
        chmod($control_file, 0600);

        // move_uploaded_file to somewhere sensible (in docroot?)
        $data_file = "$dropbox_dir/$id.data";

        if(!move_uploaded_file($_FILES['file_name']['tmp_name'], $data_file))
        {
                print "<p><strong>Error: Unable to move uploaded file to dropbox space.</strong></p>";
                unlink($control_file);
                unlink($lock_link_pid);
                unlink($lock_link);
                return;
        }
        chmod($data_file, 0600);

        $drop_date = date("Y/m/d H:i:s");
        
        $file_content_type = $_FILES['file_name']['type'];
        $file_basename = $_FILES['file_name']['name'];
        $file_length = $_FILES['file_name']['size'];
        $remote_addr = $_SERVER['REMOTE_ADDR'];

        // TODO : tidy down to basename and hax windows filenames
//     $file_basename = $file_name;
//     $file_basename =~ s!.*\\([^\\])!$1!; # stupid windows filenames
//     #$file_basename =~ s!.*/([^/])!$1!;  # unix browsers only send basename?


        // Write control file info
        if(!fwrite($fp,
                   "id $id\n" .
                   "drop_date $drop_date\n"))
        {
                print "<p><strong>Error: Unable to write control file</strong></p>";
                fclose($fp);
                unlink($data_file);
                unlink($control_file);
                unlink($lock_link_pid);
                unlink($lock_link);
                return;
        }
        
        if($auth_authed)
        {
                if(!fwrite($fp,
                           "drop_authed $auth_authed\n" .
                           "drop_authed_by $username\n"))
                {
                        print "<p><strong>Error: Unable to write control file</strong></p>";
                        fclose($fp);
                        unlink($data_file);
                        unlink($control_file);
                        unlink($lock_link_pid);
                        unlink($lock_link);
                        return;
                }
        }

        if(!fwrite($fp,
                   "passcode $passcode\n" .
                   "from_name $from_name\n" .
                   "from_org $from_org\n" .
                   "from_email $from_email\n" .
                   "from_ip $remote_addr\n" .
                   "to_name $to_name\n" .
                   "to_email $to_email\n" .
                   "data_file $data_file\n" .
                   "file_name $file_name\n" .
                   "file_basename $file_basename\n" .
                   "file_length $file_length\n" .
                   "file_content_type $file_content_type\n" .
                   "file_desc $file_desc\n"))
        {
                print "<p><strong>Error: Unable to write control file</strong></p>";
                fclose($fp);
                unlink($data_file);
                unlink($control_file);
                unlink($lock_link_pid);
                unlink($lock_link);
                return;
        }
        
        if(!fclose($fp))
        {
                print "<p><strong>Error: Unable to write control file</strong></p>";
                fclose($fp);
                unlink($data_file);
                unlink($control_file);
                unlink($lock_link_pid);
                unlink($lock_link);
                return;
        }
        
        // Clean up files
        unlink($lock_link_pid);
        unlink($lock_link);
        write_syslog("successfully saved file $id, $file_basename, $file_length bytes");

        // let them know it worked
        //$pickup_url = "https://$http_host$script_name?state=pickup_info&id=$id";
        $pickup_url = $pickup_url_base . "$id";

        print "<p>File successfully uploaded and saved.<br />" .
                "<strong>It will be held for $retention days (unless we run low on disk space).</strong><br />" .
                "File name is '$file_basename'.<br />" .
                "File content type is '$file_content_type'.<br />" .
                "File length is $file_length bytes.<br />" .
                "File description is '$file_desc'.<br />" .
                "Pickup claim id is <a href=\"$pickup_url\">$id</a>.<br />" .
                "Pickup passcode is $passcode</p>";

        // Send email notice to recipient
        // and remove incompetent characters from subject line
        $subject = preg_replace("/\'/", "", "Dropbox : $from_name ($from_email) has dropped off a file ($file_basename) for you");

        $message = "$from_name ($from_email) has dropped off a file for you named '$file_basename'.

  You may pick up this file at:

$pickup_url

  You will need the claim passcode '$passcode' to retrieve the file.  

  It will be held for $retention days (unless we run low on disk space).

 Additional info:
     Claim ID:            $id
     Claim passcode:      $passcode
     Drop date            $drop_date
     File name:           $file_basename
     File size:           $file_length bytes
     File content_type:   $file_content_type
     File description:    $file_desc
";
        
        // Send the email with some extra headers
        $headers .= "MIME-Version: 1.0\r\n"; 
        $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n"; 
        $headers .= "From: " . $from_name . " <" . $from_email . ">\r\n"; 
        //$headers .= "To: " . $contactname . " <". $contactemail. ">\r\n"; 
        /*$headers .= "Reply-To: " . $myname . " <$myemail>\r\n";*/ 
        $headers .= "X-Priority: 1\r\n"; 
        $headers .= "X-MSMail-Priority: High\r\n"; 
        $headers .= "X-Mailer: Dropbox 1.1"; 

        mail($to_email, $subject, $message, $headers);

//     from name: $from_name
//     from org: $from_org
//     from email: $from_email
//     from ip: $remote_addr
//     To name: $to_name
//     To email: $to_email
}

// CURRENT : get email address from LDAP
function get_email_address($username)
{
        global $insiders_domain;
        global $ldap_host;
        global $ldap_list_base;
        global $old_error_handler;

  
        // ensure ldap search filter is in config.php and looks ok
        $ds = ldap_connect($ldap_host);

        if($ds)
        {
                // bind anonymously
                $r = ldap_bind($ds);

                $filter = "(uid=$username)";
        
                // ldap_list does a single-level search
                //print "ldap_list($ds, $ldap_list_base, $filter);";
                $sr = ldap_list($ds, $ldap_list_base, $filter);
                
                if(!$sr)
                {
                        return "Error attempting to find user in LDAP";
                }
                
                $num_results = ldap_count_entries($ds, $sr);
                $info = ldap_get_entries($ds, $sr);
                ldap_close($ds);

                if($num_results > 1)
                {
                        return "Multiple results found for user";
                }

                // TODO : coping with more than one mail address per user?
                return $info[0]["mail"][0];
        }
        else
        {
                write_syslog("ERROR : Unable to connect to LDAP server ($ldap_host)");
                return "Error connecting to LDAP server";
        }
}

function email_address_match($username, $control_email)
{
        global $insiders_domain;
        global $insiders_domain_short;
                         
        // CURRENT : Competent checks.

        // Check ldap mail entry (zero or more) (alias@exeter.ac.uk)
        $ldap_email = get_email_address($username);

        if(strcasecmp($ldap_email, $control_email) == 0)
        {
                return true;
        }
        
        // TODO : Check ldap mail with shortened domain (alias@ex.ac.uk)
        list($ldap_alias, $to_domain) = explode("@", $ldap_email, 2);

        if(strcasecmp("$ldap_alias@$insiders_domain_short", $control_email) == 0)
        {
                return true;
        }
        
        // Check $username@$insiders_domain (user@exeter.ac.uk)
        if(strcasecmp("$username@$insiders_domain", $control_email) == 0)
        {
                return true;
        }

        // Check $username@$insiders_domain_short (user@ex.ac.uk)
        if(strcasecmp("$username@$insiders_domain_short", $control_email) == 0)
        {
                return true;
        }
        
        // Otherwise no match, so fail
        return false;
}

function pickup_list()
{
        global $dropbox_dir;
        global $pickup_url_base;
        
        if(!($handle = opendir($dropbox_dir)))
        {
                print "<p><strong>Error: Unable to open dropbox dir: $dropbox_dir</strong></p>";
                return;
        }

        $username = $_SESSION['username'];
                             
        //$to_email = get_email_address($username);
        //write_syslog("pickup_list : got email '$to_email' for user '$username'");
        
        $numpickups = 0;
        
        // This is the correct way to loop over the directory.
        while (false !== ($file = readdir($handle))) {
                //echo "<p>$file</p><br>";

                // skip if not .control file
                if(!fnmatch("*.control", $file))
                {
                        continue;
                }

                // get id from file name
                $id_a = explode(".", $file, 2);
                $id = $id_a[0];

                if(!($fp = fopen("$dropbox_dir/$file", "r")))
                {
                        print "<p><strong>Error: Unable to open dropbox file: $file</strong></p>";
                        return;
                }

                while($str = fgets($fp))
                {
                        // Get key and rest of line:
                        $expl_array = explode(" ", $str, 2);
                        $key = $expl_array[0];
                        $rest = $expl_array[1];
                        // strip leading and trailing whitespace
                        $rest = trim($rest);

                        //print "<p>key $key -> rest $rest</p>";

                        // add to array
                        $pickups_tmp[$key] = $rest;
                }
                fclose($fp);

                // check 'to' email address matches entry in control file
                // CURRENT : Check 'all' possible matches for username
                if(!email_address_match($username, $pickups_tmp['to_email']))
                {
                        // doesn't match, so junk it and continue looping
                        unset($pickups_tmp);
                        continue;
                }
                
                // copy entry to array
                $pickups[$id] = $pickups_tmp;
                $numpickups++;

                //print "<br><br>";
        }
                
        closedir($handle); 

        if($numpickups == 1)
        {
                print "<p>There is <strong>1</strong> claim id for your username</p>";
        }
        else
        {
                print "<p>There are <strong>$numpickups</strong> claim ids for your username</p>";
        }

        if($numpickups == 0)
        {
                return;
        }
        
        print   "<table border=1>" .
                " <tr>" .
                "  <th>pickup id</th>" .
                "  <th>from name</th>" .
                "  <th>from email</th>" .
                //"  <th>to email</th>" .
                "  <th>file name</th>" .
                "  <th>file size</th>" .
                "  <th>file description</th>" .
                " </tr>";
        
        foreach($pickups as $pickup)
        {
                $pickup_url = $pickup_url_base . $pickup['id'];

                print   "<tr>" .
                        " <td><a href=\"$pickup_url\">" . $pickup['id'] . "</a></td>" .
                        " <td>" . $pickup['from_name'] . "</td>" .
                        " <td>" . $pickup['from_email'] . "</td>" .
                        //" <td>" . $pickup['to_email'] . "</td>" .
                        " <td>" . $pickup['file_basename'] . "</td>" .
                        " <td>" . $pickup['file_length'] . "</td>" .
                        " <td>" . $pickup['file_desc'] . "</td>" .
                        "</tr>";
        }

        print "</table>\n";
}

function pickup_outbound_form()
{
        global $auth_authed;
        global $dropbox_baseref;
        
        $id = $_GET['id'];

        print "<p>Please enter the claim id and claim passcode.</p>";

        if($auth_authed)
        { 
                print "<p>Since you are logged in, you may not need the passcode, it depends on how the sender dropped off the file. If they gave you a passcode, enter it, otherwise you can leave it blank.</p>";
	}

        print "
<p>
<form method=\"post\" action=\"".$dropbox_baseref."?state=pickup_info\">
<input type=hidden name=state value=pickup_info>
<table>
<tr><td>Claim id:</td><td><input type=text name=id size=12 value=\"$id\"></td></tr>
<tr><td>Claim passcode:</td><td><input type=text name=passcode size=12 value=\"\"></td></tr>
<tr><td></td><td><input type=submit name=pickup value=\"pickup\"></td></tr>
</table>
</form>
</p>
";
}

function pickup_outbound_info()
{
        global $dropbox_dir;
        global $auth_authed;
        global $insiders_domain;
        global $dropbox_baseref;
        

        if(isset($_GET['id']))
        {
                $id = $_GET['id'];
        }
        elseif(isset($_POST['id']))
        {
                $id = $_POST['id'];
        }
        else
        {
                write_syslog("No ID specified on pickup");
                print "<p><strong>Error : No claim id specified.</strong></p>";
                pickup_outbound_form();
                return;
        }
        
        $username = $_SESSION['username'];
        
        write_syslog("pickup_info user:`$username' requesting info on id `$id'" );

        $control_file = "$dropbox_dir/$id.control";
        if(!file_exists($control_file))
        {
                write_syslog("$id no control file");
                sleep(5); // pause to avoid spamming of random IDs
                print "<p><strong>Error : Unable to find a dropbox file with claim id '$id'.</strong></p>";
                return;
        }
        
        if(!($fp = fopen($control_file, "r")))
        {
                print "<p><strong>Error: Unable to open dropbox file: $file</strong></p>";
                return;
        }
        
        while($str = fgets($fp))
        {
                // Get key and rest of line:
                $expl_array = explode(" ", $str, 2);
                $key = $expl_array[0];
                $rest = $expl_array[1];
                // strip leading and trailing whitespace
                $rest = trim($rest);
                
                //print "<p>key $key -> rest $rest</p>";
                
                // add to array
                $pickups[$id][$key] = $rest;
        }
        fclose($fp);

        if(!$pickups[$id]['drop_authed'] && !$auth_authed)
        { 
                write_syslog("unauthed drop, need to be logged in to pickup");
                print "<p>File was dropped by an an unauthenticated user,";
                print " so you must be logged in to pick it up.</p>";
                auth_login_form();
                return;
 	}

        if(isset($_GET['passcode']))
        {
                $passcode = $_GET['passcode'];
        }
        elseif(isset($_POST['passcode']))
        {
                $passcode = $_POST['passcode'];
        }
        //$to_email = "$username@$insiders_domain";

        //write_syslog("pickup_info : given pass=`$passcode' want `" .
        //             $pickups[$id]['passcode'] . "', " .
        //             "email `$to_email', cmp_e `" . $pickups[$id]['to_email'] .
        //             "'");

        if ($passcode != $pickups[$id]['passcode'] &&
            (! email_address_match($username, $pickups[$id]['to_email'])))
        {
                write_syslog("access denied");
                sleep(3); // avoid thrashing

                if($auth_authed)
                {  
                        print "<p>The requested file was not dropped for you,";
                        
                        print " and you did not provide the correct claim passcode.</p>";
                }
                else
                {
                        if(isset($passcode))
                        {
                                print "<p>You are not logged in, and did not provide the";
                                print " correct claim passcode for claim id $id.</p>";
                        }
                        else
                        {
        // No passcode specified, message asking for it is printed in form below
                        }
                }
                
                // show form for pickup auth
                pickup_outbound_form();
                return;
 	}

        // Print nice table of dropped file info
        print "<p><strong>Drop info for ID '$id':</strong></p>";
        print "<table>";
        print "<tr><td>From name: </td><td>" . $pickups[$id]['from_name'] . "</td></tr>";
        print "<tr><td>From org: </td><td>" . $pickups[$id]['from_org'] . "</td></tr>";
        print "<tr><td>From email: </td><td>" . $pickups[$id]['from_email'] . "</td></tr>";
        print "<tr><td>From IP: </td><td>" . $pickups[$id]['from_ip'] . "</td></tr>";
        print "<tr><td>To name: </td><td>" . $pickups[$id]['to_name'] . "</td></tr>";
        print "<tr><td>To email: </td><td>" . $pickups[$id]['to_email'] . "</td></tr>";
        print "<tr><td>Filename: </td><td>" . $pickups[$id]['file_basename'] . "</td></tr>";
        print "<tr><td>File content type: </td><td>" . $pickups[$id]['file_content_type'] . "</td></tr>";
        print "<tr><td>File size: </td><td>" . $pickups[$id]['file_length'] . "bytes</td></tr>";
        print "<tr><td>File description: </td><td>" . $pickups[$id]['file_desc'] . "</td></tr>";
        print "</table>";

        print "<p><strong>Warning: file has not been virus scanned!</strong></p>";

        print "<br />";
        print "<form method=\"post\" action=\"".$dropbox_baseref."?state=pickup_download&amp;id=$id\">\n";
        print "<input type=\"hidden\" name=\"state\" value=\"pickup_download\" />";
        print "<input type=\"hidden\" name=\"id\" value=\"$id\" />";
        print "<input type=\"hidden\" name=\"passcode\" value=\"$passcode\" />";
        print "<input type=\"submit\" name=\"submit\" value=\"download\" />";
        print "</form>";
}

function http_error($code, $message)
{
        // Redirect to non-existant or forbidden pages for 403 or 404 errors..
        // Bit of a hack, but I can't see another way to get the styled
        // error pages to work without including the style again from here
        // and writing my own error page, which seems pointless.
        switch($code)
        {
                case 403:
                        header("Location: https://as.exeter.ac.uk/utils/dropbox/forbidden");
                        break;
                case 404:
                default:
                        header("Location: https://as.exeter.ac.uk/utils/dropbox/missing");
                        break;
        }
}

function pickup_download()
{
        global $dropbox_dir;
        global $auth_authed;
        global $insiders_domain;


        $id = $_GET['id'];
        $username = $_SESSION['username'];
        write_syslog("download : user:`$username' requesting `$id'");
        
        $control_file = "$dropbox_dir/$id.control";
        if(!file_exists($control_file))
        {
                write_syslog("`$id' no control file");
                sleep(3);
                http_error("404", "Not Found");
                exit;
        }
        
        if(!($fp = fopen($control_file, "r")))
        {
                write_syslog("`$id' cannot open control file");
                sleep(3);
                http_error("403", "Forbidden");
                exit;
        }
        
        while($str = fgets($fp))
        {
                // Get key and rest of line:
                $expl_array = explode(" ", $str, 2);
                $key = $expl_array[0];
                $rest = $expl_array[1];
                // strip leading and trailing whitespace
                $rest = trim($rest);
                
                //print "<p>key $key -> rest $rest</p>";
                
                // add to array
                $pickups[$id][$key] = $rest;
        }
        fclose($fp);
                
        if(!$pickups[$id]['drop_authed'] && !$auth_authed)
        { 
                write_syslog("unauthed drop, need to be logged in to pickup");
                sleep(5);
                http_error("403", "Forbidden");
                exit;
        }

        $passcode = $_POST['passcode'];
        $username = $_SESSION['username'];
        
        if(($passcode != $pickups[$id]['passcode']) &&
           (! email_address_match($username, $pickups[$id]['to_email'])))
        {
                write_syslog("access denied (user=$username, passcode=$passcode, wantedpass=" . $pickups[$id]['passcode'] . ")");
                sleep(3);
                http_error("403", "Forbidden");
                exit;
        }
        
        $data_file = $pickups[$id]['data_file'];

        //if(!($contents = file_get_contents($data_file)))
	if(!($data_file_fp = fopen($data_file, 'rb')))
        {
                write_syslog("ID `$id' cannot open data file `$data_file'");
                http_error("404", "Not Found");
                exit;
        }
        
        $file_basename = $pickups[$id]['file_basename'];
        $file_content_type = $pickups[$id]['file_content_type'];
        $file_length = $pickups[$id]['file_length'];
        $file_desc = $pickups[$id]['file_desc'];

        // output the headers
        header("Content-type: $file_content_type");
        header("Content-length: $file_length");
        header("Content-Disposition: attachment; filename=\"$file_basename\"");
        header("Content-Description: $file_desc");
        header("Pragma: ");
        header("Expires: " . date("Y/m/d H:i:s"));

        // TODO : expires header ok?  and attachment type ok and stuff?

        //print $contents;
	fpassthru($data_file_fp);

        write_syslog("download : user=`$username' downloaded id=`$id'");
        exit;
}

function auth_check()
{
        global $auth_authed;
        global $ldap_host;
        global $ldap_bind_dn_start;
        global $ldap_bind_dn_end;


        // If already authed then will be in the session
        if(isset($_SESSION["auth_authed"]) && $_SESSION["auth_authed"])
        {
                write_syslog("auth_check : pre-authed in SESSION ok");
                $auth_authed = 1;
                return;
        }

        // Else check with user and pass
        if(isset($_POST["username"]) && isset($_POST["password"]))
        {
                $username = $_POST["username"];
                $password = $_POST["password"];
        }
        else
        {
                // no username/pass, so no auth
                write_syslog("auth_check : No user/pass given in POST");
                $auth_authed = 0;
                return;
        }
        
        $ds = ldap_connect($ldap_host);  // must be a valid LDAP server!
        
        if($ds)
        {
                $r = ldap_bind($ds, $ldap_bind_dn_start . $username .
                               $ldap_bind_dn_end, $password); // authenticated bind

                $auth_authed = $r;
                $_SESSION["auth_authed"] = $r;
                $_SESSION["username"] = $username;
                
                ldap_close($ds);

                if($auth_authed)
                {
                        write_syslog("auth_check : LOGIN ($username) APPROVED");
                }
                else
                {
                        write_syslog("auth_check : LOGIN FAILED");
                }
        }
        else
        {
                echo "<h4>Unable to connect to LDAP server</h4>";
                $auth_authed = false;
        }
}


function auth_logout()
{
        //print "<p>Unsetting session variables...</p>";
        // Unset all of the session variables.
        $_SESSION = array();
        
        // Kill session cookie
        //if (isset($_COOKIE[session_name()]))
        //{
        //        setcookie(session_name(), '', time()-42000, '/');
        //}
        
        //print "<p>Destroying session...</p>";
        // Finally, destroy the session.
        session_destroy();

        print "<p>You are now logged out from dropbox.</p>";
}


function open_syslog()
{
        openlog("dropboxlog", LOG_PID, LOG_LOCAL0);
}


function write_syslog($string)
{
        $access = date("Y/m/d H:i:s");
        // TODO : log remote URL and username and other stuff?
        syslog(LOG_INFO, "dropbox - $access - $string ");
}


function check_session_vars()
{
//        global $state;
//        
//        if(!session_is_registered("state"))
//        {
//                session_register("state");
//                $state = "";
//        }
}
       

function check_state()
{
        global $state;
        global $auth_authed;
        
        check_session_vars();

        $username = $_SESSION['username'];
        
        // Check if user is already logged in (cookie)
        // or if we should show the login form or some other behaviour
        auth_check();

        // TODO: check this is an acceptable way to get the state
        $state = $_GET["state"];

        // make log entry
        write_syslog("state = $state, username = $username, session = " . session_id());

        switch($state)
        {
                case "login":
                        if($auth_authed) { member_menu(); }
                        else             { auth_login_form(); }
                        break;
                case "drop":
                        drop_off_inbound_form();
                        break;
                case "drop2":
                        dropped_inbound();
                        break;
                case "pickup":
                        pickup_outbound_form();
                        break;
                case "pickup_info":
                        pickup_outbound_info();
                        break;
                      // for pickup_download, stop headers being printed
                case "pickup_download":
                        pickup_download();
                        break;
                case "pickup_list":
                        if($auth_authed) { pickup_list(); }
                        else             { top_menu(); }
                        break;
                case "logout":
                        if($auth_authed) { auth_logout(); }
                        else             { top_menu(); }
                        break;
                case "member_menu":
                default:
                        if($auth_authed) { member_menu(); }
                        else             { top_menu(); }
                        break;
        }
}

open_syslog();
check_state();

if($state != "pickup_download")
{
        // menu link
        top_menu_footer();
        printFooter($site, $uri);
} // pickup_download not set (so allow headers)

?>
