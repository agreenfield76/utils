<?php
$src = file_get_contents('https://testsid.exeter.ac.uk/aspx_queues/now_serving.aspx?ROOM=SSC&CALLCENTRE=EXSS');

preg_match('/<input type="hidden" name="ctl00\$cph_MAIN\$hidCurrServeList" id="ctl00_cph_MAIN_hidCurrServeList" value="(.*)" \/>/', $src, $concatIDs);

preg_match('/<table id="ctl00_esd_OverAllPlaceholder" class="esd_OverAllPlaceholder esd_OverAllPlaceholderOption" cellpadding="0" cellspacing="0" style="height:100%;width:100%;">(.*)<\/table>(.*)<a id="ESDbottomofpage">/s',$src,$newContent);

if(empty($concatIDs[1])) $concatIDs[1] = ' ';

$idsNewTemp = explode('$', $concatIDs[1]);
$idsOldTemp = explode('$', $_POST['ids']);

foreach ($idsNewTemp as $temp)
{
	$idsNew[$temp] = $temp;
}
foreach ($idsOldTemp as $temp)
{
	$idsOld[$temp] = $temp;
}
$unsets = array("",''," ",' ');
foreach ($unsets as $unset)
{
	unset($idsOld[$unset]);
	unset($idsNew[$unset]);
}

foreach ($idsOld as $id)
{
	$key = array_search($id,$idsNew);
	unset($idsNew[$key]);
}

$count = 0;
foreach ($idsNew as $newid)
{
	$count++;
}

$newCode= array();

$newCode['ids'] = $concatIDs[1];

$newCode['newcontent'] = $newContent[1];

if($count >0) $newCode['status'] = 'TRUE';
else $newCode['status'] = 'FALSE';

echo json_encode($newCode);


?>