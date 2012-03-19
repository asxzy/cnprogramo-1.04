<?php
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
$XmlEntities = array(
    '&amp;'  => '&',
    '&lt;'   => '<',
    '&gt;'   => '>',
    '&apos;' => '\'',
    '&quot;' => '"',
);


$g_tagName = null;
$aiml_sql = "";
$pattern_sql = "";
$that_sql = "";
$template_sql = "";
$insert_sql = "";
//$file = " ";
$file = "";
$full_path = "";
$cat_counter = 0;



function parseAIML()
{
	global $debugmode, $file, $g_tagName, $aiml_sql, $topic_sql, $pattern_sql, $that_sql, $template_sql, $file, $cat_counter, $insert_sql, $dbn;
	$dbconn = openDB();
	
	$file = $_FILES['aimlfile']['name'];
	$target_path = "../aiml/";
	$full_path = "../aiml/" . $file;
	$target_path = $target_path . basename( $_FILES['aimlfile']['name']); 

	if(move_uploaded_file($_FILES['aimlfile']['tmp_name'], $target_path)) 
	{
    	$msg = "The file ".  basename( $_FILES['aimlfile']['name']). " has been uploaded";
	} 
	else
	{
		$msg = "There was an error uploading the file, please try again!";
		return $msg;
		exit();
	}
	
	
// delete previous patterns from this file
$sql = "DELETE FROM `$dbn`.`aiml`  WHERE `filename` = '$file'";
if(($debugmode==1)||($debugmode==2)) {
	mysql_query($sql,$dbconn)or die(mysql_error());
} else {
	mysql_query($sql,$dbconn);
}

// set up XML parser
$xml_parser = xml_parser_create();

xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
xml_set_element_handler($xml_parser, "startElement", "endElement");
xml_set_character_data_handler($xml_parser, "characterData");
if (!($fp = fopen($full_path, "r"))) {
    die("could not open XML input");
}

while ($data = fread($fp, 4096)) {
    if (!xml_parse($xml_parser, $data, feof($fp))) {
        die(sprintf("XML error: %s at line %d",
        xml_error_string(xml_get_error_code($xml_parser)),
        xml_get_current_line_number($xml_parser)));
    }
}

//echo "INSERT INTO `aiml` (`id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`) VALUES\n";
//echo $insert_sql;
//echo ";\n";

// insert remaining patterns into the database
$sql = "INSERT INTO `$dbn`.`aiml` (`id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`)	VALUES " . $insert_sql . ";";

mysql_query($sql,$dbconn);

xml_parser_free($xml_parser);

mysql_close($dbconn);

$msg .= "<br/>".$file . ": AIML file loaded";
return $msg;

}

function uploadAIMLForm()
{
	$form = "<div id=\"container\">
				<fieldset> <br/>
   					<form name=\"upload\" enctype=\"multipart/form-data\"  action=\"upload.php\" method=\"post\">
					<div class=\"fm-opt\">
							<label for=\"aimlfile\">File: </label>	
							<input type=\"file\" id=\"aimlfile\" name=\"aimlfile\" />
					</div>
					<div id=\"fm-submit\" class=\"fm-req\">
						<input type=\"submit\" name=\"action\" id=\"action\" value=\"upload\">
					</div>
					</form>
				</fieldset>
				</div>";
	

	return $form;
	
}

function startElement( $parser, $tagName, $attr )
{
	global $g_tagName, $aiml_sql, $topic_sql, $template_sql, $XmlEntities;

	if ( $tagName == 'TOPIC' ) {
		foreach ($attr as $Key => $Value) {
			if ($Key == "NAME") {
				$topic_sql = strtr(trim($Value), $XmlEntities);
			}
        }
	} else if ($tagName == "CATEGORY" ) {
        $aiml_sql = "<" . strtolower($tagName) . ">";
        $g_tagName = $tagName;
	} else if ($tagName == "PATTERN" ) {
        $aiml_sql .= "<" . strtolower($tagName) . ">";
        $g_tagName = $tagName;
	} else if ($tagName == "THAT" ) {
        if ($g_tagName != "TEMPLATE") {
			$aiml_sql .= "<" . strtolower($tagName) . ">";
            $g_tagName = $tagName;
        } else {
            $tag = "<" . strtolower($tagName);
            foreach ($attr as $Key => $Value) {
               $tag .= " " . strtolower($Key) . "=\"" . strtr(trim($Value), $XmlEntities) . "\"";
            }
            $tag .= "/>";
			$aiml_sql .= $tag;
            $template_sql .= $tag;
        }
	} else if ($tagName == "TEMPLATE" ) {
        $aiml_sql .= "<" . strtolower($tagName) . ">";
        $g_tagName = $tagName;
	} else {
        $tag = "<" . strtolower($tagName);
        foreach ($attr as $Key => $Value) {
           $tag .= " " . strtolower($Key) . "=\"" . strtr(trim($Value), $XmlEntities) . "\"";
        }

        if ($tagName == "STAR" || $tagName == "BOT" || $tagName == "GET" || $tagName == "BR" || $tagName == "SR" || $tagName == "THATSTAR" || $tagName == "TOPICSTAR" || $tagName == "SIZE" || $tagName == "DATE" || $tagName == "ID" || $tagName == "VERSION") {
           $tag .= "/";
        }
        $tag .= ">";
        $aiml_sql .= $tag;
	    if ($g_tagName == "TEMPLATE" ) {
            $template_sql .= $tag;
        }
	}
}

