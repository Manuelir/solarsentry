<?php

class solarStatus {

    public $mode_debbug = false;

    public $cache_file_activity = 'cache/solar_data_activity.json';
    // Orígen explicación: https://www.swpc.noaa.gov/products/report-and-forecast-solar-and-geophysical-activity#:~:text=Geophysical%20Activity%20Summary%2027%2F2100Z%20to,MeV%20at%20geosynchronous%20orbit
    public $source              = 'https://services.swpc.noaa.gov/text/sgarf.txt';
    // Esta fuente de datos contiene las alertas de eventos solares, pero no se utiliza en este script (por ahora)
    // Origen explicación: https://www.swpc.noaa.gov/products/alerts-watches-and-warnings
    public $cache_file_alerts = 'cache/solar_source_alerts_data.json';
    public $source_alerts     = 'https://services.swpc.noaa.gov/products/alerts.json';
    // Predicicón de actividad del ciclo solar futuro
    // Origen explicación: https://www.swpc.noaa.gov/products/predicted-sunspot-number-and-radio-flux
    public $cache_file_solar_cycle = 'cache/solar_cycle_data.json';
    public $source_solar_cycle     = 'https://services.swpc.noaa.gov/json/solar-cycle/predicted-solar-cycle.json';
    //
    public $cache_file_solar_images = 'cache/solar_images_data.json';
    public $source_solar_images     = 'https://sdo.gsfc.nasa.gov/assets/img/';

    // Datos de la prevision solar
    public $solar_data_activity;
    public $solar_cycle_data;
    public $solar_alerts_data;
    public $solar_images_data;

    function __construct () {

        // Mil millones de naves vendrán de la galaxia de Andrómeda, Alfa Centauri y Raticulín para destruir la Tierra
    }

    function get_cache_file_activity () {

        return __DIR__.'/'.$this->cache_file_activity;
    }

    function load_cached_solar_data_activity () {

        if ($this->mode_debbug) { return; }

        $file_path    = $this->get_cache_file_activity();
        $file_content = (file_exists($file_path) ? file_get_contents($file_path) : '');

        if (!empty($file_content)) { $this->solar_data_activity = json_decode($file_content, true); }
    }

    function save_cached_solar_data_activity () {

        $file_content = json_encode($this->solar_data_activity);

        if (!empty($file_content)) { file_put_contents($this->get_cache_file_activity(), $file_content); }
    }

