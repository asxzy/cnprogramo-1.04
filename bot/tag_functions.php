<?php
//-----------------------------------------------------------------------------------------------
//Program-o Version 1.0.4
//PHP MYSQL AIML interpreter
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
//tag_functions.php
//contains functions used to read and evaluate AIML tags
//-----------------------------------------------------------------------------------------------

//-------------------------------------------------------------
//function closeSystem
//WARNING: assumes the system contains PHP instructions
//wrap the contents of the system tag in php
//-------------------------------------------------------------
function closeSystem($response_Array)
{
	//get current position
	$curPos = getCurrentPosition($response_Array);
	//intialise
	$tmp = "";
	//find start tag
	$start = findStartElement($curPos,"system",$response_Array);
	//find values inside the system tags
	for($j=$start+1;$j<$curPos;$j++)
	{
		$tmp.=$response_Array['responseparts'][$j]." ";
	}
	//debug
	runDebug($response_Array,3,"closeSystem","<br>Array Name = ".$response_Array['rname']."<br>".htmlentities($tmp,ENT_QUOTES,"UTF-8"));
	$cmd = explode(" ", $tmp);
	
	
	$command[0]=$cmd[0];
	
	foreach($cmd as $index => $value)
	{
		if(is_numeric($value))
		{
			if($command[1]!="")
			{
				$command[2] = $value;
			}
			else
			{
				$command[1] = $value;
			}
		}
	}
	
	
	
	
	$output = "";
	switch (strtolower($command[0])) {
		case "add":
			$output .= $command[1] + $command[2];
			break;
		case "subtract":
			$output .= $command[1] - $command[2];
			break;
		case "multiply":
			$output .= $command[1] * $command[2];
			break;
		case "divide":
    		if ($command[2] == 0) {
    			$output = "You can't divide by 0!";
    		} else {
	    		$output .= $command[1] / $command[2];
	    	}
			break;
		case "sqrt":
			$output .= sqrt($command[1]);
			break;
		case "power":
			$output .= pow($command[1], $command[2]);
			break;
		default:
			$output = $command[0];
	}
	//unset all elements involved
	$response_Array = unsetAll($response_Array,$start,$curPos);
	//add value to array in current position then re order all
	$response_Array = resetResponseParts($response_Array,$output,$curPos);
	//debug
	runDebug($response_Array,3,"closeSystem","<br>Array Name = ".$response_Array['rname']."<br>".htmlentities($output,ENT_QUOTES,"UTF-8"));
	//return
	return $response_Array;
}
//--------------------------------------------------------------
//function forget($response_Array)
//forget var(s)
//--------------------------------------------------------------
/*
function forget($response_Array)
{
	global $sessionid,$userid;
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
	return $response_Array;
}
*/
//--------------------------------------------------------------
//function learn($response_Array)
//adds new user - specific responses to the a different table in db
//--------------------------------------------------------------
function learn($response_Array)
{
	global $dbconn,$dbn,$debugmode;
	//get current position
	$start = getCurrentPosition($response_Array); //this is the learn opening tag
	//initialise
	$tmp = "";
	//find start tag....
	$end = findEndElement($start,"learn",$response_Array,0);
	//set what is in between to equal temp
	for($i=$start;$i<$end;$i++)
	{
		$tmp.=$response_Array['responseparts'][$i]." ";
	}
	//----------------------------------------------
	
	
	//there are two types of learn.. on ewhere the user tells the bot its bad response and types in the new one
	//and the one where the the user makes a statement and tell the bot to learn it
	
	//handle the first type
	if(preg_match('/tag_open_get name=\"badanswer-input\"\/|tag_open_get name=\"badanswer-input\"/i',$tmp,$found))
	{
			$type = "badanswer";
			$tmp = preg_replace('/tag_open_get name=\"badanswer-input\"\/|tag_open_get name=\"badanswer-input\"/i',$response_Array['badanswer-input'][0],$tmp);
			$tmp = preg_replace('/tag_open_get name=\"badanswer-newresp\"\/|tag_open_get name=\"badanswer-newresp\"/i',$response_Array['badanswer-newresp'][0],$tmp);	
			$tmp = preg_replace("/\s+/i"," ",$tmp);
	}
	else
	{
		$type = "statement";
	}
	
	//back to handling both		
			preg_match_all('/tag_open_eval tag_open_uppercase(.*?)tag_close_\/uppercase tag_close_\/eval/i',$tmp,$tempArr);
			
			$uppercareThis = strtoupper($tempArr[1][0]);
			$tmp = preg_replace('/tag_open_eval tag_open_uppercase(.*?)tag_close_\/uppercase tag_close_\/eval/i',$uppercareThis,$tmp);	
		
			$tmp = str_replace('tag_open_','<',$tmp);	
			$tmp = str_replace('tag_close_/','</',$tmp);
			$tmp = preg_replace("/\s+/i"," ",$tmp);
			
			$words = explode(" ",$tmp);
			$aiml="";
			foreach($words as $items => $word)
			{
				$word = trim($word);
				if(strpos($word,"<")!==FALSE)
				{
					$word .= ">";
				}
				if( ($word!="<eval>")&&($word!="</eval>")&&($word!="<learn>")&&($word!="</learn>") )
				{
					$aiml.=$word." ";  
				}
			}
		$aiml = urldecode($aiml);
		preg_match_all('/<pattern>(.*?)<\/pattern>/i',$aiml,$pattern);
		preg_match_all('/<template>(.*?)<\/template>/i',$aiml,$template);
	
	
		$pattern = trim($pattern[1][0]);
		$template = trim($template[1][0]);
		
		$pattern = formatchinese(trim($pattern));
		$template = formatchinese(trim($template));
		
		$b = $response_Array['bot'];
		$u = $response_Array['userid'];
		$i = mysql_escape_string($response_Array['usersaid']);
		$a = mysql_escape_string($response_Array['biganswer']);
	
//	}
	

	
	//build query
	$sql = "INSERT INTO `$dbn`.`aiml_userdefined` (`id` ,`aiml` ,`pattern` ,`template` ,`userid` ,`botid` ,`date`) VALUES (NULL , '$aiml', '$pattern', '$template', '$u', '$b',CURRENT_TIMESTAMP)";

	//do query	
	if(($debugmode==1)||($debugmode==2))
	{
		mysql_query($sql,$dbconn)or die(mysql_error());
	}
	else
	{
		mysql_query($sql,$dbconn);
	}	
	
	//now get everything between the first and last learn and remove it from array it.....
	$end = findEndElement($response_Array['totalparts'],"learn",$response_Array,1);
	$response_Array = unsetAll($response_Array,$start,$end);

	runDebug($response_Array,3,"learn","<br>Array Name = ".$response_Array['rname']."<br>learned to say '$template' in response to '$pattern'");
	return $response_Array;
}

