<?PHP
include_once("response_handler.php");
if(trim($_POST['chat'])!="")
{
	$savelook = minClean($_POST['chat']);
	$look = trim(strtolower($_POST['chat']));
	$look = formatchinese($look);
	$look = preg_replace("/\.|!|\?|,|:|;/",".",$look);
	$look = preg_replace("/\.+/",".",$look);
	$look = str_replace('"','',stripslashes($look));
	$look = cleanInput($look);
	$look = spellcheck($look);
	if($look=="cleardefaults")
	{
		$response_Array=array();
		$response_Array['sessionid'] = $sessionid;
		$response_Array['userid'] = $userid;
		$response_Array['top']="om";
		$response_Array['second']="om";
		$response_Array['third']="om";
		$response_Array['fourth']="om";
		$response_Array['fifth']="om";
		$response_Array['sixth']="om";
		$response_Array['seventh']="om";
		$response_Array['last']="om";
		$_SESSION['response_Array'] = resetResponses($response_Array);
		runDebug("",3,"Post Detected","<br>Array Name = Not in array<br>All memory cleared");
	}
	else
	{
		runDebug("",3,"Post Detected","<br>Array Name = Not in array<br>Starting program");
		$response_Array = $_SESSION['response_Array'];
		$response_Array['who']="human";
		$response_Array['lookingfor']=$look;
		$response_Array['masterlook']=$look;
		$response_Array = frontOfStack($response_Array,htmlentities(urlencode(stripslashes($savelook)),ENT_QUOTES,"UTF-8") ,"input");
		$response_Array['biganswer']="";
		$response_Array['usersaid'] = $savelook;
		$response_Array['bot'] = $thisbot;
		if($userid!="")
		{
			$response_Array['sessionid'] = $sessionid;
			$response_Array['userid'] = $userid;
		}
		$line = trim($look);
		if($line!="")
		{
			$response_Array['lookingfor']=$line;
			$response_Array = checkresponse($response_Array,"human");
			if($response_Array['who'] == "human")
			{
				$response_Array['biganswer'] .= " ".$response_Array['answer'];
			}
		}

		if($response_Array['who'] == "human")
		{
			for($i=$convoLines;$i>=0;$i--)
			{
				if(isset($response_Array['input'][$i]))
				{
					$returnstr=restorechinese(stripslashes(urldecode($response_Array['that'][$i])));
				}
			}
			logConvo($response_Array);
			updateUser($response_Array);
		}
	}
	endTime($response_Array,$time_start);
	$_SESSION['response_Array'] = resetResponses($response_Array);
}
else
{
	$response_Array = array();
	if($userid!="")
	{
		$response_Array['sessionid'] = $sessionid;
		$response_Array['userid'] = $userid;
	}
	$response_Array['top']="om";
	$response_Array['second']="om";
	$response_Array['third']="om";
	$response_Array['fourth']="om";
	$response_Array['fifth']="om";
	$response_Array['sixth']="om";
	$response_Array['seventh']="om";
	$response_Array['last']="om";
	$_SESSION['response_Array'] = resetResponses($response_Array);
}
mysql_close($dbconn);
echo $returnstr;
?>