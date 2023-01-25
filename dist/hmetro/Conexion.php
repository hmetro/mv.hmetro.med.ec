<?php
namespace app\models\v3\hmetro;

class Conexion
{
    private $servidor;
    private $usuario;
    private $contrasenia;
    private $basedatos;
    private $conn;

    public function __construct()
    {
        global $config;

        $driver = $config['database']['drivers']['oracle_produccion'];

        $this->servidor = "(DESCRIPTION=( ADDRESS_LIST= (ADDRESS= (PROTOCOL=TCP) (HOST=" . $driver['host'] . ") (PORT=" . $driver['port'] . ")))( CONNECT_DATA= (SID=" . $driver['dbname'] . ") ))";
        $this->usuario = $driver['user'];
        $this->contrasenia = $driver['password'];
    }

    public function conectar()
    {
        $this->conn = oci_new_connect($this->usuario, $this->contrasenia, $this->servidor, 'AL32UTF8');
    }

    public function cerrar()
    {
        if ($this->conn != null) {
            oci_close($this->conn);
        }
    }

    public function getConexion()
    {
        return $this->conn;
    }
}
