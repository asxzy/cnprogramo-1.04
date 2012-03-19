<?PHP
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
function getuserList($showing)
{
	//db globals
	global $dbn;
	$dbconn = openDB();
	$list ="";
	
	
	$showarray = array("last 20","previous week","previous 2 weeks","previous month","last 6 months","this year","previous year","all years");
	

	if($showing == "today")
	{
		$sql = "SELECT DISTINCT(`userid`),COUNT(`userid`) AS TOT FROM `$dbn`.`conversation_log` WHERE DATE(`timestamp`) = '".date("Y-m-d")."' GROUP BY `userid`,DATE(`timestamp`) ORDER BY `timestamp` DESC";
	}
	elseif($showing == "previous week")
	{
		$lastweek = strtotime("-1 week");
		$sql = "SELECT DISTINCT(`userid`),COUNT(`userid`) AS TOT FROM `$dbn`.`conversation_log` WHERE DATE(`timestamp`) >= '$lastweek' GROUP BY `userid`,DATE(`timestamp`) ORDER BY `timestamp` DESC";
	}
	elseif($showing == "previous 2 weeks")
	{
		$lasttwoweek = strtotime("-2 week");
		$sql = "SELECT DISTINCT(`userid`),COUNT(`userid`) AS TOT FROM `$dbn`.`conversation_log` WHERE DATE(`timestamp`) >= '$lasttwoweek' GROUP BY `userid`,DATE(`timestamp`) ORDER BY `timestamp` DESC";
	}	
	elseif($showing == "previous month")
	{
		$lastmonth = strtotime("-1 month");
		$sql = "SELECT DISTINCT(`userid`),COUNT(`userid`) AS TOT FROM `$dbn`.`conversation_log` WHERE DATE(`timestamp`) >= '$lastmonth' GROUP BY `userid`,DATE(`timestamp`) ORDER BY `timestamp` DESC";
	}	
	elseif($showing == "previous 6 months")
	{
		$lastsixmonth = strtotime("-6 month");
		$sql = "SELECT DISTINCT(`userid`),COUNT(`userid`) AS TOT FROM `$dbn`.`conversation_log` WHERE DATE(`timestamp`) >= '$lastsixmonth' GROUP BY `userid`,DATE(`timestamp`) ORDER BY `timestamp` DESC";
	}	
	elseif($showing == "past 12 months")
	{
		$lastyear = strtotime("-1 year");
		$sql = "SELECT DISTINCT(`userid`),COUNT(`userid`) AS TOT FROM `$dbn`.`conversation_log` WHERE DATE(`timestamp`) >= '$lastyear' GROUP BY `userid`,DATE(`timestamp`) ORDER BY `timestamp` DESC";
	}
	elseif($showing == "all time")
	{
		$sql = "SELECT DISTINCT(`userid`),COUNT(`userid`) AS TOT FROM `$dbn`.`conversation_log` GROUP BY `userid`,DATE(`timestamp`) ORDER BY `timestamp` DESC";
	}	
	else//if($showing == "last 20")
	{
		$sql = "SELECT DISTINCT(`userid`),COUNT(`userid`) AS TOT FROM `$dbn`.`conversation_log` GROUP BY `userid`,DATE(`timestamp`) ORDER BY `timestamp` DESC limit 20";
	}
	
	$list ="<div class=\"userlist\">";
	
	$result = mysql_query($sql,$dbconn)or die(mysql_error());
	while($row = mysql_fetch_array($result))
	{
		$list .= "<br><a href=\"logs.php?showing=$showing&id=".$row['userid']."\">USER:".$row['userid']."(".$row['TOT'].")</a>";
	}
	$list .="</div>";
	
	
	mysql_close($dbconn);
	
	return $list;
	
}

function showThis($showing="last 20")
{

	$showarray = array("last 20","today","previous week","previous 2 weeks","previous month","last 6 months","past 12 months","all time");
	$options = "";
	
	
	
	foreach($showarray as $index => $value)
	{
		if($value == $showing)
		{
			$sel = "SELECTED = SELECTED";
		}
		else
		{
			$sel = "";
		}
		
		$options .= "<option value=\"$value\" $sel>$value</option>";
		
	} 

	$form = "<form name=\"showthis\" method=\"get\" action=\"logs.php\">
			<select name=\"showing\" id=\"showing\">
				$options
			</select>
			<input type=\"submit\" id=\"submit\" name=\"submit\" value=\"show\">
			</form>";
	return $form;
}	

function getuserConvo($id,$showing)
{
	//db globals
	global $dbn;
	
	
	if($showing == "today")
	{
		$sqladd = "AND DATE(`timestamp`) = '".date('Y-m-d')."'";
		$title = "Todays ";
	}
	elseif($showing == "previous week")
	{
		$lastweek = strtotime("-1 week");
		$sqladd = "AND DATE(`timestamp`) >= '".$lastweek."'";
		$title = "Last weeks ";
	}
	elseif($showing == "previous 2 weeks")
	{
		$lasttwoweek = strtotime("-2 week");
		$sqladd = "AND DATE(`timestamp`) >= '".$lasttwoweek ."'";
		$title = "Last two weeks ";
	}	
	elseif($showing == "previous month")
	{
		$lastmonth = strtotime("-1 month");
		$sqladd = "AND DATE(`timestamp`) >= '".$lastmonth ."'";
		$title = "Last months ";
	}	
	elseif($showing == "previous 6 months")
	{
		$lastsixmonth = strtotime("-6 month");
		$sqladd = "AND DATE(`timestamp`) >= '".$lastsixmonth ."'";
		$title = "Last six months ";
	}	
	elseif($showing == "past 12 months")
	{
		$lastyear = strtotime("-1 year");
		$sqladd = "AND DATE(`timestamp`) >= '".$lastyear ."'";
		$title = "Last twelve months ";
	}
	elseif($showing == "all time")
	{
		$sql = "";
		$title = "All ";
	}	
	else//if($showing == "last 20")
	{
		$sqladd = "";
		$title = "Last ";
	}
	
	
	
	$lasttimestamp = "";
	$i = 1;
	
	
	$dbconn = openDB();
	//get undefined defaults from the db
	$sql = "SELECT *  FROM `$dbn`.`conversation_log` WHERE `userid` = $id $sqladd ORDER BY `id` ASC";

	
	$list = "<hr><br/><h4>$title conversations for user: $id</h4>";
	
	$list .="<div class=\"convolist\">";
	
	$result = mysql_query($sql,$dbconn)or die(mysql_error());
	while($row = mysql_fetch_array($result))
	{
		$thisdate = date("Y-m-d",strtotime($row['timestamp']));
		

		
		
		if($thisdate!=$lasttimestamp)
		{
			
			if($i>1)
			{
				if($showing == "last 20")
				{
						break;
				}
			}
			
			$date = date("Y-m-d");
			$list .= "<hr><br/><h4>Conversation#$i $thisdate</h4>";
			
		
			
			
			$i++;
		}
			$list .= "<br><span style=\"color:DARKBLUE;\">USER:".$row['input']."</span>";
			$list .= "<br><span style=\"color:GREEN;\">BOT:".$row['response']."</span>";
			
	
		
		
		$lasttimestamp = $thisdate;
		
	}
	$list .="</div>";
	mysql_close($dbconn);




	return $list;	
}
?>