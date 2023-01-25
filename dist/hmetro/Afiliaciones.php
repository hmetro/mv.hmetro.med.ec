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
use Ocrend\Kernel\Models\IModels;
use Ocrend\Kernel\Models\Models;
use Ocrend\Kernel\Models\ModelsException;
use Ocrend\Kernel\Router\IRouter;

/**
 * Modelo Afiliaciones
 */

class Afiliaciones extends Models implements IModels
{
    # Variables de clase
    private $sortField = 'ROWNUM_';
    private $sortType = 'desc'; # desc
    private $start = 0;
    private $length = 10;
    private $searchField = null;
    private $startDate = null;
    private $endDate = null;
    private $_conexion = null;

    #Paciente
    private $numeroHistoriaClinica;
    private $codigoEspecialidadMedico;
    private $codigoHorario;
    private $fechaNacimiento;
    private $codigoConsulta;
    private $indentificacionPaciente;
    private $valorConsulta;
    private $numeroTurno;
    private $primerApellidoPaciente;
    private $primerNombrePaciente;
    private $codigoLugarAtencion;
    private $genero;
    private $tipoIdentificacionPaciente;
    private $valorCobertura;
    private $codigoPersona;

    private $codigoInstitucion;
    private $tipoIdentificacion;
    private $identificacion;
    private $primerApellido;
    private $segundoApellido;
    private $primerNombre;
    private $segundoNombre;
    private $estadoCivil;
    private $calle;
    private $numero;
    private $celular;
    private $email;
    private $pais;
    private $provincia;
    private $ciudad;
    private $distrito;

    #Datos de Factura
    private $apellidosFactura;
    private $correoFactura;
    private $direccionFactura;
    private $identificacionFactura;
    private $nombresFactura;
    private $tipoIdentificacionFactura;

    #Datos de TC
    private $identificacionTitular;
    private $nombreTitular;
    private $numeroAutorizacion;
    private $numeroVoucher;
    private $tipoTarjetaCredito;
    private $telefono;

    #Tipo de la cita
    private $tipoCita;

    # Variables de clase
    private $conexion;

