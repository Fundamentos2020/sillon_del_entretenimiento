/* Var globales */
/* Div a los que agregar información de noticias; son los de hasta abajo */
const c_imagen_v = document.querySelector('#img_card_vid');
const c_imagen_s = document.querySelector('#img_card_ser');
const c_imagen_p = document.querySelector('#img_card_pel');
const c_text_v = document.querySelector('#text_card_vid');
const c_text_s = document.querySelector('#text_card_ser');
const c_text_p = document.querySelector('#text_card_pel');

/* Div que tiene la noticia central */
const c_noticia_principal = document.querySelector('#menu_mostrar_noticias');
const boton_der = document.querySelector('#boton_der');
const boton_izq = document.querySelector('#boton_izq');

/* Declaración de eventos */
boton_der.addEventListener('click', cargaSigNoticia );
boton_izq.addEventListener('click', cargaSigNoticia );

/* Declaración de funciones */
function cargaSigNoticia()  {
    if( this.id === 'boton_der')
        console.log( this.id );

    else    {/* Carga noticia de la izquierda */
        /* Buscar en un arreglo de noticias el cambio. */
        var noticia = {};
        noticia.src = 'https://picsum.photos/800/800';
        noticia.titulo = 'Prueba';
        noticia.resumen = 'Hola mundo.';
        cambiaTarjetaNoticiaPrincipal( noticia );
    }
}

function cambiaTarjetaNoticiaPrincipal( noticia )  {
    c_noticia_principal.innerHTML = '';

    let c_imagen = document.createElement('div');
    c_imagen.className = 'text-center';

    let imagen = document.createElement('img');
    imagen.className = 'img-fluid p-1';
    imagen.id = 'imagen_principal';
    imagen.src = noticia.src ;

    let c_texto = document.createElement('div');
    c_texto.className = 'p-3';
    c_texto.id = 'principal-card';

    let titulo = document.createElement('h1');
    titulo.innerText = noticia.titulo ;

    let texto = document.createElement('span');
    texto.innerText = noticia.resumen ;

    c_imagen.appendChild( imagen ); 
    c_texto.appendChild( titulo );
    c_texto.appendChild( texto );

    c_noticia_principal.appendChild( c_imagen );
    c_noticia_principal.appendChild( c_texto );
}

function dameImagenNoticiaSecundaria( noticia ) {
    let imagen = document.createElement('img');
    imagen.className = 'img-fluid p-1';
    imagen.id = 'imagen_secundaria';
    imagen.src = noticia.src ;
}

/* Cuando carge la página, vamos por las noticias. */
function pideNoticias() {

}

function agregaNoticias()   {

}

/* Main */
pideNoticias();