//--------------------------------------------------------------
//function buildStarIndex
//building the star index
//adding things that were *'s or _'s in the SQL / Pattern 
//to a star stack
//--------------------------------------------------------------
function buildStarIndex($response_Array,$wildcard)
{
	$matchedinput = $response_Array['matchpattern'];
	$lookingfor = trim($response_Array['lookingfor']);
	$vals = array();
	$tmp="";

	$getstars = trim($matchedinput);
	
	$getstars = str_replace("*","(.*)",$getstars);
	$getstars = str_replace("_","(.*)",$getstars);
	
	$getstars = "/$getstars/i";
	
	$getstars = str_replace(" (","(",$getstars);
	$getstars = str_replace(") ",")",$getstars);
	
	$totalstars = preg_match_all($getstars,$lookingfor,$allstars);

	$count = count($allstars)-1;

	
	for($index=$count;$index>=1;$index--)
	{
	
		if($index>0)
		{
			$star = $allstars[$index][0];
			$star = stripslashes(trim($star));
			
			if($star!="*")
			{
				$response_Array = frontOfStack($response_Array,$star,'star');
			}
		}
	}
	//debug	
	runDebug($response_Array,3,"StarsIndex","<br>Array Name = ".$response_Array['rname']."<br><pre>".$tmp."</pre>");
	//return	
	return $response_Array;
}


//---------------------------------------------------------------------------------
//function evaluateCondition($response_Array,$conditionTag,$curPos,$toSet) 
//if we have met a condition tag that as a name and value
//we have to evaluate the name / value it here and see if the condition is true
//if toset = 1 we just getting value and checking against a name that has been declared previously
//if = 2 then we are checking the name against the value
//---------------------------------------------------------------------------------
function evaluateCondition($response_Array,$conditionTag,$curPos,$toSet) 
{

	// if we havent sent a current position then get the one that is set
	if($curPos=="")
	{
			$curPos = getCurrentPosition($response_Array);
	}
	$response_Array = cleanResponseArray($response_Array);
	$curPos = getCurrentPosition($response_Array);
	//get the current part
	$thispart = $response_Array['responseparts'][$curPos];
	
	//do we need to know the value and the named variable to check against?
	if($toSet==2)
	{
		
		$findthis = extractVar($thispart); //get the  condition name
		$findthisa = $findthis ."\ \"";
		$findthisb = $findthis ."\ \'";
		$findthisc = $findthis ."\"";
		$findthisd = $findthis ."\'";
		
		$thispart = preg_split("/".$findthisa."|".$findthisb."|".$findthisc."|".$findthisd."/i",$thispart);
		$valueoffindthis = extractVar($thispart[1]); //extract the index from the tag
		
	}
	else //we only want to know the value so the variable must have been set alrady and stored in the conditionName
	{
		$findthis = $response_Array['conditionName'];
		$valueoffindthis = extractVar($thispart); //now we have the index

	}	
	//tidy up first
	$valueoffindthis = trim(strtolower($valueoffindthis));
	
	if($valueoffindthis == "*")
	{
		$valueoffindthis = $response_Array['star'][0];
	}

	//it could be a 2d array
	if(isset($response_Array[$findthis][0]))
	{
		$ourValueInArray = trim(strtolower($response_Array[$findthis][0]));
	}
	//it oculd be a 1d array
	elseif(isset($response_Array[$findthis]))
	{
		$ourValueInArray = trim(strtolower($response_Array[$findthis]));
	}
	//it could not be set
	else
	{
		
		$ourValueInArray = "";
	}
	
	
	//if it not already set.. then set to zero
	if(!isset($response_Array['conditionFound']))
	{
		$response_Array['conditionFound']=0;
	}

	//the condition may have been set previously in which case we will not bother with checking any more
	if($response_Array['conditionFound']==1)
	{
		$arrayVal = "";
	}

			

	if($toSet!=2)
	{
		//find our li value in the array
		$liToFind = 'tag_open_li value="'.$ourValueInArray.'"';
		
		
		$matchedLiStart = findElement($curPos,$liToFind,$response_Array,0);
		$matchedLiStartElse = findElement($curPos,"tag_open_li",$response_Array,0);
				
		if($matchedLiStart<$response_Array['totalparts'])
		{
			$matchedLiEnd = findEndElement($matchedLiStart,"li",$response_Array,0);
			$response_Array = unsetAll($response_Array,$curPos-1,$matchedLiStart);
			$endCond = findEndElement($matchedLiEnd,"condition",$response_Array,0);
			$response_Array = unsetAll($response_Array,$matchedLiEnd,$endCond);
			//all that should be left at this point is the right value
		}
		elseif($matchedLiStartElse<$response_Array['totalparts'])
		{
			$endCond = findEndElement($curPos,"condition",$response_Array,0);
			
			if($matchedLiStartElse<$endCond)
			{
				$matchedLiEnd = findEndElement($matchedLiStart,"li",$response_Array,0);
				$response_Array = unsetAll($response_Array,$curPos-1,$matchedLiStartElse);
				$endCond = findEndElement($matchedLiEnd,"condition",$response_Array,0);
				$response_Array = unsetAll($response_Array,$matchedLiEnd,$endCond);
				//all that should be left at this point is the right value
			}
		}
		else 
		{
			$endCond = findEndElement($curPos,"condition",$response_Array,0);
			$startCond = findStartElement($curPos,"condition",$response_Array,0);
			
					
			$response_Array = unsetAll($response_Array,$startCond,$endCond); //nts this used to be just $cupos not curpos minus 1
			$response_Array['conditionFound']=0;
			$response_Array['partnumber'] = $startCond;
			
			
			
			if($startCond>1)
			{
				for($s=$startCond;$s>0;$s--)
				{
					if((isset($response_Array['responseparts'][$s]))&&($response_Array['responseparts'][$s]!=""))
					{
						$response_Array['partnumber'] = $s;
						break;
					}
				}
			}
			
			
			$curPos = getCurrentPosition($response_Array);
			$response_Array['conditionFound']=0;
			
		}
		
			//now clean response array
			$response_Array = cleanResponseArray($response_Array);
			
	}	
	else
	{
		//now we can check if this is true or not.... 
		if( ($ourValueInArray!="")&&(strtoupper($ourValueInArray)==strtoupper($valueoffindthis)) ) //IT IS TRUE................
		{
			//continue find current position
			if($curPos=="")
			{
				$curPos = getCurrentPosition($response_Array);
			}
			
			//leave what ever follows in place and remove everything else 
			$endPosition = findEndElement($curPos,"li",$response_Array,0);
			$response_Array = pick($curPos,$endPosition,$response_Array,"notrandom","condition");
			$response_Array['conditionFound']=1;
			
						
			//now clear all the other li's
			$endPos = findEndElement($endPosition,"condition",$response_Array,0);
			$response_Array = unsetAll($response_Array,$endPosition,$endPos-1);
			
			//now clean response array
			$response_Array = cleanResponseArray($response_Array);
			
		}
		else //the value is not true or the condition has already been found
		{
			if($curPos=="")
			{
				$curPos = getCurrentPosition($response_Array);
			}		
			
			
				
			$endPos = findEndElement($curPos,$conditionTag,$response_Array,0);
			
			$response_Array = unsetAll($response_Array,$curPos,$endPos);
			
			//now clean response array
			$response_Array = cleanResponseArray($response_Array);
			
			$response_Array['partnumber'] = $curPos-1;
			
			$response_Array['conditionFound']=0;
			
		}
	}

	
	return $response_Array;
}

