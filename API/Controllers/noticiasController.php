<?php

require_once('../Models/DB.php');
require_once('../Models/Response.php');
require_once('../Models/Sesion.php');
require_once('../Models/Noticia.php');

$response = new Response();

try {
    if( $_SERVER['REQUEST_METHOD'] === 'GET' )  {   
        if( isset($_GET['num_noticias'] ) )   {   /* Obtener las noticias más recientes. */
            if( !is_numeric( $_GET['num_noticias'] ) )   {
                throw new NoticiaException('El número de noticias no es númerico.');
            }

            $num_noticias = $_GET['num_noticias'];
            
            if( isset($_GET['seccion']) )   {
                $seccion = $_GET['seccion'];
                if( !Noticia::ExisteSeccion( $seccion ) )
                    throw new Exception('Seccion inválida', 400 );
            }

            $connection = DB::getConnection();
            $stringSQL = 'SELECT idnoticia, seccion, idmoderador, titulo,
                            DATE_FORMAT( fecha, "%Y-%m-%d %H:%i" ) AS fecha, texto, imagepath
                                FROM noticia';
            if( isset($_GET['seccion']) )   {
                $stringSQL .= ' WHERE seccion = :seccion';
            }

            $stringSQL .= ' ORDER BY idnoticia DESC LIMIT :num_noticias';
            
            $query = $connection->prepare( $stringSQL );
            $query->bindParam(':num_noticias', $num_noticias, PDO::PARAM_INT );

            if( isset($_GET['seccion']) )   {
                $query->bindParam(':seccion', $seccion, PDO::PARAM_STR );
            }

            $query->execute();
            $noticias = array();
            while( $row = $query->fetch( PDO::FETCH_ASSOC ) )   {
                $noticia = Noticia::NuevaNoticiaDesdeFila( $row );
                $noticias[] = $noticia->getArray();
            }

            $response->setHttpStatusCode( 200 );
            $response->setSuccess( true );
            $response->addMessage('Top noticias');
            $response->setData( $noticias );
        }

        else if( isset($_GET['id_noticia']) )   {
            if( !is_numeric( $_GET['id_noticia'] ) )   {
                throw new NoticiaException('Id de noticia no es númerico.');
            }

            $id_noticia = $_GET['id_noticia'];
            
            $connection = DB::getConnection();
            $noticia = Noticia::NoticiaDesdeId( $id_noticia, $connection );

            $response->setHttpStatusCode( 200 );
            $response->setSuccess( true );
            $response->addMessage('Noticia recuperada');
            $response->setData( $noticia->getArray() );
        }

        else if( isset($_GET['palabra_clave']) )   {
            $palabra_clave = $_GET['palabra_clave'];

            $connection = DB::getConnection();
            $stringSQL = "SELECT idnoticia, seccion, idmoderador, titulo,
                            DATE_FORMAT( fecha, '%Y-%m-%d %H:%i' ) AS fecha, texto, imagepath 
                                FROM noticia " ;

            $stringSQL2 = sprintf("WHERE titulo LIKE '%%%s%%'", $palabra_clave );

            $stringSQL = $stringSQL . $stringSQL2 ;

            $query = $connection->prepare( $stringSQL );
            $query->bindParam(':param', $palabra_clave, PDO::PARAM_STR );
            
            $query->execute();
            $noticias = array();
            while( $row = $query->fetch( PDO::FETCH_ASSOC ) )   {
                $noticia = Noticia::NuevaNoticiaDesdeFila( $row );
                $noticias[] = $noticia->getArray();
            }

            $response->setHttpStatusCode( 200 );
            $response->setSuccess( true );
            $response->addMessage('Búsqueda noticias');
            $response->setData( $noticias );
        }
    }

    else if( $_SERVER['REQUEST_METHOD'] === 'POST' )    {
        /* Checar sesión; el token de autorización se envia por un header especial. */
        $headers = getallheaders();
        if( !isset( $headers['SILLON'] ) || strlen( $headers['SILLON'] ) <= 0 )  {
            throw new SessionException('Token de sesión inválido.');
        }

        $token_acceso = $headers['SILLON'];

        if( $_SERVER['CONTENT_TYPE'] !== 'application/json' )
            throw new Exception('Encabezado "Content Type" no es un json', 400 );

        $post_data = file_get_contents('php://input');
        if( !$json_post_data = json_decode( $post_data ) )
            throw new Exception('El cuerpo de la solicitud no es un JSON válido', 400 );

        /* Checar sesión. */
        $connection = DB::getConnection();
        if( !Sesion::EsValido( $token_acceso, $connection ) )   {
            throw new SessionException('No se ha iniciado sesión.');
        }

        if( Sesion::EstaCaducado( $token_acceso, $connection ) )   {
            throw new SessionException('El token de acceso ha caducado.');
        }

        $usuario = Sesion::UsuarioDeSesion( $token_acceso, $connection );

        /* Solo el administrador y los moderadores pueden postear noticias */
        if( $usuario->getRol() === 'Usuario' )  {   
            throw new Exception('No tiene permiso para crear noticias', 403 );
        }

        foreach( Noticia::json_post_parameter_names as $param_name )   {
            if( !isset( $json_post_data->$param_name ) )
                throw new Exception('Falta algún parametro para POST.', 400 );
        }
        
        /* El id y la fecha se crean en la base de datos. */
        /* Se crea un objeto para verificar la informacion. */
        $noticia = new Noticia(
            null,
            $json_post_data->seccion,
            $usuario->getID(),
            $json_post_data->titulo,
            null,
            $json_post_data->texto,
            $json_post_data->imagepath
        );

        $seccion = $noticia->getSeccion();
        $id_moderador = $noticia->getIdModerador();
        $titulo = $noticia->getTitulo();
        $texto = $noticia->getTexto();
        $image_path = $noticia->getImagePath();

        $stringSQL = 'INSERT INTO noticia( seccion, idmoderador, titulo, fecha, texto, imagepath ) 
                        VALUES ( :seccion, :id_moderador, :titulo, NOW(), :texto, :image_path )';

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':seccion', $seccion, PDO::PARAM_STR );
        $query->bindParam(':id_moderador', $id_moderador, PDO::PARAM_INT );
        $query->bindParam(':titulo', $titulo, PDO::PARAM_STR );
        $query->bindParam(':texto', $texto, PDO::PARAM_STR );
        $query->bindParam(':image_path', $image_path, PDO::PARAM_STR );

        try {
            $connection->beginTransaction();

            $query->execute();
            if( $query->rowCount() == 0 )   {
                throw new DatabaseException('Error al insertar la noticia.');
            }

            $last_inserted_id = $connection->lastInsertId();
            $noticia = Noticia::NoticiaDesdeId( $last_inserted_id, $connection );

            $connection->commit();
        }
        catch( Exception $e )   {
            $connection->rollBack();
            throw $e ;
        }

        $response->setHttpStatusCode( 201 );
        $response->setSuccess( true );
        $response->addMessage('Noticia creada');
        $response->setData( $noticia->getArray() );
    }

    else if( $_SERVER['REQUEST_METHOD'] === 'PATCH' )   {
        /* Checar sesión; el token de autorización se envia por un header especial. */
        $headers = getallheaders();
        if( !isset( $headers['SILLON'] ) || strlen( $headers['SILLON'] ) <= 0 )  {
            throw new SessionException('Token de sesión inválido.');
        }

        $token_acceso = $headers['SILLON'];

        if( $_SERVER['CONTENT_TYPE'] !== 'application/json' )
            throw new Exception('Encabezado "Content Type" no es un json', 400 );

        $patch_data = file_get_contents('php://input');
        if( !$json_patch_data = json_decode( $patch_data ) )
            throw new Exception('El cuerpo de la solicitud no es un JSON válido', 400 );

        /* Checar sesión. */
        $connection = DB::getConnection();
        if( !Sesion::EsValido( $token_acceso, $connection ) )   {
            throw new SessionException('No se ha iniciado sesión.');
        }

        if( Sesion::EstaCaducado( $token_acceso, $connection ) )   {
            throw new SessionException('El token de acceso ha caducado.');
        }

        $usuario = Sesion::UsuarioDeSesion( $token_acceso, $connection );

        /* Solo el administrador y los moderadores pueden patchear noticias */
        if( $usuario->getRol() === 'Usuario' )  {   
            throw new Exception('No tiene permiso para modificar noticias', 403 );
        }

        /* Para modificar tiene que venir el id de la noticia. */
        if( !isset( $json_patch_data->id_noticia ) )    {
            throw new Exception('Falta el id de la noticia', 400 );
        }

        /* Solo el administrador o el moderador que creó la noticia puede modificarla. */
        $id_noticia = $json_patch_data->id_noticia;
        $noticia = Noticia::NoticiaDesdeId( $id_noticia, $connection );
        if( ($usuario->getRol() === 'Moderador' && $noticia->getIdModerador() !== $usuario->getID())
                || $usuario->getRol() === 'Usuario' )   {
            throw new Exception('No tiene permiso para modificar la noticia', 403 );
        }

        $stringSQL = 'UPDATE noticia SET';
        $antes = FALSE ;
        if( isset( $json_patch_data->seccion ) )   {
            $stringSQL .= ' seccion = :seccion';

            $antes = TRUE ;
        }
        if( isset( $json_patch_data->titulo ) )   {
            if( $antes )
                $stringSQL .= ',';

            $stringSQL .= ' titulo = :titulo';
            $antes = TRUE ;
        }
        if( isset( $json_patch_data->texto ) )   {
            if( $antes )
                $stringSQL .= ',';

            $stringSQL .= ' texto = :texto';
            $antes = TRUE ;
        }
        if( isset( $json_patch_data->imagepath ) )   {
            if( $antes )
                $stringSQL .= ',';

            $stringSQL .= ' imagepath = :imagepath';
            $antes = TRUE ;

            $path_nuevo = $json_patch_data->imagepath ;
            $path_antiguo = $noticia->getImagePath();
            if( strcmp( $path_antiguo, $path_nuevo ) !== 0 )   {
                /* Eliminar la imagen anterior de la noticia. */
                $filesys_array = explode('\\', __DIR__ );
                $curr_dir = end( $filesys_array );
                $image_path = str_replace( $curr_dir, 'Imagenes', __DIR__ ) . '\\';
                $nombre_imagen = $noticia->getImagePath();
                $nombre_imagen = explode('/', $nombre_imagen );
                $nombre_imagen = end( $nombre_imagen );

                $image_path = $image_path . $nombre_imagen ;
                if( !unlink( realpath( $image_path ) ) )    {
                    throw new Exception('NO se pudo eliminar la imagen anterior de la noticia', 500 );
                }
            }
        }

        $stringSQL .= ' WHERE idnoticia = :id_noticia';

        $query = $connection->prepare( $stringSQL );
        if( isset( $json_patch_data->seccion ) )
            $query->bindParam(':seccion', $json_patch_data->seccion, PDO::PARAM_STR );

        if( isset( $json_patch_data->titulo ) )
            $query->bindParam(':titulo', $json_patch_data->titulo, PDO::PARAM_STR );

        if( isset( $json_patch_data->texto ) )
            $query->bindParam(':texto', $json_patch_data->texto, PDO::PARAM_STR );

        if( isset( $json_patch_data->imagepath ) )
            $query->bindParam(':imagepath', $json_patch_data->imagepath, PDO::PARAM_STR );

        $query->bindParam(':id_noticia', $id_noticia, PDO::PARAM_INT );

        try {
            $connection->beginTransaction();

            $query->execute();
            $noticia_modificada = Noticia::NoticiaDesdeId( $id_noticia, $connection );

            $connection->commit();
        }
        catch( Exception $e )   {
            $connection->rollBack();
            throw $e ;
        }

        $response->setHttpStatusCode( 201 );
        $response->setSuccess( true );
        $response->addMessage('Noticia modificada');
        $response->setData( $noticia_modificada->getArray() );
    }

    else if( $_SERVER['REQUEST_METHOD'] === 'DELETE' )   {
        $headers = getallheaders();
        if( !isset( $headers['SILLON'] ) || strlen( $headers['SILLON'] ) <= 0 )  {
            throw new SessionException('Token de sesión inválido.');
        }

        $token_acceso = $headers['SILLON'];

        if( $_SERVER['CONTENT_TYPE'] !== 'application/json' )
            throw new Exception('Encabezado "Content Type" no es un json', 400 );

        $delete_data = file_get_contents('php://input');
        if( !$json_delete_data = json_decode( $delete_data ) )
            throw new Exception('El cuerpo de la solicitud no es un JSON válido', 400 );

        /* Checar sesión. */
        $connection = DB::getConnection();
        if( !Sesion::EsValido( $token_acceso, $connection ) )   {
            throw new SessionException('No se ha iniciado sesión.');
        }

        if( Sesion::EstaCaducado( $token_acceso, $connection ) )   {
            throw new SessionException('El token de acceso ha caducado.');
        }

        $usuario = Sesion::UsuarioDeSesion( $token_acceso, $connection );

        /* Solo el administrador puede eliminar noticias */
        if( $usuario->getRol() !== 'Administrador' )  {   
            throw new Exception('No tiene permiso para modificar noticias', 403 );
        }

        if( !isset($json_delete_data->id_noticia) ) {
            throw new Exception('Falta el id de la noticia', 400 );
        }

        $id_noticia = $json_delete_data->id_noticia ;

        $stringSQL = 'SELECT imagepath FROM noticia WHERE idnoticia = :id_noticia';
        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':id_noticia', $id_noticia, PDO::PARAM_INT );

        $query->execute();
        if( !($row = $query->fetch( PDO::FETCH_ASSOC )) )   {
            throw new Exception('No se encontró la noticia.', 400 );
        }

        $stringSQL = 'DELETE FROM comentario WHERE idnoticia = :id_noticia';
        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':id_noticia', $id_noticia, PDO::PARAM_INT );

        try {
            $connection->beginTransaction();

            $query->execute();

            /* Eliminar la imagen de la noticia. */
            $filesys_array = explode('\\', __DIR__ );
            $curr_dir = end( $filesys_array );
            $image_path = str_replace( $curr_dir, 'Imagenes', __DIR__ ) . '\\';
            $nombre_imagen = explode('/', $row['imagepath'] );
            $nombre_imagen = end( $nombre_imagen );

            $image_path = $image_path . $nombre_imagen ;
            if( !unlink( realpath( $image_path ) ) )    {
                throw new Exception('NO se pudo eliminar la noticia', 500 );
            }

            $id_noticia = $json_delete_data->id_noticia ;
            $stringSQL = 'DELETE FROM noticia WHERE idnoticia = :id_noticia';
            $query = $connection->prepare( $stringSQL );
            $query->bindParam(':id_noticia', $id_noticia, PDO::PARAM_INT );

            $query->execute();
            if( $query->rowCount() == 0 )   {
                throw new DatabaseException('No se pudo eliminar la noticia');
            }

            $connection->commit();
        }
        catch( Exception $e )   {
            $connection->rollBack();
            throw $e ;
        }

        $response->setHttpStatusCode( 200 );
        $response->setSuccess( true );
        $response->addMessage('Noticia eliminada');
        $response->setData( $id_noticia );
    }

    else    {
        throw new Exception('Método no permitido', 405 );
    }

}
catch( Exception $e ) {
    if( get_class( $e ) === 'PDOException' )
        error_log('Error en base de datos - ' . $e );

    $response->setHttpStatusCode( $e->getCode() );
    $response->addMessage( $e->getMessage() );
}
finally {
    $response->send();
}

?>