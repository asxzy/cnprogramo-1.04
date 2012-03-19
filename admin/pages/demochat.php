<?php
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
include("../funcs/secure.php");
include("../funcs/config.php");

include("inc/header.php");
include("inc/header_nav_bar.php");
echo <<<EOT
</div> 
<script type="text/javascript">
function down(code)
{
	if(code == 13)
	{
		chat();
	}
}
function f()
{
	document.getElementById('text').focus();
}
var xmlhttp;
function chat()
{
document.getElementById('result').innerHTML="You: "+document.getElementById('text').value+"<br/>";
document.getElementById('btn').disabled=true;
document.getElementById('btn').value="请稍后……";
xmlhttp=null;
if (window.XMLHttpRequest)
  {// code for Firefox, Opera, IE7, etc.
  xmlhttp=new XMLHttpRequest();
  }
else if (window.ActiveXObject)
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
if (xmlhttp!=null)
  {
  xmlhttp.onreadystatechange=state_Change;
  xmlhttp.open("POST","./../../bot/chat.php",true);
  xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  xmlhttp.send("chat="+document.getElementById('text').value);
  }
else
  {
  alert("Your browser does not support XMLHTTP.");
  }
 document.getElementById('text').value="";
}

function state_Change()
{
	if (xmlhttp.readyState==4)
	{
		if (xmlhttp.status==200)
		{
			document.getElementById('result').innerHTML+="Bot: "+xmlhttp.responseText+"<br/>";
		}
		else
		{
			alert("Problem retrieving data:" + xmlhttp.statusText);
		}
		document.getElementById('btn').disabled=false;
		document.getElementById('btn').value="发送";
		f();
	}
}
</script>
	<div id="content">
		<div id="nocol">
			<div id="pTitle">Chat</div>
  				<p style="width:400px; display:block;">
<div>
        <div id="result"></div>
        <div>
		<input type="text" name="text" id="text" />
		<input type="button" value="发送" id="btn" onclick="javascript:chat();" />
		</div>
</div>
<p>&nbsp;</p>

</p></div>
			</div>
<div id="nav">
EOT;
include("inc/side_nav_bar.php");
include("inc/footer.php");
?>
