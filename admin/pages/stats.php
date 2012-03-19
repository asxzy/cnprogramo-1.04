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
include("../funcs/stats.php");


$oneday = getStats("today");
$oneweek = getStats("-1 week");
$onemonth = getStats("-1 month");
$sixmonths = getStats("-6 month");
$oneyear = getStats("1 year ago");
$alltime = getStats("all");

$singlelines = getChatLines(1,1);
$alines = getChatLines(1,25);
$blines = getChatLines(26,50);
$clines = getChatLines(51,100);
$dlines = getChatLines(101,1000000);
$avg = getChatLines("average",1000000);

include("inc/header.php");
include("inc/header_nav_bar.php");
echo "</div> 
	<div id=\"content\">
		<div id=\"nocol\">
			<div id=\"pTitle\">Stats</div>
  				<p><u>Conversations: </u></p><br/>
					<p>Today: $oneday
					<br/>Last week: $oneweek
					<br/>Last month: $onemonth
					<br/>Last six months: $sixmonths
					<br/>Last year: $oneyear
					<br/>All time: $alltime</p>
					<p>&nbsp;</p>
				<p><u>Conversation Lines: </u></p><br/>
					<p>Single line: $singlelines
					<br/>1-25 lines: $alines
					<br/>25-50 lines: $blines
					<br/>51-100 lines: $clines
					<br/>101+ lines: $dlines
					<br/>Average: $avg
					</p>
			</div>
		</div>
	<div id=\"nav\">";
include("inc/side_nav_bar.php");
include("inc/footer.php");
?>