<?php
// Include web header
include(".includes/test-header.php");

$heading = 'Online Staff IT Account Activation';
$content = '';
$info;
$ok = 1;
$passwd = "";

if (! isset($_POST["User"]))
{
   $content = '<h3><br>Invalid user details, please try again.</h3>';
   $ok = 0;
}
if($ok) 
{  
   $user = $_POST['User'];
  #$pass = _genPass(6);
}

if($ok && ($ok = check_user($user)))
if($ok && ($ok = _collectPass($user)))
if($ok && ($ok = _pdfGen($user, $passwd)));
if(!$ok){ modifyTemplate($site, $uri, $heading, $content); }

function check_user($user)
{
   global $content, $info;
   $ds=connect_ldap();

   if ($ds) {
       $dn = 'ou=people,dc=exeter,dc=ac,dc=uk';
       $filter = "(uid=$user)";
       $sr = ldap_list($ds, $dn, $filter);
       if (ldap_count_entries($ds, $sr) != 1) {
          $content = "<br><h1>There was an error fetching the users account details</h1>";
          return 0;
       }
       $info = ldap_get_entries($ds, $sr);
       $name = $info[0]["cn"][0];
       $collectBy = isset($info[0]["exetercollectby"][0]) ? $info[0]["exetercollectby"][0] : "";
       $collectDate = isset($info[0]["exetercollectdate"][0]) ? $info[0]["exetercollectdate"][0] : "19700101000000Z";
   } else {
      return 0;
   }
   if ($collectDate != "19700101000000Z") {
       $content = "<br><h3>IT Account details may only be collected once.<br>";
       if ($collectDate != "") {
             $date = substr($collectDate,6,2)."/".substr($collectDate,4,2)."/".substr($collectDate,0,4);
             $content .= "Details for <b>$name</b> where collected by $collectBy on $date</h3>";
       } else {
             $content .= "Details for <b>$name</b> have already been collected</h3>";
       }
       return 0;
   }
   return 1;
}

