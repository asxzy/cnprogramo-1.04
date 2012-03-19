<?php
//-----------------------------------------------------------------------------------------------
//Program-o Version 1.0.4
//PHP MYSQL AIML interpreter
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
//getsetvars.php
//contains functions for getting and setting variables used by the bot to generate sensible responses
//-----------------------------------------------------------------------------------------------

//--------------------------------------------------------------
//function setGossip($response_Array)
//this function will only excute correctly if the name and gender of the user has been determined...
//--------------------------------------------------------------
function setGossip($response_Array)
{
	//get current position
	$i = getCurrentPosition($response_Array);
	
	//check if name and gender are present
	if((isset($response_Array['name'][0]))&&($response_Array['name'][0]!="")&&(isset($response_Array['gender'][0]))&&($response_Array['gender'][0]!=""))
	{
		// initialise
		global $dbconn,$debugmode,$dbn;
		$buildGossip = "";
		$tmp = "";
		
		//get start position
		$start = findStartElement($i,"gossip",$response_Array);
		
		//build entire response
		for($s=$start+1;$s<$i;$s++)
		{
			$tmp .=  $response_Array['responseparts'][$s]." ";
		}
		
		//set name and gender		
		$gender = $response_Array['gender'][0];
		$name=$response_Array['name'][0];
		
		//transform
		if($gender=="she")
		{
			$tmp = preg_replace('/\bme\b/i',"her",$tmp);
			$tmp = preg_replace('/\bmine\b/i',"hers",$tmp);
		}
		if($gender=="he")
		{
			$tmp = preg_replace('/\bme\b/i',"his",$tmp);
			$tmp = preg_replace('/\bmine\b/i',"his",$tmp);
		}		
		
		$tmp = str_replace("I ","$name said $gender ",$tmp);
		$tmp = str_replace("YOU ","$name said $gender ",$tmp);
		$tmp = preg_replace('/\bI\b/i',$gender,$tmp);
		$tmp = preg_replace('/\bam\b/i','is',$tmp);
		$tmp = preg_replace('/\byou\b/i','I',$tmp);
		$tmp = preg_replace('/\s+/',' ',$tmp);
		
		//final clean...
		$template = UCFirst(trim($tmp));
		
		//create sql and insert
		$aiml = "<category><pattern>GOSSIP</pattern><template>$template</template></category>";
		$sql = "INSERT INTO `$dbn`.`aiml` (`id`,`aiml`,`pattern`,`template`,`topic`,`filename`) VALUES (null,'$aiml','GOSSIP','$template','gossip','usergossip')";
	
		//if in debug mode if die display error
		if(($debugmode==1)||($debugmode==2))
		{
			$result = mysql_query($sql,$dbconn)or die(mysql_error());
		}
		else //silent insert
		{
			$result = mysql_query($sql,$dbconn);
		}
	}
	
	//return
	return $response_Array;
}

//--------------------------------------------------------------
//function getIndex($thispart)
//--------------------------------------------------------------
function getIndex($thispart)
{
	$thispart = strtolower(str_replace("'","\"",$thispart));
	$indexes = preg_match("/\"(.*?)\"/is",$thispart);
	
	if(strpos($thispart,",")!==false) //shouldnt exist as i have replace all 2,1 and 2,* with just 2 and 2
	{
		$indexArr = explode(",",$thispart);
	}
	else
	{
		$indexArr[0]=$indexes-1;
	}
	if($indexArr[0]==-1)
	{
		$indexArr[0]=0;
	}
	//debug
	runDebug("",3,"getIndex","<br>Array Name = not in array<br>HUH");
	return $indexArr;
}

