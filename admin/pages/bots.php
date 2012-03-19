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
include("../funcs/bots.php");


if((isset($_POST['action']))&&($_POST['action']=="update"))
{
	$content = updateBot();
	$content .= getBot(1);
}
else
{
	$content = getBot(1);
}



include("inc/header.php");
include("inc/header_nav_bar.php");

echo "</div><div id=\"content\"><div id=\"nocol\">";
echo "<div id=\"pTitle\">Bot Personality</div>";
echo "<p>$content</p>";
echo "</div></div><div id=\"nav\">";

include("inc/side_nav_bar.php");
include("inc/footer.php");
?>