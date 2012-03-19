<?php
session_start();
//-----------------------------------------------------------------------------------------------
//Program-o Version 1.0.4
//PHP MYSQL AIML interpreter
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
//response_handler.php
//contains functions for initilizing and returning inputs and outputs
//-----------------------------------------------------------------------------------------------

//-----------------------------------------------------------
//formatchinese($str) add a space into each Chinese character
//-----------------------------------------------------------
function formatchinese($str){
	include_once('./pscws4.class.php');
	$pscws = new PSCWS4('utf8');
	$pscws->set_charset('utf8');
	$pscws->set_dict('./etc/dict.utf8.xdb');
	$pscws->set_rule('./etc/rules.utf8.ini');
	$temp = "";
	$pscws->send_text($str);
	while ($some = $pscws->get_result())
	{
	   foreach ($some as $word)
	   {
		 $temp .= $word["word"]." ";
	   }
	}
	if(preg_match_all("/[\x7f-\xff]{1}  /",$temp,$out)){
		foreach($out[0] as $value){
			$temp = preg_replace("/$value/",trim($value),$temp);
		}
	}
return trim($temp);
}


//-----------------------------------------------------------------
//restorechinese($str) remove the space after each Chinse character
//-----------------------------------------------------------------
function restorechinese($str){
	if(preg_match_all("/[\x7f-\xff]{1} /",$str,$out)){
		foreach($out[0] as $value){
			$str = preg_replace("/$value/",trim($value),$str);
		}
	}
	return trim($str);
}



//leo modify
if (isset($_COOKIE["botconvoid"])) //check to see if cookie exists
{
  	//cookie exists BUT session is different so we need to reset and get the current user so set the incs to 0
	if((!isset($_SESSION['botconvoid'])) || ($_SESSION['botconvoid']!=$_COOKIE["botconvoid"]))
	{
			$_SESSION['botconvoid']=$_COOKIE["botconvoid"]; //set the sessions
			$_SESSION['set']=0;
	}
	$_SESSION['botconvoid']=$_COOKIE["botconvoid"]; //lets just update 
}
else // no cookie exists......
{
	if(session_id()=="") //session hasnt started re-generate it
	{
		session_start();
		session_regenerate_id();
	}
	$_SESSION['botconvoid']=session_id(); //set the sessionid
	$_SESSION['set']=0; //set the flag to intialise te user
	
	$expire=time()+60*60*24*365; //set it to last 1 year
	setcookie("botconvoid", $_SESSION['botconvoid'], $expire); //set the cookie
	$_COOKIE["botconvoid"] = session_id();
} 

//set this global for the sessionid to equal the cookie

$sessionid = $_COOKIE["botconvoid"];


$time_start = microtime(true);
require_once("debugging.php");
require_once("config.php");
require_once("tag_functions.php");
require_once("check_aiml_part.php");
require_once("getsetvars.php");
debugMessage();

//globals
$dbconn = mysql_connect($dbh,$dbu,$dbp);
mysql_query("SET NAMES UTF8"); //leo modify
$arrayHolder = array();


if((!isset($_SESSION['set']))||($_SESSION['set']==0))
{
	$userid = intisaliseUser($sessionid);
	$_SESSION['set']=1;
	$_SESSION['userid']=$userid;
}
else
{
	$userid = "";
}
$userid = $_SESSION['userid'];


//--------------------------------------------------
//getuserid($sessionid) get the user id from the db
//based on the session id
//--------------------------------------------------
function getuserid($sessionid)
{
	//db globals
	global $dbconn,$dbn,$debugmode;
	//get undefined defaults from the db
	$sql = "SELECT * FROM `$dbn`.`users` WHERE `session_id` = '$sessionid' limit 1";

	if(($debugmode==1)||($debugmode==2))
	{
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
	}
	else
	{
		$result = mysql_query($sql,$dbconn);
	}
	$count = mysql_num_rows($result);
	if($count>0)
	{
		$row = mysql_fetch_array($result);
		$userid = $row['id'];
	}
	else
	{
		$userid = intisaliseUser($sessionid);
	}
	
	return $userid;
	
}

