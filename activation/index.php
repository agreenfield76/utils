<?php 
// Include web header
include(".includes/header.php");

$heading = 'Online Staff IT Account Activation';
$user = $content = '';

# Get account details from LDAP for user.
function get_ldap_data($ds, $user, $filter)
{
   include(".includes/authusers.php");
   global $content, $admin;
   $USERBASE    = 'ou=people,dc=exeter,dc=ac,dc=uk';
   $USERATRBS   = array("sn","uid","exetercollectdate","exeterstaffnumber","exeterassociatenumber","cn","mail","exeterprimaryorg","exeterprimaryorgunit","exetercollectby","exetercardnumber","exeteridf");
   #$filter = "Activated";
   $thismonth = date("Ym");
   $lastmonth = date("Ym", strtotime('-1 month'));
   $admin = 0;
   if (isset($authstf[$user]))
   {
      $authOrgs = $authstf[$user];
      $admin = 1;
   } else
   {
      $authOrgs = $authstf["generic"];
   }
   #print_r($authOrgs); print "<BR>";
   foreach ($authOrgs as $type => $group)
   {
      foreach (explode(",", $group) as $value)
      {
         $search = "(&(exeterStatus=$type)";
         if ($primaryOrg[$value] == "all") {
            $search_org = "All $type";
         } else 
         {
            $search_org = $primaryOrg[$value];
            $safe_search = quotemeta($search_org);
            $search .= "(exeterPrimaryOrg=$safe_search)";
         }
         if ($filter == "Activated") { 
            $search .= "(|(exeterCollectDate=$lastmonth*)(exeterCollectDate=$thismonth*))"; 
            if ($admin != 1) { $search .= "(exeterCollectBy=$user)"; }
         } else {
            $search .= "(exeterCollectDate=19700101000000Z)";
            if ($admin != 1) { $search .= "(exeterIDF=$user)"; }
         }
         $search .= ")";
#         if ($filter == "Activated") { $search .= ")"; }
#print "SEARCH: $search<br>";
         $sr = ldap_search($ds, $USERBASE, $search, $USERATRBS);
         $count = ldap_count_entries($ds, $sr);
#print "COUNT: $count<br>";
         $title = "$filter <b>". $type ."</b> IT Accounts in <b>". $search_org ."</b>";
         if ($count == 0) 
         { 
            $content .= "<h4>No $title</h4>"; 
         } else
         {
            $content .= "<h4>$title</h4>";
            $info = ldap_get_entries($ds, $sr);
            printAccounts($info, $filter, $type);
         }
	 $content .= "<br>";
      }
   }
}

# Check LDAP array value is not null, or return empty string
function get_data($data, $index, $attr)
{
   if (isset($data[$index]["$attr"][0]))
   {
      return $data[$index]["$attr"][0];
   } else
   {
      return " ";
   }
}