    function get_activity () {

        $this->load_cached_solar_data_activity();

        if (       empty($this->solar_data_activity)
            or strtotime($this->solar_data_activity['date_last_updated']) < strtotime('-1 day')) {

            $file_content = file_get_contents($this->source);
            $file_content = preg_replace("/[\n\r]+/s", ' ', $file_content);

            // Inicializar un array para almacenar los datos extraídos
            $this->solar_data_activity = [
                'date_last_updated' => date('Y-m-d H:i:s'),
                'source'            => $this->source
            ];

            // 1. Actividad solar en las últimas 24 horas
            if (preg_match('/Solar activity has been at (.*?) levels for the past 24 hours./', $file_content, $matches)) {
                $this->solar_data_activity['solar_activity_last_24_hours'] = $matches[1];
            }

            // 2. Evento solar más grande
            if (preg_match('/The largest solar event of the period was a (.*?) event observed at (.*?)Z./', $file_content, $matches)) {
                $this->solar_data_activity['largest_solar_event'] = array(
                    'class' => $matches[1],
                    'time' => $this->convert_date_zulu($matches[2])
                );
            }

            // 3. Número de regiones de manchas solares en el disco
            if (preg_match('/There are currently (\d+) numbered sunspot regions on the disk./', $file_content, $matches)) {
                $this->solar_data_activity['number_sunspot_regions'] = (int)$matches[1];
            }

            // 4. Actividad solar prevista
            if (preg_match('/Solar activity is expected to be (.*?) with a chance for M-class flares on days (.*?)/', $file_content, $matches)) {
                $this->solar_data_activity['solar_activity_forecast'] = $matches[1];
            }

            // 5. Velocidad del viento solar
            if (preg_match('/Solar wind speed reached a peak of (\d+) km\/s at (.*?)Z./', $file_content, $matches)) {
                $this->solar_data_activity['solar_wind_speed'] = array(
                    'speed' => (int)$matches[1],
                    'time' => $this->convert_date_zulu($matches[2])
                );
            }

            // 0. Event probabilities

            if (preg_match_all('/(class m|class x|proton)\s+(\d+)\/(\d+)\/(\d+)/i', $file_content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $type = str_replace(['-', ' '], '_', strtolower($match[1]));
                    $this->solar_data_activity['event_probability'][$type] = [
                        'day_1' => (int)$match[2],
                        'day_2' => (int)$match[3],
                        'day_3' => (int)$match[4]
                    ];
                }
            }
            if (preg_match('/PCAF\s+([^\s]+)/', $file_content, $matches)) {
                $this->solar_data_activity['event_probability']['pcaf'] = $matches[1];
            }

            // 6. Probabilidades de llamaradas de clase M y X
            if (preg_match('/Class M (\d+)\/(\d+)\/(\d+) Class X (\d+)\/(\d+)\/(\d+)/', $file_content, $matches)) {
                $this->solar_data_activity['flare_probabilities'] = array(
                    'class_M' => array($matches[1], $matches[2], $matches[3]),
                    'class_X' => array($matches[4], $matches[5], $matches[6])
                );
            }

            // 7. Probabilidades de actividad, tormentas menores y tormentas mayores-severas para latitudes medias y altas
            if (preg_match_all('/(Active|Minor Storm|Major-severe storm) (\d+)\/(\d+)\/(\d+)/', $file_content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $this->solar_data_activity['geomagnetic_activity_probabilities'][$match[1]] = array($match[2], $match[3], $match[4]);
                }
            }

            // 8. Pronóstico del Campo geomagnético
            if (preg_match('/The geomagnetic field is expected to be at (.*?) levels on day one/', $file_content, $matches)) {
                $this->solar_data_activity['geomagnetic_field_forecast'] = $matches[1];
            }

            // 9. Probabilidades de actividad geomagnética

            /*
                Latitudes Medias (Middle Latitudes):
                    Activo: Indica una actividad geomagnética elevada pero no necesariamente tormentosa.
                    Tormenta Menor (Minor Storm): Refleja una tormenta geomagnética de menor intensidad.
                    Tormenta Mayor-Severa (Major-Severe Storm): Indica una tormenta geomagnética de mayor intensidad, que puede tener efectos significativos en
                        las tecnologías basadas en el espacio y en tierra.
                Latitudes Altas (High Latitudes):
                    Los mismos niveles de actividad descritos para las latitudes medias se aplican a las latitudes altas, pero las tormentas geomagnéticas tienden
                        a ser más intensas y frecuentes en las latitudes altas debido a la cercanía de los polos magnéticos.
            */
            $regex = '/(A.  Middle Latitudes|B.  High Latitudes)\s+'.
                     '((Active|Minor storm|Major-severe storm)\s+(\d+)\/(\d+)\/(\d+)\s+)?'.
                     '((Active|Minor storm|Major-severe storm)\s+(\d+)\/(\d+)\/(\d+)\s+)?'.
                     '((Active|Minor storm|Major-severe storm)\s+(\d+)\/(\d+)\/(\d+)\s+)?/i';
            if (preg_match_all($regex, $file_content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $activity_level = (stripos($match[1], 'middle') !== false ? 'middle_latitudes' : 'high_latitudes');
                    foreach ([3, 8, 13] as $level) {
                    if (isset($match[$level]))
                        $type = str_replace(['-', ' '], '_', strtolower($match[$level]));
                        $this->solar_data_activity['geomagnetic_activity_probabilities'][$activity_level][$type] = [
                            'day_1' => (int)$match[$level + 1],
                            'day_2' => (int)$match[$level + 2],
                            'day_3' => (int)$match[$level + 3]
                        ];
                    }
                }
            }

            // 10. Índice Penticton 10.7 cm Flux
            $regex = '/IV\.\s*Penticton\s*10\.7\s*cm\s*Flux\s*'.
                     'Observed\s+\d+\s+\w+\s(\d+)\s+'.
                     'Predicted\s+\d+\s+\w+\-\d+\s+\w+\s(\d+)\/(\d+)\/(\d+)\s+'.
                     '90 Day Mean\s+\d+\s+\w+\s(\d+)'.
                     '/i';
            if (preg_match($regex, $file_content, $matches)) {
                $this->solar_data_activity['penticton_10.7cm_flux'] = array(
                    'Observed'    => (int)$matches[1],
                    'Predicted'   => [
                        'day_1'   => (int)$matches[2],
                        'day_2'   => (int)$matches[3],
                        'day_3'   => (int)$matches[4]
                    ],
                    '90 Day Mean' => (int)$matches[5]
                );
            }

            $this->save_cached_solar_data_activity();
        }

        return $this->solar_data_activity;
    }

