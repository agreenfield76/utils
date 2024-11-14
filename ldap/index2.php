<?php
//
// Update eduPersonNickname LDAP entry for user.
//
// Bill Edmunds, IT Services, University of Exeter
//
// Last modified: 16th April 2010
//

// Turn off all error reporting

//error_reporting(0);

// Variables
// Include web header
include(".includes/header.php");

$site = 'as.exeter.ac.uk';
include('/home/webs/' . $site . '/utils/ldap/template2.php');
$uri = '/utils/ldap/index.php';
$heading = 'University Directory Entry';
$debug = 0;
$base = 'ou=people,dc=exeter,dc=ac,dc=uk';
$rdn = 'uid';
$host = 'ldaps://ldap-master.ex.ac.uk';
$filter = '(uid=*)';
$maxlength = 32;
$minlength = 2;
$athensaccess = "No";

$justthese = array( 'uid', 'displayname', 'cn', 'sn', 'givenname',
                    'edupersonnickname', 'mail', 'telephonenumber',
                    'exeterstaffnumber', 'exeterstudentnumber',
                    'exeterassociatenumber', 'exeterstatus',
                    'exeterprimaryorg', 'exeterprimaryorgunit',
                    'exeterorg', 'exeterorgunit', 'l', 'telephonenumber',
                    'exeterathensaccount','exeterprintstatus' );

$athens = array ('exeterstaffnumber', 'exeterstudentnumber', 'exeterathensaccount');
$lookup = array( 'uid'                    	=>  'Username',
                 'displayname'            	=>  'Display Name',
                 'cn'                     	=>  'Common Name',
                 'sn'                     	=>  'Family Name',
                 'givenname'              	=>  'Given Name',
                 'edupersonnickname'      	=>  'Preferred Name',
                 'mail'                   	=>  'Email Address',
                 'exeterstaffnumber'      	=>  'Staff Number',
                 'exeterstudentnumber'    	=>  'Student Number',
                 'exeterassociatenumber'  	=>  'Associate Number',
                 'exeterathensaccount'    	=>  'Athens Account',
                 'exeterstatus'           	=>  'Status',
                 'exeterprimaryorg'       	=>  'Primary Location',
                 'exeterprimaryorgunit'   	=>  'Primary Sub-location',
                 'exeterorg'              	=>  'Other Location',
                 'exeterorgunit'          	=>  'Other Sub-Location',
                 'l'          			=>  'Locality',
                 'telephonenumber'        	=>  'Telephone Number',
                 'athens'                 	=>  'eResources Access',
		 'exeterprintstatus'		=>  'Print Services billing preference',
                 'userpassword'           	=>  'Password' );

                 #'cn'                     =>  '<a href="javascript:void(window.open(\'update-cn.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
