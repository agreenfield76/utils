<?php

//echo implode(' ', file("http://vmlabstats.ex.ac.uk:8080/PieChart?lab=12"));

//http://vmlabstats.ex.ac.uk:8080/PieChart?lab=12&title=Lab Usage: Amory Ground Floor

//echo implode(' ', file("http://vmlabstats.ex.ac.uk:8080/public/current_stats.jsp"));

//echo implode(' ', file("http://vmlabstats.ex.ac.uk:8080/public/custom_stats.jsp?type=current&lab_id=12"));

//echo file("http://vmlabstats.ex.ac.uk:8080/PieChart?lab=12&title=Lab%20Usage:%20Amory%20Ground%20Floor&width=864&height=615");

$im = imagecreatefrompng("http://vmlabstats.ex.ac.uk:8080/PieChart?lab=12&title=Lab%20Usage:%20Amory%20Ground%20Floor&width=1024&height=960");
$im2 = imagecreatefrompng("http://vmlabstats.ex.ac.uk:8080/PieChart?lab=7&title=Lab%20Usage:%20Library%20Open%20Area&width=864&height=615");
header('Content-type: image/png');
imagepng($im);
imagepng($im2);
imagedestroy($im);


?>

<html>

<body>

<!--<img src="http://vmlabstats.ex.ac.uk:8080/public/PieChart?lab=12">-->


</body>

</html>