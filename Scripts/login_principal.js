/* Boton de login */
const boton_login = document.getElementById('boton_login');
boton_login.addEventListener('click', login ); 

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

    window.location.href = index ;
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
    return ;
}