$update = array( 'uid'                    =>  '<a href="javascript:void(window.open(\'update-username.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'displayname'            =>  '<a href="javascript:void(window.open(\'update-dn.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'cn'                     =>  'EVALUATE',
                 'sn'                     =>  '<a href="javascript:void(window.open(\'update-sn.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'givenname'              =>  '<a href="javascript:void(window.open(\'update-gn.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'edupersonnickname'      =>  'EVALUATE',
                 'mail'                   =>  '<a href="javascript:void(window.open(\'update-email.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'exeterstaffnumber'      =>  '<a href="javascript:void(window.open(\'update-staffno.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'exeterstudentnumber'    =>  '<a href="javascript:void(window.open(\'update-studentno.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'exeterassociatenumber'  =>  '<a href="javascript:void(window.open(\'update-assocno.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'exeterstatus'           =>  '<a href="javascript:void(window.open(\'update-status.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'exeterprimaryorg'       =>  '<a href="javascript:void(window.open(\'update-porg.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'exeterprimaryorgunit'   =>  '<a href="javascript:void(window.open(\'update-punit.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'exeterorg'              =>  '<a href="javascript:void(window.open(\'update-org.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'exeterorgunit'          =>  '<a href="javascript:void(window.open(\'update-unit.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'l'                      =>  '<a href="javascript:void(window.open(\'update-locality.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'telephonenumber'        =>  '<a href="javascript:void(window.open(\'update-tn.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
                 'athens'                 =>  '<a href="javascript:void(window.open(\'update-athens.html\',\'_190x240\',\'height=600,width=500\'));">Further details</a>',
		 'exeterprintstatus'	  =>  'EVALUATEPRINT',
                 'userpassword'           =>  '<a href="javascript:void(window.open(\'/it/account/changepassword/\'));">Change Password</a>' );

//printHeader($site, $uri, $heading);

// Connect to LDAP
global $content;
$content .="<script language='javascript'>function enable_input(obj) { if (obj.options[obj.selectedIndex].value=='other') { document.print.textfield.disabled=false; } else { document.print.textfield.value=''; document.print.textfield.disabled=true; } }</script>";

$content .= "<h3>Directory Search for " . $_SERVER['PHP_AUTH_USER'] . "</h3>";
if ($debug) $content .= "<p>Connecting ...<br />";
$ds=ldap_connect($host);
if ($debug) $content .= "Connect result is ".$ds."</p>";

if ($ds) { 

    // Bind to lDAP as user

    $me = $rdn . "=" . $_SERVER['PHP_AUTH_USER'] . "," . $base;
    if ($debug) $content .= "<p>Binding as $me ...<br />";
    $r=ldap_bind($ds, $me, $_SERVER['PHP_AUTH_PW']);

    if ($debug) $content .= "Bind result is " . $r . "</p>";

    if (!isset($r) || $r != 1) {
        $content .= "<p>Could not obtain details. Wrong password?</p>";
        myfooter();
        exit;
    }

    // Carry out updates

    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && isset($_POST['update'])) {

        if ($debug) $content .= "<p>Updating ...</p>";
        $thisisit = $_POST['attribute'];
        $that[$thisisit] = $_POST[$thisisit];
	if ($_POST[$thisisit]=="other"){$that[$thisisit] = $_POST['other'];}
		

        // Parse input string and modify LDAP
	if (($thisisit=="exeterprintstatus" && (!(check($that[$thisisit]))))) {
	$content .="<p>Sorry, an invalid billing code was found. Please try again!</p>"; 
	}	 else { 

        $length = strlen($that[$thisisit]);
        if ($length > $maxlength || $length < $minlength) {
            $content .= "<p>Update string length out of bounds (" . $minlength . " - " . $maxlength . ").</p>";
        } elseif (wordcheck($that[$thisisit])) {
            $content .= "<p>Updating <b><i>" . $lookup[$thisisit] . "</b></i> with <b><i>" . $that[$thisisit] . "</b></i>&nbsp;&nbsp;&nbsp;";
            $mr = ldap_modify($ds, $me, $that);
            if ($mr) {
                $content .= "<b>... Success!</b></p>";
            } else {
                $content .= "<b>... Failed!</b></p>";
            }
        } else {
            $content .= "<p>Update string contains invalid characters.<p>";
        }

    }}

    // Search LDAP for user entry

    if ($debug) $content .= "<p>Searching for $filter ...<br />";
    $sr=ldap_search($ds, $me, $filter, $justthese);  
    if ($debug) $content .= "<p>Search result is " . $sr . "</p>";

    if ($debug) $content .= "<p>Number of entries returned is " . ldap_count_entries($ds, $sr) . "</p>";

    if ($debug) $content .= "<p>Getting entries ...<br />";
    $info = ldap_get_entries($ds, $sr);
    if ($debug) $content .= "Data for " . $info["count"] . " items returned:</p>";

    // Display search results

    for ($i=0; $i<$info["count"]; $i++) {

        $content .= '<table cellpadding="5" border="1">';
        $content .= '<tr bgcolor="#fffff0"><td><b>Entry</b></td><td><b>Data</b></td><td align="center"><b>Update Details</b></td></tr>';

        for($c = 0; $c<$info[$i]["count"]; $c++) {

            $data = $info[$i][$c];

            // Check Athens access

            if (in_array($data, $athens)) {
                $athensaccess = "Yes";
            }

            if (strcmp($update[$data], 'EVALUATE') == 0) {
                $detail = display($data);
            } else {
		if (strcmp($update[$data], 'EVALUATEPRINT') == 0) {
		$detail = displayprint($data,$data);
	    } else {
                $detail = $update[$data];
            }}

            $content .= '<tr><td><b><i>' . $lookup[$data] . '</i></b></td><td>' . $info[$i][$data][0] . '</td><td align="center">' . $detail . '</td></tr>';

        }

        $content .= '<tr><td><b><i>' . $lookup['athens'] . '</i></b></td><td>' . $athensaccess . '</td><td align="center">' . $update['athens'] . '</td></tr>';
        $content .= '<tr><td><b><i>' . $lookup['userpassword'] . '</i></b></td><td>' . '********' . '</td><td align="center">' . $update['userpassword'] . '</td></tr>';
        $content .= '</table>';

    }

    $content .= "<p><b>Please note:</b> Updates to the directory should be processed immediately. " .
         "However, there may be some delay before these updates filter through to other applications.</p>";
    if ($debug) $content .= "<p>Closing connection</p>";
    ldap_close($ds);

} else {

    $content .= "<h4>Unable to connect to LDAP server</h4>";
    $content .= "<p>Please try again later</p>";

}

//printFooter($site, $uri);
modifyTemplate($site, $uri, $heading, $content);

function check($name) { 
    $commaSeparatedString = 'billable, nonbillable, 1563, 5691, 5696'; 
 
    return in_array($name, explode(', ', $commaSeparatedString), true); 
} 

?>
