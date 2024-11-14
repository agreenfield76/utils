<?php
//
// Update eduPersonNickname LDAP entry for user.
//
// Bill Edmunds, IT Services, University of Exeter
//
// Last modified: 16th April 2010
//

// Turn off all error reporting

error_reporting(0);

// Variables

$site = 'as.exeter.ac.uk';
include('/home/webs/' . $site . '/utils/ldap/template.php');
$uri = '/ldap/info/php.php';
$heading = 'Using PHP to connect to the LDAP servers';

printHeader($site,$uri,$heading);

?>

<p>The following code can be used to retrieve details from the LDAP server:</p>
<pre>
&lt;?php
// basic sequence with LDAP is connect, bind, search, interpret search
// result, close connection

echo "&lt;h3&gt;LDAP query test&lt;/h3&gt;";
echo "Connecting ...";
$ds=ldap_connect("ldaps://ldap.ex.ac.uk);
echo "connect result is " . $ds . "&lt;br /&gt;";

if ($ds) {
   echo "Binding ...";
   $r=ldap_bind($ds);    // this is an "anonymous" bind with read-only access
   echo "Bind result is " . $r . "&lt;br /&gt;";

   echo "Searching for (dn) ...";
   // Search surname entry
   $sr=ldap_search($ds, "dc=exeter, dc=ac, dc=uk", "dn"); 
   echo "Search result is " . $sr . "&lt;br /&gt;";

   echo "Number of entires returned is " . ldap_count_entries($ds, $sr) . "&lt;br /&gt;";

   echo "Getting entries ...&lt;p>";
   $info = ldap_get_entries($ds, $sr);
   echo "Data for " . $info["count"] . " items returned:&lt;p&gt;";

   for ($i=0; $i&lt;$info["count"]; $i++) {
       echo "dn is: " . $info[$i]["dn"] . "&lt;br /&gt;";
       echo "first cn entry is: " . $info[$i]["cn"][0] . "&lt;br /&gt;";
       echo "first email entry is: " . $info[$i]["mail"][0] . "&lt;br /&gt;&lt;hr /&gt;";
   }

   echo "Closing connection";
   ldap_close($ds);

} else {
   echo "&lt;h4&gt;Unable to connect to LDAP server&lt;/h4&gt;";
}
?&gt;

</pre>
<p>Please see the LDAP reference at <a href="http://uk2.php.net/ldap">http://uk2.php.net/ldap</a> for further details.</p>

<?php

printFooter($site,$uri);

?>
