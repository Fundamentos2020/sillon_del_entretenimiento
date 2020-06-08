<?php

/* Permite el paso de las solicitudes preflight de CORS. */
if( $_SERVER['REQUEST_METHOD'] === 'OPTIONS' )  {
    http_response_code( 200 );
    exit();
} 

?>