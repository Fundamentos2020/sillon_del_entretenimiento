/* Variables globales */
const contenedor_tarjetas = document.querySelector('#contenedor_tarjetas');
const contenedor_nav_noticias = document.querySelector('#contenedor_botones_nav_noticias');

/* Utilizamos una variable para guardar en vez de localStorage por que este arreglo es especifico a la página. */
let arreglo_noticias ;
let numNoticias ;

/* Declaracion de funciones. */
/* Obtener el json. */
async function obtenerNoticias()  {
    arreglo_noticias = await ObtenerNoticiasSeccion( 'Peliculas', 20 );
    numNoticias = arreglo_noticias.length ;
    cargaNoticiasIniciales();
    agregarBotonesNavNoticias();
}

/* Botones para navegar las tarjetas de noticias. */
function agregarBotonesNavNoticias()    {
    var i ;
    var num_botones = Math.round( numNoticias / 2 ) ;
    for( i = 0 ; i < num_botones ; ++i )  {
        const boton_nav = document.createElement('button');

        if( i == 0 )
            boton_nav.className = 'align-item-right boton_nav_noticias';

        else
            boton_nav.className = 'boton_nav_noticias m-left-2';

        boton_nav.id = i ;
        boton_nav.innerHTML = `<h3>${i+1}</h3>` ;
        boton_nav.onclick = cargaSiguientesNoticias ;
        contenedor_nav_noticias.appendChild( boton_nav );
    }
}

/* Cada vez que se da click en un botón de navegación, cambian las tarjetas que se muestran. */
function cargaSiguientesNoticias()  {
    contenedor_tarjetas.innerHTML = '';
    var i ;
    for( i = 0 ; i < 2 ; ++i )  {
        const noticia = arreglo_noticias[this.id * 2 + i];
        if( noticia === undefined )
            break ;

        const tarjetaNoticia = dameTarjetaNoticia( noticia );
        contenedor_tarjetas.appendChild( tarjetaNoticia );
    }
}

/* Tarjetas iniciales. */
function cargaNoticiasIniciales()   {
    var i ;
    for( i = 0 ; i < 2 ; ++i )  {
        const noticia = arreglo_noticias[i];
        if( noticia === undefined )
        {
            break ;
        }

        const tarjetaNoticia = dameTarjetaNoticia( noticia );
        contenedor_tarjetas.appendChild( tarjetaNoticia );
    }
}

/* Toma una noticia y crea una tarjeta con ella. */
function dameTarjetaNoticia( noticia )  {
    console.log( noticia );
    const contenedor_noticia = document.createElement('div');
    contenedor_noticia.className = 'card_pag_noticias w-s-50 m-s-2';
    contenedor_noticia.onclick = CargaNoticiaCarpeta ;
    contenedor_noticia.id = `${noticia.id}`;

    const cont_img = document.createElement('div');
    cont_img.className = 'contenedor_imagen text-center';

    const img = document.createElement('img');
    img.className = 'img-fluid p-2';
    img.src = noticia.imagepath ;

    const titulo = document.createElement('h2');
    titulo.innerText = noticia.titulo ;

    cont_img.appendChild( img );
    contenedor_noticia.appendChild( cont_img );
    contenedor_noticia.appendChild( titulo );

    return( contenedor_noticia );
}

function CargaNoticiaCarpeta()   {
    var noticias = JSON.parse( sessionStorage.getItem('noticias') );
    var id_noticia = this.id ;
    var noticia = noticias.find( n => n.id == id_noticia );

    localStorage.setItem('noticia_actual', JSON.stringify( noticia ) );
    
    window.location.href = 'noticia_especifica.html';
}

/* Programa, es decir, lo que se ejecuta al cargar la página. */
obtenerNoticias();