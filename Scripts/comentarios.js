
/* Boton para postear comentarios. */
var boton_comentar = document.getElementById('boton_comentar');


/* Obtiene los comentarios relacionados con la noticia. */
async function ComentariosDeNoticia( id_noticia )   {
    let url_api = `${api}comentarios/id_noticia=${id_noticia}` ;

    let response = await fetch( url_api );
    let data = await response.json();

    return data.data[0] ;
    /* Devuelve un arreglo de comentarios. */
}

async function PostComentario( comentario, id_noticia ) {
    if( !ExisteSesion() )   {
        alert('No se ha iniciado sesión');
    }

    let body = {
        'texto':comentario,
        'idnoticia':id_noticia
    }

    let url_api = `${api}comentarios` ;

    let param = {
        headers:{
            'Content-type':'application/json',
            'SILLON':localStorage.getItem('token_acceso')
        },
        method:'POST',
        body:JSON.stringify( body )
    }

    let data = await fetch( url_api, param )
                        .then( response => response.json() )
                        .then( data => ValidateCommentResponse( data ) )
                        .catch( error => alert( error ) )

    return data ;
    /* Devuelve el comentario creado. */
}

function ValidateCommentResponse( response ) {
    if( response.statusCode == 200 || response.statusCode == 201 )    {
        return( response );
    }

    else if( response.statusCode == 401 )   {   /* Actualizar la sesión e intentar otra vez. */
        return false ;
    }

    else    {
        return response.messages ; 
    }
}

async function PatchComentario( id_comentario, texto )    {

}

async function DeleteComentario( id_comentario )   {

}