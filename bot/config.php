<?php
//-----------------------------------------------------------------------------------------------
//Program-o Version 1.0.4
//PHP MYSQL AIML interpreter
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
//Program-O config file.....
//Please edit the information below before uploading your bot to your server....
//---------------------------------------------------------------
//leave as europe check with the net for the correct timezone for your area

if(function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get"))
{
	@date_default_timezone_set(@date_default_timezone_get());
}
elseif(function_exists("date_default_timezone_set"))
{
	@date_default_timezone_set('Europe/London');
}

//---------------------------------------------------------------

//---------------------------------------------------------------
$dbh = "localhost"; //server location (localhost should be ok for this)
$dbn = "qbot"; //database name/prefix
$dbu = "root"; //database username
$dbp = ""; //database password

//---------------------------------------------------------------

//---------------------------------------------------------------
//Number of lines from conversation to show
//---------------------------------------------------------------
$convoLines = 1;

//---------------------------------------------------------------
//The usercontants array list the parts of the conversation we do not want to overwrite when recursing through AIML
//---------------------------------------------------------------
$userConstants = array("lookingfor","matchtemplate","matchpattern","matchthatpattern","orignalinput","bot","responseparts","totalparts","currentposition","thispart","partnumber","reset","that","input","answer","who","rname","randomanswer","randomchoice");

//---------------------------------------------------------------
//catch repeats here
//---------------------------------------------------------------
$catchrepeats = 0; //if you want the bot to ask the user to stop repeating themselves set this to 1 if not 0

//---------------------------------------------------------------
//bot id - leave this as 1 
//---------------------------------------------------------------
$thisbot = 1;

?>
