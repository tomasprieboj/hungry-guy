<?php
$uniqueValues = [];
$result = "";
$pscInput;

if( $_SERVER["REQUEST_METHOD"] == "POST" ){
	$psc = test($_POST["psc"]);
	if($psc !== ""){
		$pscInput = $psc;
		$urlPSC = "http://www.pscpsc.sk/index.php?input_txt_psc=" . $psc;
		$ch = curl_init ();

		curl_setopt ($ch, CURLOPT_URL, $urlPSC);
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		
		$page = curl_exec ($ch);
		
		$dom = new DOMDocument(); 
		$dom->loadHTML($page); 
		 
		$tables = $dom->getElementsByTagName('table'); 
		
		$rows = $tables->item(0)->getElementsByTagName('tr'); 
		global $result;
		
		$result .= "<table class=\"table\"><thead><tr><th>Obec</th><th>Cena paliva</th></tr></thead>";
		foreach ($rows as $row) {  
			$cols = $row->getElementsByTagName('td');
			if(isUnique($cols->item(0)->textContent)){
				echoValues($cols->item(0)->textContent);
				
			}
		}
		$result .= "</table>";
		
		curl_close($ch);
	}
}

function echoValues($obec){
	$palivoID = test($_POST["palivo"]);
	$obec = mb_convert_encoding($obec, "windows-1252", "UTF-8");
	$urlCenyPaliv = "http://www.benzin.sk/index.php?price_search_town=" . urlencode($obec) . "&price_submit=Vyh%BEada%9D&price_search_region=-1&price_search_brand=-1&price_search_fuel=" . $palivoID . "&price_search_day=7&selected_id=118&article_id=-1";
	$ch = curl_init ();

	curl_setopt ($ch, CURLOPT_URL, $urlCenyPaliv);
	curl_setopt($ch, CURLOPT_HTTPGET, 1);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	
	$page = curl_exec ($ch);
	
	$dom = new DOMDocument(); 
    $dom->loadHTML($page); 
	
    $tables = $dom->getElementsByTagName('table'); 
    $trs = $tables->item(12)->getElementsByTagName('tr'); 
	global $result;
	$i = 0;
	//ak nenaslo cerpacie stanice v obci
	if($trs->length === 1){
		return;
	}
	
	
    foreach ($trs as $tr) {
		if($tr->getAttribute('class') === "pump_list_row1" || $tr->getAttribute('class') === "pump_list_row2"){
			$result .= "<tr>";
			$tds= $tr->getElementsByTagName('td');
			$result .= "<td>" . $tds->item(3)->nodeValue . "</td> " ;
			$aTags = $tds->item(5)->childNodes;
			$imgs = $aTags->item(0)->childNodes;
			$srcLink = $imgs->item(0)->getAttribute('src');
			$result .="<td><img src=http://www.benzin.sk/" . $srcLink . "> Eur</td>";
			$result .="</tr>";
		}
	}

	curl_close($ch);
}

function test($data){
    $data = trim($data);
    $data = stripcslashes($data);
    $data = htmlspecialchars($data);
	$data = preg_replace('/\s+/', '', $data);
    return $data;
}

function isUnique($string){
	$isIn = false;
	global $uniqueValues;
	
	for($i = 0; $i < count($uniqueValues); $i++){
		if($uniqueValues[$i] === $string){
			$isIn = true;
			break;
		}	
	}
	
	if($isIn === false){
		$uniqueValues[] = $string;
		return true;
	}

	return false;
}