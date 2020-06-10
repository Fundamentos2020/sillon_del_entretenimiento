
/* Cambiar el rol de usuario */
async function CambiaRol( id_usuario, rol ) {
    var usuarios_modificar = [
        {
            id_usuario:id_usuario,
            rol:rol
        }
    ];

    var datos = {
        usuarios_modificar:usuarios_modificar
    };

    var url_api = `${api}usuarios` ;
    var param = {
        headers: {
            'Content-type':'application/json',
            'SILLON':localStorage.getItem('token_acceso')
        },
        method: 'PATCH',
        body: JSON.stringify( datos )
    };

    await fetch( url_api, param )
            .catch( error => console.log( error) )
}

async function ObtenerUsuarios()    {
    var url_api = `${api}usuarios` ;

    let param = {
        headers: {
            'SILLON':localStorage.getItem('token_acceso')
        }
    }

    var res = await fetch( url_api, param )
                        .then( response => response.json() )
                        .then( datos => { return datos } )
                        .catch( error => console.log( error ) )

    return res ;
}

async function CambiaContrasenia( nombre_usuario, contrasenia_nueva, contrasenia_vieja ) {
    var url_api = `${api}usuarios` ;
    let datos = {
        nombre_usuario:nombre_usuario,
        contrasenia:contrasenia_nueva,
        contrasenia_vieja:contrasenia_vieja
    };

    let param = {
        headers: {
            'Content-type':'application/json',
            'SILLON':localStorage.getItem('token_acceso')
        },
        method: 'PATCH',
        body: JSON.stringify( datos )
    };

    let response = await fetch( url_api, param );
    let data = await response.json();

    return data ;
}