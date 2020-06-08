
/* Obtener el boton de cambiar contrasenia. */
var cambiar_contrasenia = document.getElementById('boton_cambiar_contrasenia');
cambiar_contrasenia.onclick = CambiaContrasenia ;

/* Titulo */
var nombre_usuario = document.getElementById('nombreUsuario');
nombre_usuario.innerText = localStorage.getItem('nombre_usuario');

window.onload = CargaLinks ;

function CargaLinks()   {
    var rol = localStorage.getItem('rol');
    
    if( rol !== 'Usuario' ) {
        var c_crear = document.getElementById('c_crear_noticia');
        c_crear.innerHTML = `<a href="../editar_noticia.html">Crear Noticia</a>`;
    }

    if( rol === 'Administrador' )   {
        var c_admin = document.getElementById('c_admin_cuentas');
        c_admin.innerHTML = `<a href="AdminCuentas.html">Administración de Cuentas</a>`;
    }
}

async function CambiaContrasenia()  {
    
}