function getThat($response_Array,$indexArr)
{
	$i = getCurrentPosition($response_Array);
	$elements = count($indexArr);
	if(!isset($indexArr[0]))
	{
		$that =  "nothing";
	}
	elseif($elements==1)
	{
		$that =  urldecode($response_Array['that'][$indexArr[0]]);
	}
	else
	{
		$that =  urldecode($response_Array['that'][$indexArr[0]][$indexArr[1]]);
	}
	$response_Array['responseparts'][$i]=$that;
	//debug
	runDebug($response_Array,3,"getThat","<br>Array Name = ".$response_Array['rname']."<br>That = $that");
	return $response_Array;
}

function getInput($response_Array,$indexArr)
{
	$i = getCurrentPosition($response_Array);
	$elements = count($indexArr);
	if($elements==0)
	{
		$input =  "undefined-input";
	}
	else
	{
		$input =  urldecode($response_Array['input'][$indexArr[0]]);
	}
	$response_Array['responseparts'][$i]=$input;
	//debug
	runDebug($response_Array,3,"getInput","<br>Array Name = ".$response_Array['rname']."<br>input = $input");
	return $response_Array;
}

function getGender($response_Array)
{
	$i = getCurrentPosition($response_Array);
	if(isset($response_Array['gender']))
	{
		$gender = $response_Array['gender'];
	}
	else
	{
		$gender = "undefined-gender";
	}
	$response_Array['responseparts'][$i]=$gender;
	
	//debug
	runDebug($response_Array,3,"getGender","<br>Array Name = ".$response_Array['rname']."<br>gender = $gender");

	return $response_Array;
}

//-------------------------------------------------------------
//function getAnswer($response_Array)
//put togather all the evaluated parts to form the sentance to display
//as a reponse to the user
//-------------------------------------------------------------
function getAnswer($response_Array)
{
	$tmp ="";
	if((!isset($response_Array['randomchoice'])) || ($response_Array['randomchoice']==0))
	{
		$tmpArr = $response_Array['responseparts'];
		foreach($tmpArr as $index => $part)
		{
			 // $part = strtolower($part);
			  if((strpos($part,"parsed_tag_open")===false)&&(strpos($part,"parsed_tag_closed")===false))
  			  {
			  	$tmp .= $part." ";
			  }
		}	
	}
	else
	{
		$max = $response_Array['randomchoice']-1; 
		srand((double)microtime()*1000000);
		$seed = rand(1000000,9999999);
		srand((double)microtime()*$seed);
		$rand = rand (0,$max); 
		
		$tmp = $response_Array['randomanswer'][$rand];
	}
	$tmp = cleanWords($tmp);
	$tmp = replaceUndefined($tmp,$response_Array['bot']);

	if($response_Array['who'] == "human")
	{
		$tmp = stripslashes($tmp);
		$tmpLastLine = preg_split("/<br(\s|\/)>/i",$tmp);
		if($tmpLastLine)
		{
			$ct = count($tmpLastLine);
			$ct--;
			$lastline_tmp = trim($tmpLastLine[$ct]);
		}
		else
		{
			$lastline_tmp = $tmp;
		}
		//add to that stack if this is user input not recursion srai
		$response_Array = frontOfStack($response_Array,urlencode($lastline_tmp),"that");
		//add to input stack
	}
	$response_Array['answer'] = cleanWords(stripslashes($tmp));
	return $response_Array;
}

