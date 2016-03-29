<?php

	require 'lib/caching.php';
	
	$db_table = 'documentation';
	
	$keyword = '';
	if(isset($_GET['kw']))
		$keyword = $_GET['kw'];
	
	if(check_last_update($db_table, null, 10)){
		
		$url_devdocs = 'https://api.import.io/store/connector/c6e91e02-e1f5-42a6-bf42-e49be901db54/_query?input=webpage/url:http%3A%2F%2Fdevdocs.io';
		
		$base_url = 'http://devdocs.io';
		
		delete_old_cache($db_table, null);

		$results = exec_curl_importio($url_devdocs);
			
		$title_array = $results[0]['title'];
		$url_array   = $results[0]['url'];
		
		$itemsToReturn = array();
		
		for($i=0; $i < count($title_array); $i++) {
			
			$language = $title_array[$i];
			$url      = $base_url.$url_array[$i];
			
			$itemToReturn = array(
				'language'   => $language,
				'url'        => $url,
				'occurences' => 0
			);
			
			insert_data($db_table, null, $itemToReturn);
			
			$numOfOccurences = contains($keyword, $language);	
			if($numOfOccurences > 0){
				$itemToReturn['occurences'] = $numOfOccurences;
				array_push($itemsToReturn, $itemToReturn);
			}
			
		}
		
		$static_documentation_string = file_get_contents('./documentation_static.json');
		$static_documentation_json = json_decode($static_documentation_string, true);
		
		foreach($static_documentation_json as $item) {
			
			$language = $item['language'];
			$url      = $item['url'];
			
			$itemToReturn = array(
				'language'   => $language,
				'url'        => $url,
				'occurences' => 0
			);
			
			insert_data($db_table, null, $itemToReturn);
			
			$numOfOccurences = contains($keyword, $language);	
			if($numOfOccurences > 0){
				$itemToReturn['occurences'] = $numOfOccurences;
				array_push($itemsToReturn, $itemToReturn);
			}
			
		}
		
	}
	else {
		
		$results = get_data($db_table, null);
		
		$itemsToReturn = array();
		
		while($row = $results->fetch_array(MYSQLI_ASSOC)){
			$itemToReturn = array(
		    'language'   => $row['language'],
		    'url'        => $row['url'],
		    'occurences' => 0
			);
			
			$numOfOccurences = contains($keyword, $row['language']);	
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
	  'name'        => 'Documentation',
	  'description' => ''
	);
	
	// Action Data
	$response['data'] = $itemsToReturn;
	
	header('Content-Type: application/json');

	echo json_encode($response);
	
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