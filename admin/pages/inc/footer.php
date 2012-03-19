<?PHP
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
$admess = "You are logged in as: $name from $ip since: $last";
$admess .= "<br/>You last logged in from $lip on $llast";

echo "</div>
		<div id=\"footer\"><p>&copy; ".date("Y")." My Program-O<br/>$admess</p></div>
	</div>
	</body>
</html>";
?>