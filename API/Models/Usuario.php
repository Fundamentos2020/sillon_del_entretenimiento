<?php

class UsuarioException extends Exception {
    protected $code = 400 ; /* Bad request */
}

class Usuario {
    /* Constantes de la clase en la base de datos */
    private const ID_MIN_VAL = 0 ;
    private const USRNM_LEN = 50 ;
    private const PASSW_LEN = 255 ;
    private const EMAIL_LEN = 50 ;
    private const INT_SIZE = 2147483647 ;
    private const DESC_LEN = 150 ;
    private const ROLES = array(
        'ADMIN' => 'Administrador',
        'MOD' => 'Moderador',
        'USR' => 'Usuario'
    );

    /* Arreglo para checar los parametros del json */
    public const json_post_parameter_names = array(
        'nombre_usuario',
        'contrasenia',
        'email'
    );
    
    /*Atributos */
    private $_id ;
    private $_nombre_usuario ;
    private $_contrasenia ;
    private $_email ;
    private $_fecha_registro ;
    private $_rol ;

    /* Constructor */
    public function __construct( $id, $nombre_usuario, $contrasenia, $email, $fecha_registro, $rol )  {
        $this->setID( $id );
        $this->setNombreUsuario( $nombre_usuario );
        $this->setContrasenia( $contrasenia );
        $this->setEmail( $email );
        $this->setFechaRegistro( $fecha_registro );
        $this->setRol( $rol );
    }

    public static function NuevoUsuarioDesdeFila( $row )    {
        $usuario = new Usuario(
            $row['idusuario'],
            $row['nombreusuario'],
            $row['contrasenia'],
            $row['email'],
            $row['fecharegistro'],
            $row['rol']
        );

        return $usuario ;
    } 

    public static function UsuarioDesdeId( $id_usuario, $connection )   {
        $stringSQL = 'SELECT idusuario, nombreusuario, contrasenia, email, 
                            DATE_FORMAT( fecharegistro, "%Y-%m-%d %H:%i" ) AS fecharegistro, rol 
                            FROM usuario WHERE idusuario = :id_usuario' ;
        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT );
        $query->execute();
        
        if( !($row = $query->fetch( PDO::FETCH_ASSOC )) )    {
            throw new DatabaseException('Error al obtener despues de insertar');
        }

        return Usuario::NuevoUsuarioDesdeFila( $row );
    }

    /* Setters */
    public function setID( $id ) {
        if( $id !== null && ( !is_numeric( $id ) || $id <= self::ID_MIN_VAL || $id >= self::INT_SIZE || $this->_id !== null ) ) {
            throw new UsuarioException('Error en id de usuario.');
        }

        $this->_id = $id ;
    }

    public function setNombreUsuario( $nombre_usuario )    {
        if( $nombre_usuario === null || strlen( $nombre_usuario ) > self::USRNM_LEN || strlen( $nombre_usuario ) <= 0 )  {
            throw new UsuarioException('Error en nombre_usuario de usuario');
        }

        $this->_nombre_usuario = $nombre_usuario ;
    }

    public function setContrasenia( $contrasenia )  {
        if( $contrasenia !== null && strlen( $contrasenia ) > self::PASSW_LEN )    {
            throw new UsuarioException('Error en contrasenia de usuario.');
        }

        $this->_contrasenia = $contrasenia ;
    }

    public function setEmail( $email )    {
        if( $email === null || strlen( $email ) > self::EMAIL_LEN )  {
            throw new UsuarioException('Error en campo email de usuario.');
        }

        $this->_email = strtoupper( $email );
    }

    public function setFechaRegistro( $fecha_registro ) {
        if( $fecha_registro !== null && date_format(date_create_from_format( 'Y-m-d H:i', $fecha_registro ), 'Y-m-d H:i') !== $fecha_registro )   {
            throw new UsuarioException('Error en fecha registro de usuario.');
        }

        $this->_fecha_registro = $fecha_registro ;
    }

    public function setRol( $rol ) {    //Compara sin tener en cuenta mayúsculas ni minúsculas, luego assigna valores de la enum.
        if( $rol != null )  {
            if( !strcasecmp( $rol, self::ROLES['ADMIN'] ) )
                $this->_rol = self::ROLES['ADMIN'] ;

            else if( !strcasecmp( $rol, self::ROLES['MOD'] ) )
                $this->_rol = self::ROLES['MOD'];

            else if( !strcasecmp( $rol, self::ROLES['USR'] ) )
                $this->_rol = self::ROLES['USR'];
            
            //No ponemos aqui el valor por defecto por el caso de un rol inexistente.
            else
                throw new UsuarioException('Error en rol de usuario.');
        }

        //Puede ser null, por defecto se le asigna 'Usuario' en la base de datos
        $this->_rol = $rol ;
    }

    /* Getters */
    public function getID() {
        return $this->_id ;
    }

    public function getNombreUsuario() {
        return $this->_nombre_usuario ;
    }

    public function getContrasenia()    {
        return $this->_contrasenia ;
    }

    public function getEmail()    {
        return $this->_email ;
    }

    public function getFechaRegistro() {
        return $this->_fecha_registro ;
    }

    public function getRol()    {
        return $this->_rol ;
    }

    /* Data to echo */
    public function getArray()  {
        $usuario = array();

        $usuario['id'] = $this->getID();
        $usuario['nombre_usuario'] = $this->getNombreUsuario();
        $usuario['contrasenia'] = $this->getContrasenia();
        $usuario['email'] = $this->getEmail();
        $usuario['fecha_registro'] = $this->getFechaRegistro();
        $usuario['rol'] = $this->getRol();

        return $usuario ;
    }
}

?>