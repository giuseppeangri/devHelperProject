<?php
	
	function getXpathObject($url){
		
		$curl = curl_init($url);
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$headers = array(
			"Accept-Language: en-US,en;q=0.8,it;q=0.6",
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8"
		); 
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
		$html = curl_exec($curl);
		if(curl_errno($curl)) { // check for execution errors
	  	echo 'Scraper error: ' . curl_error($curl);
			exit;
		}
		curl_close($curl);
		
/*
		echo $html;
		exit();
*/
	
		$config = array(
			'indent'      => true,
			'output-html' => true,
			'wrap'        => 200
		);

		$DOM = new DOMDocument;

		libxml_use_internal_errors(true);
		

/*
		$write = tidy_repair_string($html, $config, 'latin1');
		$fh = fopen('mytextfile.html', 'w');
		fwrite($fh, $write);
		exit();
*/
	
		if( !$DOM->loadHTML(tidy_repair_string($html, $config, 'latin1')) ) {
			$errors = "";
	    foreach (libxml_get_errors() as $error) {
				$errors .= $error->message."<br/>"; 
			}
			libxml_clear_errors();
			print "libxml errors:<br>$errors";
			return;
		}
		
		$xpath = new DOMXPath($DOM);
		return $xpath;
	}
	
?>