    /**
     * Obtiene el token
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
     * Obtiene los datos del paciente con parámetro codigoPersona
     */
    public function getDatosPersonales()
    {
        global $config;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $datosPaciente[] = null;

        try {

            $this->getAuthorization();

            // Asignar parámetros de entrada
            $this->setParameters();

            // Validar parámetros de entrada

            // Código de la persona
            $this->codigoPersona = (int) $this->codigoPersona;

            if ($this->codigoPersona == null) {
                throw new ModelsException($config['errors']['codigoPersonaObligatorio']['message'], 1);
            } else {
                //Validaciones de tipo de datos y rangos permitidos
                if (!is_numeric($this->codigoPersona)) {
                    throw new ModelsException($config['errors']['codigoPersonaNumerico']['message'], 1);
                }
            }

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stid);

            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(),
                "BEGIN PRO_TEL_DATOS_PACIENTE(:pn_cod_persona, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_cod_persona", $this->codigoPersona, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $datosPaciente = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {

                $existeDatos = true;

                # RESULTADO OBJETO
                $datosPaciente[] = array(
                    'primerApellido' => $row[0] == null ? '' : $row[0],
                    'segundoApellido' => $row[1] == null ? '' : $row[1],
                    'primerNombre' => $row[2] == null ? '' : $row[2],
                    'segundoNombre' => $row[3] == null ? '' : $row[3],
                    'genero' => $row[4] == null ? '' : $row[4],
                    'estadoCivil' => $row[5] == null ? '' : $row[5],
                    'fechaNacimiento' => $row[6] == null ? '' : $row[6],
                    'cedula' => $row[7] == null ? '' : $row[7],
                    'pasaporte' => $row[8] == null ? '' : $row[8],
                    'ruc' => $row[9] == null ? '' : $row[9],
                    'direcciones' => $this->obtenerDirecciones($this->codigoPersona, $stid),
                    'mediosContacto' => $this->obtenerMediosContacto($this->codigoPersona, $stid),
                );
            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {

                return array(
                    'status' => true,
                    'data' => $datosPaciente[0],
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
     * Obtiene los medios de contacto del paciente
     */
    public function obtenerMediosContacto($codigoPersona, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $mediosContacto[] = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_CONTACTOS_PACIENTE(:pn_cod_persona, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_cod_persona", $codigoPersona, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $mediosContacto = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $mediosContacto[] = array(
                    'valor' => $row[0] == null ? '' : $row[0],
                    'tipo' => $row[1] == null ? '' : $row[1],
                );

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {
                return $mediosContacto;
            } else {
                return [];
            }

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * Obtiene las direcciones asociadas al paciente
     */
    public function obtenerDirecciones($codigoPersona, $stid)
    {
        global $config;

        //Inicialización de variables
        $pc_datos = null;
        $existeDatos = false;
        $direcciones[] = null;

        try {
            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN PRO_TEL_DIRECCION_PACIENTE(:pn_cod_persona, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_cod_persona", $codigoPersona, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $direcciones = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {
                $existeDatos = true;

                # RESULTADO OBJETO
                $direcciones[] = array(
                    'codigoDireccion' => $row[0] == null ? '' : $row[0],
                    'tipoDireccion' => $row[1] == null ? '' : $row[1],
                    'calle' => $row[2] == null ? '' : $row[2],
                    'numero' => $row[3] == null ? '' : $row[3],
                    'interseccion' => $row[4] == null ? '' : $row[4],
                    'referencia' => $row[5] == null ? '' : $row[5],
                    'pais' => $row[6] == null ? '' : $row[6],
                    'provincia' => $row[7] == null ? '' : $row[7],
                    'canton' => $row[8] == null ? '' : $row[8],
                );

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {
                return $direcciones;
            } else {
                return [];
            }

        } finally {
            //Libera recursos de conexión
            if ($pc_datos != null) {
                oci_free_statement($pc_datos);
            }

        }
    }

    /**
     * Consulta el listado de Historias Clínicas anteriores
     */
    public function getExpedientes()
    {
        global $config, $http;

        //Inicialización de variables
        $stid = null;
        $pc_datos = null;
        $existeDatos = false;
        $historiasClinicasAnteriores[] = null;
        $registraHistoriaClinica;

        try {

            $this->getAuthorization();

            //Asignar parámetros de entrada
            $this->setParameters();

            $this->length = $this->start + 10;

            //Validar parámetros de entrada
            //Número de historia clínica
            if ($this->numeroHistoriaClinica == null) {
                throw new ModelsException($config['errors']['numeroHistoriaClinicaObligatorio']['message'], 1);
            } else {
                //Validaciones de tipo de datos y rangos permitidos
                if (!is_numeric($this->numeroHistoriaClinica)) {
                    throw new ModelsException($config['errors']['numeroHistoriaClinicaNumerico']['message'], 1);
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

            //Conectar a la BDD
            $this->conexion->conectar();

            //Setear idioma y formatos en español para Oracle
            $this->setSpanishOracle($stid);

            $pc_datos = oci_new_cursor($this->conexion->getConexion());

            $stid = oci_parse($this->conexion->getConexion(), "BEGIN
                PRO_TEL_HISTORIAS_ANT(:pn_hc, :pn_num_reg, :pn_num_pag, :pc_datos); END;");

            // Bind the input num_entries argument to the $max_entries PHP variable
            oci_bind_by_name($stid, ":pn_hc", $this->numeroHistoriaClinica, 32);
            oci_bind_by_name($stid, ":pn_num_reg", $this->length, 32);
            oci_bind_by_name($stid, ":pn_num_pag", $this->start, 32);
            oci_bind_by_name($stid, ":pc_datos", $pc_datos, -1, OCI_B_CURSOR);

            //Ejecuta el SP
            oci_execute($stid);

            //Ejecutar el REF CURSOR como un ide de sentencia normal
            oci_execute($pc_datos);

            //Resultados de la consulta
            $historiasClinicasAnteriores = array();

            while (($row = oci_fetch_array($pc_datos, OCI_BOTH + OCI_RETURN_NULLS)) != false) {

                $existeDatos = true;
                $registraHistoriaClinica = 'S';

                //Valida que tenga registrado la
                if ($row[6] == 'S') {
                    if ($row[7] == '' and $row[8] == '' and $row[9] == '') {
                        $registraHistoriaClinica = 'N';
                    }
                }

                if ($row[5] == $this->id_user->codMedico && $row[3] == 'C. EXTERNA') {
                    # RESULTADO OBJETO
                    $historiasClinicasAnteriores[] = array(
                        'numeroAdmision' => $row[0] == null ? '' : $row[0],
                        'fechaAdmision' => $row[1] == null ? '' : $row[1],
                        'especialidad' => $row[2] == null ? '' : $row[2],
                        'origen' => $row[3] == null ? '' : $row[3],
                        'nombreMedicoTratante' => $row[4] == null ? '' : $row[4],
                        'codigoMedicoTratante' => $row[5] == null ? '' : $row[5],
                        'esTeleconsulta' => $row[6] == null ? '' : $row[6],
                        'registraHistoriaClinica' => $registraHistoriaClinica,
                        'motivoCitaMedica' => $row[7] == null ? '' : $row[7],
                    );
                }

            }

            //Verificar si la consulta devolvió datos
            if ($existeDatos) {

                return array(
                    'status' => true,
                    'data' => $historiasClinicasAnteriores,
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
    }
}
