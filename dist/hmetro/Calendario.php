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
use DateTime;
use Exception;
use Ocrend\Kernel\Models\IModels;
use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\ModelsException;
use Ocrend\Kernel\Models\Traits\DBModel;
use Ocrend\Kernel\Router\IRouter;

/**
 * Modelo Odbc GEMA -> Calendario
 */

class Calendario extends Models implements IModels
{

    use DBModel;

    # Variables de clase
    private $conexion;
    private $start = 0;
    private $length = 10;
    private $startDate = null;
    private $endDate = null;
    private $codigoMedico = null;
    private $tipoHorario = 2;
    private $codigoInstitucion = 1;
    private $fechaInicial = null;
    private $fechaFinal = null;
    private $horaInicial = null;
    private $horaFinal = null;
    private $nombresPaciente = null;
    private $duracion = null;
    private $codigoOrganigrama = null;
    private $lunes = null;
    private $martes = null;
    private $miercoles = null;
    private $jueves = null;
    private $viernes = null;
    private $sabado = null;
    private $domingo = null;
    private $codigoHorario = null;
    private $numeroTurno = null;
    private $codigoEspecialidad = null;

    /**
     * getAuthorizationn
     *
     */

    private function getAuthorization()
    {

        try {

            global $http;

            $token = $http->headers->get("Authorization");

            $auth = new Model\Auth;
            $data = $auth->GetData($token);

            $this->id_user = $data;

            $this->codigoMedico = $this->id_user->codMedico;

            $this->codigoEspecialidad = $this->id_user->codigoEspecialidadMedico;

        } catch (ModelsException $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Asigna los parámetros de entrada
     */
    private function setParameters()
    {
        global $http;

        foreach ($http->query->all() as $key => $value) {
            $this->$key = $value;
        }

        foreach ($http->request->all() as $key => $value) {
            $this->$key = $value;
        }

    }

    /**
     * Valida los parámetros de entrada
     */
    private function setParametersMisCitas()
    {
        global $config;

        //Código del médico
        if ($this->codigoMedico == null) {
            throw new ModelsException($config['errors']['codigoMedicoObligatorio']['message'], 1);
        } else {
            //Validaciones de tipo de datos y rangos permitidos
            if (!is_numeric($this->codigoMedico)) {
                throw new ModelsException($config['errors']['codigoMedicoNumerico']['message'], 1);
            }
        }

        //Fecha de fin
        $this->endDate = "12-12-2030";
        if ($this->endDate == null) {
            throw new ModelsException($config['errors']['endDateObligatorio']['message'], 1);
        } else {
            $endDate = $this->endDate;

            $sd = new DateTime();
            $ed = new DateTime($endDate);

            if ($sd->getTimestamp() > $ed->getTimestamp()) {
                throw new ModelsException($config['errors']['endDateIncorrectaFechaHoy']['message'], 1);
            }
        }

        //Max row to fetch
        if ($this->length == null) {
            throw new ModelsException($config['errors']['lengthObligatorio']['message'], 1);
        } else {
            if ($this->length <= 0) {
                throw new ModelsException($config['errors']['lengthIncorrecto']['message'], 1);
            }
        }

        //Min row to fetch
        if ($this->start == null) {
            throw new ModelsException($config['errors']['startObligatorio']['message'], 1);
        } else {
            if ($this->start < 0) {
                throw new ModelsException($config['errors']['startIncorrecto']['message'], 1);
            }
        }

        //Código de tipo de horario
        if ($this->tipoHorario == null) {
            throw new ModelsException($config['errors']['tipoHorarioObligatorio']['message'], 1);
        } else {
            if ($this->tipoHorario < 0) {
                throw new ModelsException($config['errors']['tipoHorarioIncorrecto']['message'], 1);
            }
        }
    }

    /**
     * Todas las citas agendadas del Medico
     *
     * @return array : Con información de éxito/falla.
     */

    public function getCitasAgendadas_Medico()
    {

        try {

            global $config, $http;

            $this->getAuthorization();

            $idMedico = $this->id_user->codMedico;

            $query = $this->db->select("*", 'VW_CITAS_HMQ', null, " codigoMedico = '" . $idMedico . "' AND statusCita IN ('2','3') order by idCita desc ");

            if ($query == false) {
                throw new ModelsException('No existe resultados.');
            }

            return array(
                'status' => true,
                'data' => $query,
            );

        } catch (ModelsException $e) {

            return array(
                'status' => false,
                'data' => [],
                'message' => $e->getMessage(),
            );

        }

    }

    /**
     * Consulta de las citas aegdnadas modo calendario CITAS AGENDADAS
     */
    public function getMiCalendario()
    {
        global $config, $http;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $agendaPendienteMedico[] = null;
        $this->start = "0";
        $this->length = "10000";

        try {
            $this->getAuthorization();

            //Asignar parámetros de entrada
            $this->setParameters();

            //Validar parámetros de entrada
            $this->setParametersMisCitas();

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stid);

            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_AGENDAS_MEDICO_PEN(:pc_cod_medico, :pn_tip_horario, :pc_fec_fin, :pn_num_reg, :pn_num_pag, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pc_cod_medico", $this->codigoMedico, 32);
            oci_bind_by_name($stid, ":pn_tip_horario", $this->tipoHorario, 32);
            oci_bind_by_name($stid, ":pc_fec_fin", $this->endDate, 32);
            oci_bind_by_name($stid, ":pn_num_reg", $this->length, 32);
            oci_bind_by_name($stid, ":pn_num_pag", $this->start, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $agendaPendienteMedico = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {

                $existeDatos = true;

                # RESULTADO OBJETO
                $agendaPendienteMedico[] = array(
                    'codigoHorario' => $row[0] == null ? '' : $row[0],
                    'numeroTurno' => $row[1] == null ? '' : $row[1],
                    'fecha' => $row[2] == null ? '' : $row[2],
                    'horaInicio' => $row[3] == null ? '' : $row[3],
                    'horaFin' => $row[4] == null ? '' : $row[4],
                    'primerApellidoPaciente' => $row[5] == null ? '' : $row[5],
                    'segundoApellidoPaciente' => $row[6] == null ? '' : $row[6],
                    'primerNombrePaciente' => $row[7] == null ? '' : $row[7],
                    'segundoNombrePaciente' => $row[8] == null ? '' : $row[8],
                    'codigoPersonaPaciente' => $row[9] == null ? '' : $row[9],
                    'numeroHistoriaClinica' => $row[10] == null ? '' : $row[10],
                    'numeroAdmision' => $row[11] == null ? '' : $row[11],
                    'asistio' => $row[12] == null ? '' : $row[12],
                    'timestamp' => $row[2] == null ? '' : strtotime($row[2]),
                    'codigoMedico' => $this->codigoMedico,
                    'codigoEspecialidad' => $this->codigoEspecialidad,
                    'start' => date('Y-m-d', strtotime($row[2])) . 'T' . $row[3] . ':00',
                    'end' => date('Y-m-d', strtotime($row[2])) . 'T' . $row[4] . ':00',
                    'title' => $row[5],
                    'description' => $row[10],
                    'id' => $row[0] . '-' . $row[1],

                );

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {

                return array(
                    'status' => true,
                    'data' => $agendaPendienteMedico,
                    'start' => (int) $this->start,
                    'length' => (int) $this->length,
                );

            } else {
                throw new ModelsException($config['errors']['noExistenResultados']['message'], 1);
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

    /**
     * Valida los parámetros de entrada
     */
    private function validarParametrosDetalleCita()
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

    }

    /**
     * Consulta el detalle de la cita.
     */
    public function detalleCita()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;

        try {

            $this->getAuthorization();

            //Asignar parámetros de entrada
            $this->setParameters();

            //Validar parámetros de entrada
            $this->validarParametrosDetalleCita();

            //Conectar a la BDD
            $this->conexion->conectar();

            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CONFIRMA_CITA(:pn_cod_horario, :pn_num_turno, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_cod_horario", $this->codigoHorario, 32);
            oci_bind_by_name($stid, ":pn_num_turno", $this->numeroTurno, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $citasDisponibles = array();

            while (($row = oci_fetch_array($pc_datos, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                return array(
                    'status' => true,
                    'data' => array(
                        'nombresMedico' => $row['NOMBRE_MEDICO'],
                        'codigoEspecialidadMedico' => $row['COD_ESPEC'],
                        'especialidadMedico' => $row['DESC_ESPECIALIDAD'],
                        'fechaCita' => $row['FECHA_CITA'],
                        'horaCita' => $row['HORA_CITA'],
                        'duracionCita' => $row['DURACION'],
                        'codigoOrganigrama' => $row['COD_ORGANIGRAMA'],
                        'descripcionOrganigrama' => $row['DESC_ORGANIGRAMA'],
                        'direccionOrganigrama' => $row['DIRECCION'],
                        'codigoConsulta' => $row['COD_CONSULTA'],
                        'valorConsulta' => $row['VALOR_CONSULTA'],
                        'codigoLugarAtencion' => $row['CODIGO_LUGAR_ATENCION'],
                    ),
                );

                //print("Parámetro: " . $row['PARAMETRO']);
            }

            //Verificar si la consulta devolvió datos
            if (!$existeDatos) {

                throw new ModelsException($config['errors']['noExistenResultados']['message'], 1);

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

        $sql = " alter session set NLS_DATE_FORMAT = 'DD-MM-YYYY HH24:MI'";
        # Execute
        $stid = oci_parse($this->conexion->getConexion(), $sql);
        oci_execute($stid);

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
