<?php

/*
 * This file is part of the Ocrend Framewok 3 package.
 *
 * (c) Ocrend Software <info@ocrend.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace app\models\v3\hmetro;

use app\models\v3\hmetro as Model;
use Exception;
use Ocrend\Kernel\Helpers as Helper;
use Ocrend\Kernel\Models\IModels;
use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\ModelsException;
use Ocrend\Kernel\Models\Traits\DBModel;
use Ocrend\Kernel\Router\IRouter;

/**
 * Modelo Odbc GEMA -> Historia clínica
 */

class Diagnosticos extends Models implements IModels
{
    use DBModel;

    # Variables de clase
    private $conexion;
    private $start = 0;
    private $length = 20000;
    private $nombreDiagnostico;

    /**
     * Asigna los parámetros de entrada
     */
    private function setParameters()
    {
        global $http;

        foreach ($http->request->all() as $key => $value) {
            $this->$key = strtoupper($value);
        }

    }

    /**
     * Valida los parámetros de entrada
     */
    private function validarParametros()
    {
        global $config;

        //Nombre diagnostico
        if ($this->nombreDiagnostico == null) {
            $this->nombreDiagnostico = "%";
        } else {
            $this->nombreDiagnostico = $this->quitar_tildes(mb_strtoupper($this->sanear_string($this->nombreDiagnostico), 'UTF-8'));

            # Setear valores para busquedas dividadas
            if (stripos($this->nombreDiagnostico, ' ')) {
                $this->nombreDiagnostico = str_replace(' ', '%', $this->nombreDiagnostico);
            }
        }

    }

    private function setSpanishOracle($stid)
    {

        $sql = "alter session set NLS_LANGUAGE = 'SPANISH'";
        # Execute
        $stid = oci_parse($this->conexion->getConexion(), $sql);
        oci_execute($stid);

        $sql = "alter session set NLS_TERRITORY = 'SPAIN'";
        # Execute
        $stid = oci_parse($this->conexion->getConexion(), $sql);
        oci_execute($stid);

        $sql = " alter session set NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI'";
        # Execute
        $stid = oci_parse($this->conexion->getConexion(), $sql);
        oci_execute($stid);

    }

