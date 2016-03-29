<?php
	
	require 'lib/xpath.php';
	require 'lib/caching.php';
	
	$db_table = 'videotutorial';
	$keyword = $_GET['kw'];
		
	if(check_last_update($db_table, $keyword, 1)){

		// URL Making
		$base_url = 'https://www.udemy.com/courses/search/?lr=1&q=';
	
		$url = $base_url.urlencode($keyword);
		
		// Response Object	
		$response = array();
		
		// Array for all XpathObject
		$xpathItems = array();
		
		// XpathObject of First Page
		$xpathItem = getXpathObject($url);
		array_push($xpathItems, $xpathItem);
		
		// Check if there are more pages

		$numPages = $xpathItem->evaluate('count(//li[@data-purpose="pagination-page-number"])');
		
		// Add XpathObject of Other Pages to XpathObject Array
		for($i=2; $i<=$numPages; $i++){
			$currentUrl = $url.'&p='.$i;
			$xpathItem	= getXpathObject($currentUrl);
			array_push($xpathItems, $xpathItem);
		}
		
		// Scraping from XpathObject Array
		
		$itemsToReturn = array();
		
		delete_old_cache($db_table, $keyword);
	
		foreach($xpathItems as $xpath) {
	
			$items = $xpath->query('//*[@id="courses"]/li');
	
			for($i=0; $i < $items->length; $i++) {
				
				$item = $items->item($i);
			    
				$url = $xpath->query('a', $item);
				if($url->item(0)) {
					$urls = 'https://www.udemy.com'.$url->item(0)->getAttribute('href');
				}
			  
		    $titolo = $xpath->query('div/div/div[@class="title-wr"]/span', $item);
		    if($titolo->item(0)) {
			    $titolo = $titolo->item(0)->nodeValue;
				}
			    
		    $autore = $xpath->query('div/div/span[@class="ins"]', $item);
		    if($autore->item(0)) {
			    $autore = $autore->item(0)->nodeValue;
			    $autore = explode("\n" , $autore)[0];
			    $autore = explode("," , $autore)[0];
				}
			    
		    $prezzo = $xpath->query('div[2]/div[4]/span', $item);
		    if($prezzo->item(0)) {
			    $prezzo = $prezzo->item(0)->nodeValue;
			    $prezzo = trim($prezzo);
			    
			    $match = array();
					preg_match('([0-9-.,]{1,9})', $prezzo, $match);
					if(isset($match[0])) {
						$prezzo = floatval($match[0]);
						$prezzo = number_format($prezzo, 2, '.', ',');
					}
				}
				  
		    $img = $xpath->query('div/span/img', $item);
		    if($img->item(0)) {
			    $img = $img->item(0)->getAttribute('src');
				}
				  
				$itemToReturn = array(
					'url'         => $urls,
			    'title'       => $titolo,
			    'author'      => $autore,
			    'price'       => $prezzo,
			    'img'         => $img,
			    'description' => ''
				); 
				
				
				array_push($itemsToReturn, $itemToReturn);
		
			}
		}
		
		$lyndaArray = getLyndaJson($keyword);
		$ma_array   = get_ma_json($keyword);
		
		$itemsToReturn = mergeResults($itemsToReturn, $lyndaArray, $ma_array);
		
		foreach($itemsToReturn as $item){
			insert_data($db_table, $keyword, $item);
		}
						
	}
	else {
		$results = get_data($db_table, $keyword);
		$itemsToReturn = array();
		while($row = $results->fetch_array(MYSQLI_ASSOC)){
			$itemToReturn = array(
		    'url'         => $row['url'],
		    'title'       => $row['title'],
		    'author'      => $row['author'],
		    'price'       => $row['price'],
		    'img'         => $row['img'],
		    'description' => ''
		  );
				
			array_push($itemsToReturn, $itemToReturn);
		}
	}
	
	// Action Header
	$response['action'] = array(
	  'status'      => 1,
	  'name'        => 'Videotutorials',
	  'description' => 'Videotutorials from Udemy, Lynda, Microsoft Academy'
	);
	
	// Action Data
	$response['data'] = $itemsToReturn;
	
	header('Content-Type: application/json');

	echo json_encode($response);
	
	////////////////////////////////////////
	//	Function
	////////////////////////////////////////
		
	function getLyndaJson($keyword) {
		
		$url = "https://api.import.io/store/connector/12513729-2036-425e-9600-1325a160d3e7/_query?&input=webpage/url:http%3A%2F%2Fwww.lynda.com%2Fsearch%3Ff=producttypeid%3a2%26q%3D".urlencode(urlencode($keyword));
		
		$json_array = exec_curl_importio($url);
				
		$returnArray = array();
		
		foreach($json_array as $course){
			
			$src = '';
			if(isset($course['img'])) {
				$img 		= $course['img'];
				$array 	= explode('data-img-src="', $img);
				$src 		= explode('"', $array[1]);
				$src 		= $src[0];
			}
			
			$url = '';
			if(isset($course['url'])) {
				$url = $course['url'];
			}
			
			$title = '';
			if(isset($course['title'])) {
				$title = $course['title'];
			}
			
			$author = '';
			if(isset($course['author'])) {
				$author = $course['author'];
			}
			
			$price = '';
			if(isset($course['price'])) {
				$price = $course['price'];
			}
			
			$description = '';
			if(isset($course['description'])) {
				$description = $course['description'];
			}
			
			$item = array(
			 'url'         => $url,
		    'title'       => $title,
		    'author'      => $author,
		    'price'       => $price,
		    'img'         => $src,
		    'description' => $description
			);
			
			array_push($returnArray, $item);
			
		}
		
		return $returnArray;
		
	}
	
	function get_ma_json($keyword) {
		
		// URL Making
		$url = 'http://api-mlxprod.microsoft.com/sdk/search/v1.0/5/courses';
				
		$content = array(
			'SelectCriteria' => array(
				array(
					'SelectOnField'     => 'LCID',
					'SelectTerm'        => '1040',
					'SelectMatchOption' => 2
				),
				array(
					'SelectOnField'     => 'LCID',
					'SelectTerm'        => '1033',
					'SelectMatchOption' => 2
				)
			),
			'DisplayFields' => array(),
			'SortOptions'   => array(
				array(
					'SortOnField' => 'Relevance',
					'SortOrder'   => 1
				)
			),
			'SearchKeyword'    => $keyword,
			'UILangaugeCode'   => 1040,
			'UserLanguageCode' => 1040
		);
		
		$content = json_encode($content);
						
		$options = array(
			'http' => array(
			  'method'  => 'POST',
			  'header'  => array(
			    "X-Requested-With: XMLHttpRequest",
			    "Content-type: application/json"
			  ),
			  'content' => $content
			)
		);

		$context  = stream_context_create($options);
		
		$result_string = file_get_contents($url, false, $context);
		$result_obj    = json_decode($result_string);
		$result_json   = get_object_vars($result_obj);
		
		$returnArrayMicrosoft = array();
		
		$base_url_video = 'https://mva.microsoft.com/it-it/training-courses/';
		
		foreach($result_json['results'] as $obj) {
			
			$item = get_object_vars($obj);
			
			if($item['id']) {
				$url = $base_url_video.$item['id'];
			}
			
			if($item['courseName']) {
				$title = $item['courseName'];
			}
			
			if($item['courseShortDescription']) {
				$description = $item['courseShortDescription'];
			}
			
			$author = '';
			if($item['authorInfo']) {

				foreach($item['authorInfo'] as $author_obj) {
					
					$author_single = get_object_vars($author_obj);
										
					$author = $author.$author_single['displayName'].' - ';
					
				}
				
				$author = substr($author, 0, strlen($author)-3);
								
			}
			
			$price = 'Free';
			
			if($item['courseImage']) {
				$img = $item['courseImage'];
			}
			
			$itemToReturn = array(
			 'url'         => $url,
		    'title'       => $title,
		    'description' => $description,
		    'author'      => $author,
		    'price'       => $price,
		    'img'         => $img
			);
			
			array_push($returnArrayMicrosoft, $itemToReturn);
			
		}
		
		return $returnArrayMicrosoft;
				
	}
	
	function mergeResults($udemyArray, $lyndaArray, $ma_array) {
		
		$lyndaSize = count($lyndaArray);
		$udemySize = count($udemyArray);
		$maSize    = count($ma_array);
		
		$max = max(array($lyndaSize, $udemySize, $maSize));
	
		$mergedArray = array();
		
		for($i = 0; $i < $max; $i++){
			if($i < $udemySize){
				array_push($mergedArray, $udemyArray[$i]);
			}
			if($i < $lyndaSize){
				array_push($mergedArray, $lyndaArray[$i]);
			}
			if($i < $maSize){
				array_push($mergedArray, $ma_array[$i]);
			}
		}
		
		return $mergedArray;
		
	}
	
	function exec_curl_importio($url) {
		
		$done      = false;
		$times     = 0;
		$max_times = 5;
		
		$results = array();
		
		do {
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_FAILONERROR, true);

			$result = curl_exec($curl);
			$result_json	= json_decode($result, true);
			
			if(isset($result_json['results']))			
				$results = $result_json['results'];
			
			if(curl_errno($curl)) {
				$times++;
			}
			else if( empty($results) ) {
				$times++;
			}
			else {
				$done = true;
			}
			
			curl_close($curl);
			
		}while( !$done && ($times<=$max_times) );
		
		return $results;
		
	}
	