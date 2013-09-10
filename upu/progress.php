<?php
/**
 * $Id: progress.php,v 1.2 2005/09/22 02:17:10 legend9 Exp $
 */

$processID = md5(microtime() . mt_rand(0, 99999));
?>
<html>
<head>
<title>UGiA PHP UPLOADER</title>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=utf-8">
<style type="text/css" media="screen">
	@import url(misc/progress.css );
</style>
<script type="text/javascript">
var	processID =	"<?php echo $processID;?>";
</script>
<script type="text/javascript" src="misc/zh-cn.js"></script>
<script type="text/javascript" src="misc/progress.js"></script>
</head>
<body>
<div align="center">
<table width="350" cellpadding="3px" cellspacing="0" border="0">
  <tr>
    <td><img src="images/upload.gif" alt="uploading..." width="272" height="60" id="dynamicimg"/></td>
  </tr>
  <tr>
    <td><span id="filename"><script type="text/javascript">document.write(lang_plzwait);</script><span></td>
  </tr>
  <tr>
    <td><div id="div_progress"><div id="progressbar"></div></div></td>
  </tr>  
  <tr>
    <td><div id="transinfo"><span class="fixwidth"><script type="text/javascript">document.write(lang_remaintime);</script></span>: <span id="remaintime"></span> (<script type="text/javascript">document.write(lang_uploaded);</script>:<span id="bytes"></span> &nbsp;&nbsp;<script type="text/javascript">document.write(lang_totalsize);</script>:<span id="httplength"></span>)</div></td>
  </tr>
  <tr>
    <td><span class="fixwidth"><script type="text/javascript">document.write(lang_traspeed);</script></span>: <span id="speed"></span></td>
  </tr>
  <tr>
    <td align="right"><button onclick="cancel()" style="height: 22px; width:60px;" id="cancel"><script type="text/javascript">document.write(lang_cancel);</script></button>&nbsp;<button disabled style="height: 22px; width:60px;" onclick="window.close()" id="ok"><script type="text/javascript">document.write(lang_ok);</script></button></td>
  </tr>  
</table>
</div>
</body>
</html>