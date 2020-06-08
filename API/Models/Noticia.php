<?php

class NoticiaException extends Exception {
    protected $code = 400 ; /* Bad request */
}

class Noticia {
    /* Constantes de la clase en la base de datos */
    private const ID_MIN_VAL = 0 ;
    private const TITLE_LEN = 50 ;
    private const TEXT_LEN = 1000 ;
    private const IMG_PATH_LEN = 300 ;
    private const INT_SIZE = 2147483647 ;
    private const SECCION = array(
        'VID' => 'Videojuegos',
        'SER' => 'Series',
        'PEL' => 'Peliculas'
    );

    /* Arreglo para checar los parametros del json */
    public const json_post_parameter_names = array(
        'seccion',
        'titulo',
        'texto',
        'imagepath'
    );
    
    /*Atributos */
    private $_id ;
    private $_seccion ;
    private $_idmoderador ;
    private $_titulo ;
    private $_fecha ;
    private $_texto ;
    private $_imagepath ;

    /* Constructor */
    public function __construct( $id, $seccion, $idmoderador, $titulo, $fecha, $texto, $imagepath )  {
        $this->setID( $id );
        $this->setSeccion( $seccion );
        $this->setIdModerador( $idmoderador );
        $this->setTitulo( $titulo );
        $this->setFecha( $fecha );
        $this->setTexto( $texto );
        $this->setImagePath( $imagepath );
    }

    public static function NuevaNoticiaDesdeFila( $row )    {
        $noticia = new Noticia(
            $row['idnoticia'],
            $row['seccion'],
            $row['idmoderador'],
            $row['titulo'],
            $row['fecha'],
            $row['texto'],
            $row['imagepath']
        );

        return $noticia ;
    }

    public static function ExisteSeccion( $seccion ) {
        
        if( !strcasecmp( $seccion, self::SECCION['VID'] )
            || !strcasecmp( $seccion, self::SECCION['SER'] )
                || !strcasecmp( $seccion, self::SECCION['PEL'] ) )
            return TRUE ;

        else
            return FALSE ;
    }

    public static function NoticiaDesdeId( $id_noticia, $connection )   {
        $stringSQL = 'SELECT idnoticia, seccion, idmoderador, titulo, 
                            DATE_FORMAT( fecha, "%Y-%m-%d %H:%i" ) AS fecha, texto, imagepath
                                FROM noticia WHERE idnoticia = :id_noticia';

        $query = $connection->prepare( $stringSQL );
        $query->bindParam(':id_noticia', $id_noticia, PDO::PARAM_INT );

        $query->execute();
        if( !($row = $query->fetch( PDO::FETCH_ASSOC )) )   {
            throw new DatabaseException('Error al leer la noticia desde id.');
        }

        return Noticia::NuevaNoticiaDesdeFila( $row );
    }

    public static function getNoticiaArray()    {
        $array = array(
            'idusuario' => null,
            'seccion' => null,
            'idmoderador' => null,
            'titulo' => null,
            'fecha' => null,
            'texto' => null,
            'imagepath' => null
        );

        return $array ;
    }

    /* Setters */
    public function setID( $id ) {
        if( $id !== null && ( !is_numeric( $id ) || $id <= self::ID_MIN_VAL || $id >= self::INT_SIZE || $this->_id !== null ) ) {
            throw new NoticiaException('Error en id de noticia.');
        }

        $this->_id = $id ;
    }

    public function setSeccion( $seccion )    {
        if( $seccion != null )  {
            if( !strcasecmp( $seccion, self::SECCION['VID'] ) )
                $this->_seccion = self::SECCION['VID'] ;

            else if( !strcasecmp( $seccion, self::SECCION['SER'] ) )
                $this->_seccion = self::SECCION['SER'];

            else if( !strcasecmp( $seccion, self::SECCION['PEL'] ) )
                $this->_seccion = self::SECCION['PEL'];
            
            //No ponemos aqui el valor por defecto por el caso de un rol inexistente.
            else
                throw new NoticiaException('Error en seccion de noticia.');
        }

        //Puede ser null
        $this->_seccion = $seccion ;
    }

    public function setIdModerador( $idmoderador )  {
        if( $idmoderador !== null && ( !is_numeric( $idmoderador ) || $idmoderador < self::ID_MIN_VAL || $idmoderador > self::INT_SIZE || $this->_idmoderador !== null ) ) {
            throw new NoticiaException('Error en idmoderador de noticia.');
        }

        $this->_idmoderador = $idmoderador ;
    }

    public function setTitulo( $titulo )    {
        if( $titulo !== null && ( strlen($titulo) > self::TITLE_LEN || strlen($titulo) <= 0 ) ) {
            throw new NoticiaException('Error en titulo de noticia');
        }

        $this->_titulo = $titulo ;
    }

    public function setFecha( $fecha ) {
        if( $fecha !== null && date_format(date_create_from_format( 'Y-m-d H:i', $fecha ), 'Y-m-d H:i') !== $fecha )   {
            throw new NoticiaException('Error en fecha de noticia.');
        }

        $this->_fecha = $fecha ;
    }

    public function setTexto( $texto ) {
        if( $texto !== null && ( strlen($texto) > self::TEXT_LEN || strlen($texto) <= 0 ) ) {
            throw new NoticiaException('Error en texto de noticia');
        }

        $this->_texto = $texto ;
    }

    public function setImagePath( $image_path ) {
        if( $image_path !== null && ( strlen($image_path) > self::IMG_PATH_LEN || strlen($image_path) <= 0 ) ) {
            throw new NoticiaException('Error en image_path de noticia');
        }

        $this->_imagepath = $image_path ;
    }

    /* Getters */
    public function getID() {
        return $this->_id ;
    }

    public function getSeccion() {
        return $this->_seccion ;
    }

    public function getIdModerador()    {
        return $this->_idmoderador ;
    }

    public function getTitulo()    {
        return $this->_titulo ;
    }

    public function getFecha() {
        return $this->_fecha ;
    }

    public function getTexto()    {
        return $this->_texto ;
    }

    public function getImagePath()    {
        return $this->_imagepath ;
    }

    /* Data to echo */
    public function getArray()  {
        $noticia = array();

        $noticia['id'] = $this->getID();
        $noticia['seccion'] = $this->getSeccion();
        $noticia['idmoderador'] = $this->getIdModerador();
        $noticia['titulo'] = $this->getTitulo();
        $noticia['fecha'] = $this->getFecha();
        $noticia['texto'] = $this->gettexto();
        $noticia['imagepath'] = $this->getImagePath();

        return $noticia ;
    }
}

?>