function endElement( $parser, $tagName )
{
    	//db globals
	
	$dbconn = openDB();
	
	global $g_tagName, $aiml_sql, $topic_sql, $pattern_sql, $that_sql, $template_sql, $file, $cat_counter, $insert_sql, $dbn;

    if ( $tagName == 'TOPIC' ) {
      	$topic_sql = null;
	} else if ($tagName == "CATEGORY" ) {
        $aiml_sql .= "</" . strtolower($tagName) . ">";
        $g_tagName = null;
        $aiml_sql = str_replace("> <", "><", $aiml_sql);
        $aiml_sql = str_replace("<person2></person2>", "<person2/>", $aiml_sql);
        $aiml_sql = str_replace("<person2><star/></person2>", "<person2/>", $aiml_sql);
        $aiml_sql = str_replace("<person></person>", "<person/>", $aiml_sql);
        $aiml_sql = str_replace("<person><star/></person>", "<person/>", $aiml_sql);
        $template_sql = str_replace("> <", "><", $template_sql);
        $template_sql = str_replace("<person2></person2>", "<person2/>", $template_sql);
        $template_sql = str_replace("<person2><star/></person2>", "<person2/>", $template_sql);
        $template_sql = str_replace("<person></person>", "<person/>", $template_sql);
        $template_sql = str_replace("<person><star/></person>", "<person/>", $template_sql);
//            echo "AIML:" . $aiml_sql . "\n";
//            echo "PATTERN:" . $pattern_sql . "\n";
//            echo "THAT:" . $that_sql . "\n";
//            echo "TEMPLATE:" . $template_sql . "\n";
//            echo "TOPIC:" . $topic_sql . "\n";


        if ($cat_counter > 0) {
        	$insert_sql .= ",\n";
        }
        $cat_counter++;
        $insert_sql .= "(null, '" . $aiml_sql . "', '" . formatchinese($pattern_sql) . "', '" . formatchinese($that_sql) . "', '" . formatchinese($template_sql) . "', '" . formatchinese($topic_sql) . "', '" . $file . "')";
        if ($cat_counter >= 200) {
//	echo "INSERT INTO `aiml` (`id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`) VALUES\n";
//	echo $insert_sql;
//	echo ";\n";

        	$sql = "INSERT INTO `$dbn`.`aiml` (`id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`)	VALUES " . $insert_sql . ";";

        	mysql_query($sql,$dbconn);
	        
		$insert_sql = "";
	        $cat_counter = 0;
        }

        $aiml_sql = "";
        $pattern_sql = "";
        $that_sql = "";
        $template_sql = "";
	} else if ($tagName == "PATTERN" ) {
            $aiml_sql .= "</" . strtolower($tagName) . ">";
            $g_tagName = null;
	} else if ($tagName == "THAT" ) {
            if ($g_tagName != "TEMPLATE") {
	            $aiml_sql .= "</" . strtolower($tagName) . ">";
	            $g_tagName = null;
	        }
	} else if ($tagName == "TEMPLATE" ) {
            $aiml_sql .= "</" . strtolower($tagName) . ">";
            $g_tagName = null;
    } else {
        $tag = "</" . strtolower($tagName) . ">";
        if ($tagName == "STAR" || $tagName == "BOT" || $tagName == "GET" || $tagName == "BR" || $tagName == "SR" || $tagName == "THATSTAR" || $tagName == "TOPICSTAR" || $tagName == "SIZE" || $tagName == "DATE" || $tagName == "ID" || $tagName == "VERSION") {
            $tag = "";
        }
        $aiml_sql .= $tag;
	    if ($g_tagName == "TEMPLATE" ) {
            $template_sql .= $tag;
        }
	}
	
	mysql_close($dbconn);
}

function characterData( $parser, $text )
{
    global $g_tagName, $aiml_sql, $pattern_sql, $that_sql, $current_topic, $template_sql;

    if ($g_tagName == 'PATTERN' ) {
        $aiml_sql .= strtoupper($text);
        $pattern_sql .= strtoupper($text);
	} else if ($g_tagName == 'THAT' ) {
        $aiml_sql .= strtoupper($text);
        $that_sql = strtoupper($text);
	} else if ($g_tagName == 'TEMPLATE' ) {
		$text = str_replace("'", "''", $text);
        $aiml_sql .= $text;
        $template_sql .= $text;
	} else {
		$text = str_replace("'", "''", $text);
        $aiml_sql .= $text;
	    if ($g_tagName == "TEMPLATE" ) {
            $template_sql .= $text;
        }
	}
}


function showhelp()
{
$showhelp = "<div>
  <b class=\"hintbox\">
  <b class=\"hintbox1\"><b></b></b>
  <b class=\"hintbox2\"><b></b></b>
  <b class=\"hintbox3\"></b>
  <b class=\"hintbox4\"></b>
  <b class=\"hintbox5\"></b></b>

  <div class=\"hintboxfg\">
  
  		<h3>Help...</h3>				
			
			<dd> If your aiml file will not upload you may want to check the following.</dd>
   			<dd>--Make sure the folder you are trying to upload to has read/write privs on it (CHMOD 755)</dd>
			<dd>--Remove any comments from the aiml</dd>
   			<dd>--Replace everything above the first &lt;cattegory&gt; with a simple &lt;aiml&gt; tag</dd>
   <br/>--You can find alot of information on the net about writing well formed aiml. <br/>--If you want to learn more about writing AIML <a href=\"http://www.alicebot.org/documentation/aiml-primer.html\" target=\"_new\" class=\"hbox\">start here</a> 
   </div>

  <b class=\"hintbox\">
  <b class=\"hintbox5\"></b>
  <b class=\"hintbox4\"></b>
  <b class=\"hintbox3\"></b>
  <b class=\"hintbox2\"><b></b></b>
  <b class=\"hintbox1\"><b></b></b></b>
</div>";

return $showhelp;

}

?>