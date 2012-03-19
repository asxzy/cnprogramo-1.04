<?PHP
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
error_reporting(E_ALL);
include("funcs/config.php");

//INSTRUCTIONS//--------------------------------------------------------------------------------------
$step_one = "
<h2>Welcome to the My Program-O installer.</h2>
<h3>Step 1/4</h3>
<p>Please make sure that you have installed the Program-O chatbot before you install My Program-O.</p>
<p>Complete steps 1-5 below before proceeding</p>
<ol>
  <li>You will need access to a server that runs PHP and MySQL.</li>
  <li>Unzip and upload all the folders/files to the server.</li>
  <li>Find the folder called 'aiml' (inside the admin folder) make sure this has read/write priveldges (CHMOD 755).</li>
  <li>Create a MySQL user with privileges to select, insert, update, delete and create tables.</li>
  <li>Find the file called funcs/config.php and enter the MySQL username, password, host and database name.</li>
</ol>
<p>Once you have done steps 1-5 above <a href=\"install_myprogramo.php?step=2\">click here to proceed</a>. </p>";

$step_two = "
<h2>Welcome to the My Program-O installer.</h2>
<h3>Step 2/4</h3>
<p>Creating the admin users table</p>
";

$step_three = "
<h2>Welcome to the My Program-O installer.</h2>
<h3>Step 3/4</h3>
<p>Add a username and password. You will use this to access the admin area so don't forget it.</p>
";

$step_four = "
<h2>Welcome to the My Program-O installer.</h2>
<h3>Step 4/4</h3>
<p>Checking the privs on the aiml directory.</p>
";

//INSTRUCTIONS FUNCTIONS//--------------------------------------------------------------------------------


function checkprivs()
{
	$msg="";
	$ourFileName = "aiml/testFile.txt";
	$ourFileHandle = fopen($ourFileName, 'w');
	if(!$ourFileHandle)
	{
		$msg = "<p>There was an error with the privileges on the AIML folder.<br/>You need to chmod it to 755 <a href=\"install_myprogramo.php?step=4\">and then try again</a>.</p>";	
	}
	else
	{
		fclose($ourFileHandle);
		$myFile = "aiml/testFile.txt";
		unlink($myFile);
	}
	
	if($msg == "")
	{
		$msg = "<p>Installation complete please do the following:
						<ul>
						<li>Remove the create table privilege for the MySQL user (these are no longer needed).</li>
						<li>Delete this file - to stop dodgy hackers from over-writing your installation.</li>
						</ul>
						<a href=\"index.php\">Log in to the admin area</a><p>";	
	}
	echo $msg;
}


function adduser()
{
	global $dbn;
	$dbconn = openDB();
	
	$u = mysql_escape_string(strip_tags(trim($_POST['u'])));
	$p = mysql_escape_string(strip_tags(trim($_POST['p'])));
	$cp = mysql_escape_string(strip_tags(trim($_POST['cp'])));
	
	if( ($u=="") || ($p=="") || ($cp=="") || ($p!=$cp) )
	{
			$msg = "<p>There was an error when trying to add the user to the table.<br/>You need to enter a username and password. Make sure that your password and confirm password match <a href=\"install_myprogramo.php?step=3\">and then try again</a>.</p>";	
	}
	else
	{
			$addadmin = "INSERT INTO  `$dbn`.`myprogramo` (
			`id` ,
			`uname` ,
			`pword` ,
			`lastip` ,
			`lastlogin`
			)
			VALUES (
			NULL ,  '".$u."', '".MD5($p)."' ,  'First time', 
			CURRENT_TIMESTAMP
			);";
		
			$result = mysql_query($addadmin, $dbconn);
			
			if(!mysql_error())
			{
				$msg = "<p>The admin user has been added. <a href=\"install_myprogramo.php?step=4\">click here to proceed</a>. </p>";
			}
			else
			{
				$msg = "<p>There was an error when trying to add the user to the table.<br/>Check that your user has privileges and that the username does not already exist <a href=\"install_myprogramo.php?step=3\"> and then try again</a>.<br/>MySQL says: ".mysql_error()."</p>";
			}

	}

	
	echo $msg;
	
	mysql_close($dbconn);
}

function create_users_table()
{
	global $dbn;
	$dbconn = openDB();
	
	
	$installsql = "CREATE TABLE  `$dbn`.`myprogramo` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`uname` VARCHAR( 10 ) NOT NULL ,
`pword` VARCHAR( 255 ) NOT NULL ,
`lastip` VARCHAR( 25 ) NOT NULL ,
`lastlogin` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
UNIQUE (
`uname`
)
)";

	$result = mysql_query($installsql, $dbconn);
	
	if(!mysql_error())
	{
		$msg = "<p>The users table has been created. <a href=\"install_myprogramo.php?step=3\">click here to proceed</a>. </p>";
	}
	else
	{
		$msg = "<p>There was an error when trying to create the table.<br/>Check that your user has privileges and that the table does not already exist <a href=\"install_myprogramo.php?step=2\">and then try again</a>.<br/>MySQL says: ".mysql_error()."</p>";
	}
	
	echo $msg;
	
	mysql_close($dbconn);
	
}

function adduserform()
{
	$form = "<fieldset>
<legend>Add your admin username and password</legend>
<form name=\"adduser\" method=\"post\" action=\"install_myprogramo.php?step=3a\">
<label for=\"u\">username:</label>
<input type=\"text\" name=\"u\" id=\"u\"  MAXLENGTH=10/>
<br /><br />
<label type=\"p\">password:</label>
<input type=\"password\" name=\"p\" id=\"p\"  MAXLENGTH=10/>
<br />
<label type=\"cp\">confirm<br/>password:</label>
<input type=\"password\" name=\"cp\" id=\"cp\" MAXLENGTH=10/>
<br /><br />
<input type=\"submit\" id=\"submit\" name=\"submit\" value=\"submit\" />
</form></fieldset>";

echo $form;

}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>My Program-O Installer</title>
</head>
<body>

<?php
if(isset($_GET['step']))
{
	$step = $_GET['step'];
}
else
{
	$step = "";
}
	switch($step)
	{
		case '1':
			echo $step_one;
			break;
		case '2':
			echo $step_two;
			create_users_table();
			break;
		case '3':
			echo $step_three;
			adduserform();
			break;
		case '3a':
			echo $step_three;
			adduser();
			break;
		case '4':
			echo $step_four;
			checkprivs();
			break;
		default:
			echo $step_one;
			break;
	}
?>
</body>
</html>