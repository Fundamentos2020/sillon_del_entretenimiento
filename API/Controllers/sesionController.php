<?php

require_once('../Models/DB.php');
require_once('../Models/Response.php');
require_once('../Models/Usuario.php');
require_once('../Models/Sesion.php');

/* Por seguridad, todos los intentos fallidos regresan esto */
$error_string = 'El nombre de usuario/contraseña son incorrectos';  

$response = new Response();

try {
    if( $_SERVER['REQUEST_METHOD'] === 'POST' )  {   /* Crear una sesión. */
        /* Se pide crear una nueva sesión cuando el token de actualización */
        /* dejo de ser válido, o se esta accediendo al sitio por primera vez. */
        if( $_SERVER['CONTENT_TYPE'] !== 'application/json' )
            throw new Exception('Encabezado "Content Type" no es un json', 400 );

        $post_data = file_get_contents('php://input');
        if( !$json_post_data = json_decode( $post_data ) )
            throw new Exception('El cuerpo de la solicitud no es un JSON válido', 400 );

        /* Solo se requiere un usuario y una contraseña para obtener una sesión. */
        if( !isset($json_post_data->nombre_usuario) || !isset($json_post_data->contrasenia) )    {
            throw new SessionException( $error_string );
        }

        /* Checar que el usuario exista. */
        $nom_usuario = $json_post_data->nombre_usuario ;
        $contrasenia = $json_post_data->contrasenia ;
        $connection = DB::getConnection();
        $stringSQL = 'SELECT idusuario, nombreusuario, contrasenia, rol FROM usuario 
                        WHERE nombreusuario = :nombre_usuario';

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':nombre_usuario', $nom_usuario, PDO::PARAM_STR );
        $query->execute();
        
        if( !($row = $query->fetch( PDO::FETCH_ASSOC )) )   {
            throw new SessionException( $error_string );
        }

        $contrasenia_hash = $row['contrasenia'];
        $usuario_response = $row ;
        
        if( !password_verify( $contrasenia, $contrasenia_hash ) )    {
            throw new SessionException( $error_string );
        }

        /* Desde este punto se supone que la información es válida. */
        $id_usuario = $row['idusuario'];
        Sesion::EliminaSesionesActivas( $id_usuario, $connection );

        $token_acceso = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());
        $caducidad_token_acceso = 1200 ;    /* Segundos */
        $token_actualizacion = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());
        $caducidad_token_actualizacion = 1296000 ;  /* Segundos */

        /* Se convierte a DATETIME, sumando NOW() + cantidad de segundos. Este valor se compara con time() para ver si ya pasó. */
        $stringSQL = 'INSERT INTO sesion( idusuario, tokenacceso, caducidadtokenacceso, 
                                            tokenactualizacion, caducidadtokenactualizacion )
                        VALUES( :id_usuario, 
                                    :token_acceso, 
                                    DATE_ADD( NOW(), INTERVAL :caducidad_token_acceso SECOND),
                                    :token_actualizacion, 
                                    DATE_ADD( NOW(), INTERVAL :caducidad_token_actualizacion SECOND) )';

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT );
        $query->bindParam(':token_acceso', $token_acceso, PDO::PARAM_STR );
        $query->bindParam(':caducidad_token_acceso', $caducidad_token_acceso, PDO::PARAM_INT );
        $query->bindParam(':token_actualizacion', $token_actualizacion, PDO::PARAM_STR );
        $query->bindParam(':caducidad_token_actualizacion', $caducidad_token_actualizacion, PDO::PARAM_INT );

        try {
            $connection->beginTransaction();

            $query->execute();
            if( $query->rowCount() == 0 )   {
                throw new DatabaseException('No se pudo crear la sesion');
            }

            $stringSQL = 'SELECT * FROM sesion 
                            WHERE idusuario = :id_usuario';

            $query = $connection->prepare( $stringSQL );
            $query->bindParam('id_usuario', $id_usuario, PDO::PARAM_INT );

            $query->execute();
            if( !($row = $query->fetch( PDO::FETCH_ASSOC )) )   {
                throw new DatabaseException('No se pudo leer despues de insertar');
            }

            $connection->commit();
        }
        catch( Exception $e )   {
            $connection->rollBack();
            throw $e ;
        }

        $array = array( 
            'token_acceso' => $row['TokenAcceso'],
            'token_actualizacion' => $row['TokenActualizacion'],
            'nombre_usuario' => $usuario_response['nombreusuario'],
            'id' => $usuario_response['idusuario'], 
            'rol' => $usuario_response['rol']
        );

        $response->setHttpStatusCode( 201 );
        $response->setSuccess( true );
        $response->addMessage('Sesion creada');
        $response->setData( $array );
    }   /* FIN -POST- */

    else if( $_SERVER['REQUEST_METHOD'] === 'PATCH' )    {   /* Actualiza un token de acceso. */    
        /* Vamos a recibir un token de actualización, y con ese token vamos a actualizar */
        /* el token de acceso. En otras partes de la API se checa si el token de acceso */
        /* es válido, para hacer varias operaciones. Si no es válido, el cliente tiene */
        /* que mandar una solicitud de PATCH, para que se le devuelva un token de acceso */
        /* válido (no caducado). */
        if( $_SERVER['CONTENT_TYPE'] !== 'application/json' )
            throw new Exception('Encabezado "Content Type" no es un json', 400 );

        $patch_data = file_get_contents('php://input');
        if( !$json_patch_data = json_decode( $patch_data ) )
            throw new Exception('El cuerpo de la solicitud no es un JSON válido', 400 );

        /* Se requiere del token de acceso y del de actualizacion. */
        if( !isset($json_patch_data->token_acceso) || !isset($json_patch_data->token_actualizacion) )    {
            throw new SessionException('Faltan argumentos para el PATCH', 400 );
        }

        $token_acceso = $json_patch_data->token_acceso ;
        $token_actualizacion = $json_patch_data->token_actualizacion ;

        /* Verificar que exista una sesión con los tokens especificados. */
        $connection = DB::getConnection();
        $stringSQL = 'SELECT * FROM sesion
                        WHERE tokenacceso = :token_acceso AND
                            tokenactualizacion = :token_actualizacion' ;

        $query = $connection->prepare( $stringSQL );
        $query->bindParam('token_acceso', $token_acceso, PDO::PARAM_STR );
        $query->bindParam('token_actualizacion', $token_actualizacion, PDO::PARAM_STR );

        $query->execute();
        if( !($row = $query->fetch( PDO::FETCH_ASSOC )) )   {
            throw new SessionException('No se encontró la sesión, vuelva a iniciar sesión.');
        }

        $token_actualizacion = $row['TokenActualizacion'];
        $caducidad_token_actualizacion = $row['CaducidadTokenActualizacion'];

        if( strtotime( $caducidad_token_actualizacion ) < time() )  {
            /* Si el token de actualización esta caducado, se elimina la sesión. */
            $token_acceso = $row['TokenAcceso'];

            $stringSQL = 'DELETE FROM sesion WHERE tokenactualizacion = :token_actualizacion AND
                            tokenacceso = :token_acceso';

            $query = $connection->prepare( $stringSQL );
            $query->bindParam(':token_actualizacion', $token_actualizacion, PDO::PARAM_STR );
            $query->bindParam(':token_acceso', $token_acceso, PDO::PARAM_STR );
            $query->execute();

            if( $query->rowCount() == 0 )   {
                throw new DatabaseException('Error al eliminar sesion caducada.');
            }

            throw new SessionException('Token de actualizacion caducado. Inicie sesión de nuevo');
        }

        /* WHERE */
        $id_usuario = $row['IdUsuario'];
        
        /* UPDATE */
        $nuevo_token_acceso = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)) . time());
        $caducidad_token_acceso = 1200 ;    /* SEGUNDOS */

        $stringSQL = 'UPDATE sesion SET tokenacceso = :nuevo_token_acceso,
                        caducidadtokenacceso = DATE_ADD( NOW(), INTERVAL :caducidad_token_acceso SECOND )
                            WHERE idusuario = :id_usuario AND tokenactualizacion = :token_actualizacion';

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':nuevo_token_acceso', $nuevo_token_acceso, PDO::PARAM_STR );
        $query->bindParam(':caducidad_token_acceso', $caducidad_token_acceso, PDO::PARAM_INT );
        $query->bindParam('id_usuario', $id_usuario, PDO::PARAM_INT );
        $query->bindParam(':token_actualizacion', $token_actualizacion, PDO::PARAM_STR );

        try {
            $connection->beginTransaction();

            $query->execute();
            if( $query->rowCount() == 0 )   {
                throw new DatabaseException('No se pudo actualizar la sesión.');
            }
            
            $connection->commit();
        }
        catch( Exception $e )   {
            $connection->rollBack();
            throw $e ;
        }

        $array = array(
            'token_acceso' => $nuevo_token_acceso,
            'token_actualizacion' => $token_actualizacion
        );

        $response->setHttpStatusCode( 200 );
        $response->setSuccess( true );
        $response->addMessage('Sesion refrescada.');
        $response->setData( $array );
    }

    else if( $_SERVER['REQUEST_METHOD'] === 'DELETE' )    {   /* Cerrar sesion. */
        if( $_SERVER['CONTENT_TYPE'] !== 'application/json' )
            throw new Exception('Encabezado "Content Type" no es un json', 400 );

        $patch_data = file_get_contents('php://input');
        if( !$json_patch_data = json_decode( $patch_data ) )
            throw new Exception('El cuerpo de la solicitud no es un JSON válido', 400 );

        /* Se requiere del token de acceso y del de actualizacion. */
        if( !isset($json_patch_data->token_acceso) || !isset($json_patch_data->token_actualizacion) )    {
            throw new SessionException('Faltan argumentos para el DELETE', 400 );
        }

        $token_acceso = $json_patch_data->token_acceso ;
        $token_actualizacion = $json_patch_data->token_actualizacion ;

        $connection = DB::getConnection();
        $stringSQL = 'DELETE FROM sesion
                        WHERE tokenacceso = :token_acceso AND
                            tokenactualizacion = :token_actualizacion' ;

        $query = $connection->prepare( $stringSQL );
        $query->bindParam('token_acceso', $token_acceso, PDO::PARAM_STR );
        $query->bindParam('token_actualizacion', $token_actualizacion, PDO::PARAM_STR );

        try {
            $connection->beginTransaction();

            $query->execute();
            if( $query->rowCount() == 0 )   {
                throw new SessionException('No se pudo cerrar sesión.');
            }

            $connection->commit();
        }
        catch( Exception $e )   {
            $connection->rollBack();
            throw $e ;
        }

        $response->setHttpStatusCode( 200 );
        $response->setSuccess( true );
        $response->addMessage('Sesion cerrada.');
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