//--------------------------------------------------
//intisaliseUser($sessionid) - initialise the user
//--------------------------------------------------
function intisaliseUser($sessionid)
{
	//db globals
	global $dbconn,$dbn,$debugmode;
	//get undefined defaults from the db
	
	$sr = "";
	
	$sa = mysql_escape_string($_SERVER['REMOTE_ADDR']); 
	if(isset($_SERVER['HTTP_REFERER']))
	{
		$sr = mysql_escape_string($_SERVER['HTTP_REFERER']);
	}
	$sb = mysql_escape_string($_SERVER['HTTP_USER_AGENT']);

	$sql = "INSERT INTO `$dbn`.`users` (`id` ,`session_id` ,`chatlines` ,`ip` ,`referer` ,`browser` ,`date_logged_on` ,`last_update`)
	VALUES ( NULL , '$sessionid', '0', '$sa', '$sr', '$sb', CURRENT_TIMESTAMP , '0000-00-00 00:00:00')";

	if(($debugmode==1)||($debugmode==2))
	{
		mysql_query($sql,$dbconn)or die(mysql_error());
	}
	else
	{
		mysql_query($sql,$dbconn);
	}
	$sql = "SELECT * FROM `$dbn`.`users` WHERE `session_id` = '$sessionid' limit 1";

	if(($debugmode==1)||($debugmode==2))
	{
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
	}
	else
	{
		$result = mysql_query($sql,$dbconn);
	}
	$row = mysql_fetch_array($result);
	
	$userid = $row['id'];
	return $userid;
	
}

//--------------------------------------------------
//function logConvo($response_Array)
//function to log the conversation
//--------------------------------------------------
function logConvo($response_Array)
{
	//db globals
	global $dbconn,$dbn,$debugmode;
	//get undefined defaults from the db
	
	$b = $response_Array['bot'];
	$u = $response_Array['userid'];
	$i = mysql_escape_string($response_Array['usersaid']);
	$a = restorechinese(mysql_escape_string($response_Array['biganswer']));
	
	$sql = "INSERT INTO `$dbn`.`conversation_log` (`id` , `input` , `response` , `userid` ,`bot_id` ,`timestamp`)
	VALUES (NULL , '$i', '$a', '$u', '$b', CURRENT_TIMESTAMP)";
	if(($debugmode==1)||($debugmode==2))
	{
		mysql_query($sql,$dbconn)or die(mysql_error());
	}
	else
	{
		 mysql_query($sql,$dbconn);
	}	
}

//--------------------------------------------------
//function logUnknown($response_Array)
//if we come across anythiing we do not have a response for 
//then insert into db for us to check later
//--------------------------------------------------
function logUnknown($response_Array)
{
	//db globals
	global $dbconn,$dbn,$debugmode;
	//get undefined defaults from the db
	
	$u = $response_Array['userid'];
	$i = mysql_escape_string($response_Array['usersaid']);
	
	$sql = "INSERT INTO `$dbn`.`unknown_inputs` (`id` , `input` , `userid` ,`timestamp`)
	VALUES (NULL , '$i', '$u', CURRENT_TIMESTAMP)";
	
	if(($debugmode==1)||($debugmode==2))
	{
		mysql_query($sql,$dbconn)or die(mysql_error());
	}
	else
	{
		mysql_query($sql,$dbconn);
	}	
}

//--------------------------------------------------
//function updateUser($response_Array)
//just update the number of chats with the bot
//--------------------------------------------------
function updateUser($response_Array)
{
	//db globals
	global $dbconn,$dbn,$debugmode;
	//get undefined defaults from the db
	
	$u = $response_Array['userid'];
	
	$sql = "UPDATE `$dbn`.`users` SET `chatlines` = chatlines+1, `last_update`=CURRENT_TIMESTAMP WHERE `id` = '$u' limit 1";
	if(($debugmode==1)||($debugmode==2))
	{
		mysql_query($sql,$dbconn)or die(mysql_error());
	}
	else
	{
		mysql_query($sql,$dbconn);
	}	
}

