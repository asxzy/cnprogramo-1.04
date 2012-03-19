<?PHP
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
$filename =  basename($_SERVER['PHP_SELF']);
$nclass = "";
$dclass = "";
$sclass = "";
$hclass = "";
$bclass = "";
if($filename == "news.php")
{
	$nclass = "selected";
}
elseif($filename == "docu.php")
{
	$dclass = "selected";
}
elseif($filename == "stats.php")
{
	$sclass = "selected";
}
elseif($filename == "index.php")
{
	$hclass = "selected";
}
elseif($filename == "bugs.php")
{
	$bclass = "selected";
}
echo "<ul>
<li><a href=\"index.php\" class=\"$hclass\">Home</a> </li>
<li><a href=\"news.php\" class=\"$nclass\">News </a></li>
<li><a href=\"docu.php\" class=\"$dclass\">Documentation</a></li>
<li><a href=\"bugs.php\" class=\"$bclass\">Bug Tracking</a></li>
<li><a href=\"stats.php\" class=\"$sclass\">Stats </a></li>
<li><a href=\"http://www.program-o.com/support/\" target=\"_blank\">Support </a></li>
<li><a href=\"logout.php\">Log out</a> </li>
</ul>";
?>