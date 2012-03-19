<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>STU Robot学盟智能机器人</title>
<link href="css.css" rel="stylesheet" type="text/css" />
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
document.getElementById('result').innerHTML+="<div class=\"demouser\">You: "+document.getElementById('text').value+"</div>";
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
  xmlhttp.open("POST","./bot/chat.php",true);
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
			document.getElementById('result').innerHTML+="<div class=\"demobot\">Bot: "+xmlhttp.responseText+"</div>";
			document.getElementById('result').scrollTop=document.getElementById('result').scrollHeight ;
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
</head>
<body OnLoad="f();" onkeydown="down(event.keyCode)">
<h1>STU Robot学盟智能机器人</h1><br/>
<h2>注意：请不要输入数学算式、非简体中文的内容，目前暂不支持！</h2>
<h2>对话时请符合中文语法，不要省略主谓宾！</h2>
<h2>请不要教一些无用、谩骂的对话，不会通过审核！</h2>
<h2>另：征集STU Robot形象，具体请联系<a href="http://leo108.com">leo108</a></h2>
<div class="container">
	<div class="main">
        <div id="result">
		</div>
        <div>
				<input type="text" name="text" id="text" />
				<input type="button" value="发送" id="btn" onclick="javascript:chat();" />
		</div>
	</div>    
	<div class="right">
		<div class="avatar">
		<img src="normal.png" alt="STU Robot" />
		</div>
		<div class="notice"><b>使用说明：</b><br />
		输入<b>学习</b> 进入内容教学模式；<br />
		学习的问题和回答请尽量使用中文；<br />
		学习的内容需要通过审核之后才会显示；<br />
		目前的学习模式只能学习单句对话，如果要机器人学习连续对话，请到<a href="http://www.stuhack.net" target="_blank">学盟论坛</a>联系leo108。<br />
		机器人回答的形式是<b>数字|回答|数字</b>，这样做的具体原因以后会告诉大家。<br />
		目前对话库还比较小，希望大家多多“调教”……<br />
		</div>
	</div>
</div>
<div>
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4404463912949543";
/* robot */
google_ad_slot = "7768666418";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
<br/>
<a href="http://leo108.com" target="_blank">leo108's blog</a>
</div>
<script src="http://s96.cnzz.com/stat.php?id=3767474&web_id=3767474"></script>
</body>
</html>
