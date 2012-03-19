<?php
//-----------------------------------------------------------------------------------------------
//Program-o Version 1.0.4
//PHP MYSQL AIML interpreter
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
//debugging.php
//contains simple functions for debugging the bot
//-----------------------------------------------------------------------------------------------
//error handler to get the errors emailed to you for debugging
//set_error_handler("myHandler"); //comment this out if you do not want to be emailed error messages your bot makes...
error_reporting(0); //uncomment this if you want to hide all errors your bot code may make from the user
//error_reporting(E_ALL); //uncomment this if you want to show all errors to the user

//debuglevels
$debugMode = 0; // special turn on and off to test this and that
//$debugMode = 0; // show nothing
//$debugMode = 1; // show everything...
//$debugMode = 2; // show sql errors
//$debugMode = 3; // show gerenal debug info
//$debugMode = 4; // show naviagation only... for tracing which tag the program is examining
//$debugMode = 5; // show everything and show the array at this point

//alter this for your location needed for debuging only if running two versions live/dev
$location = "live";
//if you wish the program to email you you errors instead of displaying them set the flag below to 1 if not then set to 0
$mailerrors = 0;
//if you have chosen to report errors to your email address set the address here
$debugemail = "youremail@yourdomain.com";
//to report on long scripts set the seconds here and any script that take longer than nSeconds will be emailed if mailerrors (flag=1)
$longScriptSeconds = 7;

//-----------------------------------------------------------------------------------------------
//DO NOT EDIT BELOW THIS LINE
//-----------------------------------------------------------------------------------------------
//debugMessage()
//Just indicate which debug level we are in ... if any
//-----------------------------------------------------------------------------------------------
function debugMessage()
{
	global $debugMode;
	if($debugMode!=0) //0 = hide all debugging
	{
		echo "<h4>Debug Level: $debugMode</h4>";
		switch ($debugMode)
		{
			case 1:
				echo "Show All";
				break;
			default:
				echo "Show Special";
				break;
		}
		echo "<hr>";
	}
	
}

//-------------------------------------------------------------------------
//runDebug($response_Array,$level,$functionName,$info)
//function to display information depending on the debug leve
//-------------------------------------------------------------------------
function runDebug($response_Array,$level,$functionName,$info)
{
	global $debugMode;
	
	if($debugMode == 0) //show nothing
	{}
	elseif($debugMode == 1) //show everything
	{
		echo "<hr><br><br>FUNCTION: ".$functionName."</b>";
		echo $info;
		echo "<br>".date("H:i:s");
		echo "<hr>";
	}
	elseif(($debugMode == $level)&&($level==5)) //show only if we have set this to equal...
	{
		echo "FUNCTION: ".$functionName;
		echo $info;
		echo "<br>".date("H:i:s");
		displayArray($response_Array);
		echo "<hr>";
	}
	elseif($debugMode == $level) //show only if we have set this to equal...
	{
		echo "FUNCTION: ".$functionName."</b>";
		echo $info;
		echo "<br>".date("H:i:s");
		echo "<hr>";
	}
}
//-----------------------------------------------------------------------------------------------
//displayArray($response_Array)
//Sometime when debugging we want to show the response_array parts....
//this will only be triggered if the debug level is 5
//-----------------------------------------------------------------------------------------------
function displayArray($response_Array)
{
	//these are the parts we want to show..... add more if you want to show more array parts from the response array
	$viewList=array('responseparts','rname','lookingfor','answer','anagramanswer','htmltemplate','topic');
	if($response_Array!="")
	{
		echo "<pre>"; //format
		foreach($viewList as $u => $item) //loop
		{
			if(isset($response_Array[$item])) //if she
			{
				echo "<br>$item=<br>"; //show
				print_r($response_Array[$item]);
			}
		}
		echo "<pre>";
	}
	else
	{
		echo "<br>array not set";
	}
}

function emailthis($response_Array,$subject)
{
	global $debugemail,$mailerrors;
	
	if($mailerrors==1)
	{
		if(is_array($response_Array))
		{
			$body = "";
			foreach($response_Array as $item => $r)
			{
				if(is_array($r))
				{
					$body .= "\n\t[$item]";
				
						foreach($r as $i => $t)
						{
							$body .= "\n\t\t[$i] = $t";
						}
				}
				else
				{
					$body .= "\n\t[$item] = $r";
				}
			}
			$body .= "";	
		}
		else
		{
			$body = $response_Array;
		}	
			$to      = $debugemail;
			
			$headers = 'From: '.$debugemail . "\r\n" .
				'Reply-To: '.$debugemail . "\r\n" .
				'X-Mailer: PHP/' . phpversion();
			
		mail($to, $subject, $body, $headers);
	}
}

function endTime($response_Array,$time_start)
{
	global $location,$mailerrors,$longScriptSeconds;
	
	if($mailerrors==1)
	{
	
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		if($time>$longScriptSeconds)
		{
			emailthis($response_Array,"$location - Long Script = $time seconds");
		}
	}
}

function myHandler($code, $msg, $file, $line, $context) 
{
    // print error page
	global $location,$debugemail;
    // email error to admin
    $body = "$msg at $file ($line), timed at " . date ("d-M-Y h:i:s", mktime());
    $body .= "\n\n<pre>" . print_r($context, TRUE) ."</pre>";
    // log error to file, with context
    $logData = date("d-M-Y h:i:s", mktime()) . ", $code, $msg, $line, $file\n";
    //file_put_contents("web.log", $logData, FILE_APPEND);

   	$subject = "$location - $msg at $file ($line)"; 
	$to      = $debugemail;
		
		$headers = 'From: '.$debugemail . "\r\n" .
			'Reply-To: '.$debugemail . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		
	mail($to, $subject, $body, $headers);
}

?>