//-------------------------------------------------------------
//function unsetAll($response_Array,$start,$end)
//unset everytihng between 2 points in the responseparts array
//-------------------------------------------------------------
function unsetAll($response_Array,$start,$end)
{
	//set to empty
	for($i=$start;$i<=$end;$i++)
	{
		$response_Array['responseparts'][$i]="";
	}
	//debug
	runDebug($response_Array,3,"unsetAll","<br>Array Name = ".$response_Array['rname']."<br>unsetting $start to $end");
	//return
	return $response_Array;
}
//-------------------------------------------------------------
//replace the getstar tag with the value of the star
//-------------------------------------------------------------
function getStar($response_Array)
{
	//get current position
	$i = getCurrentPosition($response_Array);
	//set current tag..
	$thispart = strtolower($response_Array['responseparts'][$i]);
	if(strpos($thispart,"index")!==FALSE)
	{
		$starPosition = extractVar($thispart);
		//subtract one as our array starts at zero but the aiml array starts at 1
		$starPosition = $starPosition-1;
		if($starPosition<0)
		{
			$starPosition=0;
		}
	}
	else //else set to start pos 0
	{
		$starPosition = 0;
	}
	if(isset($response_Array['star'][$starPosition]))
	{
		$response_Array['responseparts'][$i]=$response_Array['star'][$starPosition];
	//debug	
		runDebug($response_Array,3,"getStar","<br>Array Name = ".$response_Array['rname']."<br>Position:$starPosition<br>Value:".$response_Array['star'][$starPosition]);
	}
	else
	{
		$response_Array['responseparts'][$i] = "it";
		runDebug($response_Array,3,"getStar","<br>Array Name = ".$response_Array['rname']."<br>Position:$starPosition<br>Value:Not Found");
	}
	//return
	return $response_Array;
}

//--------------------------------------------------------------
//function getConversationVariable
//gets a variable that has been set in the conversation and replaces it in the output
//--------------------------------------------------------------
function getConversationVariable($response_Array,$extractedTag)
{
	//get current position
	$curPos = getCurrentPosition($response_Array);
	//is there a 2-d value
	if( (isset($response_Array[$extractedTag])) && (is_array($response_Array[$extractedTag])) )
	{
		$value = $response_Array[$extractedTag][0];
	}
	elseif((isset($response_Array[$extractedTag])) && ($response_Array[$extractedTag]!="") )//is there a 1d value
	{
		$value = $response_Array[$extractedTag];
	}
	else
	{
		$value = "your ".$extractedTag; //the value has not been set yet
	}
	$response_Array['responseparts'][$curPos]=$value;
	//debug
	runDebug($response_Array,3,"getConversationVariable","<br>Array Name = ".$response_Array['rname']."<br>TAG:".$extractedTag ."<br>VAL:".$value);
	//return
	return $response_Array;
}
//-----------------------------------------------------------------
//not recieved any var in extracttag... becuse this function is called when the close set tag is opened
//-----------------------------------------------------------------
function setConversationVariable($response_Array)
{
	//get the current position....
	$curPos = getCurrentPosition($response_Array);
	//get the previous opening set tag
	$start = findStartElement($curPos,"set name",$response_Array);
	//get everything between the open and close tag...
	$item = "";
	for($j=$start+1;$j<$curPos;$j++) //(start is set to $start+1) the start tag is not needed we need what is BETWEEN the start and end
	{
		$item.=$response_Array['responseparts'][$j]." ";
	}	
	//now find the value we are setting e.g. <set name="xxx"> this will return xxx
	$extractedTag = extractVar($response_Array['responseparts'][$start]);
	
	
	//set the item.....
	//$response_Array[$extractedTag][]=$item; //TODO is everything a 2d array ? does it still work???
	//it was the stuck on the end but now its on the front of the stack
	$response_Array = frontOfStack($response_Array,$item,$extractedTag);
	
	//echo "<br><b>Now setting</b>: $extractedTag = $item";
	
	//if the extracted tag was it (i.e. set name="it") we have set the it value but then we want to replace the value with the word IT itself
	if($extractedTag=="it")
	{
		//set the word it to the cuurent position
		$response_Array['responseparts'][$curPos] = "it";

		//unset everything from the first open set to the position before the it
		$response_Array = unsetAll($response_Array,$start,$curPos-1);
	}
	else
	{
		//echo "HERE";
		//we jusyt want to removed tags... not the inside value so dont give start and end.. must remove one element
		$response_Array = unsetAll($response_Array,$curPos,$curPos);
		$response_Array = unsetAll($response_Array,$start,$start);
	}
	//debug	
	runDebug($response_Array,3,"setConversationVariable","<br>Array Name = ".$response_Array['rname']."<br>TAG:".$extractedTag ."<br>VAL:".$item);
	//return array back for more processing
	return $response_Array;
}
//--------------------------------------------------------------
//function getCurrentDate
//rplaces tag request with current date
//--------------------------------------------------------------
function getCurrentDate($response_Array)
{
	//get current position
	$i = getCurrentPosition($response_Array);
	//set
	$response_Array['responseparts'][$i]=date('l jS \of F Y');
	//debug
	runDebug($response_Array,3,"getCurrentDate","<br>Array Name = ".$response_Array['rname']."<br>DATE: ".$response_Array['responseparts'][$i]);
	//return
	return $response_Array;
}
//--------------------------------------------------------------
//function getIP
//rplaces tag request with users IP address
//--------------------------------------------------------------
function getIP($response_Array)
{
	//get current position	
	$i = getCurrentPosition($response_Array);
	//set ip address
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}
    else
	{
	 	$ip=$_SERVER['REMOTE_ADDR'];
	}
	
	if($ip == "")
	{
		$ip = "127.0.0.1";
	}
	//replace in reply
	$response_Array['responseparts'][$i]=$ip;
	//debug
	runDebug($response_Array,3,"getIP","<br>Array Name = ".$response_Array['rname']."<br>IP: ".$response_Array['responseparts'][$i]);
	//return
	return $response_Array;
}

