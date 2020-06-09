
/* Boton de login */
var boton_login = document.getElementById('boton_login');
boton_login.onclick = async function()  {
    console.log( 'click' );
    await login();
}

/* Se intenta ingresar al cargar. */
window.onload = function()    {
    if( ExisteSesion() )    {
        PanelUsuario();
    }
};

/* Login */
async function login()    {
    let nombre_usuario = document.getElementById('nombre_usuario').value ;
    let contrasenia = document.getElementById('input_contrasena').value ;

    let datos = {
        nombre_usuario : nombre_usuario,
        contrasenia : contrasenia 
    };

    let url_api = api + 'sesiones' ;
    let param_login = {
        headers:{
            'Content-type':'application/json',
        },
        body: JSON.stringify( datos ),
        method:'POST'
    };

    await fetch( url_api, param_login )
            .then( response => response.json() )
            .then( data => ValidateSesionResponse( data ) )
            .catch( error => console.log( error) )
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
            .then( data => ValidateSesionResponseA( data ) )
            .catch( error => console.log( error ) )
}

function ValidateSesionResponse( response ) {
    if( response.statusCode == 200 || response.statusCode == 201 )    {
        GuardaTokens( response );
        location.reload();
    }

    else    {
        alert( response.messages );
    }
}

function ValidateSesionResponseA( response ) {
    if( response.statusCode == 200 || response.statusCode == 201 )    {
        GuardaTokensA( response );
        location.reload();
    }

    else    {
        alert( response.messages );
    }
}

function ExisteSesion()    {
    if( localStorage.getItem('token_acceso') !== null 
        && localStorage.getItem('token_actualizacion') !== null
            && localStorage.getItem('nombre_usuario') !== null  )  {
        return true ; 
    }

    return false ;
}

function GuardaTokens( respuesta )  {   /* Si se creo la sesión. */
    localStorage.setItem('token_acceso', respuesta.data[0].token_acceso );
    localStorage.setItem('token_actualizacion', respuesta.data[0].token_actualizacion );
    localStorage.setItem('nombre_usuario', respuesta.data[0].nombre_usuario );
    localStorage.setItem('rol', respuesta.data[0].rol );
    localStorage.setItem('id_usuario', respuesta.data[0].id );

    PanelUsuario();
    return ;
}

function GuardaTokensA( respuesta )  {   /* Si se creo la sesión. */
    localStorage.setItem('token_acceso', respuesta.data[0].token_acceso );
    localStorage.setItem('token_actualizacion', respuesta.data[0].token_actualizacion );

    PanelUsuario();
    return ;
}

async function IniciaCerrarSesion()   {
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
        method:'DELETE'
    };

    fetch( url_api, param )
        .then( response => response.json() )
        .then( CerrarSesion() )
        .catch( error => console.log( error) )
}

function CerrarSesion() {
    localStorage.removeItem('token_acceso');
    localStorage.removeItem('token_actualizacion');
    localStorage.removeItem('nombre_usuario');
    localStorage.removeItem('rol');
    localStorage.removeItem('id_usuario');
    PanelRegistro();
    window.location.reload(true);
}

function PanelUsuario()  {
    /* Si fue exitosa, cambia panel de registro. */
    let panel_registro = document.getElementById('panel_registro');
    panel_registro.innerHTML = `
    <div class="text-center">
    <h1>${localStorage.getItem('nombre_usuario')}</h1>
    </div>
    <div class="text-center">
    <a href="/Usuarios/Cuenta.html">Administrar Cuenta</a>
    </div>
    <div class="text-center">
    <a id="link_cerrar_sesion" href="">Cerrar sesión</a>
    </div>
    ` ;

    let link_cerrar_sesion = document.getElementById('link_cerrar_sesion');
    link_cerrar_sesion.onclick = function() {
        CerrarSesion();
        return false ;
    };

    let menu_usuario_movil = document.getElementById('menu_movil_usuario');
    menu_usuario_movil.innerHTML =`
    <div class="g-700 p-3 m-t-2 font-size-xl">${localStorage.getItem('nombre_usuario')}</div>
    <div class="g-700 p-3 m-t-2 font-size-xl" onclick="document.location = '${url_general}Usuarios/Cuenta.html'">Administrar Cuenta</div>
    <div class="g-700 p-3 m-t-2 font-size-xl" onclick="CerrarSesion()">Cerrar Sesión</div>
    `;
}

function PanelRegistro()    {
    let panel_registro = document.getElementById('panel_registro');
    panel_registro.innerHTML = `
    <div class="p-2 sp-2 text-center" id="titulo_registro">
        Login
    </div>
    <div class="titulo_registro_secundario sp-2 text-center">
        Nombre de usuario
    </div>
    <input class="background_input text-center" type="text" id="nombre_usuario">
    <div class="titulo_registro_secundario text-center"> 
        Contraseña
    </div>
    <input class="background_input" type="password" id="input_contrasena">
    <div class="titulo_registro_secundario flex-row">
        <input type="checkbox"><span>Recuerdame</span>
    </div>
    <div class="p-1 text-center">
        <button class="boton_rojo change-cursor-on-hover" id="boton_login">INICIAR SESIÓN</button>
    </div>
    <div class="flex-column p-2">
        <a href="/registro.html" class="">Registrarse</a>
        <a href="">¿Olvidaste tu contraseña?</a>
    </div>
    ` ;

    let menu_usuario_movil = document.getElementById('menu_movil_usuario');
    menu_usuario_movil.innerHTML =`
    <div class="g-700 p-3 m-t-2 font-size-xl" onclick="document.location = 'registro.html'">Registro</div>
    <div class="g-700 p-3 m-t-2 font-size-xl" onclick="document.location = 'login.html'">Login</div>
    `;
}

