<?php
//
//

// Turn off all error reporting

//error_reporting(0);

$base = 'ou=people,dc=exeter,dc=ac,dc=uk';
$rdn = 'uid';
$host = 'ldaps://ldap-master.ex.ac.uk';
$filter = '(uid=*)';

$justthese = array( 'uid', 'displayname', 'cn', 'sn', 'givenname',
                    'edupersonnickname', 'mail', 'telephonenumber',
                    'exeterstaffnumber', 'exeterstudentnumber',
                    'exeterassociatenumber', 'exeterstatus',
                    'exeterprimaryorg', 'exeterprimaryorgunit',
                    'exeterorg', 'exeterorgunit', 'l', 'telephonenumber',
                    'exeterathensaccount','exeterprintstatus' );

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

		 'exeterprintstatus'	  =>  'EVALUATEPRINT',
                 'userpassword'           =>  '<a href="javascript:void(window.open(\'/it/account/changepassword/\'));">Change Password</a>' );

//printHeader($site, $uri, $heading);

// Connect to LDAP
global $content;
$content .="<script type='text/javascript'>function disable_input() {        if( document.print.exeterprintstatus.value == 'other' ) {                document.print.other.style.visibility='visible';  document.print.other.disabled = false;    }else { document.print.other.style.visibility='hidden';  }  }</script>";

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
		$detail = displayprint($data,$info[$i][$data][0]);
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
    $commaSeparatedString = 'billable, nonbillable, 1563, 5691, 5696, 1010, 1020, 1030, 1504, 1521, 1532, 1533, 1534, 1535, 1536, 1543, 1546, 1553, 1558, 1560, 1561, 1562, 1563, 1564, 1565, 1566, 1567, 1568, 1569, 1572, 1575, 1576, 1577, 1578, 1579, 1580, 1581, 1582, 1583, 1584, 1585, 1586, 1587, 2103, 2106, 2109, 2113, 2117, 2121, 2125, 2129, 2133, 2137, 2141, 2146, 2151, 2157, 2163, 2169, 2174, 2175, 2179, 2187, 2716, 2727, 2738, 2749, 2760, 3203, 3204, 3205, 3206, 3207, 3208, 3209, 3210, 3211, 3212, 3213, 3214, 3215, 3216, 3217, 3218, 3219, 3220, 3221, 3222, 3223, 3224, 3225, 3226, 3227, 3228, 3229, 3230, 3231, 3232, 3233, 3234, 3235, 3236, 3237, 3238, 3240, 3251, 3265, 3266, 3267, 3268, 3269, 3270, 3271, 3272, 3273, 3274, 3275, 3276, 3277, 3278, 3279, 3280, 3281, 3611, 3612, 3613, 3623, 3624, 3625, 3626, 3627, 3628, 3629, 3630, 3631, 3632, 3633, 3634, 3635, 3636, 3637, 3638, 3639, 3640, 3641, 3642, 3643, 3644, 3651, 3652, 3653, 4123, 4124, 4125, 4504, 4505, 4506, 4507, 4508, 4509, 4510, 4511, 4512, 4513, 4514, 4515, 4516, 4517, 4518, 4519, 4520, 4521, 4522, 4523, 4524, 4525, 4526, 4527, 4528, 4529, 4530, 4531, 4532, 4533, 4534, 4535, 4536, 4537, 4538, 4539, 4542, 4543, 4544, 5406, 5408, 5414, 5421, 5422, 5423, 5424, 5425, 5426, 5427, 5428, 5429, 5430, 5431, 5433, 5434, 5436, 5438,  5440, 5460, 5461, 5462, 5470, 5473, 5483, 5484, 5486, 5489, 5491, 5498, 5501, 5502, 5503, 5504, 5505, 5506, 5507, 5508, 5509, 5510, 5511, 5512, 5513, 5514, 5515, 5516, 5517, 5518, 5519, 5520, 5521, 5522, 5523, 5524, 5525, 5526, 5527, 5528, 5600, 5602, 5607, 5616, 5625, 5634, 5643, 5652, 5661, 5670, 5679, 5688, 5691, 5694, 5696, 5801, 5806, 5807, 5710, 5717, 5724, 5731, 5738, 5745, 5752, 5757, 5766, 5773, 5778, 5780, 5782, 6006, 6009, 6015, 6019, 6028, 6039, 6050, 6066, 6074, 6081, 6093, 6512, 6521, 6522, 6523, 6530, 6531, 6532, 6545, 6546, 6547, 6564, 6567, 6568, 6570, 6572, 6574, 7006, 7008, 7012, 7031, 7032, 7033, 7034, 7035, 7036, 7037, 7038, 7039, 7040, 7041, 7042, 7043, 7050, 7054, 7061, 7077, 7503, 7514, 7519, 7521, 7530, 7541, 7552, 7563, 7584, 9012, 9015, 9017, 9024, 9031, 9035, 9038, 9045, 9048, 9049, 9050, 9202, 9203, 9204, 9205, 9206, 9207, 9208, 9209, 9210, 9211, 9212, 9213, 9214, 9215, 9216, 9217, 9218, 9219, 9220, 9221, 9222, 9223, 9224, 9225, 9226, 9227, 9228, 9229, 9230, 9231, 9232, 9233, 9234, 9235, 9236, 9237, 9238, 9239, 9240, 9241, 9242, 9243, 9244, 9245, 9246, 9247, 9248, 9249, 9250, 9251, 9252, 9253, 9254, 9255, 9256, 9257, 9258, 9259, 9260, 9261, 9262, 9263, 9264, 9265, 9266, 9267, 9268, 9269, 9270, 9271, 9272, 9273, 9274, 8001'; 
 
    return in_array($name, explode(', ', $commaSeparatedString), true); 
} 

?>
