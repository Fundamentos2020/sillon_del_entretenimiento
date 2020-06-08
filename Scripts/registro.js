
/* Boton de registro. */
var boton_registro = document.getElementById('boton_registro');
boton_registro.onclick = async function() { 
    var d = await RegistrarUsuario()

    if( ExisteSesion() )
        window.location.href = 'index.html' ;
};

/* Registrar un usuario */
async function RegistrarUsuario() {
    var nombre_usuario = document.querySelector('#input_nombre_usuario').value ;
    var contrasenia = document.querySelector('#input_contrasenia').value ;
    var email = document.querySelector('#input_email').value ;

    if( nombre_usuario === '' || contrasenia === '' || email === '' )   {
        alert('Falta algún parametro');
        return ;
    }

    var url_api = api + 'usuarios' ;
    var datos = {
        nombre_usuario:nombre_usuario,
        contrasenia:contrasenia,
        email:email
    };

    var param = {
        headers: {
            'Content-type':'application/json'
        },
        body: JSON.stringify( datos ),
        method: 'POST'
    };

    fetch( url_api, param )
        .then( response => response.json() )
        .then( data => ValidarRegistro( data, contrasenia ) )
        .catch( error => {} )
}

async function ValidarRegistro( respuesta, contrasenia )  {
    if( respuesta.statusCode == 200 || respuesta.statusCode == 201 )    {
        await login( respuesta.data[0], contrasenia );
    }

    else    {
        alert( respuesta.messages );
    }
}

async function login( usuario, contrasenia )    {
    let datos = {
        nombre_usuario : usuario.nombre_usuario,
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

    fetch( url_api, param_login )
        .then( response => response.json() )
        .then( data => ValidarRespuesta( data ) )
        .catch( error => console.log( error) )
}

function ValidarRespuesta( response ) {
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
    localStorage.setItem('nombre_usuario', respuesta.data[0].nombre_usuario );
    localStorage.setItem('rol', respuesta.data[0].rol );
    localStorage.setItem('id_usuario', respuesta.data[0].id );

    boton_registro.innerText = 'LISTO, IR A PAG. PRINCIPAL';
}

function ExisteSesion()    {
    if( localStorage.getItem('token_acceso') !== null 
        && localStorage.getItem('token_actualizacion') !== null
            && localStorage.getItem('nombre_usuario') !== null  )  {
        return true ; 
    }

    return false ;
}