    private function convert_date_zulu ($date_zulu) {

        list($day, $time) = explode('/', $date_zulu);

        $year  = date('Y');
        $month = date('m');

        // Crear una cadena de fecha y hora completa (Año-Mes-Día Hora:Minuto:Segundo)
        $date_string    = sprintf('%04d-%02d-%02d %02d:%02d:00', $year, $month, $day, substr($time, 0, 2), substr($time, 2, 2));
        // Crear un objeto DateTime a partir de la cadena de fecha y hora
        $date_object    = DateTime::createFromFormat('Y-m-d H:i:s', $date_string);
        // Formatear el objeto DateTime al formato deseado
        $formatted_date = $date_object->format('Y-m-d H:i:s');

        return $formatted_date;
    }

    function get_cache_file_solar_cycle () {

        return __DIR__.'/'.$this->cache_file_solar_cycle;
    }

    function load_cached_solar_cycle_data () {

        if ($this->mode_debbug) { return; }

        if (empty($this->solar_cycle_data)) {

            $file_path    = $this->get_cache_file_solar_cycle();
            $file_content = (file_exists($file_path) ? file_get_contents($file_path) : '');

            if (!empty($file_content)) { $this->solar_cycle_data = json_decode($file_content, true); }
        }
    }

    function save_cached_solar_cycle_data () {

        $file_content = json_encode($this->solar_cycle_data);

        if (!empty($file_content)) { file_put_contents($this->get_cache_file_solar_cycle(), $file_content); }
    }

    function load_cached_solar_images_data () {

        if ($this->mode_debbug) { return; }

        if (empty($this->solar_images_data)) {

            $file_path    = $this->get_cache_solar_images();
            $file_content = (file_exists($file_path) ? file_get_contents($file_path) : '');

            if (!empty($file_content)) { $this->solar_images_data = json_decode($file_content, true); }
        }
    }

    function save_cached_solar_images_data () {

        $file_content = json_encode($this->solar_images_data);

        if (!empty($file_content)) { file_put_contents($this->get_cache_solar_images(), $file_content); }
    }

    function get_solar_cycle () {

        $this->load_cached_solar_cycle_data();

        if (       empty($this->solar_cycle_data)
            or strtotime($this->solar_cycle_data['date_last_updated']) > strtotime('-1 day')) {

            $file_content           = file_get_contents($this->source_solar_cycle);
            $this->solar_cycle_data = [
                'date_last_updated' => date('Y-m-d H:i:s'),
                'source'            => $this->source_solar_cycle,
                'predictions'       => json_decode($file_content, true)
            ];

            $this->save_cached_solar_cycle_data();
        }

        return $this->solar_cycle_data;
    }

    function get_cache_file_alerts () {

        return __DIR__.'/'.$this->cache_file_alerts;
    }

    function get_cache_solar_images () {

        return __DIR__.'/'.$this->cache_file_solar_images;
    }

    // Función para obtener los datos de la URL
    function get_data_from_url ($url) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    function load_cached_alerts_data () {

        if ($this->mode_debbug) { return; }

        if (empty($this->solar_alerts_data)) {

            $file_path    = $this->get_cache_file_alerts();
            $file_content = (file_exists($file_path) ? file_get_contents($file_path) : '');

            if (!empty($file_content)) { $this->solar_alerts_data = json_decode($file_content, true); }
        }
    }