//-----------------------------------------------------------------------------------------------
//minClean()
//this is the minimum clean we can do .. we want to save the user input BUT we dont want tags and other exploity stuff
//-----------------------------------------------------------------------------------------------
function minClean($tmp)
{
	$tmp = strip_tags($tmp);
	$tmp = trim($tmp);
	//debug
	runDebug("",3,"minClean","<br>Array Name = Not in array<br>Input was min cleaned");

	return $tmp;
}
//-----------------------------------------------------------------------------------------------
//cleanInput($tmp)
//clean the input before trying to match it to an AIML Patteren
//-----------------------------------------------------------------------------------------------
function cleanInput($tmp)
{
	//debug
	$dtmp = $tmp;
	
	//strip any html tags .. naughty naughty these shouldnt be here but just in case
	$tmp = strip_tags($tmp);
	
	//remove puncutation except full stops
	$tmp = preg_replace('/\.+/', '.', $tmp);
	$tmp = preg_replace('/\,+/', '', $tmp);
	$tmp = preg_replace('/\!+/', '', $tmp);
	$tmp = preg_replace('/\?+/', '', $tmp);
	
	$tmp = str_replace("+", " PLUS ", $tmp);
	$tmp = str_replace(" - ", " MINUS ", $tmp);
	$tmp = str_replace("*", " TIMES ", $tmp);
	$tmp = str_replace("/", " DIVIDED BY ", $tmp);
	
	$tmp = str_replace("'", " ", $tmp);
	$tmp = str_replace("\"", " ", $tmp);
	$tmp = preg_replace('/\s\s+/', ' ', $tmp);
	//replace more than 2 in a row occurances of the same char with two occurances of that char
	$tmp = preg_replace('/aa+/', 'oaa', $tmp);
	$tmp = preg_replace('/bb+/', 'bb', $tmp);
	$tmp = preg_replace('/cc+/', 'cc', $tmp);
	$tmp = preg_replace('/dd+/', 'dd', $tmp);
	$tmp = preg_replace('/ee+/', 'ee', $tmp);
	$tmp = preg_replace('/ff+/', 'ff', $tmp);
	$tmp = preg_replace('/gg+/', 'gg', $tmp);
	$tmp = preg_replace('/hh+/', 'hh', $tmp);
	$tmp = preg_replace('/ii+/', 'ii', $tmp);
	$tmp = preg_replace('/jj+/', 'jj', $tmp);
	$tmp = preg_replace('/kk+/', 'kk', $tmp);
	$tmp = preg_replace('/ll+/', 'll', $tmp);
	$tmp = preg_replace('/mm+/', 'mm', $tmp);
	$tmp = preg_replace('/nn+/', 'nn', $tmp);
	$tmp = preg_replace('/oo+/', 'oo', $tmp);
	$tmp = preg_replace('/pp+/', 'pp', $tmp);
	$tmp = preg_replace('/qq+/', 'qq', $tmp);
	$tmp = preg_replace('/rr+/', 'rr', $tmp);
	$tmp = preg_replace('/ss+/', 'ss', $tmp);
	$tmp = preg_replace('/tt+/', 'tt', $tmp);
	$tmp = preg_replace('/uu+/', 'uu', $tmp);
	$tmp = preg_replace('/vv+/', 'vv', $tmp);
	$tmp = preg_replace('/ww+/', 'ww', $tmp);
	$tmp = preg_replace('/xx+/', 'xx', $tmp);
	$tmp = preg_replace('/yy+/', 'yy', $tmp);
	$tmp = preg_replace('/zz+/', 'zz', $tmp);
	
	//trim to remove white space	
	$tmp = trim($tmp);
	
	//debug
	runDebug("",3,"cleanInput","<br>Array Name = Not in array<br>Cleaned input string<br>Was = $dtmp<br>Is = $tmp");
	
	//return the string
	return $tmp;
}


//-----------------------------------------------------------------------------------------------
//replaceUndefined($originalinput,$bot)
//we have the answer to send to the user... but it included 'undefined' bot/user variables so we need to replace these before outputting
//-----------------------------------------------------------------------------------------------
function replaceUndefined($originalinput,$bot)
{
	//db globals
	global $dbconn,$dbn,$debugmode;
	//debug
	$tmp = $originalinput;
	//initialise
	$template="";
	
	//get undefined defaults from the db
	$sql = "SELECT * FROM `$dbn`.`undefined_defaults` where `bot` = ".$bot;

	if(($debugmode==1)||($debugmode==2))
	{
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
	}
	else
	{
		$result = mysql_query($sql,$dbconn);
	}	
	
	if($result)
	{
		if(mysql_num_rows($result)>0)
		{
			while($row=mysql_fetch_array($result))//loop thru results
			{
				$pattern = '/\b'.$row['pattern'].'\b/';
				$replacement = $row['replacement'];
				$originalinput = preg_replace($pattern, $replacement, $originalinput); //do replacement
			}
		}
	}	
	//debug
	runDebug("",3,"replaceUndefined","<br>Array Name = Not in array<br>Replaced undefined<br>Was = $tmp<br>Is = $originalinput");
	
	//return input
	return $originalinput;
}
function replaceBotVars($response_Array)
{
	//db globals
	global $dbconn,$dbn,$debugmode,$thisbot;
	//for debug 

	//just set this tmp for the debug..
	$tmp = $response_Array['matchtemplate'];
	$matchedOutput = $response_Array['matchtemplate'];

	if(strpos($matchedOutput,"bot name=")!==FALSE)
	{
		preg_match_all('/\"(.*?)\"/',$matchedOutput,$botVar);
		$allbotVar =implode(',',$botVar[0]);
		
		
		
		$sql = "SELECT * FROM `$dbn`.`botpersonality` WHERE `name` IN ($allbotVar) AND `bot` = $thisbot";

		if(($debugmode==1)||($debugmode==2))
		{
			$result = mysql_query($sql,$dbconn)or die(mysql_error());
		}
		else
		{
			$result = mysql_query($sql,$dbconn);
		}	
	
		if($result)
		{
			if(mysql_num_rows($result)>0)
			{
				while($row=mysql_fetch_array($result))//loop thru results
				{
					$value = $row['value'];
					$name = $row['name'];
										
					$name_a = "<bot name=\"$name\">";
					$name_b = "<bot name=\"$name\"\/>";
					$name_c = "<bot name=\"$name\" \/>";
					
					$matchedOutput = preg_replace('/'.$name_a.'|'.$name_b.'|'.$name_c.'/i',$value,$matchedOutput);
					
				}
			}
		}
	}
	//debug
	runDebug("",3,"replaceBotVars","<br>Array Name = Not in array<br>Bot Var-ed<br>Was = ".htmlentities($tmp,ENT_QUOTES,"UTF-8")."<br>Is = ".htmlentities($matchedOutput,ENT_QUOTES,"UTF-8"));
	//return
	return $matchedOutput;
}


