
/* Obtiene tantas noticias y las devuelve en un arreglo json. */
async function ObtenerNoticias( num_noticias )  {
    let url_api = api + 'noticias/num_noticias=' + num_noticias ;
    let param_login = {
        headers:{
            'Content-type':'application/json',
        },
        method:'GET'
    };

    let response = await fetch( url_api );
    let data = await response.json();

    return data.data[0] ;
    /* Devuelve un arreglo de noticias. */
}

/* Obtiene tantas noticias de una seccion y las devuelve en un arreglo json. */
async function ObtenerNoticiasSeccion( seccion, num_noticias )  {
    let url_api = `${api}noticias/num_noticias=${num_noticias}&seccion=${seccion}`;
    let response = await fetch( url_api );
    let data = await response.json();

    return data.data[0] ;
    /* Devuelve un arreglo de noticias. */
}

/* Agrega las noticias a sessionStorage, sin repetir. */
function AgregaNoticiaSessionStorage( noticia )   {
    if( sessionStorage.getItem('noticias') === null )   {
        var noticias = [];
        sessionStorage.setItem('noticias', JSON.stringify( noticias ) );
    }

    var noticias = JSON.parse( sessionStorage.getItem('noticias') );

    if( noticias.find( function(noti) { return noti.id === noticia.id ; } ) === undefined ) {
        noticias.push( noticia );
    }

    sessionStorage.setItem('noticias', JSON.stringify( noticias ) );
}

/* Redirecciona a una página de noticia. */
function CargaNoticia()   {
    var noticias = JSON.parse( sessionStorage.getItem('noticias') );
    var id_noticia = this.id ;
    var noticia = noticias.find( n => n.id == id_noticia );

    localStorage.setItem('noticia_actual', JSON.stringify( noticia ) );
    
    window.location.href = 'noticia_especifica.html';
}