//--------------------------------------------------------------
//function closeBullet($response_Array)
//this bullet will have been evaluated previously if required
//so this function just tidys everything up
//--------------------------------------------------------------
function closeBullet($response_Array) //
{
	//get current position
	$curPos = getCurrentPosition($response_Array);
	//initialise
	$tmp = "";
	//find start tag....
	$start = findStartElement($curPos,"li",$response_Array);
	//get everything that is inbetween the two li tags and move into tmp
	for($j=$start+1;$j<$curPos;$j++)
	{
		$tmp.=$response_Array['responseparts'][$j]." ";
	}
	//----------------------------------------------
	
	//this just might be a floating else - which we do not want to use as we already have our evaluted anser
	if((isset($response_Array['conditionFound']))&&($response_Array['conditionFound']==1))
	{
		$response_Array = unsetAll($response_Array,$start,$curPos);
	}
	else //move it into random answer to be used later
	{
		$response_Array['randomanswer'][] = $tmp;
		$response_Array['li'][] = $tmp;
	
		$response_Array = unsetAll($response_Array,$start,$curPos);
		if(!isset($response_Array['randomchoice']))
		{
			$response_Array['randomchoice']=0;
		}
		$response_Array['randomchoice']++;
	}
	runDebug($response_Array,3,"closebullet","<br>Array Name = ".$response_Array['rname']."<br>tmp = $tmp");
	return $response_Array;
}
//--------------------------------------------------------------
//function resetResponseParts
//we have made sentances out of many response parts so now we want to 
//remove the parts 
//and insert the sentance in the correct place back in the response part
//do that here and reset the array
//--------------------------------------------------------------
function resetResponseParts($response_Array,$tmp,$pos)
{
	//add to array
	$response_Array['responseparts'][$pos]=$tmp;
	//sort the keys back into order
	ksort($response_Array['responseparts']);
	//debug
	runDebug($response_Array,3,"resetResponseParts","<br>Array Name = ".$response_Array['rname']."<br>reset index total = ".count($response_Array['responseparts']));
	//return
	return $response_Array;
}
//--------------------------------------------------------------
//function walkThruTemplate
//--------------------------------------------------------------
function walkThruTemplate($response_Array)
{
	//set stars for underscores and stars
	$response_Array = fillStarValues($response_Array,"_");
	$response_Array = fillStarValues($response_Array,"*");
	//i have the response BUT.... to make it less complicated open up any abbrievaitions
	$response_Array['matchtemplate'] = replaceBotVars($response_Array);
	$response_Array['matchtemplate'] = abbreviations($response_Array);	
	//extract all the parts of the answer in the AIML (the template) and break into its parts and place in the response_parts sub array 		
	$response_Array = extractall($response_Array);

	//count parts
	$parts = count($response_Array['responseparts']);
	//initailise
	$response_Array['totalparts']=$parts;
	$response_Array['currentposition']=0;	
	
	//move through each parts of the reponseparts array
	//sending each tag or textpart to the checkthis array
	for($i=0;$i<$parts;$i++)
	{
		if((isset($response_Array['responseparts'][$i]))&&($response_Array['responseparts'][$i]!=""))
		{
			//debug
			runDebug($response_Array,3,"walkThruTemplate","<br>Array Name = ".$response_Array['rname']."<br>walkingthru = $i");
	
	
			$thispart = $response_Array['responseparts'][$i];
			$response_Array['thispart']=$thispart;
			$response_Array['partnumber']=$i;
			//run the main switch that interprets the aiml tags
			runDebug($response_Array,3,"walkThruTemplate","<br>Array Name = ".$response_Array['rname']."<br>CHECKING: ".$thispart);
			$response_Array = checkthis($response_Array);
	
			//some times the reponseparts have records deleted and or added to them
			//so the part we are looking at may be changed in another function
			//so make sure everything tallys up here
			if($response_Array['partnumber']!=$i)
			{
				$i = $response_Array['partnumber']-1; //set to minus one as it was skipping the current moving on to the next
				runDebug($response_Array,3,"walkThruTemplate","<br>Array Name = ".$response_Array['rname']."<br>position changed: ".$i);
			}
			
		}
		else
		{
			runDebug($response_Array,5,"walkThruTemplate","<br>Array Name = ".$response_Array['rname']."<br>Looking for a response part that dpoesnt exist!");
		}
	}
	runDebug($response_Array,3,"walkThruTemplate","<br>Array Name = ".$response_Array['rname']."<br>TOTAL PARTs: ".$parts);
	return $response_Array;
}

//--------------------------------------------------------------
//function nullifyOutput
//erases everything between an opening and closing tag
//handles the thinking tag (and any other tag) by earasing everything inside it!
//--------------------------------------------------------------
function nullifyOutput($response_Array,$tag)
{
	//get start pos
	$curPos = getCurrentPosition($response_Array);

	//get start element (the one before the current one we are looking at)
	$start = findStartElement($curPos,$tag,$response_Array);
	//get element element (the one after the current one we are looking at)
	$end = findEndElement($curPos,$tag,$response_Array,0);
	//unset everything in the response parts 
	$response_Array = unsetAll($response_Array,$start,$end);
	//debug
	runDebug($response_Array,3,"nullifyOutput","<br>Array Name = ".$response_Array['rname']."<br>Thinking and nullifying - $tag which is $start to $end");
	//clean the array
	$response_Array = cleanResponseArray($response_Array);
	//return
	return $response_Array;
}

function countEmptyParts($response_Array)
{
	$emptyspace=0;
	foreach($response_Array['responseparts'] as $index => $value)
	{
		if( (!isset($value)) || (trim($value)=="") )
		{
			$emptyspace++;
		}
	}
	
	return $emptyspace;
}


//-------------------------------------
//function cleanResponseArray
//once we have nullified parts of the array
//we want to clean it up and remove all null spaces and reset the cur pos and cur part
//--------------------------------------
function cleanResponseArray($response_Array)
{
	//get the current start pos
	$curPos = getCurrentPosition($response_Array);
	$oldpart = $response_Array['responseparts'][$curPos];
	$emptyValues = countEmptyParts($response_Array);
	
	
	foreach($response_Array['responseparts'] as $key => $value) {
	  if($value == "") {
	    unset($response_Array['responseparts'][$key]);
	  }
	}
	$new_array = array_values($response_Array['responseparts']); 
	$response_Array['responseparts'] = array();
	$response_Array['responseparts'] = $new_array;
	
	
	$response_Array['partnumber'] = $curPos-$emptyValues;
	
	
	$response_Array['thispart'] = $response_Array['responseparts'][$response_Array['partnumber']];
	
	
	return $response_Array;
}




