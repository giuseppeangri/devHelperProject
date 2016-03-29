<?php
	
	require 'lib/xpath.php';
	require 'lib/caching.php';
	
	$db_table = 'tutorial';
	$keyword  = $_GET['kw'];
	
	if(check_last_update($db_table, null, 3)){
		
		delete_old_cache($db_table, null);
		
		// HMTL.it
			
		// URL Making
		$url = 'http://www.html.it/development/guide/';
		
		// Response Object	
		$response = array();
		
		// Array for all XpathObject
		$xpathItems = array();
		
		// XpathObject of First Page
		$xpathItem = getXpathObject($url);
		array_push($xpathItems, $xpathItem);
		
		// Check if there are more pages
		$numPages = $xpathItem->query('//*[@id="content-article"]/div[2]/div/div/a[5]');
		$numPages = $numPages->item(0)->getAttribute('href');
		$numPages = explode("/", $numPages);
		$numPages = $numPages[ count($numPages)-2 ];
	
		// Add XpathObject of Other Pages to XpathObject Array
		for($i = 2; $i <= $numPages; $i++){
			$xpathItem = getXpathObject($url.$i.'/');
			array_push($xpathItems, $xpathItem);
		}
		
		// Scraping from XpathObject Array
		$itemsToReturn = array();
		
		foreach($xpathItems as $xpath) {
			$items = $xpath->query('//dd');
			
			for($i=0; $i < $items->length; $i++) {
				$item = $items->item($i);
				
				$url = '';
				$url_node = $xpath->query('h3/a', $item);
				if($url_node->item(0)){
					$url = $url_node->item(0)->getAttribute('href');
				}
				
				$title = '';
				$title_node = $xpath->query('h3/a', $item);
				if($title_node->item(0)){
					$title = $title_node->item(0)->nodeValue;
				}
				
				$category = '';
				$category_node = $xpath->query('ul/li/a', $item);
				if($category_node->item(0)){
					$category = $category_node->item(0)->nodeValue;
				}
				
				$keywords = '';
				$keywords_node = $xpath->query('a', $item);
				if($keywords_node->item(0)){
					foreach($keywords_node as $k){
						$keywords = $keywords.' #'.$k->nodeValue;
					}
				}
				
				$lang = 'IT';
				
				$itemToReturn = array(
			    'url'        => $url,
			    'title'      => $title,
			    'category'   => $category,
			    'keywords'   => $keywords,
			    'lang'       => $lang,
			    'occurences' => 0
				);
				
				insert_data($db_table, null, $itemToReturn);
				
				$numOfOccurences = contains($keyword, $title);	
				if($numOfOccurences > 0){
					$itemToReturn['occurences'] = $numOfOccurences;
					array_push($itemsToReturn, $itemToReturn);
				}
				
			}	
			
		}
		
		// TutorialsPoint
		
		// URL Making
		$url = 'http://www.tutorialspoint.com/tutorialslibrary.htm';
	
		// Response Object	
		$response = array();
		
		// Array for all XpathObject
		$xpathItems = array();
		
		// XpathObject of First Page
		$xpath = getXpathObject($url);
				
		$items = $xpath->query("//ul[@class='menu']");
		
		foreach($items as $item){
			
			$id = $item->getAttribute('id');
			$h4s = $xpath->query("//ul[@id='$id']/preceding-sibling::h4");
			
			$category = '';
			if($h4s->item(($h4s->length)-1)->nodeValue) {
				$category = $h4s->item(($h4s->length)-1)->nodeValue;
				$category = preg_replace('/\s+/', '', $category);
			}
			
			$menuItem = $xpath->query("//ul[@id='$id']")->item(0);
			$tutorials = $xpath->query('li/a', $menuItem);
			
			foreach($tutorials as $tutorial){
				
				$url = '';
				if($tutorial->getAttribute('href')) {
					$url = 'http://www.tutorialspoint.com'.$tutorial->getAttribute('href');
				}
				
				$title = '';
				if($tutorial->nodeValue) {
					$title = $tutorial->nodeValue;
					$title = str_ireplace('Learn ', '', $title);
				}
								
				$keywords = null;
				$lang = 'EN';
				
				$item = array(
					'url'      => $url,
					'title'    => $title,
					'category' => $category,
					'keywords' => $keywords,
					'lang'   	 => $lang,
			    'occurences' => 0
				);
				
				insert_data($db_table, null, $item);
				
				$numOfOccurences = contains($keyword, $title);	
				if($numOfOccurences > 0){
					$item['occurences'] = $numOfOccurences;
					array_push($itemsToReturn, $item);
				}
								
			} 
			
		}
	
	}
	else {
		$results = get_data($db_table, null);
		
		$itemsToReturn = array();
		
		while($row = $results->fetch_array(MYSQLI_ASSOC)){
			$itemToReturn = array(
			    'category'   => $row['category'],
			    'title'      => $row['title'],
			    'url'        => $row['url'],
			    'keywords'   => $row['keywords'],
			    'lang'       => $row['lang'],
			    'occurences' => 0
			);
			
			$numOfOccurences = contains($keyword, $row['title']);	
			if($numOfOccurences > 0){
				$itemToReturn['occurences'] = $numOfOccurences;
				array_push($itemsToReturn, $itemToReturn);
			}
						
		}
	}
	
	//ordering results
	usort($itemsToReturn, function($a, $b) {
		return $b['occurences'] - $a['occurences'];
	});
			
	// Action Header
	$response['action'] = array(
	  'status'      => 1,
	  'name'        => 'tutorial',
	  'description' => ''
	);
	
	// Action Data
	$response['data'] = $itemsToReturn;
	
	header('Content-Type: application/json');

	echo json_encode($response);
	
	function contains($search_string, $title){
		$result = 0;
		$substrings = explode(" ",$search_string);
		foreach($substrings as $substr){
			if (stripos($title, $substr) !== false) {
				$result++;
			}
		}
		
		return $result;
	}
	