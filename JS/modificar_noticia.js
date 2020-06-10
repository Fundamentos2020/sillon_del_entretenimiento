
/* Obtener la noticia. */
var noticia = JSON.parse( localStorage.getItem('noticia_actual'));

window.onload = function()  {
    CargaInformacion();
}

/* Evento de click */
var boton_modificar = document.getElementById('boton_patch');
boton_modificar.onclick = async function()    {
    await UploadImagen();
    ModificarNoticia();
};

function CargaInformacion() {
    /* LLenar los campos. */
    let titulo = document.getElementById('titulo_editar_noticia') ;
    let texto = document.getElementById('texto_editar_noticia') ;
    let seccion = document.getElementById('categorias_noticia') ;

    titulo.innerText = noticia.titulo ;
    texto.innerText = noticia.texto ;
    seccion.value = noticia.seccion ;
}

async function ModificarNoticia()   {
    let titulo = document.getElementById('titulo_editar_noticia').value ;
    let texto = document.getElementById('texto_editar_noticia').value ;
    let seccion = document.getElementById('categorias_noticia').value ;

    pathImagen = localStorage.getItem('image_name');
    localStorage.setItem('datos_moderador_noticia', pathImagen );

    var datos = {
        id_noticia: noticia.id,
        seccion: seccion,
        titulo: titulo,
        texto:  texto,
        imagepath: pathImagen
    };

    var url_api = `${api}noticias` ;
    var param = {
        headers: {
            'Content-type':'application/json',
            'SILLON':localStorage.getItem('token_acceso')
        },
        method: 'PATCH',
        body: JSON.stringify( datos )
    };

    let noticia_modificada = await fetch( url_api, param )
                                    .then( response => response.json() )
                                    .then( datos => { return datos.data[0] } )
                                    /* .then( datos => { console.log( datos ) } ) */
                                    .catch( error => console.log( error) )

    let noticias = JSON.parse( sessionStorage.getItem('noticias') );
    noticias = noticias.filter( function(noti) { return noti.id !== noticia_modificada.id ; } );
    sessionStorage.setItem('noticias', JSON.stringify( noticias ) );

    AgregaNoticiaSessionStorage( noticia_modificada );
    localStorage.setItem('noticia_actual', JSON.stringify( noticia_modificada ) );
    window.location.href = 'noticia_especifica.html' ;
}

async function UploadImagen() {
    let imagen = document.getElementById('img').files[0];

    if( imagen == undefined )   {
        localStorage.setItem('image_name', noticia.imagepath );
        return ;
    }

    let formData = new FormData();
    formData.append('imagen', imagen );

    let url_api = `${api}upload_imagen` ;
    let param = {
        headers: {
            'SILLON': localStorage.getItem('token_acceso')
        },
        method: 'POST',
        body: formData
    };

    let image_name = await fetch( url_api, param )
                            .then( response => response.json() )
                            .then( data => { return data ; } )
                            .catch( error => localStorage.setItem('error', error ) )    


    localStorage.setItem('image_name', `${api}Imagenes/${image_name.data}`);
}