<?PHP
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
function searchForm()
{
	$form = "
	<div id=\"container\">
		<fieldset> 
    		<legend>Search</legend><br/>
				<form name=\"search\" action=\"search.php\" method=\"post\">
					<div class=\"fm-req\">
						<input type=\"text\" id=\"search\" name=\"search\" />
					
						<input type=\"submit\" name=\"action\" id=\"action\" value=\"search\">
					</div>
				</form>
			</fieldset>
		</div>
		<hr>";
	

	return $form;
	
}

function delAIML($id)
{
	global $dbn;
	$dbconn = openDB();
	
	if($id!="")
	{
		$sql = "DELETE FROM `$dbn`.`aiml` WHERE `id` = $id LIMIT 1";
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


function runSearch()
{
	//db globals
	global $dbn;
	$dbconn = openDB();
	$i=0;
	
	$search = mysql_escape_string(trim(formatchinese($_POST['search'])));
	
	if($search != "")
	{
		$sql = "SELECT * FROM `$dbn`.`aiml` WHERE `topic` LIKE '%$search%' OR `filename` LIKE '%$search%' OR 
		`pattern` LIKE '%$search%' OR `template` LIKE '%$search%' OR `thatpattern` LIKE '%$search%' LIMIT 50";
	
		$result = mysql_query($sql,$dbconn)or die(mysql_error());
	
		$htmltbl = "<table>
						<thead>
							<tr>
								<th class=\"sortable\">Topic</th>
								<th class=\"sortable\">Previous Bot Response</th>
								<th class=\"sortable\">User Input</th>
								<th class=\"sortable\">Bot Response</th>
								<th class=\"sortable\">Filename</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>";
		
		while($row=mysql_fetch_array($result))
		{
			$i++;
			
			$filename = $row['filename'];
			$topic = restorechinese($row['topic']);
			$pattern = restorechinese($row['pattern']);
			$thatpattern = restorechinese($row['thatpattern']);
			$template = restorechinese(htmlentities($row['template'],ENT_QUOTES,"UTF-8"));
			$id = $row['id'];
			
			$action = "<a href=\"search.php?action=edit&id=$id\"><img src=\"Img/edit.png\" border=0 width=\"15\" height=\"15\" /></a>
						<a href=\"search.php?action=del&id=$id\" onclick=\"return confirm('Do you really want to delete this AIML record?You will not be able to undo this!')\";><img src=\"Img/del.png\" border=0 width=\"15\" height=\"15\"/></a>";
			
			$htmltbl .= "<tr valign=top>
								<td>$topic</td>
								<td>$thatpattern</td>
								<td>$pattern</td>
								<td>$template</td>
								<td>$filename</td>
								<td align=center>$action</td>
							</tr>";
		}
	
		$htmltbl .= "</tbody></table>";
		$search = restorechinese($search);
		if($i == 50)
		{
			$msg = "Found more than 50 results for '<b>$search</b>', please refine your search further";
			
		}
		elseif($i == 0)
		{
			$msg = "Found 0 results for '<b>$search</b>', please try again";
			$htmltbl="";
			
		}
		else
		{
			$msg = "Found $i results for '<b>$search</b>'";
			
		}
	
		$htmlresults = "<div id=\"pTitle\">".$msg."</div>".$htmltbl;
	}
	else
	{
		$htmlresults = "<div id=\"errMsg\">Please enter a search term.</div>"; 
	}
	mysql_close($dbconn);
	
	return $htmlresults;
	
}


function editAIMLForm($id)
{
	
		//db globals
	global $dbn;
	$dbconn = openDB();
		
	$sql = "SELECT * FROM `$dbn`.`aiml` WHERE `id` = '$id' LIMIT 1";
	$result = mysql_query($sql,$dbconn)or die(mysql_error());
	
	$row=mysql_fetch_array($result);
	
		
		$topic = restorechinese($row['topic']);
		$pattern = restorechinese($row['pattern']);
		$thatpattern = restorechinese($row['thatpattern']);
		$template = restorechinese(htmlentities($row['template'],ENT_QUOTES,"UTF-8"));
		$filename = $row['filename'];
		$id = $row['id'];
		
	
	
	$form = "<div id=\"container\"><fieldset> 
    <legend>Update AIML</legend><br/>
	
	<form name=\"teach\" action=\"search.php\" method=\"post\">
			
			<div class=\"fm-opt\">
				<label for=\"topic\">Topic: </label>
				<input type=\"text\" id=\"topic\" name=\"topic\" value=\"$topic\" />
			</div>		
			
			<div class=\"fm-opt\">
				<label for=\"thatpattern\">Previous Res: </label>
				<input type=\"text\" id=\"thatpattern\" name=\"thatpattern\"  value=\"$thatpattern\" />
			</div>	
			
			<div class=\"fm-opt\">
				<label for=\"pattern\">User Input: </label>
				<input type=\"text\" id=\"pattern\" name=\"pattern\"  value=\"$pattern\"/>
			</div>
		
			<div class=\"fm-opt\">
				<label for=\"template\">Bot Response: </label>
				<input type=\"text\" id=\"template\" name=\"template\"  value=\"$template\"/>
			</div>
			
						<div class=\"fm-opt\">
				<label for=\"filename\">Filename: </label>
				<input type=\"text\" id=\"filename\" name=\"filename\"  value=\"$filename\"/>
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
	$filename = mysql_escape_string(trim(formatchinese($_POST['filename'])));
	$pattern = strtoupper(mysql_escape_string(trim(formatchinese($_POST['pattern']))));
	$thatpattern = strtoupper(mysql_escape_string(trim(formatchinese($_POST['thatpattern']))));
	$topic = strtoupper(mysql_escape_string(trim(formatchinese($_POST['topic']))));
	$id = trim($_POST['id']);


	if(($template == "")||($pattern== "")||($id==""))
	{
		$msg = "<div id=\"errMsg\">Please make sure you have entered a user input and bot response.</div>"; 
	}
	else
	{
		$sql = "UPDATE `$dbn`.`aiml` SET `pattern` = '$pattern',`thatpattern`='$thatpattern',`template`='$template',`topic`='$topic',`filename`='$filename' WHERE `id`='$id' LIMIT 1";
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