 // Comprobamos si el navegador admite la geolocalización
 if ("geolocation" in navigator) {
    navigator.geolocation.getCurrentPosition(function(position) {
        // Obtenemos la latitud y la longitud
        var latitud = position.coords.latitude;
        var longitud = position.coords.longitude;

        // Mostramos la latitud y la longitud en la página
        document.getElementById("latitud").textContent = "Latitude " + latitud;
        document.getElementById("longitud").textContent = "Longitude " + longitud;
    });
} else {
    alert("Geolocalización no es compatible en este navegador.");
}