//--------------------------------------------------------------
//function lookAgain
//handles recursion
//this function is triggered by the srai tag
//--------------------------------------------------------------
function lookAgain($response_Array)
{
	//get the global array holder which contains all the arrays 
	global $arrayHolder;
	//get the user constants that must be set back to the other ones...
	global $userConstants;
	//initialise
	$tmp = "";
	//get current position
	$curPos = getCurrentPosition($response_Array);
	//find where the srai tags opens
	$start = findStartElement($curPos,"srai",$response_Array);

	//get everything inside the srai tag.....
	for($j=$start+1;$j<$curPos;$j++) //find everything between the start and end tags this is what we will look for in next loop
	{
		$tmp.=$response_Array['responseparts'][$j]." ";
		//reset the part positions
		$response_Array['thispart'] = $response_Array['responseparts'][$j];
    	$response_Array['partnumber'] = $j;
		//set to null	
		$response_Array['responseparts'][$j]="";
	}
	//clear opening srai
	$response_Array['responseparts'][$start]="";
	$response_Array['responseparts'][$curPos]="";	
	//debug
	runDebug($response_Array,3,"0.1. Cur Pos","<br>Array Name = ".$response_Array['rname']."<br>".$response_Array['thispart']." and ".$response_Array['partnumber']."");
	//debug
	runDebug($response_Array,3,"1. just before lookAgain","<br>Array Name = ".$response_Array['rname']."<br>Looped and found: $tmp");
	
	//push past the closing srai tag
	$response_Array['thispart'] = $response_Array['responseparts'][$j+1];
    $response_Array['partnumber'] = $j+1;	
	
	//debug
	runDebug($response_Array,3,"2. just before lookAgain","<br>Array Name = ".$response_Array['rname']."<br>Searching again on: $tmp");

	//set the current response_Array in a temp holder to save for later.....
	$arrayHolder[]=$response_Array;

	//count the array holder for debuging
	$countbefore = count($arrayHolder);
	

	

	//debug
	runDebug($response_Array,3,"New lookAgain","<br>Array Name = ".$response_Array['rname']."<br>c = $countbefore");
	runDebug($response_Array,3,"3. just before lookAgain","<br>Array Name = ".$response_Array['rname']."<br>Array before look again: $countbefore");
	runDebug($response_Array,3,"4. just before lookAgain","<br>Array Name = ".$response_Array['rname']."<br>Looking for: $tmp");
	
	//in the new array set the looking for to the new value...	
	//set new array to equal current
	$newArray = $response_Array;
	//reset everything we want to blank for fresh array
	$newArray['lookingfor']=$tmp;
	$newArray['matchtemplate']="";
	$newArray['matchpattern']="";
	$newArray['matchthatpattern']="";
	$newArray['orignalinput']=$tmp;
	$newArray['bot']=1;
	$newArray['responseparts']="";
	$newArray['totalparts']=0;
	$newArray['currentposition']=0;
	$newArray['thispart']="";
	$newArray['partnumber']=0;
	$newArray['reset']="";
	if(isset($response_Array['that']))
	{
		//$newArray['that']=$response_Array['that'];
		$newArray['that']="";
	}
	if(isset($response_Array['input']))
	{
		$newArray['input']=$response_Array['input'];
	}
	$newArray['answer']="";
	$newArray['rname'] = "Array#$countbefore";
	$newArray['who']="bot";
	$newArray['conditionFound']=0; //added mite remove
	$newArray['randomchoice']=0;//added mite remove
	$newArray['randomanswer']="";//added mite remove


	//debug
	runDebug($response_Array,5,"5. just about to lookAgain","<br>Array Name = ".$newArray['rname']."<br>Searching again on: $tmp");	
	
	//if we have over ten levels of recursion then assume its gone wrong and break out with a random pick up line
	if(trim($newArray['rname']) == "Array#10")
	{
		
		emailthis($newArray,"Infinite Loop");
		
		$newArray['lookingfor']="RANDOM PICKUP LINE";
		$newArray['rname'] = "Array#1";

		$newArray = array();
		$newArray = $response_Array;
		$newArray['lookingfor']="RANDOM PICKUP LINE";
		$star = $newArray['star'][9];
		$newArray['star']=array();
		$newArray['that'][0]=$star;
		$newArray['that'][0]="";
		
	}
	

	
	//run the whole process again
		
	$newArray = checkresponse($newArray,"bot");

	//return here and use the answer which is the srai tag....
	$ans = $newArray['answer'];
	
	//now we are going to switch back to the old array....
	//we may have multiple recursions so set the array to the array on the top of the stack (max element)
	//count the array holder for debuging
	$countafter = $countbefore;
	
	$response_Array = $arrayHolder[$countafter-1];
	
	foreach($newArray as $u => $item)
	{
		if((isset($newArray[$u]))&&(!in_array($u, $userConstants)) )//it is set in the newarray but not in the constants
		{
			$response_Array[$u]=$newArray[$u];
		}
		else
		{
		}
	}
	$arrayHolder[$countafter-1]=array();
	unset($arrayHolder[$countafter-1]); //erase
	//resort
	
	$b=0;
	$cleanNewArray=array();
	for($a=0;$a<$countafter;$a++)
	{
		if(isset($arrayHolder[$a]))
		{
			$cleanNewArray[$b]=$arrayHolder[$a];
			$b++;
		}
	}
	$arrayHolder=array();
	$arrayHolder=$cleanNewArray;
	//now unset all srai open close and in between

	//debug
	runDebug($response_Array,3,"6. finished lookAgain","<br>Array Name = ".$response_Array['rname']."<br>Array after look again: $countafter");	
	runDebug($response_Array,3,"7. finished lookAgain","<br>Array Name = ".$response_Array['rname']."<br>Found: $ans");
	
	//null all things
	for($j=$start;$j<$curPos;$j++) //find everything between the start and end tags this is what we will look for in next loop
	{
		//reset the part positions
		$response_Array['thispart'] = $response_Array['responseparts'][$j];
    	$response_Array['partnumber'] = $j;
		//set to null	
		$response_Array['responseparts'][$j]="";
	}
	//set the start position to the answer we found
	$response_Array['responseparts'][$curPos]=$ans;
	
	if(($countafter-1)==0)
	{
		$response_Array['who']="human";
	}
	//clean it
	$response_Array = cleanResponseArray($response_Array);
	//debug
	runDebug($response_Array,3,"8. finished lookedAgain","<br>Array Name = ".$response_Array['rname']."<br>found: $ans");
	//return
	return $response_Array;
}

//--------------------------------------------------------------
//function parseTag
//just moves thru array and changes the tag to inciate they have been parsed
//--------------------------------------------------------------
function parseTag($response_Array)
{
	//get current position
	$i = getCurrentPosition($response_Array);
	//change
	$response_Array['responseparts'][$i]="parsed_".$response_Array['responseparts'][$i];
	//debug
	runDebug($response_Array,3,"ParseTag","<br>Array Name = ".$response_Array['rname']."<br>Parsed: ".$response_Array['responseparts'][$i]);
	//return
	return $response_Array;
}

