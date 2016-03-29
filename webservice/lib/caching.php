<?php
require_once 'connect.php';

//  mysqli_report(MYSQLI_REPORT_ALL);

function check_last_update($source, $search_string, $num_days){		
	
	searchCounter($search_string);
	$db = Connect::getInstance();
	
	$results = $db->query("SELECT last_update FROM cache_register WHERE source ='$source' and search_string='$search_string' ");
	
	$row = $results->fetch_array(MYSQLI_ASSOC);
	$now_time = time();
	
	if(!$row) {
		$db->query("
			INSERT INTO cache_register VALUES ('$source', '$search_string', '$now_time');
		");
		
		$last_update_time = 0;
	}
	else {
		$last_update_time = $row['last_update'];
	}
	
	$diff= $now_time - $last_update_time;
	$day_diff = $diff/(60*60*24);
	
	//Se la differenza in giorni è maggiore di 3 allora lo scraping è da rifare
	if($day_diff > $num_days){ 
		$db->query("UPDATE cache_register SET last_update = '$now_time' WHERE source ='$source' and search_string='$search_string' ");
		return true;
	}
	else{
		return false; 
	}

}

function get_data($source, $search_string){
	$db = Connect::getInstance();

	if($source == "book_price")
		$results = $db->query("SELECT * FROM `$source` where isbn='$search_string'");
	else if($source == "framework")
		$results = $db->query("SELECT * FROM `$source` where programming_language='$search_string'");
	else {
		
		if($search_string){
			$search_string = str_ireplace(' ', '+', $search_string);
			$results = $db->query("SELECT * FROM `$source` where search_string='$search_string'");
		}
		else
			$results = $db->query("SELECT * FROM `$source`");
	}

	return $results;	
}

function insert_data($source, $search_string, $params){
	
	$db = Connect::getInstance();
	
	if($source == "event"){
		$stmt = $db->prepare('INSERT INTO event (search_string, url, title, description, date_start, date_end, venue_city, venue_region, img) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ? )');
		$stmt->bind_param("sssssssss", $search_string, $url, $title, $description, $date_start, $date_end, $venue_city, $venue_region, $img);
		$url = $params['url'];
		$title = $params['title'];
		$description = $params['description'];
		$date_start = $params['date_start'];
		$date_end = $params['date_end'];
		$venue_city = $params['venue_city'];
		$venue_region = $params['venue_region'];
		$img = $params['img'];
		
		$result = $stmt->execute();
		if (!$result) {
	    throw new Exception($mysqli->error);
		}
		
	}
	
	if($source == "videotutorial"){
		$stmt = $db->prepare('INSERT INTO videotutorial (search_string, url, title, description, author, price, img) VALUES (?, ?, ?, ?, ?, ?, ?)');
		$stmt->bind_param("sssssss", $search_string, $url, $title, $description, $author, $price, $img);
		$url = $params['url'];
		$title = $params['title'];
		$author = $params['author'];
		$price = $params['price'];
		$img = $params['img'];
		$description = $params['description'];
		$stmt->execute();
	}
	
	if($source == "tutorial"){
		$stmt = $db->prepare('INSERT INTO tutorial (url, title, category, keywords, lang) VALUES (?, ?, ?, ?, ?)');
		$stmt->bind_param("sssss", $url, $title, $category, $keywords, $lang);
		
		$url      = $params['url'];
		$title    = $params['title'];
		$category = $params['category'];
		$keywords = $params['keywords'];
		$lang     = $params['lang'];
				
		$result = $stmt->execute();
		if (!$result) {
	    throw new Exception($mysqli->error);
		}
	}
	
	if($source == "book_price"){
		$stmt = $db->prepare('INSERT INTO book_price (isbn, price, url, seller, type) VALUES (?, ?, ?, ?, ?)');
		$stmt->bind_param("sssss", $search_string, $price, $url, $seller, $type);
		$url    = $params['url'];
		$price  = $params['price'];
		$seller = $params['seller'];
		$type   = $params['type'];
		$stmt->execute();
	}
	
	if($source == "book"){
				
		$stmt = $db->prepare('INSERT INTO book (search_string, url, title, description, author, isbn, img, publication_date, pages_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
		
		$url              = $params['url'];
		$title            = $params['title'];
		$description      = $params['description'];
		$author           = $params['author'];
		$isbn             = $params['isbn'];
		$img              = $params['img'];
		$publication_date = $params['publication_date'];
		$pages_number     = $params['pages_number'];
		
		$stmt->bind_param("sssssssss", $search_string, $url, $title, $description, $author, $isbn, $img, $publication_date, $pages_number);
		
		$result = $stmt->execute();
		if (!$result) {
	    throw new Exception($mysqli->error);
		}
		
	}
	
	if($source == "group"){
		
		$stmt = $db->prepare('INSERT INTO `group` (search_string, url, title, description, city, region, members, img) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
		
		$url         = $params['url'];
		$title       = $params['title'];
		$description = $params['description'];
		$city        = $params['city'];
		$region      = $params['region'];
		$members     = $params['members'];
		$img         = $params['img'];
		
		$stmt->bind_param("ssssssss", $search_string, $url, $title, $description, $city, $region, $members, $img);
		
		$result = $stmt->execute();
		if (!$result) {
	    throw new Exception($mysqli->error);
		}
		
	}
	
	if($source == "documentation"){
		
		$stmt = $db->prepare('INSERT INTO `documentation` (language, url) VALUES (?, ?)');
		
		$language = $params['language'];
		$url      = $params['url'];
		
		$stmt->bind_param("ss", $language, $url);
		
		$result = $stmt->execute();
		if (!$result) {
	    throw new Exception($mysqli->error);
		}
		
	}
	
	if($source == "framework"){
		
		$stmt = $db->prepare('INSERT INTO `framework` (programming_language, title, url_detail, url_framework, description) VALUES (?, ?, ?, ?, ?)');
		
		$programming_language = $params['programming_language'];
		$title                = $params['title'];
		$url_detail           = $params['url_detail'];
		$url_framework        = $params['url_framework'];
		$description          = $params['description'];
		
		$stmt->bind_param("sssss", $programming_language, $title, $url_detail, $url_framework, $description);
		
		$result = $stmt->execute();
		if (!$result) {
	    throw new Exception($mysqli->error);
		}
		
	}

}

function delete_old_cache($source, $search_string){
	$db = Connect::getInstance(); 
	
	if($search_string)
		$db->query("delete from `".$source."` where search_string='$search_string'");
	else
		$db->query("truncate `".$source."`");
	
}

function delete_libUniv_cache($isbn){
	$db = Connect::getInstance(); 
	$db->query("delete from book_price where isbn='$isbn'");
}

function randStrGen($len){
    $result = "";
    $chars = "abcdefghijklmnopqrstuvwxyz_?!-0123456789";
    $charArray = str_split($chars);
    for($i = 0; $i < $len; $i++){
	    $randItem = array_rand($charArray);
	    $result .= "".$charArray[$randItem];
    }
    return $result;
}

function searchCounter($search_string){
	session_start();
	if(isset($_SESSION['token'])){
		$token = $_SESSION['token'];
	}
	else{
		$token = randStrGen(64);
		$_SESSION['token'] = $token;
	}
	
	$db = Connect::getInstance();
	$results = $db->query("SELECT * FROM search_token WHERE token ='$token' and search_string='$search_string' ");
	$num_row = $results->num_rows;
	if($num_row == 0){
		$db->query( "INSERT INTO search_token VALUES ('$search_string', '$token')" );
		
		$res = $db->query("SELECT count FROM search where search_string='$search_string' ");
		if($res->num_rows == 0){
			$db->query( "INSERT INTO search VALUES ('$search_string', 1 )" );
		}
		else{
				
			$row = $res->fetch_array(MYSQLI_ASSOC);
			$count = $row['count'];		
			$db->query("UPDATE search SET count=".++$count." WHERE search_string='$search_string'");
		
		}
	}
}

