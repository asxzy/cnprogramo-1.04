<?PHP
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
//db config file
//you might want to make thise different to the program-o chatbot user as you need privs to insert, delete, create tables.
if(function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get"))
{
	@date_default_timezone_set(@date_default_timezone_get());
}
elseif(function_exists("date_default_timezone_set"))
{
	@date_default_timezone_set('Europe/London');
}
$dbh = "localhost"; //server location (localhost should be ok for this)
$dbn = ""; //database name/prefix
$dbu = ""; //database username
$dbp = ""; //database password


function openDB()
{
	global $dbh,$dbp,$dbu,$dbn;
	$conn = mysql_connect($dbh,$dbu,$dbp,$dbn)or die(mysql_error());
	mysql_query("SET NAMES UTF8");
	mysql_query("use ".$dbn);
	return $conn;
}
//-----------------------------------------------------------
//formatchinese($str) add a space into each Chinese character
//-----------------------------------------------------------
function formatchinese($str){
	if(preg_match_all("/[\x{4e00}-\x{9fa5}]{1}/u",$str,$out)){
		$tmp=$str;
		foreach($out[0] as $value){
			$str = preg_replace("/".$value."/",$value." ",$str);
			$str = preg_replace("/\s+/"," ",$str);
		}
	}
	return trim(str_replace("*","* ",$str));
}
//-----------------------------------------------------------------
//restorechinese($str) remove the space after each Chinse character
//-----------------------------------------------------------------
function restorechinese($str){
	if(preg_match_all("/[\x{4e00}-\x{9fa5}]{1} /u",$str,$out)){
		foreach($out[0] as $value){
			$str = preg_replace("/$value/",trim($value),$str);
		}
	}
	return trim($str);
}
//leo modify
?>