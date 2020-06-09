<?php

require_once('../Models/DB.php');
require_once('../Models/Response.php');
require_once('../Models/Sesion.php');
require_once('../Models/Usuario.php');
require_once('../Models/Imagen.php');

$response = new Response();

try {
    if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        /* Para subir una imagen necesitamos el token de acceso. */
        /* Solo administradores y moderadores pueden subir una imagen. */

        $headers = getallheaders();
        if( !isset( $headers['SILLON'] ) || strlen( $headers['SILLON'] ) <= 0 )  {
            throw new SessionException('Token de sesión inválido.');
        }

        $token_acceso = $headers['SILLON'];

        /* Param */
        if( !isset( $_FILES['imagen']['error'] ) || is_array( $_FILES['imagen']['error'] ) )    {
            throw new Exception('Error en los parametros.', 400 );
        }

        /* Si hubo un error al subir la imagen. */
        if( $_FILES['imagen']['error'] !== UPLOAD_ERR_OK )    {
            $mensaje = 'Error al subir imagen.' . Imagen::ImagenUploadErrors[$_FILES['imagen']['error']] ;
            throw new Exception( $mensaje, 500 );
        }

        /* Checar sesión. */
        $connection = DB::getConnection();
        if( !Sesion::EsValido( $token_acceso, $connection ) )   {
            throw new SessionException('No se ha iniciado sesión.');
        }

        if( Sesion::EstaCaducado( $token_acceso, $connection ) )   {
            throw new SessionException('El token de acceso ha caducado.');
        }

        $usuario = Sesion::UsuarioDeSesion( $token_acceso, $connection );

        if( $usuario->getRol() === 'Usuario' )  {
            throw new Exception('No tiene permiso para subir imagenes.', 403 );
        }    

        /* Obtenemos el path donde se van a guardar la imagen. */
        $filesys_array = explode('\\', __DIR__ );
        $curr_dir = end( $filesys_array );
        $image_path = str_replace( $curr_dir, 'Imagenes', __DIR__ ) . '\\';
        $temp_name = $_FILES['imagen']['tmp_name'] ;
        $image_path = $image_path . basename( $_FILES['imagen']['tmp_name'] );
        $type = explode( '/', $_FILES['imagen']['type'] );
        $type = '.' . end( $type );

        $image_path = str_replace( '.tmp', $type, $image_path );
            
        if( !move_uploaded_file( $temp_name, $image_path ) ) {
            throw new Exception('No se pudo copiar el archivo', 500 );
        }

        $nombre_imagen = explode('\\', $image_path );
        $nombre_imagen = end( $nombre_imagen );

        $response->setHttpStatusCode( 201 );
        $response->setSuccess( true );
        $response->addMessage('Imagen subida.');
        $response->setData( $nombre_imagen );
    }

    else    {
        throw new Exception('Método no permitido', 405 );
    }

}
catch( Exception $e )   {
    if( get_class( $e ) === 'PDOException' )
        error_log('Error en base de datos - ' . $e );

    $response->setHttpStatusCode( $e->getCode() );
    $response->addMessage( $e->getMessage() );
}
finally {
    $response->send();
}

?>