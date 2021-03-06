
<?php 

include ( 'LunchExtractor.php' );

$extractor = LunchExtractor::getInstance();
$batidaContent = $extractor->getBatidaMenu( 'http://www.batida.sk/index.php/ct-menu-item-3' );
$delfinContent = $extractor->getDelfinMenu( 'http://restauraciadelfin.sk/denne-menu-ruzinov/' );
$ruzaContent = $extractor->getRuzaMenu( 'http://budvarpuburuzi.sk/obedove-menu/' );
$bazantContent = $extractor->getBazantMenu( 'http://www.uzlatehobazanta.sk/denne-menu/' );


?>
<!DOCTYPE html>
<html>
<head>
	<meta id="viewportMeta" name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	
	<link rel="stylesheet" type="text/css" href="css/styles.css">
	
	
    <meta charset="utf-8">
<title>Menu Extractor</title>
</head>
<body>
	<div class="container">
		
	
	<p>
		<?php
		echo "<img src='./images/batidaText.png' alt='batida restaurant text'><br>";
		foreach( $batidaContent as $item ){
			echo $item['name'] . " " . $item['price'] . "<br>";
		}

		echo "<br><img src='./images/delfinLogo.png' alt='delfin restaurant logo'><br>";
		foreach( $delfinContent as $item ){
			echo $item['name'] . " " . $item['price'] . "<br>";
		}
	
		echo "<br><img src='./images/ruzaLogo.png' alt='ruza restaurant logo'><br>";
		foreach( $ruzaContent as $item ){
			echo $item['name'] . " " . $item['price'] . "<br>";
		}
	
		echo "<br><img src='./images/bazantLogo.png' alt='bazant restaurant logo'><br>";
		foreach( $bazantContent as $item ){
			echo $item['name'] . " " . $item['price'] . "<br>";
		}

		?>
	</p>
	</div>
	<!-- jQuery library -->
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	
	<!-- Latest compiled JavaScript -->
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script type="text/javascript" scr="js/mustache/mustache.min.js"></script>
	<script type="text/javascript" src="js/pubSub.js"></script>
	<script type="text/javascript" src="js/script.js"></script>
</body>
</html>