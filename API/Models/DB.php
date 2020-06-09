<?php
/* Como todas las operaciones ocupan DB.php, ponemos aqui la opcion de responder a solictud OPTIONS. */
require_once('../Controllers/optionsController.php');

/* Si se usa la base de datos, tenemos la seguridad de que se establece la zona horaria. */
date_default_timezone_set('America/Monterrey');

class DatabaseException extends Exception   {
    protected $code = 500 ; /* Internal server error */
}

class DB    {
    private static $connection ;

    public static function getConnection()  {
        
        if( self::$connection !== null )    {
            throw new DatabaseException('Error: ya existe una conexión a la base de datos.', 503 );
        }

        $dns = 'mysql:host=localhost;dbname=db_sillondelentretenimiento;charset=utf8';
        $username = 'root';
        $password = '';

        self::$connection = new PDO( $dns, $username, $password );
        self::$connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        self::$connection->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );

        return self::$connection ;
    }
}

?>