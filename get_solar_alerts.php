<?php

require __DIR__.'/solar_status_class.php';

$solarStatus = new solarStatus();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$from_date = ($_GET['from_date'] ?? null);
$to_date   = ($_GET['to_date']   ?? null);

echo json_encode($solarStatus->get_alerts($from_date, $to_date));