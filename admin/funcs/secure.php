<?PHP
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
session_start();
if((!isset($_SESSION['poadmin']['uid'])) || ($_SESSION['poadmin']['uid']==""))
{
	header("location: ../index.php?msg=Session timed out");
}
else
{
	$name = $_SESSION['poadmin']['name'];
	$ip = $_SESSION['poadmin']['ip'];
	$last = $_SESSION['poadmin']['lastlogin'];
	$lip = $_SESSION['poadmin']['lip'];
	$llast = $_SESSION['poadmin']['llastlogin'];
}
?>