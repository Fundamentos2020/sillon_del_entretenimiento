/* Var globales */
/* Div a los que agregar información de noticias; son los de hasta abajo */
var c_imagen_v = document.querySelector('#img_card_vid');
var c_imagen_s = document.querySelector('#img_card_ser');
var c_imagen_p = document.querySelector('#img_card_pel');
var c_text_v = document.querySelector('#text_card_vid');
var c_text_s = document.querySelector('#text_card_ser');
var c_text_p = document.querySelector('#text_card_pel');

/* Div que tiene la noticia central */
var c_noticia_principal = document.querySelector('#menu_mostrar_noticias');
var boton_der = document.querySelector('#boton_der');
var boton_izq = document.querySelector('#boton_izq');

/* Declaración de eventos */
boton_der.addEventListener('click', cargaSigNoticia );
boton_izq.addEventListener('click', cargaSigNoticia );

/* Index y arreglo de la noticias recientes mostrada */
var index_noticia = 0 ;
var noticias_recientes ;

/* Declaración de funciones */
function cargaSigNoticia()  {
    if( this.id === 'boton_der')    {
        /* Buscar en un arreglo de noticias el cambio. */
        index_noticia = index_noticia >= 2 ? 0 : index_noticia + 1 ;
        cambiaTarjetaNoticiaPrincipal( noticias_recientes[index_noticia] );
    }

    else    {/* Carga noticia de la izquierda */
        /* Buscar en un arreglo de noticias el cambio. */
        index_noticia = index_noticia <= 0 ? 2 : index_noticia - 1 ;
        cambiaTarjetaNoticiaPrincipal( noticias_recientes[index_noticia] );
    }
}

function cambiaTarjetaNoticiaPrincipal( noticia )  {
    c_noticia_principal.innerHTML = '';

    let c_imagen = document.createElement('div');
    c_imagen.className = 'text-center c_noticia_principal';

    let imagen = document.createElement('img');
    imagen.className = 'p-1 change-cursor-on-hover img_noticia_principal';
    imagen.id = `${noticia.id}`;
    imagen.onclick = CargaNoticia ;
    imagen.src = noticia.imagepath ;

    let c_texto = document.createElement('div');
    c_texto.className = 'p-3';
    c_texto.id = 'principal-card';

    let titulo = document.createElement('h1');
    titulo.className = 'change-cursor-on-hover';
    titulo.innerText = noticia.titulo ;
    titulo.id = `${noticia.id}`;
    titulo.onclick = CargaNoticia ;

    c_imagen.appendChild( imagen ); 
    c_texto.appendChild( titulo );
    
    c_noticia_principal.appendChild( c_imagen );
    c_noticia_principal.appendChild( titulo );
}

function dameImagenNoticiaSecundaria( noticia ) {
    let imagen = document.createElement('img');
    imagen.className = 'img-fluid p-1';
    imagen.id = 'imagen_secundaria';
    imagen.src = noticia.src ;
}

/* Cuando carge la página, vamos por las noticias. */
async function pideNoticiasSeccion() {
    var noticia_videojuegos = await ObtenerNoticiasSeccion('Videojuegos', 1 );
    var noticia_peliculas = await ObtenerNoticiasSeccion('Peliculas', 1 );
    var noticia_series = await ObtenerNoticiasSeccion('Series', 1 );

    AgregaNoticiaSessionStorage( noticia_videojuegos );
    AgregaNoticiaSessionStorage( noticia_series );
    AgregaNoticiaSessionStorage( noticia_peliculas );

    MuestraTarjetasNoticias( noticia_videojuegos[0], noticia_series[0], noticia_peliculas[0] );
}

async function pideNoticiasRecientes()  {
    noticias_recientes = await ObtenerNoticias( 3 );
    noticias_recientes.forEach( noticia => {
        AgregaNoticiaSessionStorage( noticia );
    });

    MuestraNoticiasRecientes( noticias_recientes );

    let todas_noticias = await ObtenerNoticias( 20 );
    todas_noticias.forEach( noticia => {
        AgregaNoticiaSessionStorage( noticia );
    });
}

/* Se muestran las noticias más recientes de cada sección. */
function MuestraTarjetasNoticias( videojuegos, series, peliculas )  {
    /* Tarjeta videojuegos. */
    var img = document.createElement('img');
    img.className = 'img-fluid p-1 imagen_secundaria change-cursor-on-hover  m-t-b-auto';
    img.src = `${videojuegos.imagepath}`;
    img.id = `${videojuegos.id}` ;
    img.onclick = CargaNoticia ;
    c_imagen_v.appendChild( img );

    var titulo = document.createElement('h2');
    titulo.innerText = `${videojuegos.titulo}`;
    titulo.className = 'change-cursor-on-hover';
    titulo.id = `${videojuegos.id}`;
    titulo.onclick = CargaNoticia ;
    c_text_v.appendChild( titulo );

    /* Tarjeta series */
    var img = document.createElement('img');
    img.className = 'img-fluid p-1 imagen_secundaria change-cursor-on-hover';
    img.src = `${series.imagepath}`;
    img.id = `${series.id}` ;
    img.onclick = CargaNoticia ;
    c_imagen_s.appendChild( img );

    var titulo = document.createElement('h2');
    titulo.innerText = `${series.titulo}`;
    titulo.className = 'change-cursor-on-hover';
    titulo.id = `${series.id}`;
    titulo.onclick = CargaNoticia ;
    c_text_s.appendChild( titulo );

    /* Tarjeta películas */
    var img = document.createElement('img');
    img.className = 'img-fluid p-1 imagen_secundaria change-cursor-on-hover';
    img.src = `${peliculas.imagepath}`;
    img.id = `${peliculas.id}` ;
    img.onclick = CargaNoticia ;
    c_imagen_p.appendChild( img );

    var titulo = document.createElement('h2');
    titulo.innerText = `${peliculas.titulo}`;
    titulo.className = 'change-cursor-on-hover';
    titulo.id = `${peliculas.id}`;
    titulo.onclick = CargaNoticia ;
    c_text_p.appendChild( titulo );
}

/* Se muestran las noticias más recientes en el panel principal. */
function MuestraNoticiasRecientes( noticias_recientes )    {
    cambiaTarjetaNoticiaPrincipal( noticias_recientes[index_noticia] );
}

/* Main */
pideNoticiasSeccion();
pideNoticiasRecientes();