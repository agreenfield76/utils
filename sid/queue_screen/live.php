<?php

$src = file_get_contents('https://sid.exeter.ac.uk/aspx_queues/now_serving.aspx?ROOM=SSC&CALLCENTRE=EXSS');

preg_match('/<input type="hidden" name="ctl00\$cph_MAIN\$hidCurrServeList" id="ctl00_cph_MAIN_hidCurrServeList" value="(.*)" \/>/', $src, $concatIDs);

if(empty($concatIDs[1])) $concatIDs[1] = ' ';
/*

*/
/*
'</title>' => '</title><script type="text/javascript" src="js/jquery-1.4.1.min.js"></script>
  <script type="text/javascript" src="js/custom.js"></script>',
  */

$replacements = array(
	'<meta http-equiv="Page-Enter" content="revealtrans(duration=0.0)" /><meta http-equiv="Page-Exit" content="revealtrans(duration=0.0)" />' => '<meta http-equiv="Page-Enter" content="blendTrans(Duration=0.1)" /><meta http-equiv="Page-Exit" content="blendTrans(Duration=0.1)" />',
	'src="../' => 'src="https://sid.exeter.ac.uk/',
	'href="../' => 'href="https://sid.exeter.ac.uk/',
	'</body>' => '<form id="testform" name="testform" action="http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'" method="post"><input type="hidden" name="ids" value="'.$concatIDs[1].'" /></form><script type="text/JavaScript">
window.onload = function() {
   setTimeout(\'document.getElementById("testform").submit()\',6000);
};
</script></body>',
	'<script type="text/javascript">
//<![CDATA[
Sys.Application.add_init(function() {
    $create(Sys.UI._Timer, {"enabled":true,"interval":5000,"uniqueID":"ctl00$cph_MAIN$UpdateTimer"}, null, null, $get("ctl00_cph_MAIN_UpdateTimer"));
});
//]]>
</script>
' => '',
'<script type="text/javascript">
    /*@cc_on 
       @if (@_jscript)
          Sys.UI.DomElement.getLocation=function(a){if(a.self||a.nodeType===9)return new Sys.UI.Point(0,0);var b=a.getBoundingClientRect();if(!b)return new Sys.UI.Point(0,0);var c=a.document.documentElement,d=b.left-2+c.scrollLeft,e=b.top-2+c.scrollTop;try{var g=a.ownerDocument.parentWindow.frameElement||null;if(g){var f=2-(g.frameBorder||1)*2;d+=f;e+=f}}catch(h){}return new Sys.UI.Point(d,e)};
       @else */
    /* @end @*/
    </script>
' => '',
'<embed src=\'../aspx_queues/sounds/Bing2.wav\' autostart=true width=1 height=1 id=\'sound1\' enablejavascript=\'true\'>' => '',
'<script src="/ScriptResource.axd?d=zHgEP5rVBGL3jMYaTC16zAJFayEkCN6JZvHwjosG8UVlqh03dnShNpQ5LzlZKAwAWEpP8UMTNbQbIWiy3nCZQZV-2MXdne_YbnIW1xvMDecNM-tIkVzSRzxbuuQeLk0NFRtwFek3JigA0PG_yf43WA_nkZ4y3V2bVsRysp8yGAA1&amp;t=ffffffff940d030f" type="text/javascript"></script>
<script src="/ScriptResource.axd?d=GjG9OPW-0TZ1nfoAd1TuD18NSXA_nw12dtPS3BEQrChYxcL5PLs-ywseBizNLunqqMWN7KSif3tm9OsCmE5zUDFg3Ne31NBex3MoQ74Qu-yPGxko5HnIulFTrSN4YALafzbTfxgmCOKvu3XFvAyhujAsEIbDWbbfR46TSpxZe_k1&amp;t=ffffffff940d030f" type="text/javascript"></script>
' => '',
'url(../exeter_files/queueimage.png)' => 'url(https://sid.exeter.ac.uk/exeter_files/queueimage.png)',
);

$edited_src = strtr($src,$replacements);

if (isset($_POST['ids']) && $_POST['ids'] != "")
{
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
	
	
	if($count >0)
	{
		
		$makeSoundReplacement = array(
			'</body>' => '<embed src=\'lib/next_please.wav\' autostart=true width=1 height=1></body>'
		);
		
		
		$edited_src = strtr($edited_src,$makeSoundReplacement);
	}
}


echo $edited_src;
?>