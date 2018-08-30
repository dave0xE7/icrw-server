<?php

$eurbtc = json_decode(file_get_contents('https://beatcoin.pl/public/graphs/EURBTC/1day.json'));
$btcicr = json_decode(file_get_contents('https://beatcoin.pl/public/graphs/BTCICR/1day.json'));

$btceurval = $eurbtc[0]->open;
$icrbtcval = $btcicr[0]->open;

$icreurval = number_format($icrbtcval * $btceurval, 2, '.', ',');

function UpdateFile () {
	global $btceurval, $icrbtcval, $icreurval;
	file_put_contents('charts.json', json_encode(array($btceurval, $icrbtcval, $icreurval)));
}

UpdateFile();

function ShowCurrencies () {
	global $btceurval, $icrbtcval, $icreurval;
	echo ("Bitcoin in Euro ".$btceurval."<br>");
	echo ("Intercrone in Bitcoin ".$icrbtcval."<br>");
	echo ("Intercrone in Euro ".$icreurval."<br>");
}

?>