    function save_cached_alerts_data () {

        $file_content = json_encode($this->solar_alerts_data);

        if (!empty($file_content)) { file_put_contents($this->get_cache_file_alerts(), $file_content); }
    }

    function get_alerts ($from_date = null, $to_date = null) {

        $this->load_cached_alerts_data($from_date, $to_date);

        $from_timestamp = (!empty($from_date) ? strtotime(is_numeric($from_date) ? date('Y-m-d h:i:s', $from_date) : $from_date) : strtotime('yesterday'));
        $to_timestamp   = (!empty($to_date)   ? strtotime(is_numeric($to_date)   ? date('Y-m-d h:i:s', $to_date)   : $to_date)   : strtotime('tomorrow'));

        if (       empty($this->solar_alerts_data)
            or strtotime($this->solar_alerts_data['date_last_updated']) < strtotime('-1 day')) {

            $file_content = file_get_contents($this->source_alerts);
            $data_alers   = json_decode($file_content, true);

            if (!isset($this->solar_alerts_data)) { $this->solar_alerts_data = []; }

            $this->solar_alerts_data['date_last_updated'] = date('Y-m-d H:i:s');
            $this->solar_alerts_data['original_data']     = $data_alers;

            $this->save_cached_alerts_data();
        }

        $alerts = [
            'date_last_updated' => $this->solar_alerts_data['date_last_updated'],
            'source'            => $this->source_alerts,
            'messages'          => []
        ];

        foreach ($this->solar_alerts_data['original_data'] as $alert) {

            $product_id = $alert['product_id'];
            $datetime   = $alert['issue_datetime'];
            $timestamp  = strtotime($datetime);

            if ($timestamp < $from_timestamp or $timestamp > $to_timestamp) { continue; }

            // Protege forato de fecha "Oct 07:"
            $alert['message'] = preg_replace("/([\n\r]*[a-z]{3}\s+\d+):/is", '\\1=', $alert['message']);
            // Escapa formato "Título: texto"
            $alert['message'] = preg_replace("/([^:]+:)[\n\r]*/s",  '\\1',    $alert['message']);
            $alert['message'] = preg_replace("/[\n\r]+([^:]+\:)/s",  '|\\1',  $alert['message']);
            $alert['message'] = preg_replace("/[\n\r]+/s",               '|', $alert['message']);
            $alert['message'] = preg_replace("/\|([^:]+)\|/s", '|Info: \\1|', $alert['message']);
            $alert['message'] = preg_replace("/\|([^:]+)$/s",  '|Info: \\1',  $alert['message']);
            $alert['message'] = preg_replace("/\|([^:]+\|)/s",       ' \\1',  $alert['message']);
            $alert['message'] = preg_replace("/\|([^:]+)$/s",        ' \\1',  $alert['message']);
            $alert['message'] = preg_replace("/\|([^:]+)$/s",        ' \\1',  $alert['message']);

            preg_match_all('/(([^:]+):\s*([^\|]+)\|*)/s', $alert['message'], $matches, PREG_SET_ORDER);

            if (empty($matches)) { continue; }

            $message = [];

            foreach ($matches as $match) {

                $key = str_replace([' ', '-', '.'], '_', strtolower($match[2]));

                if (strpos($key, '|') !== false) {

                    $key = preg_replace('/^[^\|]+\|/', '', $key);

                    if (!$key) { continue; }
                }

                // Detecta el texto del tipo de alerta porque está en mayúsculas todo
                if ($match[2] ===  mb_strtoupper($match[2])) {

                    $key                   = 'alert_description';
                    $message['alert_type'] = $match[2];
                }

                $message[$key] = (!empty($message[$key]) ? $message[$key]."\n\r" :  '').trim($match[3]);
            }

            $alerts['messages'][] = [

                'datetime'   => $datetime,
                'timestamp'  => $timestamp,
                'product_id' => $product_id,
                'message'    => $message,
            ];
        }

        return $alerts;
    }

