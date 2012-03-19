<?PHP
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
include("../funcs/secure.php");
include("../funcs/config.php");
include("../funcs/userdefined.php");
echo "<script type=\"text/javascript\" src=\"js/tablesorter.js\"></script>";

if((isset($_POST['action']))&&($_POST['action']=="update"))
{
	$content = updateAIML();
	
}
elseif((isset($_GET['action']))&&($_GET['action']=="del")&&(isset($_GET['id']))&&($_GET['id']!=""))
{
	$content = delAIML($_GET['id']);
}
elseif((isset($_GET['action']))&&($_GET['action']=="edit")&&(isset($_GET['id']))&&($_GET['id']!=""))
{
	$content = editAIMLForm($_GET['id']);
}
elseif((isset($_GET['action']))&&($_GET['action']=="pass")&&(isset($_GET['id']))&&($_GET['id']!=""))
{
	$content = PassAIML($_GET['id']);
}
else
{
	$content = userdefinedlist();
}



include("inc/header.php");
include("inc/header_nav_bar.php");
echo "</div> 
	<div id=\"content\"><div id=\"nocol\">
	<div id=\"pTitle\">User Defined AIML</div>
		<p>$content</p>
	</div></div>
	<div id=\"nav\">";
include("inc/side_nav_bar.php");
include("inc/footer.php");
?>