<?php

require __DIR__.'/solar_status_class.php';

$solarStatus = new solarStatus();

$solarInfo = $solarStatus->get_activity();

/*
$solarInfo = Array (
    [date_last_updated] => 2023-10-07 17:00:06
    [solar_activity_last_24_hours] => low
    [largest_solar_event] => Array ( [class] => C4 [time] => 2023-10-06 18:17:00 )
    [number_sunspot_regions] => 9
    [solar_activity_forecast] => low
    [solar_wind_speed] => Array ( [speed] => 497 [time] => 2023-10-05 22:35:00 )
    [event_probability] => Array (
        [class_m] => Array ( [day_1] => 30 [day_2] => 30 [day_3] => 30 )
        [class_x] => Array ( [day_1] => 1 [day_2] => 1 [day_3] => 1 )
        [proton] => Array ( [day_1] => 1 [day_2] => 1 [day_3] => 1 )
        [pcaf] => green
    )
    [geomagnetic_field_forecast] => quiet to minor storm
    [geomagnetic_activity_probabilities] => Array (
        [middle_latitudes] => Array (
            [active] => Array ( [day_1] => 35 [day_2] => 20 [day_3] => 10 )
            [minor_storm] => Array ( [day_1] => 20 [day_2] => 5 [day_3] => 5 )
            [major_severe_storm] => Array ( [day_1] => 1 [day_2] => 1 [day_3] => 1 )
        )
        [high_latitudes] => Array (
            [active] => Array ( [day_1] => 10 [day_2] => 15 [day_3] => 15 )
            [minor_storm] => Array ( [day_1] => 25 [day_2] => 25 [day_3] => 20 )
            [major_severe_storm] => Array ( [day_1] => 50 [day_2] => 35 [day_3] => 15 )
        )
    )
    [penticton_10.7cm_flux] => Array (
        [Observed] => 155
        [Predicted] => Array ( [day_1] => 156 [day_2] => 156 [day_3] => 158 )
        [90 Day Mean] => 161
    )
)
*/
echo 'Solar activity last 24 hours: '.$solarInfo['solar_activity_last_24_hours']."<br>\n";
echo 'Número de manchas solares: '.$solarInfo['number_sunspot_regions']."<br>\n";
echo 'Solar wind speed: '.$solarInfo['solar_wind_speed']['speed']." Km/s<br>\n";