//-----------------------------------------------------------------------------------------------
//spellcheck($originalinput)
//spellcheck any inputs before we check for any AIML pattern matches
//-----------------------------------------------------------------------------------------------
function spellcheck($originalinput)
{
	//db globals
	global $dbconn,$dbn,$debugmode;
	//for debug 
	$tmp = mysql_escape_string($originalinput);
	//initialise
	$template="";
	

	
	//get all words to spellcheck
	$sql = "SELECT * FROM `$dbn`.`spellcheck`";

	if(($debugmode==1)||($debugmode==2))
	{
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
	}
	else
	{
		$result = mysql_query($sql,$dbconn);
	}	
	
	if($result)
	{
		if(mysql_num_rows($result)>0)
		{
			while($row=mysql_fetch_array($result))//loop thru results
			{
				$ms = str_replace("'","",$row['missspelling']);
				$ms = str_replace("/","\/",$ms);
				
				$oi = str_replace("'","",$originalinput);
				$pattern = '/\b'.$ms.'\b/';
				$replacement = $row['correction'];
				$originalinput = preg_replace($pattern, $replacement, $oi); //do replacement
			}
		}
	}
	
	//debug
	runDebug("",3,"replaceUndefined","<br>Array Name = Not in array<br>Spellchecked<br>Was = $tmp<br>Is = $originalinput");
	//return
	return $originalinput;
}

//-----------------------------------------------------------------------------------------------
//resetResponses($response_Array)
//when the user types something new we want to disregard/reset the following items from the bots memory
//-----------------------------------------------------------------------------------------------
function resetResponses($response_Array)
{
	unset($response_Array['thispart']);
	unset($response_Array['partnumber']);
	unset($response_Array['matchtemplate']);
	unset($response_Array['matchpattern']);
	unset($response_Array['matchthatpattern']);
	unset($response_Array['orignalinput']);
	unset($response_Array['responseparts']);
	unset($response_Array['totalparts']);
	unset($response_Array['currentposition']);
	unset($response_Array['li']);
	unset($response_Array['randomanswer']);
	unset($response_Array['randomchoice']);
	unset($response_Array['answer']);
	unset($response_Array['waiting2set']);
	unset($response_Array['htmltemplate']);	
	unset($response_Array[0]);	
	unset($response_Array['masterlook']);
	unset($response_Array['biganswer']);
	unset($response_Array['usersaid']);
	unset($response_Array['match_wildcards']);
	
	$response_Array['rname']="Array#0";
	
	//debug
	runDebug($response_Array,3,"resetResponses","<br>Array Name = Array#0<br>Resetting response items");
	//return
	return $response_Array;
}

