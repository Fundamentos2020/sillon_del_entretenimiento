<?php

require_once('Usuario.php');

class SessionException extends Exception {
    protected $code = 401 ; /* Sin autorización */
}

class Sesion    {

    public static function EsValido( $token_acceso, $connection ) {
        $stringSQL = 'SELECT * FROM sesion WHERE tokenacceso = :token_acceso'; 

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':token_acceso', $token_acceso, PDO::PARAM_STR );
        $query->execute();

        if( !($row = $query->fetch( PDO::FETCH_ASSOC) ) )   {
            return FALSE ;
        }

        return TRUE ;
    }

/* Checa la caducidad de los tokens de acceso. */
    public static function EstaCaducado( $token_acceso, $connection ) {
        $stringSQL = 'SELECT caducidadtokenacceso FROM sesion WHERE tokenacceso = :token_acceso';
        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':token_acceso', $token_acceso, PDO::PARAM_STR );
        $query->execute();

        $row = $query->fetch( PDO::FETCH_ASSOC );
        $caducidad = $row['caducidadtokenacceso'];

        if( time() > strtotime( $caducidad ) )  {
            return TRUE ;
        }

        return FALSE ;
    }

/* Devuelve el usuario realcionado con la sesión */
    public static function UsuarioDeSesion( $token_acceso, $connection )  {
        $stringSQL = 'SELECT usuario.idusuario, nombreusuario, contrasenia, email, 
                        DATE_FORMAT( fecharegistro, "%Y-%m-%d %H:%i" ) AS fecharegistro, rol FROM sesion 
                            INNER JOIN usuario ON sesion.IdUsuario = usuario.IdUsuario 
                                WHERE sesion.TokenAcceso = :token_acceso'; 

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':token_acceso', $token_acceso, PDO::PARAM_STR );
        $query->execute();

        if( !($row = $query->fetch( PDO::FETCH_ASSOC) ) )   {
            throw new SessionException('No ha iniciado sesión');
        }

        return Usuario::NuevoUsuarioDesdeFila( $row ); ;
    }

    /* Se borran todas las sesiones de ese usuario. */
    public static function EliminaSesionesActivas( $id_usuario, $connection ) {
        $stringSQL = 'DELETE FROM sesion WHERE idusuario = :id_usuario';
        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT );

        $query->execute();
        /* No nos interesa si afecto a algo, solo que se eliminen todas las sesiones anteriores. */
    }
}
?>