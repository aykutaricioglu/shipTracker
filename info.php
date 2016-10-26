<?php

$imoNumber = 9299927;
$mmsiNumber = 563000700;
$shipName = "MAERSK-SEVILLE";

$url = 'https://www.vesselfinder.com/vessels/' .$shipName. '-IMO-'.$imoNumber.'-MMSI-'.$mmsiNumber; 
$ch = curl_init();    
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); //https için gerekli panpa


curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //dönen deðer kayýt



  $address=curl_exec($ch);

//lat:
$latBaslangic = "latitude\">";
$bitis = "</span>/<span ";

$sKonum = strpos($address , $latBaslangic) ;
$sKonum = $sKonum + 11;
$temp = substr($address,  $sKonum-1,  $sKonum+1); 
$konum3= strpos($temp, $bitis);
$lat = substr($temp, 0, $konum3 );

//lon:

$lonBaslangic = "\"longitude\"";
$lonBitis = "</span>";

$lonKonum = strpos($temp, $lonBaslangic);
$lonBitisKonum = strpos($temp, $lonBitis);

$lon = substr($temp, $lonKonum+12, $lonBitisKonum); 

					
echo ("LAT : " . $lat. " LON : ". $lon . " <br>");

//speed

$speedBas = "itemprop=\"value\">";
$speedBitis = "&nbsp;";
$speedBasKonum = strpos($temp, $speedBas);
$speedBasKonum = $speedBasKonum + strlen($speedBasKonum);
$speedBitisKonum = strpos($temp, $speedBitis);

$strlength= ($speedBitisKonum-29) - $speedBasKonum;

$speedDegree = substr($temp, $speedBasKonum+23, $strlength); 

echo ("derece  : ".$speedDegree . "<br>");

$knBaslangic = "&nbsp;/";
$knBaslangicKonum = strpos($temp, $knBaslangic);
$knBaslangicKonum = $knBaslangicKonum + strlen($knBaslangic)+1;
$knBitisSoz= "kn.							</span>";
$knBitisSozKonum = strpos($temp, $knBitisSoz);
$knLength = $knBitisSozKonum- $knBaslangicKonum-1;

$speedKn = substr($temp, $knBaslangicKonum, $knLength); 
echo ("KN :".$speedKn);

?> 	