<?php

class ComentarioException extends Exception {
    protected $code = 400 ; /* Bad request */
}

class Comentario {
    /* Constantes de la clase en la base de datos */
    private const ID_MIN_VAL = 0 ;
    private const INT_SIZE = 2147483647 ;
    private const TEXT_LEN = 500 ;

    /* Arreglo para checar los parametros del json */
    public const json_post_parameter_names = array(
        'idnoticia',
        'texto'
    );
    
    /*Atributos */
    private $_id ;
    private $_id_noticia ;
    private $_id_usuario ;
    private $_fecha ;
    private $_texto ;
    private $_nombreusuario ;   /* Creado con un inner join. */
    
    /* Constructor */
    public function __construct( $id, $id_noticia, $id_usuario, $fecha, $texto )  {
        $this->setID( $id );
        $this->setIdNoticia( $id_noticia );
        $this->setIdUsuario( $id_usuario );
        $this->setFecha( $fecha );
        $this->setTexto( $texto );
    }

    public static function NuevoComentarioDesdeFila( $row )    {
        $comentario = new Comentario(
            $row['idcomentario'],
            $row['idnoticia'],
            $row['idusuario'],
            $row['fecha'],
            $row['texto']
        );

        $comentario->setNombreUsuario( $row['nombreusuario'] );
        return $comentario ;
    }

    public static function ComentarioDesdeId( $id_comentario, $connection ) {
        $stringSQL = 'SELECT usuario.nombreusuario, comentario.idcomentario, 
                        comentario.idnoticia, comentario.idusuario,  
                            DATE_FORMAT( comentario.fecha, "%Y-%m-%d %H:%i") AS fecha, comentario.texto
                                FROM comentario INNER JOIN usuario 
                                    ON comentario.idusuario = usuario.idusuario 
                                        WHERE idcomentario = :id_comentario';

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':id_comentario', $id_comentario, PDO::PARAM_INT );

        $query->execute();
        if( !($row = $query->fetch( PDO::FETCH_ASSOC )) )   {
            throw new Exception('No se encontro el comentario', 404 );
        }

        return Comentario::NuevoComentarioDesdeFila( $row );
    }

    /* Setters */
    public function setID( $id ) {
        if( $id !== null && ( !is_numeric( $id ) || $id <= self::ID_MIN_VAL || $id >= self::INT_SIZE || $this->_id !== null ) ) {
            throw new UsuarioException('Error en id de comentario.');
        }

        $this->_id = $id ;
    }

    public function setIdNoticia( $id_noticia ) {
        if( $id_noticia !== null && ( !is_numeric( $id_noticia ) || $id_noticia <= self::ID_MIN_VAL || $id_noticia >= self::INT_SIZE || $this->_id_noticia !== null ) ) {
            throw new UsuarioException('Error en id_noticia de comentario.');
        }

        $this->_id_noticia = $id_noticia ;
    }

    public function setIdUsuario( $id_usuario ) {
        if( $id_usuario !== null && ( !is_numeric( $id_usuario ) || $id_usuario <= self::ID_MIN_VAL || $id_usuario >= self::INT_SIZE || $this->_id_usuario !== null ) ) {
            throw new UsuarioException('Error en id_usuario de comentario.');
        }

        $this->_id_usuario = $id_usuario ;
    }


    public function setFecha( $fecha ) {
        if( $fecha !== null && date_format(date_create_from_format('Y-m-d H:i', $fecha ), 'Y-m-d H:i') !== $fecha )   {
            throw new UsuarioException('Error en fecha de comentario.');
        }

        $this->_fecha = $fecha ;
    }

    public function setTexto( $texto )  {
        if( $texto !== null && ( strlen( $texto ) > self::TEXT_LEN || strlen( $texto ) <= 0 ) ) {
            throw new UsuarioException('Error en texto de comentario.');
        }

        $this->_texto = $texto ;
    }

    public function setNombreUsuario( $nombre_usuario ) {
        if( $nombre_usuario !== null && strlen( $nombre_usuario ) <= 0 ) {
            throw new UsuarioException('Error en nombreusuario de comentario.');
        }

        $this->_nombreusuario = $nombre_usuario ;
    }

    /* Getters */
    public function getID() {
        return $this->_id ;
    }

    public function getIdNoticia() {
        return $this->_id_noticia ;
    }

    public function getIdUsuario()    {
        return $this->_id_usuario ;
    }

    public function getFecha()    {
        return $this->_fecha ;
    }

    public function getTexto() {
        return $this->_texto ;
    }

    public function getNombreUsuario()  {
        return $this->_nombreusuario ;
    }

    /* Data to echo */
    public function getArray()  {
        $comentario = array();

        $comentario['id'] = $this->getID();
        $comentario['id_noticia'] = $this->getIdNoticia();
        $comentario['id_usuario'] = $this->getIdUsuario();
        $comentario['fecha'] = $this->getFecha();
        $comentario['texto'] = $this->getTexto();
        $comentario['nombre_usuario'] = $this->getNombreUsuario();

        return $comentario ;
    }
}

?>