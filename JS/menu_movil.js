
/* Obtener el contenedor */
var contenedor = document.getElementById('menu_movil');
contenedor.innerHTML = `
    <div class="g-700 m-2 font-size-xxl change-cursor-on-hover" id="cerrar_menu_movil" onclick="ocultarMenu()">x</div>
    <div id='menu_movil_usuario'>
        <div class="g-700 p-3 m-t-2 font-size-xl" onclick="document.location = 'registro.html'">Registro</div>
        <div class="g-700 p-3 m-t-2 font-size-xl" onclick="document.location = 'login.html'">Login</div>
    </div>
    <div class="g-700 bt w-100 align-item-right align-item-left m-t-2"></div>
    <div class="g-500 p-3 font-size-xl" onclick="document.location = 'noticias.html'">Noticias</div>
    <div class="g-500 p-3 m-t-1 font-size-xl" onclick="document.location = 'videojuegos.html'">Videojuegos</div>
    <div class="g-500 p-3 m-t-1 font-size-xl" onclick="document.location = 'series.html'">Series</div>
    <div class="g-500 p-3 m-t-1 font-size-xl" onclick="document.location = 'peliculas.html'">Peliculas</div>
`;