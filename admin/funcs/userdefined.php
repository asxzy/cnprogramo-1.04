<?PHP
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
function delAIML($id)
{
	global $dbn;
	$dbconn = openDB();
	
	if($id!="")
	{
		$sql = "DELETE FROM `$dbn`.`aiml_userdefined` WHERE `id` = $id LIMIT 1";
		//echo $sql;
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
	
		if(!$result)
		{
			$msg = "<div id=\"errMsg\">Error AIML couldn't be deleted - no changes made.</div>"; 
		}
		else
		{
			$msg = "<div id=\"successMsg\">AIML has been deleted.</div>"; 
		}
	}
	else
	{
		$msg = "<div id=\"errMsg\">Error AIML couldn't be deleted - no changes made.</div>"; 
	}
	mysql_close($dbconn);
	return $msg;
}
function PassAIML($id)
{
	global $dbn;
	$dbconn = openDB();
	if($id!="")
	{
		$sql = "SELECT * FROM `$dbn`.`aiml_userdefined` WHERE `id` = '$id' LIMIT 1";
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
		$row=mysql_fetch_array($result);
		$pattern = $row['pattern'];
		$template = htmlentities($row['template'],ENT_QUOTES,"UTF-8");
		$sql = "INSERT INTO `$dbn`.`aiml` (`id`,`aiml`,`pattern`,`thatpattern`,`template`,`topic`,`filename`) VALUES (NULL,'','$pattern','','$template','','USER ADDED')";
		mysql_query($sql,$dbconn)or die(mysql_error());
		$sql = "DELETE FROM `$dbn`.`aiml_userdefined` WHERE `id` = $id LIMIT 1";
		//echo $sql;
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
		if(!$result)
		{
			$msg = "<div id=\"errMsg\">Error AIML couldn't be passed - no changes made.</div>"; 
		}
		else
		{
			$msg = "<div id=\"successMsg\">AIML has been passed.</div>"; 
		}
	}
	else
	{
		$msg = "<div id=\"errMsg\">Error AIML couldn't be passed - no changes made.</div>"; 
	}
	mysql_close($dbconn);
	return $msg;
}
function userdefinedlist()
{
	//db globals
	global $dbn;
	$dbconn = openDB();
	$i=0;
		$sql = "SELECT * FROM `$dbn`.`aiml_userdefined`";
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
		$htmltbl = "<table>
						<thead>
							<tr>
								<th class=\"sortable\">User Input</th>
								<th class=\"sortable\">Bot Response</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>";
		while($row=mysql_fetch_array($result))
		{
			$i++;
			$pattern = restorechinese($row['pattern']);
			$template = restorechinese(htmlentities($row['template'],ENT_QUOTES,"UTF-8"));
			$id = $row['id'];
			$action = "<a href=\"userdefined.php?action=pass&id=$id\">Pass</a>&nbsp;<a href=\"userdefined.php?action=edit&id=$id\"><img src=\"Img/edit.png\" border=0 width=\"15\" height=\"15\" /></a>
						<a href=\"userdefined.php?action=del&id=$id\" onclick=\"return confirm('Do you really want to delete this AIML record?You will not be able to undo this!')\";><img src=\"Img/del.png\" border=0 width=\"15\" height=\"15\"/></a>";
			$htmltbl .= "<tr valign=top>
								<td>$pattern</td>
								<td>$template</td>
								<td align=center>$action</td>
							</tr>";
		}
		$htmltbl .= "</tbody></table>";
		if($i == 50)
		{
			$msg = "Found more than 50 results";
			
		}
		elseif($i == 0)
		{
			$msg = "Found 0 results";
			$htmltbl="";
			
		}
		else
		{
			$msg = "Found $i results";
			
		}
	
		$htmlresults = "<div id=\"pTitle\">".$msg."</div>".$htmltbl;

	mysql_close($dbconn);
	return $htmlresults;
}


function editAIMLForm($id)
{
	
		//db globals
	global $dbn;
	$dbconn = openDB();
		
	$sql = "SELECT * FROM `$dbn`.`aiml_userdefined` WHERE `id` = '$id' LIMIT 1";
	$result = mysql_query($sql,$dbconn)or die(mysql_error());
	
	$row=mysql_fetch_array($result);

		$pattern = restorechinese($row['pattern']);
		$template = restorechinese(htmlentities($row['template'],ENT_QUOTES,"UTF-8"));
		$id = $row['id'];

	$form = "<div id=\"container\"><fieldset> 
    <legend>Update AIML</legend><br/>
	
	<form name=\"teach\" action=\"userdefined.php\" method=\"post\">
						
			<div class=\"fm-opt\">
				<label for=\"pattern\">User Input: </label>
				<input type=\"text\" id=\"pattern\" name=\"pattern\"  value=\"$pattern\"/>
			</div>
		
			<div class=\"fm-opt\">
				<label for=\"template\">Bot Response: </label>
				<input type=\"text\" id=\"template\" name=\"template\"  value=\"$template\"/>
			</div>
	</fieldset>
			<div id=\"fm-submit\" class=\"fm-req\">
			<input type=\"hidden\" name=\"id\" id=\"id\" value=\"$id\">
				<input type=\"submit\" name=\"action\" id=\"action\" value=\"update\">
			</div>
			</form></div>";
	
mysql_close($dbconn);
	return $form;
	
}

function updateAIML()
{
	//db globals
	global $dbn;
	$dbconn = openDB();
	
	$template = mysql_escape_string(trim(formatchinese($_POST['template'])));
	$pattern = strtoupper(mysql_escape_string(trim(formatchinese($_POST['pattern']))));
	$id = trim($_POST['id']);


	if(($template == "")||($pattern== "")||($id==""))
	{
		$msg = "<div id=\"errMsg\">Please make sure you have entered a user input and bot response.</div>"; 
	}
	else
	{
		$sql = "UPDATE `$dbn`.`aiml_userdefined` SET `pattern` = '$pattern',`template`='$template' WHERE `id`='$id' LIMIT 1";
		//echo $sql;
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
		
		if($result)
		{
			$msg = "<div id=\"successMsg\">AIML Updated.</div>"; 
		}
		else
		{
			$msg = "<div id=\"errMsg\">There was an error updating the AIML - no changes made.</div>"; 
		}
	}
	mysql_close($dbconn);
	
	return $msg;
	
}
?>