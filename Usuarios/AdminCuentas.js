
/* Obtener el contendor de usuarios */
window.onload = async function()    {
    await ActualizaSesion();
    AgregaUsuarios( await ObtenerUsuarios() );
};

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

function GuardaTokens( respuesta )  {   /* Si se creo la sesión. */
    console.log( respuesta );
    localStorage.setItem('token_acceso', respuesta.data[0].token_acceso );
    localStorage.setItem('token_actualizacion', respuesta.data[0].token_actualizacion );
    return ;
}

function ValidateSesionResponse( response ) {
    if( response.statusCode == 200 || response.statusCode == 201 )    {
        GuardaTokens( response );
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

function AgregaUsuarios( datos )   {
    var usuarios = datos.data[0] ;
    var c_usuarios = document.getElementById('panel_principal');

    usuarios.forEach( usuario => {
        c_usuarios.innerHTML += `
        <div class="flex-row b-dark-gray m-3 flex-wrap">
            <div class="p-3">
                <h3>${usuario.nombre_usuario}</h3>
                <div><i>${usuario.email}</i></div>
                <div>${usuario.rol}</div>
            </div>
            <div class="align-item-right flex-row">
                <div class="p-4">
                    <select id='rol${usuario.id}'>
                        <option value="" selected disabled hidden>Escoga un rol:</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Moderador">Moderador</option>
                        <option value="Usuario">Usuario</option>
                    </select>
                    <button id='${usuario.id}' onclick='ActualizarUsuario( this.id )'>Actualizar</button>
                </div>
            </div>
        </div>
        `;
    });
}

async function ActualizarUsuario( id )  {
    var rol = document.getElementById('rol' + id ).value ;

    if( rol === '' )    {
        return ;
    }

    await CambiaRol( id, rol );
    location.reload();
}