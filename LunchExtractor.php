<?php

class LunchExtractor{

	private static $inst = null;

	private function __construct(){
	}

	private function __clone(){
	}

	public static function getInstance(){

		if( self::$inst === null )
			self::$inst = new LunchExtractor();
		
		return self::$inst;
	}

	private function getPageContent( $url ){

		$ch = curl_init ();

		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HTTPGET, 1 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		
		$page = curl_exec ($ch);
		/*
		for not logging Invalid nav tag
		*/
		libxml_use_internal_errors( true );
		$dom = new DOMDocument(); 
		$dom->loadHTML($page); 
		libxml_use_internal_errors( false );

		return $dom;
	}

	private function correctShortDate( $dateStr ){
		
		if( strlen( $dateStr ) > 5 || strlen( $dateStr )  <= 2)
			return $dateStr;

		$dateArr = explode( '.', $dateStr );
		/*
		if days are short for example: 1 => 01
		*/
		if( strlen($dateArr[0]) < 2  )
			$dateArr[0] = "0" . $dateArr[0];
		if( strlen($dateArr[1]) < 2 )
			$dateArr[1] = "0" . $dateArr[1];

		return implode( '.', $dateArr);
	}
	
	public function getBatidaMenu( $url ){

		$menuArr = array();
		$isReadyToExtract = false;
		$currentDayMonth = date("d.m.");

		$dom = $this->getPageContent( $url );
		$tBody = $dom->getElementsByTagName( 'tbody' )->item(0);
		$trs = $tBody->getElementsByTagName( 'tr' );
		
		$i = 0;
		foreach ( $trs as $tr ) {
			$tds = $tr->getElementsByTagName('td');
			/*
			remove whitespaces
			*/
			$trDate = preg_replace('/\s+/', '', $tds->item(0)->nodeValue );
			$trDate = $this->correctShortDate( $trDate );


			if( strcmp( $trDate, $currentDayMonth ) === 0 ){
				$isReadyToExtract = true;
			}
			/*
			it looks like Batida has only 4 menus per day +1 is header
			*/
			if( $isReadyToExtract && ($i < 5)){
				$menuArr[$i++] = array(
					"name" => $tds->item(2)->nodeValue,
					"price" => $tds->item(3)->nodeValue
				);
			}
			/*
			we already have what we want ... stop searching
			*/
			if( $i >= 5){
				break;
			}
			
		}

		return $menuArr;
	}

	public function getDelfinMenu( $url ){

	}

	public function getRuzaMenu( $url ){

	}

	public function getBazantMenu( $url ){

	}

	public function getMlynMenu( $url ){

	}

}