//--------------------------------------------------------------
//function dateFormat($response_Array)
//formats the time and date
//--------------------------------------------------------------
function dateFormat($response_Array)
{
	$curPos = getCurrentPosition($response_Array);
	$thispart = $response_Array['responseparts'][$curPos];
	$tag = extractVar($thispart);
	
	switch($tag)
	{
		case "%A": //day
			$date = date("l"); //monday
			break;
		case "%x": //today
			$date = date('l jS \of F Y h:i:s A'); //// Prints something like: Monday 8th of August 2005 03:12:46 PM
			break;		
		case "%p": //hour
			$date = date("H A");
			break;	
		case "%Y": //year
			$date = date("Y"); //2009
			break;	
		case "%B": //month
			$date = date("F"); //January March etc
			break;	
		case "%I %p": //time
			$date = date("H:i:s A");
			break;	
		default:
			$date = date('l jS \of F Y h:i:s A'); //// Prints something like: Monday 8th of August 2005 03:12:46 PM
			break;		
		}
	
	$response_Array['responseparts'][$curPos] = $date;
	runDebug($response_Array,3,"dateFormat","<br>Array Name = ".$response_Array['rname']."<br>$tag = $date");
	return $response_Array;
}

//--------------------------------------------------------------
//function  isHTML($response_Array)
//takes the tag and converts it to html e.g. tag_open_a becomes <a>
//--------------------------------------------------------------
function isHTML($response_Array)
{
	$i = getCurrentPosition($response_Array);
	$tags = strtolower($response_Array['responseparts'][$i]);
	$tags = str_replace("tag_close_/","</",$tags);
	$tags = str_replace("tag_close_","<",$tags);
	$tags = str_replace("tag_open_","<",$tags);
	$html = $tags.">";
	$response_Array['responseparts'][$i]=$html;

	runDebug($response_Array,3,"HTMLTag","<br>Array Name = ".$response_Array['rname']."<br>Parsed: ".htmlentities($html,ENT_QUOTES,"UTF-8"));
	return $response_Array;
}
//--------------------------------------------------------------
//function extractVar($thispart)
//gets what is ever between to speechmarks
//i.e. index="2"
//or name="bob"
//--------------------------------------------------------------
function extractVar($thispart)
{
	$thispart = str_replace("'","\"",$thispart);
	
	preg_match("/\"(.*?)\"/i",$thispart,$extractedTag);
	
	if(preg_match('/\bindex\b/i',$thispart,$isindex))
	{
		$isindex = 1;
	}
	else
	{
		$isindex = 0;
	}
	
	if(!isset($extractedTag[1]))
	{
		$tag = 0; //default to current
	}
	else
	{
		$tag = trim($extractedTag[1]);
	}
	
	//if its numerical index value we subtract one from it becuase AIML arrays have an offest of 1
	//program -o arrays start from 0
	if((is_numeric($tag))&&($isindex==1))
	{
		$tag = $tag-1;
	}
	
	runDebug("",3,"extractVar","<br>Array Name = not in array<br>looked in: $thispart<br>found: $tag");
	return $tag;
}
//-------------------------------------------------------------
//function closeJavascript
//wrap the contents of the javascript tag in real javascript 
//-------------------------------------------------------------
function closeJavascript($response_Array,$tag)
{
	//get current position
	$curPos = getCurrentPosition($response_Array);
	//intialise
	$tmp = "";
	//find start tag
	$start = findStartElement($curPos,$tag,$response_Array);
	//find values inside the javascript tags
	for($j=$start+1;$j<$curPos;$j++)
	{
		$tmp.=$response_Array['responseparts'][$j]." ";
	}
	//wrap in javascript
	$tmp = "<script type=\"text/javascript\">".$tmp."</script>";
	//unset all elements involved
	$response_Array = unsetAll($response_Array,$start,$curPos);
	//add value to array in current position then re order all
	$response_Array = resetResponseParts($response_Array,$tmp,$curPos);
	
	//debug	
	runDebug($response_Array,3,"close$tag","<br>Array Name = ".$response_Array['rname']."<br>".htmlentities($tmp,ENT_QUOTES,"UTF-8"));
	//return	
	return $response_Array;
}

//-------------------------------------------------------------
//function textFormat($response_Array,$format)
//format the contents between certain tags
//-------------------------------------------------------------
function textFormat($response_Array,$format)
{
	//get items to format
	$curPos = getCurrentPosition($response_Array);
	$tmp = ""; //intialise
	
	//get the starting element
	$start = findStartElement($curPos,$format,$response_Array);
	for($j=$start;$j<$curPos;$j++)
	{
		$tmp.=$response_Array['responseparts'][$j]." "; //build
	}
	
	//debug
	$dtmp = $tmp; 
	
	//run formating
	switch(strtolower($format))
	{
		case "uppercase":
			$tmp = strtoupper($tmp);
		break;
		case "lowercase":
			$tmp = strtolower($tmp);
		break;		
		case "formal":
			$tmp = ucwords($tmp);
		break;	
		case "sentence":
			$tmp = ucfirst($tmp);
		break;
	}
	//unset all
	$response_Array = unsetAll($response_Array,$start-1,$curPos);
	//insert new item in response parts
	$response_Array = resetResponseParts($response_Array,$tmp,$curPos);
	//debug
	runDebug($response_Array,3,"textFormat","<br>Array Name = ".$response_Array['rname']."<br>Was = $dtmp<br>Is = $tmp");
	//return
	return $response_Array;
}

//-------------------------------------------------------------
//function cleanWords($tmp)
//this function cleans the bot responses before they are diplayed to the user
//-------------------------------------------------------------
function cleanWords($tmp)
{
	    $tmp = preg_replace('/\s\s+/', ' ', $tmp);
		$tmp = str_replace(' .', '.', $tmp);
		$tmp = str_replace('..', '.', $tmp);
		$tmp = str_replace('?.', '?', $tmp);
		$tmp = str_replace('!.', '!', $tmp);
		$tmp = str_replace(' ,', ',', $tmp);
		$tmp = str_replace(' ?', '?', $tmp);
		$tmp = str_replace(' !', '!', $tmp);
		
		$tmp = str_replace('tag_open_botresponse', '', $tmp);
		$tmp = str_replace('tag_close_/botresponse', '', $tmp);
		
		$tmp = str_replace(' .', '.', $tmp);
		
		$tmp = trim($tmp);
		//$tmp = ucfirst($tmp);
		
		return $tmp;
}


