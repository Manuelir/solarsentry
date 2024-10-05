<?php

require __DIR__.'/solar_status_class.php';

$solarStatus = new solarStatus();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$year       = ($_GET['year']       ?? 'last');
$month      = ($_GET['month']      ?? 'last');
$day        = ($_GET['day']        ?? 'last');
$hour       = ($_GET['hour']       ?? 'last');
$filter     = ($_GET['filter']     ?? '');
$resolution = ($_GET['resolution'] ?? '');

echo json_encode($solarStatus->get_solar_images ($year, $month, $day, $hour, $filter, $resolution));