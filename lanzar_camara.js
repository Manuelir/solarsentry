document.querySelector('input[type="file"]').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        // Haz algo con el archivo, por ejemplo, mostrarlo en un elemento <img>
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        document.body.appendChild(img);
    }
});