//--------------------------------------------------------------
//replace the tag with the value
//if we cant find the value we replacing with undefined
//--------------------------------------------------------------
function getBotVariable($response_Array,$tagToFind)
{
	global $dbconn,$dbn,$debugmode;
	
	//get the current position 
	$curPos = getCurrentPosition($response_Array);
	$thispart = $response_Array['responseparts'][$curPos];
	$thisbot = $response_Array['bot'];

	//run sql
	$sql = "select * from `$dbn`.`botpersonality` where lcase(`name`) = '".mysql_escape_string(strtolower(trim($tagToFind)))."' and `bot` = $thisbot";

	
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
		$value = $row['value'];
	}
	else
	{	
		$value = "my ".$tagToFind;
	}

	//replace
	$response_Array['responseparts'][$curPos]=$value;
	//debug
	runDebug($response_Array,3,"getBotVariable","<br>Array Name = ".$response_Array['rname']."<br>PART: $thispart<br>VAR: $value<br>SQL: <pre>$sql</pre>");
	//return
	return $response_Array;
}
function getThatStar($response_Array)
{
	$curPos = getCurrentPosition($response_Array);
	$response_Array['responseparts'][$curPos]=$response_Array['thatstar'];
	return $response_Array;
}
function getTopicStar($response_Array)
{
	$curPos = getCurrentPosition($response_Array);
	$response_Array['responseparts'][$curPos]=$response_Array['topicstar'];
	return $response_Array;
}
//-----------------------------------------------------
//funciton setConditionName
//tag_open_condition has been found... extract the condition name and save for later
//-----------------------------------------------------
function setConditionName($response_Array)
{
	$curPos = getCurrentPosition($response_Array);
	$thispart = $response_Array['responseparts'][$curPos];
	$extractedTag = extractVar($thispart);
	$response_Array['conditionName']=$extractedTag;
	runDebug($response_Array,3,"setConditionName","<br>Array Name = ".$response_Array['rname']."<br>condition at pos = $curPos, Found: $extractedTag");
	return $response_Array;
}
//-----------------------------------------------------
//funciton setConditionValue($response_Array)
//extract the condition value and save for later
//-----------------------------------------------------
function setConditionValue($response_Array)
{
	$curPos = getCurrentPosition($response_Array);
	$thispart = $response_Array['responseparts'][$curPos];
	$val = extractVar($thispart);
	$condname = $response_Array['conditionName'];
	$response_Array['condition'][$condname]=$val;
	return $response_Array;
}
?>