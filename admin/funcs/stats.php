<?PHP
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
function getStats($interval)
{
	global $dbn;
	
	$dbconn = openDB();
	
	if($interval!="all")
	{
		$intervaldate =  date("Y-m-d", strtotime($interval));
		$sqladd = " WHERE date(timestamp) >= '$intervaldate'";
	}
	else
	{
		$sqladd ="";
	}
		
	//get undefined defaults from the db
	$sql = "SELECT count(distinct(`userid`)) AS TOT FROM `$dbn`.`conversation_log` $sqladd";
	//echo $sql;
	$result = mysql_query($sql,$dbconn)or die(mysql_error());
	$row = mysql_fetch_array($result);
	$res = $row['TOT'];
	return $res;
}

function getChatLines($i,$j)
{
	global $dbn;

	$dbconn = openDB();
	
	if($i=="average")
	{
		$sql = "SELECT AVG(`chatlines`) AS TOT FROM `$dbn`.`users` WHERE `chatlines` != 0";	
	}
	else
	{
		$sql = "SELECT count(distinct(`id`)) AS TOT FROM `$dbn`.`users` WHERE `chatlines` >= $i AND `chatlines` <= $j";
	}
		
	//get undefined defaults from the db
	//echo $sql;
	$result = mysql_query($sql,$dbconn)or die(mysql_error());
	$row = mysql_fetch_array($result);
	$res = $row['TOT'];
	return $res;
}

?>