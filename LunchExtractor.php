<?php

/*
Singleton class
*/
final class LunchExtractor{

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
	/*
	!!! this function may not be needed
	*/
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
	/*
	need to correct dates leading zero
	*/
	public function getBatidaMenu( $url ){

		$menuArr = array();
		$isReadyToExtract = false;
		$currentDayMonth = date("d.m.");

		$dom = self::getPageContent( $url );
		$tBody = $dom->getElementsByTagName( 'tbody' )->item(0);
		$trs = $tBody->getElementsByTagName( 'tr' );
		
		$i = 0;
		foreach ( $trs as $tr ) {
			$tds = $tr->getElementsByTagName('td');
			/*
			remove whitespaces
			*/
			$trDate = preg_replace('/\s+/', '', $tds->item(0)->nodeValue );
			$trDate = self::correctShortDate( $trDate );


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

		$menuArr = array();
		$isReadyToExtract = false;
		$currentDayMonth = date("j.m.Y");

		$dom = self::getPageContent( $url );
		/*
		we need elements by its class so we use XPath
		*/
		$finder = new DomXPath( $dom );
		$nodes = $finder->query( "//div[contains(concat(' ', normalize-space(@class), ' '), ' post ')] ");
		$currentDayNode;
		$index = 0;

		foreach( $nodes as $node ){
			$header = $node->getElementsByTagName('h2')->item(0);
			$headValue = $header->nodeValue;
			$headArr = explode( ' ', $headValue );
			/*
			we found our day
			*/
			if( strcmp( $headArr[1], $currentDayMonth ) === 0 ){
				$ps = $node->getElementsByTagName('p');
				/*
				6th is price
				*/
				$menuArr = self::convertPsToArr( $ps ,$headArr[5]);
	
				break;
			}
			
		}
		
		return $menuArr;
		
	}

	private function convertPsToArr( $ps, $price ){

		$retArr = array();
		$i = 0;
		foreach( $ps as $p ){
			$retArr[$i++] = array(
					"name" => $p->nodeValue,
					"price" => $price
				);
		}

		return $retArr;

	}

	public function getRuzaMenu( $url ){
		$menuArr = array();
		$isCurrentDay = false;
		$counter = 0;
		$currentDayMonth = date("d.M Y");
		/*
		array shwould contain 2 values
		1: soup
		2: main courses (not parsed)
		*/
		$rawArr = array();
		$dom = self::getPageContent( $url );
		$finder = new DomXPath( $dom );
		$nodes = $finder->query( "//div[contains(concat(' ', normalize-space(@class), ' '), ' entry-content ')] ");
		/*
		all entry-content children
		*/
		foreach( $nodes->item(0)->childNodes as $child ){
			
			if( strcmp( $child->nodeName, 'h5' ) === 0 && 
				strcmp( $child->nodeValue, $currentDayMonth ) === 0){

				$isCurrentDay = true;
			}
			/*
			As html structure has no divs for each day,
			we need to cneck for specific tag names and count elements
			we want to read
			*/
			if(strcmp( $child->nodeName, 'p' ) === 0 && 
				$isCurrentDay == true &&
				$counter < 2){
				
				$rawArr[ $counter++ ] = $child->C14N();
			}
			/*
			we found all values 
			*/
			if( $counter === 2 ){
				$menuArr = self::getFormatedData( $rawArr );
				break;
			}
			
		}
		return $menuArr;

	}
	
	private function getFormatedData( $rawArr ){

		$retArr = array();

		$retArr[] = array(
			"name" => strip_tags($rawArr[0]),
			"price" => "Free"
		);
		$brBreakArr = explode( '<br>', $rawArr[1] );

		for( $i = 0; $i < count($brBreakArr); $i++ ){
			$srippedElem = strip_tags( $brBreakArr[$i] );
			/*
			It looks like main corses name and price
			are separated by TAB character ffs
			*/
			$spaceSplitArr = explode( '	',  $srippedElem );
			echo $spaceSplitArr[1] . "<br>";

			$retArr[] = array(
				"name" => $spaceSplitArr[0],
				"price" => $spaceSplitArr[1]
			);
		}

		return $retArr;

	}

	public function getBazantMenu( $url ){
		//TODO
	}

	public function getMlynMenu( $url ){
		//TODO
	}

}