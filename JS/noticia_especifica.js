
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
    c_texto.innerHTML = `${noticia.texto}<div><i>${noticia.fecha}</i></div>`;

    c_texto.innerHTML += '<div>' ;

    if( localStorage.getItem('rol') === 'Administrador' || noticia.idmoderador == localStorage.getItem('id_usuario') )    {
        c_texto.innerHTML += `
            <button id=${noticia.id} class='boton_rojo m-1 change-cursor-on-hover'
            onclick='ModificaNoticia( this.id )'>Modificar</button>
        `;
    }

    if( localStorage.getItem('rol') === 'Administrador' )   {
        c_texto.innerHTML += `
            <button id=${noticia.id} class='boton_rojo m-1 change-cursor-on-hover'
            onclick='EliminaNoticia( this.id )'>Eliminar</button>
        `;
    }

    c_texto.innerHTML += '</div>' ;
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
    await ActualizaSesion();
    let texto = document.getElementById('caja_texto_comentarios').value ;
    let noticia_id = noticia.id ;
    await PostComentario( texto, noticia_id );
    location.reload( true );
}

function DameComentarioRol( comentario ) {
    let contenedor = document.createElement('div');
    contenedor.className = 'comentario flex-column b-dark-gray p-2 m-1';
    contenedor.id = `c_${comentario.id}`;

    let div_texto = document.createElement('div');
    div_texto.innerText = `${comentario.texto}`;

    let div_nombre_usuario = document.createElement('div');
    div_nombre_usuario.className = 'p-2';
    div_nombre_usuario.innerHTML = `<i>${comentario.nombre_usuario}</i>//${comentario.fecha}`;

    if( ExisteSesion() )    {
        div_nombre_usuario.innerHTML += '<div>' ;

        var id_usuario = localStorage.getItem('id_usuario');
        var rol = localStorage.getItem('rol');
        if( comentario.id_usuario == id_usuario )   {
            div_nombre_usuario.innerHTML += `<button id=${comentario.id} class='boton_rojo m-1 change-cursor-on-hover'
            onclick='EditarComentario( this.id )'>Modificar</button>`;
        }

        if( rol !== 'Usuario' || comentario.id_usuario == localStorage.getItem('id_usuario') )  {
            div_nombre_usuario.innerHTML += `<button id=${comentario.id} class='boton_rojo m-1 change-cursor-on-hover'
            onclick='EliminarComentario( this.id )'>Eliminar</button>`;
        }

        div_nombre_usuario.innerHTML += '</div>'
    }

    contenedor.appendChild( div_texto );
    contenedor.appendChild( div_nombre_usuario );

    return contenedor ;
}

async function EditarComentario( id )   {   /* Vamos a crear una caja de texto en el comentario. */
    var contenedor = document.getElementById(`c_${id}`);
    let children = contenedor.children ;
    let texto = children[0].innerText ;

    console.log( id );
    
    contenedor.innerHTML = `
        <div>
        <div class='m-2'>
            <textarea class='texto' id='caja_modificar${id}'>${texto}</textarea>
        </div>
        <div class='m-2'>
            <button class='boton_rojo m-1 change-cursor-on-hover' id='${id}' onclick='PostModificacion( this.id )'>Modificar</button>
            <button class='boton_rojo m-1 change-cursor-on-hover' id='${id}' onclick='Recargar()'>Cancelar</button>
        </div>
        </div>
    `;
}

function Recargar() {
    location.reload();
}

async function PostModificacion( id )   {
    let texto_modificado = document.getElementById(`caja_modificar${id}`).value ;
    await PatchComentario( id, texto_modificado );
    location.reload();
}

async function EliminarComentario( id ) {
    await DeleteComentario( id );
    location.reload();
}

async function ModificaNoticia()    {
    window.location.href = 'modificar_noticia.html';
}

async function EliminaNoticia( id )    {
    await EliminarNoticia( id );
    window.location.href = 'index.html' ;
}