//-----------------------------------------------------------------------------------------------
//buildValuesFromArray($response_Array)
//when the user types something new we want to save some items from the conversation
//this function writes the data we want to save into the form
//-----------------------------------------------------------------------------------------------
function buildValuesFromArray($response_Array)
{
    if(count($response_Array)<=0) //there are no array values to save! so just reset
	{
		$buildformvals="<input type=\"hidden\" name=\"response_Array[]\" id=\"response_Array[]\" value=\"\">";
	}
	else
	{
		$buildformvals=""; //intialise
		$response_Array = resetResponses($response_Array); //reset the items we dont want to carry over
		foreach($response_Array as $index=>$value) //loop thru everything else
		{
			//if its a 2D array then loop thru and set those
			if(is_array($value))
			{
				foreach($value as $dindex => $dvalue)
				{
				
					$dvalue = preg_replace('/(\\\\(?:\\\\[^:\\s?*"<>|]+)+)/','\\',$dvalue);
					
					$buildformvals.="<input type=\"hidden\" name=\"response_Array[$index][$dindex]\" id=\"response_Array[$index][$dindex]\" value=\"$dvalue\">";
				}
			}
			else //its just a 1D array so we can set that easily
			{
				$value = preg_replace('/(\\\\(?:\\\\[^:\\s?*"<>|]+)+)/','\\',$value);
				$buildformvals.="<input type=\"hidden\" name=\"response_Array[$index]\" id=\"response_Array[$index]\" value=\"$value\">";
				//quick clean
				
			}
		}
	}
	//debug
	runDebug($response_Array,3,"buildValuesFromArray","<br>Array Name = Array#0<br>Building form items");
	//return the form parts
    return $buildformvals;
}


//-----------------------------------------------------------------------------------------------
//formchat($response_Array)
//build form to display
//-----------------------------------------------------------------------------------------------
/*
function formchat($response_Array)
{
	//get the values from the array we want to use in the conversation
	$buildformvals = buildValuesFromArray($response_Array);
	//build form
	$form = " 
  &nbsp;&nbsp;&nbsp;&nbsp;<form name=\"chat\" id=\"chat\" method=\"post\" action=\"\">
			<a name=\"chat\">&nbsp;</a>
			<input type=\"text\" name=\"chat\" id=\"chat\" size=\"35\" maxlength=\"50\">
			<input type=\"hidden\" name=\"action\" id=\"action\" value=\"checkresponse\">
			".$buildformvals."
			<input type=\"submit\" name=\"submit\" value=\"SAY\">
			</form>";
	//debug
	runDebug($response_Array,3,"formchat","<br>Array Name = Array#0<br>Display form");
	//display form		
	return $form;
}
*/
function formchat($response_Array)
{
	//get the values from the array we want to use in the conversation
	$buildformvals = buildValuesFromArray($response_Array);
	//build form
	$form = " 
  &nbsp;&nbsp;&nbsp;&nbsp;<form name=\"chat\" id=\"chat\" method=\"post\" action=\"\">
			<a name=\"chat\">&nbsp;</a>
			<input type=\"text\" name=\"chat\" id=\"chat\" size=\"35\" maxlength=\"50\">
			<input type=\"hidden\" name=\"action\" id=\"action\" value=\"checkresponse\">
			".$buildformvals."
			<input type=\"submit\" name=\"submit\" value=\"SAY\">
			</form>";
	//debug
	runDebug($response_Array,3,"formchat","<br>Array Name = Array#0<br>Display form");
	//display form		
	return $buildformvals;
}
//-----------------------------------------------------------------------------------------------
//cleanThatPattern($that)
//the sql wants to match a nice clean no punctuation uppercase version of the last thing the bot said
//when finding the next thing it wants to say.......
//this funciton will clean the that pattern ready for the AIML pattern match
//-----------------------------------------------------------------------------------------------
function cleanThatPattern($that) //passing the last thing the bot said
{
	//for debug
	$tmp = $that;
	//break up the last thing so we only use the last line (if the last thing said was on multiple lines)
	$tmpLastLine = preg_split("/<br(\s|\/)>/i",$that);
	if($tmpLastLine)
	{
		$ct = count($tmpLastLine);
		$ct--;
		$that = $tmpLastLine[$ct];
	}
	
	//convert to uppercase
	$that = strtoupper($that);
	$that = formatchinese($that);
	//replace any hyphens with spaces
	$that = str_replace("-"," ",$that);
	//keep only alphanumeric chars
	//$that = preg_replace('/[^a-z0-9\s]/i','',$that);
	$that = preg_replace('/\s+/',' ',$that);
	
	//debug
	runDebug("",3,"cleanThatPattern","<br>Array Name = Not in array<br>Cleaned that pattern<br>Was = $tmp<br>Is = $that");
	
	//returned the clean that
	return $that;
}

//-----------------------------------------------------------------------------------------------
//function findPersonalMatch($response_Array)
//has the user taught the bot a new repsose
//check new response table first
//-----------------------------------------------------------------------------------------------
function findPersonalMatch($response_Array)
{

	//db globals
	global $dbconn,$dbn,$debugmode;
	//initialise
	$template="";
	//sql to run
	$sql = "SELECT * FROM `$dbn`.`aiml_userdefined` where `pattern` = '".$response_Array['lookingfor']."' AND `userid` = '".$response_Array['userid']."' AND `botid` = '".$response_Array['bot']."' ORDER BY `id` DESC";
	if(($debugmode==1)||($debugmode==2))
	{
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
	}
	else
	{
		$result = mysql_query($sql,$dbconn);
	}	
	//debug
	runDebug("",3,"findMatch","<br>Array Name = Not in array<br>Checking<br><pre>".$sql."</pre>");
	
	//if found a result that isnt 1!=1
	if( ($result) && (mysql_num_rows($result)>0) )
	{
		$answer = mysql_fetch_array($result);
	}
	else
	{
		$answer['template']="undefined-template";
	}
	
	return $answer;
}

