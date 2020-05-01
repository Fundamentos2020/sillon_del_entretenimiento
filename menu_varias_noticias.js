
/* Variables globales */
const contenedor_tarjetas = document.querySelector('#contenedor_tarjetas');
const contenedor_nav_noticias = document.querySelector('#contenedor_botones_nav_noticias');

/* Declaracion de funciones. */
function obtenerNoticias()  {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'noticias.json', true );
    xhr.onreadystatechange = function() {
        if( this.readyState == 4 && this.status == 200 )
            guardarNoticiasLocalStorage( this );
    }
    xhr.send();
}

function guardarNoticiasLocalStorage( xhr )  {
    const noticias = JSON.parse( xhr.responseText ).noticias ;
    localStorage.setItem( 'noticias', JSON.stringify( noticias ) );
    localStorage.setItem( 'numNoticias', noticias.length );

    var i ;
    var seg_noticias = Math.round( noticias.length / 2 ) ;
    for( i = 0 ; i < seg_noticias ; ++i )  {
        const boton_nav = document.createElement('button');

        if( i == 0 )
            boton_nav.className = 'align-item-right boton_nav_noticias';

        else
            boton_nav.className = 'boton_nav_noticias m-left-2';

        boton_nav.id = i ;
        boton_nav.onclick = cargaSiguientesNoticias ;
        contenedor_nav_noticias.appendChild( boton_nav );
    }
}

function cargaSiguientesNoticias()  {
    contenedor_tarjetas.innerHTML = '';
    const noticias = JSON.parse( localStorage.getItem('noticias'));
    
    var i ;
    for( i = 0 ; i < 2 ; ++i )  {
        const noticia = noticias[this.id * 2 + i];
        if( noticia === undefined )
            break ;

        const contenedor_noticia = document.createElement('div');
        contenedor_noticia.className = 'card_pag_noticias w-s-50 m-s-2';
        contenedor_noticia.onclick = `"document.location = 'noticia_especifica.html'"` ;

        const cont_img = document.createElement('div');
        cont_img.className = 'contenedor_imagen text-center';

        const img = document.createElement('img');
        img.className = 'img-fluid p-2';
        img.src = noticia.ImagePath ;

        const titulo = document.createElement('h2');
        titulo.innerText = noticia.Titulo ;

        const resumen = document.createElement('div');
        resumen.className = 'overflow_hidden';
        resumen.innerText = noticia.Resumen ;

        cont_img.appendChild( img );
        contenedor_noticia.appendChild( cont_img );
        contenedor_noticia.appendChild( titulo );
        contenedor_noticia.appendChild( resumen );

        contenedor_tarjetas.appendChild( contenedor_noticia );
    }
}

/* Programa, es decir, lo que se ejecuta al cargar la página. */
obtenerNoticias();