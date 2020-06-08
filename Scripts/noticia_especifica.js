
/* Se obtiene la noticia a mostrar. */
var noticia = JSON.parse( localStorage.getItem('noticia_actual') );

window.onload = function()    {
    AgregaNoticia();
    AgregaComentarios();

    if( ExisteSesion() )    {
        PanelUsuario();
    }
};

/* Agrega la información de la noticia */
function AgregaNoticia()    {
    let c_titulo = document.getElementById('c_titulo_noticia');
    c_titulo.innerHTML = `<h1 class="text-center">${noticia.titulo}</h1>`;

    let c_imagen = document.getElementById('c_imagen_noticia');
    c_imagen.innerHTML = `<img class="img-fluid p-1" src="${noticia.imagepath}">`;

    let c_texto = document.getElementById('c_texto_noticia');
    c_texto.innerHTML = `${noticia.texto}`;
}

/* Agrega los comentarios de la noticia. */
async function AgregaComentarios()  {

    var comentarios = await ComentariosDeNoticia( noticia.id );
    let c_comentarios = document.getElementById('seccion_comentarios');
    c_comentarios.innerHTML = '';   /* Borrar los comentarios */

    comentarios.forEach( comentario => {
        var contenedor = DameComentarioRol( comentario );
        c_comentarios.appendChild( contenedor );
    });

    AgregaCajaTexto();
}

function AgregaCajaTexto()  {
    let c_comentarios = document.getElementById('seccion_comentarios');

    c_comentarios.innerHTML += `
    <div>
        <div class='m-2'>
            <textarea class='texto' id='caja_texto_comentarios'></textarea>
        </div>
        <div class='m-2'>
            <button class='boton_rojo change-cursor-on-hover' id='boton_comentar'>Comentar</button>
        </div>
    </div>
    `;

    let boton_comentar = document.getElementById('boton_comentar');
    boton_comentar.onclick = Comenta ;
}

async function Comenta() {
    let texto = document.getElementById('caja_texto_comentarios').value ;
    let noticia_id = noticia.id ;
    let result = await PostComentario( texto, noticia_id );

    if( !result )   {
        await ActualizaSesion();
        await PostComentario( texto, noticia_id );
    }

    await AgregaComentarios();
}

function DameComentarioRol( comentario ) {
    let contenedor = document.createElement('div');
    contenedor.className = 'comentario flex-column b-dark-gray p-2 m-1';
    contenedor.id = `${comentario.id}`;

    let div_texto = document.createElement('div');
    div_texto.innerText = `${comentario.texto}`;

    let div_nombre_usuario = document.createElement('div');
    div_nombre_usuario.className = 'p-2';
    div_nombre_usuario.innerHTML = `<i>${comentario.nombre_usuario}</i>`;

    if( ExisteSesion() )    {
        div_nombre_usuario.innerHTML += '<div>' ;

        var id_usuario = localStorage.getItem('id_usuario');
        var rol = localStorage.getItem('rol');
        if( comentario.id_usuario == id_usuario )   {
            div_nombre_usuario.innerHTML += `<button id=${comentario.id} class='boton_rojo m-1 change-cursor-on-hover'
            onclick='EditarComentario( this.id )'>Modificar</button>`;
        }

        if( rol != 'Usuario' )  {
            div_nombre_usuario.innerHTML += `<button id=${comentario.id} class='boton_rojo m-1 change-cursor-on-hover'
            onclick='EliminarComentario( this.id )'>Eliminar</button>`;
        }

        div_nombre_usuario.innerHTML += '</div>'

    }

    contenedor.appendChild( div_texto );
    contenedor.appendChild( div_nombre_usuario );

    return contenedor ;
}

async function EditarComentario( id )   {
    console.log( `Modificar ${id}` );
}

async function EliminarComentario( id ) {
    console.log( `Eliminar ${id}` );
}