    function get_solar_image_filters () {

        $this->load_cached_solar_images_data();

        if (!isset($this->solar_images_data) or !isset($this->solar_images_data['filters_list'])) {
            $this->get_solar_images();
        }
        sort  ($this->solar_images_data['filters_list']);

        return $this->solar_images_data['filters_list'];
    }

    function get_solar_image_resolutions () {

        $this->load_cached_solar_images_data();

        if (!isset($this->solar_images_data) or !isset($this->solar_images_data['resolutions_list'])) {
            $this->get_solar_images();
        }
        sort  ($this->solar_images_data['resolutions_list']);

        return $this->solar_images_data['resolutions_list'];
    }

    function get_solar_images ($year = 'last', $month = 'last', $day = 'last', $hour = 'last', $filter = '', $resolution = '') {

        $this->load_cached_solar_images_data();

        if (!isset($this->solar_images_data)) { $this->solar_images_data = []; }

        $refresh    = false;
        $filter     = ($filter     ? $filter     : '0171');
        $resolution = ($resolution ? $resolution : '1024');

        $year       = (is_numeric($year)  ? $year  : 'last');
        $month      = (is_numeric($month) ? $month : 'last');
        $day        = (is_numeric($day)   ? $day   : 'last');
        $hour       = (is_numeric($hour)  ? $hour  : 'last');

        if ($year === 'last' and $month === 'last' and $day === 'last' and $hour === 'last') {

            // Modo íltima imagen disponible
            $mode = 'latest';

            if (empty( $this->solar_images_data['date_last_updated']) or strtotime( $this->solar_images_data['date_last_updated']) <= strtotime('-1 hour')) {
                // Hay que actualizar las últimas imágenes cada hora
                $refresh = true;
            }

        } else {

            // Modo imagen histórica concreta
            $mode  = 'browser';
            $year  = sprintf('%04d', (is_numeric($year)  ? $year  : date('Y')));
            $month = sprintf('%02d', (is_numeric($month) ? $month : date('m')));
            $day   = sprintf('%02d', (is_numeric($day)   ? $day   : date('d')));
            $hour  = sprintf('%02d', (is_numeric($hour)  ? $hour  : date('H')));
            $date  = $year.$month.$day;

            if (empty($this->solar_images_data['images'][$date][$resolution][$filter][$hour]) and $year.$month.$day.$hour === date('YmdH')) {
                // Si en la hora actual aún no hay imágenes, se intenta mostrar la de la última hora disponible
                if (     !empty($this->solar_images_data['images'][$date][$resolution][$filter])) {
                          ksort($this->solar_images_data['images'][$date][$resolution][$filter]);
                            end($this->solar_images_data['images'][$date][$resolution][$filter]);
                    $hour = key($this->solar_images_data['images'][$date][$resolution][$filter]);
                } else if (   !empty($this->solar_images_data['images'])) {
                               ksort($this->solar_images_data['images']);
                                 end($this->solar_images_data['images']);
                    $test_date = key($this->solar_images_data['images']);
                    if (      !empty($this->solar_images_data['images'][$test_date][$resolution][$filter])) {
                               ksort($this->solar_images_data['images'][$test_date][$resolution][$filter]);
                                 end($this->solar_images_data['images'][$test_date][$resolution][$filter]);
                        $hour  = key($this->solar_images_data['images'][$test_date][$resolution][$filter]);
                        $date  = $test_date;
                        $year  = substr($date, 0, 4);
                        $month = substr($date, 4, 2);
                        $day   = substr($date, 6, 2);
                    }
                }
            }

            if (empty($this->solar_images_data['images'][$date][$resolution][$filter][$hour]) and strtotime($year.'-'.$month.'-'.$day.' '.$hour.':00:00') < time()) {
                // Si no existe el directorio temporal hay que añadirlo a la cache
                $refresh = true;
            }
        }

        if ($refresh) {

            $url          = $this->source_solar_images.$mode.'/';
            $html_content = $this->get_data_from_url($url);

            $this->solar_images_data['date_last_updated'] = date('Y-m-d H:i:s');

            if (!isset($this->solar_images_data['filters_list']))     { $this->solar_images_data['filters_list']     = []; }
            if (!isset($this->solar_images_data['resolutions_list'])) { $this->solar_images_data['resolutions_list'] = []; }

            // Use DOMDocument to parse the HTML and extract the files from the directory
            $dom = new DOMDocument();
            @$dom->loadHTML($html_content);
            $links = $dom->getElementsByTagName('a');
            // Iterate over each link to identify image files
            foreach ($links as $link) {

                $$file_resolution = '';
                $href = $link->getAttribute('href');
                // Check if the file is an image (e.g., .jpg, .png)
                if (preg_match('/\.(jpg|png)$/i', $href)) {

                    if ($mode === 'latest') {
                        // Extract information from the file name using regex
                        if (preg_match('/^latest_([^_]+)_([^_]+)\.\w+$/', $href, $matches)) {
                            $file_resolution = $matches[1];
                            $file_filter     = $matches[2];
                            $this->solar_images_data[$mode][$file_resolution][$file_filter] = $href;
                            if (!is_numeric($file_resolution)) { $file_resolution = ''; }
                        }

                    } else {

                        if (preg_match('/^(\d{8})_(\d{6})_([^_]+)_([^_]+)\.\w+$/', $href, $matches)) {
                            $file_date       = $matches[1]; // YearMonthDay
                            $file_time       = $matches[2]; // HourMinuteSecond
                            $file_hour       = substr($file_time, 0, 2);
                            $file_minute     = substr($file_time, 2, 2);
                            $file_resolution = $matches[3];
                            $file_filter     = $matches[4];
                            // Organize the images in the array
                            $this->solar_images_data[$mode][$file_date][$file_resolution][$file_filter][$file_hour][$file_minute] = $href;
                        }
                    }

                    if ($file_resolution) {
                        // Add the filter and resolution to the list if they are not already there
                        if (!in_array($file_filter, $this->solar_images_data['filters_list'])) {
                            $this->solar_images_data['filters_list'][] = $file_filter;
                        }
                        if (!in_array($file_resolution, $this->solar_images_data['resolutions_list'])) {
                            $this->solar_images_data['resolutions_list'][] = $file_resolution;
                        }
                    }
                }
            }

            $this->save_cached_solar_images_data();
        }

        $images = [];

        if ($mode === 'latest') {

            if (!empty($this->solar_images_data[$mode][$resolution][$filter])) {
                // Modo última imagen disponible
                $images = [
                    'date'       => date('Y-m-d'),
                    'hour'       => date('H'),
                    'filter'     => $filter,
                    'resolution' => $resolution,
                    'image'      => $this->source_solar_images.$mode.'/'.$this->solar_images_data[$mode][$resolution][$filter]
                ];

            } else {
                // Error
                $images = [
                    'error'      => 1,
                    'error_text' => 'No images found for the specified date and time.',
                    'date'       => date('Y-m-d'),
                    'hour'       => date('H'),
                    'filter'     => $filter,
                    'resolution' => $resolution
                ];
            }

        } else if (   isset($this->solar_images_data[$mode][$date][$resolution][$filter][$hour])) {
            // Modo imagen concreta
            $image  = reset($this->solar_images_data[$mode][$date][$resolution][$filter][$hour]);
            $year   = substr($date, 0, 4);
            $month  = substr($date, 4, 2);
            $day    = substr($date, 6, 2);
            $images = [
                'date'       => $year.'-'.$month.'-'.$day,
                'hour'       => $hour,
                'filter'     => $filter,
                'resolution' => $resolution,
                'image'      => $this->source_solar_images.$mode.'/'.$year.'/'.$month.'/'.$day.'/'.$image
            ];

        } else {
            // Error
            $images = [
                'error'      => 1,
                'error_text' => 'No images found for the specified date and time.',
                'date'       => $year.'-'.$month.'-'.$day,
                'hour'       => $hour,
                'filter'     => $filter,
                'resolution' => $resolution
            ];
        }

        return $images;
    }
}