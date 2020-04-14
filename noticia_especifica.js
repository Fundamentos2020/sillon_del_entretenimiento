const cerrar_menu_movil = document.querySelector('#cerrar_menu_movil');
const boton_menu_movil = document.querySelector('#boton_menu');

boton_menu_movil.addEventListener('click', mostrarMenu );
cerrar_menu_movil.addEventListener('click', ocultarMenu );

function loadNoticias()  {
    
}

function mostrarMenu()  {
    const menu = document.querySelector('#menu_movil');
    menu.style.display = 'inline' ;
    menu.style.width = '300px' ;
}

function ocultarMenu()  {
    const menu = document.querySelector('#menu_movil');
    menu.style.width = '0px';
}