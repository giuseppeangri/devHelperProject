<?php
	
	require 'lib/caching.php';

	if(isset($_GET['kw']))
		$search_string = $_GET['kw'];
	else if(isset($_GET['city']))
		$search_string = $_GET['city'];
	
	if(check_last_update('event', $search_string, 1)){
		
		delete_old_cache('event', $search_string);
		
		$devTokenKey="";		

		if(isset($_GET['kw'])){
			$url="https://www.eventbriteapi.com/v3/events/search/?venue.country=IT&token=".$devTokenKey."&format=json&categories=102&sort_by=best&q=".$search_string;
		}
		else{
			$url="https://www.eventbriteapi.com/v3/events/search/?venue.country=IT&token=".$devTokenKey."&format=json&categories=102&sort_by=best&venue.city=".$search_string;
		}
				
		$curl = curl_init($url);		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		
		$json = curl_exec($curl);
		
		if(curl_errno($curl)) { // check for execution errors
		 	echo 'Scraper error: ' . curl_error($curl);
			exit;
		}
		curl_close($curl);
		
		$json_array=json_decode($json, true);
		$events=$json_array['events'];
		
		$itemsToReturn = array();
		
		if($events){
			foreach($events as $ev){
				
				$title = $ev['name']['text'];
				$description = $ev['description']['text'];
				$description = (strlen($description) > 5996) ? substr($description, 0, 5996) . '...' : $description;
				$url = $ev['url'];
				$startTime = $ev['start']['local'];
				$endTime = $ev['end']['local'];
				$logoUrl = $ev['logo']['url'];
				$venueArray = getVenueInfo($ev['venue_id']);
				
				$itemToReturn = array(
					'title'	=>	$title,
					'description'	=> $description,
					'url'	=> $url,
					'date_start'	=>	strtotime($startTime),
					'date_end' => strtotime($endTime),
					'img' => $logoUrl,
					'venue_city' => $venueArray['city'],
					'venue_region'	=> $venueArray['region']
				);
				insert_data('event', $search_string, $itemToReturn);
				
				array_push($itemsToReturn, $itemToReturn);
			
			}
		}
	}
	else{
		$results = get_data('event', $search_string);
		$itemsToReturn = array();
		while($row = $results->fetch_array(MYSQLI_ASSOC)){
			$itemToReturn = array(
			   'title'	=>	$row['title'],
				'description'	=> $row['description'],
				'url'	=> $row['url'],
				'date_start'	=>	$row['date_start'],
				'date_end' => $row['date_end'],
				'img' => $row['img'],
				'venue_city' => $row['venue_city'],
				'venue_region'	=> $row['venue_region']
			  );
				
			 array_push($itemsToReturn, $itemToReturn);
		}
	}
	
	// Action Header
	$response['action'] = array(
		'status'      => 1,
		'name'        => 'EventBrite',
		'description' => 'Eventi relativi alla ricerca'
	);
		
	// Action Data
	$response['data'] = $itemsToReturn;
		
	header('Content-Type: application/json');
	
	echo json_encode($response);
	
		
	function getVenueInfo($venueId){
		
		$devTokenKey="";
		$url="https://www.eventbriteapi.com/v3/venues/".$venueId."/?token=".$devTokenKey."&format=json";		
		$curl = curl_init($url);		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$json = curl_exec($curl);
		if(curl_errno($curl)) { // check for execution errors
		 	echo 'Scraper error: ' . curl_error($curl);
			exit;
		}
		curl_close($curl);
		
		$json_array=json_decode($json, true);
		
		
		$city = $json_array['address']['city'];
		$region = $json_array['address']['region'];
		
		$venueArray = array(
			'city'	=> $city,
			'region'	=> $region
		);
		
		return $venueArray;
		
	}
