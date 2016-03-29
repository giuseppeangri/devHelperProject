<?php
	
	require 'lib/caching.php';
	
	$db_table = 'book';
	
	if(isset($_GET['kw']))
		$keyword = trim($_GET['kw']);
	else if(isset($_GET['author']))
		$keyword = trim($_GET['author']);
	
	if(check_last_update($db_table, $keyword, 1)){
		
		delete_old_cache($db_table, $keyword);
		
		// URL Making
		$base_url = 'http://isbn.directory';
		$q 				= '/ajax?ajax=search&q=';
		$page 		= '&page=';
		
// 		$keyword 			= str_replace(' ', '+', $keyword);
		$page_number 	= 1;
	
		$url = $base_url.$q.urlencode($keyword).$page.$page_number;
				
		$options = array(
			'http' => array(
			  'method'  => 'GET',
			  'header'  => array(
			    "X-Requested-With: XMLHttpRequest",
			    "Content-type: application/json"
			  )
			)
		);

		$context  = stream_context_create($options);
		
		$itemsToReturn = array();
		
		do {
			
			$result_string = file_get_contents($url, false, $context);
			$result_json   = json_decode($result_string);
			
			if($result_json != null) {
				
				foreach($result_json as $item) {
					
					$obj = get_object_vars($item);
					
					if( is_string($obj['url']) ) {
												
						$url = null;
						if($obj['url']) {
							$url = $base_url.$obj['url'];
						}
						
						$title = null;
						if($obj['title']) {
							$title = $obj['title'];
						}
						
						if($obj['subtitle']) {
							$title = $title.' - '.$obj['subtitle'];
						}
						
						$description = null;
						if($obj['desc']) {
							$description = $obj['desc'];
						}
						
						$author = null;
						if($obj['author']) {
							$author = $obj['author'];
							$author = extractAuthors($author);
						}
						
						$isbn = null;
						if($obj['isbn13']) {
							$isbn = $obj['isbn13'];
						}
						
						$img = null;
						if($obj['img']) {
							$img = $base_url.$obj['img'];
						}
						
						$publication_date = null;
						if($obj['date']) {
							$publication_date = $obj['date'];
						}
						
						$pages_number = null;
						if($obj['page']) {
							$pages_number = $obj['page'];
						}
						
						$itemToReturn = array(
					    'url'              => $url,
					    'title'            => $title,
					    'description'      => $description, 
					    'author'           => $author,
					    'isbn'             => $isbn,
					    'img'              => $img,
					    'publication_date' => $publication_date,
					    'pages_number'     => $pages_number
					  );
					  
						if(isset($_GET['author'])) {
							
							$strings = explode('+', $keyword);
							
							$contains = 0;
							foreach($strings as $string) {

								if( (stripos($author, $string) !== false) && ($contains != 2) ) {
									$contains = 1;
								}
								else{
									$contains = 2;
								}
								
							}
							
							if($contains == 1) {
								
								insert_data($db_table, $keyword, $itemToReturn);
								array_push($itemsToReturn, $itemToReturn);
							
							}
							
						}
						else {
							
						  insert_data($db_table, $keyword, $itemToReturn);
							array_push($itemsToReturn, $itemToReturn);
							
						}
						
					}
					
				}
							
				$page_number++;
				$url = $base_url.$q.$keyword.$page.$page_number;
				
			}
			
		} while( $result_json != null );

	}
	else {
		
		$results = get_data($db_table, $keyword);
		$itemsToReturn = array();
		while($row = $results->fetch_array(MYSQLI_ASSOC)){
			$itemToReturn = array(
			    'url'              => $row['url'],
			    'title'            => $row['title'],
			    'description'      => $row['description'], 
			    'author'           => $row['author'],
			    'isbn'             => $row['isbn'],
			    'img'              => $row['img'],
			    'publication_date' => $row['publication_date'],
			    'pages_number'     => $row['pages_number']
			  );
				
			array_push($itemsToReturn, $itemToReturn);
		}
		
	}
	
	// Action Header
	$response['action'] = array(
	  'status'      => 1,
	  'name'        => 'book',
	  'description' => ''
	);
	
	// Action Data
	$response['data'] = $itemsToReturn;
	
	header('Content-Type: application/json');

	echo json_encode($response);
	
	
	//FUNCTION
	function extractAuthors($str){
		if(strrpos($str, "by") ===false){
			return $str;
		}
		else{	
			$doc = new DOMDocument();
			$doc->loadHTML($str);
			$authors = $doc->getElementsByTagName("a");
			$string = "";
			foreach ($authors as $a){
				$string .= $a->nodeValue.", ";
			}
			$string = substr($string, 0, strlen($string)-2);
			return  $string;
		}
	}