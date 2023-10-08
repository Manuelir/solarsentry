<?php

require __DIR__.'/solar_status_class.php';

$solarStatus = new solarStatus();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

echo json_encode($solarStatus->get_solar_cycle());