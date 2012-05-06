<?php

// We'll be outputting a JSON document
header('Content-type: application/json');
//header('Transfer-Encoding: chunked');

/**
 * Created by IntelliJ IDEA.
 * User: MKnuts6173c
 * Date: 11/1/11
 * Time: 1:10 PM
 * To change this template use File | Settings | File Templates.
 */
$volume_r = 0;
$abv_r = 0.00;
$price_r = 0.000;

//{"volume_r": "12","abv_r": "12.4","price_r": "5.00"}
//-----------------------------------------------------//
if(isset($_GET['abv_r'])) {
    $abv_r = round(strip_tags($_GET['abv_r']), 2);
}

if(isset($_GET['volume_r'])) {
    $volume_r = round(strip_tags($_GET['volume_r']));
}

if(isset($_GET['price_r'])) {
    $price_r = round(strip_tags($_GET['price_r']), 3);
}

$value = ($price_r / $volume_r) / $abv_r;
$value = round($value, 3);

$score = round($value * 100, 1);


$result = array('abv_r'=>"$abv_r", 'volume_r'=>"$volume_r", 'price_r'=>"$price_r", 'value_r'=>"$value", 'score'=>"$score");

#echo json_encode($result);
echo $_GET['callback'] . '(' . json_encode($result) . ');';
?>