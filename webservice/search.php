<?php

	require 'lib/caching.php';
	
	$db_table 	= 'search';
	$min_count	= 10;
	
	$results = get_data($db_table, null);
	
	$itemsToReturn = array();
	
	while($row = $results->fetch_array(MYSQLI_ASSOC)){
		
		if($row['count'] >= $min_count) {
			array_push($itemsToReturn, $row['search_string']);
		}
			
	}
	
	// Action Header
	$response['action'] = array(
	  'status'      => 1,
	  'name'        => 'Search List',
	  'description' => ''
	);
	
	// Action Data
	$response['data'] = $itemsToReturn;
	
	header('Content-Type: application/json');

	echo json_encode($response);