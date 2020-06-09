
/* Obtener el boton de cambiar contrasenia. */
var cambiar_contrasenia = document.getElementById('boton_cambiar_contrasenia');
cambiar_contrasenia.onclick = CambiaContraseniaInputs ;

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

async function CambiaContraseniaInputs()  {
    await ActualizaSesion();
    var nombre_usuario = localStorage.getItem('nombre_usuario');
    var contrasenia_vieja = document.getElementById('input_contrasenia_vieja').value ;
    var contrasenia_nueva = document.getElementById('input_contrasenia_nueva').value ;

    if( nombre_usuario == ''
        || contrasenia_nueva == ''
            || contrasenia_nueva == '' )    {
        alert('Faltan parametros');
        return ;
    }

    let mensaje = await CambiaContrasenia( nombre_usuario, contrasenia_nueva, contrasenia_vieja );
    alert( mensaje.messages[0] );
    document.getElementById('input_contrasenia_vieja').value = '' ;
    document.getElementById('input_contrasenia_nueva').value = '' ;
}

async function ActualizaSesion()   {
    if( !ExisteSesion() )   {
        return false ;
    }

    let url_api = api + 'sesiones' ;
    let datos = {
        'token_acceso' : localStorage.getItem('token_acceso'),
        'token_actualizacion' : localStorage.getItem('token_actualizacion')
    }
    let param = {
        headers:{
            'Content-type':'application/json',
        },
        body: JSON.stringify( datos ),
        method:'PATCH'
    };

    await fetch( url_api, param )
            .then( response => response.json() )
            .then( data => ValidateSesionResponse( data ) )
            .catch( error => console.log( error ) )
}

function ValidateSesionResponse( response ) {
    if( response.statusCode == 200 || response.statusCode == 201 )    {
        GuardaTokens( response );
    }

    else    {
        alert( response.messages );
    }
}

function GuardaTokens( respuesta )  {   /* Si se creo la sesión. */
    localStorage.setItem('token_acceso', respuesta.data[0].token_acceso );
    localStorage.setItem('token_actualizacion', respuesta.data[0].token_actualizacion );
    return ;
}

function ExisteSesion()    {
    if( localStorage.getItem('token_acceso') !== null 
        && localStorage.getItem('token_actualizacion') !== null
            && localStorage.getItem('nombre_usuario') !== null  )  {
        return true ; 
    }

    return false ;
}