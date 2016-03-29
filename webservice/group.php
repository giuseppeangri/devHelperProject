<?php
	
	require 'lib/caching.php';
	
	$db_table = 'group';
		
	if(isset($_GET['kw']))
		$keyword = $_GET['kw'];
	else if(isset($_GET['city']))
		$keyword = $_GET['city'];
	
	if(check_last_update($db_table, $keyword, 1)){
		
		delete_old_cache($db_table, $keyword);
				
		// URL Making
		$base_url   = 'https://api.meetup.com/find/groups?key=';
		$api_key    = '';
		
		if(isset($_GET['kw']))
			$text 			= '&sign=true&photo-host=public&country=IT&category=34&text=';
		else
			$text 			= '&sign=true&photo-host=public&country=IT&category=34&location=';
		
		$keyword = preg_replace('/\s+/', '', $keyword);
	
		$url = $base_url.$api_key.$text.$keyword;
				
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

		$result_string = file_get_contents($url, false, $context);
		$result_json   = json_decode($result_string);
					
		$itemsToReturn = array();
		
		if($result_json != null) {
			
			foreach($result_json as $item) {
				
				$obj = get_object_vars($item);
							
				$url = null;
				if( isset($obj['link']) ) {
					$url = $obj['link'];
				}
				
				$title = null;
				if( isset($obj['name']) ) {
					$title = $obj['name'];
				}
				
				$description = null;
				if( isset($obj['description']) ) {
					$description = $obj['description'];
				}
				
				$city = null;
				if( isset($obj['city']) ) {
					$city = $obj['city'];
				}
				
				$region = null;
				if( isset($obj['state']) ) {
					$region = $obj['state'];
				}
				
				$members = null;
				if( isset($obj['members']) ) {
					$members = $obj['members'];
				}
				
				$img = null;
				if( isset($obj['group_photo']) ) {

					$group_photo = get_object_vars($obj['group_photo']);
					
					if( isset($group_photo['photo_link']) ) {
						$img = $group_photo['photo_link'];
					}
					
				}
				
				$itemToReturn = array(
			    'url'         => $url,
			    'title'       => $title,
			    'description' => $description, 
			    'city'        => $city,
			    'region'      => $region,
			    'members'     => $members,
			    'img'         => $img,
			  );
			  
			  insert_data($db_table, $keyword, $itemToReturn);
				
				array_push($itemsToReturn, $itemToReturn);
									
			}

		}

	}
	else {
		
		$results = get_data($db_table, $keyword);
		$itemsToReturn = array();
		while($row = $results->fetch_array(MYSQLI_ASSOC)){
			$itemToReturn = array(
		    'url'         => $row['url'],
		    'title'       => $row['title'],
		    'description' => $row['description'], 
		    'city'        => $row['city'],
		    'region'      => $row['region'],
		    'members'     => $row['members'],
		    'img'         => $row['img'],
		  );
				
			array_push($itemsToReturn, $itemToReturn);
		}
		
	}
	
	// Action Header
	$response['action'] = array(
	  'status'      => 1,
	  'name'        => 'group',
	  'description' => ''
	);
	
	// Action Data
	$response['data'] = $itemsToReturn;
	
	header('Content-Type: application/json');

	echo json_encode($response);
