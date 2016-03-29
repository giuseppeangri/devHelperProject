<?php
	
	require 'lib/xpath.php';
	require 'lib/caching.php';
	
	$db_table = 'book_price';
	$isbn     = $_GET['kw'];
	
	if(check_last_update($db_table, $isbn, 1)){
		
		delete_libUniv_cache($isbn);
		
		// Libreria Universitaria
		
		// URL Making
		$base_url = 'http://www.libreriauniversitaria.it/ricerca/query/';
		
		//$keyword = 'javascript';
		$isbn = str_replace(' ', '+', $isbn);
	
		$url = $base_url.$isbn;
		
		// Response Object	
		$response = array();
		
		// Array for all XpathObject
		$xpathItems = array();
		
		// XpathObject of First Page
		$xpathItem = getXpathObject($url);
		array_push($xpathItems, $xpathItem);
		
		// Check if there are more pages
		$numPages = $xpathItem->evaluate('count(//*[@id="paginazione"]/span)') - 1;
		
		// Add XpathObject of Other Pages to XpathObject Array
		for($i=2; $i<=$numPages; $i++){
			$currentUrl = $url.'/pagina/'.$i;
			$xpathItem	= getXpathObject($currentUrl);
			array_push($xpathItems, $xpathItem);
		}
				
		// Scraping from XpathObject Array
		
		$itemsToReturn = array();
	
		foreach($xpathItems as $xpath) {
	
			$items = $xpath->query('//*[@id="colmain"]/div[4]/div');
	
			for($i=0; $i < $items->length; $i++) {
								
			  $item = $items->item($i);
			  
			  if($item->nodeValue) {
				  
				  $url = $xpath->query('div[@class="search-image"]/a/@href', $item);
				  if($url->item(0)) {
				    $url = $url->item(0)->nodeValue;			    
				  }
				  
				  $prezzo = $xpath->query('div[@class="search-details-ricerca"]/div[@class="info-prezzosconto"]/div[@class="product_our_price"]', $item);
				  if($prezzo->item(0)) {
				    $prezzo = $prezzo->item(0)->nodeValue;
				    $prezzo = trim($prezzo);
				    
				    $match = array();
						preg_match('([0-9-.,]{1,9})', $prezzo, $match);
						$prezzo = floatval($match[0]);
						$prezzo = number_format($prezzo, 2, '.', ',');
				  }
				  
				  $seller = 'Libreria Universitaria';
				  $type   = 'Copertina flessibile';
				 		  
				  $itemToReturn = array(
				    'isbn'   => $isbn,
				    'price'  => $prezzo,
				    'url'    => $url,
				    'seller' => $seller,
				    'type'   => $type
				  );
				  
				  insert_data($db_table, $isbn, $itemToReturn);
				  
				  array_push($itemsToReturn, $itemToReturn);
				  
			  }

			}
			
		}
				
		// Amazon
		
		$base_url_amazon = 'https://api.import.io/store/connector/5fabc8c9-42f6-40e1-adec-5bbe05bf109d/_query?&_apikey=5f0f27e3af5b407aadad7dbd64192fefb8a442135c06933d72cd3a6e8afae0f059b619518717447ab74dec596ef3c0e4f663b20cacde7671a8bf19b638ee6aa184dd5fe8dac5cfd6c23f28de5322bab3&input=webpage/url:http%3A%2F%2Fwww.amazon.it%2Fs%2Fref%3Dnb_sb_noss%3F__mk_it_IT%3D%25C3%2585M%25C3%2585%25C5%25BD%25C3%2595%25C3%2591%26url%3Dsearch-alias%253Dstripbooks%26field-keywords%3D';
		
		$base_url_amazon_other = 'https://api.import.io/store/connector/dde12cc1-26a0-403b-81a7-bc0e7a4c480e/_query?&_apikey=5f0f27e3af5b407aadad7dbd64192fefb8a442135c06933d72cd3a6e8afae0f059b619518717447ab74dec596ef3c0e4f663b20cacde7671a8bf19b638ee6aa184dd5fe8dac5cfd6c23f28de5322bab3&input=webpage/url:';
		
		$url = $base_url_amazon.$isbn;
		
		$results_book = exec_curl_importio($url);
		
		foreach($results_book as $item) {
			
			$price = '';
			if($item['price']) {
				$price = $item['price'];
				
				$match = array();
				preg_match('([0-9-.,]{1,9})', $price, $match);
				$price = floatval($match[0]);
				$price = number_format($price, 2, '.', ',');
			}
			
			$url = '';
			if($item['link']) {
				$url = $item['link'];
			}
			
			$type = '';
			if($item['type']) {
				$type = $item['type'];
			}
			
			$seller = 'Amazon';
			
			$itemToReturn = array(
		    'isbn'   => $isbn,
		    'price'  => $price,
		    'url'    => $url,
		    'seller' => $seller,
		    'type'   => $type
		  );
		  
		  array_push($itemsToReturn, $itemToReturn);
		  insert_data($db_table, $isbn, $itemToReturn);
		  
		  if( !empty($item['other_formats']) ) {
			  
			  $other_formats_url = $item['other_formats'];
			  
			  $other_formats_text = '';
			  if($item['other_formats/_text']) {
				  $other_formats_text = $item['other_formats/_text'];
			  }
			  
			  $curl_url = $base_url_amazon_other.$other_formats_url;
			  $results = exec_curl_importio($curl_url);
			  			  
			  if(!empty($results)) {
				  
				  $price = $results[0]['price'];
				  
				  $match = array();
					preg_match('([0-9-.,]{1,9})', $price, $match);
					$price = floatval($match[0]);
					$price = number_format($price, 2, '.', ',');
				  
				  $itemToReturn = array(
				    'isbn'   => $isbn,
				    'price'  => $price,
				    'url'    => $other_formats_url,
				    'seller' => $seller,
				    'type'   => $other_formats_text
				  );
				  
				  array_push($itemsToReturn, $itemToReturn);
				  insert_data($db_table, $isbn, $itemToReturn);
			  }
			  
		  }

		}
				
	}
	else {
		$results = get_data($db_table, $isbn);
		$itemsToReturn = array();
		while($row = $results->fetch_array(MYSQLI_ASSOC)){
			$itemToReturn = array(
			    'isbn'   => $row['isbn'],
			    'price'  => $row['price'],
			    'url'    => $row['url'],
			    'seller' => $row['seller'],
			    'type'   => $row['type']
			  );
				
			 array_push($itemsToReturn, $itemToReturn);
		}
	}
	
	// Action Header
	$response['action'] = array(
	  'status'      => 1,
	  'name'        => 'Book Price',
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