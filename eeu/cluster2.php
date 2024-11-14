<?php

$labstats = implode(' ', file("http://vmlabstats.ex.ac.uk:8080/public/current_stats.jsp"));

$labstats = str_replace("<title>LabStats: Display Statistics</title>", "<title>LabStats: Display Statistics - Lib Info Screen</title>", $labstats);

//adjust the css path
$labstats = str_replace("/all.css", "http://as.exeter.ac.uk/utils/eeu/all.css", $labstats);
$labstats = str_replace("/table.list.css", "http://as.exeter.ac.uk/utils/eeu/table.list.css", $labstats);
$labstats = str_replace("/wordWrap.js", "http://vmlabstats.ex.ac.uk:8080/wordWrap.css", $labstats);


//change graph style
$labstats = str_replace("width: 100px", "width: 285px", $labstats);
$labstats = str_replace("height: 10px", "height: 65px", $labstats);


//Take out manipulated header to start
$token_a = "<html>";
$token_b = "<body>";

$point_a = strpos($labstats, $token_a);
$point_b = strpos($labstats, $token_b);
$length_ab = ($point_b + strlen($token_b)) - $point_a;

$new_header = substr($labstats, $point_a, $length_ab);


//Start of body
$token_a = "<div id=\"main-content-container\">";
$token_b = "</h3>";

$point_a = strpos($labstats, $token_a);
$point_b = strpos($labstats, $token_b);
$length_ab = ($point_b + strlen($token_b)) - $point_a;

$new_body_start = substr($labstats, $point_a, $length_ab);

$new_body_start = str_replace("<h3>Current Stats</h3>", 
			"<TABLE WIDTH=900><TR><TD><img src=\"logo_70_wide.gif\"></TD><TD ALIGN=RIGHT>Current PC Usage<IMG SRC=\"mp33.jpg\" width=49 height=42></TD></TR></TABLE>", 
			$new_body_start);


//End of body
$new_body_end = "</body></html>";


//Start the table
$token_a = "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"list\">";
$token_b = "<th>Total</th>";

$point_a = strpos($labstats, $token_a);
$point_b = strpos($labstats, $token_b);
$length_ab = ($point_b + strlen($token_b)) - $point_a;

$table_start = substr($labstats, $point_a, $length_ab)."</tr>";


//Now to select which clusters to show and how:
//array of lab stat id's in specific order for display
// Z $clusterIDs = array("8", "9", "7", "12", "27", "3", "14", "5", "21");
// Z Removed display for main library cluster room 2 (id=9)
$clusterIDs = array("8", "9", "7", "12", "27", "3", "14", "5", "21");

echo $new_header;
echo $new_body_start;

echo "$table_start";

$cluster_start_dark = "<tr class=\"dark\"><td style=\"width:350px;\">";
$cluster_start_light = "<tr ><td style=\"width:350px;\">";
$cluster_end = "Chart</a></td--></tr>";

$light_dark = "dark";



for($x=0; $x<count($clusterIDs); $x++){

$y = $clusterIDs[$x];

//start cluster data
$token_a = "<a href=\"custom_stats.jsp?type=current&lab_id=".$y."\"><span";
$token_b = "<!--td><a href=\"custom_stats.jsp?type=current&lab_id=".$y."\">";

$point_a = strpos($labstats, $token_a);
$point_b = strpos($labstats, $token_b);
$length_ab = ($point_b + strlen($token_b)) - $point_a;


$cluster_data = substr($labstats, $point_a, $length_ab);

$cluster_data = str_replace("<a href=\"", "<a href=\"http://vmlabstats.ex.ac.uk:8080/public/", $cluster_data);

if ( $y == "9"  ) {
// DEFINE "TOTAL" BECAUSE LABSTATS GIVES "WRONG" VALUE FOR THIN_CLIENT CLUSTERS.

   $znew_total = 11; // DEFINE THE TOTAL WHICH WILL BE USED BELOW TO CALCULATE NUMBER AVAILABLE

// If numbers are collected from $cluster_data
//   look for  <th>Total</th>
//     	  then <td>number</td> # In use
//	  then <td>number</td> # Available
//	  then <td>number</td> # Total
//	  (Offline is not there, unfortunately - does it work at all?)
//
//   then do maths and replace Available with correct value

//   FIND NUMBER OF COMPUTERS IN USE

   $z_in_use_section_start = "<a href=\"http://vmlabstats.ex.ac.uk:8080/public/custom_stats.jsp?type=current&lab_id=9\"><span class=\"mediumwidth-word-wrap\">Main Library PC room 2</span></a>";  // for id=9

   $zpos = strpos($cluster_data,$z_in_use_section_start,0);
   $zpos++;  // move pointer beyond current position
   $zpos = strpos($cluster_data,"<td>",$zpos);	// This is the "in use" <td>
   $zpos_in_use = $zpos + 4;
   $zpos = strpos($cluster_data,"</td>",$zpos_in_use);
   $zin_use_length = $zpos - $zpos_in_use;
   $zin_use = substr($cluster_data,$zpos_in_use,$zin_use_length);

//   FIND NUMBER AVAILABLE
   $zpos = $zpos_in_use + $zin_use_length;  //zpos at "</td>" at end of "in-use"
   $zpos = strpos($cluster_data,"<td>",$zpos) + 1; // miss this <td><div...
   $zpos = strpos($cluster_data,"<td>",$zpos); // This is the "available" <td>
   $zpos_available = $zpos + 4;
   $zpos = strpos($cluster_data,"</td>",$zpos_available);
   $zavailable_length = $zpos - $zpos_available;
   $zavailable = substr($cluster_data,$zpos_available,$zavailable_length);
   $znew_available = $znew_total - $zin_use;
   $cluster_data = substr_replace($cluster_data,$znew_available,$zpos_available,$zavailable_length);


//   REPLACE TOTAL FROM LABSTATS BY ARTIFICIAL (BUT CORRECT!) TOTAL
   $zpos = $zpos_available + $zavailable_length;
   	   		     //zpos at "</td>" at end of "available"
   $zpos = strpos($cluster_data,"<td>",$zpos) + 1; // miss this <td>< %= ...
   $zpos = strpos($cluster_data,"<td>",$zpos) + 1; // miss this <td>offline</td>
   $zpos = strpos($cluster_data,"<td>",$zpos);  // This is the "Total" <td>
   $zpos_total = $zpos + 4;
   $zpos = strpos($cluster_data,"</td>",$zpos_total);
   $ztotal_length = $zpos - $zpos_total;
   $ztotal = substr($cluster_data,$zpos_total,$ztotal_length);
   $cluster_data = substr_replace($cluster_data,$znew_total,$zpos_total,$ztotal_length);


} // End of special treatment for cluster id 9


if($light_dark == "dark"){
	echo $cluster_start_dark . $cluster_data . $cluster_end;
	$light_dark = "light";
} else {
	echo $cluster_start_light . $cluster_data . $cluster_end;
	$light_dark = "dark";
}

$cluster_data = "";


}


echo "</table> <!-- TemplateEndEditable --></div>";

echo $new_body_end;

?>