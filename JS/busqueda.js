
/* Obtener el textbox. */
var input_busqueda = document.getElementById('input_busqueda');
input_busqueda.onkeypress = async function( e ) {
    if( e.keyCode == 13 )   {
        await BuscarPalabraClave();
        window.location.href = 'busqueda.html' ;
    }
};

async function BuscarPalabraClave() {
    var palabra_clave = input_busqueda.value ;
    if( palabra_clave == '' )
        return ;

    let url_api = api + 'noticias/palabra_clave=' + palabra_clave ;
    let response = await fetch( url_api );
    let data = await response.json();

    var noticias_match = data.data[0] ;
    localStorage.setItem('noticias_match', JSON.stringify( noticias_match) );
    localStorage.setItem('palabra_match', palabra_clave );
}