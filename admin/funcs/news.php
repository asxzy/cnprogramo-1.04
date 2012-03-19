<?PHP
//-----------------------------------------------------------------------------------------------
//My Program-O Version 1.0.1
//Program-O  chatbot admin area
//Written by Elizabeth Perreau
//Feb 2010
//for more information and support please visit www.program-o.com
//-----------------------------------------------------------------------------------------------
function getNews()
{
	//Pull certain elements 
	$news = "";
	 $reader = new XMLReader(); 
	  $reader->open("http://www.program-o.com/xml/programo_news.xml"); 
	while ($reader->read()) { 
	 switch ($reader->nodeType) { 
	   case (XMLREADER::ELEMENT): 
	
	if ($reader->name == "title") 
		 { 
		   $reader->read(); 
		   $title = trim($reader->value); 
		   $news .= "<div id=\"newsTitle\"><br/>$title</div>"; 
		   break; 
		 } 
	
	 if ($reader->name == "content") 
		 { 
		   $reader->read(); 
		   $content = trim( $reader->value ); 
		   $news .= "<div id=\"newsBody\"><br/>$content</div><br/><hr>"; 
		   break; 
		 } 
	
	 if ($reader->name == "date") 
		{ 
		   $reader->read(); 
		   $date = trim( $reader->value ); 
		   $news .= "<div id=\"newsDate\"><br/>$date</div>"; 
		   break; 
		} 
	  } 
	} 
	
	return $news;
}
?>