//-----------------------------------------------------------------------------------------------
//checkresponse($response_Array)
//the main control function for bot.....
//-----------------------------------------------------------------------------------------------
function checkresponse($response_Array)
{
	global $thisbot;
	if($response_Array['who'] =="human")
	{
		$response_Array['rname']="Array#0";
	}
	/*
	$answer = findPersonalMatch($response_Array);
	
	if($answer['template']!="undefined-template")//there was no answer so call this function again.....
	{
		$response_Array['htmltemplate'] = htmlentities($answer['template']);
		$response_Array['matchtemplate'] = "<botresponse>".$answer['template']."</botresponse>";
		$response_Array['matchpattern'] = $answer['pattern'];	
		if(isset($answer['thatpattern']))
		{
			$response_Array['matchthatpattern'] = $answer['thatpattern'];
		}
		else
		{
			$response_Array['matchthatpattern'] = "";
		}
	}
	else
	{
		//DELETE COME BACK HERE
		$response_Array = findMatch($response_Array);
	}*/
	$response_Array = findMatch($response_Array);
	//set the bot	
	$response_Array['bot']=$thisbot;	

	//move through all parts of the matched response.....
	$response_Array = walkThruTemplate($response_Array);
	//we have the parts of the answer so mash them together
	$response_Array = getAnswer($response_Array);
	
	//return for next chat
	return $response_Array;
}
//-----------------------------------------------------------------------------------------------
//function findMatch($response_Array)
//run the sql then score the results
//the row with the highest score is the correct answer
//-----------------------------------------------------------------------------------------------
function findMatch($response_Array)
{
	
	global $dbconn,$dbn,$debugmode;
	$input = trim($response_Array['lookingfor']);
	
	
	$words = explode(' ', $input); 
	$lastInput = trim($words[count($words) - 1]);//, '.?![](){}*'); 
	$firstInput = trim($words[0]);
	
	
	if(isset($response_Array['topic'][0]))
	{
		$storedtopic = trim(strtoupper($response_Array['topic'][0]));
	}
	else
	{
		$storedtopic = "";
	}
	
	
	if((isset($response_Array['that'][0]))&&($response_Array['that'][0]!=""))
	{
		$thatPattern = strtoupper(trim(cleanThatPattern(urldecode($response_Array['that'][0]))));
		$storedthatpattern = $thatPattern;
		
		$words = explode(' ', $thatPattern); 
		if(count($words)<=1)
		{
			$lastThatPattern = $thatPattern;
			$firstThatPattern = $thatPattern;
		}
		else
		{
			$lastThatPattern = trim($words[count($words) - 1]);//, '.?![](){}*'); 
			$firstThatPattern = trim($words[0]);
		}
		
		$thatPatternSQL = "OR ((`thatpattern` LIKE '$firstThatPattern %') AND (`thatpattern` LIKE '% $lastThatPattern'))
	OR ((`thatpattern` LIKE '$firstThatPattern %') AND (`thatpattern` LIKE '% *'))
	OR ((`thatpattern` LIKE '$firstThatPattern %') AND (`thatpattern` LIKE '% _'))
	OR ((`thatpattern` LIKE '* %') AND (`thatpattern` LIKE '% $lastThatPattern'))
	OR ((`thatpattern` LIKE '_ %') AND (`thatpattern` LIKE '% $lastThatPattern'))
	OR ((`thatpattern` LIKE '* %') AND (`thatpattern` LIKE '% _'))
	OR ((`thatpattern` LIKE '_ %') AND (`thatpattern` LIKE '% *'))";
	}
	else
	{
		$thatPattern = "";
		$lastThatPattern = "";
		$firstThatPattern = "";
		$thatPatternSQL = "";
		$storedthatpattern ="";
	}


	$sql = "SELECT * FROM `$dbn`.`aiml` WHERE
( ((`pattern` = '_') 
OR (`pattern` = '*') 
OR (`pattern` = '$input') 
OR ((`pattern` LIKE '$firstInput %') AND (`pattern` LIKE '% $lastInput'))
OR ((`pattern` LIKE '$firstInput %') AND (`pattern` LIKE '% *'))
OR ((`pattern` LIKE '$firstInput %') AND (`pattern` LIKE '% _'))
OR ((`pattern` LIKE '* %') AND (`pattern` LIKE '% $lastInput'))
OR ((`pattern` LIKE '_ %') AND (`pattern` LIKE '% $lastInput'))
OR ((`pattern` LIKE '* %') AND (`pattern` LIKE '% _'))
OR ((`pattern` LIKE '_ %') AND (`pattern` LIKE '% *'))
OR (`pattern` = 'RANDOM PICKUP LINE' )) )
AND
(
	(`thatpattern` = '_') 
	OR (`thatpattern` = '*')
	OR (`thatpattern` = '') 
	OR (`thatpattern` = '$thatPattern') 
	$thatPatternSQL 
)
AND 
( 
	(`topic`='')
   	OR (`topic`='".$storedtopic."') 
)";	 
	
	$debugsql = "<Br><Br><Br><Br><Br><Br><hr><pre>$sql</pre>";
	
	runDebug($response_Array, 7 ,"main sql: ",$debugsql);
	
			
	if(($debugmode==1)||($debugmode==2))
	{
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
	}
	else
	{
		$result = mysql_query($sql,$dbconn);
	}	
	
	
	//debug
	runDebug("",3,"findNewMatch","<br>Array Name = Not in array<br>Checking<br><pre>".$sql."</pre>");
	
	if(($result)&&(mysql_num_rows($result)>0))
	{
		$i = -1;
		
			
	
		while($row=mysql_fetch_array($result)) //loop through results
		{
			
			
			//due to the sql above some of the patterns may not match the original input so lets sort them here
			$pattern = trim(stripslashes($row['pattern']));
			
			
			if(($pattern!="RANDOM PICKUP LINE")&&($pattern!="_")&&($pattern!="*"))
			{
					$botres = $pattern;
			
					$botres = str_replace("*","(.*)",$botres);
					$botres = str_replace("_","(.*)",$botres);
					$botres = trim($botres);
					$botres = str_replace(" ","\s",$botres);
					
					
					
					
					if(preg_match('/^'.$botres.'/i',$input,$found))
					{
						$startcountparts=1;	
						$countparts=1;
						$patternparts[0]=$pattern; //do i need this	
						$i++;
						$ansArr[$i]['#'] = $i;
						$ansArr[$i]['id'] = $row['id'];
						$ansArr[$i]['pattern'] = $row['pattern'];
						$ansArr[$i]['thatpattern'] = $row['thatpattern'];
						$ansArr[$i]['topic'] = $row['topic'];
						$ansArr[$i]['template'] = $row['template'];
						
						
					}
			}
			else
			{
					$i++;
					$patternparts[0]=$pattern;
					$startcountparts=1;
					$countparts=1;
					
					$ansArr[]['#'] = $i;
					$ansArr[$i]['id'] = $row['id'];
					$ansArr[$i]['pattern'] = $row['pattern'];
					$ansArr[$i]['thatpattern'] = $row['thatpattern'];
					$ansArr[$i]['topic'] = $row['topic'];
					$ansArr[$i]['template'] = $row['template'];
					
				
					
			} 
		}

		//echo "<pre>ALL: <br/>";
		//print_r($ansArr);
		//echo "</pre>";



		foreach($ansArr as $item => $set)
		{
			$points = 0;
			foreach($set as $tag => $bit)
			{
				
				if($tag == "pattern")
				{
					$chkpat = $bit;
					//if($input == $bit) //exact match
					if(preg_match('/\b('.$input.')\b/i', $bit,$found))
							
					{
						$points += 11;
						//echo "<h3>$input == $bit</h3>";
					}
					//echo "<h3>$input == $bit</h3>";
					if($bit == "RANDOM PICKUP LINE")
					{
						$points = 7;
						break;
					}
					else
					{
						$p = preg_split("/\s/",trim($bit));
						
						foreach($p as $w => $word)
						{
							if($word == "_")
							{
								$points += 100;
								//echo "<h2>100 $bit</h2>";
							}
							elseif($word == "*")
							{
								$points += 5;
							}
							else
							{	//improved matching score
								//if the word is any of the common words below then we will give it a lower score
								//over less common words
								//this is to give a better rating to a reply that is 
								//i am going to teach piano (higher schore)
								//over
								//i am going to do it (lower score)
								
								//echo "<br/>word = ".$word;
								if(preg_match('/[^A-Za-z0-9]/i', $word,$found)) //matching on symbol like = or +
								{
									$points += 11;
								}								
								elseif(preg_match('/\b(x|the|of|and|a|to|in|is|you|that|it|he|was|for|on|as|with|his|they|I|at|be|this|have|from|or|one|had|by|word|but|not|what|all|were|we|when|your|can|said|there|use|an|each|which|she|do|how|their|if|will|up|other|about|out|many|then|them|these|so|some|her|would|make|like|him|into|time|has|look|two|more|write|go|see|number|no|way|could|people|my|than|first|water|been|call|who|oil|its|now|find|long|down|day|did|get|come|made|may|part)\b/i', $word,$found))
								{
									$points += 11;
								}
								else
								{
									$points += 30;
								}
							}
						}
					}	
				}
				
				
				
				if($tag == "topic")
				{
					if($bit!="")
					{
						
						if(trim(strtoupper($bit))==trim(strtoupper($storedtopic)))
						{
							$points += 100;
						}
						else
						{
							$points -= 100;
						}			
					}
				}
				if($tag == "#")
				{
					$i = $bit;
				}
				
				if($tag == "thatpattern")
				{
					if($bit!="")
					{
						
						if($bit==$storedthatpattern)
						{
							$points += 90;
						}
						elseif( (strpos($bit,"*")!==FALSE)&&(strpos($bit,"_")!==FALSE))//if the that pattern has no stars or underscores
						{
							$points -= 1000;
						}
						elseif($bit!=$storedthatpattern)
						{
							
							//STRING REPLACE the _ or the * with (.*) then preg match
							$bit = str_replace("_","(.*)",$bit);
							$bit = str_replace("*","(.*)",$bit);
							
							if(preg_match('/'.$bit.'/is',$storedthatpattern,$found))
							{
								$points += 85;
							}
							else
							{
								$points -= 1000;
							}
						} 
							
						
					}
					
				}
			}
			$score[$i]=$points;
			
			if($ansArr[$i]['pattern'] == 'RANDOM PICKUP LINE')
			{
				$botoutput="RANDOM LINE";
			}
			else
			{
				$botoutput=htmlentities($ansArr[$i]['template'],ENT_QUOTES,"UTF-8");
			}
			
			$debug[$i][$points][$ansArr[$i]['pattern']]=$botoutput."###".$ansArr[$i]['thatpattern']."###".$ansArr[$i]['topic'];
		}
		arsort($score);
		
		//if there is more than one top scoring answer its a dead choice between the two randoms
		$topscore = 0;
		foreach($score as $recid => $thisscore)
		{
			
			if($thisscore == $topscore)
			{
				$topRecords[]=$recid;
			}
			elseif($thisscore > $topscore)
			{
				$topRecords=array();
				$topRecords[]=$recid;
				$topscore = $thisscore;
			}
		}
		
		$top = $topRecords[array_rand($topRecords)];
	}
	//square bear hack to reset a topic once the game has ended
	//this is needed to make the squarebear aiml files work
	if((strtolower($input)=="quitgame")||(strtolower($input)=="quit")||(strtolower($input)=="quit game"))
	{
			$alltemplate ="<think><set name=\"topic\">QUITTING GAME</set></think>".$ansArr[$top]['template'];
	}
	else
	{
			$alltemplate = $ansArr[$top]['template'];
	}	

		$debugtable = "<table border=1><tr><td>ID</td><td>SCORE</td><td>INput</td><td>Response</td><td>That Pattern</td><td>Topic</td></tr>";
		
		foreach($debug as $a => $b)
			{
				
				$debugtable .= "<tr><td>$a</td>";
				
				
				foreach($b as $c => $d)
				{
					
					$debugtable .= "<td>$c</td>";
					
					foreach($d as $e => $f)
					{
						$g = explode("###",$f);
						
						
						$debugtable .= "<td>$e</td>";
						$debugtable .= "<td>".$g[0]."&nbsp;</td>";
						$debugtable .= "<td>".$g[1]."&nbsp;</td>";
						$debugtable .= "<td>".$g[2]."&nbsp;</td></tr>";
					}
				}				
			}
		
		$debugtable .= "</table>";
		
		//echo $debugtable;

	//echo "<h4>top:$top</h4>";
	
	runDebug($response_Array, 7 ,"find match: ",$debugtable);
	runDebug($response_Array, 7 ,"top match: ",$top);
	
	

	$response_Array['htmltemplate'] = htmlentities($alltemplate,ENT_QUOTES,"UTF-8");
	$response_Array['matchtemplate'] = "<botresponse>".$alltemplate."</botresponse>";
	$response_Array['matchpattern'] = $ansArr[$top]['pattern'];	
	$response_Array['matchthatpattern'] = $ansArr[$top]['thatpattern'];	
	return $response_Array;
}
?>
