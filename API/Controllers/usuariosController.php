<?php

require_once('../Models/DB.php');
require_once('../Models/Response.php');
require_once('../Models/Usuario.php');
require_once('../Models/Sesion.php');

$response = new Response();

try {
    if( $_SERVER['REQUEST_METHOD'] === 'POST' )  {  /* Crear un usuario. */
        if( $_SERVER['CONTENT_TYPE'] !== 'application/json' )
            throw new Exception('Encabezado "Content Type" no es un json', 400 );

        $post_data = file_get_contents('php://input');
        if( !$json_post_data = json_decode( $post_data ) )
            throw new Exception('El cuerpo de la solicitud no es un JSON válido', 400 );

        /* Checa que el json tenga los parametros definidos en Usuario::json_post_parameter_names */
        /* Los nombres de los parametros deben de coincidir exactamente el json. */
        foreach( Usuario::json_post_parameter_names as $param_name )   {
            if( !isset( $json_post_data->$param_name ) )
                throw new Exception('Falta algún parametro para INSERT.', 400 );
        }

        /* Se encripta la contraseña. */
        $contrasenia_hash = password_hash( $json_post_data->contrasenia, PASSWORD_DEFAULT );

        /* Elementos a verificar contruyendo un nuevo usuario */
        $usuario = new Usuario(
            null,
            trim( $json_post_data->nombre_usuario ),
            trim( $contrasenia_hash ),
            $json_post_data->email,
            null,
            null
        );

        $nombre_usuario = $usuario->getNombreUsuario();
        $contrasenia = $usuario->getContrasenia();
        $email = $usuario->getEmail();

        /* Conexión a db_sillondelentretenimiento */
        $connection = DB::getConnection();
        
        /* Verificar que no exista un usuario con el mismo nombre o email. */
        $stringSQL = 'SELECT * FROM usuario 
                        WHERE nombreusuario = :nombre_usuario OR email = :email';

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':nombre_usuario', $nombre_usuario, PDO::PARAM_STR );
        $query->bindParam(':email', $email, PDO::PARAM_STR );
        $query->execute();

        if( $row = $query->fetch( PDO::FETCH_ASSOC ) )    {   /* Ya existe un usuario con ese nombre de usuario o email */
            throw new DatabaseException('Ya existe un usuario con ese nombre/email.');
        }

        /* Insertar el usuario. */
        $stringSQL = 'INSERT INTO usuario( nombreusuario, contrasenia, email ) 
                        VALUES( :nombre_usuario, :contrasenia, :email )';

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':nombre_usuario', $nombre_usuario, PDO::PARAM_STR );
        $query->bindParam(':contrasenia', $contrasenia, PDO::PARAM_STR );
        $query->bindParam(':email', $email, PDO::PARAM_STR );

        /* Transacción: si no se puede insertar o no se puede obtener despues de crearla, rollback. */
        try {
            $connection->beginTransaction();

            $query->execute();
            if( $query->rowCount() == 0 )   {
                throw new DatabaseException('Error al insertar');
            }

            $insertedID = $connection->lastInsertId();
            $usuario = Usuario::UsuarioDesdeId( $insertedID, $connection );

            $connection->commit();
            /* Hasta que sabemos que los valores devueltos son válidos, hacemos el commit. */
        }
        catch( Exception $e )   {
            $connection->rollBack();
            throw $e ;
        }

        $response->setHttpStatusCode( 201 );
        $response->setSuccess( true );
        $response->addMessage('Usuario creado.');
        $response->setData( $usuario->getArray() );
    }

    else if( $_SERVER['REQUEST_METHOD'] === 'GET' ) {   /* Obtener usuarios; solo el administrador puede. */
        /* Checar sesión; el token de autorización se envia por un header especial. */
        $headers = getallheaders();
        if( !isset( $headers['SILLON'] ) || strlen( $headers['SILLON'] ) <= 0 )  {
            throw new SessionException('Token de sesión inválido.');
        }

        $token_acceso = $headers['SILLON'];
        
        /* Conexión a db_sillondelentretenimiento */
        $connection = DB::getConnection();
        $stringSQL = 'SELECT usuario.rol, sesion.CaducidadTokenAcceso FROM sesion 
                        INNER JOIN usuario ON sesion.IdUsuario = usuario.IdUsuario WHERE sesion.TokenAcceso = :token_acceso'; 

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':token_acceso', $token_acceso, PDO::PARAM_STR );
        $query->execute();

        if( !($row = $query->fetch( PDO::FETCH_ASSOC) ) )   {
            throw new SessionException('No ha iniciado sesión');
        }

        $caducidad_token_acceso = $row['CaducidadTokenAcceso'];
        
        if( time() > strtotime( $caducidad_token_acceso ) ) {   /* Si ya caducó el token de acceso. */
            
            throw new SessionException('Token de acceso caducado. Refresque con un token de actualización');
        }

        $rol = $row['rol'];

        if( $rol != 'Administrador' )   {
            throw new Exception('No tiene permiso para ejecutar esa operación.', 403 );
        }

        /* El token pertenece a una sesión válida y a un administrador. */
        $stringSQL = 'SELECT idusuario, nombreusuario, email, 
                            DATE_FORMAT( fecharegistro, "%Y-%m-%d %H:%i" ) AS fecharegistro, rol 
                            FROM usuario';

        $query = $connection->prepare( $stringSQL );
        $query->execute();

        $usuarios = array();
        while( $row = $query->fetch(PDO::FETCH_ASSOC) )    {
            $usuario = new Usuario(
                $row['idusuario'],
                $row['nombreusuario'],
                null,
                $row['email'],
                $row['fecharegistro'],
                $row['rol']
            );

            $usuarios[] = $usuario->getArray();
        }

        $response->setHttpStatusCode( 201 );
        $response->setSuccess( true );
        $response->addMessage('Usuarios');
        $response->setData( $usuarios );
    }

    else if( $_SERVER['REQUEST_METHOD'] === 'PATCH' ) {   /* Modificar un usuario */
        /* Un administrador puede modificar el rol de usuario. Un usuario puede modificar */
        /* su nombre de usuario, y contraseña. */

        if( $_SERVER['CONTENT_TYPE'] !== 'application/json' )
            throw new Exception('Encabezado "Content Type" no es un json', 400 );

        $patch_data = file_get_contents('php://input');
        if( !$json_patch_data = json_decode( $patch_data ) )
            throw new Exception('El cuerpo de la solicitud no es un JSON válido', 400 );

        /* Checar sesión; el token de autorización se envia por un header especial. */
        $headers = getallheaders();
        if( !isset( $headers['SILLON'] ) || strlen( $headers['SILLON'] ) <= 0 )  {
            throw new SessionException('Token de sesión inválido.');
        }

        $token_acceso = $headers['SILLON'];
        
        /* Conexión a db_sillondelentretenimiento */
        $connection = DB::getConnection();
        $stringSQL = 'SELECT usuario.idusuario, usuario.rol, sesion.CaducidadTokenAcceso FROM sesion 
                        INNER JOIN usuario ON sesion.IdUsuario = usuario.IdUsuario WHERE sesion.TokenAcceso = :token_acceso'; 

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':token_acceso', $token_acceso, PDO::PARAM_STR );
        $query->execute();

        if( !($row = $query->fetch( PDO::FETCH_ASSOC) ) )   {
            throw new SessionException('No ha iniciado sesión');
        }

        $caducidad_token_acceso = $row['CaducidadTokenAcceso'];
        
        if( time() > strtotime( $caducidad_token_acceso ) ) {   /* Si ya caducó el token de acceso. */
            throw new SessionException('Token de acceso caducado. Refresque con un token de actualización');
        }
        
        $id_usuario = $row['idusuario'];
        $rol = $row['rol'];

        if( isset( $json_patch_data->usuarios_modificar ) )  {
            if( $rol !== 'Administrador' )  {   /* Si es un administrador con una lista de usuarios para modificar. */
                throw new Exception('No tiene permisos para hacer eso.', 403 );
            }

            /* Contiene el id y el rol. */
            $usuarios_modificar = $json_patch_data->usuarios_modificar ;
            $usuarios_modificados = array();
            try {
                $connection->beginTransaction();
                foreach( $usuarios_modificar as $usuario )  {
                    $rol = $usuario->rol ;
                    $id_usuario = $usuario->id_usuario ;

                    $stringSQL = 'UPDATE usuario SET rol = :rol WHERE idusuario = :id_usuario';
                    $query = $connection->prepare( $stringSQL );
                    $query->bindParam(':rol', $rol, PDO::PARAM_STR );
                    $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT );

                    $query->execute();
                    if( $query->rowCount() == 0 )   {
                        throw new DatabaseException('Error al actualizar usuario: ' . $id_usuario );
                    }

                    $usuarios_modificados[] = array('id_usuario' => $id_usuario, 
                                                'rol' => $rol );
                        
                }

                $connection->commit();
            }
            catch( Exception $e )   {
                $connection->rollBack();
                throw $e ;
            }

            $response->setHttpStatusCode( 200 );
            $response->setSuccess( true );
            $response->addMessage('Usuarios modificados');
            $response->setData( $usuarios_modificados );
        }

        else    {   /* Si es un usuario modificando su contraseña. */
            if( !isset( $json_patch_data->contrasenia )
                    || !isset( $json_patch_data->contrasenia_vieja ) )
                throw new Exception('Faltan parametros para el PATCH.', 400 );

            $nueva_contrasenia_hash = password_hash( $json_patch_data->contrasenia, PASSWORD_DEFAULT );
            $vieja_contrasenia = $json_patch_data->contrasenia_vieja ;

            $stringSQL = 'SELECT contrasenia FROM usuario WHERE idusuario = :id_usuario';
            $query = $connection->prepare( $stringSQL );
            $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_STR );
            $query->execute();

            $row = $query->fetch( PDO::FETCH_ASSOC );
            if( !(password_verify( $vieja_contrasenia, $row['contrasenia'] )) )  {
                throw new SessionException('La contraseña es incorrecta.');
            }

            $stringSQL = 'UPDATE usuario SET contrasenia = :nueva_contrasenia_hash
                            WHERE idusuario = :id_usuario';

            $query = $connection->prepare( $stringSQL );
            $query->bindParam(':nueva_contrasenia_hash', $nueva_contrasenia_hash, PDO::PARAM_STR );
            $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT );

            try {
                $connection->beginTransaction();
                $query->execute();

                if( $query->rowCount() == 0 )   {
                    throw new DatabaseException('No se pudo actualizar.');
                }

                $usuario_modificado = Usuario::UsuarioDesdeId( $id_usuario, $connection );
                $connection->commit();
            }
            catch( Exception $e )    {
                $connection->rollBack();
                throw $e ;
            }

            $response->setHttpStatusCode( 200 );
            $response->setSuccess( true );
            $response->addMessage('Contraseña modificada');
            $response->setData( $usuario_modificado->getArray() );
        }
    }

    else if( $_SERVER['REQUEST_METHOD'] === 'DELETE' )  {
        if( $_SERVER['CONTENT_TYPE'] !== 'application/json' )
            throw new Exception('Encabezado "Content Type" no es un json', 400 );

        $delete_data = file_get_contents('php://input');
        if( !$json_delete_data = json_decode( $delete_data ) )
            throw new Exception('El cuerpo de la solicitud no es un JSON válido', 400 );

        /* Checar sesión; el token de autorización se envia por un header especial. */
        $headers = getallheaders();
        if( !isset( $headers['SILLON'] ) || strlen( $headers['SILLON'] ) <= 0 )  {
            throw new SessionException('Token de sesión inválido.');
        }

        $token_acceso = $headers['SILLON'];
        
        /* Conexión a db_sillondelentretenimiento */
        $connection = DB::getConnection();
        $stringSQL = 'SELECT usuario.idusuario, usuario.rol, sesion.CaducidadTokenAcceso FROM sesion 
                        INNER JOIN usuario ON sesion.IdUsuario = usuario.IdUsuario WHERE sesion.TokenAcceso = :token_acceso'; 

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':token_acceso', $token_acceso, PDO::PARAM_STR );
        $query->execute();

        if( !($row = $query->fetch( PDO::FETCH_ASSOC) ) )   {
            throw new SessionException('No ha iniciado sesión');
        }

        $caducidad_token_acceso = $row['CaducidadTokenAcceso'];
        
        if( time() > strtotime( $caducidad_token_acceso ) ) {   /* Si ya caducó el token de acceso. */
            throw new SessionException('Token de acceso caducado. Refresque con un token de actualización');
        }
        
        $rol = $row['rol'];

        if( $rol !== 'Administrador' )  {   /* Si es un administrador con una lista de usuarios para modificar. */
            throw new Exception('No tiene permisos para hacer eso.', 403 );
        }

        if( !isset( $json_delete_data->id_usuario_eliminar ) )  {
            throw new Exception('Faltan parametros para delete.', 400 );
        }

        $id_usuario_eliminar = $json_delete_data->id_usuario_eliminar ;

        $stringSQL = 'DELETE FROM usuario WHERE idusuario = :id_usuario_eliminar';
        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':id_usuario_eliminar', $id_usuario_eliminar, PDO::PARAM_INT );

        try {
            $connection->beginTransaction();
            $query->execute();

            if( $query->rowCount() == 0 )   {
                throw new DatabaseException('No se pudo eliminar.');
            }

            $connection->commit();
        }
        catch( Exception $e )    {
            $connection->rollBack();
            throw $e ;
        }

        $response->setHttpStatusCode( 200 );
        $response->setSuccess( true );
        $response->addMessage('Usuario eliminado');
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