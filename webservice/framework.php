<?php
	
	require 'lib/caching.php';
	
	$db_table = 'framework';
	$keyword  = $_GET['kw'];
	
	$urlLanguages = "https://api.import.io/store/connector/e1a24167-ed83-43ae-a38c-1a01f52811b8/_query?input=webpage/url:http%3A%2F%2Fhotframeworks.com%2Flanguages%2Fphp&&_apikey=5f0f27e3af5b407aadad7dbd64192fefb8a442135c06933d72cd3a6e8afae0f059b619518717447ab74dec596ef3c0e4f663b20cacde7671a8bf19b638ee6aa184dd5fe8dac5cfd6c23f28de5322bab3";
	
	$urlFrameworksList = "https://api.import.io/store/connector/844983fe-1b24-4648-adc1-780f8938ba05/_query?&_apikey=5f0f27e3af5b407aadad7dbd64192fefb8a442135c06933d72cd3a6e8afae0f059b619518717447ab74dec596ef3c0e4f663b20cacde7671a8bf19b638ee6aa184dd5fe8dac5cfd6c23f28de5322bab3&input=webpage/url:http%3A%2F%2Fhotframeworks.com";
	
	$urlFrameworkDetails = "https://api.import.io/store/connector/50876dfe-7b74-472c-bef2-ae6ff693b95c/_query?&_apikey=5f0f27e3af5b407aadad7dbd64192fefb8a442135c06933d72cd3a6e8afae0f059b619518717447ab74dec596ef3c0e4f663b20cacde7671a8bf19b638ee6aa184dd5fe8dac5cfd6c23f28de5322bab3&input=webpage/url:http%3A%2F%2Fhotframeworks.com";
	
	if(check_last_update($db_table, $keyword, 15)){
		
		$results_languages = exec_curl_importio($urlLanguages);
		
		$itemsToReturn = array();
		
		foreach($results_languages as $language) {
			
			$programming_language = $language['language/_text'];
			
			if( strcasecmp($programming_language, $keyword)==0 ) {
				
				$relative_url_lang  = $language['language/_source'];
				
				$results_frameworks = exec_curl_importio($urlFrameworksList.$relative_url_lang);
				
				if(isset($results_frameworks[0])) {
					
					$results = $results_frameworks[0];
					
					$length  = count($results['framework/_text']);
					
					for($i=0; $i<$length; $i++) {
						
						$title = null;
						if(isset($results['framework/_text'][$i])) {
							$title = $results['framework/_text'][$i];
						}
						
						$url_detail = null;
						if(isset($results['framework'][$i])) {
							$url_detail = $results['framework'][$i];
						}
						
						$relative_url_fw = null;
						if(isset($results['framework/_source'][$i])) {
							$relative_url_fw = $results['framework/_source'][$i];
						}
																		
						$detail =  exec_curl_importio($urlFrameworkDetails.$relative_url_fw);
						
						$url_framework = null;
						if(isset($detail[0]['framework_link'])) {
							$url_framework = $detail[0]['framework_link'];
						}
						
						$description = null;
						if(isset($detail[0]['description'])) {
							$description = $detail[0]['description'];
						}

						$itemToReturn = array(
							'programming_language' => $programming_language,
							'title'                => $title,
							'url_detail'           => $url_detail,
							'url_framework'        => $url_framework,
							'description'          => $description
						);
						
						insert_data($db_table, $keyword, $itemToReturn);
				  
					  array_push($itemsToReturn, $itemToReturn);
						
					}
					
				}
								
			}
			
		}
		
	}
	else {
		
		$results = get_data($db_table, $keyword);
		$itemsToReturn = array();
		while($row = $results->fetch_array(MYSQLI_ASSOC)){
			$itemToReturn = array(
			    'programming_language' => $row['programming_language'],
			    'title'                => $row['title'],
			    'url_detail'           => $row['url_detail'],
			    'url_framework'         => $row['url_framework'],
			    'description'          => $row['description']
			  );
				
			 array_push($itemsToReturn, $itemToReturn);
		}
		
	}
	
	// Action Header
	$response['action'] = array(
	  'status'      => 1,
	  'name'        => 'Framework',
	  'description' => ''
	);
	
	// Action Data
	$response['data'] = $itemsToReturn;
		
	header('Content-Type: application/json');

	echo json_encode($response);
	
	function exec_curl_importio($url) {
		
		$done = false;
		$times = 0;
		
		do {
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_FAILONERROR, true);

			$result      = curl_exec($curl);
			$result_json = json_decode($result, true);
			
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
			
		}while( !$done && ($times<=10) );
		
		return $results;
		
	}