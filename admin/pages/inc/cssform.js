// JavaScript Document
window.onload=function()
{if(document.getElementById)
{var linkContainer=document.getElementById('fm-intro');var linebreak=linkContainer.appendChild(document.createElement('br'));var toggle=linkContainer.appendChild(document.createElement('a'));toggle.href='#';toggle.appendChild(document.createTextNode(' Hide optional fields?'));toggle.onclick=function()
{var linkText=this.firstChild.nodeValue;this.firstChild.nodeValue=(linkText==' Hide optional fields?')?' Display optional fields?':' Hide optional fields?';var tmp=document.getElementsByTagName('div');for(var i=0;i<tmp.length;i++)
{if(tmp[i].className=='fm-opt')
{tmp[i].style.display=(tmp[i].style.display=='none')?'block':'none';}}
return false;}}}