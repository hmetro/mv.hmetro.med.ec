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

use app\models\v3 as VModel;
use app\models\v3\hmetro as Model;
use DateTime;
use Exception;
use Ocrend\Kernel\Models\IModels;
use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\ModelsException;
use Ocrend\Kernel\Router\IRouter;

/**
 * Modelo Odbc GEMA -> FACTURAS
 */

class Facturas extends Models implements IModels
{
    # Variables de clase
    private $USER = null;
    private $sortField = 'ROWNUM_';
    private $sortType = 'desc'; # desc
    private $offset = 1;
    private $limit = 25;
    private $searchField = null;
    private $startDate = null;
    private $endDate = null;
    private $tresMeses = null; # Se muestran resultados solo hasta los tres meses de la fecha actual
    private $_conexion = null;

    # Variables de clase
    private $conexion;
    private $numeroHistoriaClinica;
    private $numeroAdmision;
    private $codigoHorario;
    private $numeroTurno;

    /**
     * Parámetros de generar una factura web
     */
    private function setParametersGenerarFacturaWeb()
    {
        global $http;

        foreach ($http->request->all() as $key => $value) {
            $this->$key = $value;
        }

    }

    /**
     * Valida los parámetros de entrada generar factura web
     */
    private function validarParametrosGenerarFacturaWeb()
    {
        global $config;

        //Código de horario
        if ($this->codigoHorario == null) {
            throw new ModelsException($config['errors']['codigoHorarioObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($this->codigoHorario)) {
                throw new ModelsException($config['errors']['codigoHorarioNumerico']['message'], 1);
            }
        }

        //Número de turno
        if ($this->numeroTurno == null) {
            throw new ModelsException($config['errors']['numeroTurnoObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($this->numeroTurno)) {
                throw new ModelsException($config['errors']['numeroTurnoNumerico']['message'], 1);
            }
        }

        //Número de historia clínica
        if ($this->numeroHistoriaClinica == null) {
            throw new ModelsException($config['errors']['numeroHistoriaClinicaObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($this->numeroHistoriaClinica)) {
                throw new ModelsException($config['errors']['numeroHistoriaClinicaNumerico']['message'], 1);
            }
        }

        //Número de admisión
        if ($this->numeroAdmision == null) {
            throw new ModelsException($config['errors']['numeroAdmisionObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($this->numeroAdmision)) {
                throw new ModelsException($config['errors']['numeroAdmisionNumerico']['message'], 1);
            }
        }
    }

    /**
     * Permite generar una factura web
     */
    public function generarFacturaWeb()
    {
        global $config;

        //Inicialización de variables
        $stmt = null;
        $codigoRetorno = null;
        $mensajeRetorno = null;

        try {

            //Asignar parámetros de entrada
            $this->setParametersGenerarFacturaWeb();

            //Validar parámetros de entrada
            $this->validarParametrosGenerarFacturaWeb();

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stmt);

            $stmt = oci_parse($this->conexion->getConexion(), 'BEGIN
                PRO_GENERA_FACTURA_WEB(:pn_hcl, :pn_adm, :pn_horario, :pn_turno, :pc_error, :pc_desc_error); END;');

            // Bind the input parameter
            oci_bind_by_name($stmt, ':pn_hcl', $this->numeroHistoriaClinica, 32);
            oci_bind_by_name($stmt, ':pn_adm', $this->numeroAdmision, 32);
            oci_bind_by_name($stmt, ':pn_horario', $this->codigoHorario, 32);
            oci_bind_by_name($stmt, ':pn_turno', $this->numeroTurno, 32);

            // Bind the output parameter
            oci_bind_by_name($stmt, ':pc_error', $codigoRetorno, 32);
            oci_bind_by_name($stmt, ':pc_desc_error', $mensajeRetorno, 500);

            oci_execute($stmt);

            //Valida el código de retorno del SP
            if ($codigoRetorno == 0) {

                $at = new VModel\Atenciones;
                $at->facturarAtencion($this->numeroHistoriaClinica, $this->numeroAdmision);

                //Cita cancelada exitosamente
                return array(
                    'status' => true,
                    'data' => [],
                    'message' => $mensajeRetorno
                );
            } elseif ($codigoRetorno == -1) {
                //Mensajes de aplicación
                throw new ModelsException($mensajeRetorno, 1);
            } else {
                //Mensajes de errores técnicos
                throw new Exception($mensajeRetorno, -1);
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
                'errorCode' => $ex->getCode()
            );

        } finally {
            //Libera recursos de conexión
            if ($stmt != null) {
                oci_free_statement($stmt);
            }

            //Cierra la conexión
            $this->conexion->cerrar();
        }

    }

    private function setSpanishOracle($stmt)
    {

        $sql = "alter session set NLS_LANGUAGE = 'SPANISH'";
        # Execute
        $stmt = oci_parse($this->conexion->getConexion(), $sql);
        oci_execute($stmt);

        $sql = "alter session set NLS_TERRITORY = 'SPAIN'";
        # Execute
        $stmt = oci_parse($this->conexion->getConexion(), $sql);
        oci_execute($stmt);

        $sql = " alter session set NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI'";
        # Execute
        $stmt = oci_parse($this->conexion->getConexion(), $sql);
        oci_execute($stmt);

    }

    private function getAuthorizationn()
    {

        try {

            global $http;

            $token = $http->headers->get("Authorization");

            $auth = new Model\Auth;
            $key = $auth->GetData($token);

            $this->USER = $key;

        } catch (ModelsException $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }
    }

    private function errorsPagination()
    {

        try {

            if ($this->limit > 25) {
                throw new ModelsException('!Error! Solo se pueden mostrar 25 resultados por página.');
            }

            if ($this->limit == 0 or $this->limit < 0) {
                throw new ModelsException('!Error! {Limit} no puede ser 0 o negativo');
            }

            if ($this->offset == 0 or $this->offset < 0) {
                throw new ModelsException('!Error! {Offset} no puede ser 0 o negativo.');
            }

            return false;
        } catch (ModelsException $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }
    }

    private function setParameters(array $data)
    {

        try {

            foreach ($data as $key => $value) {

                $this->$key = strtoupper($value);
            }

            if ($this->startDate != null and $this->endDate != null) {

                $startDate = $this->startDate;
                $endDate = $this->endDate;

                $sd = new DateTime($startDate);
                $ed = new DateTime($endDate);

                if ($sd->getTimestamp() > $ed->getTimestamp()) {
                    throw new ModelsException('!Error! Fecha inicial no puede ser mayor a fecha final.');
                }

            }

            $fecha = date('d-m-Y');
            $nuevafecha = strtotime('-3 month', strtotime($fecha));

            # SETEAR FILTRO HASTA TRES MESES
            $this->tresMeses = date('d-m-Y', $nuevafecha);

            return false;
        } catch (ModelsException $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }
    }

    # Ordenar array por campo
    public function orderMultiDimensionalArray($toOrderArray, $field, $inverse = 'desc')
    {
        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
            $position[$key] = $row[$field];
            $newRow[$key] = $row;
        }
        if ($inverse == 'desc') {
            arsort($position);
        } else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    private function get_Order_Pagination(array $arr_input)
    {
        # SI ES DESCENDENTE

        $arr = array();
        $NUM = 1;

        if ($this->sortType == 'desc') {

            $NUM = count($arr_input);
            foreach ($arr_input as $key) {
                $key['NUM'] = $NUM;
                $arr[] = $key;
                $NUM--;
            }

            return $arr;

        }

        # SI ES ASCENDENTE

        foreach ($arr_input as $key) {
            $key['NUM'] = $NUM;
            $arr[] = $key;
            $NUM++;
        }

        return $arr;
    }

    private function get_page(array $input, $pageNum, $perPage)
    {
        $start = ($pageNum - 1) * $perPage;
        $end = $start + $perPage;
        $count = count($input);

        // Conditionally return results
        if ($start < 0 || $count <= $start) {
            // Page is out of range
            return array();
        } else if ($count <= $end) {
            // Partially-filled page
            return array_slice($input, $start);
        } else {
            // Full page
            return array_slice($input, $start, $end - $start);
        }
    }

/**
 * __construct()
 */

    public function __construct(IRouter $router = null)
    {
        parent::__construct($router);

        //Instancia la clase conexión a la base de datos
        $this->conexion = new Conexion();
    }
}