//-------------------------------------------------------------------------
//function abbreviations
//when we match a pattern 
//the matched aiml may contain shorter versions of the full tag
//this function replaces them to their extended version.. making parsing easier
//-------------------------------------------------------------------------
function abbreviations($response_Array)
{
	//just set this tmp for the debug..
	$matchedOutput = $response_Array['matchtemplate'];
	
	$tmpmo = htmlentities($matchedOutput,ENT_QUOTES,"UTF-8");
	//make replacements
	
	//housekeeping
	$matchedOutput = str_replace("><","> <",$matchedOutput);
	
	//XFIND replacements........
	$matchedOutput = str_replace('<![CDATA[','',$matchedOutput);
	$matchedOutput = str_replace('<![CDATA[','',$matchedOutput);
	$matchedOutput = str_replace(']]>','',$matchedOutput);
	
	$matchedOutput = str_replace("<srai>PUSH <person>YOU <star/> </person> </srai>","<gossip> <person>YOU <star/> </person> </gossip>",$matchedOutput);
	
	//TIDY UP SPACES	
	$matchedOutput = str_replace(' = "','="',$matchedOutput);
	$matchedOutput = str_replace('= "','="',$matchedOutput);
	$matchedOutput = str_replace(' ="','="',$matchedOutput);
	$matchedOutput = str_replace(" />","/>",$matchedOutput);
	
	//convert 2d values into 1d
	$matchedOutput = str_replace("2,*","2",$matchedOutput);
	$matchedOutput = str_replace("2,1","2",$matchedOutput);
	$matchedOutput = str_replace("1,1","1",$matchedOutput);
	
			
	//OPEN OUT 1.0 TAGS	
	$matchedOutput = str_replace("<personf/>","<star index=\"1\"/>",$matchedOutput);
	$matchedOutput = str_replace("<that/>","<that index=\"1\"/>",$matchedOutput);
	$matchedOutput = str_replace("<input/>","<input index=\"1\"/>",$matchedOutput);
	$matchedOutput = str_replace("<star/>","<star index=\"1\"/>",$matchedOutput);
	$matchedOutput = str_replace("<thatstar/>","<thatstar index=\"1\"/>",$matchedOutput);
	$matchedOutput = str_replace("<response","<that",$matchedOutput);
	$matchedOutput = str_replace("<sr/>","<srai> <star/> </srai>",$matchedOutput);
	$matchedOutput = str_replace("<person/>","<person> <star/> </person>",$matchedOutput);
	$matchedOutput = str_replace("<person2/>","<person2> <star/> </person2>",$matchedOutput);
	//$matchedOutput = str_replace("<srai>PUSH <set name=\"topic\"","<srai><set name=\"topicStack\"",$matchedOutput);
	//$matchedOutput = str_replace("<srai>POP</srai>","<srai><get name=\"topicStack\"></srai>",$matchedOutput);
	//need to set this to no topic to make some of square bears aiml files work...
	$matchedOutput = str_replace("<think> <set name=\"topic\"> </set>","<think> <set name=\"topic\">no topic</set>",$matchedOutput);
	$matchedOutput = str_replace("<star/>","<star index=\"1\">",$matchedOutput);	
	
	//make any replacements we can here for the input indexes
	for($ii=0;$ii<10;$ii++)
	{
		if( (isset($response_Array['input'][$ii]))&&($response_Array['input'][$ii]!="") ) //if we have a star we can make any replacements now
		{
			$inputindex = $ii+1;
			$matchedOutput = str_replace("<input index=\"".$inputindex."\" />",$response_Array['input'][$ii],$matchedOutput);
			$matchedOutput = str_replace("<input index=\"".$inputindex."\"/>",$response_Array['input'][$ii],$matchedOutput);
		}
	}	
		
	
	//make any replacements we can here for the star indexes
	for($si=0;$si<10;$si++)
	{
		if(isset($response_Array['star'][$si])) //if we have a star we can make any replacements now
		{
			$starindex = $si+1;
			$matchedOutput = str_replace("<star index=\"".$starindex."\"/>",$response_Array['star'][$si],$matchedOutput);
		}
	
	}

	//housekeeping
	$matchedOutput = preg_replace("/\s+/"," ",$matchedOutput);
	
	//debug
	$mo = htmlentities($matchedOutput,ENT_QUOTES,"UTF-8");
	
	runDebug("",3,"abbreviations","<br>Array Name = not in array<br>$tmpmo became $mo");
	//return
	
	return $matchedOutput;
}

//-------------------------------------------------------------------------
//sortConditions($response_Array,$condType)
//triggered by a closing random or condition
//nothing to do really because by this stage our answer will have been extracted
//so this func just nullifies
//-------------------------------------------------------------------------
function sortConditions($response_Array,$condType)
{
	//nullify everything as all out conditional data has been extracted by the close bullet tag
	$response_Array = nullifyOutput($response_Array,$condType);
	//reset the condition found to 0 incase there is another random tag after this one	
	$response_Array['conditionFound']='0';
	//debug
	runDebug($response_Array,3,"sortConditions","<br>Array Name = ".$response_Array['rname']."<br>nullified");
	//return
	return $response_Array;
}
//--------------------------------------------------------------
//RandomList($response_Array)
//parse the open random list tag.... we are about to put a load of answers in the array
//we will chose which one we want later
//--------------------------------------------------------------
function RandomList($response_Array)
{
	//get current position
	$curPos = getCurrentPosition($response_Array);

	//set all choices to 0
	$response_Array['randomchoice']=0;
	//mark tag as parsed
	$response_Array['responseparts'][$curPos]="parsed_tag_open_random";
	//now find the last random.....
	$endPosition = findEndElement($curPos,"random",$response_Array,0);
	//pick a random li from inside the random tag and parse it all
	$response_Array = pick($curPos,$endPosition,$response_Array,"random","random");
	//debug	
	runDebug($response_Array,3,"RandomList","<br>Array Name = ".$response_Array['rname']."<br>intialising random list 2");
	//return
	return $response_Array;
}

