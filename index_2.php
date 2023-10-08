<?php

require __DIR__.'/solar_status_class.php';

$solarStatus = new solarStatus();

$solarInfo = $solarStatus->get_activity();

$solarCycle = $solarStatus->get_solar_cycle();

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

echo 'Solar activity last 24 hours: '.$solarInfo['solar_activity_last_24_hours']."<br>\n";
echo 'Número de manchas solares: '.$solarInfo['number_sunspot_regions']."<br>\n";
echo 'Solar wind speed: '.$solarInfo['solar_wind_speed']['speed']." Km/s<br>\n";
*/
?>

<!DOCTYPE html>
<html lang="es">
<head>
<title>Develop the Oracle of DSCOVR</title><meta charset="utf-8"><meta name="robots" content="index,follow"><meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=2.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="estilos.css" />
<style>

</style></head>
<body>
<header></header>
<main>
	<div class="capa_widget">
		<h1>Estado solar</h1>
		<div class="accordion">	
			<div class="accordion__content">
				
					<div class="capa_tierra" id="tierraImg">
						<img  class="tierra" src="/solar/imagenes/sol-de-ejemplo.png" width="50px" height="50px">
					</div>
					<div class="capa_sol" id="solImg">
						
						<div class="sol"><img src="/solar/imagenes/sol-de-ejemplo.png" width="360px" height="360px"></div>
						<div class="capa_sol__datos">
							<div class="velocidad">
								<div class="t07">Velocidad del viento solar</div>
								<div class="t15"><?php echo $solarInfo['solar_wind_speed']['speed']; ?> Km/h</div>
							</div>
							<div class="manchas">
								<div class="t07">Manchas solares</div><div class="t13"></div>
								<div class="t15"><?php echo  $solarInfo['number_sunspot_regions'];?></div>
							</div>
							<div class="peligrosidad">
								<div class="t07">Nivel de peligrosidad</div>
								<div class="t15"><?php echo $solarInfo['solar_activity_last_24_hours'] ?></div>
							</div>
						</div>
						
					</div>
				</div>
				<div class="accordion__content__datos cBlanco">
					<div>Fecha: <span id="valor"></span></div>
					<input type="range" id="rango" name="rango" min="0" max="100" step="1" value="50">
					
					
				</div>
			
			
		</div>

		
		
	</div>
	<div class="capa_redes_sociales">
		<a href="" title="Facebook" target="_blank" rel="noopener nofollow"><svg width="30px" height="30px" aria-hidden="true" role="img" viewBox="0 0 512 512"><use href="#icono-facebook"/></svg></a>	
		<a href="" title="Twitter" target="_blank" rel="noopener nofollow"><svg width="30px" height="30px" aria-hidden="true" role="img" viewBox="0 0 512 512"><use href="#icono-twitter"/></svg></a>
		<a href="" title="Canal de YouTube" target="_blank" rel="noopener nofollow"><svg width="30px" height="30px" aria-hidden="true" role="img" viewBox="0 0 576 512"><use href="#icono-youtube"/></svg></a>
		<a href="" title="Instagram" target="_blank" rel="noopener nofollow"><svg width="30px" height="30px" aria-hidden="true" role="img" viewBox="0 0 448 512"><use href="#icono-instagram"/></svg></a>
		<a href="" title="Tiktok" target="_blank" rel="noopener nofollow"><svg width="30px" height="30px" aria-hidden="true" role="img" viewBox="0 0 291.72499821636245 291.1"><use href="#icono-tiktok"/></svg></a>
		<input type="file" accept="image/*" capture="user">
	</div>
</main>
<footer>
</footer>
<script>
	const rango = document.getElementById('rango');
	const valor = document.getElementById('valor');

	// Agrega un evento para actualizar el valor mostrado cuando se cambia el rango.
	rango.addEventListener('input', function () {
		valor.textContent = rango.value;
	});

	// Inicializa el valor mostrado.
	valor.textContent = rango.value;
</script>

<script>
    // Obtén las referencias a las imágenes
    var tierraImg = document.getElementById("tierraImg");
    var solImg = document.getElementById("solImg");

    // Agrega un evento de clic a la imagen de la tierra
    tierraImg.addEventListener("click", function() {
        // Agrega la clase "oculto" a la imagen de la tierra
        tierraImg.classList.add("oculto");
        // Elimina la clase "oculto" de la imagen del sol
        solImg.classList.remove("oculto");
    });

    // Agrega un evento de clic a la imagen del sol
    solImg.addEventListener("click", function() {
        // Agrega la clase "oculto" a la imagen del sol
        solImg.classList.add("oculto");
        // Elimina la clase "oculto" de la imagen de la tierra
        tierraImg.classList.remove("oculto");
    });
</script>
<script>
document.querySelector('input[type="file"]').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        // Haz algo con el archivo, por ejemplo, mostrarlo en un elemento <img>
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        document.body.appendChild(img);
    }
});
</script>
<script>
	if (navigator.share) {
    navigator.share({
        title: 'Título de mi contenido',
        text: 'Descripción o texto adicional',
        url: 'https://www.ejemplo.com',
    })
    .then(() => console.log('Contenido compartido con éxito'))
    .catch((error) => console.log('Error compartiendo:', error));
} else {
    console.log('API Web Share no soportada');
}
</script>

<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="none">
	<defs>
		<g id="icono-facebook"><path fill="currentColor" d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></g>
		<g id="icono-twitter"><path fill="currentColor" d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"/></g>
		<g id="icono-youtube"><path fill="currentColor" d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"/></g>
		<g id="icono-instagram"><path fill="currentColor" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></g>
		<g id="icono-tiktok"><path fill="currentColor" d="M180.29 182.87V107.1a88.505 88.505 0 0 0 51.76 16.58V94.84a51.73 51.73 0 0 1-28.26-16.58 51.634 51.634 0 0 1-22.71-33.89h-27.25v149.24c-.71 17.27-15.27 30.69-32.54 29.99a31.278 31.278 0 0 1-24.06-12.9c-15.29-8.05-21.16-26.97-13.11-42.26a31.274 31.274 0 0 1 27.53-16.71c3.13.03 6.24.51 9.23 1.44V123.9c-37.74.64-67.82 32.19-67.18 69.93a68.353 68.353 0 0 0 18.73 45.86 67.834 67.834 0 0 0 39.29 11.61c37.82-.01 68.49-30.62 68.57-68.43z"/></g>
	</defs>
</svg>
<script type="text/javascript" src="/solar/geolocalizacion.js" charset="UTF-8"></script>
</body>
</html>