
/* Obtener el botón rojo. */
var boton_upload = document.getElementById('boton_upload');
boton_upload.onclick = async function() {
    UploadImagen();
}

async function UploadImagen() {
    let imagen = document.getElementById('img').files[0];

    if( imagen == undefined )   {
        return( '' );
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


    localStorage.setItem('image_name', image_name.data );
    UploadNoticia();
}

async function UploadNoticia()  {
    let titulo = document.getElementById('titulo_editar_noticia').value ;
    let texto = document.getElementById('texto_editar_noticia').value ;
    let seccion = document.getElementById('categorias_noticia').value ;
    let pathImagen = localStorage.getItem('image_name');

    pathImagen = `${api}Imagenes/` + pathImagen ;

    var datos = {
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
        method: 'POST',
        body: JSON.stringify( datos )
    };

    await fetch( url_api, param )
            .then( response => response.json () )
            .then( datos => localStorage.setItem('datos_moderador_noticia', datos ) )
            .catch( error => console.log( error) )

    window.location.href = 'index.html' ;
}