<?php

class LunchExtractor{
	
	var $batidaUrl;

	function __construct(){
	}

	private function getBatidaCURL(){

		$url = $this->batidaUrl;

		$ch = curl_init ();

		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		
		$page = curl_exec ($ch);
		
		$dom = new DOMDocument(); 
		$dom->loadHTML($page); 
			
		$data = $dom->getElementsByTagName('tbody'); 

		return $dom;

	}

	public function getBatidaMenu( $url ){

		$this->batidaUrl = $url;

		$dom = $this->getBatidaCURL();


		$ret = $dom->getElementsByTagName('tbody')->item(0);
		$ret = $ret->getElementsByTagName('tr');

		return $ret->item(5);
	}

}
