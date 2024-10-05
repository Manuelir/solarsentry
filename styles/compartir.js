
	if (navigator.share) {
    navigator.share({
        title: 'Solar Centry',
        text: 'Descripción o texto adicional',
        url: 'https://api.solarsentry.earth/',
    })
    .then(() => console.log('Contenido compartido con éxito'))
    .catch((error) => console.log('Error compartiendo:', error));
} else {
    console.log('API Web Share no soportada');
}