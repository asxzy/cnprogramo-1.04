<?php
//-----------------------------------------------------------------------------------------------
//Program-o Version 1.0.4
//PHP MYSQL AIML interpreter
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
//check_aiml_part.php
//contains ons function for sorting and moveing through the AIML response
//-----------------------------------------------------------------------------------------------

//--------------------------------------------------------------
//checkthis($response_Array)
//checks the current part of the response (the current tag) to see if it triggers response building function
//--------------------------------------------------------------
function checkthis($response_Array)
{
	global $debugmode;
	//get the part we are looking at
	$thispart = strtolower($response_Array['responseparts'][$response_Array['partnumber']]);
	
	//echo "<hr><hr><h4>curpos:".$response_Array['partnumber']."</h4>";
	//		echo "<pre>";
	//	print_r($response_Array['responseparts']);
	//	echo "</pre>";
	
	if(strpos($thispart,"tag_open_aiml")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_aiml and found $thispart");
		
		$response_Array = parseTag($response_Array); //function to ignore the tag
		
		$mpart = "tag_open_aiml";
	
	}
    elseif(strpos($thispart,"tag_open_bot name")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_bot name and found $thispart");
		
		$extractedTag = extractVar($thispart);
		$response_Array = getBotVariable($response_Array,$extractedTag);
		
		$mpart = "tag_open_bot name";
		
	}
    elseif(strpos($thispart,"tag_open_that")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_that and found $thispart");
		$indexArr = getIndex($thispart);
		$response_Array = getThat($response_Array,$indexArr);
		
		$mpart = "tag_open_that";
		
	}
	elseif(strpos($thispart,"tag_open_category")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_category and found $thispart");
		
		$response_Array = parseTag($response_Array);
		
		$mpart = "tag_open_category";
		
	}
    elseif(strpos($thispart,"tag_open_input index")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_input index and found $thispart");
		
		$indexArr = getIndex($thispart);
		$response_Array = getInput($response_Array,$indexArr);
		
		$mpart = "tag_open_input index";
	}
    elseif(strpos($thispart,"tag_open_gender")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_gender and found $thispart");
		$response_Array = getGender($response_Array);	
		$mpart = "tag_open_gender";
	}
    elseif(strpos($thispart,"tag_open_date")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_date and found $thispart");
		$response_Array = getCurrentDate($response_Array);	
		$mpart = "tag_open_date";
	}
    elseif(strpos($thispart,"tag_open_id")!==false)
	{
	
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_id and found $thispart");
		$response_Array = getIP($response_Array);	
		$mpart = "tag_open_id";
	}
	elseif(strpos($thispart,"tag_open_size")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_size and found $thispart");
		$response_Array = getBotVariable($response_Array,"size");
		$mpart = "tag_open_size";
	}
    elseif(strpos($thispart,"tag_open_version")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_version and found $thispart");
		$response_Array = getBotVariable($response_Array,"version");
		$mpart = "tag_open_version";
	}
    elseif(strpos($thispart,"tag_open_get name=")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_get name and found $thispart");
		$extractedTag = extractVar($thispart);
		$response_Array = getConversationVariable($response_Array,$extractedTag);	
		$mpart = "tag_open_get name";
	}
	elseif(strpos($thispart,"tag_close_/li")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/li and found $thispart");
		$response_Array = closeBullet($response_Array);
		$mpart = "tag_close_/li";
	}
	elseif(strpos($thispart,"tag_open_pattern")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_pattern and found $thispart");
		$response_Array = parseTag($response_Array);
		$mpart = "tag_open_pattern";
	}
    elseif(strpos($thispart,"tag_open_person")!==false) //TO DO just check this later // this has been handled in the bbreviation
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_person and found $thispart");
		$response_Array = parseTag($response_Array);
		$mpart = "tag_open_person";
	}
    elseif(strpos($thispart,"tag_open_random")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_random and found $thispart");
		$response_Array['conditionFound']=0;
		$response_Array = RandomList($response_Array);
		$mpart = "tag_open_random";
	}
    elseif(strpos($thispart,"tag_close_/random")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/random and found $thispart");
		$response_Array = nullifyOutput($response_Array,"random");
		$response_Array['conditionFound']=0;
		$mpart = "tag_close_/random";
	}
    elseif(strpos($thispart,"tag_close_/randone")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/randome and found $thispart");
		$response_Array = sortConditions($response_Array,"randone");
		$mpart = "tag_close_/randone";
	}	
    elseif(strpos($thispart,"tag_close_/set")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/set and found $thispart");
		$response_Array = setConversationVariable($response_Array);		
		$mpart = "tag_close_/set";
	}	
    elseif(strpos($thispart,"tag_close_/uppercase")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/uppercase and found $thispart");
		$response_Array = textFormat($response_Array,"uppercase");
		$mpart = "tag_close_/uppercase";
	}
    elseif(strpos($thispart,"tag_close_/lowercase")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/lowercase and found $thispart");
		$response_Array = textFormat($response_Array,"lowercase");
		$mpart = "tag_close_/lowercase";
	}
    elseif(strpos($thispart,"tag_close_/sentence")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/sentance and found $thispart");
		$response_Array = textFormat($response_Array,"sentence");
		$mpart = "tag_close_/sentence";
	}
    elseif(strpos($thispart,"tag_close_/formal")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/formal and found $thispart");
		$response_Array = textFormat($response_Array,"formal");
		$mpart = "tag_close_/formal";
	}
	elseif(strpos($thispart,"tag_close_/javascript")!==false) //we dont care about tag_open_javascript
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/javascript and found $thispart");
		$response_Array = closeJavascript($response_Array,"javascript");
		$mpart = "tag_close_/javascript";
	}
	elseif(strpos($thispart,"tag_close_/script")!==false) //we dont care about tag_open_javascript
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/javascript and found $thispart");
		$response_Array = closeJavascript($response_Array,"script");
		$mpart = "tag_close_/script";
	}	
    elseif(strpos($thispart,"tag_close_/system")!==false) //we don't care about tag_open_system
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/system and found $thispart");
		$response_Array = closeSystem($response_Array);
		$mpart = "tag_close_/system";
	}
    elseif(strpos($thispart,"tag_open_br/")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_br/ and found $thispart");
		$response_Array = isHTML($response_Array);
		$mpart = "tag_open_br/";
	}	
    elseif(strpos($thispart,"tag_open_a")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_a and found $thispart");	
		$response_Array = isHTML($response_Array);
		$mpart = "tag_open_a";
	}	
    elseif(strpos($thispart,"tag_close_/a")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/a and found $thispart");		
		$response_Array = isHTML($response_Array);
		$mpart = "tag_close_/a";
	}	
    elseif(strpos($thispart,"tag_open_em")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_em and found $thispart");		
		$response_Array = isHTML($response_Array);
		$mpart = "tag_open_em";
	}	
    elseif(strpos($thispart,"tag_close_/em")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/em and found $thispart");
		$response_Array = isHTML($response_Array);
		$mpart = "tag_close_/em";
	}	
    elseif(strpos($thispart,"tag_open_botresponse")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_botresponse and found $thispart");
		$response_Array = parseTag($response_Array);
		$mpart = "tag_open_botresponse";
	}	
    elseif(strpos($thispart,"tag_open_think")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_think and found $thispart");
		$response_Array['thinking']="yes";
		$response_Array = parseTag($response_Array);
		$mpart = "tag_open_think";
	}
    elseif(strpos($thispart,"tag_close_/think")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/think and found $thispart");
		$response_Array = nullifyOutput($response_Array,"think");
		$mpart = "tag_close_/think";
	}
    elseif(strpos($thispart,"tag_close_/srai")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/srai and found $thispart");
		$response_Array = lookAgain($response_Array);
		$mpart = "tag_close_/srai";
	}
    elseif(strpos($thispart,"tag_open_star")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_star and found $thispart");
		$response_Array = getStar($response_Array);
		$mpart = "tag_open_star";
	}		
    elseif(strpos($thispart,"tag_close_/person2")!==false) // 	swap 1st & 2nd person
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_/person2 and found $thispart");
		$response_Array = person($response_Array,"2");
		$mpart = "tag_close_/person2";
	}
    elseif(strpos($thispart,"tag_close_/person")!==false) //    swap 1st & 3rd person
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_/person and found $thispart");
		$response_Array = person($response_Array,"3");
		$mpart = "tag_close_/person";
	}
    elseif((strpos($thispart,"tag_open_condition name")!==false)&&(strpos($thispart,"value")!==false))
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_condition name + value and found $thispart");
		$response_Array = evaluateCondition($response_Array,"condition","",2);
		$mpart = "tag_open_condition name";
	}
    elseif(strpos($thispart,"tag_open_condition name")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_condition name and found $thispart");
		$response_Array = setConditionName($response_Array);
		$mpart = "tag_open_condition name";
	}
    elseif(strpos($thispart,"tag_open_condition value=")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_condition value and found $thispart");
		$response_Array = evaluateCondition($response_Array,"condition","",1);
		$mpart = "tag_open_condition value";
	}	
    elseif(strpos($thispart,"tag_open_li value")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_li value and found $thispart");
		$response_Array = evaluateCondition($response_Array,"li","",1);
		$mpart = "tag_open_li value";
	}		
    elseif(strpos($thispart,"tag_close_/condition")!==false) 
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/condition and found $thispart");
		$response_Array = sortConditions($response_Array,"condition"); 
		$mpart = "tag_close_/condition";
	}
    elseif((strpos($thispart,"tag_open_li name")!==false)&&(strpos($thispart,"value=")!==false))
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open__li name and found $thispart");
		$response_Array = evaluateCondition($response_Array,"li","",2);
		$mpart = "tag_open_li name";
	}
	elseif(strpos($thispart,"tag_open_thatstar index")!==false) //how do you set the that star
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_thatstar index and found $thispart");
		$response_Array = getThatStar($response_Array);
		$mpart = "tag_open_thatstar index";
	}
    elseif(strpos($thispart,"tag_open_topicstar index")!==false) //and the topic star????
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_topicstar index and found $thispart");
		$response_Array = getTopicStar($response_Array);
		$mpart = "tag_open_topicstar index";
	}
    elseif(strpos($thispart,"tag_close_/gossip")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/gossip and found $thispart");
		$response_Array = setGossip($response_Array);
		$mpart = "tag_close_/gossip";
	}
    elseif(strpos($thispart,"tag_close_/botresponse")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_close_/botresponse and found $thispart");
		$curPos = getCurrentPosition($response_Array);
		$response_Array = unsetAll($response_Array,$curPos,$curPos);
		$response_Array = unsetAll($response_Array,0,0);
		$mpart = "tag_close_/botresponse";
	}	
    elseif(strpos($thispart,"tag_open_learn")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_learn and found $thispart");
		$response_Array = learn($response_Array);
		$mpart = "tag_open_learn";
	}/*
    elseif(strpos($thispart,"tag_open_forget")!==false)
	{
		//runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for tag_open_learn and found $thispart");
		$response_Array = forget($response_Array);
		$mpart = "tag_open_forget";
	}*/
    elseif(strpos($thispart,"date format")!==false)
	{
		runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>looking for date format and found $thispart");
		$response_Array = dateFormat($response_Array);
		$mpart = "date format";
	}		
	else
	{
		
		if(strpos($thispart,"tag_open")!==false)
		{
			$mpart = "ignoring opening tag";
		}
		elseif(strpos($thispart,"tag")!==false)
		{
			runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>ERROR FOUND UN SUPPORTED TAG - $thispart");
			$mpart = "Unsuppported tag";
		}
		else
		{
			runDebug($response_Array,4,"checkthis","<br>Array Name = ".$response_Array['rname']."<br>found $thispart");
			$mpart = "word";
		}
		
	}	
	
	
	//echo "<br/><b>Checking Parts</b> = $thispart = $mpart";
	
	
	
	return $response_Array;
}
?>