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
	need to correct dates leading zero
	*/
	public function getBatidaMenu( $url ){

		$menuArr = array();
		$isReadyToExtract = false;
		$currentDayMonth = date("j.m.");

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
			//$trDate = self::correctShortDate( $trDate );

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
			
			$menuDayMonth = $headArr[1];
			/*
			we found our day
			*/
			if( strcmp( $menuDayMonth, $currentDayMonth ) === 0 ){
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
		$correctPrice;
		$i = 0;
		$len = $ps->length;

		foreach( $ps as $p ){
			/*
			soup is firs paragraph
			*/
			if( $i === 0 ){
				$correctPrice = "Free";
			/*
			last paragraph is special and more expensive menu
			*/
			}else if( $i === ($len - 1) ){
				$correctPrice = self::getSpecialPrice( $p->nodeValue );
			}else{
				$correctPrice = $price;
			}

			$retArr[$i++] = array(
					"name" => self::getCorrectMenuName( $p->nodeValue ),
					"price" => $correctPrice
				);
		}

		return $retArr;

	}
	/*
	function extracts price from special menu
	*/
	private function getSpecialPrice( $menuStr ){

		$parts = explode( ' ', $menuStr );
		$partsLen = count($parts);

		return $parts[ $partsLen - 1 ];
	}
	/*
	function returns correct form of menu string
	as special menu contains price, we need to cutt it off the string
	*/
	private function getCorrectMenuName( $menuStr ){
		$parts = explode( '€', $menuStr );
		/*
		only special menu contains price
		*/
		if( count( $parts ) > 0 ){
			$parts = implode( ' ', explode( ' ', $menuStr , -1 ) );
			return $parts;
		}

		return $menuStr;
	}

	public function getRuzaMenu( $url ){
		$menuArr = array();
		$isCurrentDay = false;
		$counter = 0;
		$currentDayMonth = date("d.M Y");
		/*
		rawArr contains html tags
		array should contain 2 values
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
			$name = $spaceSplitArr[0];
			$price;
			/*
			sometimes int is not TAB
			*/
			if( count($spaceSplitArr) > 1){
				$price = $spaceSplitArr[1];
			}else{
				$price = "";
			}

			$retArr[] = array(
				"name" => $name,
				"price" => $price
			);
		}

		return $retArr;

	}
	/*
	function returns formated data Extracted from http://www.uzlatehobazanta.sk/denne-menu/
	menus prices does not contains prices (only empty strings)
	*/
	public function getBazantMenu( $url ){

		$menuArr = array();
		$dailyMenuArr = array();

		$dom = self::getPageContent( $url );
		$menuWrapper = $dom->getElementById('right-ornament');
		$divs = $menuWrapper->getElementsByTagName('div');
		
		foreach( $divs as $div ){
			$childsLength = $div->childNodes->length;
			/*
			daily menu has the most child elements (it looks like it is 18)
			*/
			if( $childsLength > 15){
				
				$dailyMenuArr = self::getCorrectDailyArr( $div->childNodes );
			}

			if( $childsLength > 1 && 	/*first div has no children so we ignore it*/
				$childsLength < 18 		/*second div has info about daily menus */
				){

				$menuArr = self::getCorrectDayArr( $div->childNodes );
				/*
				if returned array is not empty we found our day
				*/
				if( count( $menuArr ) !== 0 ){
					break;
				}
				
			}

		}
		/*
		we merge arrays where 
		first is daily menu
		second is current day menu
		*/
		return array_merge( $dailyMenuArr, $menuArr );
	}

	/*
	function returns array of daily menu and information about prices
	*/
	private function getCorrectDailyArr( $divsChilds ){

		$retArr = array();
		
		foreach( $divsChilds as $child ){
			if( strcmp( $child->nodeName, 'h4' ) === 0){
				$retArr[] = array(
						"name" => $child->nodeValue,
						"price" => ""
					);
			}

			if( strcmp( $child->nodeName, 'ol' ) === 0 ){
				$lis = $child->getElementsByTagName('li');
				foreach( $lis as $li ){
					$retArr[] = array(
						"name" => $li->nodeValue,
						"price" => ""
					);
				}

				break;
			}
		}

		return $retArr;
	}

	private function getCorrectDayArr( $divsChilds ){

		$retArr = array();
		$currentDayMonth = date("j.m.Y");
		$soupString = "";
		$soupStringcounter = 0;
		$correctArrElem;

		
		foreach( $divsChilds as $child ){
			/*
			searching for correct date
			*/
			if( strcmp( $child->nodeName, 'h4' ) === 0){
				$spaceExp = explode( ' ', $child->nodeValue );
				$extractedDate = preg_replace('/\s+/', '', $spaceExp[1]);//demove new line character
				/*
				this is not our day
				*/
				if( strcmp( $extractedDate, $currentDayMonth) !== 0){
					break;
				} 
			}
			/*
			extracting soup
			*/
			if( strcmp( $child->nodeName, 'p' ) === 0 &&
				$soupStringcounter < 2){//we only care about soup

				$soupString .= $child->nodeValue;
				$soupStringcounter++;
			}

			
			if( strcmp( $child->nodeName, 'ol' ) === 0){
				$lis = $child->getElementsByTagName('li');
				foreach( $lis as $li ){
					/*
					some menus are more expensive and menu string 
					contains dash or endash omg
					*/
					$moreExCheckDash = explode( '-', $li->nodeValue );
					$moreExCheckENDash = explode( '–', $li->nodeValue );

					if(count($moreExCheckENDash) > 1){
						$correctArrElem = self::getCorrectArrElem( $moreExCheckENDash );
					}else if(count($moreExCheckDash) > 1){
						$correctArrElem = self::getCorrectArrElem( $moreExCheckDash );
					}else{
						//it is just a string so make it array with one elem
						$correctArrElem = self::getCorrectArrElem( array($li->nodeValue ) );
					}
					
					$retArr[] = $correctArrElem;
					//var_dump($correctArrElem);
					
				}
						
			}

		}

		return $retArr;
	}

	function getCorrectArrElem( $menuItemArr ){
		$len = count( $menuItemArr );

		if( $len < 2 ){

			return array(
				"name" => $menuItemArr[0],
				"price" => ""
			);
		}

		return array(
			"name" => $menuItemArr[0],
			"price" => $menuItemArr[1] . "€"
		);
	}

}