//--------------------------------------------------------------
//pick($curPos,$endPosition,$response_Array,$type,$tag)
//get all the possible <li>'s between two positions
//select one randomly if no conditions set..
//or if condition has ot be checked .. check and select 
//erase the rest, then return the new responseparts
//--------------------------------------------------------------
function pick($curPos,$endPosition,$response_Array,$type,$tag)
{
	
	if($type=="random") //get all the positions of any <li> tags between start and end.. pick on and save for later
	{
		for($i=$curPos;$i<$endPosition;$i++)
		{
			if(strpos($response_Array['responseparts'][$i],"open_li")!==false)
			{
				$pickThis[]=$i;
			}
		}
		$startPos = $pickThis[array_rand($pickThis)];
		runDebug($response_Array,3,"pick","<br>Array Name = ".$response_Array['rname']."<br>inside pick looking for random found - ".$response_Array['responseparts'][$startPos]);
	}
	else //we shall start at the position sent to us
	{
		$startPos = $curPos;
		runDebug($response_Array,3,"pick","<br>Array Name = ".$response_Array['rname']."<br>inside pick looking for start pos $curPos - ".$response_Array['responseparts'][$startPos]);
	}
	//given the opening li get the closing one
	$endPos = findEndElement($startPos,"li",$response_Array,0);
	
	runDebug($response_Array,3,"pick","<br>Array Name = ".$response_Array['rname']."<br>found end pos $endPos - ".$response_Array['responseparts'][$endPos]);
	$debug = "";
	//get everything between the <li></li>
	for($i=$startPos+1;$i<=$endPos-1;$i++)
	{
		$tempArray[]=$response_Array['responseparts'][$i];
		$debug .= "<br>$i = ".$response_Array['responseparts'][$i];
	}
	
	runDebug($response_Array,3,"pick","<br>Array Name = ".$response_Array['rname']."<br>between the eyes - ".$debug);
	
	//if there was anything between the tags it will be in the temp array
	if(count($tempArray)>=1)
	{
		//unset everything in the master array
		$s = findStartElement($curPos,$tag,$response_Array);
		$e = findEndElement($curPos,$tag,$response_Array,0);
		//unset everything in the response parts 
		$response_Array = unsetAll($response_Array,$s,$e);
		//debug
		runDebug($response_Array,3,"pick","<br>Array Name = ".$response_Array['rname']."<br>nullified $s to $e");
		//shift the array up by....
		$budgeup = count($tempArray);
		$tot = $response_Array['totalparts'];
		
		runDebug($response_Array,3,"pick","<br>Array Name = ".$response_Array['rname']."<br>budge up by = $budgeup");
		runDebug($response_Array,3,"pick","<br>Array Name = ".$response_Array['rname']."<br>new total = $startPos + $budgeup");	
		
		$j=0;
		for($i=$s;$i<=$s+$budgeup;$i++)
		{
			if(isset($tempArray[$j]))
			{
				$response_Array['responseparts'][$i]=$tempArray[$j]; //shift the array up to make space for our new values and if set insert our new values
			}
			$j++;
		}	
		//reset these so they can be parsed on the next loop in the walk thru function
		$response_Array['partnumber'] = $s-1;
		$response_Array['thispart'] = $response_Array['responseparts'][$s-1];
		//clean the array to remove any nulls
		$response_Array = cleanResponseArray($response_Array);
	}	
	//return
    return $response_Array;
}

//--------------------------------------------------------------
//findStartElement($endPosition,$tagToFind,$response_Array)
//function to find the starting tag from the tag set....
//pass the end position where we will start the search
//pass the tagtofind which will end the search when it starrts
//and the array
//--------------------------------------------------------------
function findStartElement($endPosition,$tagToFind,$response_Array) //<--TODO EXCEPTIONS
{	
	
	$tagToFind = "tag_open_".strtolower($tagToFind);
	//debug
	runDebug($response_Array,3,"1st findStartElement","<br>Array Name = ".$response_Array['rname']."<br>tag to find = $tagToFind, endposition = $endPosition");
	//just intialise the min start position
	$startPosition=0;

	
	for($i=$endPosition;$i>=$startPosition;$i--)
	{
		if(!isset($response_Array['responseparts'][$i]))
		{
			runDebug($response_Array,3,"findStartElement","<br>Array Name = ".$response_Array['rname']."<br>looking for $i but it not set");
		}
		else
		{
			$part = strtolower($response_Array['responseparts'][$i]);
			if(strpos($part, $tagToFind)!==false)
			{
				$startPosition = $i; //yes we did so set and break.. //Was plus one..... hmmmmmm
				break;
			}
		}
	}
	//TODO what if we never find it????? 
	
	return $startPosition; 
}


function findElement($startPosition,$tagToFind,$response_Array,$findLastExistingTag) //<--TODO EXCEPTIONS
{	
	
	//debug
	runDebug($response_Array,3,"1st findEndElement","<br>Array Name = ".$response_Array['rname']."<br>tag to find = $tagToFind, startposition = $startPosition");
	//just intialise the max end position
	$endPosition=$response_Array['totalparts'];

	if($findLastExistingTag==1) //this means that we want to find the last tag in response parts
	{
		for($i=$endPosition;$i>=0;$i--)
		{
			if(!isset($response_Array['responseparts'][$i]))
			{
				runDebug($response_Array,3,"findEndElement","<br>Array Name = ".$response_Array['rname']."<br>looking for $i but it not set");
			}
			else
			{
				$part = trim(strtolower($response_Array['responseparts'][$i]));
				if($part == $tagToFind)
				{
					$endPosition = $i; //yes we did so set and break.. //Was plus one..... hmmmmmm
					break;
				}
			}
		}
	}
	else //we are just looking for the next tag
	{
		for($i=$startPosition;$i<=$endPosition;$i++)
		{
			if(!isset($response_Array['responseparts'][$i]))
			{
				runDebug($response_Array,3,"findEndElement","<br>Array Name = ".$response_Array['rname']."<br>looking for $i but it not set");
			}
			else
			{
				$part = trim(strtolower(str_replace('"','',$response_Array['responseparts'][$i])));
				$tagToFind = trim(str_replace('"','',$tagToFind));
	
				$part = preg_replace('/\s|\s+/','',$part); 
				$tagToFind = preg_replace('/\s|\s+/','',$tagToFind); 
	
				if($part==$tagToFind)
				{
					$endPosition = $i; //yes we did so set and break.. //Was plus one..... hmmmmmm
					break;
				}
			}
		}
	}	
	return $endPosition; 
}




function findEndElement($startPosition,$tagToFind,$response_Array,$findLastExistingTag)
{	
	
	$tagToFind = "tag_close_/".strtolower($tagToFind);
	//debug
	runDebug($response_Array,3,"1st findEndElement","<br>Array Name = ".$response_Array['rname']."<br>tag to find = $tagToFind, startposition = $startPosition");
	//just intialise the max end position
	$endPosition=$response_Array['totalparts'];

	if($findLastExistingTag==1) //this means that we want to find the last tag in response parts
	{
		for($i=$endPosition;$i>=0;$i--)
		{
			if(!isset($response_Array['responseparts'][$i]))
			{
				runDebug($response_Array,3,"findEndElement","<br>Array Name = ".$response_Array['rname']."<br>looking for $i but it not set");
			}
			else
			{
				$part = strtolower($response_Array['responseparts'][$i]);
				if(strpos($part, $tagToFind)!==false)
				{
					$endPosition = $i; //yes we did so set and break.. //Was plus one..... hmmmmmm
					break;
				}
			}
		}
	}
	else //we are just looking for the next tag
	{
		for($i=$startPosition;$i<=$endPosition;$i++)
		{
			if(!isset($response_Array['responseparts'][$i]))
			{
				runDebug($response_Array,3,"findEndElement","<br>Array Name = ".$response_Array['rname']."<br>looking for $i but it not set");
			}
			else
			{
				$part = strtolower($response_Array['responseparts'][$i]);
				if(strpos($part, $tagToFind)!==false)
				{
					$endPosition = $i; //yes we did so set and break.. //Was plus one..... hmmmmmm
					break;
				}
			}
		}
	}	
	//TODO what if we never find it?????
	
	return $endPosition; 
}

//--------------------------------------------------------------
//getCurrentPosition($response_Array)
//just return the current position
//that our parser is looking at
//--------------------------------------------------------------
function getCurrentPosition($response_Array)
{
	//get
	$i = $response_Array['partnumber'];
	//debug
	runDebug($response_Array,3,"getCurrentPosition","<br>Array Name = ".$response_Array['rname']."<br>Curpos: $i");
	//return
	return $i;
}