    /**
     * Log de respuestas HTTP
     *
     * @var array
     */
    private function getAuthorization()
    {

        try {

            global $http;

            $token = $http->headers->get("Authorization");

            $auth = new Model\Auth;
            $data = $auth->GetData($token);

            $this->id_user = $data;

        } catch (ModelsException $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Nueva Liked
     *
     * @return array : Con información de éxito/falla.
     */
    public function likedDg()
    {

        try {

            global $config, $http;

            $this->getAuthorization();

            # Set Variable
            $codigoMedico = $this->id_user->codMedico;
            $likedgs = $http->request->get('likedgs');

            # Verificar que no están vacíos
            if (Helper\Functions::e($codigoMedico, $likedgs)) {
                throw new ModelsException('Parámetros insuficientes para esta peticion.');
            }

            $medicoLiked = $this->db->select(
                '*',
                "likedgs",
                null,
                "  codigoMedico='" . $codigoMedico . "'    ",
                1
            );

            if ($medicoLiked !== false) {

                $data = array(
                    'likedgs' => $likedgs,
                );

                $update = $this->db->update('likedgs', $data, "codigoMedico='$codigoMedico'", 1);

            } else {

                $data = array(
                    'likedgs' => $likedgs,
                    'codigoMedico' => $codigoMedico,
                );

                # liberar licencia
                $idNota = $this->db->insert('likedgs', $data);
            }

            return array(
                'status' => true,
                'likedDgs' => $likedgs,
                'message' => 'exito.',
            );

        } catch (ModelsException $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }

    }

    /**
     * ELIMINAR LIKED
     *
     * @return array : Con información de éxito/falla.
     */
    public function unLikedDg()
    {

        try {

            global $config, $http;

            $this->getAuthorization();

            # Set Variable
            $codigoMedico = $this->id_user->codMedico;
            $likedgs = $http->request->get('likedgs');

            # Verificar que no están vacíos
            if (Helper\Functions::e($codigoMedico)) {
                throw new ModelsException('Parámetros insuficientes para esta peticion.');
            }

            $data = array(
                'likedgs' => $likedgs,
            );

            $update = $this->db->update('likedgs', $data, "codigoMedico='$codigoMedico'", 1);

            return array(
                'status' => true,
                'likedDgs' => $likedgs,
                'message' => 'exito.',
            );

        } catch (ModelsException $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }

    }

    public function getLikeDgs()
    {
        try {

            global $config, $http;

            # Set Variable
            $codigoMedico = $this->id_user->codMedico;

            # Verificar que no están vacíos
            if (Helper\Functions::e($codigoMedico)) {
                throw new ModelsException('Parámetros insuficientes para esta peticion.');
            }

            $dgs = $this->db->select(
                '*',
                "likedgs",
                null,
                " codigoMedico='" . $codigoMedico . "' ",
                1
            );

            if (false === $dgs) {
                throw new ModelsException('No existe likdgs.');
            }

            return array(
                'status' => true,
                'likdgs' => $dgs[0]['likedgs'],
            );

        } catch (ModelsException $e) {
            return array('status' => false, 'likdgs' => '', 'message' => $e->getMessage());
        }

    }

    /**
     * Obtiene los diagnósticos
     */
    public function getDiagnosticos()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $start = 0;
        $length = 20000;
        $pc_datos = null;
        $existeDatos = false;
        $listaDiagnosticos[] = null;

        try {

            $this->getAuthorization();

            //Asignar parámetros de entrada
            $this->setParameters();

            //Validar parámetros de entrada
            $this->validarParametros();

            $file = 'diagnosticos/diagnosticos.json';

            # sI EXISTE ARCHIVO PREGENERADO CARGA ARCHIVO
            if (file_exists($file)) {

                $datos_diagnosticos = file_get_contents($file);
                $json_diagnosticos = json_decode($datos_diagnosticos, true);

                # Devolver Información
                return array(
                    'status' => true,
                    'likedDgs' => $this->getLikeDgs()['likdgs'],
                    'data' => $json_diagnosticos,
                );

            } else {

                //Conectar a la BDD
                $this->conexion->conectar();

                //Setear idioma y formatos en español para Oracle
                $this->setSpanishOracle($stid);

                $pc_datos = oci_new_cursor($this->conexion->getConexion());

                $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_DIAGNOSTICOS(:pc_nombre_diag, :pn_num_reg, :pn_num_pag, :pc_datos); END;");

                // Bind the input num_entries argument to the $max_entries PHP variable
                oci_bind_by_name($stid, ":pc_nombre_diag", $this->nombreDiagnostico, 100);
                oci_bind_by_name($stid, ":pn_num_reg", $length, 32);
                oci_bind_by_name($stid, ":pn_num_pag", $start, 32);
                oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

                //Ejecuta el SP
                oci_execute($stid);

                //Ejecutar el REF CURSOR como un ide de sentencia normal
                oci_execute($pc_datos);

                //Resultados de la consulta
                $listaDiagnosticos = array();

                while (($row = oci_fetch_array($pc_datos, OCI_BOTH)) != false) {

                    $existeDatos = true;
                    # RESULTADO OBJETO
                    $listaDiagnosticos[] = array(
                        'codigoDiagnostico' => $row[0],
                        'descripcionDiagnostico' => $row[1],
                        'codigoGrupoDiagnostico' => $row[2],
                        'descripcionGrupoDiagnostico' => $row[3],
                        'hash' => md5($row[0] . '-' . $row[2]),
                    );

                }

                //Verificar si la consulta devolvió datos
                if ($existeDatos) {

                    $file = 'diagnosticos/diagnosticos.json';

                    $json_string = json_encode($listaDiagnosticos);
                    file_put_contents($file, $json_string);

                    return array(
                        'status' => true,
                        'likedDgs' => $this->getLikeDgs()['likdgs'],
                        'data' => $listaDiagnosticos,
                    );

                } else {
                    throw new ModelsException($config['errors']['noExistenResultados']['message'], 1);
                }

            }

        } catch (ModelsException $e) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
                'errorCode' => $e->getCode()
            );

        } catch (Exception $ex) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $ex->getMessage(),
                'errorCode' => -1
            );

        } finally {
            //Libera recursos de conexión
            if ($stid != null) {
                oci_free_statement($stid);
            }

            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

            //Cierra la conexión
            $this->conexion->cerrar();
        }
    }

    /*
     * Quita las tildes de una cadena
     */
    private function quitar_tildes($cadena)
    {
        $no_permitidas = array("%", "é", "í", "ó", "ú", "É", "Í", "Ó", "Ú", "ñ", "À", "Ã", "Ì", "Ò", "Ù", "Ã™", "Ã ", "Ã¨", "Ã¬", "Ã²", "Ã¹", "ç", "Ç", "Ã¢", "ê", "Ã®", "Ã´", "Ã»", "Ã‚", "ÃŠ", "ÃŽ", "Ã”", "Ã›", "ü", "Ã¶", "Ã–", "Ã¯", "Ã¤", "«", "Ò", "Ã", "Ã„", "Ã‹");
        $permitidas = array("", "e", "i", "o", "u", "E", "I", "O", "U", "n", "N", "A", "E", "I", "O", "U", "a", "e", "i", "o", "u", "c", "C", "a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "u", "o", "O", "i", "a", "e", "U", "I", "A", "E");
        $texto = str_replace($no_permitidas, $permitidas, $cadena);
        return $texto;
    }

    private function sanear_string($string)
    {

        $string = trim($string);

        //Esta parte se encarga de eliminar cualquier caracter extraño
        $string = str_replace(
            array(">", "< ", ";", ",", ":", "%", "|"),
            ' ',
            $string
        );

        /*

        if ($this->lang == 'en') {
        $string = str_replace(
        array("CALLE", "TORRE MEDICA", "CONSULTORIO", "CONS."),
        array('STREET', 'MEDICAL TOWER', 'DOCTOR OFFICE', 'DOCTOR OFFICE'),
        $string
        );
        }

         */

        return trim($string);
    }

    /**
     * __construct()
     */
    public function __construct(IRouter $router = null)
    {
        parent::__construct($router);

        //Instancia la clase conexión a la base de datos
        $this->conexion = new Conexion();
        $this->startDBConexion();

    }
}
