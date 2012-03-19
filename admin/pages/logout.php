<?php
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
session_start();

$_SESSION = array();

if(isset($_COOKIE[session_name()])) 
{
    setcookie(session_name(), '', time()-42000, '/');
}
session_destroy();
header("location: ../index.php?msg=You have logged out");
?>