// Function to filter accounts displayed
function printAccounts($data, $filter, $type) 
{
   global $content, $admin;
   $content .= "<table class=\"sortable\" border=\"1\" cellspacing=\"0\" cellpadding=\"2\"><tr><th><b>Name</b></th>";
   if($filter == "Activated") { $content .= "<th><b>uid</b></th>"; }
   $content .= "<th><b>E-Mail</b></th><th><b>Employee No.</b></th>";
   if($filter != "Activated") { $content .= "<th><b>UniCard No.</b></th>"; }
   if($admin == 1) { $content .= "<th><b>IDF</b></th>"; }
   $content .= "<th><b>Collected</b></th>";
   if($filter == "Activated") { if($admin == 1) { $content .= "<th><b>Collect By</b></th>"; } }
   $content .= "</tr>";

    $numberAttrb = "exeterstaffnumber";
    if ($type == "Associate") { $numberAttrb = "exeterassociatenumber"; }
    for ($i = 0; $i < $data["count"]; $i++)
    {
       $formatData[$i][0] = get_data($data, $i, "sn");
       $formatData[$i][1] = get_data($data, $i, "uid");
       $formatData[$i][2] = get_data($data, $i, "exetercollectdate");
       $formatData[$i][3] = get_data($data, $i, "$numberAttrb");
       $formatData[$i][4] = get_data($data, $i, "cn");
       $formatData[$i][5] = get_data($data, $i, "mail");
       $formatData[$i][6] = get_data($data, $i, "exeterprimaryorg");
       $formatData[$i][7] = get_data($data, $i, "exeterprimaryorgunit");
       $formatData[$i][8] = get_data($data, $i, "exetercollectby");
       $formatData[$i][9] = get_data($data, $i, "exetercardnumber");
       $formatData[$i][10] = get_data($data, $i, "exeteridf");
    }
    sort($formatData);

    for ($i = 0; $i < count($formatData); $i++) 
    {
       $username = $collectDate = $number = $name = $email = $org = $orgUnit = $collectBy = $cardno = $collected = $idf = "&nbsp";
       $username    = $formatData[$i][1];
       $collectDate = $formatData[$i][2];
       $number      = $formatData[$i][3];
       $name        = $formatData[$i][4];
       $email       = $formatData[$i][5];
       $org         = $formatData[$i][6];
       $orgUnit     = $formatData[$i][7];
       $collectBy   = $formatData[$i][8];
       $cardno      = $formatData[$i][9];
       $idf         = $formatData[$i][10];

       $cardno = substr($cardno, -9);
       if ($cardno != "") { $cardno = $cardno . "S"; }

       if ($filter == "Unactivated") 
       {
          if ($collectDate != "19700101000000Z") 
          {
             continue;
          } else 
          {
             $collected = "<form name=\"form$i\" method=\"post\" action=\"form.php\">
				<input type=\"hidden\" name=\"User\" value=\"$username\">
				<input type=\"hidden\" name=\"Name\" value=\"$name\">
				<input type=\"hidden\" name=\"Email\" value=\"$email\">
				<input type=\"hidden\" name=\"Number\" value=\"$number\">
				<input type=\"hidden\" name=\"Card\" value=\"$cardno\">
				<input type=\"submit\" name=\"Submit\" value=\"Collect Details\">
			   </form>";
          }
       }
       if ($filter == "Activated") 
       {
          if ($collectDate == "19700101000000Z") 
          {
             continue;
          } else 
          {
             if ($collectDate != "") 
             {
                $collected = substr($collectDate,6,2)."/".substr($collectDate,4,2)."/".substr($collectDate,0,4);
             }
          }
       }

   $content .= "<tr><td>$name</td>";
   if($filter == "Activated") { $content .= "<td>$username</td>";  }
   $content .= "<td>$email</td><td>$number</td>";
   if($filter != "Activated") { $content .= "<td>$cardno</td>"; }
   if($admin == 1) { $content .= "<td>$idf</td>";  }
   $content .= "<td>$collected</td>";
   if($filter == "Activated") { if($admin == 1) { $content .= "<td>$collectBy</td>"; } }
   $content .= "</tr>";
   }
   $content .= "</table>";
}

# This actually does the work!
if (check_access() == 0)
{
   $ds = connect_ldap();
   if (isset($ds))
   {
//      $content .= "
//<p><font size=\"3\" color=\"red\">
//Please be advised that on Monday 16th April there will be Migration from existing IT Account Administration server (CEN) to the new Administration server (VMCEN) between 09:00 and 17:00<br><br>
//
//During this downtime, users will be unable to access password change facility, Help Desk Account Admin pages (IT Account resets) and IT Account creation and provisioning.<br><br>
//
//This upgrade is necessary to ensure the continued reliability of the service.<br><br>
//
//We apologise for any inconvenience this may cause.<br><br>
//
//IT Help Desk 24/7 Help and Support<br>
//Phone: 01392 263934<br>
//Email: helpdesk@exeter.ac.uk <br>
//Web: www.exeter.ac.uk/its/helpdesk <br>
//Self Service: www.exeter.ac.uk/it/helpdesk/selfservice<br>
//</font></p>";
      get_ldap_data($ds, $user, "Unactivated");
      get_ldap_data($ds, $user, "Activated");
   }
   // Close LDAP connection when done
   ldap_close($ds);
}

modifyTemplate($site, $uri, $heading, $content);
?>