function _pdfGen($user, $pass)
{
   global $content, $info;
   $tf1; $tf2; $tf3; $tf4;
   $name = $info[0]["cn"][0];
   $org = $info[0]["exeterprimaryorg"][0];
   $email =  strtolower($info[0]["mail"][0]);
   $collectBy = $info[0]["exetercollectby"][0];
   $collectDate = $info[0]["exetercollectdate"][0];
   
   # Text included on our PDF document
   $date = date('F j, Y');
   $text1 = "SID Desk\nPhone: +44(0)1392 724724\nEmail: sid@exeter.ac.uk\nWeb: sid.exeter.ac.uk\n\n\n$date";
   $text2 = "$name\n$org\n\n";
   $text3 = "Dear User,\n";
   $text4 = "Please keep this information safe and confidential.\n";
   $text5 = "Your account for the University of Exeter's computing facilities has been activated.\n";
   $text6 = "Your login username is: ";
   $text7 = "Your initial password is: ";
   $text8 = "Your email address is: ";
   $text9 = "\n\n\nPlease have your UniCard details available if you need to contact the SID Desk regarding your account.";
   $text10 = "Please note the following important information:\n";
   $text11 = "Your username and password\n";
   $items1 = array(
      "All users should be aware of the importance of their password.  You must change the password above to one that follows the security guidelines on the online form at \nwww.exeter.ac.uk/it/account/changepassword",
      "Do not divulge your password to anyone else.  Members of University staff will never ask for your password, including by email."
   );
   $text23 = "Staying safe with IT\n";
   $items2 = array(
      "You should follow Exeter IT top tips (see www.exeter.ac.uk/infosec/tips), which will help to protect you, your information and the University."
   );
   $text12 = "\n\nPlease turn over the page.";
   $text13 = "Data Protection Act\n\n";
   $text14 = "The University of Exeter is registered with the Information Commissioner's Office as required under the Data Protection Act 1998. Academic Services will be holding information for purposes including administration of computer resources, maintainance of records, keeping our users informed, and responding to any query that may be raised with us. Personal data will only be disclosed to members of the University, its agents or the data subject's organisation in accordance with the University's registration and current data protection legislation.\nPlease refer to www.exeter.ac.uk/recordsmanagement for more information about Data Protection.\n\n";
   $text15 = "\nRegulations\n\n";
   $text16 = "You should read the Regulations relating to the use of Information Technology facilities and abide by them. The Regulations are available online at www.exeter.ac.uk/it/regulations\n\n";
   $text17 = "\nServices Available\n\n";
   $text18 = "Many computing facilities and services are available to all members of the University; more information can be found on the web at www.exeter.ac.uk/it.\n\nDetails of training are available at www.exeter.ac.uk/staff/development/courses/ittraining.\n\n";
   $text19 = "\nUniversity email account\n\n";
   $text20 = "University business should only be conducted using your University email account. Please consult the web pages available from www.exeter.ac.uk/it/email for information about using email at the University.\n\n";
#   $text21 = "\nLeaving\n\n";
#   $text22 = "Please complete the form on the web available from www.exeter.ac.uk/it/account/joiningorleaving when you leave the University. Your username (including all files and email) will be deleted straight away unless you choose the option to retain it for two months so that any new University of Exeter email can be forwarded to another email address. The form contains a link to a webpage that will help you to deal with your existing University email. You should make copies of any data that you want to keep.\n\nUsers of the central computing resources must have a standard employee number in Personnel & Staff Development's database or an Associate card number in the Card Office's database. When you leave the University permanently you will no longer be entitled to use our resources unless you have an Associate UniCard or are retaining your account for two months in order for your University email to be forwarded to another email address.\n\n";

   # Create a new PDF document
   # textflows are added, then initialised, before the document is finally rendered
   try {
/**
       @ob_end_clean(); //turn off output buffering to decrease cpu usage
    
       // required for IE, otherwise Content-Disposition may be ignored
       if(ini_get('zlib.output_compression'))
          ini_set('zlib.output_compression', 'Off');
    
       header('Content-Type: application/pdf');
       header('Content-Disposition: attachment; filename="'.$name.'"');
       header("Content-Transfer-Encoding: binary");
       header('Accept-Ranges: bytes');
    
       // The three lines below basically make the download non-cacheable
       header("Cache-control: private");
       header('Pragma: private');
       header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
*/
       $p = new PDFlib();

       /*  open new PDF file; insert a file name to create the PDF on disk */
       if ($p->begin_document("", "") == 0) {
           #die("Error: " . $p->get_errmsg());
           $content = "<p>PDFlib error: " . $p->get_errmsg()."</p>";
           return 0;
       }

       $p->set_info("Creator", "pdflib.php");
       $p->set_info("Author", "Exeter IT");
       $p->set_info("Title", "IT Account Activation");

       # Begin Page 1, set to A4 page
       $p->begin_page_ext(595, 842, "");
       $uoelogofile = "/home/webs/as.exeter.ac.uk/utils/activation/UOE_logo.gif";
       $uoelogo = $p->load_image("gif",$uoelogofile,"");
       $p->fit_image($uoelogo, 80, 740, "");

       $opt1 = "fontname=Helvetica fontsize=11 encoding=winansi escapesequence leftindent=0 leading=13";
       $tf1 = $p->add_textflow($tf1, $text1, $opt1);
       $p->fit_textflow($tf1, 370, 800, 540, 700, "");
       $p->delete_textflow($tf1);
       
       $opt2 = "fontname=Helvetica fontsize=11 encoding=winansi leftindent=0 leading=23";
       $opt3 = "fontname=Helvetica-Bold fontsize=11 encoding=winansi leftindent=0 leading=23";
       $tf2 = $p->add_textflow($tf2, $text2, $opt1);
       $tf2 = $p->add_textflow($tf2, $text3, $opt2);
       $tf2 = $p->add_textflow($tf2, $text4, $opt3);
       $tf2 = $p->add_textflow($tf2, $text5, $opt2);
       $tf2 = $p->add_textflow($tf2, $text6, $opt2);
       $tf2 = $p->add_textflow($tf2, "$user\n", $opt3);
       $tf2 = $p->add_textflow($tf2, $text7, $opt2);
       $tf2 = $p->add_textflow($tf2, "$pass\n", $opt3);
       $tf2 = $p->add_textflow($tf2, $text8, $opt2);
       $tf2 = $p->add_textflow($tf2, "$email\n", $opt3);
       $tf2 = $p->add_textflow($tf2, $text9, $opt1);
       $p->fit_textflow($tf2, 80, 670, 540, 400, "");
       $p->delete_textflow($tf2);

       $opt4 = "fontname=Helvetica-Bold fontsize=15 encoding=winansi leading=23 leftindent=0";
       $opt5 = "fontname=Symbol fontsize=12 encoding=builtin escapesequence leftindent=20";
       $opt6 = "fontname=Helvetica fontsize=11 encoding=winansi leftindent=42 leading=13";
       $tf3 = $p->add_textflow($tf3, $text10, $opt4);
       $tf3 = $p->add_textflow($tf3, $text11, $opt3);
       for ($i = 0; $i < count($items1); $i++) {
          $tf3 = $p->add_textflow($tf3, "\\xB7", $opt5);
          $tf3 = $p->add_textflow($tf3, "$items1[$i]\n\n", $opt6);
       }
       $tf3 = $p->add_textflow($tf3, $text23, $opt3);
       for ($i = 0; $i < count($items2); $i++) {
          $tf3 = $p->add_textflow($tf3, "\\xB7", $opt5);
          $tf3 = $p->add_textflow($tf3, "$items2[$i]\n\n", $opt6);
       }
       $tf3 = $p->add_textflow($tf3, $text12, $opt3);
       $p->fit_textflow($tf3, 80, 400, 540, 50, "");
       $p->delete_textflow($tf3);

       $p->end_page_ext("");

       # Begin Page 2, set to A4 page
       $p->begin_page_ext(595, 842, "");

       $opt7 = "fontname=Helvetica-Bold fontsize=11 encoding=winansi leftindent=0 leading=11";
       $opt8 = "fontname=Helvetica fontsize=11 encoding=winansi leftindent=0 leading=14 alignment=justify";
       $tf4 = $p->add_textflow($tf4, $text13, $opt7);
       $tf4 = $p->add_textflow($tf4, $text14, $opt8);
       $tf4 = $p->add_textflow($tf4, $text15, $opt7);
       $tf4 = $p->add_textflow($tf4, $text16, $opt8);
       $tf4 = $p->add_textflow($tf4, $text17, $opt7);
       $tf4 = $p->add_textflow($tf4, $text18, $opt8);
       $tf4 = $p->add_textflow($tf4, $text19, $opt7);
       $tf4 = $p->add_textflow($tf4, $text20, $opt8);
#       $tf4 = $p->add_textflow($tf4, $text21, $opt7);
#       $tf4 = $p->add_textflow($tf4, $text22, $opt8);
       $p->fit_textflow($tf2, 80, 800, 540, 100, "");

       $p->end_page_ext("");

       $p->end_document("");

       $buf = $p->get_buffer();
       $len = strlen($buf);

       @ob_end_clean(); //turn off output buffering to decrease cpu usage

       // required for IE, otherwise Content-Disposition may be ignored
       if(ini_get('zlib.output_compression'))
          ini_set('zlib.output_compression', 'Off');

       header('Content-Type: application/pdf');
       header('Content-Disposition: attachment; filename="'.$name.'.pdf"');
       header("Content-Transfer-Encoding: binary");
       header("Content-Length: $len");
       header('Accept-Ranges: bytes');

       /* The three lines below basically make the download non-cacheable */
       header("Cache-control: private");
       header('Pragma: private');
       header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

       print $buf;
   }
   catch (PDFlibException $e) {
       #die("PDFlib error:\n" .
       $content = "<p>PDFlib error:<br>" .
       "[". $e->get_errnum() ."] ". $e->get_apiname(). ": " .$e->get_errmsg() . "<p>";
       return 0;
   }
   catch (Exception $e) {
       #die($e);
       $content = "<p>Error: ".$e."</p>";
       return 0;
   }
   $p = 0;
   return 1;
}
?>

