<?php

require_once('../Models/DB.php');
require_once('../Models/Response.php');
require_once('../Models/Sesion.php');
require_once('../Models/Noticia.php');
require_once('../Models/Comentario.php');

$response = new Response();

try {
    if( $_SERVER['REQUEST_METHOD'] === 'GET' )  {   /* Comentarios de una noticia */
        if( !isset( $_GET['id_noticia'] ) ) {
            throw new Exception('Falta el id de la noticia.');
        }

        $id_noticia = $_GET['id_noticia'];

        if( !is_numeric( $id_noticia ) )    {
            throw new Exception('El id de lo noticia no es númerico.', 400 );
        }

        $connection = DB::getConnection();
        $stringSQL = 'SELECT usuario.nombreusuario, comentario.idcomentario, 
                        comentario.idnoticia, comentario.idusuario,  
                            DATE_FORMAT( comentario.fecha, "%Y-%m-%d %H:%i") AS fecha, comentario.texto
                                FROM comentario INNER JOIN usuario 
                                    ON comentario.idusuario = usuario.idusuario 
                                        WHERE idnoticia = :id_noticia';

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':id_noticia', $id_noticia, PDO::PARAM_INT );

        $query->execute();
        $comentarios = array();
        while( $row = $query->fetch( PDO::FETCH_ASSOC ) )   {
            $comentario = Comentario::NuevoComentarioDesdeFila( $row );
            $comentarios[] = $comentario->getArray();
        }

        $response->setHttpStatusCode( 200 );
        $response->setSuccess( true );
        $response->addMessage('Comentarios');
        $response->setData( $comentarios );
    }

    else if( $_SERVER['REQUEST_METHOD'] === 'POST' )    {   /* Agregar a noticia. */
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

        foreach( Comentario::json_post_parameter_names as $param_name )   {
            if( !isset( $json_post_data->$param_name ) )
                throw new Exception('Falta algún parametro para POST.', 400 );
        }

        $id_noticia = $json_post_data->idnoticia ;
        $texto = $json_post_data->texto ;

        /* El id se pone al insertar, asi como la fecha. */
        $comentario = new Comentario(
            null,
            $id_noticia,
            $usuario->getID(),
            null,
            $texto
        );

        $id_noticia = $comentario->getIdNoticia();
        $id_usuario = $comentario->getIdusuario();
        $text = $comentario->getTexto();

        $stringSQL = 'INSERT INTO comentario( idnoticia, idusuario, fecha, texto )
                        VALUES( :id_noticia, :id_usuario, NOW(), :texto )';
        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':id_noticia', $id_noticia, PDO::PARAM_INT );
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT );
        $query->bindParam(':texto', $texto, PDO::PARAM_INT );

        try {
            $connection->beginTransaction();

            $query->execute();
            if( $query->rowCount() == 0 )   {
                throw new DatabaseException('No se pudo crear el comentario');
            }

            $last_inserted_id = $connection->lastInsertId();
            $comentario = Comentario::ComentarioDesdeId( $last_inserted_id, $connection );

            $connection->commit();
        }
        catch( Exception $e )   {
            $connection->rollBack();
            throw $e ;
        }

        $response->setHttpStatusCode( 201 );
        $response->setSuccess( true );
        $response->addMessage('Comentario creado');
        $response->setData( $comentario->getArray() );
    }

    else if( $_SERVER['REQUEST_METHOD'] === 'PATCH' )   {   /* Modificar comentario. */
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

        if( !isset( $json_patch_data->texto_comentario ) || !isset( $json_patch_data->id_comentario ) ) {
            throw new Exception('Falta algún parametro para PATCH.', 400 );
        }

        $id_comentario = $json_patch_data->id_comentario ;
        $usuario = Sesion::UsuarioDeSesion( $token_acceso, $connection );
        $comentario = Comentario::ComentarioDesdeId( $id_comentario, $connection );

        /* Solo el usuario que creo el comentario puede modificar. */
        if( $usuario->getID() !== $comentario->getIdusuario() ) {
            throw new Exception('No tiene permiso para modificar.', 403 );
        }

        /* Solo se actualiza el texto. Un asterisco al final indica cuantas veces fue editado. */
        $texto = $json_patch_data->texto_comentario . '*' ;

        $stringSQL = 'UPDATE comentario SET texto = :texto WHERE idcomentario = :id_comentario';
        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':texto', $texto, PDO::PARAM_STR );
        $query->bindParam(':id_comentario', $id_comentario, PDO::PARAM_INT );

        try {
            $connection->beginTransaction();

            $query->execute();
            if( $query->rowCount() == 0 ) {
                throw new DatabaseException('No se pudo modificar el comentario');
            }

            $comentario = Comentario::ComentarioDesdeId( $id_comentario, $connection );
            $connection->commit();
        }
        catch( Exception $e )   {
            $connection->rollBack();
            throw $e ;
        }

        $response->setHttpStatusCode( 200 );
        $response->setSuccess( true );
        $response->addMessage('Se modifico el comentario');
        $response->setData( $comentario->getArray() );
    }

    else if( $_SERVER['REQUEST_METHOD'] === 'DELETE' )   {  /* Administradores, moderadores y el usuario que creo el comentario pueden eliminar. */
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

        if( !isset( $json_patch_data->id_comentario ) ) {
            throw new Exception('Falta algún parametro para DELETE.', 400 );
        }

        $id_comentario = $json_patch_data->id_comentario ;
        $usuario = Sesion::UsuarioDeSesion( $token_acceso, $connection );
        $comentario = Comentario::ComentarioDesdeId( $id_comentario, $connection );

        if( $usuario->getRol() === 'Usuario' && $usuario->getID() !== $comentario->getIdusuario() ) {
            throw new Exception('No tiene permiso para eliminar.', 403 );
        }

        $stringSQL = 'DELETE FROM comentario WHERE idcomentario = :id_comentario';
        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':id_comentario', $id_comentario, PDO::PARAM_INT );

        try {
            $connection->beginTransaction();

            $query->execute();
            if( $query->rowCount() == 0 )   {
                throw new DatabaseException('Error al eliminar el comentario.');
            }

            $connection->commit();
        }
        catch( Exception $e )   {
            $connection->rollBack();
            throw $e ;
        }

        $id_comentario_eliminado = array(
            'id_comentario' => $id_comentario
        );

        $response->setHttpStatusCode( 200 );
        $response->setSuccess( true );
        $response->addMessage('Se elimino el comentario');
        $response->setData( $id_comentario_eliminado );
        
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