//--------------------------------------------------------------
//--------------------------------------------------------------
function resetCurrentPosition($response_Array,$newPos)
{
	$response_Array['partnumber'] = $newPos;
	//debug
	return $response_Array;
}

//--------------------------------------------------------------
//frontOfStack($response_Array,$pushThis,$ontoThis)
//push items to the zero position of any array
//--------------------------------------------------------------
function frontOfStack($response_Array,$pushThis,$ontoThis)
{
	//make sure its set before we insert
	$ontoThis = trim($ontoThis);
	
	//this is the tradition aiml stack so its handled in a slightly different way to fit in with aiml spec
	if(($ontoThis=="top")||($ontoThis=="second")||($ontoThis=="third")||($ontoThis=="fourth")||($ontoThis=="fifth")||($ontoThis=="sixth")||($ontoThis=="seventh")||($ontoThis=="last"))
	{
		$response_Array[$ontoThis]=$pushThis;
		
	}
	else //everything else is just put on the 0 position of its one 2d array
	{
	
		//if(trim($pushThis)!="") //changed this we need to be able to push blanks onto all stacks :(
		//{
			if(isset($response_Array[$ontoThis][0]))//if set we will shove everything up by one and insert in the zero position
			{
				$mc = count($response_Array[$ontoThis])-1; //yes it is so move all items one up
				for($i=$mc;$i>=0;$i--)
				{
					$item = stripslashes($response_Array[$ontoThis][$i]);
					$item = trim($item);
					$response_Array[$ontoThis][$i+1]=$item;
				}
				$item = stripslashes($pushThis);
				$item = trim($item);
				$response_Array[$ontoThis][0]=stripslashes($pushThis); //make the zero element equal the new value
			}
			else //nothing is set so just set the zero value
			{
				$item = stripslashes($pushThis);
				$item = trim($item);
				$response_Array[$ontoThis][0]=stripslashes($pushThis);
				
			}
			
			
	}
	//debug
	runDebug($response_Array,3,"frontOfStack","<br>Array Name = ".$response_Array['rname']."<br>Putting $pushThis onto $ontoThis");
	//return		
	return $response_Array;
}

//--------------------------------------------------------------
//extractall($response_Array)
//takes the response array and takes in raw answer and breaks it into its components
//based on tags
//--------------------------------------------------------------
function extractall($response_Array)
{
	//initialise
	$data = $response_Array['matchtemplate'];
	//replace < with tag text
	$data = str_replace("<","<tag_open_",$data);
	$data = str_replace("<tag_open_/","<tag_close_/",$data);
	
	//break into tag parts
	$countingtags = explode("<",$data);
	
	//loop thru all
	foreach($countingtags as $index => $data)
	{
		//if it contains somethings		
		if(($data!=" ")&&($data!=""))
		{
			if($spilt = explode(">",$data)) //find the closing tag.....
			{
				$d=trim($spilt[0]); //clean
				if(($d!=" ")&&($d!="")) //if not empty then clean and add
				{
					$content[]=$d;
				}
				if(isset($spilt[1])) //if there is anything on the other side of the tag
				{
					$d=trim($spilt[1]); //clean
					if(($d!=" ")&&($d!="")) //if not empty then add
					{
						$content[]=$d;
					}	
				}				
			}
			else //doesnt have a closing tag so this is probably simple text
			{
				$d=trim($data); //clean 
				if(($d!=" ")&&($d!="")) //if not empty add
				{
					$content[]=$d;
				}	
			}
		}
	}

	//add parts to the response parts array
	$response_Array['responseparts']=$content;
	//debug
	runDebug($response_Array,3,"extractAll","<br>Array Name = ".$response_Array['rname']."<br>extracting tag data");
	//return
	return $response_Array;
}
//--------------------------------------------------------------
//fillStarValues($response_Array,$wildcard)
//extracting all the stars from the matched aiml pattern and putting them on the star array
//--------------------------------------------------------------
function fillStarValues($response_Array,$wildcard)
{
	$matchedinput = $response_Array['matchpattern'];
	$st=0; //for debuging
	//there is a star start extraction
	if(strpos($matchedinput,$wildcard)!==FALSE)
	{
		$response_Array = buildStarIndex($response_Array,$wildcard);
		$st++; //for debugging
	}
	//debug
	runDebug($response_Array,3,"fillStarValues","<br>Array Name = ".$response_Array['rname']."<br>Collected $st $wildcard's");
	//return	
	return $response_Array;
}

//--------------------------------------------------------------
//a pattern has been matched and the star is a person
//so we are attahcing the person to the star and saving it for later...
//person($response_Array)
//--------------------------------------------------------------
function person($response_Array,$person) //2 = swap first with second poerson // otherwise swap with third person
{
	//get the current position
	$curPos = getCurrentPosition($response_Array);
	//initialise
	$tmp = "";
	
	//which transform are we doing??? set the opening tag accordingly
	if($person == 2)
	{
		$tagtofind = "person2";
	}
	else
	{
		$tagtofind = "person";
	}
	$start = findStartElement($curPos,$tagtofind,$response_Array);
	for($j=$start+1;$j<$curPos;$j++) //get data BETWEEN the tags
	{
		$tmp.=$response_Array['responseparts'][$j]." ";
	}
	
	$tmp = trim($tmp);
	
	//unset everything
	//$response_Array = unsetAll($response_Array,$start,$curPos);

	//now to do the transform............
	$first = array("me","i");
	$second = array("you");
	$third = array("him or her");
	
	if(($tmp=="undefined-gender")||($tmp=="i")||($tmp=="me")||($tmp=="they")||($tmp=="them"))
	{
		if($person == 2)
		{
			$tmp = preg_replace('/(\byour gender\b)/i','you',$tmp);
			$tmp = preg_replace('/(\bi\b)/i','you',$tmp);
			$tmp = preg_replace('/(\bme\b)/i','you',$tmp);
		}
		if($person == 3)
		{
			$tmp = preg_replace('/(\byour gender\b)/i','him or her',$tmp);
			$tmp = preg_replace('/(\bthey\b)/i','him or her',$tmp);
			$tmp = preg_replace('/(\bthey\b)/i','him or her',$tmp);			
			$tmp = preg_replace('/(\bi\b)/i','him or her',$tmp);
			$tmp = preg_replace('/(\bme\b)/i','him or her',$tmp);
		}
	}
	else
	{
	}
	
	$response_Array['responseparts'][$start]=trim($tmp);
	$response_Array = unsetAll($response_Array,$start+1,$curPos);

	$response_Array = cleanResponseArray($response_Array);
		
	//$response_Array['partnumber'] = $start;
	//$response_Array['thispart'] = $response_Array['responseparts'][$start];
	
	
	//debug
	runDebug($response_Array,3,"person","<br>Array Name = ".$response_Array['rname']."<br>transforming person<br>Found: $tmp");
	//return
	return $response_Array;
}
?>