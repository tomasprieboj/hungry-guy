
<?php

include ( 'LunchExtractor.php' );

if ( $_SERVER['REQUEST_METHOD'] == "GET" ){
    $extractor = LunchExtractor::getInstance();

    $batidaContent = $extractor->getBatidaMenu( 'http://www.batida.sk/index.php/ct-menu-item-3' );
    $delfinContent = $extractor->getDelfinMenu( 'http://restauraciadelfin.sk/denne-menu-ruzinov/' );
    $ruzaContent = $extractor->getRuzaMenu( 'http://budvarpuburuzi.sk/obedove-menu/' );
    $bazantContent = $extractor->getBazantMenu( 'http://www.uzlatehobazanta.sk/denne-menu/' );

    $arr = array(
        "batida" => $batidaContent,
        "delfin" => $delfinContent,
        "ruza" => $ruzaContent,
        "bazant" => $bazantContent
    );

    echo json_encode( $arr );
}


